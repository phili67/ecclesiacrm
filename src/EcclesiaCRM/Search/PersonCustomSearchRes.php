<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\OutputUtils;


class PersonCustomSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _("Person Custom Field");
        parent::__construct($global, "Person Custom Field");
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isSeePrivacyDataEnabled();
    }

    public function buildSearch(string $qry)
    {
        if (SystemConfig::getBooleanValue("bSearchIncludePersons")) {
            try {
                $currentUser = SessionUser::getUser();
                $showCart = $currentUser->isShowCartEnabled();
                $showPrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $rootPath = SystemURLs::getRootPath();
                $isQuickSearch = $this->isQuickSearch();
                $peopleInCart = [];
                $ormPerCustomFields = PersonCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();

                $customFields = [];
                foreach ($ormPerCustomFields as $customfield) {
                    $customFields[] = [
                        'name' => $customfield->getCustomField(),
                        'typeId' => $customfield->getTypeId(),
                        'special' => $customfield->getCustomSpecial()
                    ];
                }

                if (empty($customFields)) {
                    return;
                }
                
                $id = 1;

                $perCustoms = PersonCustomQuery::Create()
                    ->leftJoinWithPerson();

                $perCustoms->usePersonQuery()
                    ->filterByDateDeactivated(null)
                ->endUse();

                foreach ($customFields as $customField) {
                    $perCustoms->withColumn($customField['name']);
                }

                $searchLikeString = '%' . str_replace('*', '%', $qry) . '%';
                $searchNeedle = str_replace('*', '', $qry);
                $customFieldSearchStarted = false;
                foreach ($customFields as $customField) {
                    if ($customFieldSearchStarted) {
                        $perCustoms->_or();
                    }
                    $perCustoms->where($customField['name'] . ' LIKE ?', $searchLikeString, \PDO::PARAM_STR);
                    $customFieldSearchStarted = true;
                }

                $perCustoms = $perCustoms->find();

                $processedPersonIds = [];
                foreach ($perCustoms as $per) {
                    $person = $per->getPerson();
                    if ($person === null) {
                        continue;
                    }

                    $personId = $person->getId();
                    if (isset($processedPersonIds[$personId])) {
                        continue;
                    }
                    $processedPersonIds[$personId] = true;

                    $matchedFieldValue = null;
                    foreach ($customFields as $customField) {
                        $fieldValue = $per->getVirtualColumn($customField['name']);
                        if (is_null($fieldValue)) {
                            continue;
                        }

                        if ($matchedFieldValue === null && ($searchNeedle === '' || stripos((string) $fieldValue, $searchNeedle) !== false)) {
                            $matchedFieldValue = trim((string) $fieldValue);
                        }

                        if ($matchedFieldValue === null) {
                            continue;
                        }

                        if ($isQuickSearch) {
                                    $elt = ['id' => 'person-custom-id-' . $id++,
                                        'text' => $person->getFullName(),
                                        'uri' => $person->getViewURI()
                                    ];
                                } else  {
                                    $fam = $person->getFamily();
    
                                    $address = "";
                                    if (!is_null($fam)) {
                                        $address = '<a href="' . $rootPath . '/v2/people/family/view/' . $fam->getID() . '">' .
                                            $fam->getName() . MiscUtils::FormatAddressLine($fam->getAddress1(), $fam->getCity(), $fam->getState()) .
                                            "</a>";
                                    }
    
                                    $personId = $person->getId();
                                    if (!array_key_exists($personId, $peopleInCart)) {
                                        $peopleInCart[$personId] = Cart::PersonInCart($personId);
                                    }
                                    $inCart = $peopleInCart[$personId];

                                    $res = $this->buildActionButtons([
                                        [
                                            'activate' => $showCart,
                                            'type' => 'a',
                                            'href' => $rootPath . '/v2/people/person/editor/' . $personId,
                                            'icon' => '<i class="fas fa-pencil-alt"></i>',
                                            'classes' => 'btn btn-sm btn-outline-primary',
                                            'label' => '',
                                            'datas' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('Edit')]
                                        ],
                                        [
                                            'activate' => $showCart,
                                            'type' => 'a',
                                            'href' => $rootPath . '/v2/people/person/view/' . $personId,
                                            'icon' => '<i class="fas fa-search-plus"></i>',
                                            'classes' => 'btn btn-sm btn-outline-secondary',
                                            'label' => '',
                                            'tooltip' => ['toggle' => 'tooltip', 'placement' => 'top', 'title' => _('View')]
                                        ],
                                        [
                                            'activate' => $showCart,
                                            'type' => 'a',
                                            'href' => '',
                                            'icon' => (!$inCart ? '<i class="fas fa-cart-plus fa-inverse"></i>' : '<i class="fas fa-times"></i>'),
                                            'classes' => 'btn btn-sm btn-primary' . ($inCart ? ' RemoveFromPeopleCart' : ' AddToPeopleCart'),
                                            'label' => '',
                                            'datas' => ['cartpersonid' => $personId, 'toggle' => 'tooltip', 'placement' => 'top', 'title' => (!$inCart ? _('Add to Cart') : _('Remove from Cart'))]
                                        ]
                                    ]);                                    
    
                                    $elt = [
                                        "id" => $person->getId(),
                                        "img" => $person->getJPGPhotoDatas(),
                                        "searchresult" => '<a href="' . $rootPath . '/v2/people/person/view/' . $person->getId() . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) . '</a>',
                                        "address" => (!$showPrivacyData) ? _('Private Data') : $address,
                                        "type" => " " . _($this->getGlobalSearchType()),
                                        "realType" => $this->getGlobalSearchType(),
                                        "Gender" => "",
                                        "Classification" => $matchedFieldValue ?? trim((string) $fieldValue),
                                        "ProNames" => "",
                                        "FamilyRole" => "",
                                        "members" => "",
                                        "actions" => $res
                                    ];
    
                        }

                        array_push($this->results, $elt);
                        break;
                    }
                }                
            } catch (\Exception $e) {
                LoggerUtils::getAppLogger()->warn($e->getMessage());
            }
        }
    }
}