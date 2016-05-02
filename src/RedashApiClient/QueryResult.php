<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

class QueryResult
{
    /**
     * @var int
     * @Type("integer")
     */
    public $id;

    /**
     * @var int
     * @Type("integer")
     * @SerializedName("data_source_id")
     */
    public $dataSourceId;

    /**
     * @var string
     * @Type("string")
     */
    public $query;

    /**
     * @var string
     * @Type("string")
     * @SerializedName("query_hash")
     */
    public $queryHash;

    /**
     * @var float
     * @Type("float")
     */
    public $runtime;

    /**
     * @var Data
     * @Type("RedashApiClient\Data")
     */
    public $data;
}
