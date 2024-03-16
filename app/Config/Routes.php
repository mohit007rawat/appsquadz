<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Products::index');
$routes->get('products', 'Products::productList');
$routes->post('products/create', 'Products::productCreate');
$routes->get('products/edit/(:num)', 'Products::productEdit/$1');
$routes->post('products/update', 'Products::productUpdate');
$routes->get('products/delete/(:num)', 'Products::productDelete/$1');
$routes->get('products/save-all', 'Products::saveToDatabase');
