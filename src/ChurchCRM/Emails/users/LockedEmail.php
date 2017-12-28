<?php

namespace ChurchCRM\Emails;


class LockedEmail extends BaseUserEmail
{

    protected function getSubSubject()
    {
        return gettext("Account Locked");
    }

    protected function buildMessageBody()
    {
        return gettext("Your EcclesiaCRM2 account was locked.");
    }
}
