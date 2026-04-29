<?php

require '../Include/Config.php';
require '../Include/Functions.php';

use EcclesiaCRM\dto\ImageTreatment;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\SessionUser;
use EcclesiaCRM\Note;
use EcclesiaCRM\Utils\DocumentSecurityUtils;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\Utils\RedirectUtils;

header('Content-Type: application/json');

function uploadErrorResponse(string $message, int $statusCode = 400): void
{
  http_response_code($statusCode);
  echo json_encode([
    'uploaded' => 0,
    'error' => [
      'message' => $message
    ]
  ], JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
  exit;
}

function uploadSuccessResponse(string $fileName, string $url, ?string $warningMessage = null): void
{
  $payload = [
    'uploaded' => 1,
    'fileName' => $fileName,
    'url' => $url
  ];

  if (!is_null($warningMessage)) {
    $payload['error'] = [
      'message' => $warningMessage
    ];
  }

  echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
  exit;
}

if ( ! (SessionUser::isActive() && SessionUser::getUser()->isEDrive()) ) {
  RedirectUtils::Redirect('members/404.php?type=Upload');
  return;
}


$user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());

if (is_null($user)) {
  uploadErrorResponse('User account not found.', 403);
}

$privateNoteDir = $userDir = $user->getUserRootDir();
$publicNoteDir  = $user->getUserPublicDir();
$userName       = $user->getUserName();
$currentpath    = DocumentSecurityUtils::normalizeDirectoryPath($user->getCurrentpath());
$rootPath       = rtrim(SystemURLs::getRootPath(), '/');
$documentRoot   = SystemURLs::getDocumentRoot();

if (is_null($currentpath)) {
  uploadErrorResponse('Invalid target path.', 400);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  uploadErrorResponse('Invalid request method.', 405);
}

if (!isset($_GET['type'])) {
  uploadErrorResponse('Missing upload type.', 400);
}

if (!isset($_FILES['upload']) || !is_array($_FILES['upload'])) {
  uploadErrorResponse('No uploaded file received.', 400);
}

$allowedTypes = ['privateImages', 'privateDocuments', 'publicImages', 'publicDocuments'];
$uploadType = (string)$_GET['type'];

if (!in_array($uploadType, $allowedTypes, true)) {
  uploadErrorResponse('Invalid upload type.', 400);
}

$uploadFile = $_FILES['upload'];

if (($uploadFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  uploadErrorResponse('Upload problem !!!', 400);
}

if (empty($uploadFile['tmp_name']) || !is_uploaded_file($uploadFile['tmp_name'])) {
  uploadErrorResponse('Invalid uploaded file.', 400);
}

$originalFileName = (string)($uploadFile['name'] ?? '');
$fileName = DocumentSecurityUtils::sanitizeUploadFileName($originalFileName);

if (is_null($fileName)) {
  uploadErrorResponse('Invalid file name.', 400);
}

$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!DocumentSecurityUtils::isAllowedUpload($fileName, $uploadFile['tmp_name'])) {
  uploadErrorResponse('This file type is not allowed.', 400);
}

$dropDir = $privateNoteDir;
$dropAddress = '';
$targetDirectory = '';
$notePath = $userName . $currentpath;

switch ($uploadType) {
  case 'privateImages':
  case 'privateDocuments':
    $dropDir = trim($privateNoteDir, '/') . '/' . trim($userName, '/') . $currentpath;
    $targetDirectory = DocumentSecurityUtils::resolveDirectoryFromRelativeBase($documentRoot, $privateNoteDir, '/' . trim($userName, '/') . $currentpath);
    break;
  case 'publicImages':
  case 'publicDocuments':
    $dropDir = trim($publicNoteDir, '/') . '/';
    $targetDirectory = DocumentSecurityUtils::resolveDirectoryFromRelativeBase($documentRoot, $publicNoteDir);
    break;
}

if (is_null($targetDirectory)) {
  uploadErrorResponse('Target directory is invalid.', 400);
}

$targetFilePath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;
$storedFileName = $fileName;
$warningMessage = null;

$targetDirectoryRelative = trim($dropDir, '/');

if (file_exists($targetFilePath)) {
  $pathParts = pathinfo($fileName);
  $baseName = $pathParts['filename'];
  $newFileName = $baseName . '-' . MiscUtils::gen_uuid();

  if ($extension !== '') {
    $newFileName .= '.' . $extension;
  }

  $storedFileName = $newFileName;
  $targetFilePath = $targetDirectory . DIRECTORY_SEPARATOR . $storedFileName;
  $warningMessage = 'A file with the same name already exists. The uploaded file was renamed automatically.';
}

if (!move_uploaded_file($uploadFile['tmp_name'], $targetFilePath)) {
  uploadErrorResponse('Upload problem !!!', 500);
}

$rec = ImageTreatment::imageCreateFromAny($targetFilePath);
if ($rec != false) {
  ImageTreatment::saveImageCreateFromAny($rec, $targetFilePath);
}

$note = new Note();
$note->setPerId($user->getPersonId());
$note->setFamId(0);
$note->setTitle($storedFileName);
$note->setPrivate(1);
$note->setText($notePath . $storedFileName);
$note->setType('file');
$note->setEntered(SessionUser::getUser()->getPersonId());
$note->setInfo(gettext('Create file'));
$note->save();

if ($uploadType === 'privateImages' || $uploadType === 'privateDocuments') {
  $dropAddress = $rootPath . '/api/filemanager/getFile/' . $user->getPersonId() . '/' . DocumentSecurityUtils::encodeUrlPath($notePath . $storedFileName);
} else {
  $dropAddress = $rootPath . '/' . DocumentSecurityUtils::encodeUrlPath($targetDirectoryRelative . '/' . $storedFileName);
}

uploadSuccessResponse($storedFileName, $dropAddress, $warningMessage);
