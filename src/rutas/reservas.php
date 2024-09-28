<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app = new \Slim\App();

$app->post('/api/reservas/nueva', function($request, $response, $args) {
    $pv_id = $request->getParam('pv_id');
    $usu_id = $request->getParam('usu_id');
    $cantidadPersonas = $request->getParam('cantidadPersonas');

    $sql = "INSERT INTO `reservas` (pv_id, usu_id, fechaReserva, estado, cantidadPersonas) 
            VALUES (:pv_id, :usu_id, CURDATE(), 'pendiente', :cantidadPersonas)";

    try {
        // Verificar si el paquete está disponible
        $db = new db();
        $db = $db->connectDB();
        
        $checkSql = "SELECT COUNT(*) AS available FROM `paquetesdeViajes` WHERE pv_id = :pv_id";
        $stmt = $db->prepare($checkSql);
        $stmt->bindParam(':pv_id', $pv_id);
        $stmt->execute();
        $available = $stmt->fetch(PDO::FETCH_OBJ)->available;

        if ($available > 0) {
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':pv_id', $pv_id);
            $stmt->bindParam(':usu_id', $usu_id);
            $stmt->bindParam(':cantidadPersonas', $cantidadPersonas);
            $stmt->execute();
            return $response->withJson(['message' => 'Reserva creada exitosamente']);
        } else {
            return $response->withJson(['error' => 'Paquete no disponible']);
        }

        $db = null;
    } catch(PDOException $e) {
        return $response->withJson(['error' => $e->getMessage()]);
    }
});

$app->put('/api/reservas/actualizar/{id}', function($request, $response, $args) {
    $id = $args['id'];
    $estado = $request->getParam('estado');

    $sql = "UPDATE `reservas` SET estado = :estado WHERE res_id = :id";

    try {
        $db = new db();
        $db = $db->connectDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
        return $response->withJson(['message' => 'Reserva actualizada exitosamente']);
    } catch(PDOException $e) {
        return $response->withJson(['error' => $e->getMessage()]);
    }
});

$app->get('/api/reservas/usuario/{id}', function($request, $response, $args) {
    $id = $args['id'];
    $sql = "SELECT r.res_id, r.fechaReserva, r.estado, r.cantidadPersonas, p.nombrePaquete, p.descripcion, p.precio, p.duracion 
            FROM `reservas` r
            INNER JOIN `paquetesdeViajes` p ON r.pv_id = p.pv_id
            WHERE r.usu_id = :id";

    try {
        $db = new db();
        $db = $db->connectDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $reservas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $response->withJson($reservas);
    } catch(PDOException $e) {
        return $response->withJson(['error' => $e->getMessage()]);
    }
});

