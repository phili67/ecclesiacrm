<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\dto\ImageTreatment;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Note;
use EcclesiaCRM\Utils\RedirectUtils;

if ( ! (SessionUser::isActive() && SessionUser::getUser()->isEDrive()) ) {
  RedirectUtils::Redirect('members/404.php?type=Upload');
  return;
}


$user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

$privateNoteDir = $userDir = $user->getUserRootDir();
$publicNoteDir  = $user->getUserPublicDir();
$userName       = $user->getUserName();
$currentpath    = $user->getCurrentpath();

$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

$dropDir = $privateNoteDir;
$fileName = $_FILES["upload"]["name"];

switch ($_GET['type']) {
  case 'privateImages':
  case 'privateDocuments':
    $dropDir = $privateNoteDir. "/". $userName . $currentpath ;
    $dropAddress = $protocol."://".$_SERVER['HTTP_HOST']."/api/filemanager/getFile/".$user->getPersonId(). "/". $userName. $currentpath . $fileName;
    break;
  case 'publicImages':
  case 'publicDocuments':
    $dropDir = $publicNoteDir. "/" ;
    $dropAddress = $protocol."://".$_SERVER['HTTP_HOST']."/". $publicNoteDir ."/" . $fileName;
    break;
}

//
// the main part : storing the file
//
header('Content-Type: application/json');


if (file_exists("/".$dropDir."/" . $fileName))
{
 echo json_encode([
    "uploaded" => 1,
    "fileName"=> $fileName,
    "url" => "/".$dropDir."/" . $fileName,
    "error"=> [
        "message"=> "A file with the same name already exists. The uploaded file was renamed to \"foo(2).jpg\"."
    ]
    ],JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
}
else
{
    move_uploaded_file($_FILES["upload"]["tmp_name"],"../".$dropDir."/" . $fileName);


    $rec = ImageTreatment::imageCreateFromAny(dirname(__FILE__)."/../".$dropDir."/" . $fileName);
    ImageTreatment::saveImageCreateFromAny($rec, "../".$dropDir."/" . $fileName);
    imagedestroy($res['image']);


    // now we create the note
    $note = new Note();
    $note->setPerId($user->getPersonId());
    $note->setFamId(0);
    $note->setTitle($fileName);
    $note->setPrivate(1);
    $note->setText($userName . $currentpath . $fileName);
    $note->setType('file');
    $note->setEntered(SessionUser::getUser()->getPersonId());
    $note->setInfo(gettext('Create file'));

    $note->save();

    echo json_encode([
        "uploaded" => 1,
        "fileName" =>  $fileName,
        "url" => $dropAddress
    ],JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
}
