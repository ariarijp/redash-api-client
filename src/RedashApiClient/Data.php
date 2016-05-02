<?php

namespace RedashApiClient;

use JMS\Serializer\Annotation\Type;

class Data
{
    /**
     * @var array[]
     * @Type("array")
     */
    public $rows;

    /**
     * @var Column[]
     * @Type("array<RedashApiClient\Column>")
     */
    public $columns;
}
