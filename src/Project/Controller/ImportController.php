<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ilmatar\HelperFactory;
use Ilmatar\Helper\ImportHelper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ImportController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/import';

    const IMPORT_DIRECTORY = 'import';
    
    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::IMPORT,
        'type'           => \Entities\Permission::ACCESS_READ
    );

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
            $controllers->match('/', __CLASS__ . ":step1Action")
                        ->bind('import-step1'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/step2/{entity}/{mode}/{path}', __CLASS__ . ":step2Action")
                        ->convert('entity', function ($entity) {
                            return base64_decode($entity);
                        })
                        ->bind('import-step2'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->match('/step3/{entity}/{mode}/{path}', __CLASS__ . ":step3Action")
                        ->convert('entity', function ($entity) {
                            return base64_decode($entity);
                        })
                        ->bind('import-step3'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load_entity_for_import', __CLASS__ . ":loadEntityAction")
                        ->bind('load-entity'),
            $app
        );
        return $controllers;
    }
    public function step1Action(Request $request, Application $app)
    {
        $rootImportPath = $app['app.var'] . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        
        $this->setMenu(self::$backMenus, $request, $app);

        $step1Form = $app['form.factory']->create(
            new \Project\Form\ImportStep1Type(
                array(
                    'action'             => $app['url_generator']->generate('import-step1'),
                    'importableEntities' => array_merge(
                        array('' => $app['translator']->trans("Choose an Entity")),
                        HelperFactory::build(
                            'ImportHelper',
                            [
                                'orm.em'     => $app['orm.em'],
                                'translator' => $app['translator']
                            ],
                            ['logger' => $app['monolog.import']]
                        )->getAllEntities()
                    ),
                    'upload.size.max' => $app['upload.size.max']
                )
            )
        );
       
        $step1Form->handleRequest($request);
        if ($step1Form->isSubmitted()) {
            if ($step1Form->isValid()) {
                $importStep1  = $request->request->get('importStep1');
                $files = $request->files->get($step1Form->getName());
                $path = uniqid();
                $files['path']->move(
                    $rootImportPath,
                    $path
                );
                if (file_exists($rootImportPath . DIRECTORY_SEPARATOR . $path)) {
                    return $app->handle(
                        Request::create(
                            $app['url_generator']->generate(
                                'import-step2',
                                array(
                                    'entity' => base64_encode($importStep1['entities']),
                                    'mode'   => $importStep1['mode'],
                                    'path'   => $path
                                )
                            ),
                            'GET'
                        ),
                        HttpKernelInterface::SUB_REQUEST
                    );
                }
                $app['notification'](
                    $app['translator']->trans('Oups! Something wrong happened.'),
                    'error'
                );
            } else {
                $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
            }
        }
        return $app['twig']->render(
            'back/importStep1.twig',
            array(
                'title'        => sprintf(
                    $app['translator']->trans('Import step %s/3'),
                    1
                ),
                'tablabel' => array (
                    "name"        => $app['translator']->trans('Name'),
                    "type"        => $app['translator']->trans('Type'),
                    "is_required" => $app['translator']->trans('Is required')
                ),
                'form'          => array (
                    "requiredField" => array (
                        "label" => $app['translator']->trans('Expected columns')
                    ),
                ),
                'buttonlabel'   => array (
                    "next" => $app['translator']->trans('Next'),
                ),
                'importForm'    => $step1Form->createView(),
                'loadEntityUrl' => $app['url_generator']->generate('load-entity'),
                'styles'        => array('/assets/import.css'),
            )
        );
    }
    public function loadEntityAction(Request $request, Application $app)
    {
        return $app['twig']->render(
            'back/importfieldtab.twig',
            array(
                'tablabel' => array (
                    "name" => $app['translator']->trans('Name'),
                    "type" => $app['translator']->trans('Type'),
                    "is_required" => $app['translator']->trans('Is required')
                ),
                'form' => array (
                    "requiredField" => array (
                            "label" => $app['translator']->trans('Expected fields'),
                            "value" => HelperFactory::build(
                                'ImportHelper',
                                [
                                    'orm.em'     => $app['orm.em'],
                                    'translator' => $app['translator']
                                ],
                                ['logger' => $app['monolog.import']]
                            )->getEntityImportableField($request->get('entity'))
                        ),
                )
            )
        );
    }

    public function step2Action($entity, $mode, $path, Request $request, Application $app)
    {
        $rootImportPath = $app['app.var'] . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;

        $this->setMenu(self::$backMenus, $request, $app);
        
        
        $step2Form = $app['form.factory']->create(
            new \Project\Form\ImportStep2Type(
                array(
                    'action' => $app['url_generator']->generate(
                        'import-step2',
                        array(
                            'entity' => base64_encode($entity),
                            'mode'   => $mode,
                            'path'   => $path
                        )
                    ),
                    'upload.size.max' => $app['upload.size.max']
               )
            )
        );
        
        $step2Form->handleRequest($request);
        if ($step2Form->isSubmitted()) {
            if ($step2Form->isValid()) {
                if (file_exists($rootImportPath . DIRECTORY_SEPARATOR . $path)) {
                    unlink($rootImportPath . DIRECTORY_SEPARATOR . $path);
                }
                $files = $request->files->get($step2Form->getName());
                $npath = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
                $files['path']->move(
                    $rootImportPath,
                    $npath
                );
                if (file_exists($rootImportPath . DIRECTORY_SEPARATOR . $npath)) {
                    $path = $npath;
                } else {
                    $app['notification'](
                        $app['translator']->trans('Oups! Something wrong happened.'),
                        'error'
                            
                    );
                }
            } else {
                $app['notification']($app['translator']->trans("Some items are invalid."), 'error');
            }
        }
        $modeoption = array(
            ImportHelper::MODE_ADD   => $app['translator']->trans('Add'),
            ImportHelper::MODE_ERASE => $app['translator']->trans('Erase and Add')
        );
        $error = HelperFactory::build(
            'ImportHelper',
            [
                'orm.em'     => $app['orm.em'],
                'translator' => $app['translator']
            ],
            ['logger' => $app['monolog.import']]
        )->validImportableFile($entity, $rootImportPath . DIRECTORY_SEPARATOR . $path);
        return $app['twig']->render(
            'back/importStep2.twig',
            array(
                'title'        => sprintf(
                    $app['translator']->trans('Import step %s/3'),
                    2
                ),
                'finalaction' => $app['url_generator']->generate(
                    'import-step3',
                    array(
                        'entity' => base64_encode($entity),
                        'mode'   => $mode,
                        'path'   => $path
                    )
                ),
                'errormsg' => array (
                    "fatal"  => $app['translator']->trans('fatal error(s), solve them and retry before importing'),
                    "normal" => $app['translator']->trans('error(s), you should solve them and retry before importing'),
                    "no"     => $app['translator']->trans('No error, you can import'),
                ),
                'tablabel' => array (
                    "name"        => $app['translator']->trans('Name'),
                    "type"        => $app['translator']->trans('Type'),
                    "is_required" => $app['translator']->trans('Is required')
                ),
                'form' => array (
                    "entity" => array (
                        "label" => $app['translator']->trans('Entities'),
                        "value" => substr($entity, strpos($entity, '\\') + 1),
                    ),
                    "mode" => array (
                        "label" => $app['translator']->trans('Mode'),
                        "value" => $modeoption[$mode]
                    ),
                    "requiredField" => array (
                        "label" => $app['translator']->trans('Expected columns'),
                        "value" => HelperFactory::build(
                            'ImportHelper',
                            [
                                'orm.em'     => $app['orm.em'],
                                'translator' => $app['translator']
                            ],
                            ["logger" => $app['monolog.import']]
                        )->getEntityImportableField($entity)
                    ),
                ),
                'buttonlabel' => array (
                    "retry"    => $app['translator']->trans('Retry'),
                    "import"   => $app['translator']->trans('Import'),
                    "previous" => $app['translator']->trans('Previous'),
                ),
                'styles' => array('/assets/import.css'),
                'fatalerror'  => $error["fatal"],
                'simpleerror' => $error["error"],
                'importForm'  => $step2Form->createView(),
            )
        );
    }
    
    public function step3Action($entity, $mode, $path, Request $request, Application $app)
    {
        $rootImportPath = $app['app.var'] . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        
        $this->setMenu(self::$backMenus, $request, $app);
        $result = HelperFactory::build(
            'ImportHelper',
            array(
                'orm.em'     => $app['orm.em'],
                'translator' => $app['translator']
            ),
            array(
                'logger' => $app['monolog.import']
            )
        )->importFile($entity, $rootImportPath . DIRECTORY_SEPARATOR . $path, $mode);
        
        if ($result["errors"] === "fatal") {
            $app['notification']($app['translator']->trans($result["list"]), 'error');
            return $app->handle(
                Request::create(
                    $app['url_generator']->generate(
                        'import-step2',
                        array(
                            'entity' => base64_encode($entity),
                            'mode'   => $mode,
                            'path'   => $path
                        )
                    ),
                    'GET'
                ),
                HttpKernelInterface::SUB_REQUEST
            );
        }
        return $app['twig']->render(
            'back/importStep3.twig',
            array(
                'title'        => sprintf(
                    $app['translator']->trans('Import step %s/3'),
                    3
                ),
                'fatalerror'  => $result["list"]["fatal"],
                'simpleerror' => $result["list"]["error"],
                'buttonlabel' => array (
                    'newimport' => $app['translator']->trans('New import')
                ),
                'count' =>  sprintf(
                    $app['translator']->trans('%s failure(s), %s row(s) imported'),
                    $result["errors"],
                    $result["count"],
                    __FUNCTION__
                )
            )
        );
    }
}
