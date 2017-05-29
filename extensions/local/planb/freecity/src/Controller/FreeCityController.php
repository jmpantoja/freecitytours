<?php

/**
 * This file is part of the planb-cms-project-skeleton project.
 *
 * (c) Jose Manuel Pantoja <jmpantoja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolt\Extension\PlanB\FreeCity\Controller;

use Bolt\Content;
use Bolt\Controller\Base;
use Bolt\Storage\Database\Schema\Table\ContentType;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * @TODO Description of Class ReserveController
 * @package Bolt\Extension\PlanB\FreeCity\Controller
 * @author  Jose Manuel Pantoja <jmpantoja@gmail.com>
 */
class FreeCityController extends Base
{

    /** @var Application */
//    protected $app;

    protected function addRoutes(ControllerCollection $c)
    {
    }

    public function home()
    {
        return $this->redirectToRoute('es-homepage');
    }

    public function reserve(Request $request)
    {
        $manager = $this->app['reserves.manager'];

        if ($request->isMethod('POST')) {
            $values = $request->request->all();
            $manager->create($values);

            $this->flashes()->success('form_success');

            $route = $request->get('_route');
            return $this->redirectToRoute($route);
        }

        $tomorrow = (new \DateTime())->modify('+1 day');

        return $this->app['render']->render('reserve.twig', [
            'tomorrow' => $manager->dateToArray($tomorrow),
            'disables' => $manager->getDisableDays(),
            'summer' => $this->app['config']->get('general/reserves/summer'),
            'messages' => [
                'success' => $this->flashes()->get('success'),
                'error' => $this->flashes()->get('error')
            ]
        ]);
    }

    public function places(Request $request)
    {
        $manager = $this->app['places.manager'];

        return $this->app['render']->render('places.twig', [
            'photos' => $manager->getPhotos()
        ]);
    }


}