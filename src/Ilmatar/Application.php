<?php
namespace Ilmatar;

class Application extends \Silex\Application
{
    /**
     * @var array[\Ilmatar\Application]
     */
    protected static $apps = [];

    /**
     * Instantiate a new \Ilmatar\Application.
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     *
     * @return \Ilmatar\Application
     */
    public function __construct(array $values = [])
    {
        if (! isset($values['name'])) {
            $values['name'] = 'default';
        }
        parent::__construct($values);
        static::$apps[$values['name']] = $this;
    }

    /**
     * Get application instance by name
     *
     * @param string $name The name of the Ilmatar application
     *
     * @return \Ilmatar\Application|null
     */
    public static function getInstance($name = 'default')
    {
        return isset(static::$apps[$name]) ? static::$apps[$name] : null;
    }

    /**
     * Remove an application instance by name
     *
     * @param string $name The name of the Ilmatar application
     *
     * @return void
     */
    public static function removeInstance($name = 'default')
    {
        unset(static::$apps[$name]);
    }
}
