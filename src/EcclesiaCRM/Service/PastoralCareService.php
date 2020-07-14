<?php

/*******************************************************************************
 *
 *  filename    : PastoralCareService.php
 *  last change : 2020-06-24
 *  description : manage the Pastoral Care
 *
 *  http://www.ecclesiacrm.com/
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be included in another software
 *  Updated : 2020-06-24
 *
 ******************************************************************************/

namespace EcclesiaCRM\Service;

use EcclesiaCRM\Person;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\FamilyQuery;

use EcclesiaCRM\PastoralCareQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\dto\SystemConfig;

use EcclesiaCRM\map\PersonTableMap;
use EcclesiaCRM\Map\FamilyTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\PastoralCareTableMap;

use Propel\Runtime\ActiveQuery\Criteria;

use Propel\Runtime\Propel;

class PastoralCareService
{
    public function getRange()
    {
        $choice = SystemConfig::getValue('sPastoralcarePeriod');

        $date = new \DateTime('now');

        switch ($choice) {
            case 'Yearly 1':// choice 1 : Year-01-01 to Year-12-31
                $realDate = $date->format('Y') . "-01-01";

                $start = new \DateTime($realDate);

                $startPeriod = $start->format(SystemConfig::getValue('sDateFormatLong'));

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format(SystemConfig::getValue('sDateFormatLong'));
                break;
            case '365': // choice 2 : one year before now
                $endPeriod = $date->format(SystemConfig::getValue('sDateFormatLong'));
                $date->sub(new \DateInterval('P365D'));
                $startPeriod = $date->format(SystemConfig::getValue('sDateFormatLong'));
                $realDate = $date->format('Y-m-d');
                break;
            case 'Yearly 2':// choice 3 : from september to september
                if ((int)$date->format('m') < 9) {
                    $realDate = ($date->format('Y') - 1) . "-09-01";
                } else {
                    $realDate = $date->format('Y') . "-09-01";
                }

                $start = new \DateTime($realDate);

                $startPeriod = $start->format(SystemConfig::getValue('sDateFormatLong'));

                $start->add(new \DateInterval('P1Y'));
                $start->sub(new \DateInterval('P1D'));

                $endPeriod = $start->format(SystemConfig::getValue('sDateFormatLong'));
                break;
        }

        return ['startPeriod' => $startPeriod, 'endPeriod' => $endPeriod, 'realDate' => $realDate];
    }

    public function getPersonClassificationNotBeenReached($yet = false)
    {
        if (!$yet) {
            $pdo = Propel::getConnection();

            // TO DO : convert query to propel (complex).

            $sSQL = "SELECT family_fam.fam_Name AS LastName,
person_per.per_FirstName as FirstName,
family_fam.fam_ID AS FamilyId,
d.PastoralCareLastDate as PastoralCareLastDate,
family_fam.fam_Name AS FamilyName,
       person_per.per_Id AS Id,
COALESCE(cls.lst_OptionName, 'Unassigned') AS ClassName,
COALESCE(cls.lst_OptionID, 'Unassigned') AS ClassID,
COALESCE(cls.lst_ID, NULL) AS ListID
FROM person_per
LEFT JOIN family_fam ON (person_per.per_fam_ID=family_fam.fam_ID)
LEFT   JOIN (
   SELECT pst_cr_person_id, max(pst_cr_date) AS PastoralCareLastDate
   FROM   pastoral_care
   GROUP  BY pst_cr_person_id
   ) d ON d.pst_cr_person_id = person_per.per_ID
LEFT JOIN list_lst cls ON (person_per.per_cls_ID=cls.lst_OptionID AND cls.lst_ID=1)
WHERE person_per.per_DateDeactivated IS NULL
ORDER by person_per.per_LastName;";

            $connection = Propel::getConnection();

            $statement = $connection->prepare($sSQL);
            $statement->execute();

            $prpPerson = $statement->fetchAll(\PDO::FETCH_ASSOC);// permet de récupérer le tableau associatif

            return $prpPerson;


        /* // Here's the first test to try to tranform the code to propel

            $res =  PersonQuery::create()
            ->filterByDateDeactivated(null) // GDPR, when a family is completely deactivated
            ->leftJoinFamily()
            ->addAsColumn('FamilyName', FamilyTableMap::COL_FAM_NAME )
            ->addAsColumn('FamilyId', FamilyTableMap::COL_FAM_ID )
            ->usePastoralCareRelatedByPersonIdQuery()
            ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
            ->endUse()
            ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
            ->addMultipleJoin(array(
                    array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                    array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                )
                , Criteria::LEFT_JOIN)
            ->addAsColumn('ClassName',  "COALESCE(" . ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONNAME) . ", 'Unassigned')" )
            ->addAsColumn('ClassID',    "COALESCE(" . ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID) . ", 'Unassigned')" )
            ->addAsColumn('ListID',     "COALESCE(" . ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID) . ", NULL)" )
            ->groupById();


        LoggerUtils::getAppLogger()->info($res->toString());

        return $res->find();*/

        } else {
            return PastoralCareQuery::create()
                ->joinPersonRelatedByPersonId()
                ->usePersonRelatedByPersonIdQuery()
                    ->filterByDateDeactivated(null)
                    ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
                    ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
                ->useFamilyQuery()
                    ->addAsColumn('FamilyName', FamilyTableMap::COL_FAM_NAME)
                    ->addAsColumn('FamilyId', FamilyTableMap::COL_FAM_ID)
                    ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                ->endUse()
                ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
                ->addMultipleJoin(array(
                        array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONID)),
                        array(ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_ID), 1)
                    )
                    , Criteria::LEFT_JOIN)
                ->addAsColumn('ClassName', "COALESCE(" . ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONNAME) . ", 'Unassigned')")
                ->addAsColumn('ClassID', "COALESCE(" . ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_OPTIONID) . ", 'Unassigned')")
                ->addAsColumn('ListID', "COALESCE(" . ListOptionTableMap::Alias("cls", ListOptionTableMap::COL_LST_ID) . ", NULL)")
                ->endUse()
                ->find()->toArray();
        }
    }

    public function getPersonNeverBeenContacted($realDate, $orderRand = false)
    {
        $persons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->usePastoralCareRelatedByPersonIdQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                ->filterByPersonId(NULL)
                ->_or()->filterByDate($realDate, Criteria::LESS_THAN)
            ->endUse()
            ->groupBy(PersonTableMap::COL_PER_ID);

            if ($orderRand) {
                $persons->addAscendingOrderByColumn('rand()');
                return $persons->findOne();
            } else {
                $persons->orderByLastName();
                return $persons->find();
            }
    }

    public function getRetiredNeverBeenContacted ($realDate, $orderRand = false)
    {
        $date = new \DateTime('now');

        $persons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->filterByBirthYear((int)$date->format('Y') - 60, Criteria::LESS_EQUAL)
            ->usePastoralCareRelatedByPersonIdQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                ->filterByPersonId(NULL)
                ->_or()->filterByDate($realDate, Criteria::LESS_THAN)
            ->endUse()
            ->groupBy(PersonTableMap::COL_PER_ID);

            if ($orderRand) {
                $persons->addAscendingOrderByColumn('rand()');
                return $persons->findOne();
            } else {
                $persons->orderByLastName();
                return $persons->find();
            }
    }

    public function getYoungNeverBeenContacted ($realDate, $orderRand = false)
    {
        $date = new \DateTime('now');

        $persons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->filterByBirthYear((int)$date->format('Y') - 18, Criteria::GREATER_EQUAL)
            ->usePastoralCareRelatedByPersonIdQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                ->filterByPersonId(NULL)
                ->_or()->filterByDate($realDate, Criteria::LESS_THAN)
            ->endUse()
            ->groupBy(PersonTableMap::COL_PER_ID);

            if ($orderRand) {
                $persons->addAscendingOrderByColumn('rand()');
                return $persons->findOne();
            } else {
                $persons->orderByLastName();
                return $persons->find();
            }
    }

    public function getAllFamiliesAndSingle ($orderRand = false)
    {

        $families = FamilyQuery::create()
            ->leftJoinPerson()
            ->usePersonQuery()
                ->filterByDateDeactivated( null)
            ->endUse()
            ->filterByDateDeactivated(null)
            ->usePastoralCareQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                //->filterByFamilyId(NULL)
            ->endUse()
            ->having( 'count('.PersonTableMap::COL_PER_ID.') <> 0' )
            ->groupBy(FamilyTableMap::COL_FAM_ID);

        if ($orderRand) {
            $families->addAscendingOrderByColumn('rand()');
            return $families->findOne();
        } else {
            $families->orderByName();
            return $families->find();
        }
    }

    public function getAllSingle ($orderRand = false)
    {
        $families = FamilyQuery::create()
            ->leftJoinPerson()
            ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->addAsColumn('PersonCount', 'count('.PersonTableMap::COL_PER_ID.')')
            ->endUse()
            ->filterByDateDeactivated(null)
            ->usePastoralCareQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
            ->endUse()
            ->having( 'count('.PersonTableMap::COL_PER_ID.') = 1' )
            ->groupBy(FamilyTableMap::COL_FAM_ID);

        if ($orderRand) {
            $families->addAscendingOrderByColumn('rand()');
            return $families->findOne();
        } else {
            $families->orderByName();
            return $families->find();
        }
    }

    public function getSingleNeverBeenContacted ($realDate, $orderRand = false)
    {
        $families = FamilyQuery::create()
            ->leftJoinPerson()
            ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->addAsColumn('PersonCount', 'count('.PersonTableMap::COL_PER_ID.')')
                ->addAsColumn('PersonID', PersonTableMap::COL_PER_ID )
                ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME )
                ->usePastoralCareRelatedByPersonIdQuery()
                    ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                    ->filterByPersonId(NULL)
                    ->_or()->filterByDate($realDate, Criteria::LESS_THAN)
                ->endUse()
            ->endUse()
            ->filterByDateDeactivated(null)
            ->having( 'count('.PersonTableMap::COL_PER_ID.') = 1' )
            ->groupBy(FamilyTableMap::COL_FAM_ID);

        if ($orderRand) {
            $families->addAscendingOrderByColumn('rand()');
            return $families->findOne();
        } else {
            $families->orderByName();
            return $families->find();
        }
    }


    public function getAllRealFamilies ($orderRand = false)
    {
        $families = FamilyQuery::create()
            ->leftJoinPerson()
            ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->addAsColumn('PersonCount', 'count('.PersonTableMap::COL_PER_ID.')')
            ->endUse()
            ->filterByDateDeactivated(null)
                ->usePastoralCareQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                //->filterByFamilyId(NULL)
            ->endUse()
            ->having( 'count('.PersonTableMap::COL_PER_ID.') > 1' )
            ->groupBy(FamilyTableMap::COL_FAM_ID);

        if ($orderRand) {
            $families->addAscendingOrderByColumn('rand()');
            return $families->findOne();
        } else {
            $families->orderByName();
            return $families->find();
        }
    }

    public function getFamiliesNeverBeenContacted ($realDate, $orderRand = false)
    {
       $families = FamilyQuery::create()
            ->leftJoinPerson()
            ->usePersonQuery()
                ->filterByDateDeactivated( null)
                ->addAsColumn('PersonCount', 'count('.PersonTableMap::COL_PER_ID.')')
            ->endUse()
            ->filterByDateDeactivated(null)
            ->usePastoralCareQuery()
                ->addAsColumn('PastoralCareLastDate', PastoralCareTableMap::COL_PST_CR_DATE )
                ->filterByFamilyId(NULL)
                ->_or()->filterByDate($realDate, Criteria::LESS_THAN)
            ->endUse()
            ->having( 'count('.PersonTableMap::COL_PER_ID.') > 1' )
            ->groupBy(FamilyTableMap::COL_FAM_ID);

        if ($orderRand) {
                $families->addAscendingOrderByColumn('rand()');
                return $families->findOne();
        } else {
                $families->orderByName();
                return $families->find();
        }
    }

    public function stats()
    {

        $range = $this->getRange();

// extract all the persons that were never been seen or before the date mentioned in the criteria

        $personsWithoutPastoralCare = $this->getPersonNeverBeenContacted($range['realDate']);

// extract all the families that were never been seen or before the date mentioned in the criteria
        $familiesWithoutPastoralCare = $this->getFamiliesNeverBeenContacted($range['realDate']);

// extract all the families that were never been seen or before the date mentioned in the criteria
        $singleWithoutPastoralCare = $this->getSingleNeverBeenContacted($range['realDate']);

        /*
         * stats about the persons families who were really contacted
         */

        $allPersons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->find();

        $allFamilies = $this->getAllRealFamilies();

        $allSingle = $this->getAllSingle();

        $percentViewPersons = $percentViewFamilies = 0;

        if ($allPersons->count() > 0) {
            $percentViewPersons = $personsWithoutPastoralCare->count() / $allPersons->count() * 100;
        }

        if ($allFamilies->count() > 0) {
            $percentViewFamilies = $familiesWithoutPastoralCare->count() / $allFamilies->count() * 100;
        }

        if ($allSingle->count() > 0) {
            $percentSinglePersons = $singleWithoutPastoralCare->count() / $allSingle->count() * 100;
        }

        /*
         * Stats about the retired persons
         */

        $date = new \DateTime('now');

// extract all the persons that were never been seen or before the date mentioned in the criteria
        $retiredPersonsWithoutPastoralCare = $this->getRetiredNeverBeenContacted($range['realDate']);

        $allRetiredPersons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->filterByBirthYear((int)$date->format('Y') - 60, Criteria::LESS_EQUAL)
            ->find();

        $percentRetiredViewPersons = 0;

        if ($allRetiredPersons->count() > 0) {
            $percentRetiredViewPersons = $retiredPersonsWithoutPastoralCare->count() / $allRetiredPersons->count() * 100;
        }

// extract the yong people before 18 years old
        $youngPersonsWithoutPastoralCare = $this->getYoungNeverBeenContacted($range['realDate']);

        $allYoungPersons = PersonQuery::create()
            ->filterByDateDeactivated(null)
            ->filterByBirthYear((int)$date->format('Y') - 18, Criteria::GREATER_EQUAL)
            ->find();

        $percentYoungViewPersons = 0;

        if ($allYoungPersons->count() > 0) {
            $percentYoungViewPersons = $youngPersonsWithoutPastoralCare->count() / $allYoungPersons->count() * 100;
        }


// the type of banner
        $pastoralcareAlertType = "alert-success";

// old alert style : alert-pastoral-care
        $retiredColor = 'success';
        if ((100.0 - $percentRetiredViewPersons) < 10.0) {
            $retiredColor = 'danger';
        } else if ((100.0 - $percentRetiredViewPersons) < 30.0) {
            $retiredColor = 'warning';
        } else if ((100.0 - $percentRetiredViewPersons) < 60.0) {
            $retiredColor = 'primary';
        }

        $familyColor = 'success';
        if ((100.0 - $percentViewFamilies) < 10.0) {
            $familyColor = 'danger';
        } else if ((100.0 - $percentViewFamilies) < 30.0) {
            $familyColor = 'warning';
        } else if ((100.0 - $percentViewFamilies) < 60.0) {
            $familyColor = 'primary';
        }

        $singleColor = 'success';
        if ((100.0 - $percentSinglePersons) < 10.0) {
            $singleColor = 'danger';
        } else if ((100.0 - $percentSinglePersons) < 30.0) {
            $singleColor = 'warning';
        } else if ((100.0 - $percentSinglePersons) < 60.0) {
            $singleColor = 'primary';
        }

        $personColor = 'success';
        if ((100.0 - $percentViewPersons) < 10.0) {
            $personColor = 'danger';
        } else if ((100.0 - $percentViewPersons) < 30.0) {
            $personColor = 'warning';
        } else if ((100.0 - $percentViewPersons) < 60.0) {
            $personColor = 'primary';
        }

        $youngColor = 'success';
        if ((100.0 - $percentYoungViewPersons) < 10.0) {
            $youngColor = 'danger';
        } else if ((100.0 - $percentYoungViewPersons) < 30.0) {
            $youngColor = 'warning';
        } else if ((100.0 - $percentYoungViewPersons) < 60.0) {
            $youngColor = 'primary';
        }

        // only retired, families and persons are concerned, the young people are only here to be showned
        if ((100.0 - $percentRetiredViewPersons) < 10.0 || (100.0 - $percentViewFamilies) < 10.0 || (100.0 - $percentViewPersons) < 10.0) {
            $pastoralcareAlertType = "alert-danger";
        } else if ((100.0 - $percentRetiredViewPersons) < 30.0 || (100.0 - $percentViewFamilies) < 30.0 || (100.0 - $percentViewFamilies) < 30.0) {
            $pastoralcareAlertType = "alert-warning";
        } else if ((100.0 - $percentRetiredViewPersons) < 60.0 || (100.0 - $percentViewFamilies) < 30.0 || (100.0 - $percentViewFamilies) < 60.0) {
            $pastoralcareAlertType = "alert-primary";
        }

        return ['startPeriod' => $range['startPeriod'],
            'endPeriod' => $range['endPeriod'],
            'CountNotViewPersons' => $personsWithoutPastoralCare->count(),
            'PercentNotViewPersons' => round($percentViewPersons,2),
            'personColor' => $personColor,
            'CountNotViewFamilies' => $familiesWithoutPastoralCare->count(),
            'PercentViewFamilies' => round($percentViewFamilies,2),
            'familyColor' => $familyColor,
            'PersonSingle' => $singleWithoutPastoralCare->count(),
            'PercentPersonSingle' => round($percentSinglePersons,2),
            'singleColor' => $singleColor,
            'CountNotViewRetired' => $retiredPersonsWithoutPastoralCare->count(),
            'PercentRetiredViewPersons' => round($percentRetiredViewPersons,2),
            'retiredColor' => $retiredColor,
            'CountNotViewYoung' => $youngPersonsWithoutPastoralCare->count(),
            'PercentViewYoung' => round($percentYoungViewPersons,2),
            'youngColor' => $youngColor,
            'PastoralcareAlertType' => $pastoralcareAlertType];
    }

    public function lastContactedPersons () {
        $caresPersons = PastoralCareQuery::Create()
            ->filterByPersonId(null, Criteria::NOT_EQUAL)
            ->leftJoinPastoralCareType()
            ->joinPersonRelatedByPersonId()
            ->groupBy(PastoralCareTableMap::COL_PST_CR_PERSON_ID)
            ->orderByDate(Criteria::DESC)
            ->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"))
            ->findByPastorId(SessionUser::getUser()->getPerson()->getId());

        return $caresPersons;
    }

    public function lastContactedFamilies () {
        $caresFamilies = PastoralCareQuery::Create()
            ->filterByFamilyId(null, Criteria::NOT_EQUAL)
            ->leftJoinPastoralCareType()
            ->joinWithFamily()
            ->groupBy(PastoralCareTableMap::COL_PST_CR_PERSON_ID)
            ->orderByDate(Criteria::DESC)
            ->limit(SystemConfig::getValue("iSearchIncludePastoralCareMax"))
            ->findByPastorId(SessionUser::getUser()->getPerson()->getId());

        return $caresFamilies;
    }
}
