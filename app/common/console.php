<?php
use Gedmo\Blameable\BlameableListener;
use Knp\Provider\ConsoleServiceProvider;

$app->register(
    new ConsoleServiceProvider(),
    [
        'console.name'              => $app['app.name'],
        'console.version'           => $app['app.version'],
        'console.project_directory' => $app['app.root']
    ]
);

/*
 * Checks if console is called to execute a jobby job
 */
$isJobby = false;
if (isset($_SERVER['argv'])) {
    foreach ($_SERVER['argv'] as $i => $arg) {
        if ($arg == '--jobby') {
            $isJobby = true;
            unset($_SERVER['argv'][$i]);
            break;
        }
    }
}
if (!$isJobby) {
    //Loads all declared commands from console.xml
    if (isset($app["command.classes"]) && isset($app["command.classes"]["value"])) {
        if (is_array($app["command.classes"]["value"])) {
            $commands = [];
            $namespaces   = [
                "\\Ilmatar\\Command\\",
                "\\Project\\Command\\"
            ];
            foreach ($app['command.classes']["value"] as $command) {
                foreach ($namespaces as $namespace) {
                    $class = $namespace . $command;
                    if (class_exists($class)) {
                        $commands[] = new $class();
                    }
                }
            }
            $app['console']->addCommands($commands);
        } else {
            $class = "\\Ilmatar\\Command\\" . $app["command.classes"]["value"];
            $app['console']->add(new $class());
        }
    }
} else {
    $namespaces = [
       "\\Project\\Command\\Jobby\\",
       "\\Ilmatar\\Command\\Jobby\\",
    ];
    //Loads all active commands from the database
    $jobs = $app["orm.em"]->getRepository('\\Entities\\Job')->getJobsReady();
    foreach ($jobs as $job) {
        foreach ($namespaces as $namespace) {
            $className = $namespace . $job->getClass();
            if (class_exists($className)) {
                $app['console']->add(new $className());
                break;
            }
        }
    }
}
/*
 * Doctrine behaviours
 */
//created_by & updated_by
$blameableListener = new BlameableListener();
$blameableListener->setUserValue("Console");
$app['orm.em']->getEventManager()->addEventSubscriber($blameableListener);
        
return $app;
