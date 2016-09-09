<?php

require 'vendor/autoload.php';
require_once dirname( __FILE__ ) . '/config.php';

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

function debug($data = []) {
    echo '<pre>'; print_r($data); echo '</pre>';
}

Eden::DECORATOR;
if ('127.0.0.1' == $_SERVER['REMOTE_ADDR']) {
    $db = eden('mysql', 'localhost' ,'skuuper_social', 'root');
    $base_uri = '/';
} else {
    $db = eden('mysql', 'd48295.mysql.zone.ee' ,'d48295sd117610', 'd48295sa111452', '3E7tDe6a');
    #$base_uri =  sprintf('/%s/', basename(__DIR__));
}

$base_uri = '/';

session_start();

$data = [];

$config['view'] = function ($container) {
    //TODO: Hack, remove
    global $data, $base_uri;

    $view = new \Slim\Views\Twig(dirname(__FILE__) . '/templates', [
        //'cache' => dirname(__FILE__) . '/tmp/cache',
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    
    $view->getEnvironment()->addGlobal('base_uri', $base_uri);
    $view->getEnvironment()->addGlobal('is_local', '127.0.0.1' == $_SERVER['REMOTE_ADDR']);
    $view->getEnvironment()->addGlobal('app', $data);
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('md5', 'md5'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('base64_encode', 'base64_encode'));
    $view->getEnvironment()->addFilter(new Twig_SimpleFilter('base64_decode', 'base64_decode'));


    return $view;
};

$config['settings'] = ['displayErrorDetails' => true, 'debug' => 0];

$container = new \Slim\Container($config);

$container['db'] = function($container) use($db) {
    return $db;
};

$container['data'] = function($container) use($data) {
    return $data;
};

$app = new \Slim\App($container);


$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => ['/admin', '/testers'],
    "secure" => false,
    "users" => [
        "skuuper" => "Translators82"
    ]
]));



$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        $uri = $uri->withPath(substr($path, 0, -1));
        return $response->withRedirect((string)$uri, 301);
    }
    return $next($request, $response);
});



require __DIR__ . '/app/routes.php';



$app->run();