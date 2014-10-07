<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;

/**
 * Helper class to manipulate archives.
 *
 */
class ArchiveHelper extends BaseHelper
{
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = ['app.var'];

    /**
     * Create an archive of files
     *
     * @param array   $files  Array of name => path
     * @return string         Archive
     */
    public function create(Array $files)
    {
        $zip = new \ZipArchive();
        $zippath = $this->mandatories['app.var'] . DIRECTORY_SEPARATOR . uniqid();

        $code = $zip->open($zippath, \ZipArchive::CREATE);
        foreach ($files as $name => $path) {
            if (file_exists($path)) {
                $zip->addFile($path, $name);
            }
        }
        $zip->close();
        $contents = file_get_contents($zippath);
        @unlink($zippath);
        return $contents;
    }
}
