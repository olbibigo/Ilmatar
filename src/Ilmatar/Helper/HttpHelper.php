<?php
namespace Ilmatar\Helper;

use Ilmatar\BaseHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Guzzle\Http\Client;

/**
 * Helper class to HTTP for download, upload, communicate with web services.
 *
 */
class HttpHelper extends BaseHelper
{
    const METHOD_POST    = 'POST';
    const METHOD_GET     = 'GET';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_HEAD    = 'HEAD';
    /**
     * Mandatory parameters for this helper
     */
    protected $expected = [
        'validator' //$app['validator']
    ];

    /**
     * Downloads one document given as string
     *
     * @param   string    $content   Document for download
     * @param   mimetype  $mimeType  Document mime type
     * @param   string    $filename  Document name. If null or empty, browser tries to deal with it directly.
     *
     * @return  Response  an HTTP Response object
     */
    public function downloadFromString($content, $mimeType, $filename = '')
    {
        if (empty($content)) {
            return new Response('Empty content. Cannot download.', 404, ['Content-Type' => 'text/plain']);
        }
        if (is_null($filename) || empty($filename)) {
            $headers = ['Content-Type' => $mimeType];
        } else {
            $filename = explode('/', $filename);
            $filename = $filename[count($filename) - 1];
            $headers = [
                'Content-Transfer-Encoding' => 'binary',
                'Accept-Ranges'             => 'bytes',
                'Content-Encoding'          => 'none',
                'Cache-Control'             => 'no-cache',
                'Pragma'                    => 'no-cache',
                'Content-Length'            => function_exists('mb_strlen') ? mb_strlen($content, '8bit') : strlen($content),
                'Content-Type'              => $mimeType,
                'Content-Disposition'       => 'attachment; filename=' . str_ireplace(' ', '_', $filename),
                'Last-Modified'             => gmdate('D, d M Y H:i:s', time()) . ' GMT'
            ];
        }
        return new Response(
            $content,
            200,
            $headers
        );
    }

    /**
     * Downloads one document given as a path
     *
     * @param   string    $path      Document full path
     * @param   mimetype  $mimeType  Document mime type
     * @param   string    $filename  Document name. If null or empty, browser tries to deal with it directly.
     *
     * @return  Response  a HTTP Response object
     */
    public function downloadFromPath($path, $mimeType, $filename = '')
    {
        if (empty($path) || !file_exists($path)) {
            return new Response('Invalid path. Cannot download.', 404, ['Content-Type' => 'text/plain']);
        }
        return $this->downloadFromString(file_get_contents($path), $mimeType, $filename);
    }

    /**
     * Uploads one document given in the request
     *
     * @param   Request   $request    An HTTP Request object
     * @param   string    $targetDir  Full path of the folder to write the document into
     *
     * @return  string    Document full path
     */
    public function uploadFromRequest(Request $request, $targetDir)
    {
        if (!file_exists($targetDir)) {
            throw new \Exception('Invalid folder. Cannot upload.');
        }
        $files = $request->files->all();
        $out = [];
        foreach ($files as $subFiles) {
            foreach ($subFiles as $file) {
                $file->move($targetDir, $file->getClientOriginalName());
            }
            $out[] = $targetDir . '/' . $file->getClientOriginalName();
        }
        return $out;
    }

    /**
     * Send an HTTP request
     *
     * @param   string   $url    The URL to download document from
     * @param   string   $vars   Data sent into request
     * @param   integer  $method HTTP method to use (POST or GET
     *
     * @return  string   Uploaded document
     */
    public function sendRequest($url, Array $queryVars = [], Array $postVars = [], $method = self::METHOD_GET)
    {
        $errors = $this->mandatories['validator']->validateValue($url, new Assert\Url());
        if (count($errors) != 0) {
            throw new \Exception(sprintf("Invalid parameter for %s() : must be a url.", __FUNCTION__));
        };
        $client = new Client(
            '',
            [
                'request.options' => [
                    'timeout'         => 20,
                    'connect_timeout' => 5
                ]
            ]
        );
        switch ($method) {
            case self::METHOD_GET:
                $request = $client->get($url, [], ['query' => $queryVars]);
                break;
            case self::METHOD_POST:
                $request = $client->post($url, [], ['query' => $queryVars])->addPostFiles($postVars);
                break;
            case self::METHOD_PUT:
                $request = $client->put($url, [], ['query' => $queryVars])->addPostFiles($postVars);
                break;
            case self::METHOD_DELETE:
                $request = $client->delete($url, [], ['query' => $queryVars])->addPostFiles($postVars);
                break;
            default:
                $request = $client->head($url, [], ['query' => $queryVars]);
        }
        $response = $request->send();
        return $response->getBody(true);
    }
}
