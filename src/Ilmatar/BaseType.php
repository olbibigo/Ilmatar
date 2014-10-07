<?php
namespace Ilmatar;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

abstract class BaseType extends AbstractType
{
    protected $expected = [];

    protected $mandatories = [];
    protected $options     = [];

    public function __construct(array $mandatories = [], array $options = [])
    {
        $this->mandatories = $mandatories;
        foreach ($this->expected as $key) {
            if (!array_key_exists($key, $this->mandatories)) {
                throw new MissingMandatoryParametersException(
                    sprintf('Missing mandatory option "%s" in form type %s', $key, get_called_class())
                );
            }
        }
        $this->options = $options;
    }
}
