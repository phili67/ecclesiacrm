<?php

namespace EcclesiaCRM\Search;


use EcclesiaCRM\Utils\LoggerUtils;

class SearchRes implements \JsonSerializable {
    protected $name;
    protected $array;

    public function __construct(string $name, array $array) {
        $this->name  = $name;
        $this->array = $array;
    }

    public function jsonSerialize() {
        return @['children' => $this->array,
            'text' => $this->name];
    }
}
