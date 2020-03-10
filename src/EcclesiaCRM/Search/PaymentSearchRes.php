<?php

/* copyright 2020/03/10 Philippe Logel all right reserved */

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Service\FinancialService;


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

                            if ($this->global_search) {
                                $elt["id"] = -1;
                                $elt["address"] = "";
                                $elt["type"] = _($this->getGlobalSearchType());
                                $elt["realType"] = $this->getGlobalSearchType();
                                $elt["Gender"] = "";
                                $elt["Classification"] = "";
                                $elt["ProNames"] = "";
                                $elt["FamilyRole"] = "";
                                $elt["inCart"] = 0;
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
}
