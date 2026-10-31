<?php
declare(strict_types=1);
require_once __DIR__ .'/include/conexion.php';
$conexion=new ConexionBD();
$db = $conexion->conectar();
if($db instanceof PDO){
    echo "<p>Conexion Establecida</p>";
}
?>
