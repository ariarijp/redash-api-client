<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

class Column
{
    /**
     * @var string
     * @Type("string")
     * @SerializedName("friendly_name")
     */
    public $friendlyName;

    /**
     * @var string
     * @Type("string")
     */
    public $type;

    /**
     * @var string
     * @Type("string")
     */
    public $name;
}
