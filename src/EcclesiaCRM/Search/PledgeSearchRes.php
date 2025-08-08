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
    public function __construct($global = false)
    {
        $this->name = _('Pledges');
        parent::__construct($global, 'Pledges');
    }

    public function allowed (): bool
    {
        return SessionUser::getUser()->isFinanceEnabled();
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

                    $Pledges = PledgeQuery::create();

                    if (SystemConfig::getBooleanValue('bGDPR') && !SystemConfig::getBooleanValue('bSearchFinancesGDPR')) {// normally gdpr is more important than finances ??? Plogel, a deposit can always be seen but not a pledge
                        $Pledges
                            ->useFamilyQuery()
                            ->filterByDateDeactivated(null)
                            ->endUse()
                            ->_and();
                    }

                    $Pledges->useFamilyQuery()
                            ->filterByName($searchLikeString, Criteria::LIKE)
                        ->endUse();

                    $Pledges->leftJoinDeposit()
                        ->withColumn('Deposit.Id', 'DepositId')
                        ->groupByDepositId();

                    if ( $this->isQuickSearch() ) {
                        $Pledges->limit(SystemConfig::getValue("iSearchIncludePledgesMax"));
                    }

                    $Pledges->find();

                    if ( $Pledges->count() > 0 )
                    {
                        $id=1;

                        foreach ($Pledges as $Pledge) {
                            $uri = SystemURLs::getRootPath() . "/v2/deposit/pledge/editor/GroupKey/".$Pledge->getGroupkey()."/v2-deposit-slipeditor-" . $Pledge->getDepositId();
                            if ( $this->isQuickSearch() ) {
                                $elt = ['id' => 'pledges-' . $id++,
                                    'text' => $Pledge->getFamily()->getName() . " (" . _("Deposit") . " #" . $Pledge->getDepositId() . ")",
                                    'uri' => $uri];
                            } else {
                                if (!is_null($Pledge->getDepositId())) {
                                    $res = "";
                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res = '<a href="' . $uri . '" data-toggle="tooltip" data-placement="top" title="' . _('Edit') . '">';
                                    }

                                    $res .= '<span class="fa-stack">'
                                        .'<i class="fas fa-square fa-stack-2x"></i>'
                                        .'<i class="fas fa-pencil-alt fa-stack-1x fa-inverse"></i>'
                                        .'</span>';

                                    if (SessionUser::getUser()->isShowCartEnabled()) {
                                        $res .= '</a>&nbsp;';
                                    }

                                    $elt = [
                                        'id' => $Pledge->getDepositId(),
                                        'img' => '<i class="fas fa-university fa-2x"></i>',
                                        'searchresult' => '<a href="'.SystemURLs::getRootPath()."/v2/deposit/pledge/editor/GroupKey/".$Pledge->getGroupkey()."/v2-deposit-slipeditor-".$Pledge->getDepositId().'" data-toggle="tooltip" data-placement="top" title="'._('Edit').'">'.$Pledge->getFamily()->getName()." ("._("Deposit")." #".$Pledge->getDepositId().")".'</a>',
                                        'address' => "",
                                        'type' => " "._($this->getGlobalSearchType()),
                                        'realType' => $this->getGlobalSearchType(),
                                        'Gender' => "",
                                        'Classification' => "",
                                        'ProNames' => "",
                                        'FamilyRole' => "",
                                        "members" => "",
                                        'actions' => $res
                                    ];
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
