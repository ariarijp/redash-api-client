<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

class Job
{
    const STATUS_PENDING = 1;
    const STATUS_STARTED = 2;
    const STATUS_SUCCESS = 3;
    const STATUS_FAILURE = 4;
    const STATUS_REVOKED = 4;

    /**
     * @var string
     * @Type("string")
     */
    public $id;

    /**
     * @var int
     * @Type("integer")
     * @SerializedName("status")
     */
    public $status;

    /**
     * @var int
     * @Type("integer")
     * @SerializedName("query_result_id")
     */
    public $queryResultId;

    /**
     * @var int
     * @Type("integer")
     * @SerializedName("updated_at")
     */
    public $updatedAt;

    /**
     * @var string
     * @Type("string")
     */
    public $error;
}
