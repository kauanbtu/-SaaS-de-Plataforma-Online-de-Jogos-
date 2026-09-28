<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/listing-new.php');
}
csrf_verify();

$title       = trim($_POST['title'] ?? '');
$categoryId  = (int) ($_POST['category_id'] ?? 0);
$price       = (float) ($_POST['price'] ?? 0);
$level       = trim($_POST['level'] ?? '');
$ageLabel    = trim($_POST['age_label'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '' || $categoryId <= 0 || $price <= 0) {
    flash('error', 'Preencha título, categoria e preço.');
    redirect('/pages/listing-new.php');
}

$stmt = $pdo->prepare(
    'INSERT INTO listings (seller_id, category_id, title, description, price, level, age_label, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, "active")'
);
$stmt->execute([$user['id'], $categoryId, $title, $description, $price, $level ?: null, $ageLabel ?: null]);

flash('success', 'Anúncio publicado com sucesso.');
redirect('/pages/buy-id.php');
