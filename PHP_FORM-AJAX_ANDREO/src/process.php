<?php
session_start();
//las sesiones hacen la constanvia porque el php recarga entonces eso se pìerde con el sesion n ooo
require(__DIR__ . '/includes/functions.php');

$jsonData = file_get_contents(__DIR__ . '/data/preguntasForm.json');
$preguntas = json_decode($jsonData, true);

if (!$preguntas) {
    die("Error: no se pudieron cargar las preguntas desde JSON.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$pagina_actual = $_POST['pagina'] ?? 1;
$pagina_actual = (int)$pagina_actual;

$preguntasPorBloque = 7;
$inicio = ($pagina_actual - 1) * $preguntasPorBloque;
$preguntas_pagina = array_slice($preguntas, $inicio, $preguntasPorBloque);

foreach ($preguntas_pagina as $p) {
    $name = $p['name'];
    if (isset($_POST[$name])) {
        $_SESSION['respuestas'][$name] = $_POST[$name];
    }
}

$totalPreguntas = count($preguntas);
$hay_mas_paginas = ($inicio + $preguntasPorBloque) < $totalPreguntas;

if ($hay_mas_paginas) {
    $_SESSION['pagina_actual'] = $pagina_actual + 1;
    header("Location: index.php");
    exit;
} else {
    $archivo = 'data/sociograma.json';
    $todo = load_json($archivo);
    
    $registro = $_SESSION['respuestas'];
    $registro['fecha'] = date('Y-m-d H:i:s');
    $todo[] = $registro;
    
    if (save_json($archivo, $todo)) {
        unset($_SESSION['respuestas']);
        unset($_SESSION['pagina_actual']);
        $_SESSION['formulario_enviado'] = true;
               header("Location: index.php?success=1");
exit;
    } else {
         $_SESSION['error_envio'] = true;
        header("Location: index.php");
    }
    exit;
}