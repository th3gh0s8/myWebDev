<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

session_start();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);

switch ($request_uri[0]) {
    case '/':
        if (isset($_SESSION['loggedin'])) {
            header('Location: /dashboard');
        } else {
            header('Location: /login');
        }
        break;
    case '/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->username = $_POST['username'];
            $user->password = $_POST['password'];

            if ($user->userExists()) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user->username;
                header('Location: /dashboard');
            } else {
                header('Location: /login?error=1');
            }
        } else {
            include_once __DIR__ . '/../templates/login.php';
        }
        break;
    case '/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user->username = $_POST['username'];
            $user->password = $_POST['password'];

            if ($user->create()) {
                header('Location: /login');
            } else {
                header('Location: /register?error=1');
            }
        } else {
            include_once __DIR__ . '/../templates/register.php';
        }
        break;
    case '/dashboard':
        if (isset($_SESSION['loggedin'])) {
            include_once __DIR__ . '/../templates/dashboard.php';
        } else {
            header('Location: /login');
        }
        break;
    case '/logout':
        session_destroy();
        header('Location: /login');
        break;
    case '/send':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $to = $_POST['to'];
            $message = $_POST['message'];

            $sid = TWILIO_SID;
            $token = TWILIO_AUTH_TOKEN;
            $from = TWILIO_WHATSAPP_FROM;

            $client = new Twilio\Rest\Client($sid, $token);

            try {
                $client->messages->create(
                    "whatsapp:" . $to,
                    [
                        'from' => "whatsapp:" . $from,
                        'body' => $message
                    ]
                );
                header('Location: /dashboard?success=1');
            } catch (Exception $e) {
                header('Location: /dashboard?error=1');
            }
        }
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found';
        break;
}
