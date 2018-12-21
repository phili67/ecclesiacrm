<?php

namespace EcclesiaCRM\Emails;
use EcclesiaCRM\SessionUser;


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
        return gettext("A EcclesiaCRM account was created for you").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["password" => $this->password,
            "confirmSigner" => SessionUser::getUser()->getPerson()->getFullName(),
            "passwordText" => gettext('New Password')];
        return array_merge($parentTokens, $myTokens);
    }
}
