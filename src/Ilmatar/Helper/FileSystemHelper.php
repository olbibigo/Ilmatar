<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;

/**
 * Helper class to manipulate the file system.
 *
 */
class FileSystemHelper extends BaseHelper
{
    const FILE_ALL_INFOS = 'FILE_ALL_INFOS';

    const FILE_DIRNAME   = 'FILE_DIRNAME';
    const FILE_EXTENSION = 'FILE_EXTENSION';
    const FILE_FILENAME  = 'FILE_FILENAME';
    const FILE_BASENAME  = 'FILE_BASENAME';

    const FILE_ATIME  = 'FILE_ATIME';
    const FILE_CTIME  = 'FILE_CTIME';
    const FILE_GROUP  = 'FILE_GROUP';
    const FILE_MTIME  = 'FILE_MTIME';
    const FILE_OWNER  = 'FILE_OWNER';
    const FILE_PERMS  = 'FILE_PERMS';
    const FILE_SIZE   = 'FILE_SIZE';
    const FILE_TYPE   = 'FILE_TYPE';
    const FILE_MIME   = 'FILE_MIME';
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [];

    /**
     * Removes recursively a folder and all its content
     *
     * @param   string  $path  Folder or file to delete (recursively)
     *
     * @return void
     */
    public function rrmdir($path)
    {
        $classFunc = [__CLASS__, __FUNCTION__];
        //here is a trick to merge two statements into one statement.
        //This is because ternary operators allow only one statement per argument.
        return is_file($path) ?
            @unlink($path) :
            array_map($classFunc, glob($path . '/*')) == @rmdir($path);
    }
    /**
     *  Lists files and directories inside the specified path
     *
     * @param  string  $path           Root folder path
     * @param  boolean $isPattern      Is $path a pattern to search? (default: false)
     * @param  boolean $isFileOnly     Filters only files? (default: false)
     * @param  boolean $isRecursively  Is recursive search? (default false)
     * @return array
     */
    public function rscandir($path, $isPattern = false, $isFileOnly = false, $isRecursively = false)
    {
        if (!$isRecursively) {
            if (!$isPattern) {
                if ($isFileOnly) {
                    //Without path as prefix
                    return array_filter(
                        array_diff(
                            scandir($path),
                            ['..', '.']
                        ),
                        function ($item) use ($path) {
                            return !is_dir($path . DIRECTORY_SEPARATOR . $item);
                        }
                    );
                }
                //Without path as prefix
                return array_diff(
                    scandir($path),
                    ['..', '.']
                );
            }
            if ($isFileOnly) {
                //With path as prefix
                return array_filter(
                    glob($path),
                    function ($item) {
                        return !is_dir($item);
                    }
                );
            }
            //With path
            return glob($path);
        }
        if (!$isPattern) {
            //recursively without pattern
            $files = [];
            $cdir  = scandir($path);
            foreach ($cdir as $key => $value) {
                if (!in_array($value, [".", ".."], true)) {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $value)) {
                        $files[$value] = $this->rscandir($path . DIRECTORY_SEPARATOR . $value, $isPattern, $isFileOnly, $isRecursively);
                    } else {
                        $files[] = $path . DIRECTORY_SEPARATOR . $value;
                    }
                }
            }
            //With path as prefix
            return $files;
        }
        //recursively with pattern
        if ($isFileOnly) {
            //With path as prefix
            $files = array_filter(
                glob($path),
                function ($item) {
                    return !is_dir($item);
                }
            );
        } else {
            $files = glob($path);
        }
        foreach (glob(dirname($path) . '/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rscandir($dir . DIRECTORY_SEPARATOR . basename($path), $isPattern, $isFileOnly, $isRecursively));
        }
        //With path as prefix
        return $files;
    }
    /**
     * Get file infos from all files below a folder path
     *
     * @param  string        $folderPath     Folder path to analyse
     * @param  boolean       $isRecursively  Is recursive search? (default false)
     * @param  string|array  $options        Wanted info (default: all);

     * @return array
     */
    public function getFileInfosByDir($folderPath, $isRecursively = false, $options = self::FILE_ALL_INFOS)
    {
        $files  = $this->rscandir($folderPath, false, true, $isRecursively);
        $result = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveArrayIterator($files), \RecursiveIteratorIterator::LEAVES_ONLY) as $filePath) {
            $result[] = $this->getFileInfos($filePath, $options);
        }
        return $result;
    }
    /**
     * Get file infos from a path
     *
     * @param   string  $filePath    File path to analyse
     * @param   string  $option      Wanted info (default: all);
     * @return array
     */
    public function getFileInfos($filePath, $options = self::FILE_ALL_INFOS)
    {
        if (!file_exists($filePath)) {
            return [];
        }
        if (!is_array($options)) {
            $options = [$options];
        }
        $result = [];
        foreach ($options as $option) {
            switch ($option) {
                case self::FILE_DIRNAME:
                    $parts = pathinfo($filePath);
                    $result[self::FILE_DIRNAME]   = $parts['dirname'];
                    break;
                case self::FILE_EXTENSION:
                    $parts = pathinfo($filePath);
                    $result[self::FILE_EXTENSION] = $parts['extension'];
                    break;
                case self::FILE_FILENAME:
                    $parts = pathinfo($filePath);
                    $result[self::FILE_FILENAME]  = $parts['filename'];
                    break;
                case self::FILE_BASENAME:
                    $parts = pathinfo($filePath);
                    $result[self::FILE_BASENAME]  = $parts['basename'];
                    break;
                case self::FILE_ATIME:
                    $result[self::FILE_ATIME] = fileatime($filePath);
                    break;
                case self::FILE_CTIME:
                    $result[self::FILE_CTIME] = filectime($filePath);
                    break;
                case self::FILE_GROUP:
                    $result[self::FILE_GROUP] = filegroup($filePath);
                    break;
                case self::FILE_MTIME:
                    $result[self::FILE_MTIME] = filemtime($filePath);
                    break;
                case self::FILE_OWNER:
                    $result[self::FILE_OWNER] = fileowner($filePath);
                    break;
                case self::FILE_PERMS:
                    $result[self::FILE_PERMS] = fileperms($filePath);
                    break;
                case self::FILE_SIZE:
                    $result[self::FILE_SIZE]  = filesize($filePath);
                    break;
                case self::FILE_TYPE:
                    $result[self::FILE_TYPE]  = filetype($filePath);
                    break;
                case self::FILE_MIME:
                    $result[self::FILE_MIME]  = $this->getMimeType($filePath);
                    break;
                default:
                    $result = array_merge(
                        pathinfo($filePath),
                        [
                            'atime' => fileatime($filePath),
                            'ctime' => filectime($filePath),
                            'group' => filegroup($filePath),
                            'mtime' => filemtime($filePath),
                            'owner' => fileowner($filePath),
                            'perms' => fileperms($filePath),
                            'size'  => filesize($filePath),
                            'type'  => filetype($filePath),
                            'mime'  => $this->getMimeType($filePath)
                        ]
                    );
                    break;
            }
        }
        return $result;
    }
    /**
     * Get Mime type from a file
     *
     * @param   string  $path    File path to analyse
     *
     * @return string
     */
    public function getMimeType($path)
    {
        $mimeTypes = [
            'txt'  => 'text/plain',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'php'  => 'text/html',
            'css'  => 'text/css',
            'csv'  => 'text/csv',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'xml'  => 'application/xml',
            'swf'  => 'application/x-shockwave-flash',
            'flv'  => 'video/x-flv',
            // images
            'png'  => 'image/png',
            'jpe'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'bmp'  => 'image/bmp',
            'ico'  => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif'  => 'image/tiff',
            'svg'  => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt'  => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai'  => 'application/postscript',
            'eps' => 'application/postscript',
            'ps'  => 'application/postscript',

            // ms office
            'rtf'  => 'application/rtf',
            'doc'  => 'application/msword',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation'
            
            //...
        ];
        $array = explode('.', $path);
        $ext   = strtolower(array_pop($array));
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mimetype;
        }
        return 'application/octet-stream';
    }

    /**
     * Returns $length last characters from a file.
     *
     * @param string  $filePath
     * @param integer $length
     * @param boolean $isBackwards
     * @param boolean $isForWeb
     */
    public function tail($filePath, $length = null, $isBackwards = false, $isForWeb = true)
    {
        $handle = fopen($filePath, 'rb');
        $size   = filesize($filePath);

        if ($length === null) {
            $length = $size;
        }

        $buffer = '';

        if ($isBackwards) {
            $output = [];
            for ($xpos = 0, $ln = 0; (fseek($handle, $xpos, SEEK_END) != -1) && ($xpos > -$length); $xpos--) {
                $char = fgetc($handle);
                if ($char === "\n") {
                    $ln++;
                    continue;
                }
                $output[$ln] = $char . ((array_key_exists($ln, $output)) ? $output[$ln] : '');
            }
            $buffer= implode($isForWeb ? '<br/>' : "\n", $output);
            if ($size - $length >= 0) {
                $buffer .= ($isForWeb ? '<br/>' : "\n") .'&hellip;';
            }
        } else {
            $offset = $size - $length;
            if ($offset >= 0) {
                $buffer = '&hellip;';
                fseek($handle, $offset);
            }
            while (!feof($handle)) {
                $buffer .= fread($handle, 8192);
            }
            if ($isForWeb) {
                $buffer = str_replace("\n", "<br/>", $buffer);
            }
        }
        fclose($handle);
        return $buffer;
    }

    /**
     * Magic method (interpreter hooks) to deal with all isFileXXX() methods
     * usefull to determine file type using  numbers
     *
     */
    public function __call($method, $args)
    {
        if (substr($method, 0, 6) == 'isFile') {
            $fileSignatures = [//Each bytes is defined in DEC mode
                'png' => [137, 80, 78, 71, 13, 10, 26, 10], //.PNG....
                'jpg' => [255, 216, 255], //ÿØÿà
                'pdf' => [37, 80, 68, 70] //%PDF
                //...
            ];

            $expectedType = strtolower(substr($method, 6));
            if (!array_key_exists($expectedType, $fileSignatures)) {
                throw new \Exception(sprintf("Unknown method %s() in %s()", $method, __FUNCTION__));
            }
            //Is file given as a path
            if (file_exists($args[0])) {
                if ($f = fopen($args[0], 'rb')) {
                    $header = fread($f, count($fileSignatures[$expectedType]));
                    fclose($f);
                } else {
                    throw new \Exception(sprintf("File cannot be opened in %s()", __FUNCTION__));
                }
            } else {//Is file a string
                $header = substr($args[0], 0, count($fileSignatures[$expectedType]));
            }
            //Converts string into array
            $chars = array_map('ord', str_split($header));
            return (0 === count(array_diff($fileSignatures[$expectedType], $chars))) && (strlen($header) == count($fileSignatures[$expectedType]));
        }
        throw new \Exception(sprintf("invalid method %s() in %s()", $method, __FUNCTION__));
    }
}
