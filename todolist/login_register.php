<?php 

session_start();
require_once 'config.php';

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $checkEmail = $conn->query("SELECT email FROM user WHERE email = '$email'");
    if($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'Email Is Already Registered!';
        $_SESSION['active_form'] = 'register';
    }
    else {
        $conn->query("INSERT INTO user (name, email, password) VALUES ('$name','$email','$password')");
    }

    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM user WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Setelah login sukses
            $_SESSION['user_id'] = $user['id']; // Pastikan ini ada
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            header("Location: homepage.php");
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}

?>