<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}
csrf_verify();

$referer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
$path = parse_url($referer, PHP_URL_PATH) ?: '/index.php';

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido para assinar a newsletter.');
    redirect($path);
}

$stmt = $pdo->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)');
$stmt->execute([$email]);

flash('success', 'Inscrição na newsletter confirmada!');
redirect($path);
