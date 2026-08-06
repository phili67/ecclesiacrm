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
    /**
     * @return string
     * @description Build the action buttons for the search results
     * each button is an array of [
     *  'activate' => true/false,
     *  'type' => 'a/button', 
     *  'href' => 'link/or none', 
     *  'icon' => '<i class="fa-inverse fa-cart-plus"></i>',
     *  'classes' => 'btn btn-sm btn-outline-secondary',
     *  'label' => 'button label', 
     *  'title' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')],
     *  'datas' => ['id' => '365', etc ...],
     */
    public function buildActionButtons (array $buttons) : string {
        $res = '<div class="btn-group" role="group" aria-label="Basic example"> ';
        foreach ($buttons as $button) {
            if (!($button['activate'] ?? true)) {
                continue;
            }

            $datas = $button['datas'] ?? [];
            $tooltip = $button['tooltip'] ?? [
                'toggle' => $datas['toggle'] ?? 'tooltip',
                'placement' => $datas['placement'] ?? 'top',
                'title' => $datas['title'] ?? $button['label']
            ];

            $attributes = '';
            foreach ($datas as $data => $value) {
                if (in_array($data, ['toggle', 'placement', 'title'], true)) {
                    continue;
                }
                $attributes .= ' data-' . $data . '="' . $value . '"';
            }

            $attributes .= ' data-toggle="' . $tooltip['toggle'] . '"';
            $attributes .= ' data-placement="' . $tooltip['placement'] . '"';
            $attributes .= ' title="' . $tooltip['title'] . '"';

            if (($button['type'] ?? 'button') === 'a') {
                $res .= '<a class="' . $button['classes'] . '" ' . ($button['href'] ? 'href="' . $button['href'] . '"' : '') . $attributes . '>';
                $res .= $button['icon'] . ' ' . $button['label'];
                $res .= '</a>';
            } else {
                $res .= '<button type="button" class="' . $button['classes'] . '"' . $attributes . '>';
                $res .= $button['icon'] . ' ' . $button['label'];
                $res .= '</button>';
            }
        }
        $res .= '</div>';
        return $res;
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
