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
        $lang = $this->getLang();
        $manager = $this->app['reserves.manager'];

        if ($request->isMethod('POST')) {
            $route = sprintf('%s-reserve-done', $lang);

            $values = $request->request->all();
            $reserve = $manager->create($values);

            if ($reserve instanceof Content) {
                $key = $reserve->get('uuid');
                return $this->redirectToRoute($route, ['key' => $key]);
            } else {
                $limit = $reserve;
                return $this->redirectToRoute($route, ['limit' => $limit]);
            }
        }

        $tomorrow = (new \DateTime())->modify('+1 day');

        return $this->app['render']->render('reserve.twig', [
            'tomorrow' => $manager->dateToArray($tomorrow),
            'disables' => $manager->getDisableDays(),
            'summer' => $this->app['config']->get('general/reserves/summer')
        ]);
    }

    public function confirmation(Request $request)
    {

        setlocale(LC_ALL, 'en_EN');

        if ($request->query->has('key')) {
            $key = $request->get('key');
            $reserve = $this->app['query']
                ->getContent('reserves', ['uuid' => $key])
                ->current();

            $params = ['reserve' => $reserve];
        } else {
            $limit = 0;
            if ($request->query->has('limit')) {
                $limit = $request->get('limit');
            }
            $params = ['limit' => $limit];
        }

        return $this->app['render']->render('confirmation.twig', $params);
    }


    public function places(Request $request)
    {
        $manager = $this->app['places.manager'];

        return $this->app['render']->render('places.twig', [
            'photos' => $manager->getPhotos()
        ]);
    }

    /**
     * @return array
     */
    private function getLang()
    {
        $pieces = explode('/', $this->app['paths']['current']);
        return $pieces[3];
    }


}