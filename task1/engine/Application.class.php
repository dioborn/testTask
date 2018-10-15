<?php

class Application
{

    public $Config = false;
    public $Controller = false;
    private $_classes = array();
    static private $instance = NULL;

    static function getInstance($Config = null)
    {
        if (self::$instance == NULL)
            self::$instance = new Application($Config);

        return self::$instance;
    }

    private function __clone()
    {
        
    }

    private function __construct()
    {
        
    }
    /* create new element of class while first calling */

    public function __get($name)
    {
        if (isset($this->_classes[$name])) {
            return $this->ImportModule(
                    $name, $this->_classes[$name]['name'], $this->_classes[$name]['conf']
            );
        }
        return false;
    }

    public function __destruct()
    {
        foreach ($this as $k => $v) {
            unset($v);
        }
    }
    /* init application */

    public function AppInit($config, $request = '')
    {
        if ($config == null) {
            return false;
        }
        if (isset($config['controller'])) {
            if ($request != '' && isset($config['controller']['map']) && isset($config['controller']['map'][$request])) {
                $controller = $config['controller']['map'][$request] . 'Controller';
            } elseif (isset($config['controller']['default'])) {
                $controller = $config['controller']['default'] . 'Controller';
            } else {
                $controller = "Controller";
            }
            if (!file_exists($config['controller']['path'] . $controller . '.class.php')) {
                return false;
            }
            require_once($config['controller']['path'] . $controller . '.class.php');
        } else {
            $controller = 'Controller';
        }

        if (!class_exists($controller)) {
            return false;
        }

        $this->Config = new Config();
        foreach ($config['global'] as $var => $value) {
            $this->Config->{$var} = $value;
        }

        foreach ($config['classes'] as $name => $class) {
            if (isset($config['autoload']) && !in_array($name, $config['autoload'])) {
                $this->_classes[$name] = array(
                    'name' => $class,
                    'conf' => isset($config[$name]) ? $config[$name] : array(),
                );
            } else {
                $this->ImportModule(
                    $name, $class, isset($config[$name]) ? $config[$name] : array()
                );
            }
        }
        $this->Controller = new $controller();

        return true;
    }

    private function ImportModule($name, $class, $conf = array())
    {
        $c = array();

        /* include class */
        if (file_exists(dirname(__FILE__) . "/classes/" . $class . ".class.php")) {
            include_once(dirname(__FILE__) . "/classes/" . $class . ".class.php");
            if (class_exists($class)) {
                if (is_array($conf)) {
                    foreach ($conf as $k => $v) {
                        $c[$k] = $v;
                    }
                } else if (is_string($conf) && file_exists($conf)) {
                    include_once($conf);
                    foreach ($config as $k => $v) {
                        $c[$k] = $v;
                    }
                }
                $this->{$name} = new $class($c);
                return $this->{$name};
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}

class Config
{

    function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    function __get($name)
    {
        return false;
    }
}

class temp
{

    function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}
