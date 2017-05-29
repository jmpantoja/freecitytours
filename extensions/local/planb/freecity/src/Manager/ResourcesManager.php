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


/**
 * @TODO Description of Class ImageManager
 * @package Bolt\Extension\PlanB\FreeCity\Manager
 * @author  Jose Manuel Pantoja <jmpantoja@gmail.com>
 */
class ResourcesManager
{
    const MAX_WIDTH = 1680;
    /**
     * @var Application
     */
    protected $app;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
    }

    public function imageResponsive($path, $with, $height = null, $title = null)
    {
        $src = $this->getSrc($path, $with, $height);

        $sets = [];
        foreach ([1680, 1280, 960, 840, 736] as $breakpoint) {
            $dimW = $this->transform($with, $breakpoint);
            $dimH = $this->transform($height, $breakpoint);
            $sets[] = $this->getSrcSet($path, $dimW, $dimH, $breakpoint);
        }

        $htmlAttribs = $this->getHtmlAttribs([
            'sizes' => '100vw',
            'src' => $src,
            'srcset' => implode(', ', $sets),
            'title' => $title
        ]);

        return sprintf('<img %s/>', $htmlAttribs);
    }

    private function transform($measure, $breakpoint)
    {
        if (is_null($measure)) {
            return null;
        }

        $max = self::MAX_WIDTH;
        if ($measure > $max) {
            $measure = $max;
        }

        $ratio = $breakpoint / $max;
        return floor($measure * $ratio);

    }

    private function getSrc($path, $with, $height)
    {
        $handler = $this->app['twig.handlers']['image'];
        return $handler->thumbnail($path, $with, $height);
    }

    private function getSrcSet($path, $with, $height, $breackpoint)
    {
        $src = $this->getSrc($path, $with, $height);
        return sprintf('%s %sw', $src, $breackpoint);
    }

    private function getHtmlAttribs($attrs = array())
    {
        $html = [];
        foreach ($attrs as $name => $value) {
            if (!empty($name) and !empty($value)) {
                $html[] = sprintf('%s="%s"', $name, $value);
            }
        }
        return implode(' ', $html);
    }

    public function bundler($resource){

        list($folder, $key) = explode('/', $resource);

        $url = null;
        $theme = $this->app['config']->get('general/theme');
        $dir = realpath($this->app['paths']['config'].'/../../bundler/build/');
        $path = sprintf('%s/%s/%s', $dir, $theme, $key);
        $manifest = json_decode(file_get_contents($path), true);

        if(isset($manifest[$key])){
            $name = $manifest[$key];
            $url = sprintf('%s%s/%s', $this->app['paths']['theme'], $folder, $name);
        }

        return $url;
    }
}

