<?php
//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

// Person APIs
use EcclesiaCRM\PersonQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use EcclesiaCRM\Utils\MiscUtils;
use EcclesiaCRM\CKEditorTemplatesQuery;
use EcclesiaCRM\CKEditorTemplates;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Note;

$app->group('/ckeditor', function () {
    // search person by Name
    
    $this->get('/{personId:[0-9]+}/templates', function ($request, $response, $args) {      
      $templates = CKEditorTemplatesQuery::Create()->findByPersonID($args['personId']);
      
      $templatesArr = [];
      foreach ($templates as $template) {
        $elt = ['title' => $template->getTitle(), 
        'description' => $template->getDesc(), 
        'html' => $template->getText(), 
        'image' => $template->getImage(),
        'id' => $template->getId()];
        array_push($templatesArr, $elt);
      }
      
      $the_real_templates = json_encode($templatesArr);
      
      return "// Register a template definition set named \"default\".
CKEDITOR.addTemplates( 'default',
{
  // The name of the subfolder that contains the preview images of the templates.
  imagesPath : CKEDITOR.getUrl( CKEDITOR.plugins.getPath( 'templates' ) + 'templates/images/' ),

  // Template definitions.
  templates :".$the_real_templates."
});";
    });
    
    
     $this->post('/alltemplates', function ($request, $response, $args) {
      
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->personID) ) {
        $templates = CKEditorTemplatesQuery::Create()->findByPersonID($input->personID);
      
        $templatesArr = [];
        foreach ($templates as $template) {
          $elt = ['title' => $template->getTitle(), 
          'description' => $template->getDesc(), 
          'html' => $template->getText(), 
          'image' => $template->getImage(),
          'id' => $template->getId()];
          array_push($templatesArr, $elt);
        }
      
        return $response->withJson($templatesArr);
      } 
      
      return $response->withJson(['status' => 'failed']);
    });
    
    
    $this->post('/deletetemplate', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->templateID) ) {
        $template = CKEditorTemplatesQuery::Create()->findOneByID($input->templateID);
      
        $template->delete();
      
        return $response->withJson(['status' => 'success']);
      }
      
      return $response->withJson(['status' => 'failed']);
    });

    $this->post('/renametemplate', function ($request, $response, $args) {
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->templateID) && isset ($input->title) && isset ($input->desc) ) {
        $template = CKEditorTemplatesQuery::Create()->findOneByID($input->templateID);
        
        $template->setTitle($input->title);
        $template->setDesc($input->desc);
        $template->setImage("template".rand(1, 3).".gif");
      
        $template->save();
      
        return $response->withJson(['status' => 'success']);
      }
      
      return $response->withJson(['status' => 'failed']);
    });
    
    
    $this->post('/savetemplate', function ($request, $response, $args) {      
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->personID) && isset ($input->title) && isset ($input->desc) && isset ($input->text) ) {
        $template = new CKEditorTemplates();
      
        $template->setPersonId($input->personID);
        $template->setTitle($input->title);
        $template->setDesc($input->desc);
        $template->setText($input->text);
        $template->setImage("template".rand(1, 3).".gif");
      
        $template->save();
      
        return $response->withJson(['status' => 'success']);
      }
      
      return $response->withJson(['status' => 'failed']);
    });
    
    $this->post('/saveAsWordFile', function ($request, $res, $args) {
      $input = (object)$request->getParsedBody();
      
      if ( isset ($input->personID) && isset ($input->title) && isset ($input->text) ) {
        $user = UserQuery::create()->findPk($input->personID);
          
        if ( !is_null($user) ) {
            $realNoteDir = $userDir = $user->getUserRootDir();
            $userName    = $user->getUserName();
            $currentpath = $user->getCurrentpath();
            
            $pw = new \PhpOffice\PhpWord\PhpWord();

            // [THE HTML] 
            $section = $pw->addSection();
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $input->text, false, false);

            // [SAVE FILE ON THE SERVER]
            $tmpFile = dirname(__FILE__)."/../../".$realNoteDir."/".$userName.$currentpath.$input->title.".docx";
            $pw->save($tmpFile, "Word2007");
            
            // now we create the note
            $note = new Note();
            $note->setPerId($input->personID);
            $note->setFamId(0);
            $note->setTitle($fileName);
            $note->setPrivate(1);
            $note->setText($userName . $currentpath . $input->title.".docx");
            $note->setType('file');
            $note->setEntered($_SESSION['user']->getPersonId());
            $note->setInfo(gettext('Create file'));
          
            $note->save();
    
            return $res->withJson(['success' => $tmpFile ]);
        }
      }
      
      return $res->withJson(['success' => false]);
    });
    

});
