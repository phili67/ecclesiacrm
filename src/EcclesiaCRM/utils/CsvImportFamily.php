<?php

namespace EcclesiaCRM\Utils\CSVImport;

class FamilyImportUtils
{
    public $Members;       // array for member data
    public $MemberCount;   // obious
    public $WeddingDate;   // one per family
    public $Phone;         // one per family
    public $Envelope;      // one per family
    public $_nAdultMale;   // if one adult male
    public $_nAdultFemale; // and 1 adult female we assume spouses
    public $_type;         // 0=patriarch, 1=martriarch

    public function __construct()
    {
    }

    // constructor, initialize variables
    public function Family($famtype)
    {
        $this->_type = $famtype;
        $this->MemberCount = 0;
        $this->Envelope = 0;
        $this->_nAdultMale = 0;
        $this->_nAdultFemale = 0;
        $this->Members = [];
        $this->WeddingDate = '';
        $this->Phone = '';
    }

    /** Add what we need to know about members for role assignment later **/
    public function AddMember($PersonID, $Gender, $Age, $Wedding, $Phone, $Envelope)
    {
        // add member with un-assigned role
        $this->Members[] = ['personid'     => $PersonID,
                                 'age'     => $Age,
                                 'gender'  => $Gender,
                                 'role'    => 0,
                                 'phone'   => $Phone,
                                 'envelope'=> $Envelope, ];

        if ($Wedding != '') {
            $this->WeddingDate = $Wedding;
        }
        if ($Envelope != 0) {
            $this->Envelope = $Envelope;
        }
        $this->MemberCount++;
        if ($Age > 18) {
            $Gender == 1 ? $this->_nAdultMale++ : $this->_nAdultFemale++;
        }
    }

    /** Assigning of roles to be called after all members added **/
    public function AssignRoles()
    {
        // only one meber, must be "head"
        if ($this->MemberCount == 1) {
            $this->Members[0]['role'] = 1;
            $this->Phone = $this->Members[0]['phone'];
        } else {
            for ($m = 0; $m < $this->MemberCount; $m++) {
                if ($this->Members[$m]['age'] >= 0) { // -1 if unknown age
                    // child
                    if ($this->Members[$m]['age'] <= 18) {
                        $this->Members[$m]['role'] = 3;
                    } else {
                        // if one adult male and 1 adult female we assume spouses
                        if ($this->_nAdultMale == 1 && $this->_nAdultFemale == 1) {
                            // find head / spouse
                            if (($this->Members[$m]['gender'] == 1 && $this->_type == 0) || ($this->Members[$m]['gender'] == 2 && $this->_type == 1)) {
                                $this->Members[$m]['role'] = 1;
                                if ($this->Members[$m]['phone'] != '') {
                                    $this->Phone = $this->Members[$m]['phone'];
                                }
                            } else {
                                $this->Members[$m]['role'] = 2;
                            }
                        }
                    }
                }
            }
        }
    }
}