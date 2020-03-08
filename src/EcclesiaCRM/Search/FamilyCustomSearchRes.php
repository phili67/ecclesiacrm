<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Person2group2roleP2g2rQuery;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\Base\FamilyCustomMasterQuery;
use EcclesiaCRM\Base\FamilyCustomQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;


class FamilyCustomSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Family Custom Search');
        parent::__construct($global, "Family Custom Search");
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludeFamilies")) {
            try {
                $searchLikeString = '%'.$qry.'%';

                // Get the lists of custom person fields
                $ormFamiliesCustomFields = FamilyCustomMasterQuery::create()
                    ->orderByCustomOrder()
                    ->find();

                $familiesCustom = FamilyCustomQuery::create();

                if (SystemConfig::getBooleanValue('bGDPR')) {
                    $familiesCustom->useFamilyQuery()
                        ->filterByDateDeactivated(null)
                        ->endUse();
                }

                foreach ($ormFamiliesCustomFields as $customfield ) {
                    $familiesCustom->withColumn($customfield->getCustomField());
                    $familiesCustom->where($customfield->getCustomField()." LIKE ?",$searchLikeString,\PDO::PARAM_STR );
                    $familiesCustom->_or();
                }

                if ($this->global_search) {
                    $familiesCustom->limit(SystemConfig::getValue("iSearchIncludeFamiliesMax"));
                }

                $familiesCustom->find();


                if (!is_null($familiesCustom))
                {
                    $id=1;

                    foreach ($familiesCustom as $fam) {
                        $elt = ['id' => 'family-custom-id-'.$id++,
                            "text" => $fam->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH")),
                            "uri" => $fam->getFamily()->getViewURI()
                        ];

                        if ($this->global_search) {
                            $members = $fam->getFamily()->getPeopleSorted();

                            $res_members = [];
                            $globalMembers = "";

                            foreach ($members as $member) {
                                $res_members[] = $member->getId();
                                $globalMembers .= '• <a href="'.SystemURLs::getRootPath().'/PersonView.php?PersonID='.$member->getId().'">'.$member->getFirstName()." ".$member->getLastName()."</a><br>";
                            }
                            $elt["text"] = _("Family").' : <a href="'.SystemURLs::getRootPath().'/FamilyView.php?FamilyID='.$fam->getFamily()->getId().'" data-toggle="tooltip" data-placement="top" data-original-title="'._('Edit').'">'.$fam->getFamily()->getName().'</a>'." "._("Members")." : <br>".$globalMembers;
                            $elt["id"] = $fam->getFamily()->getId();
                            $elt["address"] = $fam->getFamily()->getFamilyString(SystemConfig::getBooleanValue("bSearchIncludeFamilyHOH"));
                            $elt["type"] = _($this->getGlobalSearchType());
                            $elt["realType"] = $this->getGlobalSearchType();
                            $elt["Gender"] = "";
                            $elt["Classification"] = "";
                            $elt["ProNames"] = "";
                            $elt["FamilyRole"] = "";
                            $elt["inCart"] = Cart::FamilyInCart($fam->getFamily()->getId());
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
