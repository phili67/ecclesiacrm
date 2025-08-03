<?php

namespace EcclesiaCRM\Emails;


class AccountDeletedEmail extends BaseUserEmail
{

    protected function getSubSubject()
    {
        return gettext("Your Account was Deleted");
    }

    protected function buildMessageBody()
    {
        return gettext("Your CRM Account was Deleted.");
    }
}
