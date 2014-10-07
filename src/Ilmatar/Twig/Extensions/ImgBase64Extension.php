<?php
namespace Ilmatar\Twig\Extensions;

/*
 * This extension allows image src as base64 strings
 * Expected parameter is an associative array
 * <img src="{{ image64(img) }}" alt="..."/>
 */
class ImgBase64Extension extends \Twig_Extension
{
    const MIME_PNG = 'image/png';
    const MIME_JPG = 'image/jpeg';

    public function getFunctions()
    {
        return array(
            'image64'      => new \Twig_Function_Method($this, 'image64'),
            'image64Light' => new \Twig_Function_Method($this, 'image64Light')
        );
    }

    public function image64($img)
    {
        $img['binary'] = base64_encode($img['binary']);
        return $this->image64Light($img);
    }
    
    public function image64Light($img)
    {
        return sprintf('data:%s;base64,%s', $img['mime'], $img['binary']);
    }

    public function getName()
    {
        return 'ilmatar_imgBase64';
    }
}
