<?php

/*******************************************************************************
 *
 *  filename    : Emails/verify/PersonVerifyEmail.php
 *  last change : 2024-01-31 Philippe Logel
 *  description : Create emails with all the confirmation letters asking member
 *                families to verify the information in the database.
 *
 *  Test : http://url/Reports/ConfirmReportEmail.php?familyId=274
 *
 ******************************************************************************/

namespace EcclesiaCRM\Emails;

use EcclesiaCRM\dto\SystemConfig;

class PersonVerificationEmail extends BaseEmail
{
    private $token;
    protected $firstName;
    protected $LastName;
    protected $logins;
    protected $password;

    public function __construct($emails, $firstName, $LastName, $token = "", $logins = [], $password = "")
    {
        $this->firstName = $firstName;
        $this->LastName = $LastName;
        $this->token = $token;
        $this->logins = $logins;
        $this->password = $password;

        parent::__construct($emails);

        $this->mail->Subject = _("Person"). " : ". $firstName . " ". $LastName . " (" . gettext("Please verify your family's information").")";
        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    public function getTokens()
    {
        $myTokens = ["toName" => $this->LastName . " " . $this->firstName,
            "verificationToken" => $this->token,
            "body" => SystemConfig::getValue("sConfirm1"),
            "login" => _("Login") .' : '. $this->logins[0],
            "password" => _("Password") .' : '. $this->password
        ];
        return array_merge($this->getCommonTokens(), $myTokens);
    }

}
