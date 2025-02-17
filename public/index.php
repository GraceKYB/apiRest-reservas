<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require '../vendor/autoload.php';
require '../src/config/db.php';


$app = new \Slim\App();


require '../src/rutas/paquetes.php';
require '../src/rutas/destinos.php';
require '../src/rutas/reservas.php';


// Run app
$app->run();

?>