<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Zend\Barcode\Barcode;
use Ilmatar\Twig\Extensions\ImgBase64Extension;
use Dinesh\Barcode\DNS1D;
use Dinesh\Barcode\DNS2D;

/**
 * Helper class to create 1D or 2D barcode.
 *
 */
class BarcodeHelper extends BaseHelper
{
    //Valid codes are defined at http://framework.zend.com/manual/2.3/en/modules/zend.barcode.objects.html
    const BARECODE_1D_CODE39  = 'code39';
    const BARECODE_1D_EAN13   = 'EAN-13';
    const BARECODE_1D_EAN8    = 'EAN-8';
    const BARECODE_1D_UPCA    = 'UPC-A';
    const BARECODE_1D_UPCE    = 'UPC-E';
    //Valid codes are defined into Dinesh\Barcode\DNS1D
    const BARECODE_1D_CODE128 = 'C128';
    //Valid codes are defined into Dinesh\Barcode\DNS2D
    const BARECODE_2D_DATAMATRIX = 'DATAMATRIX';
    const BARECODE_2D_QRCODE     = 'QRCODE,Q';
    const BARECODE_2D_PDF417     = 'PDF417';
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    /**
     * Creates a 1D barcode
     *
     * @param string  $text
     * @param enum    $type
     *
     * @return array PNG binary image as a structure for ImgBase64Extension
     */
    public function create1DPngBarcode($text, $type = self::BARECODE_1D_CODE39)
    {
        //Code128 from ZF failed so we use DNS1D. Unfortunately it does not display text under the BC.
        if (self::BARECODE_1D_CODE128 != $type) {
            $imageResource = Barcode::draw(
                $type,
                'image',
                [
                    //http://framework.zend.com/manual/2.3/en/modules/zend.barcode.objects.html
                    'font'          => 3,
                    'stretchText'   => true,
                    'barThickWidth' => 4,
                    'barThinWidth'  => 2,
                    'withChecksum'  => true,
                    'text'          => $text
                ]
            );
            ob_start();
            imagepng($imageResource);
            $binary =  ob_get_contents();
            ob_end_clean();
        } else {
            $binary = (new DNS1D())->getBarcodePNG($text, self::BARECODE_1D_CODE128, 2, 50);//Already base64 encoded
        }
        return [
            'binary' => $binary,
            'mime'   => ImgBase64Extension::MIME_PNG
        ];
    }
    
    /**
     * Creates a 2D barcode
     *
     * @param string  $text
     * @param enum    $type
     
     * @return array PNG binary image as a structure for ImgBase64Extension
     */
    public function create2DPngBarcode($text, $type = self::BARECODE_2D_QRCODE)
    {
        return [
            'binary' => (new DNS2D())->getBarcodePNG($text, $type, 3, 3),//Already base64 encoded
            'mime'   => ImgBase64Extension::MIME_PNG
        ];
    }
}
