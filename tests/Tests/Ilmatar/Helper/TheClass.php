<?php
namespace Tests\Ilmatar\Helper;

/*
 * Utilisé par ObjectHelperTest
 */
class TheClass
{
    private $id;
    private $name;
    private $dummy1;//with getter
    public $description;
    public $dummy2;//not requested
    
    public function __construct($id, $name, $description)
    {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
    }
    public function getId()
    {
        return $this->id;
    }
    public function __get($name)
    {
        return $this->$name;
    }
}
