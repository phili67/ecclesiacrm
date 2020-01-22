<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;


class PledgeSearchRes extends BaseSearchRes
{
    public function __construct()
    {
        $this->name = _('Pledges');
        parent::__construct();
    }

    public function buildSearch(string $qry)
    {
        if ( SessionUser::getUser()->isFinanceEnabled() && SystemConfig::getBooleanValue('bEnabledFinance') )
        {
            //Pledges Search
            if (SystemConfig::getBooleanValue("bSearchIncludePledges"))
            {
                try {
                    $searchLikeString = '%'.$qry.'%';

                    $Pledges = PledgeQuery::create()
                        ->useFamilyQuery()
                            ->filterByName($searchLikeString, Criteria::LIKE)
                        ->endUse()
                        ->leftJoinDeposit()
                        ->withColumn('Deposit.Id', 'DepositId')
                        ->groupByDepositId()
                        ->limit (SystemConfig::getValue("iSearchIncludePledgesMax"))
                        ->find();

                    if (!is_null($Pledges))
                    {
                        $id=1;

                        foreach ($Pledges as $Pledge) {
                            $elt = ['id'=>'pledges-'.$id++,
                                'text'=>$Pledge->getFamily()->getName()." ("._("Deposit")." #".$Pledge->getDepositId().")",
                                'uri'=> SystemURLs::getRootPath() . "/DepositSlipEditor.php?DepositSlipID=".$Pledge->getDepositId()];

                            if (!is_null($Pledge->getDepositId())) {
                                array_push($this->results, $elt);
                            }
                        }
                    }
                } catch (Exception $e) {
                    LoggerUtils::getAppLogger()->warn($e->getMessage());
                }
            }
        }
    }
}
