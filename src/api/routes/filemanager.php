<?php

// Users APIs
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\User;
use EcclesiaCRM\dto\SystemConfig;
use EcclesiaCRM\Note;
use EcclesiaCRM\NoteQuery;
use EcclesiaCRM\Utils\MiscUtils;
use Propel\Runtime\ActiveQuery\Criteria;


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
        
        if (!is_null($note)) {
        $item['id']   = $note->getId();
        }
        $item['name'] = $file;
        $item['date'] = date(SystemConfig::getValue("sDateFormatLong"),filemtime($currentNoteDir."/".$file));
        $item['type'] = $extension;
        $item['size'] = MiscUtils::FileSizeConvert(filesize($currentNoteDir."/".$file));
        $item['icon'] = MiscUtils::FileIcon ($file);
        $item['path'] = $realNoteDir."/".$userName.$currentpath."/".$file;
        
        $item['dir']  = false;
        if (is_dir("$currentNoteDir/$file")) {
          $item['name'] = "/".$file;
          $item['dir']  = true;
          $item['icon'] = 'fa-folder-open text-yellow';
          $item['type'] = gettext("Folder");
        }
        
        $item['icon'] = "<i class='fa " . $item['icon'] . " fa-2x'></i>";
        
        $result[] = $item;
      }      
    
      echo "{\"files\":".json_encode($result)."}";
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
    
    $this->post('/deleteFolder', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->folder) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
  
              $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->folder;
              
              $searchLikeString = $userName.$params->folder.'%';
              $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
              if ( $notes->count() > 0 ) {
                $notes->delete();
              }
                    
              $ret = MiscUtils::delTree($currentNoteDir);

              return $response->withJson(['success' => $currentNoteDir]);
          }
        }
        
        return $response->withJson(['success' => false]);
    });

    $this->post('/deleteFile', function ($request, $response, $args) {
        $params = (object)$request->getParsedBody();
        
        if (isset ($params->personID) && isset ($params->file) ) {
   
          $user = UserQuery::create()->findPk($params->personID);
          if (!is_null($user)) {
              $realNoteDir = $userDir = $user->getUserRootDir();
              $userName    = $user->getUserName();
              $currentpath = $user->getCurrentpath();
  
              $currentNoteDir = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$params->file;
              
              $searchLikeString = $userName.$params->file.'%';
              $notes = NoteQuery::Create()->filterByText($searchLikeString, Criteria::LIKE)->find();
              
              if ( $notes->count() > 0 ) {
                $notes->delete();
              }
                    
              $ret = unlink ($currentNoteDir);

              return $response->withJson(['success' => $ret,"file" => $currentNoteDir]);
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
              
              mkdir($currentNoteDir, 0755, true);

              return $response->withJson(['success' => $currentNoteDir]);
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
        
        $fileName = basename($_FILES["noteInputFile"]["name"]);

        $target_file = $currentNoteDir . $fileName;
        
  
        if (move_uploaded_file($_FILES['noteInputFile']['tmp_name'], $target_file)) {
          $target_file = $target_dir . $currentpath . $fileName;
          
          // now we create the note
          $note = new Note();
          $note->setPerId($args['personID']);
          $note->setFamId(0);
          $note->setTitle($fileName);
          $note->setPrivate(1);
          $note->setText($userName.str_replace($target_dir,"",$target_file));
          $note->setType('file');
          $note->setEntered($_SESSION['user']->getPersonId());
          $note->setInfo(gettext('Create file'));
          
          $note->save();
          
          return $response->withJson(['success' => "success"]);
        }

        return $response->withJson(['success' => "failed"]);
    });
});
