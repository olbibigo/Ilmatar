<?php
namespace Ilmatar;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Carbon\Carbon;
use Ilmatar\HelperFactory;

class JqGrid extends EntityRepository
{
    const PARAM_URL_ROW_ID         = '-666';
    const ID_NEW_ENTITY            = 'new';
    const MAX_CHAR_INPUT_TEXT      = 256;
    const TYPE_ASSOCIATION         = "foreignkey";
    const TO_REMOVE                = "TO_REMOVE";
    const DATE_STORAGE_FORMAT      = 'Y-m-d';
    const DATETIME_STORAGE_FORMAT  = 'Y-m-d H:i:s';
    const DATE_DISPLAY_FORMAT      = 'd/m/Y';
    const DATETIME_DISPLAY_FORMAT  = 'd/m/Y H:i:s';
    const IS_WEBSERVICE            = 'is_webservice';

    const GETTER = 'get';
    const SETTER = 'set';

    const JQGRID_KEY_OPER                       = "oper";
    const JQGRID_KEY_SEARCH                     = "_search";
    const JQGRID_KEY_PAGE                       = "page";
    const JQGRID_KEY_ROWS                       = "rows";
    const JQGRID_KEY_SIDX                       = "sidx";
    const JQGRID_KEY_SORD                       = "sord";
    const JQGRID_KEY_FILTERS                    = "filters";
    const JQGRID_KEY_EXPORT_PERIMETER           = "exportPerimeter";
    const JQGRID_KEY_EXPORT_FORMAT              = "exportFormat";

    const JQGRID_ACTION_DELETE                  = "del";
    const JQGRID_ACTION_ADD                     = "add";
    const JQGRID_ACTION_UPDATE                  = "edit";

    const JQGRID_TRUE                           = "Yes";
    const JQGRID_FALSE                          = "No";

    const JQGRID_OPERATOR_EQUAL                 = "eq";
    const JQGRID_OPERATOR_NOT_EQUAL             = "ne";
    const JQGRID_OPERATOR_LESS_THAN             = "lt";
    const JQGRID_OPERATOR_LESS_THAN_OR_EQUAL    = "le";
    const JQGRID_OPERATOR_GREATER_THAN          = "gt";
    const JQGRID_OPERATOR_GREATER_THAN_OR_EQUAL = "ge";
    const JQGRID_OPERATOR_BEGINS_WITH           = "bw";
    const JQGRID_OPERATOR_DOES_NOT_BEGIN_WITH   = "bn";
    const JQGRID_OPERATOR_IS_IN                 = "in";
    const JQGRID_OPERATOR_IS_NOT_IN             = "ni";
    const JQGRID_OPERATOR_ENDS_WITH             = "ew";
    const JQGRID_OPERATOR_DOES_NOT_END_WITH     = "en";
    const JQGRID_OPERATOR_CONTAINS              = "cn";
    const JQGRID_OPERATOR_DOES_NOT_CONTAIN      = "nc";

    public static $operators = [
        self::JQGRID_OPERATOR_EQUAL                 => '=', //equal
        self::JQGRID_OPERATOR_NOT_EQUAL             => '<>',//not equal
        self::JQGRID_OPERATOR_LESS_THAN             => '<', //less than
        self::JQGRID_OPERATOR_LESS_THAN_OR_EQUAL    => '<=',//less than or equal
        self::JQGRID_OPERATOR_GREATER_THAN          => '>', //greater than
        self::JQGRID_OPERATOR_GREATER_THAN_OR_EQUAL => '>=',//greater than or equal
        self::JQGRID_OPERATOR_BEGINS_WITH           => 'LIKE', //begins with
        self::JQGRID_OPERATOR_DOES_NOT_BEGIN_WITH   => 'NOT LIKE', //doesn't begin with
        self::JQGRID_OPERATOR_IS_IN                 => 'LIKE', //is in
        self::JQGRID_OPERATOR_IS_NOT_IN             => 'NOT LIKE', //is not in
        self::JQGRID_OPERATOR_ENDS_WITH             => 'LIKE', //ends with
        self::JQGRID_OPERATOR_DOES_NOT_END_WITH     => 'NOT LIKE', //doesn't end with
        self::JQGRID_OPERATOR_CONTAINS              => 'LIKE', // contains
        self::JQGRID_OPERATOR_DOES_NOT_CONTAIN      => 'NOT LIKE'  //doesn't contain
    ];
    public static $datetimeOperators  = [
        self::JQGRID_OPERATOR_LESS_THAN,
        self::JQGRID_OPERATOR_LESS_THAN_OR_EQUAL,
        self::JQGRID_OPERATOR_GREATER_THAN,
        self::JQGRID_OPERATOR_GREATER_THAN_OR_EQUAL
    ];
    public static $numOperators  = [
        self::JQGRID_OPERATOR_EQUAL,
        self::JQGRID_OPERATOR_NOT_EQUAL,
        self::JQGRID_OPERATOR_LESS_THAN,
        self::JQGRID_OPERATOR_LESS_THAN_OR_EQUAL,
        self::JQGRID_OPERATOR_GREATER_THAN,
        self::JQGRID_OPERATOR_GREATER_THAN_OR_EQUAL
    ];
    public static $txtOperators  = [
        self::JQGRID_OPERATOR_EQUAL,
        self::JQGRID_OPERATOR_BEGINS_WITH,
        self::JQGRID_OPERATOR_DOES_NOT_BEGIN_WITH,
        self::JQGRID_OPERATOR_ENDS_WITH,
        self::JQGRID_OPERATOR_DOES_NOT_END_WITH,
        self::JQGRID_OPERATOR_CONTAINS,
        self::JQGRID_OPERATOR_DOES_NOT_CONTAIN
    ];

    protected $listExcludeForDisplay = ['deleted_at', 'deleted_by'];
    protected $listExcludeForEdit    = ['created_at', 'created_by', 'updated_at', 'updated_by'];

    protected $columns      = null;
    protected $associations = null;
    protected $sums         = [];//Footer data

    public function count(array $wheres = [])
    {
        $qb = $this->createQueryBuilder('a')
                   ->select('COUNT(a)');

        foreach ($wheres as $key => $value) {
            if (is_null($value)) {
                $qb->andWhere(sprintf('a.%s IS NULL', $key));
            } else {
                $qb->andWhere(sprintf('a.%s = :%s', $key, $key))
                   ->setParameter($key, $value);
            }
        }
        return intval($qb->getQuery()->getSingleScalarResult());
    }

    public function isSingle(array $wheres = [])
    {
        return (1 == $this->count($wheres));
    }

    public function getJqGridColNames(array $options = [])
    {
        $this->columns = (isset($this->columns) ? $this->columns : $this->getClassMetadata()->fieldMappings);
        //@note: Association Mappings are not managed here. it should be done into repositories

        $out = [];
        foreach ($this->columns as $column) {
            if (!in_array($column['columnName'], $this->listExcludeForDisplay, true)) {
                $out[] = str_replace(
                    '_',
                    ' ',
                    ucfirst($column['columnName'])
                );
            }
        }
        return $out;
    }

    public function getJqGridColModels(Translator $translator, UrlGenerator $urlGenerator = null, array $options = [])
    {
        $this->columns = (isset($this->columns) ? $this->columns : $this->getClassMetadata()->fieldMappings);

        $out = [];
        foreach ($this->columns as $column) {
            if (!in_array($column['columnName'], $this->listExcludeForDisplay, true)) {
                $col = [
                    "name"          => $column['columnName'],
                    "index"         => $column['columnName'],
                    //Primary key is not editable
                    "editable"      => !((isset($column["id"]) && $column["id"]) || $this->isType($column, 'datetime') ),
                    "edittype"      => $this->getEditType($column, self::MAX_CHAR_INPUT_TEXT),
                    "editrules"     => $this->getEditRules($column),
                    "editoptions"   => $this->getEditOptions($column, $translator, $urlGenerator),
                    "searchoptions" => $this->getSortOpt($column, $translator),
                    "stype"         => $this->getSType($column),
                    "hidden"        => in_array($column['columnName'], $this->listExcludeForEdit, true)
                ];
                if ("id" == $column['columnName']) {
                    $col['fixed'] = true;
                    $col['width'] = 60;
                }
                if (in_array($column['columnName'], $this->listExcludeForEdit, true)) {
                    $col['editable'] = false;
                }
                $out[] = array_merge($col, $this->getJqGridColModelOption($column, $options));
            }
        }
        return $out;
    }

    public function getJqGridColGroups(Translator $translator, array $options = [])
    {
        return [];
    }

    public function getJqGridFooterData(Translator $translator, array $options = [])
    {
        return [];
    }

    public function getJqGridColModelOption($column, array $options = [])
    {
        $out = [];

        $align = $this->getAlign($column);
        if (!is_null($align)) {
            $out['align'] = $align;
        }
        $formatter = $this->getFormatter($column, $options);
        if (!is_null($formatter)) {
            $out['formatter'] = $formatter['formatter'];
            if (isset($formatter['formatoptions'])) {
                $out['formatoptions'] = $formatter['formatoptions'];
            }
        }

        return $out;
    }

    final public function getJqGridColData(
        $isSearch,
        $page,
        $pagesize,
        $sidx,
        $sord,
        $filters,
        Translator $translator,
        $isSlicedResponse = true,
        array $options = []
    ) {
        $fullSums = $this->sums;
        $isExport = isset($options['isExport']) && $options['isExport'];
        $filters  = json_decode($filters);
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb = $this->getQuerySelectClause($qb, 'e', $options);
        $qb = $this->getQueryOrderClause($qb, 'e', $sidx, $sord);
        $qb = $this->getQueryWhereClause(
            $isSearch,
            $qb,
            $filters,
            'e',
            $translator
        );
        if (!empty($options)) {
            $qb = $this->getQueryJoinClause($qb, 'e', $options);
        }

        $paginator    = new Paginator($qb->getQuery());
        $totalResults = count($paginator);
        $totalPages   = ceil($totalResults / $pagesize);

        $rows           = [];
        $sumColumnNames = array_keys($this->sums);
        if ($isSlicedResponse) {
            $paginator
                ->getQuery()
                ->setFirstResult($pagesize * ($page-1))
                ->setMaxResults($pagesize);
            foreach ($paginator as $result) {
                $row    = $this->formatJqGridRow($result, $translator, $options);
                $rows[] = $row;
                if (BaseController::isTrue($options, BaseBackController::OPTION_HAS_FOOTER)) {
                    foreach ($sumColumnNames as $name) {
                        $this->sums[$name] += $row[$name];
                    }
                }
            }
        } else {
            $results = $qb->getQuery()->getResult();
            foreach ($results as $result) {
                $row    = $this->formatJqGridRow($result, $translator, $options);
                $rows[] = $row;
                if (BaseController::isTrue($options, BaseBackController::OPTION_HAS_FOOTER)) {
                    foreach ($sumColumnNames as $name) {
                        $this->sums[$name] += $row[$name];
                    }
                }
            }
        }

        $isFullTotalDisplayed = BaseController::isTrue($options, BaseBackController::OPTION_HAS_FOOTER)
            && BaseController::isTrue($options, BaseBackController::OPTION_HAS_FULL_TOTAL)
            && ($totalResults > count($rows));
        
        if ($isFullTotalDisplayed) {
            //let DB computes full total
            $qb->resetDQLPart('select');
            foreach ($sumColumnNames as $name) {
                $qb->addSelect(sprintf('SUM(e.%s) AS %s', $name, $name));
            }
            $results = $qb->getQuery()->getSingleResult();
            foreach ($sumColumnNames as $name) {
                $fullSums[$name] += floatval($results[$name]);
            }

            $footer = [];
            $numberHelper = HelperFactory::build('NumberHelper');
            foreach ($this->sums as $key => $value) {
                $footer[$key] = sprintf(
                    '<span>%s</span><br/><span>%s</span>',
                    //Formats here because not done by jqgrid
                    $numberHelper->formatNumber($value, 2, ',', ' '),
                    $numberHelper->formatNumber($fullSums[$key], 2, ',', ' ')
                );
            }
            $footer['id'] = sprintf(
                '<span>%s</span><br/><span>%s</span>',
                strtoupper($translator->trans('Page total')),
                strtoupper($translator->trans('Total'))
            );
        } else {
            //Formats here because not done by jqgrid
            $footer = array_map(
                function ($value) {
                    return HelperFactory::build('NumberHelper')->formatNumber($value, 2, ',', ' ');
                },
                $this->sums
            );
            $footer['id'] = sprintf(
                '<span>%s</span>',
                strtoupper(
                    $translator->trans(($totalResults > count($rows)) ? 'Page total' : 'Total')
                )
            );
        }
        if ($isExport) {
            $this->sums['id'] = strtoupper($translator->trans(($totalResults > count($rows)) ? 'Page total' : 'Total'));
            $fullSums['id']   = strtoupper($translator->trans('Total'));
            return [
                'rows'      => $rows,
                'records'   => $totalResults,
                'sum'       => $this->sums,
                'fullSum'   => $fullSums
            ];
        }
        return [
            'rows'     => $rows,
            'page'     => $page,
            'total'    => $totalPages,
            'records'  => $totalResults,
            'userdata' => BaseController::isTrue($options, BaseBackController::OPTION_HAS_FOOTER) ? $footer : ''
        ];
    }

    public function padAndFormatJqGridRows($rows, $isReformatted, $columnNames, &$keys = [])
    {
        $numberHelper = HelperFactory::build('NumberHelper');

        $this->columns = (isset($this->columns) ? $this->columns : $this->getClassMetadata()->fieldMappings);
        foreach ($columnNames as $columnName) {
            $keys[$columnName] = $this->getType($columnName);
        }

        foreach ($rows as $row) {
            $subOut = [];
            foreach ($keys as $key => $type) {
                if (array_key_exists($key, $row)) {
                    if ($isReformatted && !is_null($type)) {
                        switch(strtolower($type)) {
                            case 'float':
                                $subOut[$key] = $numberHelper->formatNumber($row[$key], 2, ',', ' ');
                                break;
                            case 'date':
                                $subOut[$key] = Carbon::createFromFormat("Y-m-d", $row[$key])->format('d/m/Y');
                                break;
                            case 'datetime':
                                $subOut[$key] = Carbon::createFromFormat("Y-m-d H:i:s", $row[$key])->format('d/m/Y H:i:s');
                                break;
                            default:
                                $subOut[$key] = $row[$key];
                        }
                    } else {
                        $subOut[$key] = $row[$key];
                    }
                } else {
                    $subOut[$key] = '';
                }
            }
            $out[] = $subOut;
        }
        return $out;
    }

    public function formatJqGridRow($entity, Translator $translator, array $options = [])
    {
        $this->columns = (isset($this->columns) ? $this->columns : $this->getClassMetadata()->fieldMappings);
        $out = [];
        foreach ($this->columns as $column) {
            $key = $column['columnName'];
            if (!in_array($key, $this->listExcludeForDisplay, true)) {
                $getter = $this->getAccessor($key);
                if (!is_null($entity->$getter())) {
                    if ($this->isType($column, 'date')) {
                        $out[$key] = $entity->$getter()->format(self::DATE_STORAGE_FORMAT);
                    } elseif ($this->isType($column, 'datetime')) {
                        $out[$key] = $entity->$getter()->format(self::DATETIME_STORAGE_FORMAT);
                    } elseif ($this->isType($column, 'boolean')) {
                        $out[$key] = (0 == $entity->$getter()) ? $translator->trans(self::JQGRID_FALSE) : $translator->trans(self::JQGRID_TRUE);
                    } else {
                        $out[$key] = $entity->$getter();
                    }
                }
            }
        }
        return $out;
    }

    public function processJqGridSpecialFields($entity, &$values, $oper, Translator $translator, array $options = [])
    {
        //Nothing here
    }

    public function getType($seekColumn)
    {
        if (is_array($seekColumn)) {
            return strtolower($seekColumn['type']);
        }
        $this->columns = (isset($this->columns) ? $this->columns : $this->getClassMetadata()->fieldMappings);
        foreach ($this->columns as $column) {
            if ($seekColumn == $column['columnName']) {
                return strtolower($column['type']);
            }
        }
        $this->associations = (isset($this->associations) ? $this->associations : $this->getClassMetadata()->associationMappings);
        foreach ($this->associations as $associations) {
            if ($seekColumn == $associations['fieldName']) {
                return self::TYPE_ASSOCIATION;
            }
        }
        return null;
    }

    public function isType($seekColumn, $type)
    {
        return $this->getType($seekColumn) == strtolower($type);
    }

    public function getAccessor($variableName, $mode = self::GETTER)
    {
        return $mode . str_replace(' ', '', ucwords(str_replace('_', ' ', $variableName)));
    }

    public function unformatAll(array $values, Translator $translator, array $options = [])
    {
        $cleanValues = [];
        foreach ($values as $key => $value) {
            $cleanValue = $this->unformat($key, $value, $translator, false, $options);
            if (self::TO_REMOVE !== $cleanValue) {
                $cleanValues[$key] = $cleanValue;
            }
        }
        return $cleanValues;
    }

    public function getFieldAndAssociationNames()
    {
        $out = array_map(
            function ($item) {
                return $item['columnName'];
            },
            $this->getClassMetadata()->fieldMappings
        );
       
        $out += array_map(
            function ($item) {
                return $item['fieldName'];
            },
            array_filter(
                $this->getClassMetadata()->associationMappings,
                function ($item) {
                    return (bool) ($item['type'] && ClassMetadataInfo::TO_ONE);
                }
            )
        );
        return $out;
    }
    
    public function getAssociations()
    {
         return $this->getClassMetadata()->associationMappings;
    }
    
    protected function getQuerySelectClause(QueryBuilder $qb, $alias, array $options = [])
    {
        return $qb->select('e')
                  ->from($this->getEntityName(), $alias);
    }

    protected function getEditType(Array $column, $minLengthForTinyEditor)
    {
        if (($this->isType($column, 'string') && isset($column['length']) && ($column['length'] > $minLengthForTinyEditor))
          || $this->isType($column, 'text')) {
            return 'textarea';
        }
        if ($this->isType($column, 'boolean')) {
            return 'checkbox';
        }
        return 'text';
    }

    protected function getEditRules(Array $column)
    {
        return [];
    }

    protected function getEditOptions(Array $column, Translator $translator, $selectUrl)
    {
        if ($this->isType($column, 'boolean')) {
            return ["value" => $translator->trans(self::JQGRID_TRUE). ':' . $translator->trans(self::JQGRID_FALSE)];
        }
        if ($this->isType($column, 'string') && isset($column['length'])) {
            return ["maxlength" => $column['length']];
        }
        return [];
    }

    protected function getSortOpt(Array $column, Translator $translator)
    {
        if ($this->isType($column, 'string') || $this->isType($column, 'text')) {
            return  ["sopt" => self::$txtOperators];
        }
        if ($this->isType($column, 'boolean')) {
            return [
                "sopt" =>  [self::JQGRID_OPERATOR_EQUAL],
                "value" => self::buildSortValue(
                    [0 =>self::JQGRID_FALSE, 1 => self::JQGRID_TRUE],
                    $translator
                )
            ];
        }
        if ($this->isType($column, 'datetime')) {
            return ["sopt" => self::$datetimeOperators];
        }
        return ["sopt" => self::$numOperators];
    }

    protected function getSType(Array $column)
    {
        if ($this->isType($column, 'boolean')) {
            return "select";
        }
        return "text";
    }

    protected function getAlign(Array $column)
    {
        return $this->getAlignFromType($this->getType($column));
    }
    
    public static function getAlignFromType($type)
    {
        if ($type == 'float') {
            return "right";
        }
        if ($type == 'integer') {
            return "right";
        }
        if (($type == 'date') || ($type == 'datetime')) {
            return "center";
        }
        return null;
    }

    protected function getFormatter(Array $column, array $options = [])
    {
        if ($this->isType($column, 'float')) {
            return ["formatter" => "number"];
        }
        if ($this->isType($column, 'integer')) {
            return ["formatter" => "integer"];
        }
        if ($this->isType($column, 'date') || $this->isType($column, 'datetime')) {
            $out = ["formatter" => "date"];
            if ($this->isType($column, 'datetime')) {
                $out["formatoptions"] = [
                   "srcformat" => self::DATETIME_STORAGE_FORMAT,
                   "newformat" => self::DATETIME_DISPLAY_FORMAT
                ];
            } else {
                $out["formatoptions"] = [
                   "srcformat" => self::DATE_STORAGE_FORMAT,
                   "newformat" => self::DATE_DISPLAY_FORMAT
                ];
            }
            return $out;
        }
        return null;
    }

    /**
     * Maps jsGrid filters with SQL (cf. http://www.trirand.com/jqgridwiki/doku.php?id=wiki:advanced_searching#options)
     */
    protected function getQueryWhereClause($isSearch, QueryBuilder $qb, $filters, $alias, Translator $translator, array $options = [])
    {
        if (!$isSearch) {
            return $qb;
        }
        if (!empty($filters)) {
            foreach ($filters->rules as $idx => $rule) {
                $qb = $this->getQuerySubWhereClause($qb, $rule->field, $rule->op, $rule->data, $alias, $translator, $options);
            }
        }
        return $qb;
    }

    protected function getQueryJoinClause(QueryBuilder $qb, $alias, array $options = array())
    {
        if (isset($options['join'])) {
            foreach ($options['join'] as $join => $joinAlias) {
                if (false === strpos($join, '.')) {
                    $qb->leftJoin(sprintf('%s.%s', $alias, $join), $joinAlias);
                } else {
                    $qb->leftJoin($join, $joinAlias);
                }
            }
        }
        
        return $qb;
    }
    
    protected function getQuerySubWhereClause(QueryBuilder $qb, $field, $oper, $data, $alias, Translator $translator, array $options = [])
    {
        $data = $this->unformat($field, $data, $translator, true, $options);

        if ($oper == self::JQGRID_OPERATOR_BEGINS_WITH
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_BEGIN_WITH) {
            $data .= '%';
        }
        if ($oper == self::JQGRID_OPERATOR_ENDS_WITH
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_END_WITH) {
            $data = '%'. $data;
        }
        if ($oper == self::JQGRID_OPERATOR_CONTAINS
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_CONTAIN
          || $oper == self::JQGRID_OPERATOR_IS_IN
          || $oper == self::JQGRID_OPERATOR_IS_NOT_IN) {
            $data = '%' . $data . '%';
        }
        if (strpos($field, '.') !== false) {
            return $qb->andWhere(sprintf("%s %s '%s'", $field, self::$operators[$oper], $data));
        }
        return $qb->andWhere(sprintf("%s.%s %s '%s'", $alias, $field, self::$operators[$oper], $data));
    }

    protected function getQueryOrderClause(QueryBuilder $qb, $alias, $sidx, $sord)
    {
        if (empty($sidx) || empty($sord)) {
            return $qb;
        }
        //Ex: sidx=field1 desc, field2  sord=asc
        $sorts = explode(',', $sidx);
        $spe   = array_pop($sorts);

        foreach ($sorts as $sort) {
            $tmp = explode(' ', trim($sort));
            $qb->addOrderBy(((false === strpos($tmp[0], '.')) ? ($alias. '.') : '') . $tmp[0], $tmp[1]);
        }
        return $qb->addOrderBy(((false === strpos(trim($spe), '.')) ? ($alias. '.') : '') . trim($spe), $sord);
    }

    protected function unformat($key, $value, Translator $translator, $forDql, array $options = [])
    {
        if ($this->isType($key, 'boolean')) {
            if (($value == "0") || ($value == "1")) {
                return intval($value);
            }
            return ($value == $translator->trans(self::JQGRID_TRUE)) ? 1 : 0;
        }
        if ($this->isType($key, 'float')) {
            return str_replace([',', ' '], ['.', ''], $value);
        }
        if ($this->isType($key, 'date')) {
            if (!empty($value)) {
                try {
                    $date = Carbon::createFromFormat(
                        BaseController::isTrue($options, self::IS_WEBSERVICE) ? self::DATE_STORAGE_FORMAT : self::DATE_DISPLAY_FORMAT,
                        $value
                    );
                } catch (\Exception $e) {//A adte is expected but maybe we have a datetime
                    $date = Carbon::createFromFormat(
                        BaseController::isTrue($options, self::IS_WEBSERVICE) ? self::DATETIME_STORAGE_FORMAT : self::DATETIME_DISPLAY_FORMAT,
                        $value
                    );
                }
                return $forDql ? $date->format(self::DATE_STORAGE_FORMAT) : $date;
            }
            return self::TO_REMOVE;
        }
        if ($this->isType($key, 'datetime')) {
            if (!empty($value)) {
                //Date comes here in display format
                try {
                    $date = Carbon::createFromFormat(
                        BaseController::isTrue($options, self::IS_WEBSERVICE) ? self::DATETIME_STORAGE_FORMAT : self::DATETIME_DISPLAY_FORMAT,
                        $value
                    );
                } catch (\Exception $e) {
                    //We assume no time is given so it will be midnight by default
                    $date = Carbon::createFromFormat(
                        BaseController::isTrue($options, self::IS_WEBSERVICE) ? self::DATE_STORAGE_FORMAT : self::DATE_DISPLAY_FORMAT,
                        $value
                    );
                }
                return $forDql ? $date->format(self::DATETIME_STORAGE_FORMAT): $date;
            }
            return self::TO_REMOVE;
        }
        if ($this->isType($key, JqGrid::TYPE_ASSOCIATION) && !$forDql) {//Foreign key
            return $this->getEntityManager()->find('\\Entities\\' . ucfirst($key), intval($value));
        }
        if (0 == strcasecmp('null', $value)) {
            return null;
        }

        return $value;
    }

    public static function getRawQueryOrderClause($sidx, $sord)
    {
        //Ex: sidx=field1 desc, field2  sord=asc
        if (!empty($sidx) && !empty($sord)) {
            return ' ORDER BY '. $sidx . ' ' . $sord;
        }
        return '';
    }

    public static function getRawQueryWhereClause($isSearch, $filters, $formatters)
    {
        if (!$isSearch) {
            return '';
        }
        $filters = json_decode($filters);
        $result = '';
        if (!empty($filters)) {
            foreach ($filters->rules as $idx => $rule) {
                if (isset($formatters[$rule->field])) {
                    $rule->data = $formatters[$rule->field]($rule->data);
                }
                self::getRawQuerySubWhereClause($rule->field, $rule->op, $rule->data, $result);
            }
        }
        //removes first ' AND  ' before return
        return 'WHERE ' . substr($result, 5);
    }

    protected static function getRawQuerySubWhereClause($field, $oper, $data, &$query)
    {
        if ($oper == self::JQGRID_OPERATOR_BEGINS_WITH
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_BEGIN_WITH) {
            $data .= '%';
        }
        if ($oper == self::JQGRID_OPERATOR_ENDS_WITH
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_END_WITH) {
            $data = '%'. trim($data);
        }
        if ($oper == self::JQGRID_OPERATOR_CONTAINS
          || $oper == self::JQGRID_OPERATOR_DOES_NOT_CONTAIN
          || $oper == self::JQGRID_OPERATOR_IS_IN
          || $oper == self::JQGRID_OPERATOR_IS_NOT_IN) {
            $data = '%' . trim($data) . '%';
        }
        return $query .= sprintf(
            " AND %s %s '%s'",
            $field,
            self::$operators[$oper],
            trim($data)
        );
    }
    
    protected static function buildSortValue(array $choices, Translator $translator, $isWithAll = true)
    {
        $out = $isWithAll ? sprintf(':%s;', $translator->trans('All')) : '';
        foreach ($choices as $key => $value) {
            $out .= sprintf('%s:%s;', $key, $translator->trans($value));
        }
        return substr($out, 0, -1);
    }
}
