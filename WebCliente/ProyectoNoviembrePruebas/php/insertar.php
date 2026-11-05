<?php
    $objecto = json_decode(file_get_contents('php://input'));

    $pdo = new PDO("mysql:host=localhost;dbname=alumno;charset=utf8mb4","root","");
    $insert = "INSERT INTO `alumno` (`id`, `nombre`) VALUES (NULL, :nombre)";
    $stmt = $pdo->prepare($insert);
    $resultado = $stmt->execute(['nombre' => $objecto->apellidos . ", " . $objecto->nombre]);

    header("Content-type:application/json");
    $objecto = new stdClass();
    $objecto->success = $resultado;
    $objecto->id = $pdo->lastInsertId();
    echo json_encode($objecto);

?>