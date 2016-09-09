<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12.05.2016
 * Time: 13:01
 */


namespace App\Controller;

class BaseController {

    protected $container;
    protected $view;
    protected $db;


    public function __construct(\Interop\Container\ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->view;
        $this->db = $this->container->db;

        $this->data = $this->container->data;
    }
}