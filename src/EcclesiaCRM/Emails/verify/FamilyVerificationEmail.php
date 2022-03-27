<?php

namespace EcclesiaCRM\Emails;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Utils\LoggerUtils;

class FamilyVerificationEmail extends BaseEmail
{
    private $token;
    protected $familyName;
    protected $login;
    protected $password;

    public function __construct($emails, $familyName, $token = "", $login = "",$password = "" )
    {
        $this->familyName = $familyName;
        $this->token = $token;
        $this->mail->Subject = $familyName . ": " . gettext("Please verify your family's information");
        $this->login = $login;
        $this->password = $password;

        parent::__construct($emails);

        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    public function getTokens()
    {
        $myTokens = ["toName" => $this->familyName . " " . gettext("Family"),
            "verificationToken" => $this->token,
            "body" => SystemConfig::getValue("sConfirm1"),
            "login" => _("Login") .' : '. $this->login,
            "password" => _("Password") .' : '. $this->password
        ];
        return array_merge($this->getCommonTokens(), $myTokens);
    }

}
