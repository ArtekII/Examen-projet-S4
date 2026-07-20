<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Operateur
$routes->group('operateur', function ($routes) {
    $routes->get('/', 'ConfigurationTransactionController::index');
    $routes->post('store', 'ConfigurationTransactionController::store');
    $routes->get('soldes', 'ConfigurationTransactionController::soldes');
    $routes->get('gains', 'ConfigurationTransactionController::gains');
});