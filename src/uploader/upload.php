<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\ImageTreatment;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Note;
use EcclesiaCRM\Utils\MiscUtils;
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


if (file_exists(SystemURLs::getDocumentRoot()."/".$dropDir."/" . $fileName))
{
  $path_parts = pathinfo($fileName);
  $dirname = $path_parts['dirname'];
  $basename = $path_parts['basename'];
  $ext =  $path_parts['extension'];
  $fileName = $path_parts['filename'];  

  $newFileName = $fileName . MiscUtils::gen_uuid().".".$ext;
  $dropAddress = $protocol."://".$_SERVER['HTTP_HOST']."/". $dropDir . $newFileName;

  $ret = move_uploaded_file($_FILES["upload"]["tmp_name"], "../".$dropDir."/" . $newFileName);

  echo json_encode([
      "uploaded" => 1,
      "fileName" =>  $fileName,
      "url" => $dropAddress,
      "error"=> [
          "message"=> "A file with the same name already exists. The uploaded file was renamed to \"foo(2).jpg\"."
      ]
      ],JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);

    exit;
}
else
{
    if ( move_uploaded_file($_FILES["upload"]["tmp_name"],"../".$dropDir."/" . $fileName) ){
      $rec = ImageTreatment::imageCreateFromAny(dirname(__FILE__)."/../".$dropDir."/" . $fileName);
      if ($rec != false) {
        ImageTreatment::saveImageCreateFromAny($rec, "../".$dropDir."/" . $fileName);
        imagedestroy($rec['image']);
      }

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

      exit;

    } else {
      echo json_encode([
        "uploaded" => 0,
        "error"=> [
            "message"=> "Upload problem !!!"
        ]
      ],JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);

      exit;
    }
}
