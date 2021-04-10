<?php

use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Family;
use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Person;
use Slim\Views\PhpRenderer;

use EcclesiaCRM\FamilyQuery;

$app->group('/register', function (RouteCollectorProxy $group) {

    $enableSelfReg = SystemConfig::getBooleanValue('bEnableSelfRegistration');

    if ($enableSelfReg) {
        $group->get('/', function (Request $request, Response $response, array $args) {
            $renderer = new PhpRenderer('templates/registration/');

            return $renderer->render($response, 'family-register.php', ['sRootPath' => SystemURLs::getRootPath()]);
        });

        $group->post('/', function (Request $request, Response $response, array $args) {
            $renderer = new PhpRenderer('templates/registration/');

            $body = $request->getParsedBody();

            $family = new Family();
            $family->setName($body['familyName']);
            $family->setAddress1($body['familyAddress1']);
            $family->setCity($body['familyCity']);
            $family->setState($body['familyState']);
            $family->setCountry($body['familyCountry']);
            $family->setZip($body['familyZip']);
            $family->setHomePhone($body['familyHomePhone']);
            $family->setEnteredBy(Person::SELF_REGISTER);
            $family->setDateEntered(new \DateTime());
            $family->save();

            $className = 'Regular Attender';
            if ($body['familyPrimaryChurch'] == 'No') {
                $className = 'Guest';
            }
            $familyRoles = ListOptionQuery::create()->filterById(2)->orderByOptionSequence()->find();

            $pageObjects = ['sRootPath' => SystemURLs::getRootPath(),
                'family' => $family,
                'familyCount' => $body['familyCount'],
                'familyRoles' => $familyRoles,
                'className' => $className,
                'familyPrimaryChurch' => $body['familyPrimaryChurch']];

            return $renderer->render($response, 'family-register-members.php', $pageObjects);
        });

        $group->post('/confirm', function (Request $request, Response $response, array $args) {
            $renderer = new PhpRenderer('templates/registration/');

            $body = $request->getParsedBody();

            $famID = $body['famId'];
            $family = FamilyQuery::create()->findOneById($famID);

            $familyCount = $body['familyCount'];

            $className = $body['className'];
            $familyMembership = ListOptionQuery::create()->filterById(1)->filterByOptionName($className)->findOne();

            $familyMembers = [];
            for ($x = 1; $x <= $familyCount; $x++) {
                $person = new Person();
                $person->setFirstName($body['memberFirstName-' . $x]);
                $person->setLastName($body['memberLastName-' . $x]);
                $person->setGender($body['memberGender-' . $x]);
                $person->setEmail($body['memberEmail-' . $x]);

                $phoneType = $body['memberPhoneType-' . $x];
                if ($phoneType == 'mobile') {
                    $person->setCellPhone($body['memberPhone-' . $x]);
                } elseif ($phoneType == 'work') {
                    $person->setWorkPhone($body['memberPhone-' . $x]);
                } else {
                    $person->setHomePhone($body['memberPhone-' . $x]);
                }

                $birthday = $body['memberBirthday-' . $x];
                if (!empty($birthday)) {
                    $birthdayDate = \DateTime::createFromFormat(SystemConfig::getValue('sDatePickerFormat'), $birthday);
                    $person->setBirthDay($birthdayDate->format('d'));
                    $person->setBirthMonth($birthdayDate->format('m'));
                    $person->setBirthYear($birthdayDate->format('Y'));
                }

                if (!empty($body['memberHideAge-' . $x])) {
                    $person->setFlags(1);
                }

                $person->setEnteredBy(Person::SELF_REGISTER);
                $person->setDateEntered(new \DateTime());

                $familyRole = $body['memberRole-' . $x];
                $person->setFamily($family);
                $person->setFmrId($familyRole);
                $person->save();
                $family->addPerson($person);
                array_push($familyMembers, $person);
            }
            $pageObjects = ['sRootPath' => SystemURLs::getRootPath(), 'family' => $family];

            return $renderer->render($response, 'family-register-done.php', $pageObjects);
        });
    }
});
