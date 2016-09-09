<?php

$app->get('/', 'App\Controller\DocumentsController:index');
$app->post('/', 'App\Controller\DocumentsController:process');
$app->post('/process_file', 'App\Controller\DocumentsController:process_file');
$app->get('/download/{filename}', 'App\Controller\DocumentsController:download');
$app->get('/test', 'App\Controller\DocumentsController:test');
