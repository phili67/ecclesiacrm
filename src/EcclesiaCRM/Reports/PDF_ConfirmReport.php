<?php

namespace EcclesiaCRM\Reports;

use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Reports\ChurchInfoReportTCPDF;
use EcclesiaCRM\Utils\OutputUtils;

use EcclesiaCRM\PersonCustomMasterQuery;
use EcclesiaCRM\FamilyCustomMasterQuery;
use EcclesiaCRM\PersonCustomQuery;
use EcclesiaCRM\FamilyCustomQuery;
use EcclesiaCRM\FamilyQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\GroupQuery;
use EcclesiaCRM\Token;
use EcclesiaCRM\TokenPassword;
use EcclesiaCRM\TokenQuery;

use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\dto\SystemURLs;

use EcclesiaCRM\Map\PersonTableMap;
use EcclesiaCRM\Map\ListOptionTableMap;
use EcclesiaCRM\Map\GroupTableMap;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

use EcclesiaCRM\Utils\InputUtils;
use Propel\Runtime\ActiveQuery\Criteria;

class PDF_ConfirmReport extends ChurchInfoReportTCPDF
{
    private $incrY;
    public $leftX;
    public $_PersonCustom;
    public $_FamilyCustom;
    private $exportType;

    // Constructor
    public function __construct()
    {
        parent::__construct('P', 'mm', $this->paperFormat);
        $this->leftX = 10;
        $this->incrY = SystemConfig::getValue('incrementY') + 0.5;
        $this->SetFont('Times', '', 10);
        $this->SetMargins(10, 20);
        $this->exportType = "family";

        $this->SetAutoPageBreak(false);
    }

    public function AddPersonCustomField($order, $use)
    {
        $this->_PersonCustom[(int)$order] = $use;
    }

    public function GetPersonCustomField($order) {
        if (!array_key_exists($order, $this->_PersonCustom)) return  0;
        
        return $this->_PersonCustom[$order];
    }

    public function AddFamilyCustomField($order, $use)
    {
        $this->_FamilyCustom[(int)$order] = $use;
    }

    public function GetFamilyCustomField($order) {
        if (!array_key_exists($order, $this->_FamilyCustom)) return  0;

        return $this->_FamilyCustom[$order];
    }

    public function StartNewPage($ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $type)
    {
        $curY = $this->StartLetterPage($ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, 'graphic', $type);
        $curY += 2 * $this->incrY;
        $blurb = SystemConfig::getValue('sConfirm1');
        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $blurb);
        $curY += 2 * $this->incrY;

        return $curY;
    }

    private function create_QR_Code($url) : string
    {
        $writer = new PngWriter();

// Create QR code
        $qrCode = QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(70)
            ->setMargin(5)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

// Create generic label
        $label = Label::create('EcclesiaCRM')
            ->setTextColor(new Color(0, 0, 0));
            //->setBackgroundColor(new Color(0, 0, 0));

        $result = $writer->write($qrCode, null);//, $label);

        // Save it to a file
        $result->saveToFile('../tmp_attach/qrcode_'.$groupID."_".$personId.'.png');

        return '../tmp_attach/qrcode_'.$groupID."_".$personId.'.png';
    }

    private function onLineVerifyWithLink($curY, $person=null, $family=null) : int
    {
        if ($this->exportType == "person") {
            $verifyType = "verifyPerson";
            $id = $person->getId();
        } else {
            $verifyType = "verifyFamily";
            $id = $family->getId();
        }

        TokenQuery::create()->filterByType($verifyType)->filterByReferenceId($id)->delete();
        $token = new Token();
        $token->build($verifyType, $id);
        $token->save();

        $tokenPassword = new TokenPassword();

        $password = MiscUtils::random_password(8);

        $tokenPassword->setTokenId($token->getPrimaryKey());
        $tokenPassword->setPassword(md5($password));
        $tokenPassword->setMustChangePwd(false);

        $tokenPassword->save();        

        if (!is_null($person)) {
            $emails = [$person->getEmail()];


            $person->setConfirmReport('Pending');
            $person->save();
            
            $myTokens = [
                "Subject" => _("Person"). " : ". $person->getFirstName() . " ". $person->getLastName() . " (" . gettext("Please verify your informations").")",
                "verificationToken" => $token->getToken(),
                "body" => "",//SystemConfig::getValue("sConfirm1"),
                "login" => _("Login") .' : '. $emails[0],
                "password" => _("Password") .' : '. $password
            ];
            $link = SystemURLs::getHost()."/ident/my-profile/".$token->getToken();
        } else if (!is_null($family)) {
            $emails = [];

            foreach ($headPeople as $headPerson) {
                $emails[] = $headPerson->getEmail();
            }

            // in the case there isn't any headPeople
            if (count($emails) == 0) {
                $emails = $family->getEmails();
            }   
            
            $family->setConfirmReport('Pending');
            $family->save();
            
            $myTokens = [
                "Subject" => _("Family"). " : ". $family->getName() . " (" . gettext("Please verify your family's information").")",
                "verificationToken" => $token->getToken(),
                "body" => "",//SystemConfig::getValue("sConfirm1"),
                "login" => _("Login") .' : '. $emails[0],
                "password" => _("Password") .' : '. $password
            ];
            
            $link = SystemURLs::getHost()."/ident/my-profile/".$token->getToken();

        }        
        
        $link = _("Link") . " : " . $link;

        // the qr code part !!!
        // 1. Optionnel : Définir le style (couleur et épaisseur)
        $style = array('width' => 0.85, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(10, 10, 10));

        // 2. Récupérer la position X actuelle pour commencer aux marges de la page
        $x = $this->GetX();
        $y = $this->GetY(); // Position verticale actuelle
        $w = $this->getPageWidth() - $x; // Largeur disponible jusqu'à la marge droite

        if ($this->useQRCode) {
            // 3. Tracer la ligne
            $this->Line($x, $curY + 9, $w, $curY  + 9, $style);

            $curY += 1.7 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $myTokens['body']);
            $curY += 1.5 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $myTokens['Subject']);
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $link);
                
            $qrCodePath = $this->create_QR_Code($link);

            $this->Image($qrCodePath, SystemConfig::getValue('leftX')+9, $curY + 4, $this->_Height*0.20, $this->_Height*0.20);

            unlink ($qrCodePath);
                

            $curY += 7 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $myTokens['login']);
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $myTokens['password']);

        }                

        return $curY;                    
    }

    public function FinishPage($curY, $person = null, $family = null)
    {
        if (SystemConfig::getValue('sConfirm2') != '') {
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm2'));
        }

        if (SystemConfig::getValue('sConfirm3') != '') {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm3'));
        }

        if (SystemConfig::getValue('sConfirm4') != '') {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm4'));
        }

        if (SystemConfig::getValue('sConfirm5') != '') {
            $curY += 3 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm5'));
            $curY += 2 * $this->incrY;
        }
        if (SystemConfig::getValue('sConfirm6') != '') {
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirm6'));
        }

        // we add the qr code with the link to the family record in CRM
        $curY = $this->onLineVerifyWithLink($curY, $person, $family);
        
        //If the Reports Settings Menu's SystemConfig::getValue("sConfirmSigner") is set, then display the closing statement.  Hide it otherwise.
        if (SystemConfig::getValue('sConfirmSigner')) {
            $curY += 2 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSincerely').',');
            $curY += 1 * $this->incrY;
            $this->WriteAt(SystemConfig::getValue('leftX'), $curY, SystemConfig::getValue('sConfirmSigner'));
        }
    }

    public function run(): void
    {        
        if (isset($_SESSION['POST_Datas'])) {
            $_POST = $_SESSION['POST_Datas'];
            unset($_SESSION['POST_Datas']);
        }

        $this->exportType = 'family';

        if (isset($_POST['letterandlabelsnamingmethod'])) {
            $this->exportType = $_POST['letterandlabelsnamingmethod'];
        }
        
        if (isset($_POST['useQRCode'])) {
            $this->useQRCode = $_POST['useQRCode'] == "1" ? 1 : 0;
        }

        $minAge = 18;
        if (isset($_POST['minAge'])) {
            $minAge = InputUtils::FilterInt($_POST['minAge']);
        }

        $maxAge = 130;
        if (isset($_POST['maxAge'])) {
            $maxAge = InputUtils::FilterInt($_POST['maxAge']);
        }

        $classList = "*";
        if (isset($_POST['classList'])) {
            $classList = $_POST['classList'];
        }

        $ageWhere = "TIMESTAMPDIFF(YEAR, STR_TO_DATE(CONCAT(" . PersonTableMap::COL_PER_BIRTHYEAR . ", '-', LPAD(" . PersonTableMap::COL_PER_BIRTHMONTH . ", 2, '0'), '-', LPAD(" . PersonTableMap::COL_PER_BIRTHDAY . ", 2, '0')), '%Y-%m-%d'), CURDATE()) BETWEEN " . (int)$minAge . " AND " . (int)$maxAge;

        // Instantiate the directory class and build the report.
        $filename = 'ConfirmReport'.date(SystemConfig::getValue("sDateFilenameFormat")).'.pdf';

        // Get the list of custom person fields
        $ormPersonCustomFields = PersonCustomMasterQuery::create()
            ->orderByCustomOrder()
            ->find();

        $numPersonCustomFields = $ormPersonCustomFields->count();

        $sPersonCustomFieldName = [];
        $sPersonCustomFieldTypeID = [];

        if ( $ormPersonCustomFields->count() > 0) {
            $iFieldNum = 0;
            foreach ($ormPersonCustomFields as $customField) {
                $sPersonCustomFieldName[$iFieldNum] = $customField->getCustomName();
                $sPersonCustomFieldTypeID[$iFieldNum] = $customField->getTypeId();
                $iFieldNum+=1;

                if ($customField->getCustomConfirmationDatas()) {
                    $this->AddPersonCustomField( $customField->getCustomOrder(), $customField->getCustomField() );
                }
            }
        }

        // Get the list of custom family fields
        $ormFamilyCustomFields = FamilyCustomMasterQuery::create()
            ->orderByCustomOrder()
            ->find();

        $numFamilyCustomFields = $ormFamilyCustomFields->count();

        $sFamilyCustomFieldName = [];
        $sFamilyCustomFieldTypeID = [];

        if ( $ormFamilyCustomFields->count() > 0) {
            $iFieldNum = 0;
            foreach ($ormFamilyCustomFields as $customField) {
                $sFamilyCustomFieldName[$iFieldNum] = $customField->getCustomName();
                $sFamilyCustomFieldTypeID[$iFieldNum] = $customField->getTypeId();
                $iFieldNum+=1;

                if ($customField->getCustomConfirmationDatas()) {
                    $this->AddFamilyCustomField( $customField->getCustomOrder(), $customField->getCustomField() );
                }
            }
        }


        $ormFamilies = FamilyQuery::create();
        $ormFamilies->orderByName();

        $perIds = $families = NULL;
        
        if (isset($_GET['familyId']) and !empty($_GET['familyId'])) {
            $families = explode(",", $_GET['familyId']);               
        } else if (isset($_POST['familiesId']) and !empty($_POST['familiesId'])) {
            $families = explode(",", $_POST['familiesId']);
        }

        if (isset($_GET['personId']) and !empty($_GET['personId'])) {
            $this->exportType = 'person';
            $perIds = [(int)$_GET['personId']];

            $per = PersonQuery::create()->findOneById($perIds[0]);
            $families = [$per->getFamily()->getId()];
        } else if (isset($_POST['personsId']) and !empty($_POST['personsId'])) {
            $this->exportType = 'person';
            $perIds = [(int)$_POST['personsId']];

            $per = PersonQuery::create()->findOneById($perIds[0]);
            $families = [$per->getFamily()->getId()];
        }

        if (!is_null($families)) {
            $ormFamilies->filterById($families); 
        }

        $ormFamilies->filterByDateDeactivated(NULL);

        // Get all the families
        $ormFamilies->find();

        $dataCol = 55;
        $dataWid = 65;

        //$arr = $ormFamilies->toArray();

        // Loop through families

        $incrYAdd = 0.0;// +0.5
        $fontSize = 8;

        $incrY = SystemConfig::getValue('incrementY')+$incrYAdd;

        $cnt = 0;

        foreach ($ormFamilies as $family) {
            //If this is a report for a single family, name the file accordingly.
            if ($_GET['familyId']) {
                $filename = 'ConfirmReport-'.$family->getName().'.pdf';
            }

            if ($this->exportType == "family") {
                $cnt += 1;
                $curY = $this->StartNewPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), $this->exportType);
                $curY += $incrY;
            
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Name'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getName());
                $curY += $incrY;
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress1());
                $curY += $incrY;
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
                $curY += $incrY;
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress2());
                $curY += $incrY;
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getHomePhone());
                $curY += $incrY;
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Send Newsletter'));
                $this->SetFont('Times', '', $fontSize);

                $this->WriteAtCell($dataCol, $curY, $dataWid, "");
                if ($family->getSendNewsletter() == 'FALSE') {
                    $this->CheckBox('newsletterFamily'.$family->getId(), 5, false, array(), array(), 'No', $dataCol, $curY);
                } else {
                    $this->CheckBox('newsletterFamily'.$family->getId(), 5, true, array(), array(), 'Yes', $dataCol, $curY);
                }
                
                $curY += $incrY;

                // Missing the following information from the Family record:
                // Wedding date (if present) - need to figure how to do this with sensitivity
                // Family e-mail address

                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
                $curY += $incrY;

                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Family Email'));
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getEmail());
                $curY += $incrY;
                
                // family custom fields : 
                $rawQry = FamilyCustomQuery::create();
                foreach ($ormFamilyCustomFields as $customField) {
                    $rawQry->withColumn($customField->getCustomField());
                }

                if (!is_null($rawQry->findOneByFamId($family->getId()))) {
                    $aCustomData = $rawQry->findOneByFamId($family->getId())->toArray();
                }

                foreach ($ormFamilyCustomFields as $customField) {
                    if ($this->GetFamilyCustomField($customField->getCustomOrder()) == 0) continue;

                    if ($sFamilyCustomFieldName[$customField->getCustomOrder() - 1]) {
                        $currentFieldData = trim($aCustomData[$customField->getCustomField()]);

                        $currentFieldData = OutputUtils::displayCustomField($customField->getTypeId(), trim($aCustomData[$customField->getCustomField()]), $customField->getCustomSpecial(), false);

                        $this->SetFont('Times', 'B', $fontSize);
                        $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), $sFamilyCustomFieldName[$customField->getCustomOrder() - 1]);
                        $this->SetFont('Times', '', $fontSize);
                        
                        if ($currentFieldData == '') {
                            $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getEmail());
                        } else {
                            $this->WriteAtCell($dataCol, $curY, $dataWid, $currentFieldData);                
                        }
                        $curY += $incrY;                    
                    }
                }

                $curY += $incrY;
                $curY += $incrY;
            }

            //Get the family members for this family
            $ormFamilyMembers = PersonQuery::create()
                ->filterByDateDeactivated(NULL)
                ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
                ->addMultipleJoin(array(
                        array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                        array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                    )
                    , Criteria::LEFT_JOIN)
                ->addAsColumn('ClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
                ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
                ->addMultipleJoin(array(
                        array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                        array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                    )
                    , Criteria::LEFT_JOIN)
                ->addAsColumn('FamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
                ->filterByFamId($family->getId())
                ->orderByFmrId();

            if ($classList != "*") {
                $ormFamilyMembers->filterByClsId($classList);
            }

            if ($minAge != 0 or $maxAge != 130) {
                $ormFamilyMembers->where($ageWhere);
            }

            $ormFamilyMembers->find();

            if ($ormFamilyMembers->count() == 0) {
                $ormFamilyMembers = PersonQuery::create()
                    ->filterByDateDeactivated(NULL)
                    ->filterByClsId($classList)
                    ->addAlias('cls', ListOptionTableMap::TABLE_NAME)
                    ->addMultipleJoin(array(
                            array(PersonTableMap::COL_PER_CLS_ID, ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_OPTIONID)),
                            array(ListOptionTableMap::Alias("cls",ListOptionTableMap::COL_LST_ID), 1)
                        )
                        , Criteria::LEFT_JOIN)
                    ->addAsColumn('ClassName', ListOptionTableMap::alias('cls', ListOptionTableMap::COL_LST_OPTIONNAME))
                    ->addAlias('fmr', ListOptionTableMap::TABLE_NAME)
                    ->addMultipleJoin(array(
                            array(PersonTableMap::COL_PER_FMR_ID, ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONID)),
                            array(ListOptionTableMap::Alias("fmr",ListOptionTableMap::COL_LST_ID), 2)
                        )
                        , Criteria::LEFT_JOIN)
                    ->addAsColumn('FamRole', ListOptionTableMap::alias('fmr', ListOptionTableMap::COL_LST_OPTIONNAME))
                    ->where($ageWhere)
                ->findByFamId($family->getId());
            }

            $XName = 10;
            $XGender = 40;
            $XRole = 50;
            $XEmail = 80;
            $XBirthday = 125;
            $XHideAge = 145;
            $XCellPhone = 155;
            $XClassification = 180;
            $XWorkPhone = 155;
            $XRight = 208;

            $numFamilyMembers = 0;
            
            foreach ($ormFamilyMembers as $fMember) {
                if ( is_null ($fMember) or !is_null($perIds) and !in_array($fMember->getId(), $perIds) ) continue;

                $numFamilyMembers++;    // add one to the people count

                // Make sure the person data will display with adequate room for the trailer and group information
                if (($curY + $numPersonCustomFields * $incrY) > 260 and $this->exportType == "family") {
                    $curY = $this->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), "", $this->exportType);            
                } else if ($this->exportType == "person") {
                    $cnt += 1;
                    $curY = $this->StartNewPage($fMember->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(),$family->getState(), $family->getZip(), $family->getCountry(), $this->exportType);

                    $curY += $incrY;  

                    // place the first table
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Name'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getlastName());
                    $curY += $incrY;
                    // place the first table
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('First Name'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getFirstName());
                    $curY += $incrY;
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 1'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress1());
                    $curY += $incrY;
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('City, State, Zip'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, ($family->getCity().', '.$family->getState().'  '.$family->getZip()));
                    $curY += $incrY;
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Address 2'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, $family->getAddress2());
                    $curY += $incrY;
                    $this->SetFont('Times', 'B', $fontSize);
                    $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Home Phone'));
                    $this->SetFont('Times', '', $fontSize);
                    $this->WriteAtCell($dataCol, $curY, $dataWid, $fMember->getHomePhone());

                    // Missing the following information from the Family record:
                    // Wedding date (if present) - need to figure how to do this with sensitivity
                    // Family e-mail address
                    if ($fMember->getFmrId() == 1 or $fMember->getFmrId() == 2) {
                        $curY += $incrY;    
                        $this->SetFont('Times', 'B', $fontSize);
                        $this->WriteAtCell(SystemConfig::getValue('leftX'), $curY, $dataCol - SystemConfig::getValue('leftX'), _('Anniversary Date'));
                        $this->SetFont('Times', '', $fontSize);
                        $this->WriteAtCell($dataCol, $curY, $dataWid, OutputUtils::FormatDate((!is_null($family->getWeddingdate())?$family->getWeddingdate()->format('Y-m-d'):'')));
                        $curY += $incrY;
                    }

                    $curY += $incrY;    
                    $curY += $incrY;
                }

                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell($XName, $curY, $XGender - $XName, _('Member Name'));
                $this->WriteAtCell($XGender, $curY, $XRole - $XGender, _('M/F'));
                $this->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Adult/Child'));
                $this->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, _('Email'));
                $this->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, _('Birthday'));
                $this->SetFont('Times', 'B', 5);
                $this->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, _('Hide Age'), "LTR");
                $this->SetFont('Times', 'B', $fontSize);            
                $this->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, substr(_('Cell phone'),0,10).".");
                $this->WriteAtCell($XClassification, $curY, $XRight - $XClassification, _('Work Phone'));
                $this->SetFont('Times', '', $fontSize);
                $curY += $incrY;

                $iPersonID = $fMember->getId();
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell($XName, $curY, $XGender - $XName, $fMember->getFirstName().' '.$fMember->getMiddleName().' '.$fMember->getLastName());
                $this->SetFont('Times', '', $fontSize);
                $genderStr = ($fMember->getGender() == 1 ? 'M' : 'F');
                $this->WriteAtCell($XGender, $curY, $XRole - $XGender, $genderStr);
                $this->WriteAtCell($XRole, $curY, $XEmail - $XRole, $fMember->getFamRole());
                $this->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $fMember->getEmail());
                if ($fMember->getBirthYear()) {
                    $theDate = new \DateTime($fMember->getBirthYear().'-'.$fMember->getBirthMonth().'-'.$fMember->getBirthDay(), new \DateTimeZone(SystemConfig::getValue('sTimeZone')));
                    $birthdayStr = $theDate->format(SystemConfig::getValue("sDatePickerFormat"));
                } elseif ($fMember->getBirthMonth()) {
                    $birthdayStr = $fMember->getBirthMonth().'-'.$fMember->getBirthDay();
                } else {
                    $birthdayStr = '';
                }
                //If the "HideAge" check box is true, then create a Yes/No representation of the check box.
                if ($fMember->getFlags()) {
                    $hideAgeStr = _('Yes');
                } else {
                    $hideAgeStr = _('No');
                }

                $this->WriteAtCell($XBirthday, $curY, $XHideAge - $XBirthday, $birthdayStr);
                $this->WriteAtCell($XHideAge, $curY, $XCellPhone - $XHideAge, $hideAgeStr);
                $this->WriteAtCell($XCellPhone, $curY, $XClassification - $XCellPhone, $fMember->getCellPhone());
                $this->WriteAtCell($XClassification, $curY, $XRight - $XClassification, $fMember->getWorkPhone());

                $curY += $incrY;
                $curY += $incrY;
                
                // Missing the following information for the personal record: ??? Is this the place to put this data ???
                // Work Phone
                $this->SetFont('Times', 'B', $fontSize);
                $this->WriteAtCell($XName, $curY, $XEmail - $XGender, _('Send Newsletter'), "0");
                $this->WriteAtCell($XGender, $curY, $XBirthday - $XEmail, "", "0");
                if ($fMember->getSendNewsletter() == 'FALSE') {
                    $this->CheckBox('newsletterPerson'.$fMember->getId(), 5, false, array(), array(), 'No', $XGender, $curY);
                } else {
                    $this->CheckBox('newsletterPerson'.$fMember->getId(), 5, true, array(), array(), 'Yes', $XGender, $curY);
                }

                
                $this->WriteAtCell($XRole, $curY, $XEmail - $XRole, _('Classification'), "0", "R");
                $this->SetFont('Times', '', $fontSize);
                $this->WriteAtCell($XEmail, $curY, $XBirthday - $XEmail, $fMember->getClassName(), "0");

                $curY += $incrY;
                $curY += $incrY;

                // *** All custom fields ***
                // Get the list of custom person fields

                $xSize = 40;
                if ($numPersonCustomFields > 0) {
                    // Get the custom field data for this person.
                    $rawQry = PersonCustomQuery::create();
                    foreach ($ormPersonCustomFields as $custField) {
                        $rawQry->withColumn($custField->getCustomField());
                    }

                    if (!is_null($rawQry->findOneByPerId($iPersonID))) {
                        $aCustomData = $rawQry->findOneByPerId($iPersonID)->toArray();
                    }

                    //$numCustomData = $aCustomData);
                    $OutStr = '';
                    $xInc = $XName;    // Set the starting column for Custom fields
                    // Here is where we determine if space is available on the current page to
                    // display the custom data and still get the ending on the page
                    // Calculations (without groups) show 84 mm is needed.
                    // For the Letter size of 279 mm, this says that curY can be no bigger than 195 mm.
                    // Leaving 12 mm for a bottom margin yields 183 mm.
                    $numWide = 0;    // starting value for columns
                    foreach ($ormPersonCustomFields as $custField) {

                        if ($this->GetPersonCustomField($custField->getCustomOrder()) == 0) continue;

                        if ($sPersonCustomFieldName[$custField->getCustomOrder() - 1]) {
                            $currentFieldData = trim($aCustomData[$custField->getCustomField()]);

                            $currentFieldData = OutputUtils::displayCustomField($custField->getTypeId(), trim($aCustomData[$custField->getCustomField()]), $custField->getCustomSpecial(), false);

                            if ($sPersonCustomFieldTypeID[$custField->getCustomOrder() - 1] == 1) {
                                $this->SetFont('Times', 'B', $fontSize);
                                $this->WriteAtCell($xInc, $curY, $xSize, $sPersonCustomFieldName[$custField->getCustomOrder() - 1]);
                                $this->SetFont('Times', '', $fontSize);
                                $this->WriteAtCell($xInc + $xSize, $curY, $xSize, "");
                                if (is_null($currentFieldData) or $currentFieldData  == '' or $currentFieldData == 'FALSE') {
                                    $this->CheckBox('props'.$custField->getId(), 5, false, array(), array(), 'No', $xInc + $xSize, $curY);
                                } else {
                                    $this->CheckBox('props'.$custField->getId(), 5, true, array(), array(), 'Yes', $xInc + $xSize, $curY);
                                }
                            } else {                    
                                $OutStr = $sPersonCustomFieldName[$custField->getCustomOrder() - 1].' : '.$currentFieldData.'    ';
                                $this->SetFont('Times', 'B', $fontSize);
                                $this->WriteAtCell($xInc, $curY, $xSize, $sPersonCustomFieldName[$custField->getCustomOrder() - 1]);

                                $this->SetFont('Times', '', $fontSize);
                                if ($currentFieldData == '') {
                                    $this->WriteAtCell($xInc + $xSize, $curY, $xSize, '');                        
                                } else {
                                    $this->WriteAtCell($xInc + $xSize, $curY, $xSize, $currentFieldData);
                                }
                            }                    
                            
                            $numWide += 1;    // increment the number of columns done
                            $xInc += (2 * $xSize);    // Increment the X position by about 1/2 page width
                            if (($numWide % 2) == 0) { // 2 columns
                                $xInc = $XName;    // Reset margin
                                $curY += $incrY;
                            }
                        }
                    }
                    //$this->WriteAt($XName,$curY,$OutStr);
                    //$curY += (2 * SystemConfig::getValue("incrementY"));
                }
                $curY += 2 * $incrY;

                if ($this->exportType == "person") {
                    $ormAssignedGroups = GroupQuery::create()
                        ->leftJoinPerson2group2roleP2g2r()
                        ->withColumn('person2group2role_p2g2r.PersonId', 'memberCount')
                        ->addAlias('role', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array('person2group2role_p2g2r.RoleId', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("role",ListOptionTableMap::COL_LST_ID), GroupTableMap::COL_GRP_ROLELISTID)
                            )
                            , Criteria::LEFT_JOIN)
                        ->addAsColumn('RoleName', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONNAME))
                        ->where('person2group2role_p2g2r.PersonId = '.$fMember->getId())
                        ->orderByName()
                        ->find();


                    if ($ormAssignedGroups->count() > 0) {
                        $groupStr = _("Assigned groups for")." ".$fMember->getFirstName().' '.$fMember->getLastName().': ';

                        foreach ($ormAssignedGroups as $group) {
                            $groupStr .= $group->getName().' ('._($group->getRoleName()).') ';
                        }
                        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                        $curY += 2 * $incrY;
                    }

                    $this->FinishPage($curY, $fMember, null);
                }
            }
            //

            $curY += $incrY;

            if (($curY + 2 * $numFamilyMembers * $incrY) >= 260 and $this->exportType == "family") {
                $curY = $this->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), "", $this->exportType);
            }

            if ($this->exportType == "family") {
                $ormFamilyMembers = PersonQuery::create()
                    ->filterByFamId($family->getId())
                    ->orderByFmrId()
                    ->find();

                foreach ($ormFamilyMembers as $aMember) {
                    // Get the Groups this Person is assigned to
                    $ormAssignedGroups = GroupQuery::create()
                        ->leftJoinPerson2group2roleP2g2r()
                        ->withColumn('person2group2role_p2g2r.PersonId', 'memberCount')
                        ->addAlias('role', ListOptionTableMap::TABLE_NAME)
                        ->addMultipleJoin(array(
                                array('person2group2role_p2g2r.RoleId', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONID)),
                                array(ListOptionTableMap::Alias("role",ListOptionTableMap::COL_LST_ID), GroupTableMap::COL_GRP_ROLELISTID)
                            )
                            , Criteria::LEFT_JOIN)
                        ->addAsColumn('RoleName', ListOptionTableMap::alias('role', ListOptionTableMap::COL_LST_OPTIONNAME))
                        ->where('person2group2role_p2g2r.PersonId = '.$aMember->getId())
                        ->orderByName()
                        ->find();


                    if ($ormAssignedGroups->count() > 0) {
                        $groupStr = _("Assigned groups for")." ".$aMember->getFirstName().' '.$aMember->getLastName().': ';

                        foreach ($ormAssignedGroups as $group) {
                            $groupStr .= $group->getName().' ('._($group->getRoleName()).') ';
                        }
                        $this->WriteAt(SystemConfig::getValue('leftX'), $curY, $groupStr);
                        $curY += 2 * $incrY;
                    }

                }

                if ($curY > 183) {    // This insures the trailer information fits continuously on the page (3 inches of "footer"
                    $curY = $this->StartLetterPage($family->getId(), $family->getName(), $family->getAddress1(), $family->getAddress2(), $family->getCity(), $family->getState(), $family->getZip(), $family->getCountry(), '', $this->exportType);
                }

                $this->FinishPage($curY, null, $family);
            }
        }

        header('Pragma: public');  // Needed for IE when using a shared SSL certificate
        ob_end_clean();
        if (SystemConfig::getValue('iPDFOutputType') == 1) {
            $this->Output($filename, 'D');
        } else {
            $this->Output();
        }
    }
}
