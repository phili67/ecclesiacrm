<?php

namespace EcclesiaCRM\Emails;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

use EcclesiaCRM\Bootstrapper;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\ChurchMetaData;
use EcclesiaCRM\Service\SystemService;

use PHPMailer\PHPMailer\PHPMailer;
use Monolog\Logger;

abstract class BaseEmail
{
    /** @var \PHPMailer */
    protected $mail;
    protected $mustache;
    protected $isActiv;// now only a user who has given authorization can receive email notifications

    public function __construct($toAddresses, $baseDirectory = '/views/email')
    {
        $this->isActiv = true;
        $this->setConnection();
        $this->mail->setFrom(ChurchMetaData::getChurchEmail(), ChurchMetaData::getChurchName());
        foreach ($toAddresses as $email) {
            $this->mail->addAddress($email);
        }

        // use .html instead of .mustache for default template extension
        $options = array('extension' => '.html');

        $this->mustache = new Mustache_Engine(array(
            'loader' => new Mustache_Loader_FilesystemLoader(SystemURLs::getDocumentRoot() . $baseDirectory, $options),
        ));
    }

    private function setSubject($text)
    {
        $this->mail->Subject = $text;
    }

    private function setConnection()
    {

        $this->mail = new PHPMailer();
        $this->mail->IsSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Timeout = intval(SystemConfig::getValue("iSMTPTimeout"));
        $res = explode(":", SystemConfig::getValue("sSMTPHost"));
        $this->mail->Host = $res[0];
        $this->mail->Port  = intval($res[1]);
        $this->mail->SMTPAutoTLS = SystemConfig::getBooleanValue("bPHPMailerAutoTLS");
        $this->mail->SMTPSecure = SystemConfig::getValue("sPHPMailerSMTPSecure");
        if (SystemConfig::getBooleanValue("bSMTPAuth")) {
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SystemConfig::getValue("sSMTPUser");
            $this->mail->Password = SystemConfig::getValue("sSMTPPass");
        }
        if (SystemConfig::getValue("sLogLevel") == Logger::DEBUG) {
            $this->mail->SMTPDebug = 1;
            $this->mail->Debugoutput = "error_log";
        }
    }

    public function send()
    {
        if (SystemConfig::hasValidMailServerSettings() && $this->isActiv) {// $this->isActiv : now only a user who has given authorization can receive email notifications
            return $this->mail->send();
        }
        return false; // we don't have a valid setting so let us make sure we don't crash.

    }

    public function getError()
    {
        return $this->mail->ErrorInfo;
    }

    public function addStringAttachment($string, $filename)
    {
        $this->mail->addStringAttachment($string, $filename);
    }

    protected function buildMessage()
    {
        return $this->mustache->render($this->getMustacheTemplateName(), $this->getTokens());
    }

    protected function getMustacheTemplateName()
    {
        return "BaseEmail";
    }

    protected function getCommonTokens() {
        return [
            "toEmails" => $this->mail->getToAddresses(),
            "churchName" => ChurchMetaData::getChurchName(),
            "churchAddress" => ChurchMetaData::getChurchFullAddress(),
            "churchPhone" => ChurchMetaData::getChurchPhone(),
            "churchEmail" => ChurchMetaData::getChurchEmail(),
            "ecclesiaCRMURL" => SystemURLs::getURL(),
            "dear" => SystemConfig::getValue('sDear'),
            "confirmSincerely" => SystemConfig::getValue('sConfirmSincerely'),
            "confirmSigner" => SystemConfig::getValue('sConfirmSigner'),
            "unsubscribeStart" => SystemConfig::getValue('sUnsubscribeStart'),
            "unsubscribeEnd" => SystemConfig::getValue('sUnsubscribeEnd'),
            "SoftwareName" => Bootstrapper::getSoftwareName(),
            "currentYear" => SystemService::getCopyrightDate()
        ];
    }

    abstract function getTokens();
}
