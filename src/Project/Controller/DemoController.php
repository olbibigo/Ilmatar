<?php
namespace Project\Controller;

use Ilmatar\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ilmatar\HelperFactory;
use Ilmatar\JqGrid;
use Ilmatar\Helper\ArrayHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Carbon\Carbon;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Ilmatar\Helper\FileSystemHelper;
use Ilmatar\Helper\BarcodeHelper;

class DemoController extends BaseBackController
{
    const ROUTE_PREFIX = 'admin/pipo';

    const FILE_TYPE_WORD = 'docx';
    const FILE_TYPE_PDF  = 'pdf';
    
    public static $DEFAULT_CREDENTIALS = array(
        'functionality' => \Entities\Functionality::DASHBOARD,
        'type'           => \Entities\Permission::ACCESS_READ
    );
    public static $CREDENTIALS = array(
        'pipo-change' => array (
            'type' => \Entities\Permission::ACCESS_READWRITE
        ),
        'pipo-document-upload' => array (
            'type' => \Entities\Permission::ACCESS_READWRITE
        )
    );

    public function __construct(Application $app, array $options = [])
    {
        parent::__construct($app, $options);
        self::$backMenus["Demos"] = [
            "extras" => ["data-iconClass" => "fa-rocket"],
            "items"  => [
                "Documents" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => 'pipo-document-display',
                    "extras"         => ["data-iconClass" => "fa-file-text"]
                ],
                "Pipo" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => 'pipo-display',
                    "extras"         => ["data-iconClass" => "fa-info"]
                ],
                "Progressbar" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => 'pipo-long_process',
                    "extras"         => ["data-iconClass" => "fa-leaf"]
                ],
                "Barcode" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => 'pipo-barcode',
                    "extras"         => ["data-iconClass" => "fa-barcode"]
                ],
                "Word" => [
                    "functionality" => \Entities\Functionality::DASHBOARD,
                    "type"           => \Entities\Permission::ACCESS_READ,
                    "route"          => 'pipo-word',
                    "extras"         => ["data-iconClass" => "fa-file-word-o"]
                ]
            ]
        ];
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
                        ->bind('pipo-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/load', __CLASS__ . ":loadAction")
                        ->bind('pipo-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/edit', __CLASS__ . ":changeAction")
                        ->bind('pipo-change'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/export', __CLASS__ . ":exportAction")
                        ->bind('pipo-export'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/select_user', __CLASS__ . ":userSelectAction")
                        ->bind('pipo-user-select'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/select_functionality', __CLASS__ . ":functionalitySelectAction")
                        ->bind('pipo-functionality-select'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/long_process', __CLASS__ . ":longProcessAction")
                        ->bind('pipo-long_process'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/long_process_call', __CLASS__ . ":longProcessCallAction")
                        ->bind('pipo-long_process_call'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/barcode', __CLASS__ . ":barcodeAction")
                        ->bind('pipo-barcode'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/barcode', __CLASS__ . ":barcodeAction")
                        ->bind('pipo-barcode'),
            $app
        );

        parent::setDefaultConfig(
            $controllers->get('/barcode-download', __CLASS__ . ":barcodeDownloadAction")
                        ->bind('pipo-barcode-download'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/doc', __CLASS__ . ":displayDocumentAction")
                        ->bind('pipo-document-display'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/doc/load', __CLASS__ . ":loadDocumentAction")
                        ->bind('pipo-document-load'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->post('/doc/upload', __CLASS__ . ":uploadDocumentAction")
                        ->bind('pipo-document-upload'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/doc/download/{path}', __CLASS__ . ":downloadDocumentAction")
                        ->convert('path', function ($path) {
                            return base64_decode($path);
                        })
                        ->bind('pipo-document-download'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/word', __CLASS__ . ":wordAction")
                        ->bind('pipo-word'),
            $app
        );
        parent::setDefaultConfig(
            $controllers->get('/word/download/{type}/{token}', __CLASS__ . ":downloadWordAction")
                        ->assert('type', sprintf('^(%s|%s)$', self::FILE_TYPE_WORD, self::FILE_TYPE_PDF))
                        ->convert('token', function ($token) {
                            return HelperFactory::build('SecurityHelper')->decryptString(rawurldecode($token));
                        })
                        ->bind('pipo-word-download'),
            $app
        );
        return $controllers;
    }

    public function displayAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        //Delegates to base controller
        return $this->displayEditableJqPage(
            '\\Entities\\Pipo',
            $app['translator']->trans('Recorded pipos'),
            array(
                'title'   => $app['translator']->trans('Pipo list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_SAVE,
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "saveJqGridRow()"
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "addJqGridRow()"
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_DELETE,
                            "action" => array(
                                "type" => self::BUTTON_ACTION_EXECUTE,
                                "data" => "deleteJqGridRow([])"
                            )
                        ),
                        array(
                            "type"   => self::BUTTON_TYPE_EXPORT,
                            "action"    => array(
                                "data" => sprintf("exportGrid('%s')", $app['url_generator']->generate('pipo-export'))
                            )
                        )
                    ),
                    $app
                )
            ),
            'pipo-load',
            'pipo-change',
            $app,
            'back/editableGrid.twig',
            array(
                self::OPTION_HAS_FOOTER     => true,
                self::OPTION_HAS_FULL_TOTAL => true
            )
        );
    }

    public function loadAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $app->json(
            $this->loadJqPage(
                '\\Entities\\Pipo',
                $request,
                $app,
                true,
                array(
                    self::OPTION_HAS_FOOTER     => true,
                    self::OPTION_HAS_FULL_TOTAL => true
                )
            )
        );
    }

    public function changeAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->changeJqPage(
            '\\Entities\\Pipo',
            $request,
            $app
        );
    }

    public function userSelectAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->selectJqPage(
            '\\Entities\\User',
            array(
                'is_active' => true
            ),
            array(
                'lastname' => 'ASC'
            ),
            'id',
            'fullname',
            $app['orm.em']
        );
    }

    public function functionalitySelectAction(Request $request, Application $app)
    {
        //Delegates to base controller
        return $this->selectJqPage(
            '\\Entities\\Functionality',
            array(
            ),
            array(
                'code' => 'ASC'
            ),
            'id',
            'code',
            $app['orm.em']
        );
    }

    public function exportAction(Request $request, Application $app)
    {
         //Delegates to base controller
        return $this->exportJqPage(
            '\\Entities\\Pipo',
            $request,
            $app,
            array(
                self::OPTION_HAS_FOOTER     => true,
                self::OPTION_HAS_FULL_TOTAL => true,
                self::OPTION_HIGHLIGHTS     => array(
                    ArrayHelper::HIGHLIGHT_ZEBRA_ROW,
                    ArrayHelper::HIGHLIGHT_LAST_ROW
                )
            )
        );
    }

    public function longProcessAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);
        return $app['twig']->render(
            'back/demo/longProcess.twig',
            [ 'title' => 'Progress bar' ]
        );
    }

    public function longProcessCallAction(Request $request, Application $app)
    {
        $progressBar = new \Zend\ProgressBar\ProgressBar(
            new \Zend\ProgressBar\Adapter\JsPush([
                'updateMethodName' => 'pbUpdate',
                'finishMethodName' => 'pbFinish'
            ]),
            0,
            10
        );
        $count = 0;
        //Dummy counter to feed the progress bar
        for ($count=0; $count < 10; $count++) {
            sleep(1);
            $progressBar->update($count);
        }
        $progressBar->finish();

        return new Response();
    }

    public function barcodeAction(Request $request, Application $app)
    {

        $barcodeHelper = HelperFactory::build('BarcodeHelper');
        $this->setMenu(self::$backMenus, $request, $app);
        $url = $app['url_generator']->generate($request->get('_route'), [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $app['twig']->render(
            'back/demo/barcode.twig',
            [
                'title'   => 'Barcode',
                'barcode1DCode39'      => $barcodeHelper->create1DPngBarcode(strtoupper('Ilmatar rocks')),
                'barcode1DEan13'      => $barcodeHelper->create1DPngBarcode('978212345680', BarcodeHelper::BARECODE_1D_EAN13),
                'barcode1DEan8'       => $barcodeHelper->create1DPngBarcode('6583325', BarcodeHelper::BARECODE_1D_EAN8),
                'barcode1DUpca'       => $barcodeHelper->create1DPngBarcode('97821234568', BarcodeHelper::BARECODE_1D_UPCA),
                'barcode1DUpce'       => $barcodeHelper->create1DPngBarcode('6583325', BarcodeHelper::BARECODE_1D_UPCE),
                'barcode1DCode128'    => $barcodeHelper->create1DPngBarcode(strtoupper('Ilmatar rocks'), BarcodeHelper::BARECODE_1D_CODE128),
                'barcode2DQrCode'     => $barcodeHelper->create2DPngBarcode($url),
                'barcode2DDatamatrix' => $barcodeHelper->create2DPngBarcode($url, BarcodeHelper::BARECODE_2D_DATAMATRIX),
                'barcode2DPdf417'     => $barcodeHelper->create2DPngBarcode($url, BarcodeHelper::BARECODE_2D_PDF417),
                'buttons' => $this->setButtons(
                    [
                        [
                            "type"   => self::BUTTON_TYPE_DOWNLOAD,
                            "action" => [
                                "type" => self::BUTTON_ACTION_OPEN_PAGE,
                                "data" => $app['url_generator']->generate('pipo-barcode-download')
                            ]
                        ]
                    ],
                    $app
                )
            ]
        );
    }

    public function barcodeDownloadAction(Request $request, Application $app)
    {
        $barcodeHelper   = HelperFactory::build('BarcodeHelper');
        $barcode1DCode39 = $barcodeHelper->create1DPngBarcode(strtoupper('Ilmatar rocks'));
        $barcode1DEan13 = $barcodeHelper->create1DPngBarcode('978212345680', BarcodeHelper::BARECODE_1D_EAN13);
        $barcode1DEan8 = $barcodeHelper->create1DPngBarcode('6583325', BarcodeHelper::BARECODE_1D_EAN8);

        $url                 = $app['url_generator']->generate($request->get('_route'), [], UrlGeneratorInterface::ABSOLUTE_URL);
        $barcode2DQrCode     = $barcodeHelper->create2DPngBarcode($url);
        $barcode2DDatamatrix = $barcodeHelper->create2DPngBarcode($url, BarcodeHelper::BARECODE_2D_DATAMATRIX);
        $barcode2DPdf41      = $barcodeHelper->create2DPngBarcode($url, BarcodeHelper::BARECODE_2D_PDF417);
        $pdfHelper = HelperFactory::build(
            'PdfHelper',
            ['app.root'=> $app['app.root']]
        );
        return HelperFactory::build('HttpHelper', ["validator" => ""])->downloadFromString(
            $pdfHelper->generatePdf(
                $app['app.root'] . '/views/back/demo/barcodeTemplate.htm',
                [
                    'title'                     => 'Barcode PDF export',
                    'barcode1DCode39Mime'       => $barcode1DCode39['mime'],
                    'barcode1DCode39Binary'     => base64_encode($barcode1DCode39['binary']),
                    'barcode1DEan13Mime'        => $barcode1DEan13['mime'],
                    'barcode1DEan13Binary'      => base64_encode($barcode1DEan13['binary']),
                    'barcode1DEan8Mime'         => $barcode1DEan8['mime'],
                    'barcode1DEan8Binary'       => base64_encode($barcode1DEan8['binary']),
                    'barcode2DQrCodeMime'       => $barcode2DQrCode['mime'],
                    'barcode2DQrCodeBinary'     => $barcode2DQrCode['binary'],
                    'barcode2DDatamatrixMime'   => $barcode2DDatamatrix['mime'],
                    'barcode2DDatamatrixBinary' => $barcode2DDatamatrix['binary'],
                    'barcode2DPdf417Mime'       => $barcode2DPdf41['mime'],
                    'barcode2DPdf417Binary'     => $barcode2DPdf41['binary']
                ]
            ),
            HelperFactory::build('FileSystemHelper')->getMimeType('.pdf'),
            'barcode.pdf'
        );
    }

    public function displayDocumentAction(Request $request, Application $app)
    {
        $this->setMenu(self::$backMenus, $request, $app);

        return $app['twig']->render(
            'back/demo/document.twig',
            array(
                'title'   => $app['translator']->trans('Document list'),
                'buttons' => $this->setButtons(
                    array(
                        array(
                            "type"   => self::BUTTON_TYPE_ADD,
                            "title"  => $app['translator']->trans('Upload a document'),
                            "action" => array(
                                "type" => self::BUTTON_ACTION_OPEN_MODAL_WINDOW,
                                "data" => "uploadFormContainer"
                            )
                        )
                    ),
                    $app
                ),
                //Builds up upload form
                'uploadForm' => $this->getUploadForm($app)->createView(),
                //Builds up JqGrid columns
                'jqGridColNames'     => array(
                    $app['translator']->trans('Name'),
                    $app['translator']->trans('Mime type'),
                    $app['translator']->trans('Upload date'),
                    $app['translator']->trans('Download'),
                ),
                'jqGridColModels'    => array(
                    array(
                        "name"          => FileSystemHelper::FILE_BASENAME,
                        "searchoptions" => array(
                            "sopt"   => JqGrid::$txtOperators
                        )
                    ),
                    array(
                        "name"          => FileSystemHelper::FILE_MIME,
                        "searchoptions" => array(
                            "sopt"   => JqGrid::$txtOperators
                        )
                    ),
                    array(
                        "name"          => FileSystemHelper::FILE_ATIME,
                        "searchoptions" => array(
                            "sopt"   => JqGrid::$numOperators
                        ),
                        "formatter"     => "date",
                        "formatoptions" => array(
                           "srcformat" => JqGrid::DATETIME_STORAGE_FORMAT,
                           "newformat" => JqGrid::DATETIME_DISPLAY_FORMAT
                        )
                    ),
                    array(
                        "name"          => "download",
                        "search"        => false,
                        "sortable"      => false,
                        "width"         => 40
                    )
                ),
                'jqGridColGroups'    => [],
                'jqGridFooterData'   => [],
                'jqGridRowIdUrlParam' => JqGrid::PARAM_URL_ROW_ID,
                'jqGridDataReadUrl'  => $app['url_generator']->generate('pipo-document-load'),
                'jqGridSortName'     => FileSystemHelper::FILE_BASENAME,
                'jqGridSortOrder'    => 'asc',
                'jqGridName'         => $app['translator']->trans('Recorded documents'),
                'jqInitialFilter'    => [],
                'jqGridUserDataOnFooter' => 'false'
            )
        );
    }

    public function loadDocumentAction(Request $request, Application $app)
    {
        $isSearch = (is_null($request->get(JqGrid::JQGRID_KEY_SEARCH))
            || "false" == $request->get(JqGrid::JQGRID_KEY_SEARCH)) ? false : true;
        $page     = is_null($request->get(JqGrid::JQGRID_KEY_PAGE))
            ? 1 : $request->get(JqGrid::JQGRID_KEY_PAGE);
        $pagesize = is_null($request->get(JqGrid::JQGRID_KEY_ROWS))
            ? 10 : $request->get(JqGrid::JQGRID_KEY_ROWS);
        $sidx     = $request->get(JqGrid::JQGRID_KEY_SIDX);
        $sord     = $request->get(JqGrid::JQGRID_KEY_SORD);
        $filters  = $request->get(JqGrid::JQGRID_KEY_FILTERS);

        //Gets file system data
        $files  = HelperFactory::build('FileSystemHelper')->getFileInfosByDir(
            $app['app.root'] . '/src',
            true,
            array(
                FileSystemHelper::FILE_DIRNAME,
                FileSystemHelper::FILE_BASENAME,
                FileSystemHelper::FILE_MIME,
                FileSystemHelper::FILE_ATIME
            )
        );
        //Use local database to apply filters
        $metadata = array(
            array(
                'name' => FileSystemHelper::FILE_DIRNAME,
                'type' => 'TEXT'
            ),
            array(
                'name' => FileSystemHelper::FILE_BASENAME,
                'type' => 'TEXT'
            ),
            array(
                'name' => FileSystemHelper::FILE_MIME,
                'type' => 'TEXT'
            ),
            array(
                'name' => FileSystemHelper::FILE_ATIME,
                'type' => 'INTEGER'
            )
        );
        $orderBy = JqGrid::getRawQueryOrderClause($sidx, $sord);
        $where   = JqGrid::getRawQueryWhereClause(
            $isSearch,
            $filters,
            array(//Unformats date
                FileSystemHelper::FILE_ATIME => function ($date) use ($app) {
                    switch (substr_count($date, ':')) {
                        case 0:
                            $date = trim($date) . ' 00:00:00';
                            break;
                        case 1:
                            $date = trim($date) . ':00';
                            break;
                        default:
                            //nothing
                    }
                    return Carbon::createFromFormat(JqGrid::DATETIME_DISPLAY_FORMAT, $date)->getTimestamp();
                }
            )
        );
        $rows    = HelperFactory::build('DbHelper')->executeSqlAgainstArray($metadata, $files, trim($where . $orderBy));

        $count        = count($rows);
        $totalPages   = 0;
        if ($count > 0) {
            $totalPages = ceil($count/$pagesize);
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $subRows = array_slice($rows, $page * $pagesize - $pagesize, $pagesize);

        foreach ($subRows as $idx => $row) {
            //Formats download link
            $path = $row[FileSystemHelper::FILE_DIRNAME] . DIRECTORY_SEPARATOR . $row[FileSystemHelper::FILE_BASENAME];
            $subRows[$idx]['download'] = sprintf(
                '<a href="%s"><i class="fa fa-download fa-2x" title="%s"></i></a>',
                $app['url_generator']->generate('pipo-document-download', array('path' => base64_encode($path))),
                $app['translator']->trans('Download this document')
            );
            $subRows[$idx][FileSystemHelper::FILE_ATIME] =
                Carbon::createFromTimeStamp(intval($subRows[$idx][FileSystemHelper::FILE_ATIME]))->format(JqGrid::DATETIME_STORAGE_FORMAT);//Formats date
            //Removes useless field now
            unset($subRows[$idx][FileSystemHelper::FILE_DIRNAME]);
        }
        return $app->json(
            array(
                'rows'    => $subRows,
                'page'    => $page,
                'total'   => $totalPages,
                'records' => $count
            )
        );
    }

    public function uploadDocumentAction(Request $request, Application $app)
    {
        $uploadForm = $this->getUploadForm($app);
        $uploadForm->bind($request);
        if ($uploadForm->isValid()) {
            $files = $request->files->get($uploadForm->getName());
            $files['path']->move(
                $app['app.var'],
                $files['path']->getClientOriginalName()
            );

            if (file_exists($app['app.var'] . DIRECTORY_SEPARATOR . $files['path']->getClientOriginalName())) {
                $app['notification'](
                    $app['translator']->trans('File has been correctly uploaded.'),
                    'success'
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        'pipo-document-display'
                    )
                );
            }
        }
        $app['notification'](
            $app['translator']->trans('Oups! Something wrong happened.'),
            'error'
        );
        return $app->handle(
            Request::create(
                $app['url_generator']->generate('pipo-document-display'),
                'GET'
            ),
            HttpKernelInterface::SUB_REQUEST
        );
    }

    public function downloadDocumentAction($path, Application $app)
    {
        $rootFolder = $app['app.root'] . '/src';
        return self::download(
            str_replace($rootFolder, '', $path),
            'dummy',
            ['dummy' => $rootFolder],
            $app
        );
    }

    public function wordAction(Request $request, Application $app)
    {
        $finalDocName    = 'final_doc.docx';
        $templateDocName = 'template.docx';
        $finalPdfDocName = 'final_doc.pdf';
        
        $this->setMenu(self::$backMenus, $request, $app);

        $user       = $app['security']->getToken()->getUser();
        $wordHelper = HelperFactory::build('WordHelper', ['app.root' => $app['app.root']]);
        $document   = $wordHelper->createDocumentFromTemplate(
            $app['app.root'] . '/views/back/demo/' . $templateDocName,
            null,
            [
                'firstname'  => $user->getFirstname(),
                'lastname'   => strtoupper($user->getLastname()),
                'username'   => $user->getUsername(),
                'time'       => date('H:i'),
                'serverName' => realpath(__DIR__)
            ]
        );
            
        $document->cloneBlock('CLONEME', 3);
        $document->deleteBlock('DELETEME');
        $document->cloneRow('rowValue', 3);
        $document->setValue('rowValue#1', 'Sun');
        $document->setValue('rowValue#2', 'Mercury');
        $document->setValue('rowValue#3', 'Venus');
        $document->setValue('rowNumber#1', '1');
        $document->setValue('rowNumber#2', '2');
        $document->setValue('rowNumber#3', '3');
        $document->cloneRow('userId', 2);
        $document->setValue('userId#1', '1');
        $document->setValue('userFirstName#1', 'James');
        $document->setValue('userName#1', 'Taylor');
        $document->setValue('userPhone#1', '+1 428 889 773');
        $document->setValue('userId#2', '2');
        $document->setValue('userFirstName#2', 'Robert');
        $document->setValue('userName#2', 'Bell');
        $document->setValue('userPhone#2', '+1 428 889 774');

        $wordHelper->save(
            $app['app.var'] . '/export/' . $finalDocName,
            $app['app.var'] . '/export/' . $finalPdfDocName,
            \Ilmatar\Helper\WordHelper::WORD_AND_PDF
        );
       
        $securityHelper = HelperFactory::build('SecurityHelper');
        
        return $app['twig']->render(
            'back/demo/word.twig',
            [
                'title' => 'Word document',
                'finalDocLink' => $app['url_generator']->generate(
                    'pipo-word-download',
                    [
                        'type'  => self::FILE_TYPE_WORD,
                        'token' => rawurlencode($securityHelper->encryptString($finalDocName))
                    ]
                ),
                'finalPdfDocLink' => $app['url_generator']->generate(
                    'pipo-word-download',
                    [
                        'type'  => self::FILE_TYPE_PDF,
                        'token' => rawurlencode($securityHelper->encryptString($finalPdfDocName))
                    ]
                ),
            ]
        );
    }
    
    public function downloadWordAction($type, $token, Application $app)
    {
        return self::download(
            $token,
            self::FILE_TYPE_WORD,
            [
                self::FILE_TYPE_WORD => $app['app.var'] . '/export/' ,
                self::FILE_TYPE_PDF  => $app['app.var'] . '/export/'
            ],
            $app
        );
    }

    protected function getUploadForm(Application $app)
    {
        return $app['form.factory']->createBuilder('form')
            ->add(
                'path',
                'file',
                [
                    'label'  => $app['translator']->trans('Path'),
                    'constraints' => array(new Assert\File(array('maxSize' => $app['upload.size.max'])))
                ]
            )
            ->getForm();
    }
}
