<?php
namespace Project\Controller;

use Ilmatar\Application;
use Ilmatar\JqGrid;
use Ilmatar\Helper\FileSystemHelper;
use Ilmatar\HelperFactory;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseBackController extends \Ilmatar\BaseBackController
{
    //Allowed action on buttons (See subLayout.js)
    const BUTTON_ACTION_OPEN_MODAL_WINDOW = 'open-modal-window';
    const BUTTON_ACTION_OPEN_PAGE         = 'open-page';
    const BUTTON_ACTION_EXECUTE           = 'execute';
    //Allowed button type (See subLayout.twig)
    const BUTTON_TYPE_ADD      = 'Add';
    const BUTTON_TYPE_EDIT     = 'Edit';
    const BUTTON_TYPE_DELETE   = 'Delete';
    const BUTTON_TYPE_SAVE     = 'Save';
    const BUTTON_TYPE_LIST     = 'List';
    const BUTTON_TYPE_EXPORT   = 'Export';
    const BUTTON_TYPE_EXECUTE  = 'Execute';
    const BUTTON_TYPE_DOWNLOAD = 'Download';
    const BUTTON_TYPE_GENERATE = "Generate";
    //Allowed device
    const DEVICE_PC = 0;
    const DEVICE_TECTON  = 1;
    //user agent
    const PDA_USER_AGENT = 'Zetakey';
    
    /*
     * Top menu configuration
     */
    protected static $backMenus = [
        "Administration" => [
            "extras" => ["data-iconClass" => "fa-cogs"],
            "items"  => [
                "Users" => [
                    "functionality" => \Entities\Functionality::USER,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "user-display",
                    "extras"        => ["data-iconClass" => "fa-users"]
                ],
                "Roles" => [
                    "functionality" => \Entities\Functionality::ROLE,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "role-display",
                    "extras"        => ["data-iconClass" => "fa-lock"]
                ],
                "Functionalities" => [
                    "functionality" => \Entities\Functionality::FUNCTIONALITY,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "functionality-display",
                    "extras"        => ["data-iconClass" => "fa-key"]
                ],
                "Parameters" => [
                    "functionality" => \Entities\Functionality::GLOBAL_PARAMETER,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "parameter-display",
                    "extras"        => ["data-iconClass" => "fa-wrench"]
                ],
                "Mail templates" => [
                    "functionality" => \Entities\Functionality::MAIL_TEMPLATE,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "mail-template-display",
                    "extras"        => ["data-iconClass" => "fa-envelope"]
                ],
                "Editorial push" => [
                    "functionality" => \Entities\Functionality::EDITORIAL,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => "editorial-display",
                    "extras"         => ["data-iconClass" => "fa-edit"]
                ],
                "Background jobs" => [
                    "functionality" => \Entities\Functionality::JOB,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "job-display",
                    "extras"        => ["data-iconClass" => "fa-exchange"]
                ],
                "Query manager" => [
                    "functionality" => \Entities\Functionality::QUERY,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "query-display",
                    "extras"        => ["data-iconClass" => "fa-flask"]
                ],
                "Logs" => [
                    "functionality" => \Entities\Functionality::LOG,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "log-display",
                    "extras"        => ["data-iconClass" => "fa-file"]
                ],
                "Database history" => [
                    "functionality" => \Entities\Functionality::LOG,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "audit-display",
                    "extras"        => ["data-iconClass" => "fa-database"]
                ],
                "Translations" => [
                    "functionality" => \Entities\Functionality::TRANSLATION,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "translation-display",
                    "extras"        => ["data-iconClass" => "fa-book"]
                ],
                "Flash messages" => [
                    "functionality" => \Entities\Functionality::FLASH_MESSAGE,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "flashmessage-display",
                    "extras"        => ["data-iconClass" => "fa-bolt"]
                ],
                "News" => [
                    //Access to news list is for every user!
                    "functionality" => \Entities\Functionality::DASHBOARD,//not NEWS
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "news-display",
                    "extras"        => ["data-iconClass" => "fa-info-circle"]
                ],
                "Changelog" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "changelog-display",
                    "extras"        => ["data-iconClass" => "fa-signal"]
                ],
                "Import" => [
                    "functionality" => \Entities\Functionality::IMPORT,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "import-step1",
                    "extras"        => ["data-iconClass" => "fa-upload"]
                ],
                "Maintenance" => [
                    "functionality" => \Entities\Functionality::MAINTENANCE,
                    "type"          => \Entities\Permission::ACCESS_READ,
                    "route"         => "maintenance-display",
                    "extras"        => ["data-iconClass" => "fa-bullseye"]
                ]
            ],
        ]
    ];

    protected function setButtons(array $buttonConfigs, Application $app, $areCheckedCredentials = true)
    {
        /*
         * Default values for buttons
         */
        $defaultButtonConfigs = [
            self::BUTTON_TYPE_ADD    => [
                "credential-type" => \Entities\Permission::ACCESS_READWRITE,
                "label"           => self::BUTTON_TYPE_ADD,
                "action"          => [
                    "type" => self::BUTTON_ACTION_OPEN_PAGE,
                    "data" => ""
                ]
            ],
            self::BUTTON_TYPE_EDIT    => [
                "credential-type" => \Entities\Permission::ACCESS_READWRITE,
                "label"           => self::BUTTON_TYPE_EDIT,
                "action"          => [
                    "type" => self::BUTTON_ACTION_OPEN_PAGE,
                    "data" => ""
                ]
            ],
            self::BUTTON_TYPE_DELETE    => [
                "credential-type" => \Entities\Permission::ACCESS_READWRITE,
                "label"           => self::BUTTON_TYPE_DELETE,
                "action"          => [
                    "type" => self::BUTTON_ACTION_EXECUTE,
                    "data" => sprintf("submitForm('%s')", JqGrid::JQGRID_ACTION_DELETE)
                ]
            ],
            self::BUTTON_TYPE_SAVE    => [
                "credential-type" => \Entities\Permission::ACCESS_READWRITE,
                "label"           => self::BUTTON_TYPE_SAVE,
                "action"          => [
                    "type" => self::BUTTON_ACTION_EXECUTE,
                    "data" => sprintf("submitForm('%s')", JqGrid::JQGRID_ACTION_UPDATE)
                ]
            ],
            self::BUTTON_TYPE_LIST    => [
                "credential-type" => \Entities\Permission::ACCESS_READ,
                "label"           => "Back to list",
                "action"          => [
                    "type" => self::BUTTON_ACTION_OPEN_PAGE,
                    "data" => ""
                ]
            ],
            self::BUTTON_TYPE_EXPORT    => [
                "credential-type" => \Entities\Permission::ACCESS_READ,
                "label"           => self::BUTTON_TYPE_EXPORT,
                "action"          => [
                    "type" => self::BUTTON_ACTION_EXECUTE,
                    "data" => ""
                ]
            ],
            self::BUTTON_TYPE_EXECUTE    => [
                "credential-type" => \Entities\Permission::ACCESS_READWRITE,
                "label"           => self::BUTTON_TYPE_EXECUTE,
                "action"          => [
                    "type" => self::BUTTON_ACTION_EXECUTE,
                    "data" => "executeQuery()"
                ]
            ],
            self::BUTTON_TYPE_GENERATE    => [
                "credential-type" => \Entities\Permission::ACCESS_READ,
                "label"           => self::BUTTON_TYPE_GENERATE,
                "action"          => [
                    "type" => self::BUTTON_ACTION_EXECUTE,
                    "data" => "generate()"
                ]
            ],
            self::BUTTON_TYPE_DOWNLOAD   => [
                "credential-type" => \Entities\Permission::ACCESS_READ,
                "label"           => self::BUTTON_TYPE_DOWNLOAD,
                "action"          => [
                    "type" => self::BUTTON_ACTION_OPEN_PAGE,
                    "data" => ""
                ]
            ]
        ];
        $repo = $app['orm.em']->getRepository('\\Entities\\Permission');
        $user = $app['security']->getToken()->getUser();
        $out = [];
        foreach ($buttonConfigs as $buttonConfig) {
            $defaultButtonConfig = $defaultButtonConfigs[$buttonConfig['type']];
            if (!$areCheckedCredentials
              || $repo->isAllowedFunctionality($user, static::$DEFAULT_CREDENTIALS["functionality"], $defaultButtonConfig["credential-type"])
            ) {
                $out[] = array_replace_recursive(
                    $defaultButtonConfig,
                    $buttonConfig
                );
            }
        }
        return $out;
    }
    
    protected static function download($token, $type, $validTypes, Application $app)
    {
        if (isset($validTypes[$type])) {
            $rootFolder = $validTypes[$type];
        } else {
            return new Response('Error', 404);
        }
        
        $filepath = $rootFolder . $token;
        if (!file_exists($filepath)) {
            return new Response('Error', 404);
        }
        return HelperFactory::build('HttpHelper', ['validator' => $app['validator']])->downloadFromPath(
            $filepath,
            HelperFactory::build('FileSystemHelper')->getFileInfos(
                $filepath,
                FileSystemHelper::FILE_MIME
            )[FileSystemHelper::FILE_MIME],
            $token
        );
    }
}
