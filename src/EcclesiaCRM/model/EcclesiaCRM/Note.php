<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Note as BaseNote;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\dto\SystemConfig;

/**
 * Skeleton subclass for representing a row from the 'note_nte' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 */
class Note extends BaseNote
{
    public function setEntered($enteredBy)
    {
        $this->setDateEntered(new \DateTime());
        $this->setEnteredBy($enteredBy);
    }

    public function currentEditbyUserName() : ?string
    {       
        if ($this->getCurrentEditedBy() > 0) {
            $currentDate = new \DateTime();
            $since_start = $currentDate->diff($this->getCurrentEditedDate());
            $min = ($since_start->days * 24 * 60) + ($since_start->h * 60) + $since_start->i;
            $timeLeft = SystemConfig::getValue('iDocumentTimeLeft') - $min;

            if ($timeLeft > 0) {
                $editor = PersonQuery::create()->findPk($this->getCurrentEditedBy());
                if ($editor) {
                    return _( "This document is opened by" ) . " : " . $editor->getFullName() . " (" . $timeLeft . " " . _( "Minutes left" ) . ")";
                }
            } else {
                // reset the count
                $this->setCurrentEditedDate(null);
                $this->setCurrentEditedBy(0);
                $this->save();
            }
        }
        return "";
    }
    
    public function isShared ()
    {
      return !is_null(NoteShareQuery::Create()->findOneByNoteId($this->getId()))?1:0;
    }

    public function getEditLink()
    {
        if ($this->currentEditbyUserName() != "") {
            return '<button class="btn btn-outline-info btn-sm" disabled><i class="fas fa-lock"></i></button>';
        }

        $noteId = htmlspecialchars($this->getId(), ENT_QUOTES, 'UTF-8');
        $perId = ($this->getPerId() != '') ? htmlspecialchars($this->getPerId(), ENT_QUOTES, 'UTF-8') : '0';
        $famId = ($this->getPerId() != '') ? '0' : htmlspecialchars($this->getFamId(), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars(_("Edit this document"), ENT_QUOTES, 'UTF-8');

        $url = sprintf(
            '<button data-id="%s" data-perid="%s" data-famid="%s" class="editDocument btn btn-outline-primary btn-sm" data-toggle="tooltip" data-placement="bottom" title="%s"><i class="fas fa-edit"></i></button>',
            $noteId,
            $perId,
            $famId,
            $title
        );

        return $url;
    }

    public function getDeleteLink()
    {
        if ($this->currentEditbyUserName() != "") {
            return '<button class="btn btn-outline-danger btn-sm deleteDocument" disabled><i class="fas fa-lock"></i></button>';
        }

        $noteId = htmlspecialchars($this->getId(), ENT_QUOTES, 'UTF-8');
        $perId = htmlspecialchars($this->getPerId(), ENT_QUOTES, 'UTF-8');
        $famId = htmlspecialchars($this->getFamId(), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars(_("Delete this document"), ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<button data-id="%s" data-perid="%s" data-famid="%s" data-toggle="tooltip" data-placement="bottom" title="%s" class="btn btn-outline-danger btn-sm deleteDocument"><i class="fas fa-trash-alt"></i></button>',
            $noteId,
            $perId,
            $famId,
            $title
        );
    }

    public function getDisplayEditedDate($format = 'Y-m-d H:i:s')// you have to set the time to 0-23, if not all the time are set to AM.
    {
        if (!empty($this->getDateLastEdited())) {
            return $this->getDateLastEdited($format);
        } else {
            return $this->getDateEntered($format);
        }
    }

    public function getDisplayEditedBy()
    {
        if ($this->getEditedBy() != '') {
            return $this->getEditedBy();
        } else {
            return $this->getEnteredBy();
        }
    }

    public function isPrivate()
    {
        return $this->getPrivate() != '0';
    }

    public function isVisualableBy ($personId)
    {
        return !$this->isPrivate() || $this->getPerId() == $personId;
    }
}
