<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;


class DepositSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Deposits');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if ( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') )
        {
            //Deposits Search
            if (SystemConfig::getBooleanValue("bSearchIncludeDeposits"))
            {
                try {

                    $Deposits = DepositQuery::create()
                        ->limit(SystemConfig::getValue("iSearchIncludeDepositsMax"))
                        ->filterByComment("%$qry%", Criteria::LIKE)
                        ->_or()
                        ->filterById($qry)
                        ->_or()
                        ->usePledgeQuery()
                        ->filterByCheckno("%$qry%", Criteria::LIKE)
                        ->endUse()
                        ->withColumn('CONCAT("#",Deposit.Id," ",Deposit.Comment)', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/DepositSlipEditor.php?DepositSlipID=",Deposit.Id)', 'uri')
                        ->find();

                    if (!is_null($Deposits))
                    {
                        $id=1;

                        foreach ($Deposits as $Deposit) {
                            $elt = ['id'=>'deposit-'.$id++,
                                'text'=>$Deposit->getComment(),
                                'uri'=> SystemURLs::getRootPath() . "/DepositSlipEditor.php?DepositSlipID=".$Deposit->getId()];

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
