<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Ilmatar\TagManager;
use Ilmatar\JqGrid;
use Ilmatar\Exception\TranslatedException;

/**
 * Helper class to manipulate emails.
 *
 */
class ImportHelper extends BaseHelper
{
    const MODE_ADD   = "0";
    const MODE_ERASE = "1";
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [
        'orm.em', //$app['orm.em']
        'translator' //$app['translator']
    ];

    private function error($errormessage, $type)
    {
        if (!is_null($this->options['logger']) && $type) {
            $this->options['logger']->$type($errormessage);
        }
        return $errormessage;
    }
    
    public function getAllEntities()
    {
        $entities = [];
        $meta = $this->mandatories['orm.em']->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $name = $m->getName();
            if (empty($name::$allowedFieldsToImport) || empty($this->getEntityImportableField($name))) {
                continue;
            }
            $entities[$name] = substr($name, strpos($name, '\\') + 1);
        }
        return $entities;
    }

    public function getEntityImportableField($name)
    {
        $result = [];
        $field;
        try {
            $msg = $this->mandatories['orm.em']->getClassMetadata($name);
            $fields = array_merge($msg->fieldMappings, $msg->associationMappings);
            foreach ($name::$allowedFieldsToImport as $field) {
                $afield = explode('.', $field);
                if (isset ($fields[$afield[0]])) {
                    $result[$field] = $fields[$afield[0]];
                    if (isset ($afield[1])) {
                    //if (isset($fields[$afield[0]]['targetEntity'])) {
                        $assos = $this->mandatories['orm.em']->getClassMetadata('Entities\\'.$afield[0])->fieldMappings;
                        if (isset($assos[$afield[1]])) {
                            $result[$field]['nullable'] = isset($fields[$afield[0]]['joinColumns'][0]['nullable']) ? false:true;
                            $result[$field]['type'] = $assos[$afield[1]]["type"];
                            $result[$field]['length'] = $assos[$afield[1]]["length"];
                        } else {
                            $this->error(sprintf(
                                'Entity %s, Field %s: The "%s" field doesn\'t exist in "%s" entity.',
                                $name,
                                $field,
                                $afield[1],
                                $afield[0]
                            ), "error");
                            return [];
                        }
                    } else {
                        $result[$field]['nullable'] = isset($fields[$afield[0]]['nullable']) ? $fields[$afield[0]]['nullable']:false;
                    }
                } else {
                    $this->error(sprintf(
                        'Entity %s, Field %s: The "%s" field doesn\'t exist.',
                        $name,
                        $field,
                        $afield[0]
                    ), "error");
                    return [];
                }
            }
        } catch (\Exception $e) {
            $this->error(sprintf(
                'Entity %s, Field %s: %s',
                $name,
                $field,
                $e->getMessage()
            ), "error");
            return [];
        }
        return $result;
    }

    private function validRow($columns, $fields, &$result)
    {
        foreach ($columns as $index => $column) {
            $tmpcolumns[$column] = $column;
        }
        foreach ($fields as $index => $field) {
            if ((!isset($field["nullable"]) || !$field["nullable"]) && !isset($tmpcolumns[$index])) {
                $result["fatal"][] = $this->error(
                    sprintf(
                        $this->mandatories['translator']->trans('The %s column is required'),
                        $index
                    ),
                    "warning"
                );
            }
        }
    }
    
    private function validAssos($data, $colname, &$avalues, &$nberror, $field, &$result)
    {
        $assosfieldname = explode('.', $colname);
        $assosfielfunc = sprintf('findOneBy%s', $assosfieldname[1]);
        $adata =  $this->mandatories['orm.em']->getRepository($field['targetEntity'])->$assosfielfunc($data);
        if ($adata === null && !($field['nullable'] && $data === "")) {
            $result["error"][] = $this->error(
                sprintf(
                    $this->mandatories['translator']->trans('Error line %s, "%s" is not a valid %s'),
                    $result["row"],
                    $data,
                    $colname
                ),
                "warning"
            );
            ++$nberror;
        } else {
            $avalues[$assosfieldname[0]] = $adata;
        }
    }

    
    private function validEntity($datas, $columns, $fields, &$values, &$avalues, &$result)
    {
        $nberror = 0;
        foreach ($datas as $index => $data) {
            $colname = $columns[$index];
            if (isset($fields[$colname])) {
                $field = $fields[$colname];
                if ($data === "" && !$field['nullable']) {
                    $result["error"][] = $this->error(
                        sprintf(
                            $this->mandatories['translator']->trans('Error line %s, "%s" is required'),
                            $result["row"],
                            $colname
                        ),
                        "warning"
                    );
                    ++$nberror;
                } else {
                    if (isset($field['targetEntity'])) {
                        $this->validAssos($data, $colname, $avalues, $nberror, $field, $result);
                    } else {
                        if ($data != "") {
                            $values[$colname] = $data;
                        }
                    }
                }
            }
        }
        return $nberror === 0;
    }

    

    
    public function validImportableFile($entity, $path)
    {
        $fields = $this->getEntityImportableField($entity);
        $result = ["fatal" => [], "error" => [], "row" => 0];
        $columns = [];
        if (file_exists($path) && ($handle = fopen($path, "r")) !== false) {
            if (($columns = fgetcsv($handle, 1000, ";")) !== false) {
                $this->validRow($columns, $fields, $result);
                if (empty($result["fatal"])) {
                    $result["row"] = 1;
                    while (($datas = fgetcsv($handle, 1000, ";")) !== false) {
                        try {
                            $result["row"]++;
                            $values = [];
                            $avalues = [];
                            if ($this->validEntity($datas, $columns, $fields, $values, $avalues, $result)) {
                                $cleanvalues = (
                                    new JqGrid(
                                        $this->mandatories['orm.em'],
                                        $this->mandatories['orm.em']->getClassMetadata($entity)
                                    )
                                )->unformatAll($values, $this->mandatories['translator']);
                                $newE = new $entity(array_merge($cleanvalues, $avalues));
                                $this->mandatories['orm.em']->persist($newE);
                            }
                        } catch (TranslatedException $e) {
                            $result["error"][] = $this->error(
                                sprintf(
                                    $this->mandatories['translator']->trans('Error line %s, %s'),
                                    $result["row"],
                                    $e->getTranslatedMessage($this->mandatories['translator'])
                                ),
                                "warning"
                            );
                        } catch (\Exception $e) {
                            if ($e->getMessage() === "The EntityManager is closed.") {
                                $result["fatal"][] = $this->mandatories['translator']->trans('Import failed');
                                return $result;
                            }
                            $result["error"][] = $this->error(
                                sprintf(
                                    $this->mandatories['translator']->trans('Error line %s, %s'),
                                    $result["row"],
                                    $e->getMessage()
                                ),
                                "warning"
                            );
                        }
                    }
                }
            }
            fclose($handle);
        } else {
            $result["fatal"][] = $this->error(
                sprintf(
                    $this->mandatories['translator']->trans('Can\'t open this file %s'),
                    $path
                ),
                "warning"
            );
        }
        return $result;
    }

    public function importFile($entity, $path, $mode = self::MODE_ERASE)
    {
        $em = $this->mandatories['orm.em'];
        $nbrow = 0;
        if (!file_exists($path)) {
            return [
                "errors" => "fatal",
                "list" => $this->error(
                    sprintf(
                        $this->mandatories['translator']->trans('Can\'t open this file %s'),
                        $path
                    ),
                    "warning"
                )
            ];
        }
        try {
            $em->getConnection()->beginTransaction();
            $entities = $em->getRepository($entity)->findAll();
            if (self::MODE_ERASE === $mode) {
                $entities = $em->getRepository($entity)->findAll();
                foreach ($entities as $ent) {
                    $em->remove($ent);
                }
                $em->flush();
            } else {
                $nbrow = count($entities);
            }
            $result = $this->validImportableFile($entity, $path);
            if (empty($result["fatal"])) {
                $em->flush();
                $nbrow = count($em->getRepository($entity)->findAll()) - $nbrow;
                $newpath = $path . ".processed";
                rename($path, $newpath);
                $this->error(
                    sprintf(
                        $this->mandatories['translator']->trans('%s %s data imported from file: %s'),
                        $nbrow,
                        $entity,
                        $newpath
                    ),
                    "info"
                );
                return ["errors" => $result["row"] - $nbrow - 1, "list" => $result, "count" => $nbrow];
            } else {
                return ["errors" => "fatal", "list" => implode('\n', $result["fatal"])];
            }
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            $this->error(sprintf($this->mandatories['translator']->trans('Rollback in %s'), __FUNCTION__), "error");
            if (preg_match("%Duplicate entry (?P<key>.+) for key (?P<value>.+)%", $e->getMessage(), $match)) {
                return [
                    "errors" => "fatal",
                    "list" => $this->error(
                        sprintf(
                            $this->mandatories['translator']->trans('Unique constraint on %s failed for value %s.'),
                            $match['value'],
                            $match['key']
                        ),
                        "error"
                    )
                ];
            } else {
                return [
                    "errors" => "fatal",
                    "list" => $this->error(
                        $this->mandatories['translator']->trans($e->getMessage()),
                        "error"
                    )
                ];
            }
        }
    }
}
