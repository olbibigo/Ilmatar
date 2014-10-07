<?php
namespace Ilmatar\Exception;

class TranslatedException extends \Exception
{
    private $format;
    private $arg;

    public function __construct($format, $arg = [])
    {
        $this->format = $format;
        $this->arg = $arg;
        parent::__construct(vsprintf($format, str_replace('trans:', '', $arg)), 0);
    }

    public function getTranslatedMessage($trans)
    {
        $narg = [];
        $count = 0;
        $nvalue = '';

        foreach ($this->arg as $value) {
            $nvalue = str_replace('trans:', '', $value, $count);
            if ($count != 0) {
                $narg[] = $trans->trans($nvalue);
            } else {
                $narg[] = $value;
            }
        }
        return vsprintf($trans->trans($this->format), $narg);
    }
}
