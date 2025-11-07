<?php
// 1) Cabecera y utilidades de respuesta JSON
header('Content-Type: application/json; charset=utf-8');

/**
 * Envía una respuesta de éxito (solo los datos sin "ok:true")
 *
 * @param mixed $contenidoDatos Datos a enviar directamente.
 * @param int $codigoHttp Código de estado HTTP (200 por defecto, 201 para create).
 */
function responder_json_exito(mixed $contenidoDatos, int $codigoHttp = 200): void
{
    http_response_code($codigoHttp);
    echo json_encode($contenidoDatos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Envía una respuesta de error (solo con la clave "error")
 *
 * @param string $mensajeError Mensaje de error legible para el cliente.
 * @param int $codigoHttp Código de estado HTTP (400 por defecto).
 */
function responder_json_error(string $mensajeError, int $codigoHttp = 400): void
{
    http_response_code($codigoHttp);
    echo json_encode(['error' => $mensajeError], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Comprueba si ya existe un usuario con el email dado (comparación exacta).
 * (Función añadida en la sección de mejoras)
 *
 * @param array $usuarios Lista actual en memoria.
 * @param string $emailNormalizado Email normalizado en minúsculas.
 */
function existeEmailDuplicado(array $usuarios, string $emailNormalizado): bool
{
    foreach ($usuarios as $u) {
        if (isset($u['email']) && is_string($u['email']) && mb_strtolower($u['email']) === $emailNormalizado) {
            return true;
        }
    }
    return false;
}


// 2) Ruta al archivo de persistencia (misma carpeta)
$rutaArchivoDatosJson = __DIR__ . '/data.json';

// 2.1) Si no existe, lo creamos con un array JSON vacío ([])
if (!file_exists($rutaArchivoDatosJson)) {
    file_put_contents($rutaArchivoDatosJson, json_encode([]) . "\n");
}

// 2.2) Cargar su contenido como array asociativo de PHP
$listaUsuarios = json_decode((string) file_get_contents($rutaArchivoDatosJson), true);

// 2.3) Si por cualquier motivo no es un array, lo normalizamos a []
if (!is_array($listaUsuarios)) {
    $listaUsuarios = [];
}

// 3) Método HTTP y acción (por querystring o formulario)
$metodoHttpRecibido = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$accionSolicitada = $_GET['action'] ?? $_POST['action'] ?? 'list';

// 4) LISTAR usuarios: GET /api.php?action=list
if ($metodoHttpRecibido === 'GET' && $accionSolicitada === 'list') {
    responder_json_exito($listaUsuarios); // 200 OK
}

// 5) CREAR usuario: POST /api.php?action=create (Versión mejorada)
if ($metodoHttpRecibido === 'POST' && $accionSolicitada === 'create') {
    $cuerpoBruto = (string) file_get_contents('php://input');
    $datosDecodificados = $cuerpoBruto !== '' ? (json_decode($cuerpoBruto, true) ?? []) : [];
    
    // Extraemos datos y normalizamos
    $nombreUsuarioNuevo = trim((string) ($datosDecodificados['nombre'] ?? $_POST['nombre'] ?? ''));
    $correoUsuarioNuevo = trim((string) ($datosDecodificados['email'] ?? $_POST['email'] ?? ''));
    $correoUsuarioNormalizado = mb_strtolower($correoUsuarioNuevo);

    // Validación mínima en servidor
    if ($nombreUsuarioNuevo === '' || $correoUsuarioNuevo === '') {
        responder_json_error('Los campos "nombre" y "email" son obligatorios.', 422);
    }
    if (!filter_var($correoUsuarioNuevo, FILTER_VALIDATE_EMAIL)) {
        responder_json_error('El campo "email" no tiene un formato válido.', 422);
    }
    // Límites razonables para este ejercicio
    if (mb_strlen($nombreUsuarioNuevo) > 60) {
        responder_json_error('El campo "nombre" excede los 60 caracteres.', 422);
    }
    if (mb_strlen($correoUsuarioNuevo) > 120) {
        responder_json_error('El campo "email" excede los 120 caracteres.', 422);
    }
    // Evitar duplicados por email
    if (existeEmailDuplicado($listaUsuarios, $correoUsuarioNormalizado)) {
        responder_json_error('Ya existe un usuario con ese email.', 409); // 409 Conflict
    }

    // Agregamos y persistimos (guardamos el email normalizado)
    $listaUsuarios[] = [
        'nombre' => $nombreUsuarioNuevo,
        'email' => $correoUsuarioNormalizado,
    ];
    
    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );
    
    responder_json_exito($listaUsuarios, 201); // 201 Created
}

// 6) ELIMINAR usuario: POST /api.php?action=delete
if (($metodoHttpRecibido === 'POST' || $metodoHttpRecibido === 'DELETE') && $accionSolicitada === 'delete') {
    
    // 6.1) Intentamos obtener el índice por distintos canales
    $indiceEnQuery = $_GET['index'] ?? null;
    if ($indiceEnQuery === null) {
        $cuerpoBruto = (string) file_get_contents('php://input');
        if ($cuerpoBruto !== '') {
            $datosDecodificados = json_decode($cuerpoBruto, true) ?? [];
            $indiceEnQuery = $datosDecodificados['index'] ?? null;
        } else {
            $indiceEnQuery = $_POST['index'] ?? null;
        }
    }

    // 6.2) Validaciones de existencia del parámetro
    if ($indiceEnQuery === null) {
        responder_json_error('Falta el parámetro "index" para eliminar.', 422);
    }
    
    $indiceUsuarioAEliminar = (int) $indiceEnQuery;
    
    if (!isset($listaUsuarios[$indiceUsuarioAEliminar])) {
        responder_json_error('El índice indicado no existe.', 404); // 404 Not Found
    }

    // 6.3) Eliminamos y reindexamos para mantener la continuidad
    unset($listaUsuarios[$indiceUsuarioAEliminar]);
    $listaUsuarios = array_values($listaUsuarios);

    // 6.4) Guardamos el nuevo estado en disco
    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );

    // 6.5) Devolvemos el listado actualizado
    responder_json_exito($listaUsuarios); // 200 OK
}
//lo de editar accion
if ($metodoHttpRecibido === 'POST' && $accionSolicitada === 'update'){
$cuerpoBruto = (string) file_get_contents('php://input');
    $datosDecodificados = json_decode($cuerpoBruto, true) ?? [];
    $indiceAEditar = $datosDecodificados['index'] ?? null;
    $nuevoNombre = trim($datosDecodificados['nombre'] ?? '');
    $nuevoEmail = trim($datosDecodificados['email'] ?? '');
if ($indiceAEditar === null) {
        responder_json_error('Falta el parámetro "index" para editar.', 422);
    }
    if (!isset($listaUsuarios[$indiceAEditar])) {
        responder_json_error('El índice indicado no existe.', 404);
    }
    if ($nuevoNombre === '' || $nuevoEmail === '') {
        responder_json_error('Los campos nombre y email son obligatorios.', 422);
    }
    $listaUsuarios[$indiceAEditar]['nombre'] = $nuevoNombre;
    $listaUsuarios[$indiceAEditar]['email'] = $nuevoEmail;
    file_put_contents(
        $rutaArchivoDatosJson,
        json_encode($listaUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n"
    );
    responder_json_exito($listaUsuarios);
}
// 7) Si llegamos aquí, la acción solicitada no está soportada
responder_json_error('Acción no soportada. Use list | create | delete', 400);