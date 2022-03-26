<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use Slim\Views\PhpRenderer;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\TokenQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\Person;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;
use EcclesiaCRM\TokensPasswordQuery;
use EcclesiaCRM\TokensPassword;

$app->group('/my-profile', function (RouteCollectorProxy $group) {

    $group->get('/{token}', function (Request $request, Response $response, array $args) {
        $renderer = new PhpRenderer("templates/verify/");
        $token = TokenQuery::create()->findPk($args['token']);

        $haveFamily = false;
        $loginWindow = false;

        if ( $token != null && $token->isVerifyFamilyToken() && $token->isValid() ) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            $haveFamily = ($family != null);
            if ($token->getRemainingUses() > 0) {
                $token->setRemainingUses($token->getRemainingUses() - 1);
                $token->save();
            }
        }

        if ($loginWindow == false && $haveFamily == true) {
            return $renderer->render($response, "login-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } elseif ($haveFamily) {
            return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } else {
            return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
        }
    });

    $group->post('/{token}', function (Request $request, Response $response, array $args) {

        $token = TokenQuery::create()->findPk($args['token']);

        if ($_POST['User'] && $_POST['Password']) {
            // post data from : login-info.php

            $renderer = new PhpRenderer("templates/verify/");

            # TODO : add the checkin of the login
            $tokenPassword = TokensPasswordQuery::create()->findOneByTokenId($args['token']);

            if (is_null($tokenPassword)) {
                return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
            }

            $family = FamilyQuery::create()->findPk($token->getReferenceId());

            $emails = [$family->getEmail()];
            $emails = array_merge($emails, $family->getEmails());

            \EcclesiaCRM\Utils\LoggerUtils::getAppLogger()->info("password : ".$tokenPassword->getPassword(). " ".$_POST['Password']);
            \EcclesiaCRM\Utils\LoggerUtils::getAppLogger()->info("User : ".$_POST['User']. " ".print_r($emails, true));

            if ( !in_array($_POST['User'], $emails) or $_POST['Password'] != $tokenPassword->getPassword() ) {
                return $renderer->render($response, "login-info.php", array("family" => $family, "token" => $token,
                    "realToken" => $args['token'], "sErrorText" => _("Wrong email or password")));
            }

            session_start();
            $_SESSION['username'] = $_POST['User'];
            $_SESSION['password'] = $_POST['Password'];

            return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } elseif ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            if ($family != null) {
                $body = (object)$request->getParsedBody();
                $note = new Note();
                $note->setFamily($family);
                $note->setType("verify");
                $note->setEntered(Person::SELF_VERIFY);
                $note->setText(gettext("No Changes"));
                if (!empty($body->message)) {
                    $note->setText($body->message);
                }
                $note->save();


            }
        }
        return $response->withStatus(200);
    });

    $group->post('/getPersonInfo/', function (Request $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        $input = (object)$request->getParsedBody();

        if ( isset ($input->token) ) {

            $token = TokenQuery::create()->findPk($input->token);
            if (!($token != null && $token->isVerifyFamilyToken() && $token->isValid())) {
                return $response->withStatus(200);
            }

            if (isset ($input->personId)) {
                $person = PersonQuery::create()->findOneById($input->personId);

                $code = '<h3>' . _("Person") . " : " . $person->getFullName() . '</h3><hr/>';

                $code .= '<div class="card card-primary">
                            <div class="card-body box-profile">
                                <div class="text-left">
                                    <img class="profile-user-img img-responsive img-circle initials-image"
                                     src="data:image/png;base64,' . base64_encode($person->getPhoto()->getThumbnailBytes()) . '">
                                </div>
                                <br/>
                                <div class="text-left">
                                    <div class="row">
                                        <div class="col-4">
                                            <label for="FirstName">' . _('First Name') . '</label>
                                        </div>
                                        <div class="col-md-6">';
                $code .= '<input type="text" name="FirstName" id="FirstName"
                                   value="' . htmlentities(stripslashes($person->getFirstName()), ENT_NOQUOTES, 'UTF-8') . '"
                                   class= "" placeholder="' . _("First Name") . '">';

                $code .= '
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">
                                            <label for="FirstName">' . _('Middle Name') . '</label>
                                        </div>
                                        <div class="col-md-6">';
                $code .= '<input type="text" name="MiddleName" id="MiddleName"
                                   value="' . htmlentities(stripslashes($person->getMiddleName()), ENT_NOQUOTES, 'UTF-8') . '"
                                   class= "" placeholder="' . _("Middle Name") . '">';

                $code .= '              </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">
                                            <label for="FirstName">' . _('Last Name') . '</label>
                                        </div>
                                        <div class="col-md-6">';
                $code .= '<input type="text" name="LastName" id="LastName"
                                   value="' . htmlentities(stripslashes($person->getLastName()), ENT_NOQUOTES, 'UTF-8') . '"
                                   class= "" placeholder="' . _("Last Name") . '">';

                $code .= '
                                        </div>
                                    </div>
                               </p>
                                <p class="text-muted text-left"><i
                                        class="fa  fa-' . ($person->isMale() ? "male" : "female") . '"></i> ';

                $iFamilyRole = $person->getFmrId();

                //Get Family Roles for the drop-down
                $ormFamilyRoles = ListOptionQuery::Create()
                    ->orderByOptionSequence()
                    ->findById(2);

                $code .= '<select name="FamilyRole" class="" id="FamilyRole">
                    <option value="0">' . _("Unassigned") . '</option>
                    <option value="0" disabled>-----------------------</option>';

                foreach ($ormFamilyRoles as $ormFamilyRole) {
                    $code .= '<option value="' . $ormFamilyRole->getOptionId() . '"
                        ' . (($iFamilyRole == $ormFamilyRole->getOptionId()) ? ' selected' : '') . '>' . $ormFamilyRole->getOptionName() . '</option>';
                }

                $code .= '</select>';

                $code .= '
                                </p>
                                <ul class="list-group list-group-unbordered">
                                    <li class="list-group-item">
                                        <br/>
                                        <div class="row">
                                            <div class="col-md-2">';
                $code .= '<i class="fa  fa-phone"
                                               title="' . _("Home Phone") . '"></i>(H)
                                            </div>
                                            <div class="col-md-6">';
                $code .= '<input type="text" name="homePhone" class="" value="' . $person->getHomePhone() . '" id="homePhone" size="30" placeholder="' . _("Home Phone") . '">';
                $code .= '
                                            </div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';

                $code .= '<i class="fa  fa-briefcase"
                                               title="' . _("Work Phone") . '"></i>(W)
                                            </div>
                                            <div class="col-md-6">';
                $code .= '<input type="text" name="workPhone" class="" value="' . $person->getWorkPhone() . '" id="workPhone" size="30" placeholder="' . _("Work Phone") . '">';
                $code .= '</div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';
                $code .= '<i class="fa  fa-mobile"
                                               title="' . _("Mobile Phone") . '"></i>(M)
                                            </div>
                                            <div class="col-md-6">';
                $code .= '<input type="text" name="cellPhone" class="" value="' . $person->getHomePhone() . '" id="cellPhone" size="30" placeholder="' . _("Cell Phone") . '">';
                $code .= '
                                            </div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';

                $code .= '<i class="fa  fa-envelope"
                                               title="' . _("Email") . '"></i>(H)
                                            </div>
                                            <div class="col-md-6">';
                $code .= '<input type="text" name="email" class="" value="' . $person->getEmail() . '" id="email" size="30" placeholder="' . _("Email") . '">';
                $code .= '
                                            </div>
                                       </div>
                                       <div class="row">
                                            <div class="col-md-2">';
                $code .= '<i class="fa  fa-envelope"
                                               title="' . _("Work Email") . '"></i>(W)
                                             </div>
                                            <div class="col-md-6">';
                $code .= '<input type="text" name="workemail" class="" value="' . $person->getWorkEmail() . '" id="workemail" size="30" placeholder="' . _("Email") . '">';
                $code .= '
                                            </div>
                                       </div>
                                       <div class="row">
                                            <div class="col-md-2">';
                $code .= '<i class="fa  fa-birthday-cake" title="' . _("Birthday") . '"></i>
                                        </div>
                                            <div class="col-md-6">';

                $iBirthMonth = $person->getBirthMonth();
                $iBirthDay = $person->getBirthDay();
                $iBirthYear = $person->getBirthYear();
                $sBirthDayDate = $iBirthDay . "-" . $iBirthMonth . "-" . $iBirthYear;

                $code .= '<input type="text" name="BirthDayDate" class="date-picker" value="' . OutputUtils::change_date_for_place_holder($sBirthDayDate) . '" maxlength="10" id="BirthDayDate" size="10" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';
                //$code .= '<i class="fa  fa-eye-slash" title="' .  _("Age Hidden") .'"></i>';

                $code .= '
                                            </div>
                                       </div>
                                    </li>
                                    <li class="list-group-item">';

                $classification = "";
                $cls = ListOptionQuery::create()->filterById(1)->filterByOptionId($person->getClsId())->findOne();
                if (!empty($cls)) {
                    $classification = $cls->getOptionName();
                }

                $code .= '<b>' . _("Classification") . ':</b> ' . $classification;
                $code .= '</li>';
                if (count($person->getPerson2group2roleP2g2rs()) > 0) {
                    $code .= '<li class="list-group-item">
                                            <h4>' . _("Groups") . '</h4>';
                    foreach ($person->getPerson2group2roleP2g2rs() as $groupMembership) {
                        if ($groupMembership->getGroup() != null) {
                            $listOption = ListOptionQuery::create()->filterById($groupMembership->getGroup()->getRoleListId())->filterByOptionId($groupMembership->getRoleId())->findOne()->getOptionName();

                            $code .= '<b>' . $groupMembership->getGroup()->getName() . '</b>: <span
                                                        class="pull-right">' . _($listOption) . '</span><br/>';
                        }
                    }
                    $code .= '</li>';
                }
                $code .= '</ul>
                                <br/>
                            <!-- /.box-body -->
                        </div>';

                return $response->withJson(["Status" => "success", "html" => $code]);
            }
        }

        return $response->withJson(["Status" => "failed"]);
    });

    $group->post('/getFamilyInfo/', function (Request $request, Response $response, array $args) {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->token) ) {

            $token = TokenQuery::create()->findPk($input->token);
            if (!($token != null && $token->isVerifyFamilyToken() && $token->isValid())) {
                return $response->withStatus(200);
            }

            if (isset ($input->familyId)) {
                $family = FamilyQuery::create()->findOneById($input->familyId);

                $code = '<h3>' . _("Family") . " : " . $family->getName() . '</h3><hr/>';

                $sName = $family->getName();
                $sAddress1 = $family->getAddress1();
                $sAddress2 = $family->getAddress2();
                $sCity = $family->getCity();
                $sState = $family->getState();
                $sZip = $family->getZip();
                $sCountry = $family->getCountry();
                $sHomePhone = $family->getHomePhone();
                $sWorkPhone = $family->getWorkPhone();
                $sCellPhone = $family->getCellPhone();
                $sEmail = $family->getEmail();
                $bSendNewsLetter = $family->getSendNewsletter();
                $dWeddingDate = ($family->getWeddingdate() != null) ? $family->getWeddingdate()->format("Y-M-d") : "";

                $code .= '<div class="row">
                    <div class="col-md-2">
                    <label>';

                $code .= _("Name");

                $code .= '</label>
                    </div>
                    <div class="col-md-9">';

                $code .= '<input type="text" name="FamilyName" class="" id="FamilyName" value="' . $sName . '" maxlength="15" id="BirthDayDate" size="50" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';

                $code .= '</div>
                    </div>';

                $code .= '<div class="row">
                    <div class="col-md-2">
                        <i class="fa  fa-map-marker" title="' . _("Home Address") . '"></i> <label>' . _('Address') . ' 1:</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" Name="Address1" id="Address1"
                               value="' . htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                               maxlength="250" class="">
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-2">
                        <i class="fa  fa-map-marker" title="' . _("Home Address") . '"></i>  <label>' . _('Address') . ' 2:</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" Name="Address2" id="Address2"
                               value="' . htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                               maxlength="250" class="">
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-2">
                        <i class="fa  fa-map-marker" title="' . _("Home Address") . '"></i>  <label>' . _('City') . ':</label>
                    </div>
                    <div class="col-md-9">
                        <input type="text" Name="City" id="City"
                               value="' . htmlentities(stripslashes($sCity), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                               maxlength="250"
                               class="">
                    </div>
                </div>';

                $code .= '<div class="row">
                    <div ' . (SystemConfig::getValue('bStateUnusefull') ? 'style="display: none;"' : 'class="form-group col-md-3"') . '>
                        <label for="StatleTextBox">' . _("State") . ': </label>';

                $statesDD = new StateDropDown();
                $code .= $statesDD->getDropDown($sState);


                $code .= '        </div>
                    <div ' . (SystemConfig::getValue('bStateUnusefull') ? 'style="display: none;"' : 'class="form-group col-md-3"') . '>
                        <label>' . _('None US/CND State') . ':</label>
                        <input type="text" class="" name="StateTextbox"
                               value="' . (($sCountry != 'United States' && $sCountry != 'Canada') ? htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8') : '')
                    . '" size="20" maxlength="30">
                    </div>
                    <div class="form-group col-md-3">
                        <label>' . _('Zip') . ':</label>
                        <input type="text" Name="Zip" class="form-control form-control-sm"';

                // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
                if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
                    $code .= 'style="text-transform:uppercase" ';
                }
                $code .= 'value="' . htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') . '"
                   maxlength="10" size="8">

                    </div>
                    <div class="form-group col-md-3">
                        <label> ' . _('Country') . ':</label>';
                $code .= CountryDropDown::getDropDown($sCountry);

                $code .= '</div>
                </div>';

                $code .= '<br/>

                    <div class="row">
                        <div class="col-md-1">';
                $code .= '<i class="fa  fa-phone"
                       title="' . _("Phone") . '"></i>(H)
                    </div>
                    <div class="col-md-6">';
                $code .= '<input type="text" name="homePhone" class="" value="' . $sHomePhone . '" id="homePhone" size="30" placeholder="' . _("Cell Phone") . '">';
                $code .= '
                        </div>
                   </div>
                   <div class="row">
                        <div class="col-md-1">';

                $code .= '<i class="fa  fa-briefcase"
                           title="' . _("Work Phone") . '"></i>(W)
                        </div>
                        <div class="col-md-6">';
                $code .= '<input type="text" name="workPhone" class="" value="' . $sWorkPhone . '" id="workPhone" size="30" placeholder="' . _("Work Phone") . '">';
                $code .= '</div>
                   </div>
                   <div class="row">
                        <div class="col-md-1">';
                $code .= '<i class="fa  fa-mobile"
                                               title="' . _("Mobile Phone") . '"></i>(M)
                      </div>
                      <div class="col-md-6">';
                $code .= '<input type="text" name="cellPhone" class="" value="' . $sCellPhone . '" id="cellPhone" size="30" placeholder="' . _("Cell Phone") . '">';
                $code .= '
                                            </div>
                                       </div>
                    <div class="row">
                        <div class="col-md-1">';
                $code .= '<i class="fa  fa-envelope"
                       title="' . _("Family Email") . '"></i>(M)
                    </div>
                    <div class="col-md-6">';
                $code .= '<input type="text" name="email" class="" value="' . $sEmail . '" id="email" size="30" placeholder="' . _("Cell Phone") . '">';
                $code .= '
                        </div>
                   </div>

                    <div class="row">
                        <div class="col-md-1">';
                $code .= '      <i class="fa  fa-heart" title="' . _("Wedding Date") . '"></i>
                        </div>
                        <div class="col-md-6">';

                $code .= '<input type="text" class="date-picker" Name="WeddingDate"
                                   value="' . OutputUtils::change_date_for_place_holder($dWeddingDate) . '" maxlength="12"
                                   id="WeddingDate" size="30"
                                   placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';


                $code .= '
                        </div>
                   </div>

                    <br/>

                    <div class="row">
                       <div class="col-md-3">
                            <label>' . _('Send Newsletter') . ':</label>
                       </div>
                       <div class="col-md-3">
                                <input type="checkbox" Name="SendNewsLetter"
                                       value="1" ' . ($bSendNewsLetter ? ' checked' : '') . '>
                        </div>
                    </div>';

                return $response->withJson(["Status" => "success", "html" => $code]);
            }
        }

        return $response->withStatus(200);
    });



    /*$group->post('/', function (Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();
        $renderer = new PhpRenderer("templates/verify/");
        $family = PersonQuery::create()->findByEmail($body["email"]);
        return $renderer->render($response, "view-info.php", array("family" => $family));
    });*/
});


