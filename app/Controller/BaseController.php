<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 12.05.2016
 * Time: 13:01
 */


namespace App\Controller;

use Interop\Container\ContainerInterface;

class BaseController {

    protected $debug;
    protected $container;
    protected $view;
    protected $db;

    protected $dl_path = './downloads/';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->view = $container->view;
        $this->db = $this->container->db;

        $this->data = $this->container->data;
        $this->debug = $container->get('settings')['debug'];
    }

    public function login($request, $response) {
        return $this->view->render($response, 'layouts/login.twig');
    }

    protected function getLanguages() {
        return [
            'en' => 'English',
            'et' => 'Estonian',
            'fr' => 'French',
            'de' => 'German',
            'ru' => 'Russian',
            'es' => 'Spanish',
            'zh' => 'Chinese',
            'fi' => 'Finnish'
        ];
    }
}