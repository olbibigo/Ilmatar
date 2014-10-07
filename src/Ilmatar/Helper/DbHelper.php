<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;

/**
 * Helper class to manipulate a 'small' database in memory.
 *
 */
class DbHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    /*
    * Processes an array with an SQL request
    *
    * @param array   $metadate
    * @param array   $data
    * @param string  $sqlWhere SQL part following FROM clause
    * @return array
    */
    public function executeSqlAgainstArray($metadata, $data, $sqlFilters = 'WHERE 1')
    {
        if (!is_array($metadata) || !is_array($metadata) || (count($data[0]) !=  count($metadata))) {
            throw new \Exception(sprintf("Invalid parameters for %s() : must be arrays", __FUNCTION__));
        }
        if (!((0 === stripos($sqlFilters, 'WHERE '))
            || (0 === stripos($sqlFilters, 'HAVING '))
            || (0 === stripos($sqlFilters, 'ORDER BY '))
            || (0 === stripos($sqlFilters, 'GROUP BY '))
        )) {
            throw new \Exception(sprintf("Invalid parameters for %s() : filter must begin with a valid clause (WHERE, HAVING, GROUP BY, or ORDER BY)", __FUNCTION__));
        }

        $db = new \SQLite3(':memory:');
        $db->exec('PRAGMA synchronous = OFF');
        $db->exec('PRAGMA journal_mode = MEMORY');

        $fieldsForCreate  = [];
        $fieldsForInsertA = [];
        $fieldsForInsertB = [];
        foreach ($metadata as $metadatum) {
            $fieldsForCreate[] = $metadatum['name'] . ' ' . $metadatum['type'];
            $fieldsForInsertA[] = $metadatum['name'];
            $fieldsForInsertB[] = ':' . $metadatum['name'];
        }
        //Creates table
        $sql = sprintf(
            'CREATE TABLE local (%s)',
            implode(
                ', ',
                $fieldsForCreate
            )
        );

        if (!$db->exec($sql)) {
            throw new \Exception(sprintf("Local database cannot be created in %s()", __FUNCTION__));
        }
        //Populates table
        $sql = sprintf(
            'INSERT INTO local (%s) VALUES (%s)',
            implode(
                ',',
                $fieldsForInsertA
            ),
            implode(
                ',',
                $fieldsForInsertB
            )
        );
        $stmt = $db->prepare($sql);

        if (!$db->exec('BEGIN TRANSACTION')) {
            throw new \Exception(sprintf("Transaction cannot begin in %s()", __FUNCTION__));
        }
        foreach ($data as $idx => $datum) {
            foreach ($datum as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            if (false === $stmt->execute()) {
                throw new \Exception(sprintf("Local database cannot be populated in %s()", __FUNCTION__));
            }
            $stmt->reset();
        }
        if (!$db->exec('COMMIT TRANSACTION')) {
            throw new \Exception(sprintf("Transaction cannot end in %s()", __FUNCTION__));
        }
        //Adds index
        foreach ($metadata as $metadatum) {
            $sql = sprintf(
                'CREATE INDEX index_%s ON local (%s)',
                str_replace(' ', '', $metadatum['name']),
                $metadatum['name']
            );
            if (!$db->exec($sql)) {
                throw new \Exception(sprintf("Index cannot be set on column in %s()", __FUNCTION__));
            }
        }
        //Performs request
        $sql = sprintf('SELECT * FROM local %s', $sqlFilters);
        if (false === $results = $db->query($sql)) {
                throw new \Exception(sprintf("request {%s} cannot be executed in %s()", $sql, __FUNCTION__));
        }
        $out = [];
        while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $out[] = $row;
        }
        return $out;
    }
}
