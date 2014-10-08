<?php
namespace Ilmatar\Command;

use Ilmatar\Application;
use Symfony\Component\Console\Input\InputInterface;
use Silex\Provider\UrlGeneratorServiceProvider;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\FormServiceProvider;
use Ilmatar\BaseCommand;
use Knp\Menu\Silex\KnpMenuServiceProvider;
use Ilmatar\Twig\Extensions\ImgBase64Extension;

class WarmUpCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:warmup')
            ->setDescription('Resets and warms up the cache')
            ->setHelp('Resets and warms up the cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Resets the OPCache
        //In php.ini set opcache.fast_shutdown=0
        if (extension_loaded('Zend OPcache')) {
            if (true === opcache_reset()) {
                $output->write("OPcache reset OK.", true);
            } else {
                $output->write("OPcache reset failed.", true);
            }
        }

        //Resets the TWIG cache
        $app = Application::getInstance();
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(
            new TwigServiceProvider(),
            array(
                'twig.path'    => $app['app.root']. '/views',
                'twig.options' => array(
                    'charset' => "utf-8",
                    'cache'   => $app['app.root'] . "/build"
                )
            )
        );
        $app['twig'] = $app->share($app->extend('twig', function (\Twig_Environment $twig, Application $app) {
            $twig->addExtension(new ImgBase64Extension($app));
            return $twig;
        }));
        $app->register(new KnpMenuServiceProvider());
        $app->register(new FormServiceProvider());
        $app->register(new FormServiceProvider());
        $twig   = $app["twig"];
        $finder = new Finder();
        $finder->files()->name('*.twig')->in($app['app.root'].'/views');
        foreach ($finder as $template) {
            $relativePath = $template->getRelativePathname();
            try {
                $twig->loadTemplate($relativePath);
                $output->write(sprintf("%s : twig cache OK", $relativePath), true);
            } catch (\Twig_Error $e) {
                $output->write(sprintf("%s : %s", $relativePath, $e->getMessage()), true);
            }
        }

        //Warmups the OPCache
        /*@todo: failed notice cache seems disabled
        if (extension_loaded('Zend OPcache')) {
            $finder = new Finder();
            $finder->files()->name('*.php')->in($app['app.root'].'/app')->in($app['app.root'].'/src');
            foreach ($finder as $template) {
                $realPath = $template->getRealpath();
                try {
                    opcache_compile_file($realPath);
                    $output->write(sprintf("%s : OPcache OK" , $realPath), true);
                } catch (\Exception $e) {
                    $output->write(sprintf("%s : %s" , $realPath, $e->getMessage()), true);
                }
            }
        }*/
    }
}
