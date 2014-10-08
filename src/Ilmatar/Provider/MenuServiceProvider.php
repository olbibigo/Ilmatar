<?php
namespace Ilmatar\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MenuServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['menu.set'] = $app->protect(function ($menus, $request) use ($app) {
            $app['my_main_menu'] = function ($app) use ($menus, $request) {
                $user = $app['security']->getToken()->getUser();
                $repo = $app['orm.em']->getRepository('\Entities\Permission');
                $root = $app['knp_menu.factory']->createItem('root');
                $root->setCurrentUri($request->getRequestUri());
                           
                foreach ($menus as $label => $subMenus) {
                    if (isset($subMenus['route'])) {//only one top level menu with link
                        if ($repo->isAllowedFunctionality($user, $subMenus['functionality'], $subMenus['type'])) {
                            $menu = $app['knp_menu.factory']->createItem(
                                $app['translator']->trans($label),
                                [
                                    'route'      => $subMenus['route'],
                                    'attributes' => (isset($subMenus['attributes']) ? $subMenus['attributes'] : []),
                                    'extras'     => (isset($subMenus['extras']) ? $subMenus['extras'] : [])
                                ]
                            );
                            $root->addChild($menu);
                        }
                        continue;
                    }
                    $menu = $app['knp_menu.factory']->createItem(
                        $app['translator']->trans($label),
                        [
                           'attributes' => (isset($subMenus['attributes']) ? $subMenus['attributes'] : []),
                           'extras'     => (isset($subMenus['extras']) ? $subMenus['extras'] : [])
                        ]
                    );
                    foreach ($subMenus['items'] as $subLabel => $subMenu) {
                        if ($repo->isAllowedFunctionality($user, $subMenu['functionality'], $subMenu['type'])) {
                            $attributes = isset($subMenu['extras']) ? $subMenu['extras'] : [];
                            $attributes  = array_merge($attributes, ["class" => "ui-widget-header", "style" =>"font-weight:normal"]);
                            $menu->addChild(
                                $subLabel,
                                [
                                    'route'      => $subMenu['route'],
                                    'label'      => $app['translator']->trans($subLabel),
                                    'attributes' => $attributes,
                                    'extras'     => (isset($subMenu['extras']) ? $subMenu['extras'] : [])
                                ]
                            );
                        }
                    }
                    if ($menu->count() > 0) {
                        $root->addChild($menu);
                    }
                }
                return $root;
            };
            $app['knp_menu.menus'] = ['main' => 'my_main_menu'];
        });
    }

    public function boot(Application $app)
    {
        //Nothing
    }
}
