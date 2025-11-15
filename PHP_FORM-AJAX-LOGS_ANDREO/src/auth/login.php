<?php
session_start();

if (isset($_SESSION['admin'])) {
    if ($_SESSION['admin']['rol'] === 'admin') {
        header('Location: index_ajax.html');
    } else {
        header('Location: index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // DEPURACIÓN: Verificar que el archivo se lee correctamente
    $archivo = '../storage/data.json';
    if (!file_exists($archivo)) {
        $error = 'Archivo data.json no encontrado';
    } else {
        $contenido = file_get_contents($archivo);
        $usuarios = json_decode($contenido, true) ?? [];
        
        // DEPURACIÓN: Mostrar usuarios para debug
        error_log("Usuarios en data.json: " . print_r($usuarios, true));
        
        $usuarioEncontrado = false;
        foreach ($usuarios as $usuario) {
            error_log("Comparando: " . $email . " con " . $usuario['email']);
            
            if ($usuario['email'] === $email) {
                $usuarioEncontrado = true;
                error_log("Usuario encontrado: " . $usuario['email']);
                error_log("Hash en DB: " . $usuario['password']);
                error_log("Password proporcionado: " . $password);
                
                // Verificar la contraseña
                if (password_verify($password, $usuario['password'])) {
                    error_log("Contraseña VERIFICADA");
                    $_SESSION['admin'] = [
                        'id' => $usuario['id'],
                        'nombre' => $usuario['nombre'],
                        'email' => $usuario['email'],
                        'rol' => $usuario['rol']
                    ];
                    
                    if ($usuario['rol'] === 'admin') {
                        header('Location: ./index_ajax.html');
                    } else {
                        header('Location: ./index.php');
                    }
                    exit;
                } else {
                    error_log("Contraseña INCORRECTA");
                    $error = 'Contraseña incorrecta';
                }
                break;
            }
        }
        
        if (!$usuarioEncontrado) {
            $error = 'Usuario no encontrado';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mini CRUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            850: '#1f1f1f',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .error {
            color: #ef4444;
            background: #fed7d7;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 min-h-screen flex items-center justify-center p-6">

    <button id="themeToggle" class="absolute top-4 right-4 px-4 py-2 text-sm font-medium rounded-lg bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition">
        Cambiar tema
    </button>

    <div class="w-full max-w-md bg-white dark:bg-gray-850 p-8 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Iniciar Sesión</h1>
            <p class="text-gray-600 dark:text-gray-400">Accede a tu cuenta</p>
        </div>

        <?php if ($error): ?>
            <div class="error text-center mb-6"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Email</label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="hola@monlau.com" 
                    required
                    value="hola@monlau.com"
                    class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900 dark:text-white"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Introduce la contraseña" 
                    required
                    class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-gray-900 dark:text-white"
                >
            </div>

            <button 
                type="submit" 
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 transform hover:scale-105"
            >
                Entrar
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600 dark:text-gray-400">
                ¿No tienes cuenta? 
                <a href="singup.php" class="text-blue-600 hover:text-blue-700 font-semibold transition">Regístrate aquí</a>
            </p>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        if (localStorage.theme === 'dark') html.classList.add('dark');
        else if (localStorage.theme === 'light') html.classList.remove('dark');

        toggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });

        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('ring-2', 'ring-blue-200', 'dark:ring-blue-800');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('ring-2', 'ring-blue-200', 'dark:ring-blue-800');
            });
        });
    </script>
</body>
</html>