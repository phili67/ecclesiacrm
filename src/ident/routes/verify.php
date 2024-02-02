<?php

/*******************************************************************************
 *
 *  filename    : verify.php
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2024 Philippe Logel
 *
 ******************************************************************************/

use Slim\Http\Response;
use Slim\Http\ServerRequest;

use Slim\Routing\RouteCollectorProxy;

use Slim\Views\PhpRenderer;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\Person;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\TokenPasswordQuery;
use EcclesiaCRM\PersonCustomMasterQuery;

use EcclesiaCRM\Emails\FamilyVerificationValidation;
use EcclesiaCRM\Emails\PersonVerificationValidation;

use EcclesiaCRM\Service\ConfirmReportService;
use EcclesiaCRM\Utils\InputUtils;
use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;

use Propel\Runtime\Propel;

use EcclesiaCRM\UserQuery;

$app->group('/my-profile', function (RouteCollectorProxy $group) {

    $group->get('/{token}', function (ServerRequest $request, Response $response, array $args) {
        $renderer = new PhpRenderer("templates/verify/");
        $token = TokenQuery::create()->findPk($args['token']);

        session_destroy();

        $haveFamily = false;
        $havePerson = false;
        $loginWindow = false;

        if ( $token != null && $token->isVerifyFamilyToken() && $token->isValid() ) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            $haveFamily = ($family != null);
            if ($token->getRemainingUses() > 0) {
                $token->setRemainingUses($token->getRemainingUses() - 1);
                $token->save();
            }

            if ($family->getDateDeactivated() != null) {
                return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
            }
        } else if ( $token != null && $token->isVerifyPersonToken() && $token->isValid() ) {
            $person = PersonQuery::create()->findPk($token->getReferenceId());
            $havePerson = ($person != null);
            if ($token->getRemainingUses() > 0) {
                $token->setRemainingUses($token->getRemainingUses() - 1);
                $token->save();
            }

            if ($person->getDateDeactivated() != null) {
                return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
            }
        }

        if ($loginWindow == false && ($haveFamily == true || $havePerson)) {
            return $renderer->render($response, "login-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } elseif ($loginWindow == false && $haveFamily == true) {
            return $renderer->render($response, "login-info.php", array("person" => $person, "token" => $token, "realToken" => $args['token']));
        } elseif ($haveFamily) {
            return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } elseif ($havePerson) {
            return $renderer->render($response, "verify-person-info.php", array("person" => $person, "token" => $token, "realToken" => $args['token']));
        } else {
            return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
        }
    });

    $group->post('/{token}', function (ServerRequest $request, Response $response, array $args) {

        $token = TokenQuery::create()->findPk($args['token']);

        $realToken = $args['token'];

        if ( isset($_POST['User']) && isset($_POST['Password'])
            or isset($_SESSION['username']) && isset($_SESSION['password']) ) {
            // post data from : login-info.php
            $renderer = new PhpRenderer("templates/verify/");

            $tokenPassword = TokenPasswordQuery::create()->findOneByTokenId($args['token']);

            if (is_null($tokenPassword)) {
                return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
            }

            if ($token->isVerifyFamilyToken()) {
                $family = FamilyQuery::create()->findPk($token->getReferenceId());

                $emails = [$family->getEmail()];
                $emails = array_merge($emails, $family->getEmails());
            } elseif ($token->isVerifyPersonToken()) {
                $person = PersonQuery::create()->findPk($token->getReferenceId());
                $emails = [$person->getEmail()];
            }


            if ( !( in_array($_POST['User'], $emails) and md5($_POST['Password']) == $tokenPassword->getPassword()
                or in_array($_SESSION['username'], $emails) and md5($_SESSION['password']) == $tokenPassword->getPassword() ) ) {
                session_destroy();
                return $renderer->render($response, "login-info.php", array("family" => $family, "token" => $token,
                    "realToken" => $realToken, "sErrorText" => _("Wrong email or password")));
            }

            // session can now start
            if ( !isset($_SESSION['username']) || !isset($_SESSION['password']) ) {
                session_start();
                $_SESSION['username'] = $_POST['User'];
                $_SESSION['password'] = $_POST['Password'];
                $_SESSION['realToken'] = $realToken;
            }

            if ( isset($_POST['oldPassword']) && isset($_POST['newPassword']) && isset($_POST['confirmPassword']) ) {

                if ( md5($_POST['oldPassword']) != $tokenPassword->getPassword() ) {
                    return $renderer->render($response, "change-password.php", array( "token" => $token,
                        "realToken" => $realToken, "sErrorText" => _("Wrong old password")) );
                }

                if ( $_POST['newPassword'] != $_POST['confirmPassword'] ) {
                    return $renderer->render($response, "change-password.php", array( "token" => $token,
                        "realToken" => $realToken, "sErrorText" => _("The two passwords must be identical.")) );
                }

                // now we get the ip address to retrieve the real person who logged in
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $ip = $_SERVER['REMOTE_ADDR'];
                }

                // now everything is done
                $tokenPassword->setMustChangePwd(false);
                $tokenPassword->setPassword(md5($_POST['newPassword']));
                $tokenPassword->setIPAddress($ip);
                $tokenPassword->save();

                $_SESSION['password'] = $_POST['newPassword'];
            }

            if ( $tokenPassword->getMustChangePwd() ) {
                return $renderer->render($response, "change-password.php", array("message" => gettext("Unable to load verification info")));
            }

            if ($token->isVerifyFamilyToken()) {
                return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token, "realToken" => $realToken));
            } else if ($token->isVerifyPersonToken()) {
                return $renderer->render($response, "verify-person-info.php", array("person" => $person, "token" => $token, "realToken" => $realToken));
            }
        }
        return $response->withStatus(200);
    });

    $group->post('/getPersonInfo/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->token) ) {

            $token = TokenQuery::create()->findPk($input->token);
            if (!($token != null && ($token->isVerifyFamilyToken() || $token->isVerifyPersonToken()) && $token->isValid())) {
                return $response->withStatus(200);
            }

            if (isset ($input->personId)) {
                $person = PersonQuery::create()->findOneById($input->personId);

                $code = ConfirmReportService::getPersonStandardTextFields($person);
                $codeCustom = ConfirmReportService::getPersonCustomTextFields($person);

                return $response->withJson(["Status" => "success", "html" => $code, "htmlCustom" => $codeCustom[1], "fields" => $codeCustom[0]]);
            }
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/getFamilyInfo/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->token) ) {

            $token = TokenQuery::create()->findPk($input->token);
            if (!($token != null && $token->isVerifyFamilyToken() && $token->isValid())) {
                return $response->withStatus(200);
            }

            if (isset ($input->familyId)) {
                $family = FamilyQuery::create()->findOneById($input->familyId);

                $code = ConfirmReportService::getFamilyFullTextFields($family);

                return $response->withJson(["Status" => "success", "html" => $code]);
            }
        }

        return $response->withStatus(200);
    });

    $group->post('/exitSession/', function (ServerRequest $request, Response $response, array $args) {
        session_destroy();

        return $response->withJson(["Status" => "success"]);
    });

    $group->post('/deletePerson/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personId) ) {

            $person = PersonQuery::create()->findOneById($input->personId);

            if (!is_null($person)) {
                $user = UserQuery::create()
                    ->findOneByPersonId($input->personId);

                // note : When a user is deactivated the associated person is deactivated too
                //        but when a person is deactivated the user is deactivated too.
                //        Important : a person re-activated don't reactivate the user

                if (!is_null($user)) {
                    $user->setIsDeactivated(true);
                    $user->save();
                }

                $person->setDateDeactivated(date('YmdHis'));
                $person->save();

                // Create a note to record the status change
                //Create a note to record the status change
                $note = new Note();
                $note->setPerId($person->getId());
                $note->setText(_('Account Deactivated'));
                $note->setType('edit');
                $note->setEntered($person->getId());
                $note->save();
            }

            return $response->withJson(["Status" => "success"]);
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/deleteFamily/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->familyId) ) {

            $family = FamilyQuery::create()->findOneById($input->familyId);

            if (!is_null($family)) {
                $family->setDateDeactivated(date('YmdHis'));
                $family->save();

                //Create a note to record the status change
                $persons = $family->getPeople();

                // all person from the family should be deactivated too
                // one person of the family deactivate the other !!!
                $id = 0;
                foreach ($persons as $person) {
                    $user = UserQuery::create()
                        ->findOneByPersonId($person->getId());

                    // note : When a user is deactivated the associated person is deactivated too
                    //        but when a person is deactivated the user is deactivated too.
                    //        Important : a person re-activated don't reactivate the user

                    if (!is_null($user)) {
                        $user->setIsDeactivated(true);
                        $user->save();
                    }
                    if ($person->getDateDeactivated() == NULL) {
                        $id = $person->getId();
                        break;
                    }
                }
                $note = new Note();
                $note->setFamId($family->getId());
                $note->setText(_('Family Deactivated'));
                $note->setType('edit');
                $note->setEntered($id);
                $note->save();
            }

            return $response->withJson(["Status" => "success"]);
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/modifyPersonInfo/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personId) and isset($input->FirstName)
            and isset($input->LastName) and isset($input->homePhone)
            and isset($input->email) and isset($input->type)
            and isset($input->BirthDayDate)) {

            $person = PersonQuery::create()->findOneById($input->personId);

            if ( !is_null($person) ) {                
                $photo = base64_encode($person->getPhoto()->getThumbnailBytes());

                if (isset($input->Title)) {
                    $person->setTitle($input->Title);
                }

                $person->setFirstName($input->FirstName);

                if (isset($input->MiddleName)) {
                    $person->setMiddleName($input->MiddleName);
                }
                $person->setLastName($input->LastName);

                if (isset($input->FamilyRole)) {
                    $person->setFmrId($input->FamilyRole);
                }
                
                $person->setHomePhone($input->homePhone);
                
                if (isset($input->workPhone)) {
                    $person->setWorkPhone($input->workPhone);
                }

                if (isset($input->cellPhone)) {
                    $person->setCellPhone($input->cellPhone);
                }

                $person->setEmail($input->email);
                
                if (isset($input->workemail)) {
                    $person->setWorkEmail($input->workemail);
                }

                if (isset($input->Address1)) {
                    $person->getFamily()->setAddress1($input->Address1);
                }

                if (isset($input->Address2)) {
                    $person->getFamily()->setAddress2($input->Address2);
                }

                if (isset($input->Zip)) {
                    $person->getFamily()->setZip($input->Zip);
                }

                if (isset($input->Country)) {
                    $person->getFamily()->setCountry($input->Country);
                }

                if ($input->SendNewsLetter) {
                    $bSendNewsLetterString = "TRUE";
                } else {
                    $bSendNewsLetterString = "FALSE";
                }

                $person->setSendNewsletter($bSendNewsLetterString);

                $sBirthDayDate = new DateTime($input->BirthDayDate);

                $iBirthMonth = $sBirthDayDate->format('m');
                $iBirthDay = $sBirthDayDate->format('d');
                $iBirthYear = $sBirthDayDate->format('Y');

                $person->setBirthDay($iBirthDay);
                $person->setBirthMonth($iBirthMonth);
                $person->setBirthYear($iBirthYear);

                if ($person->getFmrId() == 1 or $person->getFmrId() == 2) {
                    $person->getFamily()->setWeddingdate($input->WeddingDate);
                }
                
                $person->setConfirmReport('Done');
                
                $person->save();

                if (isset($input->personFields)) {
                    // only the right custom fields
                    $ormCustomFields = PersonCustomMasterQuery::Create()
                        ->orderByCustomOrder()                
                        ->find();

                    $aCustomData = [];
                    $bErrorFlag = false;

                    foreach ($ormCustomFields as $rowCustomField) {
                        $currentFieldData = InputUtils::LegacyFilterInput($input->personFields[$rowCustomField->getCustomField()]);
            
                        $bErrorFlag |= !InputUtils::validateCustomField($rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $aCustomErrors);
            
                        // assign processed value locally to $aPersonProps so we can use it to generate the form later
                        $aCustomData[$rowCustomField->getCustomField()] = $currentFieldData;                    
                    }
                

                    $sSQL = '';

                    $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!is_null($person->getFamily()))?$person->getFamily()->getCountry():null, false);

                    foreach ($ormCustomFields as $rowCustomField) {
                        $currentFieldData = trim($aCustomData[$rowCustomField->getCustomField()]);
                        MiscUtils::sqlCustomField($sSQL, $rowCustomField->getTypeId(), $currentFieldData, $rowCustomField->getCustomField(), $sPhoneCountry);
                        
                    }
        
                    // chop off the last 2 characters (comma and space) added in the last while loop iteration.
                    if ($sSQL > '') {
                        $sSQL = 'REPLACE INTO person_custom SET ' . $sSQL . ' per_ID = ' . $person->getId();
                        //Execute the SQL
        
                        $connection = Propel::getConnection();
        
                        $statement = $connection->prepare($sSQL);
                        $statement->execute();
                    }     
                }   

                if ($input->type == 'person') {
                    $res = ConfirmReportService::getPersonStandardInfos($person, $photo);
                    $resCustom = ConfirmReportService::getPersonCustomFields($person);
                } else if ($input->type == 'family') {
                    $res = ConfirmReportService::getPersonForFamilyStandardInfos($person, $photo);                    
                    $resCustom = "";//ConfirmReportService::getFamilyCustomFields($family);
                }
            }

            return $response->withJson(["Status" => "success", 'content' => $res, 'contentCustom' => $resCustom]);
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/modifyFamilyInfo/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if (isset ($input->familyId) and isset($input->FamilyName) and isset($input->Address1)
            and isset($input->Address2) and isset($input->City) and isset($input->Zip)
            and isset($input->Country) and isset($input->State) and isset($input->homePhone)
            and isset($input->workPhone) and isset($input->cellPhone) and isset($input->email)
            and isset($input->WeddingDate) and isset($input->SendNewsLetter)) {

            $family = FamilyQuery::create()->findOneById($input->familyId);

            if ( !is_null($family) ) {
                $family->setName($input->FamilyName);
                $family->setAddress1($input->Address1);
                $family->setAddress2($input->Address2);

                $family->setCity($input->City);
                $family->setZip($input->Zip);
                $family->setCountry($input->Country);
                $family->setState($input->State);

                $family->setHomePhone($input->homePhone);
                $family->setWorkPhone($input->workPhone);
                $family->setCellPhone($input->cellPhone);

                $family->setEmail($input->email);

                $family->setWeddingdate($input->WeddingDate);

                $family->setSendNewsletter(($input->SendNewsLetter)?"TRUE":"FALSE");

                $family->setConfirmReport('Done');

                $family->save();

                $res = ConfirmReportService::getFamilyStandardInfos($family);

                return $response->withJson(["Status" => "success", 'content' => $res]);
            }
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/onlineVerificationFinished/', function (ServerRequest $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->token) and isset($input->message) ) {
            $token = TokenQuery::create()->findPk($input->token);
            if ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
                $family = FamilyQuery::create()->findPk($token->getReferenceId());
                if ($family != null) {
                    $family->setConfirmReport('Done');
                    $family->save();

                    $note = new Note();
                    $note->setFamily($family);
                    $note->setType("verify");
                    $note->setEntered(Person::SELF_VERIFY);
                    $message = gettext("No Changes");
                    if (!empty($input->message)) {
                        $message = $input->message;
                    }
                    $note->setText($message);
                    $note->save();

                    $mail = new FamilyVerificationValidation([SystemConfig::getValue("sChurchEmail")], $family->getName(), $token->getToken(), $message, $family->getId());

                    if (($familyEmailSent = $mail->send())) {
                        $this->familiesEmailed = $this->familiesEmailed + 1;
                    } else {
                        LoggerUtils::getAppLogger()->error($mail->getError());
                    }

                    return $response->withJson(["Status" => "success", 'familyEmailSent' => $familyEmailSent]);
                }
            } else if ($token != null && $token->isVerifyPersonToken() && $token->isValid()) {
                $person = PersonQuery::create()->findPk($token->getReferenceId());
                if ($person != null) {
                    $person->setConfirmReport('Done');
                    $person->save();

                    $note = new Note();
                    $note->setPerson($person);
                    $note->setType("verify");
                    $note->setEntered(Person::SELF_VERIFY);
                    $message = gettext("No Changes");
                    if (!empty($input->message)) {
                        $message = $input->message;
                    }
                    $note->setText($message);
                    $note->save();

                    $mail = new PersonVerificationValidation([SystemConfig::getValue("sChurchEmail")], $person->getFullName(), $token->getToken(), $message, $person->getId());

                    if (($personEmailSent = $mail->send())) {
                        $this->personEmailSent = $this->personEmailSent + 1;
                    } else {
                        LoggerUtils::getAppLogger()->error($mail->getError());
                    }

                    return $response->withJson(["Status" => "success", 'familyEmailSent' => $personEmailSent]);
                }
            }        
        }

        return $response->withJson(["Status" => "failed"]);
    });
});


