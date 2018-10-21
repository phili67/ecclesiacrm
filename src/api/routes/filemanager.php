<?php

// copyright 2018 Philippe Logel All rights reserved nor MIT licence

// Users APIs
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\User;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;

function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}


$app->group('/filemanager', function () {
    
    $this->post('/{personID:[0-9]+}', function ($request, $response, $args) {
      $user = UserQuery::create()->findPk($args['personID']);
      
      if (is_null($user)) {// in the case the user is null
        echo "{\"files\":[]}";
        exit;
      }
      
      $realNoteDir = $userDir = $user->getUserRootDir();
      $userName    = $user->getUserName();
      $currentpath = $user->getCurrentpath();
  
      $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath;
      
      $result = [];
      $files = array_diff(scandir($currentNoteDir), array('.','..','.DS_Store','._.DS_Store'));
      foreach ($files as $file) {
        if ($file[0] == '.') {
          continue;
        }
        
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        $note = NoteQuery::Create()->findOneByText($userName.$currentpath.$file);
        
        $item['isShared'] = 0;
        $item['id']       = 0;
        $item['perID']    = 0;
        
        if (!is_null($note)) {
          $item['id']         = $note->getId();
          $item['isShared']   = $note->isShared();
          $item['perID']      = $note->getPerId();
        }
        
        $item['name']  = $file;
        $item['date']  = date(SystemConfig::getValue("sDateFormatLong"),filemtime($currentNoteDir."/".$file));
        $item['type']  = $extension;
        $item['size']  = MiscUtils::FileSizeConvert(filesize($currentNoteDir."/".$file));
        $item['icon']  = MiscUtils::FileIcon ($file);
        $item['path']  = $userName.$currentpath.$file;
        
        $item['dir']  = false;
        if (is_dir("$currentNoteDir/$file")) {
          $item['name'] = "/".$file;
          $item['dir']  = true;
          $item['icon'] = 'fa-folder-o text-yellow';
          $item['type'] = gettext("Folder");
        }
        
        $item['icon'] = "<i class='fa " . $item['icon'] . " fa-2x'></i>";
        
        $result[] = $item;
      }      
    
      echo "{\"files\":".json_encode($result)."}";
    });
    
    $this->get('/getFile/{personID:[0-9]+}/[{path:.*}]', function ($request, $res, $args) {
          $user = UserQuery::create()->findPk($args['personID']);
          $name = $request->getAttribute('path');
          
          
          if ( !is_null($user) ) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName    = $user->getUserName();
            $currentpath = $user->getCurrentpath();
  
            $searchLikeString = $name.'%';
            $note = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->findOne();
            
            if ( !is_null($note) && ( $note->isShared() > 0 || $_SESSION['user']->isAdmin() ) ) {
              $file = dirname(__FILE__)."/../../".$realNoteDir."/".$name;
          
              $response = $res->withHeader('Content-Description', 'File Transfer')
                 ->withHeader('Content-Type', 'application/octet-stream')
                 ->withHeader('Content-Disposition', 'attachment;filename="'.basename($file).'"')
                 ->withHeader('Expires', '0')
                 ->withHeader('Cache-Control', 'must-revalidate')
                 ->withHeader('Pragma', 'public')
                 ->withHeader('Content-Length', filesize($file));

              readfile($file);
          
              return $response;
            }
          }
          
          return $res->withStatus(404); 
    });

    $this->post('/changeFolder', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->folder) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $user->setCurrentpath($user->getCurrentpath().substr($params->folder, 1)."/");
              $user->save();

              return $response->withJson(['success' => true, "currentPath" => MiscUtils::pathToPathWithIcons($user->getCurrentpath())]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    $this->post('/folderBack', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $currentPath = $user->getCurrentpath();
              
              $len = strlen($currentPath);
              
              for ($i=$len-2;$i>0;$i--) {
                if ($currentPath[$i] == "/") {
                  break;
                }
              }
              
              $currentPath = substr($currentPath,0,$i+1);
              
              if ($currentPath == '') {
                $currentPath = "/";
              }
              
              $user->setCurrentpath($currentPath);
              
              $user->save();

              return $response->withJson(['success' => true, "currentPath" => MiscUtils::pathToPathWithIcons($currentPath), "isHomeFolder" => ($currentPath=="/")?true:false]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    $this->post('/deleteOneFolder', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->folder) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
  
              $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->folder;
              
              $searchLikeString = $userName.$currentpath.substr($params->folder,1).'%';
              $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
              if ( $notes->count() > 0 ) {
                $notes->delete();
              }
                    
              $ret = MiscUtils::delTree($currentNoteDir);

              return $response->withJson(['success' => $searchLikeString]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });

    $this->post('/deleteOneFile', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->file) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
  
              $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->file;
              
              $searchLikeString = $userName.$currentpath.$params->file.'%';
              $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
              if ( $notes->count() > 0 ) {
                $notes->delete();
              }
                    
              $ret = unlink ($currentNoteDir);

              return $response->withJson(['success' => $ret,"file" => $searchLikeString]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    $this->post('/deleteFiles', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->files) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
              
              foreach ($params->files as $file) {
                if ($file[0] == '/') {
                  // we're in a case of a folder
                  $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$file;
                  
                  if (MiscUtils::delTree($currentNoteDir)) {
                    $searchLikeString = $userName.$currentpath.$params->file.'%';
                    $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
                    if ( $notes->count() > 0 ) {
                      $notes->delete();
                    }
                  }
                } else {
                  // in the case of a file
                  $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$file;
                  
                  if (unlink ($currentNoteDir)) {
                      $searchLikeString = $userName.$currentpath.$params->file.'%';
                      $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
                      if ( $notes->count() > 0 ) {
                        $notes->delete();
                      }
                  }
                }
              }

              return $response->withJson(['success' => true]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });


    $this->post('/movefiles', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->folder) && isset ($params->files) ) {
          $user = UserQuery::create()->findPk($params->personID);
          
          if (!is_null($user)) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName    = $user->getUserName();
            $currentpath = $user->getCurrentpath();
              
            $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->files;
            
            foreach ($params->files as $file) {
              if ($file[0] == '/') {
                // we're in a case of a folder
                $currentDest = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$file;
                $newDest = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.substr($params->folder,1).$file;
                
                mkdir($newDest, 0755, true);
                
                if (rename($currentDest,$newDest)) {
                  $searchLikeString = $userName.$currentpath.$file.'%';
                  $notes = NoteQuery::Create()->filterByPerId ($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();
              
                  if ( $notes->count() > 0 ) {
                    foreach ($notes as $note) {
                      // we have to change all the files
                      $fileName = basename($note->getText());
                      
                      if ($params->folder == '/..') {
                        $dirname = dirname($currentpath);
                      } else {
                        $dirname = $currentpath.substr($params->folder,1);
                      }
                      
                      if ($params->type == 'file') {
                        $note->setText($dirname."/".$fileName);
                      } else {
                        // in the case of a folder
                        $note->setText($dirname."/".$fileName);
                      }
                      
                      $note->setEntered($_SESSION['user']->getPersonId());
                      $note->save();
                    }
                  }
                }
              } else {
                $currentDest = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$file;
                $newDest = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.substr($params->folder,1)."/".$file;
                
                if (rename($currentDest,$newDest)) {
                  $searchLikeString = $userName.$currentpath.$file.'%';
                  $notes = NoteQuery::Create()->filterByPerId ($params->personID)->filterByText ($searchLikeString, Criteria::LIKE)->find();
              
                  if ( $notes->count() > 0 ) {
                    foreach ($notes as $note) {
                      // we have to change all the files
                      $fileName = basename($note->getText());
                      
                      if ($params->folder == '/..') {
                        $dirname = $userName.dirname($currentpath);
                      } else {
                        $dirname = $userName.$currentpath.substr($params->folder,1)."/";
                      }
                      
                      if ($params->type == 'file') {
                        $note->setText($dirname.$fileName);
                      } else {
                        // in the case of a folder
                        $note->setText($dirname.$fileName);
                      }
                      
                      $note->setEntered($_SESSION['user']->getPersonId());
                      $note->save();
                    }
                  }
                }
              }
            }
           
            return $response->withJson(['success' => true]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    $this->post('/newFolder', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->folder) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
              
              $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->folder;
              
              // now we create the note
              $note = new Note();
              $note->setPerId($params->personID);
              $note->setFamId(0);
              $note->setTitle($params->folder);
              $note->setPrivate(1);
              $note->setText($userName.$currentpath.$params->folder);
              $note->setType('folder');
              $note->setEntered($_SESSION['user']->getPersonId());
              $note->setInfo(gettext('New Folder'));
        
              $note->save();
              
              mkdir($currentNoteDir, 0755, true);

              return $response->withJson(['success' => $currentNoteDir]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    $this->post('/rename', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->oldName) && isset ($params->newName) && isset ($params->type) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
              $extension   = pathinfo($params->oldName, PATHINFO_EXTENSION); 
              
              $oldName = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->oldName;
              $newName = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->newName.(($params->type == 'file')?".".$extension:"");
              
              if (rename($oldName, $newName)) {
                $searchLikeString = $userName.$currentpath.$params->oldName.'%';
                $notes = NoteQuery::Create()->filterByPerId ($params->personID)->filterByText($searchLikeString, Criteria::LIKE)->find();
              
                if ( $notes->count() > 0 ) {
                  foreach ($notes as $note) {
                    // we have to change all the files
                    $oldName = $note->getText();
                    if ($params->type == 'file') {
                      $note->setText($userName.$currentpath.$params->newName.".".$extension);
                    } else {
                      // in the case of a folder
                      $note->setText($userName.$currentpath.$params->newName);
                    }
                    
                    $note->setEntered($_SESSION['user']->getPersonId());
                    $note->save();
                  }
                }
                
                return $response->withJson(['success' => true]);
              }
          }
        }
        
        return $response->withJson(['success' => false]);
    });
    
    
    $this->post('/uploadFile/{personID:[0-9]+}', function ($request, $response, $args) {
        $user = UserQuery::create()->findPk($args['personID']);
        
        $realNoteDir = $userDir = $user->getUserRootDir();
        $userName    = $user->getUserName();
        $currentpath = $user->getCurrentpath();
        
        if (!isset($_FILES['noteInputFile'])) {
          return $response->withJson(['success' => "failed"]);
        }
        
        $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath;
        
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
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->setInfo(gettext('Create file'));
          
            $note->save();
          
            //return $response->withJson(['success' => "success"]);
          }
        }

        return $response->withJson(['success' => "failed"]);
    });
});
