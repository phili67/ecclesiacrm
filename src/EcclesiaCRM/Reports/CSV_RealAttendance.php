<?php
/*******************************************************************************
 *
 *  filename    : Reports/CSV_RealAttendance.php
 *  description : Creates a PDF for a Sunday School Class Attendance List
 *  Udpdated    : 2018-05-09
 *  This code is under copyright not under MIT Licence
 *  copyright   : 2018 Philippe Logel all right reserved not MIT licence
 *                This code can't be incoprorated in another software without authorizaion
 ******************************************************************************/

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\EventQuery;
use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\Record2propertyR2pQuery;
use EcclesiaCRM\PropertyQuery;
use EcclesiaCRM\Map\PersonTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Utils\InputUtils;

class CSV_RealAttendance
{
    protected $groupIDs;
    protected $withPictures;
    protected $iExtraStudents;
    protected $iFYID;
    protected $startDate;
    protected $endDate;

    // Constructor
    public function __construct($groupIDs, $withPictures, $iExtraStudents, $iFYID, $startDate, $endDate)
    {
        $this->groupIDs = $groupIDs;
        $this->withPictures = $withPictures;
        $this->iExtraStudents = $iExtraStudents;
        $this->iFYID = $iFYID;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function render()
    {
        // we will construct the labels

        $delimiter = SessionUser::getUser()->CSVExportDelemiter();
        $charset   = SessionUser::getUser()->CSVExportCharset();

        $eol = "\r\n";

        $labelArr = [];
        $labelArr['firstName'] = InputUtils::translate_special_charset("First Name");
        $labelArr['lastName'] = InputUtils::translate_special_charset("Last Name");
        $labelArr['birthDate'] = InputUtils::translate_special_charset("Birth Date");
        $labelArr['gender'] = InputUtils::translate_special_charset("Gender");
        $labelArr['age'] = InputUtils::translate_special_charset("Age");
        $labelArr['homePhone'] = InputUtils::translate_special_charset("Phone");
        $labelArr['groupName'] = InputUtils::translate_special_charset("Group");
        $labelArr['props'] = InputUtils::translate_special_charset("Notes");
        $labelArr['stats'] = InputUtils::translate_special_charset("Stats");

        $nbrGroup = count($this->groupIDs);

        foreach ($this->groupIDs as $iGroupID) {

            // we filter all the events which belongs to a group
            $activeEvents = EventQuery::Create()
                ->filterByGroupId($iGroupID)
                ->filterByInActive(1, Criteria::NOT_EQUAL)
                ->Where('event_start BETWEEN "' . $this->startDate . '" AND "' . $this->endDate . '"')// We filter only the events from the current month : date('Y')
                ->orderByStart()
                ->find();


            $date_count = 0;

            $buffer = _("Name").$delimiter._("First Name").$delimiter._("Phone").$delimiter._("Birthdate").$delimiter._("Age").$delimiter._("Props").$delimiter._("Stats");

            foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
                $date = OutputUtils::change_date_for_place_holder($activeEvent->getStart()->format("Y-m-d"));
                $labelArr['date' . $date_count++] = $date;
                $buffer .= $delimiter.$date;
            }

            $buffer .= $eol;

            //  uset($aStudents);
            //Get the data on this group
            $group = GroupQuery::Create()->findOneById($iGroupID);

            if (!is_null($group)) {
                $reportHeader = str_pad($group->getName(), 95) . $this->iFYID;
            }

            // Build the teacher string- first teachers, then the liaison
            $teacherString = _('Teachers') . ': ';
            $bFirstTeacher = true;
            $iTeacherCnt = 0;
            $iMaxTeachersFit = 4;
            $iStudentCnt = 0;

            $groupRoleMemberships = \EcclesiaCRM\Person2group2roleP2g2rQuery::create()
                ->joinWithPerson()
                ->orderBy(PersonTableMap::COL_PER_FIRSTNAME) // I've try to reproduce ORDER BY per_LastName, per_FirstName
                ->findByGroupId($iGroupID);

            $aStudents = [];
            $maxNbrEvents = 0;

            foreach ($groupRoleMemberships as $groupRoleMembership) {
                $lineRealPresence = 0;
                $lineDates = [];


                $person = $groupRoleMembership->getPerson();

                $family = $person->getFamily();

                $homePhone = "";
                if (!empty($family)) {
                    $homePhone = $family->getHomePhone();

                    if (empty($homePhone)) {
                        $homePhone = $family->getCellPhone();
                    }

                    if (empty($homePhone)) {
                        $homePhone = $family->getWorkPhone();
                    }
                }

                $groupRole = \EcclesiaCRM\ListOptionQuery::create()->filterById($group->getRoleListId())->filterByOptionId($groupRoleMembership->getRoleId())->findOne();
                $lst_OptionName = $groupRole->getOptionName();

                if ($lst_OptionName == 'Student') {// we will draw only the students
                    $assignedProperties = Record2propertyR2pQuery::Create()
                        ->findByR2pRecordId($person->getId());

                    $props = "";
                    foreach ($assignedProperties as $assproperty) {
                        $property = PropertyQuery::Create()->findOneByProId($assproperty->getR2pProId());
                        $props .= $property->getProName() . " ";
                    }

                    $buffer2 = "";

                    foreach ($activeEvents as $activeEvent) {// we loop in the events of the year
                        $eventAttendee = EventAttendQuery::create()
                            ->filterByPersonId($person->getId())
                            ->filterByEventId($activeEvent->getId())
                            ->findOne();

                        if (!is_null($eventAttendee) && !empty($eventAttendee->getCheckinDate())) {
                            $res = 1;
                            $lineRealPresence++;
                        } else {
                            $res = 0;
                        }

                        $buffer2 .= $res.$delimiter;
                    }

                    $buffer .= $person->getLastName().$delimiter;
                    $buffer .= $person->getFirstName().$delimiter;
                    $buffer .= $homePhone.$delimiter;
                    $buffer .= OutputUtils::FormatDate($person->getBirthDate()->format("Y-m-d")).$delimiter;
                    $buffer .= $person->getAge(false).$delimiter;
                    $buffer .= $props.$delimiter;
                    $buffer .= "\"".$lineRealPresence." "._("of")." ".($date_count)."\"".$delimiter;
                    $buffer .= $buffer2.$eol;



                }
            }

            // Export file
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-Type: text/csv;charset='.$charset);
            header('Content-Disposition: attachment; filename=EcclesiaCRM-'.$reportHeader.'.csv');
            header('Content-Transfer-Encoding: binary');

            if ($charset == "UTF-8") {
                echo "\xEF\xBB\xBF";
            }

            echo $buffer;
        }
    }
}
