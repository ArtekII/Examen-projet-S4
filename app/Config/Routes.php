<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('client', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('connexion', 'ClientsController::index');
    $routes->post('login', 'ClientsController::login');
});
