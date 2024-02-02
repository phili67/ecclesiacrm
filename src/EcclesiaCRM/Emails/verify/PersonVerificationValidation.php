<?php

/*******************************************************************************
 *
 *  filename    : Emails/verify/PersonVerificationValidation.php
 *  last change : 2024-01-31 Philippe Logel
 *  description : Create emails with all the confirmation letters asking member
 *                families to verify the information in the database.
 *
 ******************************************************************************/

namespace EcclesiaCRM\Emails;

class PersonVerificationValidation extends BaseEmail
{
    private $token;
    protected $person;
    protected $message;
    protected $personId;

    public function __construct($emails, $person, $token = "", $message = "", $personId=0)
    {
        $this->person = $person;
        $this->token = $token;
        $this->message = $message;
        $this->personId = $personId;

        parent::__construct($emails);

        $this->mail->Subject = _("Person"). " : ". $person . " (" . gettext("informations").")";
        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    public function getTokens()
    {
        $myTokens = ["toName" => $this->person . " " . gettext("Person"),
            "personVerificationValidation" => $this->token,
            "personId" => $this->personId,
            "body" => $this->message,
        ];
        return array_merge($this->getCommonTokens(), $myTokens);
    }

}
