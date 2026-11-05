<?php    
    $objecto = json_decode(file_get_contents('php://input'));

    $pdo = new PDO("mysql:host=localhost;dbname=alumno;charset=utf8mb4","root","");
    $sql = "SELECT * FROM `alumno`";
    $resultado = $pdo->query($sql);

    header("Content-type:application/json");
    $objecto = new stdClass();
    $objecto->success=true;
    $objecto->datos = $resultado->fetchAll();
    echo json_encode($objecto);
?>