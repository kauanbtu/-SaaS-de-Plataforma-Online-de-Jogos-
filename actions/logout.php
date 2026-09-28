<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

logout_user();
flash('success', 'Você saiu da sua conta.');
redirect('/index.php');
