<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\dto\Cart;
use EcclesiaCRM\Map\DepositTableMap;
use EcclesiaCRM\Map\PledgeTableMap;
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
    public function __construct($global = false)
    {
        $this->name = _('Deposits');
        parent::__construct($global, 'Deposits');
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
                    $date = null;
                }

                $searchLikeString = '%'.$qry.'%';

                try {
                    $Deposits = DepositQuery::create();

                    /*if (is_null($date)) {// only US date can work through api links
                            $Deposits->_or()
                                ->filterByDate($date, Criteria::LIKE);

                    }*/

                    $Deposits->withColumn('CONCAT("#",Deposit.Id," ",Deposit.Comment)', 'displayName')
                        ->withColumn('CONCAT("' . SystemURLs::getRootPath() . '/DepositSlipEditor.php?DepositSlipID=",Deposit.Id)', 'uri')
                        ->leftJoinPledge()
                        ->where( DepositTableMap::COL_DEP_COMMENT." LIKE  '".$searchLikeString."' OR ".DepositTableMap::COL_DEP_ID." = '".$qry."' OR ".PledgeTableMap::COL_PLG_CHECKNO." LIKE  '".$searchLikeString."'");

                    if (!$this->isGlobalSearch()) {
                        $Deposits->limit(SystemConfig::getValue("iSearchIncludeDepositsMax"));
                    }

                    $Deposits->find();

                    if (!is_null($Deposits))
                    {
                        $id=1;

                        foreach ($Deposits as $Deposit) {
                            $elt = ['id'=>'deposit-'.$id++,
                                'text'=>$Deposit->getComment(),
                                'uri'=> SystemURLs::getRootPath() . "/DepositSlipEditor.php?DepositSlipID=".$Deposit->getId()];

                            if ($this->isGlobalSearch()) {
                                $res = "";
                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '<a href="' . $elt['uri'] . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                }
                                $res .= '<span class="fa-stack">'
                                    .'<i class="fas fa-square fa-stack-2x"></i>'
                                    .'<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>';

                                if (SessionUser::getUser()->isShowCartEnabled()) {
                                    $res .= '</span>';
                                }

                                $elt = [
                                    "id" => -1,
                                    "img" => '<i class="fas fa-money-bill-alt fa-2x"></i>',
                                    "searchresult" => '<a href="'.SystemURLs::getRootPath()."/DepositSlipEditor.php?DepositSlipID=".$Deposit->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$Deposit->getComment().'</a>',
                                    "address" => "",
                                    "type" => _($this->getGlobalSearchType()),
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
                } catch (Exception $e) {
                    LoggerUtils::getAppLogger()->warn($e->getMessage());
                }
            }
        }
    }
}
