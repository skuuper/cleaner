<?php

$app->get('/', 'App\Controller\TmxController:index');

$app->get('/manuals', 'App\Controller\StaticController:index');

$app->get('/documents', 'App\Controller\DocumentsController:index');
$app->post('/documents', 'App\Controller\DocumentsController:process');
$app->post('/process_file', 'App\Controller\DocumentsController:process_file');
$app->get('/download/{filename}', 'App\Controller\DocumentsController:download');
$app->get('/test', 'App\Controller\DocumentsController:test');
$app->get('/login', 'App\Controller\BaseController:login');


$app->get('/tmx', 'App\Controller\TmxController:index');
$app->get('/tmx/languages', 'App\Controller\DicController:index');
$app->post('/tmx/process', 'App\Controller\TmxController:process');
$app->get('/tmx/download/{filename}', 'App\Controller\TmxController:download');
$app->get('/tmx/align', 'App\Controller\TmxController:align');
$app->get('/tmx/test', 'App\Controller\TmxController:test');


$app->get('/aligner', 'App\Controller\AlignerController:index');
$app->post('/aligner/process', 'App\Controller\AlignerController:process');
$app->get('/aligner/align', 'App\Controller\AlignerController:align');
$app->get('/aligner/get_chunks', 'App\Controller\AlignerController:get_chunks');
$app->post('/aligner/save_chunks', 'App\Controller\AlignerController:save_chunks');