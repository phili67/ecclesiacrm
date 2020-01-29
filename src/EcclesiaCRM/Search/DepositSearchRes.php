<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;
use WebDriver\Exception;


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
                    $date = InputUtils::FilterDate($qry);
                } catch (Exception $e) {
                    $date = "";
                }

                //LoggerUtils::getAppLogger()->info("date = ".$date);

                try {
                    $Deposits = DepositQuery::create()
                        ->filterByComment("%$qry%", Criteria::LIKE)
                        ->_or()
                            ->filterById($qry);

                    /*if (is_null($date)) {// only US date can work through api links
                            $Deposits->_or()
                                ->filterByDate($date, Criteria::LIKE);

                    }*/

                    $Deposits->_or()
                            ->usePledgeQuery()
                                ->filterByCheckno("%$qry%", Criteria::LIKE)
                            ->endUse()
                        ->withColumn('CONCAT("#",Deposit.Id," ",Deposit.Comment)', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/DepositSlipEditor.php?DepositSlipID=",Deposit.Id)', 'uri')
                        ->limit(SystemConfig::getValue("iSearchIncludeDepositsMax"))
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
