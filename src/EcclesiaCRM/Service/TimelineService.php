<?php

namespace EcclesiaCRM\Service;

use EcclesiaCRM\EventAttendQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use \Datetime;

require_once 'Include/Functions.php';

class TimelineService
{
    /* @var $currentUser \EcclesiaCRM\User */
    private $currentUser;

    public function __construct()
    {
        $this->currentUser = $_SESSION['user'];
    }

    public function getForFamily($familyID)
    {
        $timeline = [];
        $familyNotes = NoteQuery::create()->findByFamId($familyID);
        foreach ($familyNotes as $dbNote) {
            $item = $this->noteToTimelineItem($dbNote);
            if (!is_null($item)) {
                $timeline[$item['key']] = $item;
            }
        }

        krsort($timeline);

        $sortedTimeline = [];
        foreach ($timeline as $date => $item) {
            array_push($sortedTimeline, $item);
        }

        return $sortedTimeline;
    }
    
    private function notesForFamily($familyID, $noteTypes=null)
    {
        $firstTime = true;
        
        $timeline = [];
        $familyQuery = NoteQuery::create()
            ->filterByFamId($familyID);
            
        if ($noteTypes != null) {
          foreach ($noteTypes as $noteType) {
            
            if ($firstTime) {
               $familyQuery->filterByType($noteType);
            } else {               
               $familyQuery->_or()->filterByType($noteType);
            }
            
            $firstTime = false;
          }
        }
        foreach ($familyQuery->find() as $dbNote) {
            $item = $this->noteToTimelineItem($dbNote);
            if (!is_null($item)) {
                $timeline[$item['key']] = $item;
            }
        }

        return $timeline;
    }
    
    public function getNotesForFamily($familyID)
    {
        $timeline = $this->notesForFamily($familyID, ['note','video']);

        return $this->sortTimeline($timeline);
    }



    private function eventsForPerson($personID)
    {
        $timeline = [];
        $eventsByPerson = EventAttendQuery::create()->findByPersonId($personID);
        foreach ($eventsByPerson as $personEvent) {
            $event = $personEvent->getEvent();
            if ($event != null) {
                $item = $this->createTimeLineItem($event->getId(), 'cal',
                    $event->getStart('Y-m-d h:i:s'),
                    $event->getTitle(), '',
                    $event->getDesc(), '', '');
                $timeline[$item['key']] = $item;
            }
        }
        return $timeline;
    }

    private function notesForPerson($personID, $noteTypes=null)
    {
        $firstTime = true;
        
        $timeline = [];
        $personQuery = NoteQuery::create()
            ->filterByPerId($personID);
            
        if ($noteTypes != null) {
          foreach ($noteTypes as $noteType) {
            if ($firstTime) {            
               $personQuery->filterByType($noteType);
            } else {
               $personQuery->_or()->filterByType($noteType);
            }
            
            $firstTime = false;
          }
        }
        
        foreach ($personQuery->find() as $dbNote) {
            $item = $this->noteToTimelineItem($dbNote);
            if (!is_null($item)) {
                $timeline[$item['key']] = $item;
            }
        }
        
        $personShareQuery = NoteShareQuery::create()
            ->joinWithNote()
            ->findBySharePerId($personID);
            
        foreach ($personShareQuery as $dbNoteShare) {
          if (in_array($dbNoteShare->getNote()->getType(), $noteTypes)) {
             $item = $this->noteToTimelineItem($dbNoteShare->getNote(),$dbNoteShare->getNote()->getPerson(),$dbNoteShare->getRights(),$dbNoteShare->getEditLink());
             if (!is_null($item)) {
                 $timeline[$item['key']] = $item;
             }
          }
        }
        

        return $timeline;
    }
    
    private function sortTimeline($timeline)
    {
        krsort($timeline);

        $sortedTimeline = [];
        foreach ($timeline as $date => $item) {
            {
            array_push($sortedTimeline, $item);
           }
        }

        return $sortedTimeline;
    }
    

    public function getNotesForPerson($personID)
    {
        $timeline = $this->notesForPerson($personID, ['note','video','file']);

        return $this->sortTimeline($timeline);
    }

    public function getForPerson($personID)
    {
        $timeline = array_merge(
            $this->notesForPerson($personID, null),
            $this->eventsForPerson($personID)
        );

        return $this->sortTimeline($timeline);
    }

    /**
     * @param $dbNote Note
     *
     * @return mixed|null
     */
    public function noteToTimelineItem($dbNote,$sharePerson=null,$shareRights = 0,$shareEditLink = null)
    {
        $item = null;
        if ($this->currentUser->isAdmin() || $dbNote->isVisable($this->currentUser->getPersonId())) {
            $displayEditedBy = gettext('Unknown');
            if ($dbNote->getDisplayEditedBy() == Person::SELF_REGISTER) {
                $displayEditedBy = gettext('Self Registration');
            } else if ($dbNote->getDisplayEditedBy() == Person::SELF_VERIFY) {
                $displayEditedBy = gettext('Self Verification');
            } else {
                $editor = PersonQuery::create()->findPk($dbNote->getDisplayEditedBy());
                if ($editor != null) {
                    $displayEditedBy = $editor->getFullName();
                }
            }
            
            if ($dbNote->getType() == 'file' && empty($dbNote->getText()) ) {
              $title = $dbNote->getText();
            } else {
              $title = $dbNote->getTitle();
            }
            
            if ($dbNote->getCurrentEditedBy() > 0) {
              $currentDate = new DateTime();
            
              $min = $currentDate->diff($dbNote->getCurrentEditedDate())->format('%i');
              
              if ( $min < SystemConfig::getValue('iDocumentTimeLeft') ) {
                $editor = PersonQuery::create()->findPk($dbNote->getCurrentEditedBy());
                if ($editor != null) {
                    $currentUserName = gettext("This document is opened by")." : ".$editor->getFullName()." (".(SystemConfig::getValue('iDocumentTimeLeft')-$min)." ".gettext("Minutes left").")";
                }
              }
            }
            
            $item = $this->createTimeLineItem($dbNote->getId(), $dbNote->getType(), $dbNote->getDisplayEditedDate(),
                $dbNote->getDisplayEditedDate("Y"),$title.((!empty($title))?" : ":"").gettext('by') . ' ' . $displayEditedBy, '', $dbNote->getText(),
                (!is_null($shareEditLink)?$shareEditLink:$dbNote->getEditLink()), $dbNote->getDeleteLink(),$dbNote->getInfo(),$dbNote->isShared(),
                $sharePerson,$shareRights,$currentUserName);
        }

        return $item;
    }

    public function createTimeLineItem($id, $type, $datetime, $year, $header, $headerLink, $text, $editLink = '', $deleteLink = '',$info = '',$isShared = 0,$sharePerson = null, $shareRights = 0,$currentUserName = null)
    {
        $item['id'] = $id;
        $item['slim'] = false;
        $item['type'] = $type;
        $item['isShared'] = $isShared;
        
        switch ($type) {
            case 'create':
                $item['style'] = 'fa-plus-circle bg-blue';
                break;
            case 'edit':
                $item['style'] = 'fa-pencil bg-blue';
                break;
            case 'photo':
                $item['style'] = 'fa-camera bg-green';
                break;
            case 'video':
                $item['slim'] = true;
                $item['style'] = 'fa-video-camera bg-maroon';
                $item['editLink'] = $editLink;
                $item['deleteLink'] = $deleteLink;
                $item['currentUserName'] = $currentUserName;
                break;
            case 'file':
                $item['slim'] = true;
                $item['style'] = ' fa-file-o bg-aqua';
                $item['id'] = $id;
                $item['editLink'] = $editLink;
                $item['deleteLink'] = $deleteLink;
                $item['currentUserName'] = $currentUserName;                
                break;
            case 'group':
                $item['style'] = 'fa-users bg-gray';
                break;
            case 'cal':
                $item['style'] = 'fa-calendar bg-green';
                break;
            case 'verify':
                $item['style'] = 'fa-check-circle-o bg-teal';
                break;
            case 'verify-link':
                $item['style'] = 'fa-check-circle-o bg-teal';
                break;
            case 'user':
                $item['style'] = 'fa-user-secret bg-gray';
                break;
            default:
                $item['slim'] = true;
                $item['style'] = 'fa-sticky-note bg-green';
                $item['editLink'] = $editLink;
                $item['deleteLink'] = $deleteLink;
                $item['currentUserName'] = $currentUserName;
        }
        $item['header'] = $header;
        $item['headerLink'] = $headerLink;
        
        if (!is_null($sharePerson)) {
          $item['sharePersonName'] = $sharePerson->getFullName();
          $item['sharePersonID'] = $sharePerson->getId();
          $item['shareRights'] = $shareRights;
          $item['headerLink'] = '';
          $item['header'] = gettext("Shared by") . ' : ' . $sharePerson->getFullName();
          
          $item['deleteLink'] = '';
          
          if ($shareRights != 2) {
            $item['editLink'] = '';
          }
          
          $item['style2'] = $item['style'];
          $item['style'] = 'fa-share-square-o bg-purple';
        }
        
        if ($info) {
          $item['info'] = $info;
        }
        
        $item['text'] = $text;

        $item['datetime'] = OutputUtils::FormatDate($datetime,true);
        $item['year'] = $year;
        $item['key'] = $datetime.'-'.$id;
        
        return $item;
    }
}
