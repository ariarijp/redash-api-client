<?php

namespace RedashApiClient;

use Doctrine\Common\Annotations\AnnotationRegistry;
use GuzzleHttp\Client as GuzzleHttpClient;
use JMS\Serializer\SerializerBuilder;

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
    public function __construct($baseUrl, $userApiKey = null, $guzzleConfig = [])
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
     * @param string|null $apiKey
     * @param callable    $callback
     *
     * @throws Exception
     */
    public function getResults($id, $apiKey = null, callable $callback)
    {
        if (empty($apiKey)) {
            if (empty($this->userApiKey)) {
                throw new Exception();
            }

            $apiKey = $this->userApiKey;
        }

        $jsonString = $this->getJsonString($id, $apiKey);
        $object = $this->deserialize($jsonString);

        $columns = array_map(function ($column) {
            return $column->name;
        }, $object->queryResult->data->columns);

        foreach ($object->queryResult->data->rows as &$row) {
            $callback($row, $columns);
        }
    }

    /**
     * @param int    $id
     * @param string $apiKey
     *
     * @return string
     */
    private function getJsonString($id, $apiKey)
    {
        $url = $this->baseUrl . sprintf('api/queries/%d/results.json', $id);
        $res = $this->httpClient->request('GET', $url, [
            'query' => ['api_key' => $apiKey],
        ]);

        return $res->getBody();
    }

    /**
     * @param string $jsonString
     *
     * @return RedashApiClient\Response
     */
    private function deserialize($jsonString)
    {
        return $this->serializer->deserialize($jsonString, Response::class, 'json');
    }
}
