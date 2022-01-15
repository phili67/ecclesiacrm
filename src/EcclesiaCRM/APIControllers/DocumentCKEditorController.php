<?php

//
//  This code is under copyright not under MIT Licence
//  copyright   : 2021 Philippe Logel all right reserved not MIT licence
//                This code can't be included in another software
//
//  Updated : 2021/04/06
//

namespace EcclesiaCRM\APIControllers;

use EcclesiaCRM\Utils\LoggerUtils;
use EcclesiaCRM\Utils\MiscUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


use EcclesiaCRM\CKEditorTemplatesQuery;
use EcclesiaCRM\CKEditorTemplates;
use EcclesiaCRM\UserQuery;
use EcclesiaCRM\Note;
use EcclesiaCRM\SessionUser;



class DocumentCKEditorController
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function templates (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    }

    public function alltemplates (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    }

    public function deleteTemplate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->templateID) ) {
            $template = CKEditorTemplatesQuery::Create()->findOneByID($input->templateID);

            $template->delete();

            return $response->withJson(['status' => 'success']);
        }

        return $response->withJson(['status' => 'failed']);
    }

    public function renameTemplate (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
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
    }

    public function saveTemplate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personID) && isset ($input->title) && isset ($input->desc) && isset ($input->text) ) {
            $template = CKEditorTemplatesQuery::Create()->filterByTitle ($input->title)->findOneByDesc ($input->desc);

            if (!is_null ($template)) {
                $template->setText($input->text);
                $template->save();
            } else {
                $template = new CKEditorTemplates();

                $template->setPersonId($input->personID);
                $template->setTitle($input->title);
                $template->setDesc($input->desc);
                $template->setText($input->text);
                $template->setImage("template".rand(1, 3).".gif");

                $template->save();
            }

            return $response->withJson(['status' => 'success']);
        }

        return $response->withJson(['status' => 'failed']);
    }

    public function saveAsWordFile (ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface {
        $input = (object)$request->getParsedBody();

        if ( isset ($input->personID) && isset ($input->title) && isset ($input->text) ) {
            $user = UserQuery::create()->findPk($input->personID);

            if ( !is_null($user) ) {
                $realNoteDir = $user->getUserRootDir();
                $userName    = $user->getUserName();
                $currentpath = $user->getCurrentpath();

                // [SAVE HTML TO Word file FILE ON THE SERVER]
                $result = MiscUtils::saveHtmlAsWordFilePhpWord( $userName, $realNoteDir, $currentpath, $input->text, $input->title);
                //$result = MiscUtils::saveHtmlAsWordFile( $userName, $realNoteDir, $currentpath, $input->text, $input->title);
                $title    = $result['title'];
                $tmpFile  = $result['tmpFile'];

                // now we create the note
                $note = new Note();
                $note->setPerId($input->personID);
                $note->setFamId(0);
                $note->setTitle($tmpFile);
                $note->setPrivate(1);
                $note->setText($userName . $currentpath . $input->title.".docx");
                $note->setType('file');
                $note->setEntered(SessionUser::getUser()->getPersonId());
                $note->setInfo(gettext('Create file'));

                $note->save();

                return $response->withJson(['success' => $tmpFile ]);
            }
        }

        return $response->withJson(['success' => false]);
    }
}
