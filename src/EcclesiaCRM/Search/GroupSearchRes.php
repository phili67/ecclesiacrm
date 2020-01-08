<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;


class GroupSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Groups');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            try {
                $groups = GroupQuery::create()
                    ->filterByName("%$qry%", Criteria::LIKE)
                    ->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"))
                    ->withColumn('grp_Name', 'displayName')
                    ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                    ->select(['displayName', 'uri'])
                    ->find();


                if (!is_null($groups))
                {
                    $id=1;

                    foreach ($groups as $group) {
                        $elt = ['id'=>'group-'.$id++,
                            'text'=>$group['displayName'],
                            'uri'=>$group['uri']];

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}


