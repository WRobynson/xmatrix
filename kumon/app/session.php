<?php

/*
	SESSÃO 
	Este script é chamado no início das páginas mostrada no sistema
*/

/*
	tempo máximo da sessão (segundos)
	default (php.ini) = 1440 (24 min)
*/

//ini_set( "session.gc_maxlifetime", 1800);	//	30 min

session_name("kumon");
session_start();

$session_id = session_id();
$_SESSION["KEY"] = $session_id;	

if (! isset($_SESSION["LOGADO"]))
	$_SESSION["LOGADO"] = false;

if (! isset($_SESSION["EHADMIN"]))
	$_SESSION["EHADMIN"] = false;

if (! isset($_SESSION["EHSUP"]))
	$_SESSION["EHSUP"]	= false;

if (! isset($_SESSION["LOGIN_ATTEMPT"]))
	$_SESSION["LOGIN_ATTEMPT"] = 0;

if (! isset($_SESSION["LOGIN_LAST_ATTEMPT"]))		//	data e hora do último insucesso no login
	$_SESSION["LOGIN_LAST_ATTEMPT"] = null;


if (! isset($_SESSION["LOGIN_ESPERA"]))
	$_SESSION["LOGIN_ESPERA"]	= 0;

//	o momento da última atividade no sistema
if ($_SESSION["LOGADO"] !== false)
	$_SESSION['LAST_ACTIVITY'] = time();

?>