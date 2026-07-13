<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

if (current_user()) {
    redirect('/No_chao/index.php');
}

// Cadastro passa a abrir em modal sobre a página actual
redirect('/No_chao/index.php?cadastro=1');
