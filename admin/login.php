<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if( isset($_SESSION['admin_id']) ) {
    header('Location: index.php');
    exit;
}

$errors = [];

if( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if( $username === '' ) {
        $errors[] ='Username wajib diisi.';
    }

    if( $password === '' ) {
        $errors[] = 'Password wajib diisi.';
    }

    if( empty($errors) ) {
        $usernameEscaped = $conn->real_escape_string($username);

        $sql = "SELECT id, username, password_hash, nama FROM admins WHERE username = '$usernameEscaped' LIMIT 1";
        $result =$conn->query($sql);

        if( $result && $result->num_rows === 1 ) {
            $admin = $result->fetch_assoc();

            if( password_verify($password, $admin['password_hash']) ) {
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nama'] = $admin['nama'];

                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Password salah.';
            }
        } else {
            $errors[] = 'Username salah.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="../src/output.css">

</head>
<body class="flex items-center justify-center min-h-screen bg-slate-100">

    <div class="w-full max-w-md p-6 bg-white shadow-lg rounded-xl">
        <div class="flex items-center justify-center">
            <img src="../assets/images/logo.webp" alt="Logo SD Musapuga" class="w-[120px] h-[120px]">
        </div>
        
        <h1 class="mb-1 text-2xl font-semibold text-center text-slate-800">Login Admin</h1>
        <p class="mb-6 text-sm text-center text-slate-500">Silahkan masukkan Username dan Password untuk masuk ke halaman admin.</p>

        <?php if( !empty($errors) ) : ?>
            <div class="px-3 py-2 mb-4 text-sm border rounded-lg border-rose-200 bg-rose-50 text-rose-700">
                <ul class="list-disc list-inside">
                    <?php foreach($errors as $err) : ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="space-y-5">
            <div>
                <label for="username" class="block mb-1 text-sm font-medium text-slate-700">Username</label>
                <input type="text" id="username" name="username" required placeholder="Masukkan Username" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
            </div>
            <div>
                <label for="password" class="block mb-1 text-sm font-medium text-slate-700">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan Password" class="w-full px-3 py-2 text-sm border rounded-lg border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 placeholder:text-slate-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2.5 text-white text-sm rounded-lg transition-colors bg-blue-600 hover:bg-blue-700 active:bg-blue-800">Login</button>
            </div>
        </form>
    </div>
    

    
    
</body>
</html>