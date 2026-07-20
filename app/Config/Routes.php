<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('client', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('connexion', 'ClientsController::index');
    $routes->post('login', 'ClientsController::login');
    $routes->get('compte', 'ClientsController::solde');
    $routes->get('operation', 'ClientsController::operation');
    $routes->post('operation', 'ClientsController::store');
    $routes->get('historique', 'ClientsController::historique');
    $routes->post('deconnexion', 'ClientsController::deconnexion');
});
// Operateur
$routes->group('operateur', function ($routes) {
    $routes->get('/', 'ConfigurationTransactionController::index');
    $routes->post('store', 'ConfigurationTransactionController::store');
    $routes->get('soldes', 'ConfigurationTransactionController::soldes');
    $routes->get('gains', 'ConfigurationTransactionController::gains');
});
