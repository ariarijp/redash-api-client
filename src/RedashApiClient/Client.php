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
     * @var SerializerBuilder|Object
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
     * @param array       $parameters
     * @param callable|null    $callback
     * 
     * @return array|void
     * @throws Exception
     */
    public function fetch($id, $queryApiKey, $refresh, $parameters = [], callable $callback = null)
    {
        $apiKey = $queryApiKey;
        if (empty($apiKey) || $refresh) {
            if (empty($this->userApiKey)) {
                throw new Exception('API Key is required to call the API. If you want to fetch data with refresh option, You have to use User API Key.');
            }

            $apiKey = $this->userApiKey;
        }

        $queryResultId = 0;
        if ($refresh) {
            $res = $this->refresh($id, $apiKey, $parameters);
            $queryResultId = $res->job->queryResultId;
        }

        $response = $this->getQueryResult($id, $parameters, $apiKey, $queryResultId);
        $apiResponse = $this->deserialize($response);

        if (!$callback) {
            return $apiResponse;
        }

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
    public function getResults($id, $queryApiKey, $parameters = [], callable $callback = null)
    {
        $this->fetch($id, $queryApiKey, false, $parameters, $callback);
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|ResponseInterface
     */
    private function getQueryResult($id, $parameters = [], $apiKey, $queryResultId = 0)
    {
        $url = $this->baseUrl . sprintf('api/queries/%d/results.json', $id);

        $params = [
            'query' => ['api_key' => $apiKey],
        ];
        if ($parameters) {
            $url = str_replace('results', 'results/' . $queryResultId, $url);
        }
        return $this->httpClient->request('GET', $url, $params);
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
    public function refresh($id, $apiKey, $parameters = [])
    {
        $response = $this->refreshQueryResult($id, $apiKey, $parameters);
        $res = null;
        while (true) {
            if (in_array($response->job->status, [Job::STATUS_SUCCESS, Job::STATUS_FAILURE])) {
                break;
            }

            $res = $this->getJob($response->job->id, $apiKey);
            $response = $this->deserialize($res);

            sleep(1);
        }
        return $res ? $this->deserialize($res) : $res;
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function refreshQueryResult($id, $apiKey, $parameters = [])
    {
        $url = $this->baseUrl . sprintf('api/queries/%d/results', $id);
        $params = [
            'query' => ['api_key' => $apiKey],
        ];
        $params['json'] = [
            'max_age' => 0
        ];
        if($parameters){
            $params['json']['parameters'] = $parameters;
        }
        $response = $this->httpClient->request('POST', $url, $params);
        return $this->deserialize($response);
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function getJob($id, $apiKey)
    {
        $url = $this->baseUrl . sprintf('api/jobs/%s', $id);

        return $this->httpClient->request('GET', $url, [
            'query' => ['api_key' => $apiKey],
        ]);
    }
}
