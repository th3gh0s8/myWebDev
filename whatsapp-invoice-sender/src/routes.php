<?php

use Dompdf\Dompdf;
use Twilio\Rest\Client;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Invoice.php';

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
                $_SESSION['user_id'] = $user->id;
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
    case '/customers':
        if (isset($_SESSION['loggedin'])) {
            include_once __DIR__ . '/../templates/customers.php';
        } else {
            header('Location: /login');
        }
        break;
    case '/add_customer':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_SESSION['loggedin'])) {
                $customer = new Customer($db);
                $customer->user_id = $_SESSION['user_id'];
                $customer->name = $_POST['name'];
                $customer->phone_number = $_POST['phone_number'];

                if ($customer->create()) {
                    header('Location: /customers?success=1');
                } else {
                    header('Location: /customers?error=1');
                }
            } else {
                header('Location: /login');
            }
        }
        break;
    case '/invoices':
        if (isset($_SESSION['loggedin'])) {
            include_once __DIR__ . '/../templates/invoices.php';
        } else {
            header('Location: /login');
        }
        break;
    case '/create_invoice':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_SESSION['loggedin'])) {
                $customer = new Customer($db);
                $customer_data = $customer->readOne($_POST['customer_id']);

                $invoice = new Invoice($db);
                $invoice->user_id = $_SESSION['user_id'];
                $invoice->customer_id = $_POST['customer_id'];
                $invoice->invoice_data = $_POST['invoice_data'];
                $invoice->status = 'pending';

                if ($invoice->create()) {
                    $invoice_id = $db->lastInsertId();

                    $dompdf = new Dompdf();
                    $invoice_html = '<h1>Invoice</h1><p>Details: ' . $invoice->invoice_data . '</p>';
                    $dompdf->loadHtml($invoice_html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $pdf_content = $dompdf->output();

                    $pdf_path = __DIR__ . '/../invoices/invoice_' . $invoice_id . '.pdf';
                    file_put_contents($pdf_path, $pdf_content);

                    $sid = TWILIO_SID;
                    $token = TWILIO_AUTH_TOKEN;
                    $from = TWILIO_WHATSAPP_FROM;
                    $to = $customer_data['phone_number'];

                    $client = new Client($sid, $token);

                    try {
                        $client->messages->create(
                            "whatsapp:" . $to,
                            [
                                'from' => "whatsapp:" . $from,
                                'body' => 'Please find your invoice attached.',
                                'mediaUrl' => 'https://' . $_SERVER['HTTP_HOST'] . '/invoices/invoice_' . $invoice_id . '.pdf'
                            ]
                        );

                        $invoice->status = 'sent';
                        $invoice->update();

                        header('Location: /invoices?success=1');
                    } catch (Exception $e) {
                        header('Location: /invoices?error=1&message=' . $e->getMessage());
                    }
                } else {
                    header('Location: /invoices?error=1');
                }
            } else {
                header('Location: /login');
            }
        }
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        echo '404 Not Found';
        break;
}
