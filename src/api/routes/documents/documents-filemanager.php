<?php

// copyright 2018 Philippe Logel All rights reserved not MIT licence
use Slim\Http\Response as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

// Documents filemanager APIs
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\PersonQuery;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\dto\SystemURLs;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\SessionUser;

$app->group('/filemanager', function (RouteCollectorProxy $group) {

    $group->post('/{personID:[0-9]+}', 'getAllFileNoteForPerson' );
    $group->get('/getFile/{personID:[0-9]+}/[{path:.*}]', 'getRealFile' );
    $group->post('/getPreview', 'getPreview' );
    $group->post('/changeFolder', 'changeFolder' );
    $group->post('/folderBack', 'folderBack' );
    $group->post('/deleteOneFolder', 'deleteOneFolder' );
    $group->post('/deleteOneFile', 'deleteOneFile' );
    $group->post('/deleteFiles', 'deleteFiles' );
    $group->post('/movefiles', 'movefiles' );
    $group->post('/newFolder', 'newFolder' );
    $group->post('/rename', 'renameFile' );
    $group->post('/uploadFile/{personID:[0-9]+}', 'uploadFile' );
    $group->post('/getRealLink', 'getRealLink' );
    $group->post('/setpathtopublicfolder', 'setpathtopublicfolder' );

});

function reArrayFiles(&$file_post)
{

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

function numberOfFiles($personID)
{
    $user = UserQuery::create()->findPk($personID);

    if (is_null($user)) {// in the case the user is null
        return 0;
    }

    $realNoteDir = $userDir = $user->getUserRootDir();
    $userName = $user->getUserName();
    $currentpath = $user->getCurrentpath();

    $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath;

    $files = array_diff(scandir($currentNoteDir), array('.', '..', '.DS_Store', '._.DS_Store'));

    return count($files);
}

function getAllFileNoteForPerson(Request $request, Response $response, array $args)
{
    $user = UserQuery::create()->findPk($args['personID']);

    $realUserID = SessionUser::getUser()->getPersonId();

    if (is_null($user) || $realUserID != $args['personID']) {// in the case the user is null
        return $response->withJson(["files" => [] ]);
    }

    $realNoteDir = $userDir = $user->getUserRootDir();
    $userName = $user->getUserName();
    $currentpath = $user->getCurrentpath();

    $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath;

    $result = [];
    $files = array_diff(scandir($currentNoteDir), array('.', '..', '.DS_Store', '._.DS_Store'));
    foreach ($files as $file) {
        if ($file[0] == '.') {
            continue;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $note = NoteQuery::Create()->filterByPerId($args['personID'])->findOneByText($userName . $currentpath . $file);

        $item['isShared'] = 0;
        $item['id'] = 0;
        $item['perID'] = 0;// by default the file longs to the owner

        if (!is_null($note)) {
            $item['id'] = $note->getId();
            $item['isShared'] = $note->isShared();
            $item['perID'] = $note->getPerId();
        } else {
            $fileName = basename($file);

            // now we create the note
            $note = new Note();
            $note->setPerId($args['personID']);
            $note->setFamId(0);
            $note->setTitle($fileName);
            $note->setPrivate(1);
            $note->setText($userName . $currentpath . $fileName);
            $note->setType('file');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(gettext('Create file'));

            $note->save();

            $item['id'] = $note->getId();
            $item['isShared'] = $note->isShared();
            $item['perID'] = $note->getPerId();
        }

        $item['name'] = $file;
        $item['date'] = date(SystemConfig::getValue("sDateFormatLong"), filemtime($currentNoteDir . "/" . $file));
        $item['type'] = $extension;
        $item['size'] = MiscUtils::FileSizeConvert(filesize($currentNoteDir . "/" . $file));
        $item['icon'] = MiscUtils::FileIcon($file);
        $item['path'] = $userName . $currentpath . $file;

        $item['dir'] = false;
        if (is_dir("$currentNoteDir/$file")) {
            $item['name'] = "/" . $file;
            $item['dir'] = true;
            $item['icon'] = 'fa-folder-o text-yellow';
            $item['type'] = gettext("Folder");
        }

        $item['icon'] = "<i class='fa " . $item['icon'] . " fa-2x'></i>";

        $result[] = $item;
    }

    return $response->withJson(["files" => $result ]);
}

function getRealFile(Request $request, Response $res, array $args)
{
    $user = UserQuery::create()->findPk($args['personID']);
    $name = $request->getAttribute('path');

    $per = PersonQuery::Create()->findOneById($args['personID']);

    if (!is_null($user)) {
        $realNoteDir = $userDir = $user->getUserRootDir();
        $userName = $user->getUserName();
        $currentpath = $user->getCurrentpath();

        $searchLikeString = $name . '%';
        $searchLikeString = str_replace("//", "/", $searchLikeString);

        $note = NoteQuery::Create()->filterByPerId($args['personID'])->filterByText($searchLikeString, Criteria::LIKE)->findOne();

        if (is_null($note)) {
            $fileName = basename($name);

            // now we create the note
            $note = new Note();
            $note->setPerId($args['personID']);
            $note->setFamId(0);
            $note->setTitle($fileName);
            $note->setPrivate(1);
            $note->setText($userName . $currentpath . $fileName);
            $note->setType('file');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(gettext('Create file'));

            $note->save();
        }

        if (!is_null($note) && ($note->isShared() > 0 || SessionUser::getUser()->isAdmin()
                || SessionUser::getUser()->getPersonId() == $args['personID']
                || $per->getFamId() == SessionUser::getUser()->getPerson()->getFamId())) {
            $file = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . MiscUtils::convertUTF8AccentuedString2Unicode($name);

            if (!file_exists($file)) {// in the case the file name isn't in unicode format
                $file = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $name;
            }

            $response = $res->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment;filename="' . basename($file) . '"')
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate')
                ->withHeader('Pragma', 'public')
                ->withHeader('Content-Length', filesize($file));

            readfile($file);

            return $response;
        }
    }

    return $res->withStatus(404);
}

function getPreview(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->name)) {
        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $userName = $user->getUserName();
            $currentPath = $user->getCurrentpath();
            $extension = pathinfo($params->name, PATHINFO_EXTENSION);

            if (!(
                strtolower($extension) == 'mp4' || strtolower($extension) == 'mov' || strtolower($extension) == 'ogg' || strtolower($extension) == 'm4a'
                || strtolower($extension) == 'txt' || strtolower($extension) == 'ps1' || strtolower($extension) == 'c' || strtolower($extension) == 'cpp'
                || strtolower($extension) == 'php' || strtolower($extension) == 'js' || strtolower($extension) == 'mm' || strtolower($extension) == 'vcf'
                || strtolower($extension) == 'pdf' || strtolower($extension) == 'mp3' || strtolower($extension) == 'py' || strtolower($extension) == 'ru'
                || strtolower($extension) == 'm' || strtolower($extension) == 'vbs' || strtolower($extension) == 'admx' || strtolower($extension) == 'adml'
                || strtolower($extension) == 'ics' || strtolower($extension) == 'csv' || strtolower($extension) == 'sql' || strtolower($extension) == 'docx'
            )) {
                return $response->withJson(['success' => true, 'path' => MiscUtils::simpleEmbedFiles(SystemURLs::getRootPath() . "/api/filemanager/getFile/" . $params->personID . "/" . $userName . $currentPath . $params->name)]);
            } else {
                $realNoteDir = $userDir = $user->getUserRootDir();
                return $response->withJson(['success' => true, 'path' => MiscUtils::simpleEmbedFiles(SystemURLs::getRootPath() . "/api/filemanager/getFile/" . $params->personID . "/" . $userName . $currentPath . $params->name, SystemURLs::getRootPath() . "/" . $user->getUserRootDir() . "/" . $userName . $currentPath . $params->name)]);
            }
        }
    }

    return $response->withStatus(404);
}

function changeFolder(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->folder)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $user->setCurrentpath($user->getCurrentpath() . substr($params->folder, 1) . "/");
            $user->save();

            $_SESSION['user'] = $user;

            return $response->withJson(['success' => true, "currentPath" => MiscUtils::pathToPathWithIcons($user->getCurrentpath()), "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function folderBack(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $currentPath = $user->getCurrentpath();

            $len = strlen($currentPath);

            for ($i = $len - 2; $i > 0; $i--) {
                if ($currentPath[$i] == "/") {
                    break;
                }
            }

            $currentPath = substr($currentPath, 0, $i + 1);

            if ($currentPath == '') {
                $currentPath = "/";
            }

            $user->setCurrentpath($currentPath);

            $user->save();

            $_SESSION['user'] = $user;

            return $response->withJson(['success' => true, "currentPath" => MiscUtils::pathToPathWithIcons($currentPath), "isHomeFolder" => ($currentPath == "/") ? true : false, "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function deleteOneFolder(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->folder)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->folder;

            $searchLikeString = $userName . $currentpath . substr($params->folder, 1) . '%';
            $searchLikeString = str_replace("//", "/", $searchLikeString);
            $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

            if ($notes->count() > 0) {
                $notes->delete();
            }

            $ret = MiscUtils::delTree($currentNoteDir);

            return $response->withJson(['success' => $ret, "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function deleteOneFile(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->file)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . MiscUtils::convertUTF8AccentuedString2Unicode($params->file);

            if (!file_exists($currentNoteDir)) {// in the case the file name isn't in unicode format
                $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->file;
            }

            $searchLikeString = $userName . $currentpath . $params->file . '%';
            $searchLikeString = str_replace("//", "/", $searchLikeString);
            $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

            if ($notes->count() > 0) {
                $notes->delete();
            }

            $ret = unlink($currentNoteDir);

            return $response->withJson(['success' => $ret, "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function deleteFiles(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->files)) {

        $error = [];

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            foreach ($params->files as $file) {
                if ($file[0] == '/') {
                    // we're in a case of a folder
                    $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $file;

                    $currentNoteDir = str_replace("//", "/", $currentNoteDir);

                    if ($currentpath . $file == "//public") {
                        $error[] = _("You can't erase the public folder !");
                        continue;
                    }

                    if (MiscUtils::delTree($currentNoteDir)) {
                        $searchLikeString = $userName . $currentpath . $file . '%';
                        $searchLikeString = str_replace("//", "/", $searchLikeString);

                        $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

                        if ($notes->count() > 0) {
                            $notes->delete();
                        }
                    }
                } else {
                    // in the case of a file
                    $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $file;

                    $currentNoteDir = str_replace("//", "/", $currentNoteDir);

                    $utf8Test = MiscUtils::convertUTF8AccentuedString2Unicode($currentNoteDir);
                    if (file_exists($utf8Test)) {// in the case the file name isn't in unicode format
                        $currentNoteDir = $utf8Test;
                    }

                    if (unlink($currentNoteDir)) {
                        $searchLikeString = $userName . $currentpath . $file . '%';
                        $searchLikeString = str_replace("//", "/", $searchLikeString);

                        $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

                        if ($notes->count() > 0) {
                            $notes->delete();
                        }
                    }
                }
            }

            return $response->withJson(['success' => true, "numberOfFiles" => numberOfFiles($params->personID), 'error' => $error]);
        }
    }

    return $response->withJson(['success' => false]);
}

function movefiles(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->folder) && isset ($params->files)) {
        $user = UserQuery::create()->findPk($params->personID);

        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->files;

            foreach ($params->files as $file) {
                if ($file[0] == '/') {
                    // we're in a case of a folder
                    // $file is a folder here
                    $currentDest = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $file;
                    $newDest = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . substr($params->folder, 1) . $file;

                    if (strpos($newDest, $userName . "/public/../") > 0) {
                        $newDest = str_replace("/public/../", "/", $newDest);
                    }

                    if (is_dir($newDest)) {
                        return $response->withJson(['success' => false, "message" => gettext("A Folder") . " \"" . substr($file, 1) . "\" " . gettext("already exists at this place.")]);
                        break;
                    }

                    mkdir($newDest, 0755, true);

                    if (rename($currentDest, $newDest)) {
                        $searchLikeString = $userName . $currentpath . substr($file, 1) . '%';
                        $searchLikeString = str_replace("//", "/", $searchLikeString);

                        $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

                        if ($notes->count() > 0) {
                            if ($params->folder == '/..') {
                                // we're goaing back
                                $dropDir = $userName . dirname($currentpath) . "/";
                            } else {
                                // the new currentPath
                                $dropDir = $userName . $currentpath . substr($params->folder, 1) . "/";
                            }

                            $dropDir = str_replace("//", "/", $dropDir);

                            foreach ($notes as $note) {
                                // we have to change all the files and the folders
                                $rest = str_replace($userName . $currentpath, "", $note->getText());

                                $note->setText($dropDir . $rest);

                                if ($note->getType() == 'folder') {
                                    $note->setInfo(gettext('Folder modification'));
                                } else {
                                    $note->setInfo(gettext('File modification'));
                                }

                                $note->setEntered(SessionUser::getUser()->getPersonId());
                                $note->save();
                            }
                        }
                    }
                } else {
                    $currentDest = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $file;
                    $newDest = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . substr($params->folder, 1) . "/" . $file;

                    if (strpos($newDest, $userName . "/public/../") > 0) {
                        $newDest = str_replace("/public/../", "/", $newDest);
                    }


                    if (file_exists($newDest)) {
                        return $response->withJson(['success' => false, "message" => gettext("A File") . " \"" . $file . "\" " . gettext("already exists at this place.")]);
                        break;
                    }

                    if (rename($currentDest, $newDest)) {
                        $searchLikeString = $userName . $currentpath . $file . '%';
                        $searchLikeString = str_replace("//", "/", $searchLikeString);
                        $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();

                        if ($notes->count() > 0) {
                            // we have to change all the files
                            if ($params->folder == '/..') {
                                // we're going back
                                $dropDir = $userName . dirname($currentpath) . "/";
                            } else {
                                // the new currentPath
                                $dropDir = $userName . $currentpath . substr($params->folder, 1) . "/";
                            }

                            $dropDir = str_replace("//", "/", $dropDir);

                            foreach ($notes as $note) {
                                $rest = str_replace($userName . $currentpath, "", $note->getText());

                                $note->setText($dropDir . $rest);
                                $note->setInfo(gettext('File modification'));
                                $note->setEntered(SessionUser::getUser()->getPersonId());
                                $note->save();
                            }
                        }
                    }
                }
            }

            return $response->withJson(['success' => true, "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function newFolder(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->folder)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();

            $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->folder;

            if (is_dir($currentNoteDir)) {
                return $response->withJson(['success' => false, "message" => gettext("A Folder") . " \"" . $params->folder . "\" " . gettext("already exists at this place.")]);
            }

            // now we create the note
            $note = new Note();
            $note->setPerId($params->personID);
            $note->setFamId(0);
            $note->setTitle($params->folder);
            $note->setPrivate(1);
            $note->setText($userName . $currentpath . $params->folder);
            $note->setType('folder');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(gettext('New Folder'));

            $note->save();

            mkdir($currentNoteDir, 0755, true);

            return $response->withJson(['success' => $currentNoteDir, "numberOfFiles" => numberOfFiles($params->personID)]);
        }
    }

    return $response->withJson(['success' => false]);
}

function renameFile(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->oldName) && isset ($params->newName) && isset ($params->type)) {

        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();
            $extension = pathinfo($params->oldName, PATHINFO_EXTENSION);

            $oldName = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . MiscUtils::convertUTF8AccentuedString2Unicode($params->oldName);
            if (!file_exists($oldName)) {// in the case the file name isn't in unicode format
                $oldName = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->oldName;
            }
            $newName = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath . $params->newName . (($params->type == 'file') ? "." . $extension : "");

            if (rename($oldName, $newName)) {
                $searchLikeString = $userName . $currentpath . $params->oldName;

                $oldDir = $searchLikeString = str_replace("//", "/", $searchLikeString);

                $notes = NoteQuery::Create()->filterByPerId($params->personID)->filterByText($searchLikeString . '%', Criteria::LIKE)->find();

                if ($notes->count() > 0) {
                    foreach ($notes as $note) {
                        // we have to change all the files
                        $oldName = $note->getText();
                        if ($params->type == 'file') {
                            $note->setText($userName . $currentpath . $params->newName . "." . $extension);
                        } else {
                            // in the case of a folder
                            $newDir = $userName . $currentpath . $params->newName;
                            $newDir = str_replace("//", "/", $newDir);

                            $note->setText(str_replace($oldDir, $newDir, $oldName));
                        }

                        $note->setEntered(SessionUser::getUser()->getPersonId());
                        $note->save();
                    }
                }

                return $response->withJson(['success' => true, "numberOfFiles" => numberOfFiles($params->personID)]);
            }
        }
    }

    return $response->withJson(['success' => false]);
}

function uploadFile(Request $request, Response $response, array $args)
{
    $user = UserQuery::create()->findPk($args['personID']);

    $realNoteDir = $userDir = $user->getUserRootDir();
    $userName = $user->getUserName();
    $currentpath = $user->getCurrentpath();

    if (!isset($_FILES['noteInputFile'])) {
        return $response->withJson(['success' => "failed"]);
    }

    $currentNoteDir = dirname(__FILE__) . "/../../../" . $realNoteDir . "/" . $userName . $currentpath;

    $file_ary = reArrayFiles($_FILES['noteInputFile']);

    foreach ($file_ary as $file) {

        $fileName = basename($file["name"]);

        $target_file = $currentNoteDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // now we create the note
            $note = new Note();
            $note->setPerId($args['personID']);
            $note->setFamId(0);
            $note->setTitle($fileName);
            $note->setPrivate(1);
            $note->setText($userName . $currentpath . $fileName);
            $note->setType('file');
            $note->setEntered(SessionUser::getUser()->getPersonId());
            $note->setInfo(gettext('Create file'));

            $note->save();
        }
    }

    return $response->withJson(['success' => true, "numberOfFiles" => numberOfFiles($args['personID'])]);
}

function getRealLink(Request $request, Response $response, array $args)
{
    $params = (object)$request->getParsedBody();

    if (isset ($params->personID) && isset ($params->pathFile)) {
        $user = UserQuery::create()->findPk($params->personID);
        if (!is_null($user)) {

            $userName = $user->getUserName();
            $currentpath = $user->getCurrentpath();
            $privateNoteDir = $user->getUserRootDir();
            $publicNoteDir = $user->getUserPublicDir();
            $fileName = basename($params->pathFile);
            $publicDir = $user->getUserName() . "/public/";

            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';

            if (strpos($params->pathFile, $publicDir) === false) {
                $dropAddress = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/api/filemanager/getFile/" . $user->getPersonId() . "/" . $userName . $currentpath . $fileName;
            } else {
                $fileName = str_replace($publicDir, "", $params->pathFile);
                $dropAddress = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/" . $publicNoteDir . "/" . $fileName;
            }

            return $response->withJson(['success' => "success", "privateNoteDir" => $privateNoteDir, "publicNoteDir" => $publicNoteDir, 'fileName' => $fileName, "address" => $dropAddress]);
        }
    }

    return $response->withJson(['success' => "failed"]);
}

function setpathtopublicfolder(Request $request, Response $response, array $args)
{
    $currentpath = SessionUser::getUser()->getCurrentpath();

    if (strpos($currentpath, "/public/") === false) {
        $user = UserQuery::create()->findPk(SessionUser::getUser()->getPersonId());
        $user->setCurrentpath("/public/");
        $user->save();

        $_SESSION['user'] = $user;

        return $response->withJson(['success' => "failed"]);
    }

    return $response->withJson(['success' => "success"]);
}
