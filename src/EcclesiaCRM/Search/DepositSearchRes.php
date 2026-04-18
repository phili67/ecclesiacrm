<?php

namespace EcclesiaCRM\Search;

use EcclesiaCRM\Map\DepositTableMap;
use EcclesiaCRM\Map\PledgeTableMap;
use EcclesiaCRM\Search\BaseSearchRes;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\DepositQuery;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\PledgeQuery;
use EcclesiaCRM\SessionUser;
use Propel\Runtime\ActiveQuery\Criteria;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Utils\OutputUtils;


class DepositSearchRes extends BaseSearchRes
{
    public function __construct($global = false)
    {
        $this->name = _('Deposits');
        parent::__construct($global, 'Deposits');
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isFinanceEnabled();
    }

    private function buildEditAction(string $uri): string
    {
        $action = '';

        if (SessionUser::getUser()->isShowCartEnabled()) {
            $action .= '<a href="' . $uri . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
        }

        $action .= '<span class="fa-stack">'
            . '<i class="fas fa-square fa-stack-2x"></i>'
            . '<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
            . '</span>';

        if (SessionUser::getUser()->isShowCartEnabled()) {
            $action .= '</a>';
        }

        return $action;
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
                } catch (\Exception $e) {
                    $date = null;
                }

                $searchLikeString = '%'.$qry.'%';

                $sRootPath = SystemURLs::getRootPath();

                try {
                    $Deposits = DepositQuery::create();

                    /*if (is_null($date)) {// only US date can work through api links
                            $Deposits->_or()
                                ->filterByDate($date, Criteria::LIKE);

                    }*/

                    $Deposits->withColumn('CONCAT("#",Deposit.Id," ",Deposit.Comment)', 'displayName')
                        ->withColumn('CONCAT("' . $sRootPath . '/v2/deposit/slipeditor/",Deposit.Id)', 'uri')
                        ->leftJoinPledge()
                        ->where( DepositTableMap::COL_DEP_COMMENT." LIKE  '".$searchLikeString."' OR ".DepositTableMap::COL_DEP_ID." = '".$qry."' OR ".PledgeTableMap::COL_PLG_CHECKNO." LIKE  '".$searchLikeString."'");

                    if ( $this->isQuickSearch() ) {
                        $Deposits->limit(SystemConfig::getValue("iSearchIncludeDepositsMax"));
                    }

                    $Deposits->find();

                    if ( $Deposits->count() > 0)
                    {
                        $id=1;

                        foreach ($Deposits as $Deposit) {
                            $elt = ['id'=>'deposit-'.$id++,
                                'text'=>$Deposit->getComment(),
                                'uri'=> $sRootPath . "/v2/deposit/slipeditor/".$Deposit->getId()];

                            if ($this->isGlobalSearch()) {
                                $res = $this->buildEditAction($elt['uri']);

                                $elt = [
                                    "id" => $Deposit->getId(),
                                    "img" => '<i class="fas fa-money-bill-alt fa-2x"></i>',
                                    "searchresult" => '<a href="'.$sRootPath."/v2/deposit/slipeditor/".$Deposit->getId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$Deposit->getComment().'</a>',
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
                    } else {
                        $Pledges = PledgeQuery::create()
                            ->withColumn('Pledge.plg_depID', 'depId')
                            ->filterByDepid(0, Criteria::NOT_EQUAL)
                            ->groupByGroupkey()
                            ->withColumn('SUM(Pledge.Amount)', 'sumAmount')
                            ->useDonationFundQuery()
                            ->withColumn("GROUP_CONCAT(DonationFund.Name SEPARATOR ', ')", 'DonationFundNames')
                            ->endUse()
                            ->leftJoinDeposit()
                            ->withColumn('Deposit.Closed', 'Closed')
                            ->useFamilyQuery()
                                ->withColumn('Family.fam_ID', 'famID')
                                ->withColumn('Family.fam_Name', 'famName')
                                ->filterByName($searchLikeString, Criteria::LIKE)
                                ->leftJoinPerson()                                    
                                ->groupById()
                                ->having('COUNT(Person.per_ID) > 1')
                            ->endUse()
                            ->find()
                            ->toArray();
                    
                    
                        foreach ($Pledges as $Pledge) {
                            $elt = ['id'=>'deposit-'.$Pledge['depId'],
                                'text'=> '#'.$Pledge['depId'].' '.$Pledge['FamilyString'],
                                'uri'=> $sRootPath . "/v2/deposit/slipeditor/".$Pledge['depId']];      
                                
                                
                            if ($this->isGlobalSearch()) {
                                $res = $this->buildEditAction($elt['uri']);

                                $uriFamily = '<a href="'.$sRootPath . "/v2/people/family/view/".$Pledge['famID'].'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$Pledge['FamilyString'].'</a>';
                                $uriDeposit = '<a href="'.$sRootPath . "/v2/deposit/slipeditor/".$Pledge['depId'].'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">#'.$Pledge['depId'].'</a>';

                                $elt = [
                                    "id" =>'deposit-'.$Pledge['depId'],
                                    "img" => '<i class="fas fa-money-bill-alt fa-2x"></i>',
                                    "searchresult" => $uriDeposit.' - '._('Amount').': '.$Pledge['sumAmount'].' - '._('Check number').': '.$Pledge['plgCheckNo'],
                                    "address" => $uriFamily,
                                    "type" => _($this->getGlobalSearchType()),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => $Pledge['DonationFundNames'],
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => "",
                                    "actions" => $res
                                ];
                            }

                            if (!in_array($elt['id'], array_column($this->results, 'id'))) {                                
                                array_push($this->results, $elt);
                            }                            
                        }                                
                        
                        $Pledges = PledgeQuery::create()
                            ->withColumn('Pledge.plg_depID', 'depId')
                            ->filterByDepid(0, Criteria::NOT_EQUAL)
                            ->groupByGroupkey()
                            ->withColumn('SUM(Pledge.Amount)', 'sumAmount')
                            ->useDonationFundQuery()
                            ->withColumn("GROUP_CONCAT(DonationFund.Name SEPARATOR ', ')", 'DonationFundNames')
                            ->endUse()
                            ->leftJoinDeposit()
                            ->withColumn('Deposit.Closed', 'Closed')
                            ->useFamilyQuery()
                                ->withColumn('Family.fam_ID', 'famID')
                                ->withColumn('Family.fam_Name', 'famName')
                                ->withColumn('Family.fam_Name', 'famAddress1')
                                ->filterByName($searchLikeString, Criteria::LIKE)
                                ->leftJoinPerson()                                    
                                ->groupById()
                                ->having('COUNT(Person.per_ID) = 1')
                            ->endUse()
                            ->find()
                            ->toArray();
                    
                    
                        foreach ($Pledges as $Pledge) {
                            $elt = ['id'=>'deposit-'.$Pledge['depId'],
                                'text'=> '#'.$Pledge['depId'].' '.$Pledge['FamilyString'],
                                'uri'=> $sRootPath . "/v2/deposit/slipeditor/".$Pledge['depId']];      
                                
                                
                            if ($this->isGlobalSearch()) {
                                $res = $this->buildEditAction($elt['uri']);

                                $family = FamilyQuery::create()->findOneById($Pledge['famID']);

                                $address .= $family->getFamilyString();
                                $persons = $family->getPeople();

                                $personId = null;
                                if ($persons->count() > 0) {
                                    $person = $persons->getFirst();    
                                    $personId = $person->getId();
                                }

                                $uriPerson = "";
                                if (!is_null($personId)) {
                                    $uriPerson = '<a href="'.$sRootPath . "/v2/people/person/view/".$personId.'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$address.'</a>';
                                }
                                
                                $uriDeposit = '<a href="'.$sRootPath . "/v2/deposit/slipeditor/".$Pledge['depId'].'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">#'.$Pledge['depId'].'</a>';

                                $elt = [
                                    "id" =>'deposit-'.$Pledge['depId'],
                                    "img" => '<i class="fas fa-money-bill-alt fa-2x"></i>',
                                    "searchresult" => $uriDeposit.' - '._('Amount').': '.OutputUtils::formatNumber($Pledge['sumAmount'], 'money',true).' - '._('Check number').': '.$Pledge['plgCheckNo'],
                                    "address" => $uriPerson,
                                    "type" => _($this->getGlobalSearchType()),
                                    "realType" => $this->getGlobalSearchType(),
                                    "Gender" => "",
                                    "Classification" => $Pledge['DonationFundNames'],
                                    "ProNames" => "",
                                    "FamilyRole" => "",
                                    "members" => "",
                                    "actions" => $res
                                ];
                            }

                            if (!in_array($elt['id'], array_column($this->results, 'id'))) {                                
                                array_push($this->results, $elt);
                            }                            
                        }   
                    }
                } catch (\Exception $e) {
                    LoggerUtils::getAppLogger()->warn($e->getMessage());
                }
            }
        }
    }
}
