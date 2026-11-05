<?php    
    $id=$_GET["id"];

    $pdo = new PDO("mysql:host=localhost;dbname=alumno;charset=utf8mb4","root","");
    $sql = "DELETE FROM `alumno` WHERE `id` = :id";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute(['id' => $id]);

    header("Content-type:application/json");
    $objecto = new stdClass();
    $objecto->success = $resultado;
    echo json_encode($objecto);
?>