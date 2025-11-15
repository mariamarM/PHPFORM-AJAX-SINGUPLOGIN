<?php
session_start();
header('Content-Type: application/json');

function enviarRespuesta($exito, $datos = null, $error = '') {
    echo json_encode([
        'ok' => $exito,
        'data' => $datos,
        'error' => $error
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $datos = json_decode(file_get_contents('php://input'), true);
        $email = $datos['email'] ?? '';
        $password = $datos['password'] ?? '';
        
        $usuarios = json_decode(file_get_contents('../storage/data.json'), true) ?? [];
        
        foreach ($usuarios as $usuario) {
            if ($usuario['email'] === $email && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'rol' => $usuario['rol']
                ];
                enviarRespuesta(true, ['mensaje' => 'Login exitoso']);
            }
        }
        enviarRespuesta(false, null, 'Credenciales incorrectas');
        break;
        
    case 'logout':
        session_destroy();
        enviarRespuesta(true, ['mensaje' => 'Sesión cerrada']);
        break;
        
    case 'me':
        if (isset($_SESSION['usuario'])) {
            enviarRespuesta(true, $_SESSION['usuario']);
        } else {
            enviarRespuesta(false, null, 'No autenticado');
        }
        break;
        
    default:
        enviarRespuesta(false, null, 'Acción no válida');
}