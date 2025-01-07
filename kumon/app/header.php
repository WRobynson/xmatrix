<?php
    //  todos os erros são capturados (E_ALL & ~E_NOTICE & ~E_WARNING) para filtrar
    error_reporting(E_ALL);

    //  os erros não são mostrados na tela ('display_errors', 1) para mostrar
    //  todos os erros são direcionados para o arquivo de LOG (/php_error.log)
    //  definido em  php.ini
    ini_set('display_errors', 0);

    header("Content-Type: text/html; charset=UTF-8",true);
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type");
    mb_internal_encoding('UTF8'); 
    mb_regex_encoding('UTF8');

    putenv("TZ=America/Sao_Paulo");
    date_default_timezone_set("America/Sao_Paulo");
    setlocale(LC_ALL, NULL);
    setlocale (LC_ALL, "ptb", "pt_BR", "portuguese-brazil", "bra", "brazil", "pt_BR.iso-8859-1", "pt_BR.utf-8");
?>