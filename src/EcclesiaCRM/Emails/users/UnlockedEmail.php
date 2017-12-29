<?php

namespace EcclesiaCRM\Emails;


class UnlockedEmail extends BaseUserEmail
{

    protected function getSubSubject()
    {
        return gettext("Account Unlocked");
    }

    protected function buildMessageBody()
    {
        return gettext("Your EcclesiaCRM account was unlocked.");
    }
}
