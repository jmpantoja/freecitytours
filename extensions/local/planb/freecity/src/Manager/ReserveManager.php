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

use Bolt\Content;
use Bolt\Storage\Entity\Entity;
use Ramsey\Uuid\Uuid;
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
        $values = $this->sanitize($data);

        $avaiable = $this->calculeAvaiable($values);

        if ($avaiable >= $values['persons']) {
            $reserve = $this->newReserve($values);

            $this->save($reserve);
            $this->sendMail($reserve);
            return $reserve;
        } else {
            return $avaiable;
        }

    }

    private function calculeAvaiable(array $data)
    {
        $maxOfReservesPerDay = $this->app['config']->get('general/reserves/max_per_day');

        $date = $data['date'];

        $builder = $this->app['storage']->createQueryBuilder();

        $builder
            ->from('bolt_reserves as content')
            ->select('SUM(content.persons) as total')
            ->where(sprintf('date = "%s"', $date));


        $row = $builder->execute()->fetch();
        $total = (int)$row['total'];

        return $maxOfReservesPerDay - $total;
    }

    private function sendMail($reserve)
    {

        $body = (string)$this->app['render']->render('mail.twig', ['reserve' => $reserve]);

        $from = $this->app['config']->get('general/reserves/mail/from');
        $to = $this->app['config']->get('general/reserves/mail/to');
        $subject = $this->app['config']->get('general/reserves/mail/subject');

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($from))
            ->setTo((array) $to)
            ->setBody($body, 'text/html');

        $this->app['mailer']->send($message);
    }

    /**
     * @param array $data
     * @return mixed
     */
    private function newReserve(array $values)
    {
        $storage = $this->app['storage.legacy'];

        $reserve = $storage->getEmptyContent('reserve');
        $reserve->setValues($values);
        return $reserve;
    }


    private function sanitize(array $data): array
    {

        $data['time'] = $data['time'] ?? '12:00';
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
            'comment' => $data['comment'] ?? '',
            'persons' => $data['persons'],
            'date' => $date->format('Y-m-d G:i:00'),
            'slug' => $slug,
            'uuid' => (Uuid::uuid4())->toString()
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


        $builder = $this->app['storage']->createQueryBuilder();

        $maxOfReservesPerDay = $this->app['config']->get('general/reserves/max_per_day');

        $builder
            ->from('bolt_reserves as A')
            ->select('SUM(A.persons) as total, A.date')
            ->groupBy('A.date')
            ->where('A.date > now()')
            ->having('total >= ' . $maxOfReservesPerDay);

        $rows = $builder->execute()->fetchAll();

        foreach ($rows as $row) {
            $date = $this->dateToArray(new \DateTime($row['date']));
            $ranges[] = [
                'from' => $date,
                'to' => $date
            ];
        }

        return $ranges;
    }

}
