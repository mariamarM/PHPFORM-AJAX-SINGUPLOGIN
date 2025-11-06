<?php
declare(strict_types=1);
session_start();

include __DIR__ . "/../includes/functions.php";
// if (!isset($_SESSION['usuario'])) {
//     header("Location: login.php");
//     exit();
// }
$formulario_enviado = $_SESSION['formulario_enviado'] ?? false;
$error_envio = $_SESSION['error_envio'] ?? false;

if ($formulario_enviado) {
    unset($_SESSION['formulario_enviado']);
}
if ($error_envio) {
    unset($_SESSION['error_envio']);
}

if (!isset($_SESSION['respuestas'])) {
    $_SESSION['respuestas'] = [];
}
if (!isset($_SESSION['pagina_actual'])) {
    $_SESSION['pagina_actual'] = 1;
}

$jsonData = file_get_contents(__DIR__ . "/../data/preguntasForm.json");
$preguntas = json_decode($jsonData, true);
$totalPreguntas = count($preguntas);

$pagina = $_POST['pagina'] ?? $_SESSION['pagina_actual'];
$pagina = (int)$pagina;
$_SESSION['pagina_actual'] = $pagina;

$preguntasPorBloque = 7;
$inicio = ($pagina - 1) * $preguntasPorBloque;

$bloque1 = array_slice($preguntas, $inicio, 3);
$bloque2 = array_slice($preguntas, $inicio + 3, 4);

$totalPreguntas = count($preguntas);
$ultimaPagina = ($inicio + $preguntasPorBloque >= $totalPreguntas);

$old_field = $_SESSION['respuestas'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario Andreo PHP</title>
    <link rel="stylesheet" href="assets/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Numans&display=swap" rel="stylesheet">

</head>

<body class="<?= $formulario_enviado ? 'form-enviado' : '' ?>">
<h1>Sociometric Survey Form <?= $formulario_enviado ? '<span class="check-verde">✓</span>' : '' ?></h1>

<?php if ($formulario_enviado): ?>
    <div class="mensaje-exito">
        <strong>Send it!</strong> 
        <a href="index.php">Go back to start</a>
    </div>
        <?php  //esto lo tienes q poner porque te limpia la sesion entonces ahora te lo enseña
    unset($_SESSION['formulario_enviado']);
    unset($_SESSION['respuestas']);
    unset($_SESSION['pagina_actual']);
    ?>
<?php elseif ($error_envio): ?>
    <div class="mensaje-error">
        <strong>Error al enviar el forulari</strong> 
    </div>
<?php endif; ?>

<?php if (!$formulario_enviado): ?>
<form action="process.php" method="POST" enctype="multipart/form-data">

    <div class="formpartes">
        <?php foreach ($bloque1 as $p): ?>
            <?php
            $tipo = $p['type'];
            $nombre = $p['name'];
            $placeholder = $p['placeholder'] ?? '';
            $label = $p['label'] ?? ucfirst($nombre);
            $requerido = !empty($p['required']) ? 'required' : '';
            $valor_guardado = $old_field[$nombre] ?? '';
            ?>
            
            <?php if (in_array($tipo, ['text','password','color','date','file'])): ?>
                <label for="<?= $nombre ?>"><?= $label ?></label><br>
                <input type="<?= $tipo ?>" name="<?= $nombre ?>" id="<?= $nombre ?>"
                       placeholder="<?= $placeholder ?>" <?= $requerido ?>
                       value="<?= htmlspecialchars($valor_guardado) ?>">
                <br><br>

            <?php elseif ($tipo === 'textarea'): ?>
                <label for="<?= $nombre ?>"><?= $label ?></label><br>
                <textarea name="<?= $nombre ?>" id="<?= $nombre ?>" rows="4" cols="40"
                          placeholder="<?= $placeholder ?>" <?= $requerido ?>><?= htmlspecialchars($valor_guardado) ?></textarea>
                <br><br>

            <?php elseif ($tipo === 'select'): ?>
                <label for="<?= $nombre ?>"><?= $label ?></label><br>
                <select name="<?= $nombre ?>" id="<?= $nombre ?>" <?= $requerido ?>>
                    <option value="">Selecciona una opción</option>
                    <?php foreach ($p['options'] as $op): ?>
                        <option value="<?= $op['value'] ?>" 
                            <?= ($valor_guardado === $op['value']) ? 'selected' : '' ?>>
                            <?= $op['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="formpartes">
        <?php foreach ($bloque2 as $p): ?>
            <?php
            $tipo = $p['type'];
            $nombre = $p['name'];
            $placeholder = $p['placeholder'] ?? '';
            $label = $p['label'] ?? ucfirst($nombre);
            $requerido = !empty($p['required']) ? 'required' : '';
            $valor_guardado = $old_field[$nombre] ?? '';
            ?>
            
            <?php if ($tipo === 'radio'): ?>
                <p><?= $label ?>:</p>
                <?php foreach ($p['options'] as $op): ?>
                    <label>
                        <input type="radio" name="<?= $nombre ?>"
                               value="<?= $op['value'] ?>"
                               <?= ($valor_guardado === $op['value']) ? 'checked' : '' ?>> 
                               <?= $op['label'] ?>
                    </label><br>
                <?php endforeach; ?>
                <br><br>

            <?php elseif ($tipo === 'checkbox'): ?>
                <p><?= $label ?>:</p>
                <?php 
                $valores_guardados = is_array($valor_guardado) ? $valor_guardado : [];
                ?>
                <?php foreach ($p['options'] as $op): ?>
                    <label>
                        <input type="checkbox" name="<?= $nombre ?>[]"
                               value="<?= $op['value'] ?>"
                               <?= in_array($op['value'], $valores_guardados) ? 'checked' : '' ?>> 
                               <?= $op['label'] ?>
                    </label><br>
                <?php endforeach; ?>
                <br><br>

            <?php elseif ($tipo === 'range'): ?>
                <label for="<?= $nombre ?>"><?= $label ?></label><br>
                <input type="range" name="<?= $nombre ?>" id="<?= $nombre ?>"
                       min="<?= $p['min'] ?>" max="<?= $p['max'] ?>" step="<?= $p['step'] ?>"
                       value="<?= $valor_guardado ?>">
                <br><br>

            <?php elseif (in_array($tipo, ['text','password','color','date','file'])): ?>
                <label for="<?= $nombre ?>"><?= $label ?></label><br>
                <input type="<?= $tipo ?>" name="<?= $nombre ?>" id="<?= $nombre ?>"
                       placeholder="<?= $placeholder ?>" <?= $requerido ?>
                       value="<?= htmlspecialchars($valor_guardado) ?>">
                <br><br>
            <?php endif; ?>
        <?php endforeach; ?>

        <input type="hidden" name="pagina" value="<?= $pagina ?>">
        <button type="submit"><?= $ultimaPagina ? "Enviar" : "Siguiente" ?></button>
    </div>
</form>
<?php endif; ?>

<div class="formpartesHora">
    <div class="reloj">
        <p id="hora" class="hora"></p><p>:</p>
        <p id="minutos" class="minutos"></p><p>:</p>
        <div class="caja-segundos">
            <p id="ampm" class="ampm"></p>
            <p id="segundos" class="segundos"></p>
        </div>
    </div>
</div>

<div class="formpartesRespuestas">
    <div class="verOtros">
        <p>See others responses as a JSON format</p>
        <button id="verRespuestas" onclick="window.location.href='./auth/api.php'">View</button>
    </div>
</div>
<div class="formpartesUsers">
    <div class="verOtrosUsers">
        <p>See others Users</p>
        <button id="verRespuestas" onclick="window.location.href='./index_ajax.html'">View</button>
    </div>
</div>
<script src="assets/script.js"></script>
</body>
</html>