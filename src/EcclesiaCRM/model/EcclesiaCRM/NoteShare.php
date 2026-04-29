<?php

namespace EcclesiaCRM;

use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Base\NoteShare as BaseNoteShare;

/**
 * Skeleton subclass for representing a row from the 'note_nte_share' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class NoteShare extends BaseNoteShare
{
    public function getEditLink()
    {        
        if ($this->getNote()->currentEditbyUserName() != "") {
            return '<button class="btn btn-outline-info btn-sm" disabled><i class="fas fa-lock"></i></button>';
        }

        $noteId = htmlspecialchars($this->getNote()->getId(), ENT_QUOTES, 'UTF-8');
        $perId = !is_null($this->getSharePerId()) ? htmlspecialchars($this->getSharePerId(), ENT_QUOTES, 'UTF-8') : '0';
        $famId = !is_null($this->getSharePerId()) ? '0' : htmlspecialchars($this->getShareFamId(), ENT_QUOTES, 'UTF-8');

        $url = sprintf(
            '<button href="#" onclick="return false;" data-id="%s" data-perid="%s" data-famid="%s" class="editDocument btn btn-outline-info btn-sm"><i class="fas fa-edit"></i></button>',
            $noteId,
            $perId,
            $famId
        );

        return $url;
    }
    
    public function setNoteId($v)
    {
      $note = NoteQuery::create()->findOneById($v);
      
      $note->setDateLastEdited(new \DateTime()); 
      $note->save();
      
      parent::setNoteId($v);
    }

}
