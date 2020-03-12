<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\SearchRes;

abstract class BaseSearchRes {
    protected $name;
    protected $results;
    protected $global_search;
    protected $search_type;

    public function __construct($global = false, $type = "normal")
    {
            $this->results = [];
            $this->global_search = $global;
            if ($global) {
                $this->search_type = $type;
            } else {
                $this->search_type = "normal";
            }
    }

    public function hasGlobalSearch()
    {
        return $this->global_search;
    }

    public function getGlobalSearchType()
    {
        return $this->search_type;
    }

    public abstract function buildSearch (string $qry);

    public function getRes (string $qry) {
        $this->buildSearch($qry);
        if (!empty($this->results)) {
            if ($this->hasGlobalSearch()) {
                return $this->results;
            } else {
                return new SearchRes($this->name, $this->results, $this->search_type);
            }
        }
        return [];
    }
}
