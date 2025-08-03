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
        return _("Your New Account");
    }

    protected function buildMessageBody()
    {
        return _("A CRM account was created for you").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["password" => $this->password,
            "confirmSigner" => SessionUser::getUser()->getPerson()->getFullName(),
            "passwordText" => _('New Password')];
        return array_merge($parentTokens, $myTokens);
    }
}
