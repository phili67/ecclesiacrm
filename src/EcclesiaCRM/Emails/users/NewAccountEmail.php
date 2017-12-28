<?php

namespace EcclesiaCRM\Emails;


class NewAccountEmail extends BaseUserEmail
{

    protected $password;

    public function __construct($user, $password) {
        $this->password = $password;
        parent::__construct($user);
    }

    protected function getSubSubject()
    {
        return gettext("Your New Account");
    }

    protected function buildMessageBody()
    {
        return gettext("A EcclesiaCRM2 account was created for you").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["password" => $this->password,
            "passwordText" => gettext('New Password')];
        return array_merge($parentTokens, $myTokens);
    }
}
