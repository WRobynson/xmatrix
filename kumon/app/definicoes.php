<?php
/*
	Definições de constantes do sistema
	Para um adequado funcionamento, este script deve estar na raiz do projeto
*/

/*
	CONSTANTES DINÂMICAS DO SISTEMA
*/

/*
	BARRA
	--
	Despendendo do Sistema Operacional, o separados de nomes de pastas e arquivos
	pode ser "/" ou "\".
	--
	Em desuso com a infra em DOCKER
*/
define("BARRA", DIRECTORY_SEPARATOR);							//	"/" ou "\" dependendo se for LINUX ou WINDOWS

/*
	PASTA_DO_SISTEMA
	--
	No caso do sistema não ser executado na raiz do servidor ou do VirtualHost.
	Ou seja, para acessá-lo: www.exemplo.intraer/PASTA/
*/	
$PASTA_DO_SISTEMA = "";

/*
	SITE_ROOT
	--
	É o path completo da pasta (no Sistema Operacional) raiz do sistema web.
	Aquela pasta em que o usuário cai quando acessa o site.
*/
define("SITE_ROOT", __DIR__);									//	ex: "/var/www/html"

/*
	PATH_DO_SISTEMA
	--
	É um nível acima da pasta raiz do sistema web.
*/

$path = substr(SITE_ROOT, 0, strrpos(SITE_ROOT, "/"));

define("PATH_DO_SISTEMA", $path);									//	ex:	"/var/www"

/**
 * DIR_LOG
 * -
 * Pasta com arquivos de LOG do sistema
 */

define("DIR_LOG", PATH_DO_SISTEMA."/log");							//	ex:	"/var/www/log"

define("LOG_FILE_ATIVIDADE", DIR_LOG."/atividade.log");			//	DIR_LOG/atividade.log
define("LOG_FILE_ACESSO", DIR_LOG."/acesso.log");				//	DIR_LOG/acesso.log
define("LOG_FILE_ERRO", DIR_LOG."/erro.log");					//	DIR_LOG/erro.log
define("LOG_FILE_ALERTA", DIR_LOG."/alerta.log");				//	DIR_LOG/alerta.log

/**
 * DIR_SCRIPT
 * -
 * Pasta com arquivos que contém os scripts do sistema
 */

define("DIR_SCRIPT", PATH_DO_SISTEMA."/scripts");					//	ex:	"/var/www/scripts"

/**
 * DIR_SCRIPT_LOG
 * -
 * Pasta com arquivos que contém os LOGs dos scripts do sistema
 */

define("DIR_SCRIPT_LOG", DIR_SCRIPT."/_log");				//	ex:	"DIR_SCRIPT/_log"

/**
 * DIR_BACKUP
 * -
 * Pasta com arquivos backup do sistema
 */

define("DIR_BACKUP", PATH_DO_SISTEMA."/backup");					//	ex:	"/var/www/backup"

/**
 * DIR_CONFIG
 * -
 * Pasta com arquivos se configuração da aplicação (functions, db_connect, etc.)
 */

 define("DIR_CONFIG", SITE_ROOT."/config");					//	ex:	"/var/www/html/config"

 /**
 * DIR_CLASS
 * -
 * Pasta com as Classes PHP usadas no sistema
 */
define("DIR_CLASS", SITE_ROOT."/lib");

define("CLASS_FPDF", DIR_CLASS."/fpdf/fpdf.php");

define("CLASS_PHPMAILER", DIR_CLASS."/phpmailer/phpmailer.php");

define("CLASS_MYSQLDUMP", DIR_CLASS."/phpBackupMySQL/BackupMySQL.php");

/**
 * Pasta onde ficam as imagens do sistema
 */
define("DIR_IMG", SITE_ROOT."/img");

/**
 * Pasta do usuário web.
 * Armazenará todos os arquivos gerados pelo usuário do sistema exceto o backup (dump) da Base de Dados
 */
define("DIR_USER", SITE_ROOT."/user");

/**
 * Pasta onde ficam as imagens lançadas no svc de SUP
 */
define("DIR_IMG_SVC", DIR_USER."/svc_img");

/**
 * Pasta onde ficam as os livros em PDF
 */
define("DIR_LIVROS", DIR_USER."/livros");

/**
 * Arquivo para IMAGEM NÃO ENCONTRADA
 */
define("NO_IMG", DIR_IMG . "/no-image.jpg");
/** 
 * DOCUMENT_ROOT
*/
define("DOCUMENT_ROOT",$_SERVER["DOCUMENT_ROOT"]);				//	ex: /var/www/siop/www_root (definido na config do apache)

/**
 * PROTOCOLO - (HTTP ou HTTPS)
 * --
 * Protocolo usado para acessar o sistema.
 * Configuraodo no servidor web (apache)
 */
$protocol = (!empty($_SERVER["HTTPS"]) && (strtolower($_SERVER["HTTPS"]) == "on" || $_SERVER["HTTPS"] == "1")) ? "https://" : "http://";
define("SERVER_PROT", $protocol);								//	ex: http:// ou https://


/**
 * PORTA
 * --
 * Porta usada para acessar o sistema.
 * Configuraodo no servidor web (apache)
 */
$port = $_SERVER["SERVER_PORT"] ? ":".$_SERVER["SERVER_PORT"] : "";
define("SERVER_PORT", $port);									//	Ex.: 80, 443,...


/**
 * ENDEREÇO DO SERVIDOR 
 * IP ou HOSTNAME usado pelo usuário para acessar o sistema.
 */
define("SERVER_END", $_SERVER["SERVER_NAME"]);					//	ex: 10.228.12.160 ou siop.cindacta1.intraer


/**
 * 	URL DO SISTEMA (completo)
 * -
 * Endereço completo digitado pelo usuário para acessar o sistema
*/
define("URL", SERVER_PROT . SERVER_END . SERVER_PORT . "/{$PASTA_DO_SISTEMA}");		//	ex: https://www.cindacta1.intraer:80/siop

/**
 * URL da página de configuração do sistema
 */
define("ADMIN_PAGE", URL . "admin/");

/*
	CONSTANTES ESTÁTICAS DO SISTEMA
*/

//	Mensagens
define("ERRO_SQL", "<b>Erro na interação com o Banco de Dados</b>.");

//	Cores de operacionalidade do sistema
define("VERDE", "#7fffd4");				//	operacional
define("LARANJA", "#ffe97f");			//	parcialmente operacional
define("VERMELHO", "#ffb6c1");			//	inoperante
define("CINZA", "#adb5bd");				//	desativado
define("CIANO", "#00ffff");				//	outro...

///////////////////////////////////////////////////////////////////////////////////////////////////////
////	FIM DAS DEFINIÇÔES
///////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Se esta página estiver sendo chamada, mostro as constantes
 */

$URI = $_SERVER['REQUEST_URI'];

if (strpos($URI, "definicoes.php") > 0) {
	$constants = get_defined_constants(true);

	$userConstants = $constants['user'];
	echo "<pre>";
	print_r($userConstants);
	echo "</pre>";
}

?>