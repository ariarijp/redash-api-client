<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

class ApiResponse
{
    /**
     * @var QueryResult
     * @Type("RedashApiClient\QueryResult")
     * @SerializedName("query_result")
     */
    public $queryResult;

    /**
     * @var Job
     * @Type("RedashApiClient\Job")
     * @SerializedName("job")
     */
    public $job;
}
