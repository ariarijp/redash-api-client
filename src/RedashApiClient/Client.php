<?php

namespace RedashApiClient;

use Doctrine\Common\Annotations\AnnotationRegistry;
use GuzzleHttp\Client as GuzzleHttpClient;
use JMS\Serializer\SerializerBuilder;
use Psr\Http\Message\ResponseInterface;

AnnotationRegistry::registerLoader('class_exists');

class Client
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string|null
     */
    private $userApiKey = null;

    /**
     * @var SerializerBuilder
     */
    private $serializer;

    /**
     * @var GuzzleHttpClient
     */
    private $httpClient;

    /**
     * @param string      $baseUrl
     * @param string|null $userApiKey
     * @param array       $guzzleConfig
     */
    public function __construct($baseUrl, $userApiKey = null, array $guzzleConfig = [])
    {
        $this->baseUrl = $baseUrl;

        if (!empty($userApiKey)) {
            $this->userApiKey = $userApiKey;
        }

        $this->httpClient = new GuzzleHttpClient($guzzleConfig);
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @param int         $id
     * @param string|null $queryApiKey
     * @param bool        $refresh
     * @param callable    $callback
     *
     * @throws Exception
     */
    public function fetch($id, $queryApiKey, $refresh, callable $callback)
    {
        $apiKey = $queryApiKey;
        if (empty($apiKey) || $refresh) {
            if (empty($this->userApiKey)) {
                throw new Exception('API Key is required to call the API. If you want to fetch data with refresh option, You have to use User API Key.');
            }

            $apiKey = $this->userApiKey;
        }

        if ($refresh) {
            $this->refresh($id, $apiKey);
        }

        $response = $this->getQueryResult($id, $apiKey);
        $apiResponse = $this->deserialize($response);

        $columns = array_map(function (Column $column) {
            return $column->name;
        }, $apiResponse->queryResult->data->columns);

        foreach ($apiResponse->queryResult->data->rows as &$row) {
            $callback($row, $columns);
        }
    }

    /**
     * @deprecated use fetch()
     */
    public function getResults($id, $queryApiKey, callable $callback)
    {
        $this->fetch($id, $queryApiKey, false, $callback);
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|ResponseInterface
     */
    private function getQueryResult($id, $apiKey)
    {
        $url = $this->baseUrl.sprintf('api/queries/%d/results.json', $id);

        return $this->httpClient->request('GET', $url, [
            'query' => ['api_key' => $apiKey],
        ]);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return ApiResponse
     */
    private function deserialize(ResponseInterface $response)
    {
        return $this->serializer->deserialize($response->getBody(), ApiResponse::class, 'json');
    }

    /**
     * @param int    $id
     * @param string $apiKey
     */
    private function refresh($id, $apiKey)
    {
        $response = $this->refreshQueryResult($id, $apiKey);
        $apiResponse = $this->deserialize($response);

        while (true) {
            if (in_array($apiResponse->job->status, [Job::STATUS_SUCCESS, Job::STATUS_FAILURE])) {
                break;
            }

            $res = $this->getJob($apiResponse->job->id, $apiKey);
            $apiResponse = $this->deserialize($res);

            sleep(5);
        }
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function refreshQueryResult($id, $apiKey)
    {
        $url = $this->baseUrl.sprintf('api/queries/%d/refresh', $id);

        return $this->httpClient->request('POST', $url, [
            'query' => ['api_key' => $apiKey],
        ]);
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function getJob($id, $apiKey)
    {
        $url = $this->baseUrl.sprintf('api/jobs/%s', $id);

        return $this->httpClient->request('GET', $url, [
            'query' => ['api_key' => $apiKey],
        ]);
    }
}
