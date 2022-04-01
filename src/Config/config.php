<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../Utils/Path.php';
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

#  Limite de tempo de execução
set_time_limit(20);

# Garante que o script se mantenha vivo mesmo depois de um cliente desconectar
ignore_user_abort(false);
