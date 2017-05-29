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
 * @TODO Description of Class PlacesManager
 * @author  Jose Manuel Pantoja <jmpantoja@gmail.com>
 */
class PlacesManager
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
    }

    public function getPhotos()
    {
        $photos = [];

        for($i=1; $i<19; $i++){
            $num = sprintf('%02d', $i);
            $photos[] = [
                'image'=>"photo$num.jpg",
                'title'=>"este es el titulo $num",
                'description'=>"esta es la descripción $num"
            ];
        }

        return $photos;
    }


}