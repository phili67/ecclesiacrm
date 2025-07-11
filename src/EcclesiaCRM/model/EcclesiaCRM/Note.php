<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\Note as BaseNote;
use EcclesiaCRM\NoteShareQuery;
use EcclesiaCRM\dto\SystemURLs;

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
    
    public function isShared ()
    {
      return !is_null(NoteShareQuery::Create()->findOneByNoteId($this->getId()))?1:0;
    }

    public function getEditLink()
    {
        $url = '<a href="#" data-id="' . $this->getId() . '" data-perid="';

        if ($this->getPerId() != '') {
            $url .= $this->getPerId().'" data-famid="0" class="editDocument btn btn-primary btn-sm"';
        } else {
            $url .= '0" data-famid="' . $this->getFamId() . '" class="editDocument btn btn-primary btn-sm"';
        }

        $url .= 'data-toggle="tooltip" data-placement="bottom" title="'. _("Edit this document") .'">';

        return $url;
    }

    public function getDeleteLink()
    {
        return '<a href="#" data-id="' . $this->getId() . '" data-perid="' . $this->getPerId() .'" 
            data-famid="' . $this->getFamId() . '" 
            data-toggle="tooltip" data-placement="bottom" title="'. _("Delete this document") .'"
            class="btn btn-danger btn-sm deleteDocument">';
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
