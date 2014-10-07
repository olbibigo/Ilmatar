<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;

class TranslationController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/translation';

    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::TRANSLATION,
        'type'           => \Entities\Permission::ACCESS_READ
    );

    public static $CREDENTIALS = array(
        'translation-change' => array (
            'type' => \Entities\Permission::ACCESS_READWRITE
        )
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
            $controllers->get('/', __CLASS__ . ":displayAction")
                        ->bind('translation-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/{locale}', __CLASS__ . ":loadAction")
                        ->bind('translation-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load/{locale}', __CLASS__ . ":subloadAction")
                        ->bind('translation-subload'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->post('/edit/{locale}', __CLASS__ . ":changeAction")
                        ->bind('translation-change'),
            $app
        );

        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Gets all translation filename
        $transNames = array_map(
            function ($item) {
                return strtoupper(basename($item, '.json'));
            },
            HelperFactory::build('FileSystemHelper')->rscandir($app['app.var'] . '/locales/*.json', true)
        );
        return $app['twig']->render(
            'back/translation.twig',
            array(
                'title'      => $app['translator']->trans('Translations'),
                'transNames' => $transNames,
            )
        );
    }

    public function loadAction($locale, Request $request, Application $app)
    {
        return $app['twig']->render(
            'back/tabEditableGrid.twig',
            array(
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_SAVE,
                            "title"  => $app['translator']->trans('Save'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => sprintf("saveJqGridRow('%s')", $locale)
                            )
                        )
                    ),
                    $app
                ),
                'jqGridName'         => $app['translator']->trans('Recorded translations'),
                //Builds up JqGrid columns
                'jqGridColNames'     => array(
                    $app['translator']->trans('Key'),
                    $app['translator']->trans('Traduction'),
                ),
                'jqGridColModels'    => array(
                    array(
                        "name"     => "strid",
                        "search"   => false,
                        "editable" => false,
                        "sortable" => false,
                        "key"      => true
                    ),
                    array(
                        "name"     => "strvalue",
                        "search"   => false,
                        "editable" => true,
                        "sortable" => false
                    ),
                ),
                'jqGridDataReadUrl'   => $app['url_generator']->generate('translation-subload', array('locale' => $locale)),
                'jqGridDataWriteUrl'  => $app['url_generator']->generate('translation-change', array('locale' => $locale)),
                'jqGridRowIdUrlParam' => JqGrid::PARAM_URL_ROW_ID,
                'csrfToken'           => $this->generateToken($app),
                'gridKey'             => $locale
            )
        );
    }

    public function subloadAction($locale, Request $request, Application $app)
    {
        $json = $app['gaufrette.filesystem']->read($this->buildFilePath($locale));
        if (false === $json) {
            return $app->json([]);
        }
        $obj         = json_decode($json);
        $strids      = array_keys(get_object_vars($obj));
        $out         = [];
        $out['rows'] = [];
        foreach ($strids as $strid) {
            $out['rows'][] = array(
                'strid'    => $strid,
                'strvalue' => $obj->$strid
            );
        }
        $out['page']    = 1;
        $out['total']   = 1;
        $out['records'] = count($strids);
        return $app->json($out);
    }

    public function changeAction($locale, Request $request, Application $app)
    {
        if (!$this->isValidToken($request->get(self::PARAM_TOKEN), $app)) {
            return $app->json(
                array("error" => true, "message" => "Invalid token.")
            );
        }
        $oper = $request->get(JqGrid::JQGRID_KEY_OPER);
        if ($oper != JqGrid::JQGRID_ACTION_UPDATE) {
            return $app->json(
                array("error" => true, "message" => "Invalid operation.")
            );
        }
        $filepath = $this->buildFilePath($locale);
        try {
            $json = $app['gaufrette.filesystem']->read($filepath);
        } catch (\InvalidArgumentException $e) {
            return $app->json(
                array("error" => true, "message" => "Unknown locale.")
            );
        }
        $obj         = json_decode($json);
        $strid       = $request->get('strid');
        $obj->$strid = $request->get('strvalue');
        try {
            $app['gaufrette.filesystem']->write(
                $filepath . '.backup',
                $app['gaufrette.filesystem']->read($filepath),
                true
            );
        } catch (\InvalidArgumentException $e) {
            return $app->json(
                array("error" => true, "message" => "Translation file cannot be backup.")
            );
        }
        try {
            $app['gaufrette.filesystem']->write(
                $filepath,
                json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                true
            );
        } catch (\InvalidArgumentException $e) {
            if ($app['gaufrette.filesystem']->has($filepath . '.backup')) {
                try {
                    $app['gaufrette.filesystem']->write(
                        $filepath,
                        $app['gaufrette.filesystem']->read($filepath . '.backup'),
                        true
                    );
                } catch (\InvalidArgumentException $e) {
                    return $app->json(
                        array("error" => true, "message" => "Translation backup cannot be restored.")
                    );
                }
            }
        }

        return $app->json(
            array(
                "error" => false,
                "id"    => $strid,
                "oper"  => $oper
            )
        );
    }
    
    protected function buildFilePath($locale)
    {
        return '/locales/' . strtolower($locale) . '.json';
    }
}
