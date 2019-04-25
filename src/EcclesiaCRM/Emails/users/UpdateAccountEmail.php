<?php

namespace EcclesiaCRM\Emails;
use EcclesiaCRM\SessionUser;


class UpdateAccountEmail extends BaseUserEmail
{
    protected $type; // 1 : password; 2: update account (activated deactivated)
    protected $password;

    public function __construct($user, $password,$type=1) {
        $this->password = $password;
        $this->type     = $type;
        parent::__construct($user);
    }

    protected function getSubSubject()
    {
        return _("Your Account");
    }

    protected function buildMessageBody()
    {
        return _("Your EcclesiaCRM account was updated").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["update" => $this->password,
            "confirmSigner" => SessionUser::getUser()->getPerson()->getFullName(),
            "updateText" => (($this->type == 1)?_('New Password'):_('Status'))];
        return array_merge($parentTokens, $myTokens);
    }
}
