<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/contact.php');
}
csrf_verify();

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    flash('error', 'Preencha nome, e-mail e mensagem.');
    redirect('/pages/contact.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('/pages/contact.php');
}

$stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, $subject, $message]);

flash('success', 'Mensagem enviada! Retornaremos em breve.');
redirect('/pages/contact.php');
