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

// RUTA CORREGIDA - storage está al mismo nivel que auth
function obtenerRutaDataJson() {
    // Para: src/auth/api.php → ../storage/data.json
    $ruta = __DIR__ . '/../storage/data.json';
    
    $directorio = dirname($ruta);
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    return $ruta;
}

function obtenerUsuarios() {
    $archivo = obtenerRutaDataJson();
    
    if (!file_exists($archivo)) {
        file_put_contents($archivo, json_encode([]));
        return [];
    }
    
    $contenido = file_get_contents($archivo);
    if (empty($contenido)) {
        return [];
    }
    
    $usuarios = json_decode($contenido, true);
    return is_array($usuarios) ? $usuarios : [];
}

function guardarUsuarios($usuarios) {
    $archivo = obtenerRutaDataJson();
    $resultado = file_put_contents($archivo, json_encode($usuarios, JSON_PRETTY_PRINT));
    
    if ($resultado === false) {
        throw new Exception('No se pudo escribir en el archivo: ' . $archivo);
    }
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            $usuarios = obtenerUsuarios();
            enviarRespuesta(true, $usuarios);
            break;
            
        case 'create':
            $input = file_get_contents('php://input');
            $datos = json_decode($input, true);
            
            if (!$datos) {
                enviarRespuesta(false, null, 'Datos JSON inválidos');
            }
            
            if (empty($datos['nombre']) || empty($datos['email'])) {
                enviarRespuesta(false, null, 'Nombre y Email son obligatorios');
            }
            
            $usuarios = obtenerUsuarios();
            
            foreach ($usuarios as $usuario) {
                if ($usuario['email'] === $datos['email']) {
                    enviarRespuesta(false, null, 'El email ya está registrado');
                }
            }
            
            $nuevoId = 1;
            if (!empty($usuarios)) {
                $ids = array_column($usuarios, 'id');
                $nuevoId = max($ids) + 1;
            }
            
            $nuevoUsuario = [
                'id' => $nuevoId,
                'nombre' => trim($datos['nombre']),
                'email' => trim($datos['email']),
                'password' => password_hash($datos['password'] ?? 'password123', PASSWORD_DEFAULT),
                'rol' => $datos['rol'] ?? 'usuario'
            ];
            
            $usuarios[] = $nuevoUsuario;
            guardarUsuarios($usuarios);
            
            enviarRespuesta(true, $usuarios, 'Usuario creado correctamente');
            break;
            
        case 'update':
            $input = file_get_contents('php://input');
            $datos = json_decode($input, true);
            
            if (!$datos || empty($datos['id'])) {
                enviarRespuesta(false, null, 'Datos inválidos');
            }
            
            $usuarios = obtenerUsuarios();
            $encontrado = false;
            
            foreach ($usuarios as &$usuario) {
                if ($usuario['id'] == $datos['id']) {
                    $usuario['nombre'] = $datos['nombre'] ?? $usuario['nombre'];
                    $usuario['email'] = $datos['email'] ?? $usuario['email'];
                    $usuario['rol'] = $datos['rol'] ?? $usuario['rol'];
                    
                    if (!empty($datos['password'])) {
                        $usuario['password'] = password_hash($datos['password'], PASSWORD_DEFAULT);
                    }
                    
                    $encontrado = true;
                    break;
                }
            }
            
            if (!$encontrado) {
                enviarRespuesta(false, null, 'Usuario no encontrado');
            }
            
            guardarUsuarios($usuarios);
            enviarRespuesta(true, $usuarios, 'Usuario actualizado correctamente');
            break;
            
        case 'delete':
            $input = file_get_contents('php://input');
            $datos = json_decode($input, true);
            
            if (!$datos || empty($datos['id'])) {
                enviarRespuesta(false, null, 'ID de usuario requerido');
            }
            
            $usuarios = obtenerUsuarios();
            $totalAntes = count($usuarios);
            $usuariosFiltrados = array_filter($usuarios, function($usuario) use ($datos) {
                return $usuario['id'] != $datos['id'];
            });
            
            if (count($usuariosFiltrados) === $totalAntes) {
                enviarRespuesta(false, null, 'Usuario no encontrado');
            }
            
            guardarUsuarios(array_values($usuariosFiltrados));
            enviarRespuesta(true, array_values($usuariosFiltrados), 'Usuario eliminado correctamente');
            break;
            
        default:
            enviarRespuesta(false, null, 'Acción no válida: ' . $action);
    }
} catch (Exception $e) {
    error_log('Error en api.php: ' . $e->getMessage());
    enviarRespuesta(false, null, 'Error del servidor: ' . $e->getMessage());
}
?>