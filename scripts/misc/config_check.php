<?php
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

if (!isset($autoloader)) {
    $autoloader = require __DIR__ . '/../../vendor/autoload.php';
}

class CheckPlateform
{
    const ENTITIES_RELATIVE_PATH = '/../doctrine/schemas/current';
    //PHP version
    const MIN_ALLOWED_PHP_VERSION = "5.5.0";
    const MAX_ALLOWED_PHP_VERSION = null;//excluded
    //Internet access
    const CHECK_INTERNET_ACCESS = true;//or false
    //MySql
    const MIN_ALLOWED_MYSQL_VERSION = "5.5.0";
    const MAX_ALLOWED_MYSQL_VERSION = null;//excluded
    //List of allowed handlers
    protected $expectedApi = [
        'cli',
        'apache2handler'
    ];
    //List of necessary Apache modules
    protected $expectedApacheModules = [
        'mod_rewrite'
    ];
    //List of allowed operating systems
    protected $expectedOS = [
        'windows',
        'linux'
    ];
    //List of allowed architectures
    protected $expectedArchitecture = [
        'amd64',
        'x86_64'
    ];
    //List of necessary PHP extensions IN PRODUCTION
    protected $expectedExtensions = [//Extension name
        "common" => [
            "bcmath",
            "calendar",
            "Core",
            "ctype",
            "curl",
            "date",
            "dom",
            "ereg",
            "fileinfo",
            "filter",
            "ftp",
            "gd",
            "hash",
            "iconv",
            "intl",
            "json",
            "ldap",
            "libxml",
            "mbstring",
            "mcrypt",
            "mhash",
            "mysqlnd",
            "odbc",
            "openssl",
            "pcre",
            "PDO",
            "pdo_mysql",
            "pdo_sqlite",
            "Phar",
            "Reflection",
            "session",
            "SimpleXML",
            "sockets",
            "SPL",
            "sqlite3",
            "standard",
            "tokenizer",
            "wddx",
            "xml",
            "xmlreader",
            "xmlwriter",
            "Zend OPcache",
            "zip",
            "zlib"
        ],
        "apache2handler" => [
            "apache2handler",
            "mysqli", //for PhpMyAdmin
        ],
        "cli"            => [
            "mysqli", //for current script
        ]
    ];
    //List of expected PHP configuration IN PRODUCTION
    protected $expectedPhpConfig = [
        "allow_url_fopen"                 => ["cli" => 1 /*For Composer*/, "apache2handler" => 0],
        "allow_url_include"               => 0,
        "display_errors"                  => 0,//1 in dev
        "display_startup_errors"          => 0,//1 in dev
        "error_reporting"                 => 22527,//E_ALL & ~E_DEPRECATED & ~E_STRICT ; E_ALL in dev
        "expose_php"                      => 0,
        "file_uploads"                    => 1,
        "html_errors"                     => ["cli" => 0, "apache2handler" => 1],
        "log_errors"                      => 1,
        "magic_quotes_gpc"                => 0,
        "max_execution_time"              => ["cli" => 0, "apache2handler" => 30],
        "max_file_uploads"                => 5,
        "max_input_time"                  => ["cli" => -1, "apache2handler" => 60],
        "memory_limit"                    => ["cli" => "512M", "apache2handler" => "128M"],
        "output_buffering"                => ["cli" => 0, "apache2handler" => 4096],
        "post_max_size"                   => "5M",
        "precision"                       => 14,
        "register_argc_argv"              => ["cli" => 1, "apache2handler" => 0],
        "register_globals"                => 0,
        "request_order"                   => "GP",
        "session.gc_divisor"              => 1000,
        "session.hash_bits_per_character" => 5,
        "short_open_tag"                  => 0,
        "track_errors"                    => 0,//1 in dev
        "upload_max_filesize"             => "1M",
        "url_rewriter.tags"               => "a=href,area=href,frame=src,input=src,form=fakeentry",
        "use_trans_sid"                   => 0,
        "variables_order"                 => "GPCS",
        "opcache.enable"                  => 1,
        "opcache.enable_cli"              => 1,
        "opcache.memory_consumption"      => 128,
        "opcache.max_accelerated_files"   => 4000,
        "opcache.revalidate_freq"         => 60,
        "opcache.interned_strings_buffer" => 8,
        "opcache.fast_shutdown"           => ["cli" => 0, "apache2handler" => 1],
    ];
    //List of necessary PHP classes
    protected $expectedClasses = [//Class name
        'Silex\Application',
        'Monolog\Logger',
        'Symfony\Component\Console\Application',
        'Symfony\Component\Form\Form',
        'Symfony\Component\Security\Core\SecurityContext',
        'Symfony\Component\Translation\Translator',
        'Symfony\Component\Validator\Validator',
        'Swift_Mailer',
        'Knp\Snappy\Pdf',
        'Doctrine\ORM\EntityManager',
        'Carbon\Carbon',
        'Jobby\Jobby'
    ];
    //List of necessary files with OS and access rights
    protected $expectedFileFolders = [//Folder relative path form project root
        'vendor/h4cc/wkhtmltopdf-amd64/bin/'                    => ['os' => null, 'rights' => 'R'],
        'vendor/h4cc/wkhtmltoimage-amd64/bin/'                  => ['os' => null, 'rights' => 'R'],
        'vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf.exe'     => ['os' => 'windows', 'rights' => 'R'],
        'vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage.exe' => ['os' => 'windows', 'rights' => 'R'],
        'var/log'                                               => ['os' => null, 'rights' => 'RW'],
        'var'                                                   => ['os' => null, 'rights' => 'RW'],
        'build'                                                 => ['os' => null, 'rights' => 'RW']
    ];
    //List of expected MySql configuration IN PRODUCTION
    protected $expectedMySqlConfig = [
        "innodb_buffer_pool_size"        => 2147483648,//2G
        "innodb_log_file_size"           => 268435456,//256M
        "max_connections"                => 151,
        "innodb_file_per_table"          => "ON",
        "innodb_flush_log_at_trx_commit" => 1,
        "innodb_flush_method"            => 0,
        "innodb_log_buffer_size"         => 8388608,//8M
        "query_cache_size"               => 0,
        //"log_bin"                        => 0,
        "skip_name_resolve"              => "ON"
    ];
/*
 * DO NOT EDIT BELOW
 */
    const METHOD_PREFIX = 'assert';

    private $lineBreak;
    private $api;
    private $env;

    public function __construct()
    {
        $this->api       = php_sapi_name();
        $this->lineBreak = ('cli' == $this->api ? "\n" : "<br/>");
        $this->env       = XmlUtils::convertDomElementToArray(
            XmlUtils::loadFile(__DIR__ .  '/../../config/env.xml')->firstChild
        );
    }
    public function execute()
    {
        //Loop through all check methods by introspection
        $methods      = get_class_methods(__CLASS__);
        $finalReturn  = true;
        foreach ($methods as $method) {
            if (self::METHOD_PREFIX == substr($method, 0, strlen(self::METHOD_PREFIX))) {
                $return      = $this->$method();
                $finalReturn = $finalReturn && $return;
                echo str_pad($method, 50, '.') . ($return ? 'OK' : 'KO') . $this->lineBreak;
            }
        }
        return $finalReturn;
    }

    protected function assertApi()
    {
        foreach ($this->expectedApi as $expectedApi) {
            if (0 == strcasecmp($this->api, $expectedApi)) {
                return true;
            }
        }
        echo 'Current API : ' . $this->api . $this->lineBreak;
        return false;
    }

    protected function assertOS()
    {
        $os = php_uname("s");
        foreach ($this->expectedOS as $expectedOS) {
            if (false !== stripos($os, $expectedOS)) {
                return true;
            }
        }
        echo 'Current OS : ' . $os . $this->lineBreak;
        return false;
    }

    protected function assertArchitecture()
    {
        $archi = php_uname("m");
        foreach ($this->expectedArchitecture as $expectedArchitecture) {
            if (0 == strcasecmp($archi, $expectedArchitecture)) {
                return true;
            }
        }
        echo 'Current Architecture : ' . $archi . $this->lineBreak;
        return false;
    }

    protected function assertPhpVersion()
    {
        $return = true;
        if (!is_null(self::MIN_ALLOWED_PHP_VERSION)) {
            $return = version_compare(PHP_VERSION, self::MIN_ALLOWED_PHP_VERSION, '>=');
        }
        if (!is_null(self::MAX_ALLOWED_PHP_VERSION)) {
            $return = $return && version_compare(PHP_VERSION, self::MAX_ALLOWED_PHP_VERSION, '<');
        }
        if (!$return) {
            echo 'Current PHP version : ' . phpversion() . $this->lineBreak;
        }
        return $return;
    }

    protected function assertApacheModules()
    {
        if (!function_exists('apache_get_modules')) {//By-pass
            return true;
        }
        $modules = apache_get_modules();
        foreach ($this->expectedApacheModules as $expectedApacheModule) {
            if (!in_array($expectedApacheModule, $modules)) {
                echo 'Missing module : ' . $expectedApacheModule . $this->lineBreak;
                return false;
            }
        }
        return true;
    }

    protected function assertExtensions()
    {
        $isOK = true;

        $extensions         = get_loaded_extensions();
        $expectedExtensions = array_merge($this->expectedExtensions["common"], $this->expectedExtensions[$this->api]);

        $diffs = array_diff($expectedExtensions, $extensions);
        if (!empty($diffs)) {
            foreach ($diffs as $diff) {
                echo 'Missing extension : ' . $diff . $this->lineBreak;
            }
            $isOK = false;
        }

        $diffs = array_diff($extensions, $expectedExtensions);
        if (!empty($diffs)) {
            foreach ($diffs as $diff) {
                echo 'Unnecessary extension : ' . $diff . $this->lineBreak;
            }
            $isOK = false;
        }
        return $isOK;
    }

    protected function assertPhpConfig()
    {
        $isOK = true;
        foreach ($this->expectedPhpConfig as $key => $value) {
            if (is_array($value)) {
               $value = $value[$this->api];
            }
            if (ini_get($key) != $value) {
                echo sprintf("Wrong PHP config : %s (expected: %s, value: %s)", $key, $value, ini_get($key)). $this->lineBreak;
                $isOK = false;
            }
        }
        return $isOK;
    }

    protected function assertClasses()
    {
        foreach ($this->expectedClasses as $expectedClass) {
            if (!class_exists($expectedClass, true)) {
                echo 'Missing class : ' . $expectedExtension . $this->lineBreak;
                return false;
            }
        }
        return true;
    }

    protected function assertFilesFoldersExistenceAndAccessRights()
    {
        foreach ($this->expectedFileFolders as $expectedFileFolder => $data) {
            $filepath = __DIR__ . '/../../' . $expectedFileFolder;
            $os     = $data['os'];
            $rights = $data['rights'];
            if (is_null($os) || empty($os) || false !== stripos(php_uname("s"), $os)) {
                if ((false !== stripos($rights, 'R')) && !is_readable($filepath)) {
                    echo 'Cannot read file/folder : ' . $filepath . $this->lineBreak;
                    return false;
                }
                if ((false !== stripos($rights, 'W')) && !is_writable($filepath)) {
                    echo 'Cannot write file/folder : ' . $filepath . $this->lineBreak;
                    return false;
                }
            }
        }
        return true;
    }

    protected function assertInternetAccess()
    {
        if (!self::CHECK_INTERNET_ACCESS) {
            return true;
        }
        $connected = @fsockopen("www.free.fr", 80);
        if ($connected) {
            fclose($connected);
            return true;
        }
        return false;
    }

    protected function assertMySql()
    {
        if (is_null($this->env["dbHost"])) {//By-pass
            return true;
        }
        $mysqli = new mysqli($this->env["dbHost"], $this->env["dbUser"], $this->env["dbPassword"], $this->env["dbName"]);
        if (false === $mysqli) {
            echo 'Cannot access MySql : ' . $filepath . $this->lineBreak;
            return false;
        };
        $version = $mysqli->server_info;
        if (!is_null(self::MIN_ALLOWED_MYSQL_VERSION)) {
            $return = version_compare($version, self::MIN_ALLOWED_MYSQL_VERSION, '>=');
        }
        if (!is_null(self::MAX_ALLOWED_MYSQL_VERSION)) {
            $return = $return && version_compare($version, self::MAX_ALLOWED_MYSQL_VERSION, '<');
        }
        if (!$return) {
            echo 'Current MySQL version : ' . $version . $this->lineBreak;
        }
        return $return;
    }

    protected function assertMySqlConfig()
    {
        $isOK   = true;
        $mysqli = new mysqli($this->env["dbHost"], $this->env["dbUser"], $this->env["dbPassword"], $this->env["dbName"]);
        $res    = $mysqli->query("show variables");
        $res    = $res->fetch_all();
        foreach($res as $data) {
            $config[$data[0]] = $data[1];
        }

        foreach ($this->expectedMySqlConfig as $key => $value) {
            if (!array_key_exists($key, $config)) {
                echo sprintf("Unknown MySql config : %s", $key). $this->lineBreak;
                $isOK = false;
            } elseif ($config[$key] != $value) {
                echo sprintf("Wrong MySql config : %s (expected: %s, value: %s)", $key, $value, $config[$key]). $this->lineBreak;
                $isOK = false;
            }
        }
        return $isOK;
    }

    protected function assertDoctrineAssociation()
    {
        $isOK              = true;
        $associationsTypes = [
            'oneToMany'  => 'manyToOne',
            'manyToOne'  => 'oneToMany',
            'manyToMany' => 'manyToMany',
            'oneToOne'   => 'oneToOne'
        ];
         
        $associations = [];        
        $yaml         = new Parser();
        $finder       = new Finder();
        $finder->files()->in(__DIR__ . self::ENTITIES_RELATIVE_PATH)->name('*.yml');
        foreach ($finder as $file) {
            try {
                $fullModel             = $yaml->parse(file_get_contents($file->getRealpath()));
                $entity                =key($fullModel);
                $associations[$entity] = [];
                $model                 = reset($fullModel);
                foreach (array_keys($associationsTypes) as $associationsType) {
                    if (isset($model[$associationsType])) {
                       $associations[$entity][$associationsType] = $model[$associationsType];
                    }
                }
            } catch (ParseException $e) {
                echo sprintf("Unable to parse the YAML string : %s into %s", $e->getMessage(), $file->getRelativePath()). $this->lineBreak;
            }
        }
        
        foreach ($associations as $entity =>$entityAssociations) {
            foreach ($entityAssociations as $type => $typeAssociations) {
                foreach ($typeAssociations as $name => $details) {
                    $targetEntity = $details['targetEntity'];
                    $targetType   = $associationsTypes[$type];
                    if (isset($details['mappedBy'])) {
                        $targetName = $details['mappedBy'];
                        if (!isset($associations[$targetEntity]) || !isset($associations[$targetEntity][$targetType]) || !isset($associations[$targetEntity][$targetType][$targetName])) {
                            echo sprintf("Unable to find reversed side of %s --> %s  --> %s", $entity, $type, $name). $this->lineBreak;
                            $isOK = false;
                            continue;
                        }
                        if ($associations[$targetEntity][$targetType][$targetName]['inversedBy'] != $name) {
                            echo sprintf("Wrong inversedBy into %s --> %s  --> %s", $targetEntity, $targetType, $targetName). $this->lineBreak;
                            $isOK = false;
                        }
                    } elseif (isset($details['inversedBy'])) {
                        $targetName = $details['inversedBy'];
                        if (!isset($associations[$targetEntity]) || !isset($associations[$targetEntity][$targetType]) || !isset($associations[$targetEntity][$targetType][$targetName])) {
                            echo sprintf("Unable to find reversed side of %s --> %s  --> %s", $entity, $type, $name). $this->lineBreak;
                            $isOK = false;
                            continue;
                        }
                        if ($associations[$targetEntity][$targetType][$targetName]['mappedBy'] != $name) {
                            echo sprintf("Wrong mappedBy into %s --> %s  --> %s", $targetEntity, $targetType, $targetName). $this->lineBreak;
                            $isOK = false;
                        }
                    } elseif (isset($associations[$targetEntity][$targetType][$targetName])) {
                        echo sprintf("Bad configuration into %s --> %s  --> %s", $entity, $type, $name). $this->lineBreak;
                        $isOK = false;
                    }
                }
            }
        }
        return $isOK;
    }
}
//Launch check
$checker = new CheckPlateform();
return $checker->execute() ? 0 : -1;