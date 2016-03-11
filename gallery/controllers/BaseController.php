<?php

namespace gallery\controllers;

abstract class BaseController
{
    abstract public function execute($arguments = []);

    private $twig;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../views');
        $this->twig = new \Twig_Environment($loader, [
            'cache' => false,
        ]);
    }

    public function render($view, $params = [])
    {
        return $this->twig->render($view, $params);
    }

}