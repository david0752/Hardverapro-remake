<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
        exit;
    } else {
        $error = "Hiba történt a regisztráció során. Lehet, hogy a felhasználónév vagy email már foglalt.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - HardverApró</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-5xl w-full bg-gray-900 rounded-3xl shadow-2xl overflow-hidden flex">
        
        <!-- Bal oldal -->
        <div class="hidden lg:flex w-5/12 bg-cover bg-center relative" 
             style="background-image: url('https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-4.0.3&auto=format&fit=crop&q=80');">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
            <div class="absolute bottom-10 left-10 text-white">
                <h1 class="text-4xl font-bold">HardverApró</h1>
                <p class="text-xl mt-2">Találd meg a tökéletes alkatrészt</p>
            </div>
        </div>

        <!-- Jobb oldal -->
        <div class="w-full lg:w-7/12 p-8 md:p-12">
            <div class="max-w-md mx-auto">
                <h2 class="text-3xl font-bold text-white mb-2">Regisztráció</h2>
                <p class="text-gray-400 mb-8">Hozd létre az új fiókodat</p>

                <?php if(isset($error)): ?>
                    <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Felhasználónév</label>
                        <input type="text" name="username" required
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-purple-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Email cím</label>
                        <input type="email" name="email" required
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-purple-500 transition">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Jelszó</label>
                        <input type="password" name="password" required
                               class="w-full bg-gray-800 border border-gray-700 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-purple-500 transition">
                    </div>

                    <button type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 py-4 rounded-2xl text-white font-semibold text-lg transition">
                        Regisztráció
                    </button>
                </form>

                <p class="text-center text-gray-500 mt-8">
                    Már van fiókod? <a href="login.php" class="text-purple-400 hover:underline">Bejelentkezés</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>