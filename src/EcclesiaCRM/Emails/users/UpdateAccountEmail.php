<?php

namespace EcclesiaCRM\Emails;


class UpdateAccountEmail extends BaseUserEmail
{

    protected $password;

    public function __construct($user, $password) {
        $this->password = $password;
        parent::__construct($user);
    }

    protected function getSubSubject()
    {
        return gettext("Your Account");
    }

    protected function buildMessageBody()
    {
        return gettext("Your EcclesiaCRM account was updated").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["password" => $this->password,
            "confirmSigner" => $_SESSION['user']->getPerson()->getFullName(),
            "passwordText" => gettext('New Password')];
        return array_merge($parentTokens, $myTokens);
    }
}
