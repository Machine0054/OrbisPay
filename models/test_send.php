<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';
use SendGrid\Mail\Mail;

$apiKey = getenv('SENDGRID_API_KEY');
if (!$apiKey) {
    die("No hay API key\n");
}

$email = new Mail();
$email->setFrom("no-reply@orbispay.com.co", "OrbisPay"); // remitente del dominio autenticado
$email->setSubject("Prueba directa SendGrid");
$email->addTo("jhonalexander0002@gmail.com");
$email->addContent("text/plain", "Hola Jhon, este es un test de SendGrid.");

$sg = new \SendGrid($apiKey);

try {
    $response = $sg->send($email);
    echo "Status: " . $response->statusCode() . "<br>";
    echo "Body: <pre>" . htmlspecialchars($response->body()) . "</pre>";
} catch (Throwable $e) {
    echo "Exception: " . $e->getMessage();
}