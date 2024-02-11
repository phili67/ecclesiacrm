<?php

/*******************************************************************************
 *
 *  filename    : EcclesiaCRM/service/ConfirmReportService.php
 *  last change : 2024-01-31 Philippe Logel
 *  description : Create emails with all the confirmation letters asking member
 *                families to verify the information in the database.
 *
 ******************************************************************************/

namespace EcclesiaCRM\Service;

use EcclesiaCRM\Utils\OutputUtils;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\StateDropDown;
use EcclesiaCRM\dto\CountryDropDown;
use EcclesiaCRM\Utils\MiscUtils;

use EcclesiaCRM\ListOptionQuery;
use EcclesiaCRM\Person;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\Family;

class ConfirmReportService {

    public static function getPersonStandardInfos(Person $person, string $photo) : String
    {
        $res = '<img class="img-circle center-block pull-right img-responsive initials-image" width="100" height="100" style="float: left;margin-right:10px"
                                     src="data:image/png;base64,' . $photo . '">

                                <h3>' . $person->getFullName() . '</h3>

                                <div class="text-muted font-bold m-b-xs family-info">
                                <p class="text-muted"><i
                                        class="fa  fa-' . ($person->isMale() ? "male" : "female") .'"></i> '. $person->getFamilyRoleName() .'
                                </p>
                                </div>

                                <ul class="list-group list-group-unbordered">';

        $res .= '<li class="list-group-item">';
        $res .=     $person->getAddress();
        $res .= '</li>';
        $res .= '<li class="list-group-item">';

        if (!empty($person->getHomePhone())) {
            $res .= '<i class="fa  fa-phone"
                                title="'. _("Home Phone") .'"></i>(H) '. $person->getHomePhone() .'
                            <br/>';
        }
        if (!empty($person->getWorkPhone())) {
        $res .= '<i class="fa  fa-briefcase"
                                title="' . _("Work Phone") . '"></i>(W) '. $person->getWorkPhone() .'
                            <br/>';
        }
        if (!empty($person->getCellPhone())) {
            $res .= '<i class="fa  fa-mobile"
                                title="'. _("Mobile Phone") .'"></i>(M) '.  $person->getCellPhone() .'
                            <br/>';
        }

        if (!empty($person->getEmail())) {
            $res .=  '<i class="fa  fa-envelope"
                                title="'. _("Email") . '"></i>(H) ' .  $person->getEmail() . '<br/>';
        }
        if (!empty($person->getWorkEmail())) {
            $res .= '<i class="fa  fa-envelope-o"
                                title="' . _("Work Email") .'"></i>(W) '. $person->getWorkEmail() . '
                            <br/>';
        }

        $res .= '<i class="fa  fa-birthday-cake" title="' . _("Birthday") .'"></i>'. _('Birthday') . ' :';

        $birthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', 0);
        $res .= $birthDate;
        $res .= '<br/>';

        if ($person->getFmrId() == 1 or $person->getFmrId() == 2) {
            $dWeddingDate = ($person->getFamily()->getWeddingdate() != null) ? $person->getFamily()->getWeddingdate()->format("Y-M-d") : "";
            $res .= '<i class="fa  fa-birthday-cake" title="' . _("Anniversary") .'"></i>'. _('Anniversary') . ' :';
            $res .= OutputUtils::change_date_for_place_holder($dWeddingDate);
            $res .= '<br/>';
        }
            
        $res .= '</li>
            <li class="list-group-item">';

        $classification = "";
        $cls = ListOptionQuery::create()->filterById(1)->filterByOptionId($person->getClsId())->findOne();
        if (!empty($cls)) {
        $classification = $cls->getOptionName();
        }
        $res .= '<b>Classification:</b> '. $classification .'
            </li>';
        if (count($person->getPerson2group2roleP2g2rs()) > 0) {
            $res .= '<li class="list-group-item">
                            <h4>' . _("Groups") . '</h4>';

        foreach ($person->getPerson2group2roleP2g2rs() as $groupMembership) {
            if ($groupMembership->getGroup() != null) {
            $listOption = ListOptionQuery::create()->filterById($groupMembership->getGroup()->getRoleListId())->filterByOptionId($groupMembership->getRoleId())->findOne()->getOptionName();

            $res .= '<b>'. $groupMembership->getGroup()->getName() . '</b>: <span
                                        class="pull-right">'. _($listOption) .'</span><br/>';

            }
        }

        $res.= '    </li>';
        }
        $res .= '<li class="list-group-item">
                <i class="fas fa-newspaper"
                title="'. _("Send Newsletter").'"></i> '. ($person->getSendNewsletter()?_('Ok'):_('No'));
        $res .= "</li>";
        $res.= '</ul>';

        return $res;
    }

    /*
    * 
    * With the fields
    * 
    */
    public static function getPersonStandardTextFields (Person $person): string
    {
        $code = '<h3>' . _("Person") . " : " . $person->getFullName() . '</h3><hr/>';

        $code .= '   <p>
                        <div class="text-left">
                            <img class="profile-user-img img-responsive img-circle initials-image"
                            src="data:image/png;base64,' . base64_encode($person->getPhoto()->getThumbnailBytes()) . '">
                        </div>
                        <br/>
                        <div class="text-left">
                            <div class="row">
                                <div class="col-4">
                                        <label for="FirstName">' . _('Title') . '</label>
                                </div>
                                <div class="col-md-8">';
        $code .= '<input type="text" name="Title" id="Title"
                        value="' . htmlentities(stripslashes($person->getTitle()), ENT_NOQUOTES, 'UTF-8') . '"
                        class="form-control form-control-sm" placeholder="' . _("Title") . '">';

        $code .= '
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <label for="FirstName">' . _('First Name') . '</label>
                            </div>
                            <div class="col-md-8">';
        $code .= '<input type="text" name="FirstName" id="FirstName"
                        value="' . htmlentities(stripslashes($person->getFirstName()), ENT_NOQUOTES, 'UTF-8') . '"
                        class="form-control form-control-sm" placeholder="' . _("First Name") . '">';

        $code .= '
                                </div>
                            </div>                                    
                            <div class="row">
                                <div class="col-4">
                                    <label for="FirstName">' . _('Middle Name') . '</label>
                                </div>
                                <div class="col-md-8">';
        $code .= '<input type="text" name="MiddleName" id="MiddleName"
                        value="' . htmlentities(stripslashes($person->getMiddleName()), ENT_NOQUOTES, 'UTF-8') . '"
                        class="form-control form-control-sm" placeholder="' . _("Middle Name") . '">';

        $code .= '              </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <label for="FirstName">' . _('Last Name') . '</label>
                                </div>
                                <div class="col-md-8">';
        $code .= '<input type="text" name="LastName" id="LastName"
                        value="' . htmlentities(stripslashes($person->getLastName()), ENT_NOQUOTES, 'UTF-8') . '"
                        class="form-control form-control-sm" placeholder="' . _("Last Name") . '">';

        $code .= '
                    </div>
                </div>
        </p>';

        $code .=  '<span class="text-muted text-left">
                <i class="fa  fa-' . ($person->isMale() ? "male" : "female") . '"></i> ';

        $iFamilyRole = $person->getFmrId();

        //Get Family Roles for the drop-down
        $ormFamilyRoles = ListOptionQuery::Create()
            ->orderByOptionSequence()
            ->findById(2);                                                    

        $code .= '<select name="FamilyRole" class="form-control form-control-sm" id="FamilyRole">
            <option value="0">' . _("Unassigned") . '</option>
            <option value="0" disabled>-----------------------</option>';

        foreach ($ormFamilyRoles as $ormFamilyRole) {
            $code .= '<option value="' . $ormFamilyRole->getOptionId() . '"
                ' . (($iFamilyRole == $ormFamilyRole->getOptionId()) ? ' selected' : '') . '>' . $ormFamilyRole->getOptionName() . '</option>';
        }

        $code .= '</select>';

        $code .= '<label>'. _('Address') . ' 1:</label>
        <input type="text" name="Address1"
                value="'. htmlentities(stripslashes($person->getFamily()->getAddress1()), ENT_NOQUOTES, "UTF-8") . '"
                size="30" maxlength="50" class="form-control form-control-sm" id="Address1">
            <br>
            <label>'. _('Zip') . '</label>
            <input type="text" name="Zip"
                    value="'. htmlentities(stripslashes($person->getFamily()->getZip()), ENT_NOQUOTES, "UTF-8") . '"
                    size="30" maxlength="50" class="form-control form-control-sm" id="Zip">
            <br>                                
            <label>'. _('City') . '</label>
            <input type="text" name="City"
                    value="'. htmlentities(stripslashes($person->getFamily()->getCity()), ENT_NOQUOTES, "UTF-8") . '"
                    size="30" maxlength="50" class="form-control form-control-sm" id="City">';
        
        $code .= '      <br>
                        
                        <label>' . _('Zip') . ' :</label>
                            <input type="text" Name="Zip" id="Zip"
                                    value="' . htmlentities(stripslashes($person->getFamily()->getZip()), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                                    maxlength="250" class="form-control form-control-sm">'; 
    
        $code .= '<label>' . _('Address') . ' 2:</label>
                  <input type="text" Name="Address2" id="Address2"
                                    value="' . htmlentities(stripslashes($person->getFamily()->getAddress2()), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                                    maxlength="250" class="form-control form-control-sm"><br>';  

        $code .= '
                        </span>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <br/>
                                <div class="row">
                                    <div class="col-md-2">';
        $code .= '<i class="fa  fa-phone"
                                    title="' . _("Home Phone") . '"></i>(H)
                                    </div>
                                    <div class="col-md-10">';
        $code .= '<input type="text" name="homePhone" class="form-control form-control-sm" value="' . $person->getHomePhone() . '" id="homePhone" size="30" placeholder="' . _("Home Phone") . '">';
        $code .= '
                                    </div>
                            </div>
                                <div class="row">
                                    <div class="col-md-2">';

        $code .= '<i class="fa  fa-briefcase"
                                    title="' . _("Work Phone") . '"></i>(W)
                                    </div>
                                    <div class="col-md-10">';
        $code .= '<input type="text" name="workPhone" class="form-control form-control-sm" value="' . $person->getWorkPhone() . '" id="workPhone" size="30" placeholder="' . _("Work Phone") . '">';
        $code .= '</div>
                            </div>
                                <div class="row">
                                    <div class="col-md-2">';
        $code .= '<i class="fa  fa-mobile"
                                    title="' . _("Mobile Phone") . '"></i>(M)
                                    </div>
                                    <div class="col-md-10">';
        $code .= '<input type="text" name="cellPhone" class="form-control form-control-sm" value="' . $person->getHomePhone() . '" id="cellPhone" size="30" placeholder="' . _("Cell Phone") . '">';
        $code .= '
                                    </div>
                            </div>
                                <div class="row">
                                    <div class="col-md-2">';

        $code .= '<i class="fa  fa-envelope"
                                    title="' . _("Email") . '"></i>(H)
                                    </div>
                                    <div class="col-md-10">';
        $code .= '<input type="text" name="email" class="form-control form-control-sm" value="' . $person->getEmail() . '" id="email" size="30" placeholder="' . _("Email") . '">';
        $code .= '
                                    </div>
                            </div>
                            <div class="row">
                                    <div class="col-md-2">';
        $code .= '<i class="fa  fa-envelope"
                                    title="' . _("Work Email") . '"></i>(W)
                                    </div>
                                    <div class="col-md-10">';
        $code .= '<input type="text" name="workemail" class="form-control form-control-sm" value="' . $person->getWorkEmail() . '" id="workemail" size="30" placeholder="' . _("Work Email") . '">';
        $code .= '
                                    </div>
                            </div>
                            <div class="row">
                                <div class="col-md-2">';
        $code .= '                   <i class="fa  fa-birthday-cake" title="' . _("Birthday") . '"></i> <small>' . _("Birthday") . '</small>
                                </div>
                                <div class="col-md-10">';

        $iBirthMonth = $person->getBirthMonth();
        $iBirthDay = $person->getBirthDay();
        $iBirthYear = $person->getBirthYear();
        $sBirthDayDate = $iBirthDay . "-" . $iBirthMonth . "-" . $iBirthYear;

        $code .= '<input type="text" name="BirthDayDate" class="date-picker form-control form-control-sm" value="' . OutputUtils::change_date_for_place_holder($sBirthDayDate) . '" maxlength="10" id="BirthDayDate" size="10" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';
        //$code .= '<i class="fa  fa-eye-slash" title="' .  _("Age Hidden") .'"></i>';

        $code .= '
                                </div>
                            </div>';

        if ($person->getFmrId() == 1 or $person->getFmrId() == 2) {
            $dWeddingDate = ($person->getFamily()->getWeddingdate() != null) ? $person->getFamily()->getWeddingdate()->format("Y-M-d") : "";
            $code .= '      <div class="row">
                                <div class="col-md-2">';
        $code .= '                   <i class="fa  fa-birthday-cake" title="' . _("Anniversary") . '"></i> <small>' . _("Anniversary") . '</small>
                                </div>
                                <div class="col-md-10">';
            $code .= '               <input type="text" name="WeddingDate" class="date-picker form-control form-control-sm" value="' . OutputUtils::change_date_for_place_holder($dWeddingDate) . '" maxlength="10" id="WeddingDate" size="10" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';
            $code .= '          </div>';
            $code .= '       </div>';
        }                          

        $bSendNewsLetter = ($person->getSendNewsletter() == 'TRUE');

        $code .= '<hr/><div class="row">
        <div class="form-group col-md-3">
            <label>'. _('Send Newsletter') .':</label>
                    </div>
                    <div class="form-group col-md-4">
                        <input type="checkbox" Name="SendNewsLetter" value="1"
                            '. (($bSendNewsLetter) ? ' checked' : '') .' style="margin-top:10px" id="SendNewsLetter">
                    </div>
                </div>';

        $code .= '          </li>
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
        $code .= '</ul></div>';
        
        return $code;
    }

    public static function getPersonCustomFields (Person $person) : string 
    {
        $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!is_null($person->getFamily()))?$person->getFamily()->getCountry():null, false);

        // Get the lists of custom person fields
        $ormPersonCustomFields = PersonCustomMasterQuery::Create()
        ->orderByCustomOrder()
        ->find();

        // Get the custom field data for this person.
        $rawQry = PersonCustomQuery::create();
        foreach ($ormPersonCustomFields as $customfield) {
            $rawQry->withColumn($customfield->getCustomField());
        }

        if (!is_null($rawQry->findOneByPerId($person->getId()))) {
            $aCustomData = $rawQry->findOneByPerId($person->getId())->toArray();
        }

        $res = '<ul class="list-group list-group-unbordered">';
        // Display the right-side custom fields
        foreach ($ormPersonCustomFields as $rowCustomField) {
            if (!$rowCustomField->getCustomConfirmationDatas()) continue;

                $currentData = trim($aCustomData[$rowCustomField->getCustomField()]);
                if ($currentData != '') {
                    if ($rowCustomField->getTypeId() == 11) {
                        $custom_Special = $sPhoneCountry;
                    } else {
                        $custom_Special = $rowCustomField->getCustomSpecial();
                    }
                    
                    $res .= '<li class="list-group-item">
                        <strong>
                            <i class="fa-li ' . ((($rowCustomField->getTypeId() == 11) ? 'fas fa-phone' : 'fas fa-tag')) . '"></i>'
                            . $rowCustomField->getCustomName() . ':
                        </strong> 
                        <span>' .
                             nl2br(OutputUtils::displayCustomField($rowCustomField->getTypeId(), $currentData, $custom_Special))
                        . '</span>
                    </li>';
                    
                }
                
            }
               
            $res .=' </ul>  ';

            return $res;

    }

    public static function getFamilyCustomFields (Family $family) : string 
    {
        # Todo
        return "";
    }

    public static function getPersonCustomTextFields(Person $person) : array
    {
        $sPhoneCountry = MiscUtils::SelectWhichInfo($person->getCountry(), (!is_null($person->getFamily()))?$person->getFamily()->getCountry():null, false);

        $ormCustomFields = PersonCustomMasterQuery::Create()
            ->orderByCustomOrder()
            ->filterByCustomConfirmationDatas(True)
            ->find();

        $aCustomData = [];

        $bErrorFlag = false;
        $type_ID = 0;

        $aCustomData['per_ID'] = $person->getId();

        foreach ($ormCustomFields as $ormCustomField) {
            $personCustom = PersonCustomQuery::Create()
                ->withcolumn($ormCustomField->getCustomField())
                ->findOneByPerId($person->getId());

            if (!is_null($personCustom)) {                
                $aCustomData[$ormCustomField->getCustomField()] = $personCustom->getVirtualColumn($ormCustomField->getCustomField());
            }
        }

        $code = '<h3>'. _("Custom Person Fields") .'</h3>';
        
        $fields = [];

        foreach ($ormCustomFields as $customField) {
            $code .= '<label>' . $customField->getCustomName(). '</label>
            <br>';
            
            if (array_key_exists($customField->getCustomField(), $aCustomData)) {
                $currentFieldData = trim($aCustomData[$customField->getCustomField()]);
            } else {
                $currentFieldData = '';
            }

            if ($type_ID == 11) {// in the case of a phone number
                $custom_Special = $sPhoneCountry;
            } else {
                $custom_Special = $customField->getCustomSpecial();
            }

            $fields[] = $customField->getCustomField();
            $code .= OutputUtils::formCustomField($customField->getTypeId(), $customField->getCustomField(), $currentFieldData, $custom_Special);
        }

        return [$fields, $code];        
    }

    public static function getPersonForFamilyStandardInfos (Person $person, string $photo) : String {
        $res = '<div class="card card-primary">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-responsive img-circle initials-image"
                                src="data:image/png;base64,' . $photo . '">
                        </div>

                        <h3 class="profile-username text-center">' . $person->getFullName() . '</h3>

                        <p class="text-muted text-center"><i
                                class="fa  fa-' . ($person->isMale() ? "male" : "female") .'"></i> '. $person->getFamilyRoleName() .'
                        </p>

                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">';


        if (!empty($person->getHomePhone())) {
            $res .= '<i class="fa  fa-phone"
                        title="'. _("Home Phone") .'"></i>(H) '. $person->getHomePhone() .'
                    <br/>';
        }
        if (!empty($person->getWorkPhone())) {
        $res .= '<i class="fa  fa-briefcase"
                    title="' . _("Work Phone") . '"></i>(W) '. $person->getWorkPhone() .'
                <br/>';
        }
        if (!empty($person->getCellPhone())) {
            $res .= '<i class="fa  fa-mobile"
                        title="'. _("Mobile Phone") .'"></i>(M) '.  $person->getCellPhone() .'
                    <br/>';
        }

        if (!empty($person->getEmail())) {
            $res .=  '<i class="fa  fa-envelope"
                        title="'. _("Email") . '"></i>(H) ' .  $person->getEmail() . '<br/>';
        }
        if (!empty($person->getWorkEmail())) {
            $res .= '<i class="fa  fa-envelope-o"
                        title="' . _("Work Email") .'"></i>(W) '. $person->getWorkEmail() . '
                    <br/>';
        }

        $res .= '<i class="fa  fa-birthday-cake" title="' . _("Birthday") .'"></i>';

        $birthDate = OutputUtils::FormatBirthDate($person->getBirthYear(), $person->getBirthMonth(), $person->getBirthDay(), '-', 0);
        $res .= $birthDate;
        $res .= '<br/>
                    </li>
                    <li class="list-group-item">';

        $classification = "";
        $cls = ListOptionQuery::create()->filterById(1)->filterByOptionId($person->getClsId())->findOne();
        if (!empty($cls)) {
        $classification = $cls->getOptionName();
        }
        $res .= '<b>Classification:</b> '. $classification .'
                    </li>';
        if (count($person->getPerson2group2roleP2g2rs()) > 0) {
            $res .= '<li class="list-group-item">
                            <h4>' . _("Groups") . '</h4>';

        foreach ($person->getPerson2group2roleP2g2rs() as $groupMembership) {
            if ($groupMembership->getGroup() != null) {
            $listOption = ListOptionQuery::create()->filterById($groupMembership->getGroup()->getRoleListId())->filterByOptionId($groupMembership->getRoleId())->findOne()->getOptionName();

            $res .= '<b>'. $groupMembership->getGroup()->getName() . '</b>: <span
                                        class="pull-right">'. _($listOption) .'</span><br/>';

            }
        }

        $res.= '                  </li>';
        }
        $res.= '          </ul>
                <br/>
                <div class="text-center">
                    <button class="btn btn-danger btn-sm deletePerson" data-id="'. $person->getId() .'" style="height: 30px;padding-top: 5px;background-color: red"><i class="fas fa-trash"></i> '. _("Delete") .'</button>
                    <button class="btn btn-sm modifyPerson" data-id="' . $person->getId() . '" style="height: 30px;padding-top: 5px;"><i class="fas fa-edit"></i> '. _("Modify") .'</button>
                </div>
            </div>
            <!-- /.box-body -->
        </div>';

        return $res;
    }

    public static function getFamilyStandardInfos(Family $family) : String {
        $res = '<i class="fa  fa-map-marker" title="'. _("Home Address") .'"></i>'
            . str_replace("<br>", '<br><i class="fa  fa-map-marker" title="'
            . _("Home Address") .'"></i>', $family->getAddress()) .'<br/>';

        if (!empty($family->getHomePhone())) {
            $res .= '<i class="fa  fa-phone" title="'. _("Home Phone") .'"> </i>(H) '. $family->getHomePhone() .'<br/>';
        }
        if (!empty($family->getEmail())) {
            $res.= '<i class="fa  fa-envelope" title="'. _("Family Email") .'"></i>'. $family->getEmail() .'<br/>';

        }
        if ($family->getWeddingDate() !== null) {
            $res .= '<i class="fa  fa-heart"
                title="'. _("Wedding Date") .'"></i>'. $family->getWeddingDate()->format(SystemConfig::getValue("sDateFormatLong")) .'
                    <br/>';
        }

        $res .= '<i class="fas fa-newspaper"
            title="'. _("Send Newsletter") .'"></i>'. $family->getSendNewsletter() .'<br/>

            <div class="text-left">
                <button class="btn btn-danger btn-sm deleteFamily" data-id="'. $family->getId() .'" style="height: 30px;padding-top: 5px;background-color: red"><i class="fas fa-trash"></i> '. _("Delete") .'</button>
                <button class="btn btn-sm modifyFamily" data-id="'. $family->getId() .'" style="height: 30px;padding-top: 5px;"><i class="fas fa-edit"></i> '. _("Modify") .'</button>
                <button class="btn btn-success btn-sm exitSession" style="height: 30px;padding-top: 5px;background-color: green"><i class="fas fa-sign-out-alt"></i> '. _("Exit") .'</button>
            </div>';

        return $res;        
    }

    public static function getFamilyFullTextFields (Family $family): string {
        

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

        $code .= '<input type="text" name="FamilyName" class="form-control form-control-sm" id="FamilyName" value="' . $sName . '" maxlength="15" id="BirthDayDate" size="50" placeholder="' . SystemConfig::getValue("sDatePickerPlaceHolder") . '">';

        $code .= '</div>
            </div><hr/>';

        $code .= '<div class="row">
            <div class="col-md-2">
                <i class="fa  fa-map-marker" title="' . _("Home Address") . '"></i> <label>' . _('Address') . ' 1:</label>
            </div>
            <div class="col-md-9">
                <input type="text" Name="Address1" id="Address1"
                        value="' . htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                        maxlength="250" class="form-control form-control-sm">
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
                        class="form-control form-control-sm">
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
                <input type="text" class="form-control form-control-sm" name="StateTextbox"
                        value="' . (($sCountry != 'United States' && $sCountry != 'Canada') ? htmlentities(stripslashes($sState), ENT_NOQUOTES, 'UTF-8') : '')
            . '" size="20" maxlength="30">
            </div>
            <div class="form-group col-md-3">
                <label>' . _('Zip') . ':</label>
                <input type="text" Name="Zip" id="Zip" class="form-control form-control-sm"';

        // bevand10 2012-04-26 Add support for uppercase ZIP - controlled by administrator via cfg param
        if (SystemConfig::getBooleanValue('bForceUppercaseZip')) {
            $code .= 'style="text-transform:uppercase" ';
        }
        $code .= 'value="' . htmlentities(stripslashes($sZip), ENT_NOQUOTES, 'UTF-8') . '"
            maxlength="10" size="8">

            </div>';

        $code .= '<div class="row">
            <div class="col-md-2">
                <i class="fa  fa-map-marker" title="' . _("Address 2") . '"></i> <label>' . _('Address') . ' 1:</label>
            </div>
            <div class="col-md-9">
                <input type="text" Name="Address1" id="Address1"
                        value="' . htmlentities(stripslashes($sAddress1), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                        maxlength="250" class="form-control form-control-sm">
            </div>
            </div>';

        $code .= '<div class="form-group col-md-3">
                <label> ' . _('Country') . ':</label>';
        $code .= CountryDropDown::getDropDown($sCountry);

        $code .= '</div>
        </div><hr/>';

        $code .= '<div class="row">
            <div class="col-md-2">
                <i class="fa  fa-map-marker" title="' . _("Home Address") . '"></i>  <label>' . _('Address') . ' 2:</label>
            </div>
            <div class="col-md-9">
                <input type="text" Name="Address2" id="Address2"
                        value="' . htmlentities(stripslashes($sAddress2), ENT_NOQUOTES, 'UTF-8') . '" size="50"
                        maxlength="250" class="form-control form-control-sm">
            </div>
            </div><hr/>';

        $code .= '<br/>

            <div class="row">
                <div class="col-md-1">';
        $code .= '<i class="fa  fa-phone"
                title="' . _("Phone") . '"></i>(H)
            </div>
            <div class="col-md-6">';
        $code .= '<input type="text" name="homePhone" class="form-control form-control-sm" value="' . $sHomePhone . '" id="homePhone" size="30" placeholder="' . _("Cell Phone") . '">';
        $code .= '
                </div>
            </div>
            <div class="row">
                <div class="col-md-1">';

        $code .= '<i class="fa  fa-briefcase"
                    title="' . _("Work Phone") . '"></i>(W)
                </div>
                <div class="col-md-6">';
        $code .= '<input type="text" name="workPhone" class="form-control form-control-sm" value="' . $sWorkPhone . '" id="workPhone" size="30" placeholder="' . _("Work Phone") . '">';
        $code .= '</div>
            </div>
            <div class="row">
                <div class="col-md-1">';
        $code .= '<i class="fa  fa-mobile"
                                        title="' . _("Mobile Phone") . '"></i>(M)
                </div>
                <div class="col-md-6">';
        $code .= '<input type="text" name="cellPhone" class="form-control form-control-sm" value="' . $sCellPhone . '" id="cellPhone" size="30" placeholder="' . _("Cell Phone") . '">';
        $code .= '
                                    </div>
                                </div>
            <div class="row">
                <div class="col-md-1">';
        $code .= '<i class="fa  fa-envelope"
                title="' . _("Family Email") . '"></i>(M)
            </div>
            <div class="col-md-6">';
        $code .= '<input type="text" name="email" class="form-control form-control-sm" value="' . $sEmail . '" id="email" size="30" placeholder="' . _("Cell Phone") . '">';
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
                        <input type="checkbox" Name="SendNewsLetter" id="SendNewsLetter"
                                value="'. (($bSendNewsLetter == "TRUE" or $bSendNewsLetter == 1)?"TRUE":"FALSE").'" ' . (($bSendNewsLetter == "TRUE") ? ' checked' : '') . '>
                </div>
            </div>';

        return $code;
    }

    public static function getSelectedCustomPersonFields() : array
    {
        // Get the list of custom person fields
        $ormPersonCustomFields = PersonCustomMasterQuery::create()
        ->orderByCustomOrder()
        ->find();

        $customPersonFields = []; 

        if ( $ormPersonCustomFields->count() > 0) {
            foreach ($ormPersonCustomFields as $customField) {
                if ($customField->getCustomConfirmationDatas()) {
                    $customPersonFields[] = [
                        'order' => $customField->getCustomOrder(),
                        'custom' => $customField->getCustomField()
                    ];
                }
            }
        }

        return $customPersonFields;
    }

    public static function getSelectedCustomFamilyFields() : array
    {
        # family Custom fields
        // Get the list of custom person fields
        $ormFamilyCustomFields = FamilyCustomMasterQuery::create()
        ->orderByCustomOrder()
        ->find();

        $customFamilyFields = []; 

        if ( $ormFamilyCustomFields->count() > 0) {
            foreach ($ormFamilyCustomFields as $customField) {
                if ($customField->getCustomConfirmationDatas()) {
                    $customFamilyFields[] = [
                        'order' => $customField->getCustomOrder(),
                        'custom' => $customField->getCustomField()
                    ];
                }
            }
        }

        return $customFamilyFields;
    }

}