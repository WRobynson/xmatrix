<?php
/*
	CONEXAO COM A BASE LOCAL "db_kumon"
*/

/*
	CREATE USER 'kumon'@'%' IDENTIFIED VIA mysql_native_password USING '***';
	GRANT USAGE ON *.* TO 'kumon'@'%' REQUIRE NONE WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0;
	GRANT ALL PRIVILEGES ON `db\_kumon`.* TO 'kumon'@'%';

	REVOKE ALL PRIVILEGES ON `db\_kumon`.* FROM 'kumon'@'%'; GRANT SELECT, INSERT, UPDATE, DELETE, REFERENCES, LOCK TABLES ON `db\_kumon`.* TO 'kumon'@'%';
*/ 

$DBHOST		= $_ENV["MYSQL_HOST"];
$DBNAME 	= $_ENV["MYSQL_DATABASE"];
$DBUSER 	= $_ENV["MYSQL_USER"];
$DBSENHA	= $_ENV["MYSQL_PASSWORD"];

/**
	* Teste de conexão com o MySQL SERVER
 */

$PDO = "mysql:host={$DBHOST}";
try {
	$DB = new PDO($PDO, $DBUSER, $DBSENHA);
	$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$DB->exec("set names utf8");
	$DB->exec("SET character_set_connection=utf8");
	$DB->exec("SET character_set_client=utf8");
	$DB->exec("SET character_set_results=utf8");

}
catch(PDOException $e) {
	$erro = $e->getMessage();
	_log_sql("{$PDO} | User: {$DBUSER} | Paswd: {$DBSENHA}", $erro, "Erro de conexão com o Banco [{$DBNAME}].");

	if (strpos($erro, "[1045]" !== false))
		$bizu = "
			<p>Erro de autenticação. Usuário `{$DBUSER}` existe?</p>
			<p>--</p>
			<pre>
				CREATE USER IF NOT EXISTS '{$DBUSER}'@'%' IDENTIFIED BY '{$DBSENHA}';<br>
				GRANT ALL PRIVILEGES ON {$DBHOST}.* TO '{$DBUSER}'@'%' IDENTIFIED BY '{$DBSENHA}';
			</pre>
		";
	else
		$bizu = null;
	
	echo "
		<div class='alert alert-danger' role='alert'>
			<h4 class='alert-heading'>Ooops!
			<p>Não foi possível conectar com o MySQL Server.</p>
			{$bizu}
		</div>";
	die();
}

/*
	Checa se a Base Local existe (`db_kumon`)
*/

$sql = "SHOW DATABASES LIKE :database_name";

$stmt = $DB->prepare($sql);
$stmt->bindParam(':database_name', $DBNAME, PDO::PARAM_STR);
$stmt->execute();
 
 // Verifica se a base de dados foi encontrada
if (! $stmt->rowCount() > 0) {
	_log("Base de dados [{$DBNAME}] não encontrada no servidor.", 1);
	
	//	tente criar a base de dados (`db_kumon`)
	$sql = "CREATE DATABASE IF NOT EXISTS {$DBNAME} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
	
	try {
		$DB->exec($sql);
		_log("Base de dados [{$DBNAME}] criada com sucesso.");
	}
	catch (PDOException $e) {
		_log("Erro ao tentar criar o banco de dados [{$DBNAME}]: " . $e->getMessage(), 1);

		echo "
			<div class='alert alert-danger' role='alert'>
				<h4 class='alert-heading'>Ooops!
				<p>O Banco de Dados [<b>{$DBNAME}</b>] não foi encontrado no servidor.</p>
				<p>Nao foi possível criar o Banco de Dados [<b>{$DBNAME}</b>] no servidor.<br>({$e->getMessage()})</p>
			</div>";
		die();
	}
} 

//	Conecto na Base de Dados (já existente)
$DB->exec("USE $DBNAME");


?>
