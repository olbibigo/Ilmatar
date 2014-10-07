<?php
namespace Project\Tag;

use Ilmatar\BaseTagStrategy;

class UserName extends BaseTagStrategy
{
    protected $expected = array('user');

    /**
     * Returns formated string
     *
     * @return string
     */
    public function format()
    {
        if ($this->mandatories['user'] instanceof \Entities\user) {
            return $this->mandatories['user']->getFullname();
        }
        return '';
    }
}
