<?php
    $objecto = json_decode(file_get_contents('php://input'));

    echo $objecto->nombre;
?>