<?php

namespace EcclesiaCRM\Emails;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\User;


abstract class BaseUserEmail extends BaseEmail
{
    protected $user;

    /**
     * BaseUserEmail constructor.
     * @param $user User
     */
    public function __construct($user)
    {
        parent::__construct([$user->getEmail()]);
        $this->user = $user;
        $this->mail->Subject = SystemConfig::getValue("sEntityName") . ": " . $this->getSubSubject();
        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    public function getTokens()
    {
        $myTokens =  ["toName" => $this->user->getPerson()->getFirstName(),
            "userName" => $this->user->getUserName(),
            "userNameText" => _('Email/Username'),
            "body" => $this->buildMessageBody()
        ];
        return array_merge($this->getCommonTokens(), $myTokens);
    }

    protected abstract function buildMessageBody();
}
