<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    
    $stmt = $conn->prepare("
        SELECT id, username, password_hash
        FROM users
        WHERE email = ?
    ");
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
    
        header("Location: index2.php");
        exit;
    } else {
        $error = "Hibás email cím vagy jelszó.";
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés - HardverApró</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-indigo-950 to-purple-950">
    <div class="max-w-5xl w-full bg-gray-900 rounded-3xl shadow-2xl overflow-hidden flex">
        
        <!-- Bal oldal -->
        <div class="hidden lg:flex w-5/12 bg-cover bg-center relative" 
             style="background-image: url('https://images.unsplash.com/photo-1591799264318-7e6ef8f0e8e8?ixlib=rb-4.0.3&auto=format&fit=crop&q=80');">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
            <div class="absolute bottom-10 left-10 text-white">
                <h1 class="text-4xl font-bold">HardverApró</h1>
                <p class="text-xl mt-2">Üdv újra itt!</p>
            </div>
        </div>

        <!-- Jobb oldal -->
        <div class="w-full lg:w-7/12 p-8 md:p-12">
            <div class="max-w-md mx-auto">
                <h2 class="text-3xl font-bold text-white mb-2">Bejelentkezés</h2>
                <p class="text-gray-400 mb-8">Jelentkezz be a fiókodba</p>

                <?php if(isset($error)): ?>
                    <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-xl mb-6">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
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
                        Bejelentkezés
                    </button>
                </form>

                <p class="text-center text-gray-500 mt-8">
                    Nincs még fiókod? <a href="register.php" class="text-purple-400 hover:underline">Regisztrálj itt</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html> 