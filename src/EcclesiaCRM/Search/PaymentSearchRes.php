<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Service\FinancialService;


class PaymentSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Payments');
        parent::__construct();
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
