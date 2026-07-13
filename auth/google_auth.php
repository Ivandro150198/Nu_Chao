<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    redirect('/No_chao/index.php');
}

if (!google_configurado()) {
    flash('error', 'Login Google ainda não está configurado. Peça ao administrador para definir as credenciais em config/google.php.');
    redirect('/No_chao/auth/login.php');
}

$modo = (string) ($_GET['modo'] ?? 'login');
$tipo = (string) ($_GET['tipo'] ?? 'CLIENTE');

redirect(google_auth_url($modo, $tipo));
