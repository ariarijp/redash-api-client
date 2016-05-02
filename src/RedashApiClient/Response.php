<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

class Response
{
    /**
     * @var QueryResult
     * @Type("RedashApiClient\QueryResult")
     * @SerializedName("query_result")
     */
    public $queryResult;
}
