<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Ilmatar\HelperFactory;
use PhpOffice\PhpWord\IOFactory;
use \PhpOffice\PhpWord\PhpWord;

/**
 * Helper class to manipulate word document.
 *
 */
class WordHelper extends BaseHelper
{
    const ONLY_WORD     = 'only_word';
    const WORD_AND_PDF  = 'word_and_pdf';
    
    protected $document = null;
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = ['app.root'];

    /**
     * Generates a Word document
     *
     * @param string  $templateFullpath   
     * @param string  $documentFullpath
     * @param array   $tags
     *
     * @return void
     */
    public function createDocumentFromTemplate($templateFullpath, $documentFullpath = null, $tags = [])
    {
        $phpWord = new PhpWord();
        $this->document = $phpWord->loadTemplate($templateFullpath);
        foreach ($tags as $key => $value) {
            $this->document->setValue($key, $value);
        }
        
        if (!is_null($documentFullpath)) {
            $this->save($documentFullpath);
        }
        return $this->document;
    }
    /**
     * Save on file system a Word or Pdf document
     *
     * @param string  $fullpath   
     * @param string  $type
     *
     * @return void
     */
    public function save($wordFullpath, $pdfFullPath = null, $type = self::ONLY_WORD)
    {
        if (is_null($this->document)) {
            throw \Exception(sprintf("createDocumentFromTemplate() must be called before saving into %s", __FUNCTION__));
        }
        $this->document->saveAs($wordFullpath);//As .docx
        
        if (!file_exists($wordFullpath)) {
            throw \Exception(sprintf("%s was not generated into ", $wordFullpath, __FUNCTION__));
        }
        
        if (self::WORD_AND_PDF === $type) {
            $htmlFullpath = tempnam(sys_get_temp_dir(), "ILM") . ".htm";

            $phpWord      = IOFactory::load($wordFullpath);
            $htmlWriter   = IOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($htmlFullpath);//As .html
            if (!file_exists($htmlFullpath)) {
                throw \Exception(sprintf("%s was not generated into %s", $htmlFullpath, __FUNCTION__));
            }
            HelperFactory::build(
                'PdfHelper',
                $this->mandatories
            )->generatePdf($htmlFullpath, [], $pdfFullPath);//As .pdf
            
            if (!file_exists($wordFullpath)) {
                throw \Exception(sprintf("%s was not generated into %s", $pdfFullPath, __FUNCTION__));
            }
        
            unlink($htmlFullpath);
        }
    }
}
