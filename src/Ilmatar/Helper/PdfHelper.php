<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * Helper class to generate PDF documents.
 *
 */
class PdfHelper extends BaseHelper
{
    const EXPORT_ORIENTATION_PORTRAIT   = "Portrait";
    const EXPORT_ORIENTATION_PAYSAGE    = "Landscape";
    /**
     * Pattern to be seek into HTML template for replacement
     */
    const VAR_PATTERN         = "{{ %s }}";
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = ['app.root'];

    protected $snappy = null;
    protected $cache  = [];

    /**
     * Initializes PDF generator (Snappy)
     *
     * @param array   $mandatories   mandatory parameters
     * @param array   $options       optionnal parameters
     */
    public function __construct(array $mandatories = [], array $options = [])
    {
        parent::__construct($mandatories, $options);

        $isWindows = (false === stripos(php_uname(), 'windows') ? false : true);

        $binary = $this->mandatories['app.root'] . '/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64';
        if ($isWindows) {
            $binary = $this->mandatories['app.root'] . '/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf.exe';
        }
        if (!file_exists($binary)) {
            throw new FileNotFoundException();
        }
        //See options at http://madalgo.au.dk/~jakobt/wkhtmltoxdoc/wkhtmltopdf_0.10.0_rc2-doc.html
        $this->snappy = new Pdf($binary, ['encoding' => 'utf-8']);
    }

    /**
     * Generates one PDF document
     *
     * @param   string   $templatePath   HTML template full path
     * @param   array    $vars           Tags to be replaced as associative arrays without pattern (ex: 'name' not '{{ name }}')
     * @param   string   $filepath       Output filepath. If null or empty, no output file is created
     * @param   string   $orientation    Page orientation: Portrait (default) or Landscape
     *
     * @return  string   Final PDF doc
     */
    public function generatePdf($templatePath, $vars = [], $filepath = null, $orientation = self::EXPORT_ORIENTATION_PORTRAIT)
    {
        $mustDeleteIn = false;
        $mustDeleteOut = false;

        if (empty($templatePath)) {
            $templatePath = __DIR__ . '/defaultTemplate.htm';
        }
        if (!file_exists($templatePath)) {
            throw new \Exception(sprintf("Template path %s is invalid into %s", $templatePath, __FUNCTION__));
        }

        if (is_null($filepath) || empty($filepath)) {
            $outFilepath   = tempnam(sys_get_temp_dir(), 'ILM') . '.pdf';
            $mustDeleteOut = true;
        } else {
            $outFilepath = $filepath . ((strtolower(substr($filepath, -4)) == '.pdf') ? '' : '.pdf');
        }
        
        if (!isset($vars['title'])) {
            $vars['title'] = '';
        }
        if (!empty($vars)) {
            $html = $this->fromCache($templatePath);

            $html = str_ireplace(
                array_map(
                    function ($i) {
                        return sprintf(self::VAR_PATTERN, $i);
                    },
                    array_keys($vars)
                ),
                array_values($vars),
                $html
            );

            $inFilepath = tempnam(sys_get_temp_dir(), 'ILM') . '.htm';
            file_put_contents($inFilepath, $html);
            $mustDeleteIn = true;
        } else {
            $inFilepath = $templatePath;
        }
        $this->snappy->setOption('orientation', $orientation);
        $this->snappy->generate($inFilepath, $outFilepath, [], true);
        $result = file_get_contents($outFilepath);

        if ($mustDeleteIn) {
            unlink($inFilepath);
        }
        if ($mustDeleteOut) {
            unlink($outFilepath);
        }
        return $result;
    }

    /**
     * Generates a serie of PDF document based on same template
     *
     * @param   string   $templatePath   HTML template full path
     * @param   array    $vars           Tags to be replaced as array of associative arrays without pattern (ex: 'name' not '{{ name }}')
     * @param   string   $filename       Output filename as array. If null or empty, no output file is created
     *
     * @return  array    Final PDF docs
     */
    public function generateMultiplePdf($templatePath, $vars = [], $filenames = [])
    {
        if (empty($templatePath)) {
            throw new \Exception(sprintf("Invalid parameter for %s() : template path must be set.", __FUNCTION__));
        }
        if (!is_array($filenames) || !is_array($vars) || !is_array($vars[0])) {
            throw new \Exception(sprintf("Invalid parameters for %s() : must be arrays", __FUNCTION__));
        }
        if (count($filenames) != count($vars)) {
            throw new \Exception(sprintf("Invalid parameters for %s() : arrays must be same size", __FUNCTION__));
        }
        $result = [];
        foreach ($filenames as $idx => $filename) {
            $result[] = $this->generatePdf($templatePath, $vars[$idx], $filename);
        }
        return $result;
    }

    /**
     * Manages a template basic cache
     *
     * @param   string   $templatePath HTML template full path
     *
     * @return  string   HTML template
     */
    protected function fromCache($templatePath)
    {
        if (empty($templatePath)) {
            throw new \Exception(sprintf("Invalid parameter for %s() : template path must be set.", __FUNCTION__));
        }
        if (array_key_exists($templatePath, $this->cache)) {
            return $this->cache[$templatePath];
        }
        $this->cache[$templatePath] = file_get_contents($templatePath);
        return $this->cache[$templatePath];
    }
}
