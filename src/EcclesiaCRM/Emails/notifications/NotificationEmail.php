<?php

namespace EcclesiaCRM\Emails;

use EcclesiaCRM\dto\SystemConfig;

class NotificationEmail extends BaseEmail
{
    private $notificationSource;
    
    public function __construct($toAddresses,$notificationSource)
    {
        $this->notificationSource = $notificationSource;
        parent::__construct($toAddresses);
        $this->mail->Subject = SystemConfig::$this->getSubSubject() . '<font color="gray">' . gettext(" from ") . getValue("sChurchName") . '</font><br>';
        $this->mail->isHTML(true);
        $this->mail->msgHTML($this->buildMessage());
    }

    protected function getSubSubject()
    {
        return gettext("Notification");
    }
   
     public function getTokens()
    {
        $myTokens =  [
            "toName" => gettext("Guardian(s) of") . " " . $this->notificationSource,
            "body" => gettext("A notification was triggered by the classroom teacher at") . " " . date('Y-m-d H:i:s') . " " . gettext("Please go to this location")
        ];
        return array_merge($this->getCommonTokens(), $myTokens);
    }
}
