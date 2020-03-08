<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Base\Person2group2roleP2g2r;
use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\dto\SystemURLs;


class GroupSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Groups');
        parent::__construct($global, "Groups");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeGroups")) {
            try {
                $groups = GroupQuery::create()
                    ->filterByName("%$qry%", Criteria::LIKE)
                    ->withColumn('grp_Name', 'displayName')
                    ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/v2/group/",Group.Id,"/view")', 'uri')
                    ->select(['displayName', 'uri', 'Id']);


                if ($this->global_search) {
                    $groups->limit(SystemConfig::getValue("iSearchIncludeGroupsMax"));
                }

                $groups->find();


                if (!is_null($groups))
                {
                    $id=1;

                    foreach ($groups as $group) {
                        LoggerUtils::getAppLogger()->info(print_r($group,1));
                        $elt = ['id'=>'group-'.$id++,
                            'text'=>$group['displayName'],
                            'uri'=>$group['uri']];

                        if ($this->global_search) {
                            $members = Person2group2roleP2g2rQuery::create()->findByGroupId($group['Id']);

                            $res_members = [];

                            foreach ($members as $member) {
                                $res_members[] = $member->getPersonId();
                            }
                            $elt["id"] = $group['Id'];
                            $elt["address"] = "";
                            $elt["type"] = _($this->getGlobalSearchType());
                            $elt["realType"] = $this->getGlobalSearchType();
                            $elt["Gender"] = "";
                            $elt["Classification"] = "";
                            $elt["ProNames"] = "";
                            $elt["FamilyRole"] = "";
                            $elt["inCart"] = Cart::GroupInCart($group['id']);
                            $elt["members"] = $res_members;
                        }

                        array_push($this->results, $elt);
                    }
                }
            } catch (Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}
