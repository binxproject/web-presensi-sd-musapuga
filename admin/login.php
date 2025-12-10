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
<body>

    <h1>Login Admin</h1>

    <?php if( !empty($errors) ) : ?>
        <div>
            <ul>
                <?php foreach($errors as $err) : ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit">Login</button>
        </div>
    </form>

    
    
</body>
</html>