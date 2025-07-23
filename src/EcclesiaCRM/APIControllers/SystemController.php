<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;


use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\SessionUser;

use PHPMailer\PHPMailer\PHPMailer;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\ChurchMetaData;


class SystemController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function cspReport (ServerRequest $request, Response $response, array $args): Response
    {
        $input = json_decode($request->getBody());
        $log  = json_encode($input, JSON_PRETTY_PRINT);

        $Logger = $this->container->get('Logger');
        $Logger->warn($log);

        return $response;
    }

    public function deleteFile (ServerRequest $request, Response $response, array $args): Response
    {
        $params = (object)$request->getParsedBody();

        if ( isset ($params->name) && isset($params->path) ) {
            if (unlink(SystemURLs::getDocumentRoot().$params->path.$params->name)) {
                return $response->withJson(['status' => "success"]);
            }
        }

        return $response->withJson(['status' => "failed"]);
    }

    // test connection
    public function testEmailConnectionMVC (ServerRequest $request, Response $response, array $args): Response
    {
        if (!(SessionUser::getUser()->isAdmin())) {
            return $response->withStatus(401);
        }

        $mailer = new PHPMailer();
        $message = "";
        if (!empty(SystemConfig::getValue("sSMTPHost")) && !empty(ChurchMetaData::getChurchEmail())) {
            $mailer->IsSMTP();
            $mailer->CharSet = 'UTF-8';
            $mailer->Timeout = intval(SystemConfig::getValue("iSMTPTimeout"));
            $res = explode(":", SystemConfig::getValue("sSMTPHost"));
            $mailer->Host = $res[0];
            $mailer->Port  = intval($res[1]);
            if (SystemConfig::getBooleanValue("bSMTPAuth")) {
                $mailer->SMTPAuth = true;
                $result = "<b>SMTP Auth Used</b></br></br>";
                $mailer->Username = SystemConfig::getValue("sSMTPUser");
                $mailer->Password = SystemConfig::getValue("sSMTPPass");
            }
            $mailer->SMTPDebug = 3;
            $mailer->Subject = "Test SMTP Email";
            $mailer->setFrom(ChurchMetaData::getChurchEmail());
            $mailer->addAddress(ChurchMetaData::getChurchEmail());
            $mailer->Body = "test email";
            $mailer->Debugoutput = "html";
        } else {
            $message = _("SMTP Host is not setup, please visit the settings page");
        }

        if (empty($message)) {
            ob_start();
            $mailer->send();
            $result .= ob_get_clean();
            ob_end_flush();
            return $response->withJson(['success' => true,"result" => $result]);
        } else {
            return $response->withJson(['success' => false,"error" => $message]);
        }
    }
}
