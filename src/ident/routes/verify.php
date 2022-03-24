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

$app->group('/my-profile', function (RouteCollectorProxy $group) {

    $group->get('/{token}', function (Request $request, Response $response, array $args) {
        $renderer = new PhpRenderer("templates/verify/");
        $token = TokenQuery::create()->findPk($args['token']);
        $haveFamily = false;
        if ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
            $family = FamilyQuery::create()->findPk($token->getReferenceId());
            $haveFamily = ($family != null);
            if ($token->getRemainingUses() > 0) {
                $token->setRemainingUses($token->getRemainingUses() - 1);
                $token->save();
            }
        }

        if ($haveFamily) {
            return $renderer->render($response, "verify-family-info.php", array("family" => $family, "token" => $token, "realToken" => $args['token']));
        } else {
            return $renderer->render($response, "/../404.php", array("message" => gettext("Unable to load verification info")));
        }
    });

    $group->post('/{token}', function (Request $request, Response $response, array $args) {
        $token = TokenQuery::create()->findPk($args['token']);
        if ($token != null && $token->isVerifyFamilyToken() && $token->isValid()) {
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

        if (isset ($input->personId)) {
            $person = PersonQuery::create()->findOneById($input->personId);

            $code = '<div class="card card-primary">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <img class="profile-user-img img-responsive img-circle initials-image"
                                     src="data:image/png;base64,'. base64_encode($person->getPhoto()->getThumbnailBytes()) .'">
                                </div>
                                <br/>
                                <p class="text-center">';
            $code .= '<input type="text" name="FirstName" id="FirstName"
                               value="'. htmlentities(stripslashes($person->getFirstName()), ENT_NOQUOTES, 'UTF-8') .'"
                               class= "" placeholder="' . _("First Name") . '">';

            $code .= '<br/>';
            $code .= '<input type="text" name="MiddleName" id="MiddleName"
                               value="'. htmlentities(stripslashes($person->getMiddleName()), ENT_NOQUOTES, 'UTF-8') .'"
                               class= "" placeholder="' . _("Middle Name") . '">';

            $code .= '<br/>';
            $code .= '<input type="text" name="LastName" id="LastName"
                               value="'. htmlentities(stripslashes($person->getLastName()), ENT_NOQUOTES, 'UTF-8') .'"
                               class= "" placeholder="' . _("Last Name") . '">';

            $code .= '</p>
                                <p class="text-muted text-center"><i
                                        class="fa  fa-'. ($person->isMale() ? "male" : "female") .'"></i> '. $person->getFamilyRoleName().'
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
                                            $code .= '<input type="text" name="homePhone" class="" value="'. $person->getHomePhone() .'" id="homePhone" size="30" placeholder="' . _("Home Phone") . '">';
                                            $code .= '
                                            </div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';

                                            $code .= '<i class="fa  fa-briefcase"
                                               title="' . _("Work Phone") . '"></i>(W)
                                            </div>
                                            <div class="col-md-6">';
                                            $code .= '<input type="text" name="workPhone" class="" value="'. $person->getWorkPhone() .'" id="workPhone" size="30" placeholder="' . _("Work Phone") . '">';
                                            $code .= '</div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';
                                            $code .= '<i class="fa  fa-mobile"
                                               title="' . _("Mobile Phone") . '"></i>(M)
                                            </div>
                                            <div class="col-md-6">';
                                            $code .= '<input type="text" name="cellPhone" class="" value="'. $person->getHomePhone() .'" id="cellPhone" size="30" placeholder="' . _("Cell Phone") . '">';
                                            $code .= '
                                            </div>
                                       </div>
                                         <div class="row">
                                            <div class="col-md-2">';

                                            $code .= '<i class="fa  fa-envelope"
                                               title="' . _("Email") . '"></i>(H)
                                            </div>
                                            <div class="col-md-6">';
                                            $code .= '<input type="text" name="email" class="" value="'. $person->getEmail() .'" id="email" size="30" placeholder="' . _("Email") . '">';
                                            $code .= '
                                            </div>
                                       </div>
                                       <div class="row">
                                            <div class="col-md-2">';
                                            $code .= '<i class="fa  fa-envelope"
                                               title="' . _("Work Email") . '"></i>(W)
                                             </div>
                                            <div class="col-md-6">';
                                            $code .= '<input type="text" name="workemail" class="" value="'. $person->getWorkEmail() .'" id="workemail" size="30" placeholder="' . _("Email") . '">';
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

                                        $code .= '<input type="text" name="BirthDayDate" class="date-picker" value="'. OutputUtils::change_date_for_place_holder($sBirthDayDate) .'" maxlength="10" id="BirthDayDate" size="10" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';
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

                                        $code .= '<b>'._("Classification") .':</b> ' . $classification;
                                    $code .= '</li>';
                                    if (count($person->getPerson2group2roleP2g2rs()) > 0) {
                                        $code .= '<li class="list-group-item">
                                            <h4>'._("Groups") . '</h4>';
                                            foreach ($person->getPerson2group2roleP2g2rs() as $groupMembership) {
                                                if ($groupMembership->getGroup() != null) {
                                                    $listOption = ListOptionQuery::create()->filterById($groupMembership->getGroup()->getRoleListId())->filterByOptionId($groupMembership->getRoleId())->findOne()->getOptionName();

                                                    $code .= '<b>'.  $groupMembership->getGroup()->getName() . '</b>: <span
                                                        class="pull-right">' . _($listOption) . '</span><br/>';
                                                }
                                            }
                                        $code .= '</li>';
                                    }
                                $code .= '</ul>
                                <br/>
                        </div>';

            return $response->withJson(["Status" => "success", "html" => $code]);
        }

        return $response->withJson(["Status" => "failed"]);
    });


    /*$group->post('/', function (Request $request, Response $response, array $args) {
        $body = $request->getParsedBody();
        $renderer = new PhpRenderer("templates/verify/");
        $family = PersonQuery::create()->findByEmail($body["email"]);
        return $renderer->render($response, "view-info.php", array("family" => $family));
    });*/
});


