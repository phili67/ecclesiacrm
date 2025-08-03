<?php

namespace EcclesiaCRM\Emails;
use EcclesiaCRM\SessionUser;


class UpdateAccountEmail extends BaseUserEmail
{
    protected $update;

    public function __construct($user, $update) {
        $this->update = $update;
        parent::__construct($user);
    }

    protected function getSubSubject()
    {
        return _("Your Account");
    }

    protected function buildMessageBody()
    {
        return _("Your CRM account was updated").":";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["update" => $this->update,
            "confirmSigner" => SessionUser::getUser()->getPerson()->getFullName(),
            "updateText" => _('Status')];
        return array_merge($parentTokens, $myTokens);
    }
}
