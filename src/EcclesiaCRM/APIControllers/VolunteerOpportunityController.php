<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\MyPDO\CardDavPDO;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\PersonVolunteerOpportunity;
use EcclesiaCRM\PersonVolunteerOpportunityQuery;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

use EcclesiaCRM\VolunteerOpportunityQuery;
use EcclesiaCRM\VolunteerOpportunity;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;

use Propel\Runtime\Propel;
use PDO;
use Slim\Exception\HttpInternalServerErrorException;

class VolunteerOpportunityController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function settingsActiveValue (ServerRequest $request, Response $response, array $args): Response {
        $volID = $args['volID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $vol = VolunteerOpportunityQuery::create()->findOneById($volID);
            if (!is_null($vol)) {
                $vol->setActive($flag);
                $vol->save();
            } else {
                throw new HttpInternalServerErrorException($request, 'invalid group id');                
            }            
        } else {
            throw new HttpInternalServerErrorException($request, 'invalid status value');            
        }
        return $response->withJson(['status' => "success"]);
    }

    public function settingsManagersValue (ServerRequest $request, Response $response, array $args): Response {
        $volID = $args['volID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $vol = VolunteerOpportunityQuery::create()->findOneById($volID);
            if (!is_null($vol)) {
                $vol->setManagers($flag);
                $vol->save();
            } else {
                throw new HttpInternalServerErrorException($request, 'invalid group id');                
            }            
        } else {
            throw new HttpInternalServerErrorException($request, 'invalid status value');            
        }
        return $response->withJson(['status' => "success"]);
    }

    public function addressBook (ServerRequest $request, Response $response, array $args): Response
    {
        if ( !(SessionUser::getUser()->isSeePrivacyDataEnabled() and array_key_exists('volID', $args)) ) {
            return $response->withStatus(401);
        }

        // we get the group
        $vol = VolunteerOpportunityQuery::create()->findOneById ($args['volID']);

        // We set the BackEnd for sabre Backends
        $carddavBackend = new CardDavPDO();

        $addressbook = $carddavBackend->getAddressBookForVolunteers ($args['volID']);

        $filename = $vol->getName().".vcf";

        $output = $carddavBackend->generateVCFForAddressBook($addressbook['id']);
        $size = strlen($output);

        $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Content-Length',$size)
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Expires', '0');


        $response->getBody()->write($output);

        return $response;
    }

    public function settingsEmailExportVvalue(ServerRequest $request, Response $response, array $args): Response {
        $volID = $args['volID'];
        $flag = $args['value'];
        if ($flag == "true" || $flag == "false") {
            $vol = VolunteerOpportunityQuery::create()->findOneById($volID);
            if (!is_null($vol)) {
                $vol->setIncludeInEmailExport($flag);
                $vol->save();
            } else {
                throw new HttpInternalServerErrorException($request, 'invalid group id');                
            }
            return $response->withJson(['status' => "success"]);
        } else {
            throw new HttpInternalServerErrorException($request, 'invalid export value');
        }
    }

    private function selectMenuParents($menus, $volID, $parentId = NULL)
    {
        $res = '<select class="form-control form-control-sm selectHierarchy" data-id="'.$volID.'">\n';
        $res .= '<option value="-1">--'._("None").'--</option>';

        foreach ($menus as $menu) {
            if ($menu['vol_ID'] != $volID) {
                $res .= '<option value="' . $menu['vol_ID'] . '" '.(($parentId != NULL and $parentId == $menu['vol_ID'])?'selected':''). '>' . $menu['vol_Name'] . '</option>';
            }
        }
        $res .= '</select>';

        return $res;
    }    

    private function selectMenuIcons($volID, $icon)
    {
        $connection = Propel::getConnection();

        $result = $connection->query("SHOW COLUMNS FROM `volunteeropportunity_vol` LIKE 'vol_icon'");

        $res = '<div class="btn-group custom-dropdown">
  <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="'.$icon.'"></i> '.$icon.'
  </button>
  <div class="dropdown-menu">';

        if ($result) {
            $arr = $result->fetch(PDO::FETCH_ASSOC)['Type'];
            $option_array = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $arr));

            foreach ($option_array as $item) {
                $res .= '<a class="dropdown-item selectIcon" data-id="' . $item . '" data-vold-id="'.$volID.'"><i class="' . $item . '"></i> ' .$item.  ($icon == $item?'&check;':''). '</a>';
            }
        }
        $res .= '  </div>';
        $res .= '</div>';


        return $res;
    }

    private function selectMenuColors($volID, $icon)
    {
        $connection = Propel::getConnection();

        $result = $connection->query("SHOW COLUMNS FROM `volunteeropportunity_vol` LIKE 'vol_color'");

        $res = '<div class="btn-group custom-dropdown">
  <button type="button" class="btn btn-secondary dropdown-toggle '.$icon.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    '.$icon.'
  </button>
  <div class="dropdown-menu">';

        if ($result) {
            $arr = $result->fetch(PDO::FETCH_ASSOC)['Type'];
            $option_array = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/", "\\2", $arr));

            foreach ($option_array as $item) {
                $res .= '<a class="dropdown-item custom-dropdown-item selectColor '.$item.'" data-id="' . $item . '" data-vold-id="'.$volID.'">' . $item . ' ' . ($icon == $item?'&check;':''). '</a>';
            }
        }
        $res .= '  </div>';
        $res .= '</div>';

        return $res;
    }


    public function getAllVolunteerOpportunities(ServerRequest $request, Response $response, array $args): Response
    {
        if (!(SessionUser::getUser()->isCanvasserEnabled() && SessionUser::getUser()->isMenuOptionsEnabled())) {
            return $response->withStatus(401);
        }

        $volunteerOpportunities = VolunteerOpportunityQuery::Create()->orderByName(Criteria::ASC)->find();

        $volunteerOpportunitiesMenu = VolunteerOpportunityQuery::Create()
            ->select(['vol_ID', 'vol_Name'])
            ->orderByName(Criteria::ASC)->find();

        $menus = $volunteerOpportunitiesMenu->toArray();

        $res = [];

        foreach ($volunteerOpportunities as $volunteerOpportunity) {
            $elt = [
                'Id' => $volunteerOpportunity->getId(),
                'Active' => $volunteerOpportunity->getActive(),
                'Name' => $volunteerOpportunity->getName(),
                'Description' => $volunteerOpportunity->getDescription(),
                'ParentId' => $volunteerOpportunity->getParentId(),
                'MenuParents' => $this->selectMenuParents($menus, $volunteerOpportunity->getId(), $volunteerOpportunity->getParentId()),
                'MenuIcons' => $this->selectMenuIcons( $volunteerOpportunity->getId(), $volunteerOpportunity->getIcon() ),
                'MenuColors' => $this->selectMenuColors( $volunteerOpportunity->getId(), $volunteerOpportunity->getColor() )
            ];

            $res[] = $elt;
        }


        return $response->withJson(["VolunteerOpportunities" => $res]);
    }

    public function deleteVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);

            if (!is_null($vo)) {
                $vo->delete();
            }

            return $response->withJson(['success' => true]);

        }

        return $response->withJson(['success' => false]);
    }

    public function createVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->Name) && isset ($input->desc) && isset ($input->state) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = new VolunteerOpportunity();

            $vo->setName($input->Name);
            $vo->setDescription($input->desc);
            $vo->setActive(($input->state)?1:0);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function setVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && isset ($input->Name) && isset ($input->desc) && isset ($input->state) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {

            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->id);

            $vo->setName($input->Name);
            $vo->setDescription($input->desc);
            $vo->setActive(($input->state)?1:0);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function editVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->id) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            return $response->write(VolunteerOpportunityQuery::Create()->findOneById($input->id)->toJSON());
        }

        return $response->withJson(['success' => false]);
    }

    public function changeParentVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->parentId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            if ($input->parentId == -1) {
                $vo->setParentId(NULL);
            } else {
                $vo->setParentId($input->parentId);
            }

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function changeIconVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->iconId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            $vo->setIcon($input->iconId);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function changeColorVolunteerOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->voldId) && isset($input->colId) && SessionUser::getUser()->isMenuOptionsEnabled() && SessionUser::getUser()->isCanvasserEnabled()) {
            $vo = VolunteerOpportunityQuery::Create()->findOneById($input->voldId);

            $vo->setColor($input->colId);

            $vo->save();

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }
    
    public function getMembers(ServerRequest $request, Response $response, array $args): Response
    {
        $volID = $args['volunteerID'];

        $persons = PersonVolunteerOpportunityQuery::create()
            ->usePersonQuery()
            ->addAsColumn('FirstName', PersonTableMap::COL_PER_FIRSTNAME)
            ->addAsColumn('LastName', PersonTableMap::COL_PER_LASTNAME)
            ->addAsColumn('PersonId', PersonTableMap::COL_PER_ID)          
            ->addAsColumn('FamId', PersonTableMap::COL_PER_FAM_ID)            
            ->endUse()
            ->addAscendingOrderByColumn('person_per.per_LastName')
            ->addAscendingOrderByColumn('person_per.per_FirstName')
            ->findByVolunteerOpportunityId($volID);

        $res = [];
        
        foreach ($persons->toArray() as $member)
        {
            $fam = FamilyQuery::create()->findOneById($member['FamId']);
            $per = PersonQuery::create()->findOneById($member['PersonId']);

            // Philippe Logel : this is usefull when a person don't have a family : ie not an address
            if (!is_null($fam)
                && !is_null($fam->getAddress1())
                && !is_null($fam->getAddress2())
                && !is_null($fam->getCity())
                && !is_null($fam->getState())
                && !is_null($fam->getZip())
            ) {
                $member['Person']['Address1']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getAddress1();
                $member['Person']['Address2']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getAddress2();
                $member['Person']['City']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getCity();
                $member['Person']['State']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getState();
                $member['Person']['Zip']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getZip();
                $member['Person']['CellPhone']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getCellPhone();
                $member['Person']['Email']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $fam->getEmail();
                
            } else {
                $member['Person']['Address1']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getAddress1();
                $member['Person']['Address2']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getAddress2();
                $member['Person']['City']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getCity();
                $member['Person']['State']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getState();
                $member['Person']['Zip']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getZip();
                $member['Person']['CellPhone']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getCellPhone();
                $member['Person']['Email']= (!SessionUser::getUser()->isSeePrivacyDataEnabled()) ? _('Private Data') : $per->getEmail();
                
            }

            $member['Person']['img']= $per->getJPGPhotoDatas();

            $res[] = $member;
        }

        return $response->withJson(['PersonVolunteers' => $res, 'count' => $persons->count()]);
    }

    public function addPerson(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->volID) && isset($input->PersonID) ) {

            $person = PersonVolunteerOpportunityQuery::create()
                ->filterByVolunteerOpportunityId($input->volID)
                ->filterByPersonId($input->PersonID)
                ->findOneOrCreate();         
                
            $vol = VolunteerOpportunityQuery::create()
                ->findOneById($input->volID);

            $vol->addPersonVolunteerOpportunity($person);
            $vol->save();            

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function removeperson(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->volID) && isset($input->PersonID) ) {

            $vol = PersonVolunteerOpportunityQuery::create()
                ->filterByVolunteerOpportunityId($input->volID)
                ->findOneByPersonId($input->PersonID);

            if (!is_null($vol)) {
                $vol->delete();
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function removePersons(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->volID) && isset($input->Persons) ) {

            foreach ($input->Persons as $PersonID) {
                $vol = PersonVolunteerOpportunityQuery::create()
                    ->filterByVolunteerOpportunityId($input->volID)
                    ->findOneByPersonId($PersonID);

                if (!is_null($vol)) {
                    $vol->delete();
                }
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    public function removeAllMembers(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        if (isset ($input->volId) ) {

            $vols = PersonVolunteerOpportunityQuery::create()
                    ->filterByVolunteerOpportunityId($input->volId)
                    ->find();

            foreach ($vols as $vol) {
                if (!is_null($vol)) {
                    $vol->delete();
                }
            }

            return $response->withJson(['success' => true]);
        }

        return $response->withJson(['success' => false]);
    }

    

    
       
    public function defaultOpportunity(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        $vol = VolunteerOpportunityQuery::create()
            ->orderByName(Criteria::ASC)
            ->findOne();

            
        if (is_null ($vol)) {
            return $response->withJson(['success' => false]);
        }

        return $response->withJson(['success' => true, 'id' => $vol->getId()]);
    }

    public function getAll(ServerRequest $request, Response $response, array $args): Response
    {
        $input = (object)$request->getParsedBody();

        $vol = VolunteerOpportunityQuery::create()
            ->orderByName(Criteria::ASC)
            ->find();

            
        return $response->withJson(['success' => true, 'Opportunities' => $vol->toArray()]);
    }
}
