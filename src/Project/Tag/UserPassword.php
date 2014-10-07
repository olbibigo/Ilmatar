<?php
namespace Project\Tag;

use Ilmatar\BaseTagStrategy;

class UserPassword extends BaseTagStrategy
{
    protected $expected = array('password');

    /**
     * Returns formated string
     *
     * @return string
     */
    public function format()
    {
        return $this->mandatories['password'];
    }
}
