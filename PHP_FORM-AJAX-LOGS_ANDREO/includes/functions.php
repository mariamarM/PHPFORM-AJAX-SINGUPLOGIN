<?php
// function ValidacionCamposVacios($campo)
// {
//     if (!empty($_POST["nombre"]) && isset($_POST["nombre"])) {
//         return "<div class='warning'> Tienes que rellenar todos los campos </div>";
//     }
// }
if (!function_exists('load_json')) {
    function load_json(string $path) {
        if (!file_exists($path)) return [];
        $raw = file_get_contents($path);
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('save_json')) {
    function save_json(string $path, array $data): bool {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($path, $json) !== false;
    }
}

if (!function_exists('old_field')) {
    function old_field($name, $source = []) {
        return $source[$name] ?? '';
    }
}

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('field_error')) {
    function field_error($name, $errors = []) {
        return $errors[$name] ?? '';
    }
}

?>