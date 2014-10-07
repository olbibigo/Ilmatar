<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Symfony\Component\Translation\Translator;
use Ilmatar\HelperFactory;

/**
 * Helper class to manipulate arrays.
 *
 */
class ArrayHelper extends BaseHelper
{
    const HIGHLIGHT_FIRST_ROW    = 'HIGHLIGHT_FIRST_ROW';
    const HIGHLIGHT_LAST_ROW     = 'HIGHLIGHT_LAST_ROW';
    const HIGHLIGHT_FIRST_COLUMN = 'HIGHLIGHT_FIRST_COLUMN';
    const HIGHLIGHT_LAST_COLUMN  = 'HIGHLIGHT_LAST_COLUMN';
    const HIGHLIGHT_ZEBRA_COLUMN = 'HIGHLIGHT_ZEBRA_COLUMN';
    const HIGHLIGHT_ZEBRA_ROW    = 'HIGHLIGHT_ZEBRA_ROW';
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    protected $document;
    
    /**
     * Build an associative array from an array of objects and their properties
     *
     * @param array   $array         Array of object
     * @param string  $accessorKey   Getter name for key
     * @param string  $accessorValue Getter name for value
     * @return array                 Associative array
     */
    public function buildAssociativeArray(array $array, $accessorKey, $accessorValue)
    {
        $objectHelper  = HelperFactory::build('ObjectHelper');
        $isKeyMethod   = $objectHelper->isValidMethod($array[0], $accessorKey);
        $isValueMethod = $objectHelper->isValidMethod($array[0], $accessorValue);
        
        $out = [];
        foreach ($array as $item) {
            $out[$isKeyMethod ? $item->$accessorKey() : $item->$accessorKey] = ($isValueMethod ? $item->$accessorValue() : $item->$accessorValue);
        }
        return $out;
    }
    /**
     * Transforms an array into an XML string or document
     * Exemple :
     * array(
     *     array(
     *         'id'          => 1,
     *         'name'        => 'nom 1',
     *         'description' => 'description 1',
     *     ),
     *     array(
     *         'id'          => 2,
     *         'name'        => 'nom 2',
     *         'description' => 'description 2',
     *     ),
     * );
     * est transformé en :
     * <?xml version="1.0" encoding="UTF-8"?>
     * <items>
     *     <item>
     *         <id><![CDATA[1]]></id>
     *         <name><![CDATA[nom 1]]></name>
     *         <description><![CDATA[description 1]]></description>
     *     </item>
     *     <item>
     *         <id><![CDATA[2]]></id>
     *         <name><![CDATA[nom 2]]></name>
     *         <description><![CDATA[description 2]]></description>
     *     </item>
     * </items>
     *
     * @param array   $array         Array to transform
     * @param string  $rootNode      XML root node name
     * @param string  $itemNode      XML item node name
     * @param boolean $returnType    Expected response format
     * @param boolean $isStrippedTag Are HTML tags removed
     * @return string|DOMDocument    XML string or document
     */
    public function getXmlFromArray(array $array, $rootNodeName, $itemNodeName, $returnType = 'string', $isStrippedTag = true)
    {
        $this->document = new \DOMDocument('1.0', 'UTF-8');
        $mainNode       = $this->document->createElement($rootNodeName);

        $this->getDOMElementFromArray($array, $mainNode, $itemNodeName, $isStrippedTag);
        $this->document->appendChild($mainNode);

        switch ($returnType) {
            case 'xml':
                return $this->document;
            case 'string':
                return $this->document->saveXML($mainNode);
            case 'full_string':
                return $this->document->saveXML();
            default:
                return null;
        }
    }
    /**
     * Transforms an array into an Json string
     *
     * @param array   $array        Array to transform
     * @return string               JSON string
     */
    public function getJsonFromArray(array $array)
    {
        $result = json_encode($array);
        if (\JSON_ERROR_NONE != ($error = json_last_error())) {
            switch ($error) {
                case JSON_ERROR_NONE:
                    $message = ' - No errors';
                    break;
                case JSON_ERROR_DEPTH:
                    $message = ' - Maximum stack depth exceeded';
                    break;
                case JSON_ERROR_STATE_MISMATCH:
                    $message = ' - Underflow or the modes mismatch';
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    $message = ' - Unexpected control character found';
                    break;
                case JSON_ERROR_SYNTAX:
                    $message = ' - Syntax error, malformed JSON';
                    break;
                case JSON_ERROR_UTF8:
                    $message = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                default:
                    $message = ' - Unknown error';
            }
            throw new \Exception(sprintf("Json encoding failed into %s() : %s.", __FUNCTION__, $message));
        }
        return $result;
    }
    /**
     * Transforms an array into an CSV string
     *
     * @param array      $array         Array to transform
     * @param string     $delimiter     (default=';')
     * @param Translator $translator    Translator
     * @param boolean    $isStrippedTag Are HTML tags removed
     * @return string                   CSV string
     */
    public function getCsvFromArray(array $array, $delimiter = ';', Translator $translator = null, $isStrippedTag = true)
    {
        if (empty($array) || !is_array($array[0])) {
            return '';
        }
        $keys = $this->getKeys($array, $translator);

        $keys   = implode('%%%', $keys);
        $result = str_replace('%%%', $delimiter, str_replace($delimiter, '\\' . $delimiter, $keys)) . "\n";
        foreach ($array as $idx => $value) {
            $subValues = array_values($value);
            if ($isStrippedTag) {
                $subValues = array_map("strip_tags", $subValues);
            }
            $temp = implode('%%%', $subValues);
            $result .= str_replace('%%%', $delimiter, str_replace($delimiter, '\\' . $delimiter, $temp)) . "\n";
        }
        return $result;
    }

    /**
     * Transforms an array into a HTML select
     *
     * @param array      $array         Array to transform
     * @param Translator $translator    Translator
     * @param boolean    $isStrippedTag Are HTML tags removed
     * @param array      $styles        Associative array of $tag => CSS
     * @param array      $highlights    Elements to highlight
     * @return string                   HTML string
     */
    public function getHTMLTableFromArray(
        array $array,
        Translator $translator = null,
        $isStrippedTag = true,
        array $styles = [],
        array $highlights = [self::HIGHLIGHT_ZEBRA_ROW]
    ) {
        if (empty($array) || !is_array($array[0])) {
            return '';
        }
        $keys = $this->getKeys($array, $translator);

        $defaultStyles = [
            'table'                      => 'border:1px solid black;border-collapse:collapse;font-size:10px;',
            'th'                         => 'border:1px solid black;background-color:#B6EBF2;',
            'tr'                         => '',
            'td'                         => 'border:1px solid black;',
            self::HIGHLIGHT_FIRST_ROW    => 'background-color:#CDFCD5;font-weight:bold;',
            self::HIGHLIGHT_LAST_ROW     => 'background-color:#CDFCD5;font-weight:bold;',
            self::HIGHLIGHT_FIRST_COLUMN => 'background-color:#CDFCD5;font-weight:bold;',
            self::HIGHLIGHT_LAST_COLUMN  => 'background-color:#CDFCD5;font-weight:bold;',
            self::HIGHLIGHT_ZEBRA_COLUMN => [
                '',//odd
                'background-color:#EEE;'//even
            ],
            self::HIGHLIGHT_ZEBRA_ROW    => [
                '',//odd
                'background-color:#EEE;'//even
            ],
        ];
        $styles = array_merge($defaultStyles, $styles);

        $out  = sprintf('<table style="%s">', $styles['table']);
        $out .= sprintf(
            '<tr style="%s">%s</tr>',
            $styles['tr'],
            implode(
                '',
                array_map(
                    function ($item) use ($styles) {
                        return sprintf('<th style="%s">%s</th>', $styles['th'], $item);
                    },
                    $keys
                )
            )
        );
        $isZebraRow = in_array(self::HIGHLIGHT_ZEBRA_ROW, $highlights, true);
        $isZebraCol = in_array(self::HIGHLIGHT_ZEBRA_COLUMN, $highlights, true);
        $rowCount   = count($array) - 1;
        $colCount   = count($array[0]) - 1;
        foreach ($array as $idRow => $row) {
            $trStyle = $styles['tr'];
            if ($isZebraRow) {
                $trStyle .= $styles[self::HIGHLIGHT_ZEBRA_ROW][$idRow % 2];
            }
            if ((0 == $idRow) && in_array(self::HIGHLIGHT_FIRST_ROW, $highlights, true)) {
                $trStyle .= $styles[self::HIGHLIGHT_FIRST_ROW];
            }
            if (($rowCount == $idRow) && in_array(self::HIGHLIGHT_LAST_ROW, $highlights, true)) {
                $trStyle .= $styles[self::HIGHLIGHT_LAST_ROW];
            }

            $out .= sprintf('<tr style="%s">', $trStyle);

            $count = 0;
            foreach ($row as $idCol => $cell) {
                $tdStyle = $styles['td'];
                if ($isZebraCol) {
                    $tdStyle .= $styles[self::HIGHLIGHT_ZEBRA_COLUMN][$count % 2];
                }
                if ((0 == $count) && in_array(self::HIGHLIGHT_FIRST_COLUMN, $highlights, true)) {
                    $tdStyle .= $styles[self::HIGHLIGHT_FIRST_COLUMN];
                }
                if (($colCount == $count++) && in_array(self::HIGHLIGHT_LAST_COLUMN, $highlights, true)) {
                    $tdStyle .= $styles[self::HIGHLIGHT_LAST_COLUMN];
                }

                $out .= sprintf(
                    '<td style="%s">%s</td>',
                    $tdStyle,
                    $isStrippedTag ? strip_tags($cell) : $cell
                );
            }
            $out .= '</tr>';
        }

        return $out . '</table>';
    }
    /**
     * Transforms an array into a HTML select
     *
     * @param array   $array        Array to transform
     * @param string  $keyName      Name of the key to use as option val
     * @param string  $valueName    Name of the value
     * @param array   $attributes   Select attributes
     * @return string               HTML string
     */
    public function getHTMLSelectFromArray(array $array, $keyName, $valueName, array $attributes = [])
    {
        $result = array_map(
            function ($item) use ($keyName, $valueName) {
                return '<option value="' . $item[$keyName] . '">' . htmlentities($item[$valueName]) .'</option>';
            },
            $array
        );

        $attributeStr = '';
        foreach ($attributes as $key => $value) {
            $attributeStr .= sprintf(' %s="%s"', $key, $value);
        }

        return sprintf('<select%s>%s</select>', $attributeStr, implode('', $result));
    }

    protected function getDOMElementFromArray(array $array, \DOMElement &$parentNode, $itemNodeName, $isStrippedTag = true)
    {
        foreach ($array as $item) {
            if (!is_array($item)) {
                throw new \Exception('Array can not be parsed [1].');
            } else {
                $itemNode = $this->document->createElement($itemNodeName);
                foreach ($item as $k => $v) {
                    if (is_numeric($k)) {
                        throw new \Exception('Array can not be parsed [2]');
                    }
                    $itemNode->appendChild($this->createDOMElement($k, $v, $isStrippedTag));
                }
                $parentNode->appendChild($itemNode);
            }
        }
    }

    protected function createDOMElement($name, $value, $isStrippedTag = true)
    {
        $element = $this->document->createElement($name);
        $element->appendChild(
            $this->document->createCDATASection(
                $isStrippedTag ? strip_tags($value) : $value
            )
        );
        return $element;
    }

    protected function getKeys(array $array, Translator $translator = null)
    {
        $keys = array_keys($array[0]);
        if (!is_null($translator)) {
            $keys = array_map(
                function ($item) use ($translator) {
                    return $translator->trans(
                        str_replace(
                            '_',
                            ' ',
                            ucfirst($item)
                        )
                    );
                },
                $keys
            );
        }
        return $keys;
    }
}
