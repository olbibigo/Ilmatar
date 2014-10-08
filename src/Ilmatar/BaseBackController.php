<?php
namespace Ilmatar;

use Ilmatar\Application;
use Ilmatar\HelperFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;
use Ilmatar\Helper\ArrayHelper;
use Ilmatar\Exception\TranslatedException;

abstract class BaseBackController extends BaseController
{
    //Warning: same name into export.twig & subLayout.js
    const EXPORT_FORMAT_PARAM_NAME = 'exportFormat';
    const EXPORT_FORMAT_XLS        = 'XLS';
    const EXPORT_FORMAT_PDF        = 'PDF';
    const EXPORT_FORMAT_CSV        = 'CSV';
    const EXPORT_FORMAT_XML        = 'XML';

    const EXPORT_PERIMETER_PARAM_NAME = 'exportPerimeter';
    const EXPORT_PERIMETER_FULL       = "Full data";
    const EXPORT_PERIMETER_PAGE       = "Only current page";

    const EXPORT_NUMBER_DATE_PARAM_NAME = 'exportNumberDateFormat';
    const EXPORT_NUMBER_DATE_ENGLISH    = "x.x and yyyy-mm-dd";
    const EXPORT_NUMBER_DATE_LOCAL      = "x,x and dd/mm/yyyy";

    const EXPORT_ORIENTATION_PARAM_NAME = 'exportOrientation';
    //Possible values are defined into Ilmatar\Helper\PdfHelper

    const EXPORT_COLUMN_PARAM_NAME = 'exportColumns';

    const OPTION_HAS_FOOTER      = "hasFooter";
    const OPTION_HAS_FULL_TOTAL  = "hasFullTotal";
    const OPTION_INITIAL_FILTER  = "initialFilter";
    const OPTION_IS_STRIPPED_TAG = "areStrippedTags";
    const OPTION_HIGHLIGHTS      = "highlights";
    const OPTION_STYLES          = "styles";

    protected function displayEditableJqPage(
        $entityName,
        $gridName,
        $twigOptions,
        $readRouteName,
        $writeRouteName,
        Application $app,
        $twigModel = 'back/editableGrid.twig',
        array $options = []
    ) {
        $content = $app['twig']->render(
            $twigModel,
            array_merge(
                $twigOptions,
                $this->buildDataForEditableJqPage(
                    $entityName,
                    $gridName,
                    $readRouteName,
                    $writeRouteName,
                    $app,
                    $options
                )
            )
        );
        return new Response($content);
    }

    protected function processJqAjaxInformations(Request $request)
    {
        $isSearch = (is_null($request->get(JqGrid::JQGRID_KEY_SEARCH))
            || "false" == $request->get(JqGrid::JQGRID_KEY_SEARCH)) ? false : true;
        $pagesize = is_null($request->get(JqGrid::JQGRID_KEY_ROWS))
            ? 10 : $request->get(JqGrid::JQGRID_KEY_ROWS);
        $sidx     = $request->get(JqGrid::JQGRID_KEY_SIDX);
        $sord     = $request->get(JqGrid::JQGRID_KEY_SORD);
        $filters  = $request->get(JqGrid::JQGRID_KEY_FILTERS);
        $page = $request->get(JqGrid::JQGRID_KEY_PAGE);
        if (is_null($page) || $page < 1) {
            $page = 1;
        }

        //Use local database to apply filters
        $orderBy = JqGrid::getRawQueryOrderClause($sidx, $sord);

        // $start = ($page-1)*$pagesize-1;
        // if ($start < 0)
        if (0 > $start = ($page-1) * $pagesize - 1) {
            $start = 0;
        }
        $limit = sprintf('%s OFFSET %s', $pagesize, $start);

        $where   = JqGrid::getRawQueryWhereClause(
            $isSearch,
            $filters,
            array()
        );
        return ['where' => trim($where), 'orderBy' => trim($orderBy), 'limit' => $limit, 'page' => $page, 'pagesize' => $pagesize];
    }

    protected function displayJqPage(
        $entityName,
        $gridName,
        $twigOptions,
        $readRouteName,
        Application $app,
        $twigModel = 'back/grid.twig',
        array $options = []
    ) {
        $content = $app['twig']->render(
            $twigModel,
            array_merge(
                $twigOptions,
                $this->buildDataForJqPage(
                    $entityName,
                    $gridName,
                    $readRouteName,
                    $app,
                    $options
                )
            )
        );
        return new Response($content);
    }

    protected function buildDataForEditableJqPage(
        $entityName,
        $gridName,
        $readRouteName,
        $writeRouteName,
        Application $app,
        array $options = []
    ) {
        $data = $this->buildDataForJqPage(
            $entityName,
            $gridName,
            $readRouteName,
            $app,
            $options
        );
        $columns = $data['jqGridColModels'];
        unset($data['jqGridColModels']);
        return array_merge(
            $data,
            [
                'jqGridColModels'        => $this->checkWritePermission($columns, $app),
                'jqGridDataWriteUrl'     => ($writeRouteName[0] === '/') ? $writeRouteName : $app['url_generator']->generate($writeRouteName),
                'csrfToken'              => $this->generateToken($app)
            ]
        );
    }

    protected function buildDataForJqPage(
        $entityName,
        $gridName,
        $readRouteName,
        Application $app,
        array $options = []
    ) {
        $repository = $app['orm.em']->getRepository($entityName);

        return [
            'jqGridColNames'         => $this->translate($repository->getJqGridColNames(), $app['translator'], $options),
            'jqGridColModels'        => $repository->getJqGridColModels($app['translator'], $app['url_generator'], $options),
            'jqGridColGroups'        => $repository->getJqGridColGroups($app['translator'], $options),
            'jqGridFooterData'       => $repository->getJqGridFooterData($app['translator'], $options),
            'jqGridDataReadUrl'      => ($readRouteName[0] === '/') ? $readRouteName : $app['url_generator']->generate($readRouteName),
            'jqGridSortName'         => '',
            'jqGridSortOrder'        => '',
            'jqGridName'             => $gridName,
            'jqInitialFilter'        => isset($options[self::OPTION_INITIAL_FILTER]) ? $options[self::OPTION_INITIAL_FILTER] : [],
            'jqGridUserDataOnFooter' => self::isTrue($options, self::OPTION_HAS_FOOTER) ? 'true' : 'false',
            'jqGridRowIdUrlParam'    => JqGrid::PARAM_URL_ROW_ID
        ];
    }

    protected function loadJqPage($entityName, Request $request, Application $app, $isSlicedResponse = true, array $options = [])
    {
        $repository = $app['orm.em']->getRepository($entityName);
        $filters = json_decode($request->get(JqGrid::JQGRID_KEY_FILTERS));
        if (is_object($filters)) {
            $filters = get_object_vars($filters);
        }
        if (isset($options['filter'])) {
            $filters["groupOp"] = "AND";
            $filters["rules"] = array_merge(
                empty($filters["rules"]) ? [] : $filters["rules"],
                empty($options['filter']) ? [] : $options['filter']
            );
        }
        try {
            return $repository->getJqGridColData(
                (!is_null($request->get(JqGrid::JQGRID_KEY_SEARCH))
                    && "true" == $request->get(JqGrid::JQGRID_KEY_SEARCH))
                || (!empty($options['filter'])) ? true : false,
                is_null($request->get(JqGrid::JQGRID_KEY_PAGE)) ? 1 : intval($request->get(JqGrid::JQGRID_KEY_PAGE)),
                is_null($request->get(JqGrid::JQGRID_KEY_ROWS)) ? 10 : $request->get(JqGrid::JQGRID_KEY_ROWS),
                $request->get(JqGrid::JQGRID_KEY_SIDX),
                $request->get(JqGrid::JQGRID_KEY_SORD),
                json_encode($filters),
                $app['translator'],
                $isSlicedResponse,
                $options
            );
        } catch (\Exception $e) {
            return array("error" => true, "message" => $app['translator']->trans($e->getMessage()));
        }
    }

    protected function selectJqPage($entity, $filter, $order, $key, $value, $em, $isWithEmpty = false)
    {
        $objs = $em->getRepository($entity)->findBy(
            $filter,
            $order
        );
        $out = HelperFactory::build('ObjectHelper')->getHTMLSelectFromObjects(
            $objs,
            [$key, $value],
            $key,
            $value,
            $isWithEmpty
        );
        return new \Symfony\Component\HttpFoundation\Response(
            $out,
            200
        );
    }

    protected function exportJqPage($entityName, Request $request, Application $app, array $options = [])
    {
        $options['isExport'] = true;

        $content = $this->loadJqPage(
            $entityName,
            $request,
            $app,
            self::EXPORT_PERIMETER_PAGE == $request->get(self::EXPORT_PERIMETER_PARAM_NAME),
            $options
        );
        $exported = $content['rows'];

        $isFullTotalDisplayed = self::isTrue($options, self::OPTION_HAS_FOOTER)
            && self::isTrue($options, self::OPTION_HAS_FULL_TOTAL)
            && ($content['records'] > count($exported));

        if (self::isTrue($options, self::OPTION_HAS_FOOTER)) {
            $exported[] = $content['sum'];
            if ($isFullTotalDisplayed) {
                $exported[] = $content['fullSum'];
            }
        }
        $columnNames = explode(';', $request->get(self::EXPORT_COLUMN_PARAM_NAME));
        array_shift($columnNames);//Removes 'rn' (jqgrid row counter)
        $columnType = [];
        $data = $app['orm.em']->getRepository($entityName)->padAndFormatJqGridRows(
            $exported,
            self::EXPORT_NUMBER_DATE_LOCAL == $request->get(self::EXPORT_NUMBER_DATE_PARAM_NAME),
            $columnNames,
            $columnType
        );

        return $this->exportData(
            $data,
            $request,
            $app,
            $columnType,
            $options
        );
    }

    protected function changeJqPage($entityName, Request $request, Application $app)
    {
        if (!$this->isValidToken($request->get(self::PARAM_TOKEN), $app)) {
            return $app->json(
                ["error" => true, "message" => "Invalid token"]
            );
        }
        $oper       = $request->get(JqGrid::JQGRID_KEY_OPER);
        $repository = $app['orm.em']->getRepository($entityName);
        $values     = $request->request->all();
        unset($values[JqGrid::JQGRID_KEY_OPER], $values[self::PARAM_TOKEN]);

        $cleanValues = $repository->unformatAll($values, $app['translator']);

        if ($oper != JqGrid::JQGRID_ACTION_ADD) {
            $entity = $app['orm.em']->find($entityName, $cleanValues['id']);
            if (is_null($entity)) {
                $json = ["error" => true, "message" => $app['translator']->trans("Unknown object so cannot perform requested operation.")];
            }
        } else {
            $entity = new $entityName($cleanValues);
        }
        switch($oper) {
            case JqGrid::JQGRID_ACTION_DELETE:
                try {
                    $repository->processJqGridSpecialFields($entity, $cleanValues, $oper, $app['translator']);
                    $app['orm.em']->remove($entity);
                    $app['orm.em']->flush();
                    $json = ["error" => false, "id" => $cleanValues['id']];
                } catch (\Doctrine\DBAL\DBALException $e ) {
                    if ($e->getPrevious()->getCode() === "23000") {
                        $json = array("error" => true, "message" => $app['translator']->trans("This element is used in an another part of this application."));
                    } else {
                        $json = array("error" => true, "message" => $app['translator']->trans($e->getPrevious()->getMessage()));
                    }
                }catch (\Exception $e) {
                    $json = ["error" => true, "message" => $app['translator']->trans($e->getMessage())];
                }
                break;
            case JqGrid::JQGRID_ACTION_ADD:
            case JqGrid::JQGRID_ACTION_UPDATE:
                try {
                    $repository->processJqGridSpecialFields($entity, $cleanValues, $oper, $app['translator']);
                    unset($cleanValues['id']);
                    foreach ($cleanValues as $key => $value) {
                        $setter = $repository->getAccessor($key, JqGrid::SETTER);
                        if (is_callable([$entity, $setter])) {
                            $entity->$setter($value);
                        }
                    }
                    $errors = $app['validator']->validate($entity);
                    if (count($errors) > 0) {
                        $json = [
                            "error" => true,
                            "message" => $errors[0]->getMessage()
                        ];
                        break;
                    }
                    $app['orm.em']->persist($entity);
                    $app['orm.em']->flush();
                    $json = ["error" => false, "id" => $entity->getId()];
                } catch (TranslatedException $e) {
                    $json = ["error" => true, "message" => $e->getTranslatedMessage($app['translator'])];
                } catch (\Exception $e) {
                    if (false !== stripos($e->getMessage(), 'duplicate entry')) {
                        if (preg_match("%Duplicate entry (?P<key>.+) for%", $e->getMessage(), $match)) {
                            $json = [
                                "error"  => true,
                                "message" => sprintf(
                                    $app['translator']->trans('Unique constraint failed for value %s.'),
                                    $match['key']
                                )
                            ];
                        }
                    } else {
                        $json = ["error" => true, "message" => $app['translator']->trans($e->getMessage())];
                    }
                }
                break;
            default:
                $json = ["error" => true, "message" => $app['translator']->trans("Unknown operation sent to server.")];
        }
        $json[JqGrid::JQGRID_KEY_OPER] = $oper;

        return $app->json(
            $json
        );
    }

    protected function translate(array $data, Translator $translator)
    {
        return array_map(
            function ($item) use ($translator) {
                return $translator->trans($item);
            },
            $data
        );
    }

    protected function checkWritePermission(Array &$columns, Application $app)
    {
        if (!isset($app['security']) || !$app['orm.em']->getRepository('\\Entities\\Permission')->isAllowedFunctionality(
            $app['security']->getToken()->getUser(),
            static::$DEFAULT_CREDENTIALS["functionality"],
            \Entities\Permission::ACCESS_READWRITE
        )) {
            foreach ($columns as $idx => $column) {
                 $columns[$idx]['editable'] = false;
            }
        }
        return $columns;
    }

    /*
     * Method called when using dedicated forms to edit entities
     */
    protected function processChange($entity, $values, $redirectRoute, Application $app)
    {
        $repository = $app['orm.em']->getRepository(get_class($entity));
        $oper       = $values[JqGrid::JQGRID_KEY_OPER];
        switch($oper) {
            case JqGrid::JQGRID_ACTION_DELETE:
                try {
                    $objId = $entity->getId();
                    $repository->processJqGridSpecialFields(
                        $entity,
                        $values,
                        $oper,
                        $app['translator'],
                        ['user' => (isset($app['security']) ? $app['security']->getToken()->getUser() : null)]
                    );
                    $app['orm.em']->remove($entity);
                    $app['orm.em']->flush();
                    $app['notification'](
                        sprintf(
                            $app['translator']->trans("Requested operation executed successfully (id: %s)."),
                            $objId
                        ),
                        'success'
                    );
                    if (!is_null($redirectRoute)) {
                        return $app->redirect($app['url_generator']->generate($redirectRoute));
                    }
                } catch (\Exception $e) {
                    $app['notification']($app['translator']->trans($e->getMessage()), 'error');
                }

                break;
            case JqGrid::JQGRID_ACTION_ADD:
            case JqGrid::JQGRID_ACTION_UPDATE:
                try {
                    $repository->processJqGridSpecialFields(
                        $entity,
                        $values,
                        $oper,
                        $app['translator'],
                        ['user' => (isset($app['security']) ? $app['security']->getToken()->getUser() : null)]
                    );
                    $errors = $app['validator']->validate($entity);
                    if (count($errors) > 0) {
                        $app['notification']($app['translator']->trans($errors[0]->getMessage()));
                        break;
                    }
                    $app['orm.em']->persist($entity);
                    $app['orm.em']->flush();
                    $app['notification'](
                        sprintf(
                            $app['translator']->trans("Requested operation executed successfully (id: %s)."),
                            $entity->getId()
                        ),
                        'success'
                    );
                    //Redirects to list
                    if (!is_null($redirectRoute)) {
                        return $app->redirect($app['url_generator']->generate($redirectRoute));
                    }
                } catch (TranslatedException $e) {
                    $app['notification']($e->getTranslatedMessage($app['translator']), 'error');
                } catch (\Exception $e) {
                    if (false !== stripos($e->getMessage(), 'duplicate entry')) {
                        if (preg_match("%Duplicate entry (?P<key>.+) for%", $e->getMessage(), $match)) {
                            $app['notification'](
                                sprintf(
                                    $app['translator']->trans('Unique constraint failed for value %s.'),
                                    $match['key']
                                ),
                                'error'
                            );
                        }
                    } else {
                        $app['notification']($app['translator']->trans($e->getMessage()), 'error');
                    }
                }
                break;
            default:
                $app['notification']($app['translator']->trans("Unknown operation sent to server."), 'error');
        }
        return new Response();
    }

    protected function exportData(array $content, Request $request, Application $app, array $columnType = [], array $options = [])
    {
        switch ($request->get(self::EXPORT_FORMAT_PARAM_NAME)) {
            case self::EXPORT_FORMAT_XML:
                //Into XML exports, tag name are field name
                return HelperFactory::build('HttpHelper', ["validator" => ""])->downloadFromString(
                    HelperFactory::build('ArrayHelper')->getXmlFromArray(
                        $content,
                        'items',
                        'item',
                        'full_string'
                    ),
                    HelperFactory::build('FileSystemHelper')->getMimeType('.xml'),
                    'export.xml'
                );
                break;
            case self::EXPORT_FORMAT_XLS:
                if (!empty($content)) {
                    return HelperFactory::build('HttpHelper', ["validator" => ""])->downloadFromString(
                        HelperFactory::build('ExcelHelper')->createExport(
                            $content,
                            $columnType,
                            $app['translator'],
                            isset($options[self::OPTION_STYLES]) ? $options[self::OPTION_STYLES] : [],
                            isset($options[self::OPTION_HIGHLIGHTS]) ? $options[self::OPTION_HIGHLIGHTS] : [ArrayHelper::HIGHLIGHT_ZEBRA_ROW]
                        ),
                        HelperFactory::build('FileSystemHelper')->getMimeType('.xls'),
                        'export.xls'
                    );
                }
                break;
            case self::EXPORT_FORMAT_CSV:
                //Into CSV exports, column name are translated field name
                return HelperFactory::build('HttpHelper', ["validator" => ""])->downloadFromString(
                    HelperFactory::build('ArrayHelper')->getCsvFromArray($content, ';', $app['translator']),
                    HelperFactory::build('FileSystemHelper')->getMimeType('.csv'),
                    'export.csv'
                );
                break;
            case self::EXPORT_FORMAT_PDF:
                $pdfHelper        = HelperFactory::build(
                    'PdfHelper',
                    ['app.root'=> $app['app.root']]
                );
                return HelperFactory::build('HttpHelper', ["validator" => ""])->downloadFromString(
                    $pdfHelper->generatePdf(
                        '',
                        [
                            'title' => '',
                            'body'  => HelperFactory::build('ArrayHelper')->getHtmlTableFromArray(
                                $content,
                                $app['translator'],
                                BaseController::isTrue($options, self::OPTION_IS_STRIPPED_TAG) ? $options[self::OPTION_IS_STRIPPED_TAG] : true,
                                isset($options[self::OPTION_STYLES]) ? $options[self::OPTION_STYLES] : [],
                                isset($options[self::OPTION_HIGHLIGHTS]) ? $options[self::OPTION_HIGHLIGHTS] : [ArrayHelper::HIGHLIGHT_ZEBRA_ROW]
                            )
                        ],
                        null,
                        $request->get(self::EXPORT_ORIENTATION_PARAM_NAME)
                    ),
                    HelperFactory::build('FileSystemHelper')->getMimeType('.pdf'),
                    'export.pdf'
                );
                break;
            default:
                new \Exception(
                    sprintf('Invalid export format %s into %s', $request->get(self::EXPORT_FORMAT_PARAM_NAME), __FUNCTION__)
                );
        }
        return new Response();
    }
}
