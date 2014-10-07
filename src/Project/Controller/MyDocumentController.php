<?php

namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Symfony\Component\Validator\Constraints as Assert;
use Ilmatar\Helper\FileSystemHelper;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Entities\Document;

class MyDocumentController extends BaseBackController
{
    const ROUTE_PREFIX       = 'admin/my-document';
    const DELETE_MODE_PARAM  = 'mode';
    const DELETE_MODE_ALL    = 'all';
    const DELETE_MODE_LATEST = 'latest';
    const PREVIEW_PARAM      = 'mode';

    public static $DEFAULT_CREDENTIALS = [
        'functionality'     => \Entities\Functionality::USER,
        'type'              => \Entities\Permission::ACCESS_READWRITE,
        'managed_by_action' => true //With this flag, all route are checked into actions.
    ];

    protected $docFolder;

    public function __construct(Application $app, array $options = [])
    {
        parent::__construct($app, $options);
        $this->docFolder = $app['app.var'] . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
    }

    public function connect(\Silex\Application $app)
    {
        $app[__CLASS__] = $app->share(
            function () {
                return $this;
            }
        );
        $controllers = $app['controllers_factory'];
        /*
         * Route declarations
         */
        parent::setDefaultConfig(
            $controllers->get('/', __CLASS__ . ":displayAction")
                ->bind('mydocument-display'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/display/{userId}', __CLASS__ . ":userDisplayAction")
                ->assert('userId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                ->bind('mydocument-user-display'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->match('/load/{userId}', __CLASS__ . ":loadAction")
                ->assert('userId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                ->bind('mydocument-user-load'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->post('/upload/{userId}', __CLASS__ . ":uploadAction")
                ->assert('userId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                ->bind('mydocument-user-upload'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->match('/download/{documentId}/{version}', __CLASS__ . ":downloadAction")
                ->assert('documentId', '^-?\d*$')
                ->value('documentId', JqGrid::ID_NEW_ENTITY)
                ->assert('version', '^-?\d*$')
                ->value('version', JqGrid::ID_NEW_ENTITY)
                ->bind('mydocument-user-download'),
            $app
        );
        
        parent::setDefaultConfig(
            $controllers->match('/download_all/{userId}', __CLASS__ . ":downloadAllAction")
                ->assert('userId', '^(\d*|' . JqGrid::ID_NEW_ENTITY . ')$')
                ->bind('mydocument-user-download-all'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/delete/{documentId}', __CLASS__ . ":deleteAction")
                ->assert('documentId', '^-?\d*$')
                ->value('documentId', JqGrid::ID_NEW_ENTITY)
                ->bind('mydocument-user-delete'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/download-progress/{id}', __CLASS__ . ":downloadProgressAction")
                ->value('id', JqGrid::ID_NEW_ENTITY)
                ->bind('download-progress'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $userId = $app['security']->getToken()->getUser()->getId();
        
        $buttons = $this->setButtons(
            [
                [
                    "type" => self::BUTTON_TYPE_ADD,
                    "title" => $app['translator']->trans('Upload documents'),
                    "id" => "upload",
                    "action" => [
                        "type" => self::BUTTON_ACTION_OPEN_MODAL_WINDOW,
                        "data" => "uploadFormContainer"
                    ]
                ],
                [
                    "type"  => self::BUTTON_TYPE_DOWNLOAD,
                    "label" => $app['translator']->trans('Download all'),
                    "title" => $app['translator']->trans('Download all documents in one archive'),
                    "id" => "downloadAll",
                    "action" => [
                        "type" => self::BUTTON_ACTION_EXECUTE,
                        "data" => sprintf("downloadAll('%s')", $app['url_generator']->generate("mydocument-user-download-all", ['userId' => $userId]))
                    ]
                ]
            ],
            $app,
            false
        );
        $this->setMenu(
            self::$backMenus,
            $request,
            $app
        );

        return $this->displayJqPage(
            '\\Entities\\Document',
            '',
            [
                'title'      => $app['translator']->trans('My documents'),
                'buttons'    => $buttons,
                'uploadName' => ini_get("session.upload_progress.name"),
                'fileId'     => uniqid(),
                'uploadForm' => $this->getUploadForm(
                    $app,
                    $app['url_generator']->generate("mydocument-user-upload", ['userId' => $userId])
                )->createView(),
                'jqGridListId' => 'document'
            ],
            $app['url_generator']->generate('mydocument-user-load', ['userId' => $userId]),
            $app,
            'back/mydocument.twig'
        );
    }

    public function userDisplayAction($userId, Request $request, Application $app)
    {
        if (!($this->hasAccessToFunction($app, $userId) instanceof \Entities\User)) {
            return $app->redirect('/');
        }

        return $this->displayJqPage(
            '\\Entities\\Document',
            $app['translator']->trans('Documents linked to current user'),
            [
                'title' => $app['translator']->trans('My documents'),
                'uploadName' => ini_get("session.upload_progress.name"),
                'fileId' => uniqid(),
                'uploadForm' => $this->getUploadForm(
                    $app,
                    $app['url_generator']->generate("mydocument-user-upload", ['userId' => $userId])
                )->createView(),
                'jqGridListId' => 'document',
                'gridKey'      => "document"
            ],
            $app['url_generator']->generate('mydocument-user-load', ['userId' => $userId]),
            $app,
            'back/userMyDocument.twig'
        );
    }

    protected function getUploadForm(Application $app, $actionRoute = 'dummy')
    {
        $form = $app['form.factory']->createBuilder('form');
        
        $form->setAction($actionRoute)
             ->setMethod('POST');
        for ($nb = 0; $nb < $app['upload.file.max']; ++$nb) {
            $form->add(
                'path' . $nb,
                'file',
                [
                    'required'    => false,
                    'label'       => $app['translator']->trans('Path'),
                    'constraints' => [new Assert\File(['maxSize' => $app['upload.size.max']])]
                ]
            );
        }
        return $form->getForm();
    }

    public function loadAction($userId, Request $request, Application $app)
    {
        if (!($this->hasAccessToFunction($app, $userId) instanceof \Entities\User)) {
            return $app->redirect('/');
        }

        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Document',
                $request,
                $app,
                true,
                [
                    '_locale'       => $request->get('_locale'),
                    'url_generator' => $app['url_generator'],
                    'filter'        => [
                        [
                            "field" => "creator",
                            "op"    => "eq",
                            "data"  => $userId
                        ]
                    ],
                    'twig'          => $app['twig']
                ]
            )
        );
    }

    public function uploadAction($userId, Request $request, Application $app)
    {
        try {
            if (!($this->hasAccessToFunction($app, $userId) instanceof \Entities\User)) {
                return $app->redirect('/');
            }

            $uploadForm = $this->getUploadForm($app);
            $uploadForm->bind($request);
            if ($uploadForm->isValid()) {
                $files = $request->files->get($uploadForm->getName());
                $nbFiles = 0;
                foreach ($files as $file) {
                    if (empty($file)) {
                        continue;
                    }
                    $path = uniqid();
                    $name = $file->getClientOriginalName();
                    $mimetype = $file->getMimeType();
                    $file->move(
                        $this->docFolder,
                        $path
                    );
                    if (file_exists($this->docFolder . $path)) {
                        $document = $app['orm.em']->getRepository('\\Entities\\Document')->findOneByName($name);
                        if (is_null($document)) {
                            $document = new \Entities\Document(
                                [
                                    'name'    => $name,
                                    'mime'    => $mimetype,
                                    'creator' => $app['orm.em']->find('\\Entities\\User', $userId)
                                ]
                            );
                        }
                        $document->setUpdatedAt(new \DateTime());
                        $document->setUpdatedBy($app['security']->getToken()->getUser()->getFullname());
                        $app['orm.em']->persist($document);
                        
                        $lastdocumentfile = $app['orm.em']->getRepository('\\Entities\\DocumentFile')->findByDocument($document);
                        
                        $documentfile = new \Entities\DocumentFile(
                            [
                                'path'     => $path,
                                'document' => $document,
                                'version'  => (count($lastdocumentfile) ? ($lastdocumentfile[count($lastdocumentfile) - 1]->getVersion() + 1) : 1)
                            ]
                        );
                        $app['orm.em']->persist($documentfile);
                        $nbFiles++;
                    }
                }
                $app['orm.em']->flush();
                return $app->json(
                    [
                        "error" => false,
                        "message" => (($nbFiles > 1) ? $app['translator']->trans('Files have been correctly uploaded.') : $app['translator']->trans('File has been correctly uploaded.'))
                    ]
                );
            } else {
                return $app->json(
                    [
                        "error" => true,
                        "message" => $app['translator']->trans('Oups! Something wrong happened.')
                    ]
                );
            }
        } catch (\Exception $e) {
            return $app->json(
                [
                    "error"   => true,
                    "message" => $app['translator']->trans('Oups! Something wrong happened.')
                ]
            );
        }
    }

    public function downloadAllAction($userId, Request $request, Application $app)
    {
        try {
            $user = $this->hasAccessToFunction($app, $userId);
            if (!($user instanceof \Entities\User)) {
                return $app->redirect('/');
            }
            $documents = $user->getDocuments();
            if (is_null($documents) || empty($documents)) {
                return new Response('Error', 404);
            }
            $files = [];
            $repo = $app['orm.em']->getRepository('\Entities\DocumentFile');
            foreach ($documents as $document) {
                $files[$document->getName()] = $this->docFolder . $repo->findOneBy(['document' => $document], ['version' => 'DESC'])->getPath();
            }
            return HelperFactory::build(
                'HttpHelper',
                ['validator' => $app['validator']]
            )->downloadFromString(
                HelperFactory::build('ArchiveHelper', ['app.var' => $app['app.var']])->create($files),
                HelperFactory::build('FileSystemHelper')->getMimeType('.zip'),
                sprintf('%s_my_documents.zip', $app['app.name'])
            );
        } catch (\Exception $e) {
            return new Response('Error', 404);
        }
        return new Response('todo');
    }

    public function downloadAction($documentId, $version, Request $request, Application $app)
    {
        try {
            $document = $this->hasAccessToDocument($app, $documentId);
            if (!($document instanceof \Entities\Document)) {
                return $app->redirect('/');
            }
            
            $documentFiles = $document->getFiles();
            $file = null;
            foreach ($documentFiles as $tfile) {
                if ($tfile->getVersion() == $version) {
                    $file = $tfile;
                    break;
                }
            }
            if (empty($file)) {
                return new Response('Error', 404);
            }
            $path = $this->docFolder . $file->getPath();
            if (!file_exists($path)) {
                return new Response('Error', 404);
            }
            
            return HelperFactory::build(
                'HttpHelper',
                ['validator' => $app['validator']]
            )->downloadFromPath(
                $path,
                $document->getMime(),
                ($request->get(self::PREVIEW_PARAM) === '1') ? '' : $document->getName()
            );
        } catch (\Exception $e) {
            return new Response('Error', 404);
        }
    }

    public function deleteAction($documentId, Request $request, Application $app)
    {
        $deleteMode = $request->get(self::DELETE_MODE_PARAM);
        if (is_null($deleteMode)) {
            $app->json(
                [
                    "error" => true,
                    "message" => $app['translator']->trans('Delete mode (all or latest versions) not set.')
                ]
            );
        }
        try {
            $document = $this->hasAccessToDocument($app, $documentId);
            if (!($document instanceof \Entities\Document)) {
                return $app->redirect('/');
            }
            $documentFiles = $document->getFiles();

            $deleteMode = strtolower($deleteMode);
            if (self::DELETE_MODE_LATEST === $deleteMode) {
                $file = $documentFiles[count($documentFiles) - 1];
                $path = $this->docFolder . $file->getPath();

                $app['orm.em']->remove($file);
                if (count($documentFiles) === 1) {
                    $app['orm.em']->remove($document);
                } else {
                    $document->setUpdatedAt($documentFiles[count($documentFiles) - 2]->getUpdatedAt());
                    $document->setUpdatedBy($documentFiles[count($documentFiles) - 2]->getUpdatedBy());
                    $app['orm.em']->persist($document);
                }
                $app['orm.em']->flush();
                unlink($path);
                return $app->json(
                    [
                        "error" => false,
                        "message" => $app['translator']->trans('File has been correctly removed.')
                    ]
                );
            } elseif (self::DELETE_MODE_ALL === $deleteMode) {
                $paths = [];
                foreach ($documentFiles as $file) {
                    $paths[] = $this->docFolder . $file->getPath();
                    $app['orm.em']->remove($file);
                }
                $app['orm.em']->remove($document);
                $app['orm.em']->flush();
                foreach ($paths as $path) {
                    unlink($path);
                }
                return $app->json(
                    [
                        "error" => false,
                        "message" => $app['translator']->trans('Document and its history have been correctly removed.')
                    ]
                );
            } else {
                return $app->json(["error" => true, "message" => $app['translator']->trans('Unknown delete mode')]);
            }
        } catch (\Exception $e) {
            return $app->json(["message" => $app['translator']->trans('Oups! Something wrong happened.')]);
        }
    }

    public function downloadProgressAction($id, Request $request, Application $app)
    {
        $realId = ini_get("session.upload_progress.prefix") . $id;
        while (!isset($_SESSION[$realId])) {
            sleep(1);
        }
        $status = $_SESSION[$realId];
        $progressBar = new \Zend\ProgressBar\ProgressBar(
            new \Zend\ProgressBar\Adapter\JsPush([
                'updateMethodName' => 'pbUpdate',
                'finishMethodName' => 'pbFinish'
            ]),
            0,
            $status['content_length']
        );
        while (!empty ($status) && $status['bytes_processed'] != $status['content_length']) {
            $status = $_SESSION[$realId];
            $progressBar->update($status['bytes_processed']);
        }
        $progressBar->finish();
        return new Response();
    }
    
    protected function hasAccessToDocument(Application $app, $documentId)
    {
        $document = $app['orm.em']->find('\\Entities\\Document', $documentId);
        if (!$document instanceof \Entities\Document) {
            return false;
        }
        $currentUser            = $app['security']->getToken()->getUser();
        $isAllowedFunctionality = $app['orm.em']->getRepository('\Entities\Permission')->isAllowedFunctionality(
            $currentUser,
            self::$DEFAULT_CREDENTIALS['functionality'],
            self::$DEFAULT_CREDENTIALS['type']
        );
        //Access is allowed only if current user is document owner or has USER permission
        if (($document->getCreator()->getId() == $currentUser->getId()) || $isAllowedFunctionality) {
            return $document;
        }
        return false;
    }
    
    protected function hasAccessToFunction(Application $app, $userId)
    {
        $user = $app['orm.em']->find('\\Entities\\User', $userId);
        if (!$user instanceof \Entities\User) {
            return false;
        }
        $currentUser            = $app['security']->getToken()->getUser();
        $isAllowedFunctionality = $app['orm.em']->getRepository('\Entities\Permission')->isAllowedFunctionality(
            $currentUser,
            self::$DEFAULT_CREDENTIALS['functionality'],
            self::$DEFAULT_CREDENTIALS['type']
        );
        //Access is allowed only if current user requests his own data or has USER permission
        if (($userId == $currentUser->getId()) || $isAllowedFunctionality) {
            return $user;
        }
        return false;
    }
}
