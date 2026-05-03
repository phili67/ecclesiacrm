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
                $searchNeedle = mb_strtolower($qry);
                $matchAll = str_contains($qry, '*') && trim(str_replace('*', '', $qry)) === '';
                $ormPerCustomFields = PersonCustomMasterQuery::Create()
                    ->orderByCustomOrder()
                    ->find();

                if ($ormPerCustomFields->count() === 0) {
                    return;
                }

                $id = 1;

                $shouldShowCart = $currentUser->isShowCartEnabled();
                $rootPath = SystemURLs::getRootPath();
                $shouldSeePrivacyData = $currentUser->isSeePrivacyDataEnabled();
                $isQuickSearch = $this->isQuickSearch();
                $peopleInCart = $shouldShowCart ? array_fill_keys(Cart::PeopleInCart(), true) : [];

                $perCustoms = PersonCustomQuery::Create()
                    ->setDistinct()
                    ->leftJoinWithPerson()
                    ->usePersonQuery()
                        ->filterByDateDeactivated(null)
                        ->leftJoinWithFamily()
                    ->endUse();

                foreach ($ormPerCustomFields as $customfield) {
                    $perCustoms->withColumn($customfield->getCustomField());
                }

                $perCustoms = $perCustoms->find();

                foreach ($perCustoms as $per) {
                    $person = $per->getPerson();

                    if ($person === null) {
                        continue;
                    }

                    foreach ($ormPerCustomFields as $customfield) {
                        $customFieldName = $customfield->getCustomField();
                        $fieldValue = $per->getVirtualColumn($customFieldName);

                        if (is_null($fieldValue)) {
                            continue;
                        }

                        $currentFieldData = OutputUtils::displayCustomField($customfield->getTypeId(), trim($fieldValue), $customfield->getCustomSpecial(), false);

                        if ($currentFieldData === null) {
                            continue;
                        }

                        $normalizedFieldData = mb_strtolower((string) $currentFieldData);

                        if (!$matchAll && mb_strpos($normalizedFieldData, $searchNeedle) === false) {
                            continue;
                        }

                        $personId = $person->getId();

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

                            $inCart = isset($peopleInCart[$personId]);

                            $res = "";
                            if ($shouldShowCart) {
                                $res = '<a href="' . $rootPath . '/v2/people/person/editor/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                            }

                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                . '</span>';

                            if ($shouldShowCart) {
                                $res .= '</a>&nbsp;';
                            }

                            if (!$inCart) {
                                if ($shouldShowCart) {
                                    $res .= '<a class="AddToPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-stack-1x fa-inverse fa-cart-plus\"></i>"
                                    . "                </span>\n";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            } else {
                                if ($shouldShowCart) {
                                    $res .= '<a class="RemoveFromPeopleCart" data-cartpersonid="' . $personId . '">';
                                }
                                $res .= "                <span class=\"fa-stack\">\n"
                                    . "                <i class=\"fas fa-square fa-stack-2x\"></i>\n"
                                    . "                <i class=\"fas fa-times fa-stack-1x fa-inverse\"></i>\n"
                                    . "                </span>\n";
                                if ($shouldShowCart) {
                                    $res .= "                </a>  ";
                                }
                            }
                            if ($shouldShowCart) {
                                $res .= '&nbsp;<a href="' . $rootPath . '/v2/people/person/print/' . $personId . '"  data-toggle="tooltip" data-placement="top" title="' . _('Print') . '">';
                            }
                            $res .= '<span class="fa-stack">'
                                . '<i class="fas fa-square fa-stack-2x"></i>'
                                . '<i class="fas fa-print fa-stack-1x fa-inverse"></i>'
                                . '</span>';
                            if ($shouldShowCart) {
                                $res .= '</a>';
                            }

                            $elt = [
                                "id" => $personId,
                                "img" => $person->getJPGPhotoDatas(),
                                "searchresult" => '<a href="' . $rootPath . '/v2/people/person/view/' . $personId . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">' . OutputUtils::FormatFullName($person->getTitle(), $person->getFirstName(), $person->getMiddleName(), $person->getLastName(), $person->getSuffix(), 3) . '</a>',
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
