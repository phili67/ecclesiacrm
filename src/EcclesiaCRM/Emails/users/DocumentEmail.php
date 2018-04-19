<?php

namespace EcclesiaCRM\Emails;


class DocumentEmail extends BaseUserEmail
{

    protected $message;

    public function __construct($user, $message) {
        $this->message = $message;
        parent::__construct($user);
    }

    protected function getSubSubject()
    {
        return gettext("A document is shared with you");
    }

    protected function buildMessageBody()
    {
        return gettext("A user has share a document with you in")." EcclesiaCRM :";
    }

    public function getTokens()
    {
        $parentTokens = parent::getTokens();
        $myTokens = ["password" => $this->message,
            "passwordText" => gettext('Content')];
        return array_merge($parentTokens, $myTokens);
    }
}
