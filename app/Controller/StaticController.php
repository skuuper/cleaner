<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12.05.2016
 * Time: 13:01
 */


namespace App\Controller;

class StaticController extends BaseController {

    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        parent::__construct($container);
    }


    public function index($request, $response) {
        return $this->view->render($response, 'static/index.twig');
    }
    
}