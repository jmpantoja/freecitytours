<?php

/**
 * This file is part of the planb-cms-project-skeleton project.
 *
 * (c) Jose Manuel Pantoja <jmpantoja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolt\Extension\PlanB\FreeCity;

use Bolt\Extension\PlanB\FreeCity\Manager\ResourcesManager;
use Bolt\Extension\PlanB\FreeCity\Manager\PlacesManager;
use Bolt\Extension\PlanB\FreeCity\Manager\ReserveManager;
use Silex\Application;
use Bolt\Extension\PlanB\FreeCity\Controller\FreeCityController;
use Bolt\Extension\SimpleExtension;
use Silex\ControllerCollection;


/**
 * @TODO Description of Class FreeCity
 * @package Bolt\Extension\PlanB\FreeCity
 * @author  Jose Manuel Pantoja <jmpantoja@gmail.com>
 */
class FreeCityExtension extends SimpleExtension
{
    protected function registerServices(Application $app)
    {
        $app['controller.freecity'] = $app->share(
            function ($app) {
                $controller = new FreeCityController($this->getConfig());
                $controller->connect($app);
                return $controller;
            }
        );

        $app['reserves.manager'] = $app->share(
            function ($app) {
                return new ReserveManager($app, $this->getConfig());
            }
        );

        $app['places.manager'] = $app->share(
            function ($app) {
                return new PlacesManager($app, $this->getConfig());
            }
        );

        $app['image.manager'] = $app->share(
            function ($app) {
                return new ResourcesManager($app, $this->getConfig());
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFunctions()
    {
        $app = $this->getContainer();
        $manager = $app['image.manager'];

        return [
            'img' => [[$manager, 'imageResponsive'], ['is_safe' => ['html']]],
            'bundler' => [[$manager, 'bundler'], ['is_safe' => ['html']]],
        ];
    }

}