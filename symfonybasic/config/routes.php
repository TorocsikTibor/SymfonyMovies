<?php

use App\Controller\MovieController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('movies', '/')->controller([MovieController::class, 'showMovies']);
    $routes->add('movie', '/addmovie')->controller([MovieController::class, 'addMovie']);
    $routes->add('editmovie', 'editmovie/{id}')->controller([MovieController::class, 'editMovie'])->methods(['GET', 'POST']);
    $routes->add('deletemovie', 'deletemovie/{id}')->controller([MovieController::class, 'deleteMovie'])->methods(['GET','DELETE']);
};