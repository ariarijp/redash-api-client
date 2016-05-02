<?php

namespace RedashApiClient;

use GuzzleHttp\Client as GuzzleHttpClient;
use JMS\Serializer\SerializerBuilder;

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

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
     */
    public function __construct($baseUrl, $userApiKey = null)
    {
        $this->baseUrl = $baseUrl;

        if (!empty($userApiKey)) {
            $this->userApiKey = $userApiKey;
        }

        $this->httpClient = new GuzzleHttpClient();
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @param int         $id
     * @param string|null $apiKey
     * @param callable    $callback
     *
     * @throws RedashApiClient\Exception
     */
    public function getResults($id, $apiKey = null, callable $callback)
    {
        if (empty($apiKey)) {
            if (empty($this->userApiKey)) {
                throw new Exception();
            }

            $apiKey = $this->userApiKey;
        }

        $url = $this->baseUrl.sprintf('api/queries/%d/results.json', $id);
        $res = $this->httpClient->request('GET', $url, [
            'query' => ['api_key' => $apiKey],
        ]);

        $object = $this->serializer->deserialize($res->getBody(), Response::class, 'json');

        $columns = array_column($object->queryResult->data->columns, 'name');

        foreach ($object->queryResult->data->rows as &$row) {
            $callback($row, $columns);
        }
    }
}
