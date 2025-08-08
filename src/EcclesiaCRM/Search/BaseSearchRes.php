<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\SearchRes;

class SearchLevel {
    public const QUICK_SEARCH   =   1;
    public const GLOBAL_SEARCH  =   2;
    public const STRING_RETURN  =   3;
}

abstract class BaseSearchRes {
    protected $name;
    protected $results;
    protected $search_Level;
    protected $search_type;

    public function __construct($level = SearchLevel::QUICK_SEARCH, $type = "normal")
    {
            $this->results = [];
            $this->search_Level = $level;
            if ($level == SearchLevel::GLOBAL_SEARCH or $level == SearchLevel::STRING_RETURN) {
                $this->search_type = $type;
            } else {
                $this->search_type = "normal";
            }
    }

    public function isQuickSearch()
    {
        return ($this->search_Level == SearchLevel::QUICK_SEARCH);
    }

    public function isGlobalSearch()
    {
        return ($this->search_Level == SearchLevel::GLOBAL_SEARCH);
    }

    public function isStringSearch()
    {
        return ($this->search_Level == SearchLevel::STRING_RETURN);
    }

    public function getGlobalSearchType()
    {
        return $this->search_type;
    }

    public abstract function buildSearch (string $qry);

    public abstract function allowed (): bool;

    public function getRes (string $qry) {
        $this->buildSearch($qry);
        if (!empty($this->results)) {
            if ( $this->isGlobalSearch() or $this->isStringSearch() ) {
                return $this->results;
            } else {
                return new SearchRes($this->name, $this->results, $this->search_type);
            }
        }
        return [];
    }
}
