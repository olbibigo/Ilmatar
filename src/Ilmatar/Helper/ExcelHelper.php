<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use ExcelAnt\Adapter\PhpExcel\Workbook\Workbook;
use ExcelAnt\Adapter\PhpExcel\Sheet\Sheet;
use ExcelAnt\Adapter\PhpExcel\Writer\Writer;
use ExcelAnt\Table\Table;
use ExcelAnt\Coordinate\Coordinate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use ExcelAnt\Adapter\PhpExcel\Writer\WriterFactory;
use ExcelAnt\Adapter\PhpExcel\Writer\PhpExcelWriter\Excel5;
use ExcelAnt\Collections\StyleCollection;
use ExcelAnt\Style\Fill;
use ExcelAnt\Style\Font;
use ExcelAnt\Style\Borders;
use ExcelAnt\Style\Border;
use ExcelAnt\Style\Format;
use ExcelAnt\Style\Alignment;
use Symfony\Component\Translation\Translator;

/**
 * Helper class to generate PDF documents.
 *
 */
class ExcelHelper extends BaseHelper
{
    protected $expected = [/*'orm.em'*/];

    const HIGHLIGHT_FIRST_ROW    = 'HIGHLIGHT_FIRST_ROW';
    const HIGHLIGHT_LAST_ROW     = 'HIGHLIGHT_LAST_ROW';
    const HIGHLIGHT_FIRST_COLUMN = 'HIGHLIGHT_FIRST_COLUMN';
    const HIGHLIGHT_LAST_COLUMN  = 'HIGHLIGHT_LAST_COLUMN';
    const HIGHLIGHT_ZEBRA_COLUMN = 'HIGHLIGHT_ZEBRA_COLUMN';
    const HIGHLIGHT_ZEBRA_ROW    = 'HIGHLIGHT_ZEBRA_ROW';
    
    public function createExport(
        array $array,
        array $columnType = null,
        Translator $translator = null,
        array $styles = [],
        array $highlights = [self::HIGHLIGHT_ZEBRA_ROW]
    ) {
        if (empty($array) || !is_array($array[0])) {
            return '';
        }
        $defaultStyles = [
            'border'                     => [
                (new Borders())
                    ->setRight(new Border())
                    ->setBottom(new Border())
            ],
            'colname'                    => [
                (new Fill())
                    ->setColor('B6EBF2'),
                (new Font())
                    ->setBold(true),
                (new Alignment())
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ],
            self::HIGHLIGHT_FIRST_ROW    => [
                (new Fill())
                    ->setColor('CDFCD5'),
                (new Font())
                    ->setBold(true),
            ],
            self::HIGHLIGHT_LAST_ROW     => [
                (new Fill())
                    ->setColor('CDFCD5'),
                (new Font())
                    ->setBold(true),
            ],
            self::HIGHLIGHT_FIRST_COLUMN => [
                (new Fill())
                    ->setColor('CDFCD5'),
                (new Font())
                    ->setBold(true),
            ],
            self::HIGHLIGHT_LAST_COLUMN  => [
                (new Fill())
                    ->setColor('CDFCD5'),
                (new Font())
                    ->setBold(true),
            ],
            self::HIGHLIGHT_ZEBRA_COLUMN => [
                array (),
                array (
                    (new Fill())
                        ->setColor('EEEEEE'),
                )
            ],
            self::HIGHLIGHT_ZEBRA_ROW    => [
                array (),
                array (
                    (new Fill())
                        ->setColor('EEEEEE'),
                )
            ]
        ];
        $styles = array_merge($defaultStyles, $styles);
        $table = new Table();
        
        $this->setHeader(
            $this->getKeys(
                $array,
                $translator
            ),
            $styles,
            $table
        );

        $this->setRowStyle($array, $styles, $highlights, $table);
        
        $this->setColumnStyle($array, $styles, $highlights, $table);
        
        $path = null;
        $writer = null;
        $phpExcel = $this->toPHPExcel($table, $path, $writer);
        
        $this->setFormat($array, $columnType, $highlights, $phpExcel);
        $writer->write($phpExcel);
        $result = file_get_contents($path, FILE_USE_INCLUDE_PATH);
        return $result;
    }

    protected function setHeader($keys, $styles, &$table)
    {
        $table->setRow($keys, 0, new StyleCollection(array_merge($styles['border'], $styles['colname'])));
    }

    protected function setRowStyle($array, $styles, $highlights, &$table)
    {
        $isZebraRow = in_array(self::HIGHLIGHT_ZEBRA_ROW, $highlights, true);
        $rowCount   = count($array) - 1;
        foreach ($array as $idRow => $row) {
            $style = $styles['border'];
            if ((0 == $idRow) && in_array(self::HIGHLIGHT_FIRST_ROW, $highlights, true)) {
                $table->setRow($row, $idRow + 1, new StyleCollection(array_merge($style, $styles[self::HIGHLIGHT_FIRST_ROW])));
            } elseif (($rowCount == $idRow) && in_array(self::HIGHLIGHT_LAST_ROW, $highlights, true)) {
                $table->setRow($row, $idRow + 1, new StyleCollection(array_merge($style, $styles[self::HIGHLIGHT_LAST_ROW])));
            } elseif ($isZebraRow) {
                $table->setRow($row, $idRow + 1, new StyleCollection(array_merge($style, $styles[self::HIGHLIGHT_ZEBRA_ROW][$idRow % 2])));
            } else {
                 $table->setRow($row, $idRow + 1, new StyleCollection($style));
            }
        }
    }

    protected function setColumnStyle($array, $styles, $highlights, &$table)
    {
        $colCount   = count($array[0]) - 1;
        $isZebraCol = in_array(self::HIGHLIGHT_ZEBRA_COLUMN, $highlights);
        for ($i = 0; $i <= $colCount; ++$i) {
            $newstyles = null;
            if ((0 == $i) && in_array(self::HIGHLIGHT_FIRST_COLUMN, $highlights, true)) {
                $newstyles = $styles[self::HIGHLIGHT_FIRST_COLUMN];
            } elseif (($colCount == $i) && in_array(self::HIGHLIGHT_LAST_COLUMN, $highlights, true)) {
                $newstyles = $styles[self::HIGHLIGHT_LAST_COLUMN];
            } elseif ($isZebraCol) {
                $newstyles = $styles[self::HIGHLIGHT_ZEBRA_COLUMN][$i % 2];
            }
            if (!is_null($newstyles)) {
                $first = true;
                foreach ($table->getColumn($i) as $cell) {
                    if ($first) {
                        $first = false;
                        continue;
                    }
                    $cell->setStyles(new StyleCollection(array_merge($cell->getStyles()->toArray(), $newstyles)));
                }
            }
        }
    }
    protected function toPHPExcel($table, &$path, &$writer)
    {
        $workbook = new Workbook();
        $sheet = new Sheet($workbook);
        $sheet->addTable($table, new Coordinate(1, 1));
        $workbook->addSheet($sheet);
        $path = tempnam('/tmp', 'export-');
        $writer = (new WriterFactory())->createWriter(new Excel5($path));
        unlink($path);
        return $writer->convert($workbook);
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
    protected function setFormat($array, $columnType, $highlights, &$phpExcel)
    {
        $i = 0;
        $colCount   = count($array[0]) - 1;
        $rowCount   = count($array) - 1;
        $sheet = $phpExcel->getActiveSheet();
        $keys = array_keys($array[0]);
        foreach (range('A', 'ZZZ') as $columnID) {
            if ($i > $colCount) {
                break;
            }
            foreach (range('2', $rowCount + 2) as $rowID) {
                $value = $array[$rowID - 2][$keys[$i]];
                if (($rowCount == $rowID - 2) && in_array(self::HIGHLIGHT_LAST_ROW, $highlights, true) && ($columnID == 'A' || $value == '')) {
                    continue;
                }
                $style = is_null($columnType) ? 'string' : $columnType[$keys[$i]];
                $sheet->getStyle($columnID.$rowID)->getNumberFormat()->applyFromArray(
                    ['code' => $this->getFormat($style)]
                );
                $sheet->setCellValue($columnID.$rowID, $this->format($value, $style));
            }
            $sheet->getColumnDimension($columnID)
                ->setAutoSize(true);
            ++$i;
        }
        $phpExcel->getActiveSheet()->calculateColumnWidths();
    }
    protected function getFormat($style)
    {
        switch ($style) {
            case 'integer':
                return '0';
            case 'float':
                return '0.00';
            case 'string':
            default:
                return 'General';
        }
    }
    protected function format($item, $style)
    {
        switch ($style) {
            case 'integer':
                return intval($item);
            case 'float':
                return floatval(str_replace(',', '.', $item));
            default:
                return $item;
        }
    }
}
