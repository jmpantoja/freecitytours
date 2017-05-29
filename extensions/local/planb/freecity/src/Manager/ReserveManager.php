<?php

/**
 * This file is part of the planb-cms-project-skeleton project.
 *
 * (c) Jose Manuel Pantoja <jmpantoja@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bolt\Extension\PlanB\FreeCity\Manager;

use Silex\Application;
use Sirius\Validation\Rule\DateTime;


/**
 * @TODO Description of Class ReserveManager
 * @author  Jose Manuel Pantoja <jmpantoja@gmail.com>
 */
class ReserveManager
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
    }

    public function create(array $data)
    {
        dump($data);
        die();
        $reserve = $this->newReserve($data);
        $this->save($reserve);
    }


    private function sendMail(array $data){
        $body = $this->app->render('mail.twig', $data);

        die();
        $mailer = $this->app['mailer'];

        $message = \Swift_Message::newInstance()
            ->setSubject('Nueva reserva')
            ->setFrom(array('reservas@jerezfreecitytours.com'))
            ->setTo(array('000.micorreo@gmail.com'))
            ->setBody('<b>asdadad</b>', 'text/html');

        $response = $this->app['mailer']->send($message);

        dump($response);

        die('x');
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function newReserve(array $data)
    {
        $storage = $this->app['storage.legacy'];
        $values = $this->sanitize($data);

        $reserve = $storage->getEmptyContent('reserve');
        $reserve->setValues($values);
        return $reserve;
    }


    private function sanitize(array $data): array
    {
        if (isset($data['date_submit'])) {
            $date = new \DateTime($data['date_submit']);
        } else {
            $date = new \DateTime();
            $date->setTimestamp(strtotime($data['date']));
        }

        $pieces = explode(':', $data['time']);
        $date->setTime($pieces[0], $pieces[1]);

        $dateString = $date->format('d/M/Y G:i:a');

        $slug = sprintf('%s (%s)', $data['name'], $dateString);

        $values = [
            'title' => $slug,
            'name' => $data['name'],
            'contact' => $data['contact'],
            'date' => $date->format('Y-m-d G:i:a'),
            'slug' => $slug
        ];
        return $values;
    }

    /**
     * @param $reserve
     */
    private function save($reserve): void
    {
        $storage = $this->app['storage.legacy'];
        $storage->saveContent($reserve);
    }



    public function dateToArray(\DateTime $date)
    {
        return [
            (int)$date->format('Y'),
            (int)$date->format('m') - 1,
            (int)$date->format('d')
        ];
    }

    public function getDisableDays()
    {
        $ranges = [];
        $currentYear = (int)(new \DateTime())->format('Y');

        $config = $this->app['config']->get('general/reserves/disable');
        $from = $config['from'];
        $to = $config['to'];

        for ($i = 0; $i < 20; $i++) {

            $year = $currentYear + $i;

            $firstDay = new \DateTime(sprintf('%s/%s/%s', $year, $from['m'], $from['d']));
            $lastDay = new \DateTime(sprintf('%s/%s/%s', $year, $to['m'], $to['d']));

            if ((int)$lastDay->format('m') < (int)$firstDay->format('m')) {
                $lastDay->modify('+1 year');
            }

            if ((int)$lastDay->format('m') == 3
                AND (int)$lastDay->format('d') == 1
            ) {
                $lastDay->modify('-1 day');
            }

            $ranges[] = [
                'from' => $this->dateToArray($firstDay),
                'to' => $this->dateToArray($lastDay)
            ];
        }

        return $ranges;
    }

}