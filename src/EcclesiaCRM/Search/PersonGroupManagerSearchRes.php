<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\GroupManagerPersonQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;
use Propel\Runtime\ActiveQuery\Criteria;


class PersonGroupManagerSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Group Manager");
        parent::__construct($global, "Group Manager");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isManageGroupsEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $currentUser = SessionUser::getUser();
                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
                $quickSearch = $this->isQuickSearch();
                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $peopleInCart = $shouldShowCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];

                /*
                 *
                 * if (SystemConfig::getBooleanValue('bGDPR')) {
                        $person_Props->filterByDateDeactivated(null);// GDPR, when a family is completely deactivated
                   }
                 */


                if ( mb_strtolower($qry) == mb_strtolower(_('groupmasters')) || mb_strtolower($qry) == mb_strtolower(_('groupmaster'))
                    || mb_strtolower($qry) == mb_strtolower(_('groupmanagers')) || mb_strtolower($qry) == mb_strtolower(_('groupmanager')) ) {// we search all the GroupMasters
                    $persons = GroupManagerPersonQuery::create()
                        ->leftJoinWithPerson()
                        ->leftJoinWithGroup()
                        ->usePersonQuery()
                        ->filterByDateDeactivated(null);

                    if (!$quickSearch) {
                        $persons->leftJoinWithFamily();
                    }

                    $persons->endUse();
                } else {
                    $persons = GroupManagerPersonQuery::create()
                        ->leftJoinWithPerson()
                        ->leftJoinWithGroup()
                        ->usePersonQuery()
                        ->filterByFirstName($searchLikeString, Criteria::LIKE)
                        ->_or()->filterByLastName($searchLikeString, Criteria::LIKE)
                        ->_and()->filterByDateDeactivated(null);

                    if (!$quickSearch) {
                        $persons->leftJoinWithFamily();
                    }

                    $persons->endUse();
                }


                if ($quickSearch) {
                    $persons->limit(SystemConfig::getValue("iSearchIncludePersonsMax"));
                }

                $persons = $persons->find();
                

                if ( $persons->count() > 0 ) {

                    $id = 1;

                    foreach ($persons as $per) {
                        $person = $per->getPerson();
                        $group = $per->getGroup();

                        if ($person === null || $group === null) {
                            continue;
                        }

                        $personId = $person->getId();
                        $groupId = $group->getId();

                        if ($quickSearch) {
                            $elt = ['id' => 'person-group-manager-id-' . $id++,
                                'text' => $person->getFullName(),
                                'uri' => "/v2/group/" . $groupId . "/view"
                            ];
                        } else  {
                            $fam = $person->getFamily();

                            $address = "";
                            if (!is_null($fam)) {
                                $address = '<a href="' . $rootPath . '/v2/people/family/view/' . $fam->getID() . '">' .
                                    $fam->getName() . MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()) .
                                    "</a>";
                            }

                            $inCart = isset($peopleInCart[$personId]);

                            $res = $this->buildActionButtons([
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => $rootPath . '/v2/people/person/editor/' . $personId,
                                    'icon' => '<i class="fas fa-pencil-alt"></i>',
                                    'classes' => 'btn btn-sm btn-outline-primary',
                                    'label' => '',
                                    'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                ],
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => $rootPath . '/v2/people/person/view/' . $personId,
                                    'icon' => '<i class="fas fa-search-plus"></i>',
                                    'classes' => 'btn btn-sm btn-outline-secondary',
                                    'label' => '',
                                    'tooltip' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')]
                                ],
                                [
                                    'activate' => $shouldShowCart,
                                    'type' => 'a',
                                    'href' => '',
                                    'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                    'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromPeopleCart' : ' AddToPeopleCart'),
                                    'label' => '',
                                    'datas' => ['cartpersonid' => $personId, 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                ]
                            ]);  

                            $elt = [
                                "id" => $personId,
                                "img" => $person->getJPGPhotoDatas(),
                                "searchresult" => _("Group")." : ". '<a href="'.$rootPath.'/v2/group/'.$groupId.'/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$group->getName().'</a>'
                                            ." (".'<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) . '</a>'.")",
                                "address" => (!$shouldSeePrivacyData) ? _('Private Data') : $address,
                                "type" => " " . _($this->getGlobalSearchType()),
                                "realType" => $this->getGlobalSearchType(),
                                "Gender" => "",
                                "Classification" => "",
                                "ProNames" => "",
                                "FamilyRole" => "",
                                "members" => "",
                                "actions" => $res
                            ];

                        }

                        array_push($this->results, $elt);
                    }
                }
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}



