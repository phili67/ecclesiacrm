<?php

namespace EcclesiaCRM;

use EcclesiaCRM\Base\NoteShare as BaseNoteShare;
use EcclesiaCRM\dto\SystemURLs;

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
        $url = SystemURLs::getRootPath().'/NoteEditor.php?NoteID='.$this->getNote()->getId().'&';
        
        if ($this->getSharePerId() != '') {
            $url = $url.'PersonID='.$this->getSharePerId();
        } else {
            $url = $url.'FamilyID='.$this->getShareFamId();
        }

        return $url;
    }

}
