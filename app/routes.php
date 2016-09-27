<?php

$app->get('/', 'App\Controller\DocumentsController:index');
$app->post('/', 'App\Controller\DocumentsController:process');
$app->post('/process_file', 'App\Controller\DocumentsController:process_file');
$app->get('/download/{filename}', 'App\Controller\DocumentsController:download');
$app->get('/test', 'App\Controller\DocumentsController:test');


$app->get('/tmx', 'App\Controller\TmxController:index');
$app->post('/tmx/process', 'App\Controller\TmxController:process');
$app->get('/tmx/download/{filename}', 'App\Controller\TmxController:download');
$app->get('/tmx/align', 'App\Controller\TmxController:align');
$app->get('/tmx/test', 'App\Controller\TmxController:test');
$app->get('/tmx/get_chunks', 'App\Controller\TmxController:get_chunks');
$app->post('/tmx/save_chunks', 'App\Controller\TmxController:save_chunks');