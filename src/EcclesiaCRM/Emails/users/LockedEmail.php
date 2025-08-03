<?php

namespace EcclesiaCRM\Emails;


class LockedEmail extends BaseUserEmail
{

    protected function getSubSubject()
    {
        return gettext("Account Locked");
    }

    protected function buildMessageBody()
    {
        return gettext("Your CRM account was locked.");
    }
}
