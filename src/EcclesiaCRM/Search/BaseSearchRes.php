<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\SearchRes;

abstract class BaseSearchRes {
    protected $name;
    protected $results;

    public function __construct()
    {
            $this->results = [];
    }

    public abstract function buildSearch (string $qry);

    public function getRes (string $qry) {
        $this->buildSearch($qry);
        if (!empty($this->results)) {
            return new SearchRes($this->name, $this->results);
        }
        return [];
    }
}
