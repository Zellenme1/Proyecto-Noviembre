<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::salir();
}
header('Location: login.php');
exit;