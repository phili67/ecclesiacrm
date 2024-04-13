<?php

namespace EcclesiaCRM\Search;

class SearchRes implements \JsonSerializable {
    protected $name;
    protected $array;
    protected $type;

    public function __construct(string $name, array $array, $type = "normal") {
        $this->name  = $name;
        $this->array = $array;
        $this->type  = $type;
    }

    public function jsonSerialize() {
        if ($this->type == "normal") {
            return ['children' => $this->array,
                'text' => $this->name];
        } else {
            return $this->array;
        }
    }
}
