<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = new \Slim\App();

$app->post('/api/destinos/nuevo', function($request, $response, $args) {
    $nombreDestino = $request->getParam('nombreDestino');
    $descripcion = $request->getParam('descripcion');
    $pais = $request->getParam('pais');
    $paquetes = $request->getParam('paquetes'); // Array de IDs de paquetes

    $sql = "INSERT INTO `destinos` (nombreDestino, descripcion, pais) VALUES (:nombreDestino, :descripcion, :pais)";

    try {
        $db = new db();
        $db = $db->connectDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombreDestino', $nombreDestino);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':pais', $pais);
        $stmt->execute();

        $destino_id = $db->lastInsertId();

        // Asociar paquetes de viajes con el nuevo destino
        foreach ($paquetes as $pv_id) {
            $updateSql = "UPDATE `paquetesdeViajes` SET destino_id = :destino_id WHERE pv_id = :pv_id";
            $stmt = $db->prepare($updateSql);
            $stmt->bindParam(':destino_id', $destino_id);
            $stmt->bindParam(':pv_id', $pv_id);
            $stmt->execute();
        }

        return $response->withJson(['message' => 'Destino creado y asociado exitosamente']);
    } catch(PDOException $e) {
        return $response->withJson(['error' => $e->getMessage()]);
    }
});
