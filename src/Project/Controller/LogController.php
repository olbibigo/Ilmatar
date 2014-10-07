<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;

class LogController extends BaseBackController
{
    const ROUTE_PREFIX      = 'admin/log';
    const FILE_TYPE_LOG     = 'log';
    const LOG_READ_MAX_SIZE = 100000; //~100Kb
    
    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::LOG,
        'type'          => \Entities\Permission::ACCESS_READ
    );

    protected $priorityFilter = array(
        'INFO+',
        'NOTICE+',
        'WARNING+',
        'ERROR+',
        'CRITICAL+',
        'ALERT+',
        'EMERGENCY'
    );
    
    protected $priorityMapping = array(
        'DEBUG'     => '#00F',
        'INFO'      => '#00F',
        'NOTICE'    => '#00F',
        'WARNING'   => '##FFA500',
        'ERROR'     => '#F00',
        'CRITICAL'  => '#F00',
        'ALERT'     => '#F00',
        'EMERGENCY' => '#F00'
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
                        ->bind('log-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/load/{token}', __CLASS__ . ":loadAction")
                        ->convert('token', function ($token) {
                            return HelperFactory::build('SecurityHelper')->decryptString(rawurldecode($token));
                        })
                        ->bind('log-load'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/download/{token}', __CLASS__ . ":downloadAction")
                        ->convert('token', function ($token) {
                            return HelperFactory::build('SecurityHelper')->decryptString(rawurldecode($token));
                        })
                        ->bind('log-download'),
            $app
        );
        
        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Gets all log filename
        $fileSystemHelper = HelperFactory::build('FileSystemHelper');
        $securityhelper   = HelperFactory::build('SecurityHelper');
        $rawLogPaths      = $fileSystemHelper->rscandir($app['app.var'] . '/log/*.log', true);
        $logPaths         = [];
        foreach ($rawLogPaths as $rawLogPath) {
            $filename = basename($rawLogPath);
            $logPaths[$filename] = rawurlencode($securityhelper->encryptString($filename));
        }
        return $app['twig']->render(
            'back/log.twig',
            array(
                'title'               => $app['translator']->trans('Logs'),
                'logPaths'            => $logPaths,
                'rootDownloadUrl'     => $app['url_generator']->generate(
                    'log-download',
                    ['token' => JqGrid::PARAM_URL_ROW_ID]
                ),
                'jqGridRowIdUrlParam' => JqGrid::PARAM_URL_ROW_ID,
                'buttons'             => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_DOWNLOAD,
                            "title"  => $app['translator']->trans('Download selected log')
                        )
                    ),
                    $app
                ),
                'priorities'          => $this->priorityFilter
            )
        );
    }

    public function loadAction($token, Request $request, Application $app)
    {
        $path = $app['app.var']. '/log/' . $token;
        if (!file_exists($path)) {
            return new Response('Error', 404);
        }
        $fileSystemHelper = HelperFactory::build('FileSystemHelper');
        $logContent = $fileSystemHelper->tail($path, self::LOG_READ_MAX_SIZE, true);
        //Filters content
        $filter = $request->get("filter");
        if (!empty($filter) && $filter != 'ALL') {
            $priorities = array_keys($this->priorityMapping);
            $prioritiesToDump  = array_slice(
                $priorities,
                0,
                array_search(
                    trim($filter),
                    $priorities
                )
            );

            $lines           = explode('<br/>', $logContent);
            $filteredLines   = [];
            foreach ($lines as $line) {
                $isKept = true;
                foreach ($prioritiesToDump as $priority) {
                    if (false !== strpos($line, $priority)) {
                        $isKept = false;
                        break;
                    }
                }
                if ($isKept) {
                    $filteredLines[] = $line;
                }
            }
            $logContent = implode('<br/>', $filteredLines);
        }
        //Highlights message type with colors
        $logContent = str_replace(
            array_keys($this->priorityMapping),
            array_map(
                function ($v, $k) {
                    return sprintf('<span style="color:%s">%s</span>', $v, $k);
                },
                array_values($this->priorityMapping),
                array_keys($this->priorityMapping)
            ),
            $logContent
        );
        $intro = $app['translator']->trans('This log is sorted by descending dates so the most recent events are first. If too big, it is truncated and (&hellip;) is displayed at the bottom.');
        return new Response('<div>' . $intro . '</div><br/>' .$logContent);
    }
   
    public function downloadAction($token, Application $app)
    {
        return self::download(
            $token,
            self::FILE_TYPE_LOG,
            [self::FILE_TYPE_LOG => $app['app.var']. '/log/'],
            $app
        );
    }
}
