<?php
namespace Ilmatar;

use Symfony\Component\Translation\TranslatorInterface;
use Silex\Route as BaseRoute;

class Route extends BaseRoute
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translatorDomain = 'routes';

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function setTranslatorDomain($domain)
    {
        $this->translatorDomain = $domain;
    }

    public function setPath($pattern)
    {
        if (isset($this->translator)) {
            $translator = $this->translator;
            $domain     = $this->translatorDomain;
            $pattern    = explode("/", $pattern);
            array_walk(
                $pattern,
                function (&$value, $key) use ($translator, $domain) {
                    if (!empty($value)) {
                        $value = filter_var($translator->trans($value, [], $domain), FILTER_SANITIZE_URL);
                    }
                }
            );
            $pattern = implode("/", $pattern);
        }
        
        return parent::setPath($pattern);
    }
}
