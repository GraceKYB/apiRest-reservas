<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = new \Slim\App();

$app->get('/api/listar/paquetes', function(Request $request, Response $response, $args) {
    $sql = "SELECT p.pv_id, p.nombrePaquete, p.descripcion, p.precio, p.duracion, p.estado, 
                   d.nombreDestino, d.descripcion as destinoDescripcion, d.pais, 
                   a.actividad, a.descripcion as actividadDescripcion
            FROM paquetesdeviajes p
            LEFT JOIN destinos d ON p.pv_id = d.des_id
            LEFT JOIN actividadespaquetes a ON p.pv_id = a.pv_id";

    try {
        $db = new db();
        $db = $db->connectDB();
        $stmt = $db->query($sql);
        $paquetes = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($paquetes);
    } catch(PDOException $e) {
        return $response->withJson(['error' => $e->getMessage()], 500);
    }
});
