<?php

// Define root directory
define('ROOTDIR', dirname(__DIR__));

// Router
require ROOTDIR . '/Router.php';
require ROOTDIR . '/App.php';

// Init router
$router = new Router();

// Routes
$router
    ->map('/', 'App->actionIndex')
    ->map('/api/shorten', 'App->actionShorten', 'POST')
    ->map('/<short>', 'App->actionRedirect', 'GET', array('short' => '[a-z]{1,7}'))
    ->notFound('App->routeNotFound')
    ->run();