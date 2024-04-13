<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Service\FinancialService;
use EcclesiaCRM\Search\SearchLevel;


class PaymentSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Payments');
        parent::__construct($global, 'Payments');
    }

    public function buildSearch(string $qry)
    {
        if ( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') )
        {
            //Search Payments
            if (SystemConfig::getBooleanValue("bSearchIncludePayments"))
            {
                try {
                    $financial = new FinancialService();
                    $Payments = $financial->searchPayments($qry);

                    $id = 1;

                    if (!is_null($Payments))
                    {
                        foreach ($Payments as $Payment) {
                            $elt = ['id'=>"payment-".$id++,
                                'text'=>$Payment['displayName'],
                                'uri'=>$Payment['uri']];

                            if ($this->isGlobalSearch()) {
                                $elt = [
                                    "id" => -1,
                                    "img" => '<i class="fas fa-university fa-2x"></i>',
                                    "searchresult" => '<a href="'.SystemURLs::getRootPath().'/v2/deposit/slipeditor/'.$Payment['dep_ID'].'/view" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">'.$Payment['displayName'].'</a>',
                                    "address" => "",
                                    "type" => _($this->getGlobalSearchType()),
                                    "realType" => " ".$this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => "",
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => "",
                                    "actions" => ""
                                ];

                                if ($id > SystemConfig::getValue("iSearchIncludePaymentsMax")) {
                                    break;
                                }
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
}
