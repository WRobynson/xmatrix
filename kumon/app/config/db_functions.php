<?php
/*
	Qualquer página que chame este arquivo, deve carregar ANTES 
	os arquivos DEFINICOES.PHP e FUNCTIONS.PHP
*/

/*
	CONEXAO COM A BASE DE DADOS
*/
include("db_conn_KUMON.php");


//////////////////////
/*
	Funções de operação com o Banco de Dados
*/

/**
 * Insere um novo registro numa tabela do DB
 * --
 * @param array $dados_arr Nome da tabela e os campos associados aos valores a serem inseridos
 * @param string $csrf_token Para o controle de formulário (evitar duplicação com F5)
 * @return string 'F5' Se a página está sendo recarregada 
 * @return int $id O 'id' do novo registro
 * @return array Informações sobre o erro
 *   - '0' (string): A instrução SQL executada.
 *   - '1' (string): A mensagem de erro.
 * 
 * $dados_arr traz as informações necessárias para INSERIR o registro: 
 * - [0] => o nome da tabela que se deseja gravar
 * - [1] => um array com os campos (como chaves do array) e seus respectivos valores
 *   - Ex.: $dados_arr = array (
 *				0 => "t_livro_extra",
*				1 => array (
*					"turno"			=>	$turno_id,
*					"qdo_z"			=>	$qdo,
*					"titulo"		=>	$tit,
*					"sistema"		=>	$sis,
*					"informante"	=>	$inf,
*					"ocorrencia"	=>	$ocor,
*					"qc"			=>	$logado_id,
*					"qm"			=>	$logado_id,
*				)
*			); 
*
* Também pode ser passado, como valor de um campo, uma função do MySQL
*	  - Exemplo: 
*	  -		$dados_arr[1]["qdo"] = "function::NOW()";
*/

function sqlInsert($dados_arr, $csrf_token = null) 
{
	global $DB;

	if ($_SESSION["csrf_token"] == $csrf_token)	//	este foi o último FORM executado
		return "F5";
	else {
		/**
		 * construção do SQL
		 * $sql = "INSERT INTO `t_livro_extra` (`turno`, `qdo_z`, `titulo`, `sistema`, `informante`, `ocorrencia`, `qc`, `qm`)
		 * VALUES (:turno, :qdo_z, :titulo, :sistema, :informante, :ocorrencia, :qc, :qm);";
		 */

		//	$values2 é para a construção do SQL a ser retornada em caso de erro
		$campos = $values = $values2 = null;
		
		foreach ($dados_arr[1] as $campo => $val) {
			$campos .= "`{$campo}`,";
			
			//	se o dado a ser inserido for o resultado de uma função MySQL...
			if (preg_match("/^function::/", $val ?? '')) {
				list(, $func) = explode("::", $val);
				$values .= "{$func},";
				$values2 .= "{$func}, ";
			}
			else {
				$values .= ":{$campo},";
				$values2 .= "'{$val}',";
			}
		}
		
		//	retire a última vírgula
		$campos = substr($campos, 0, -1);
		$values = substr($values, 0, -1);
		$values2 = substr($values2, 0, -1);

		$sql = "INSERT INTO `{$dados_arr[0]}` ({$campos}) VALUES ({$values});";
		
		//	$sql2 é para ser retornado em caso de erro
		$sql2 = "INSERT INTO `{$dados_arr[0]}` ({$campos}) VALUES ({$values2});";

		try {
			$stmt = $DB->prepare($sql);
			
			foreach ($dados_arr[1] as $campo => &$val) {
				//	se não for uma função...
				if (! preg_match("/^function::/", $val ?? ''))
					$stmt->bindParam(":{$campo}", $val);
			}

			$stmt->execute(); 

			$n_id = $DB->lastInsertId();	//	new_id

			$_SESSION["csrf_token"] = $csrf_token;		//	atualizo o 'id' do último FORM executado

			return $n_id;
		}
		catch(Exception $e) {
			return array($sql2, $e->getMessage());
		}
	}
}


/**
 * Altera os valores de um novo registro numa tabela do DB
 * --
 * @param array $dados_arr Nome da tabela e os campos associados aos valores a serem inseridos
 * @param string $csrf_token Para o controle de formulário (evitar duplicação com F5)
 * @return string 'F5' Se a página está sendo recarregada 
 * @return int $id O 'id' do registro alterado
 * @return int '0' Se mais de um registro foi alterado
 * @return array Informações sobre o erro
 *   - '0' (string): A instrução SQL executada.
 *   - '1' (string): A mensagem de erro.
 *
 * $dados_arr traz os dados necessários para ALTERAR o registro: 
 *	- [0] => o nome da tabela que se deseja alterar; 
*	- [1] => o ID do registro que se deseja alterar;
*	-	Pode ser um array com od IDs caso se deseje alterar mais de um
*	- [2] => os campos (como chaves do array) com seus respectivos valores
* 
*	- Exemplo:
*	-	$dados_arr = array (
*				0 => "t_livro_extra",
*				1 => id,
*				2 => array (
*					"qdo_z"			=>	$qdo,
*					"titulo"		=>	$tit,
*					"sistema"		=>	$sis,
*					"informante"	=>	$inf,
*					"ocorrencia"	=>	$ocor,
*					"qm"			=>	$logado_id,
*				)
*			);
* 
* Também pode ser passado, como valor de um campo, uma função do MySQL
*	- Exemplo: 
*	-	$dados_arr[2]["qdo"] = "function::NOW()";
* 
*/

function sqlUpdate($dados_arr, $csrf_token = "NA") 
{
	global $DB;
	
	//	pego o ID do registro a ser alterado
	$id = $dados_arr[1];

	/**
	 * construção do SQL (ex.)
	 * $sql = "UPDATE `t_livro_extra` 
	 *				SET `qdo_z` 		= :qdo_z, 
	*					`sistema` 		= :sistema, 
	*					`informante`	= :informante, 
	*					`titulo` 		= :titulo, 
	*					`ocorrencia` 	= :ocorrencia, 
	*					`qm`	 		= :qm
	*				WHERE `id`= {$id};";
	*/

	//	$values2 é para a construção do SQL a ser retornada em caso de erro
	$values = $values2 = null;

	foreach ($dados_arr[2] as $campo => $val) {
		//	se o dado a ser inserido for o resultado de uma função MySQL...
		if (preg_match("/^function::/", $val ?? '')) {
			list(, $func) = explode("::", $val);

			$values .= "`{$campo}` = {$func},";
			$values2 .= "{$func}, ";
		}
		else {
			$values .= "`{$campo}` = :{$campo},";
			$values2 .= "`{$campo}` = '{$val}',";
		}
	}

	//	retiro a última vírgula
	if ($values != null) $values = substr($values, 0, -1);
	if ($values2 != null) $values2 = substr($values2, 0, -1);

	/**
	 * cláusura WHERE
	 * Se o `id` for um array, a alteração deve ser feita em mais de um registro.
	 * Nesse caso, se n houver erro, a funçáo retorna o inteiro 0
	 */

	if (is_array($id)) {
		$IDs = implode(', ', $id);
		$IDs = "({$IDs})";
		$return = 0;

		$where = "`id` IN {$IDs}";
	}
	else {
		$where = "`id` = {$id}";
		$return = $id;
	}
		
	$sql = "UPDATE `{$dados_arr[0]}` SET {$values} WHERE {$where};";
	//echo $sql;

	//	$sql2 é para ser retornado em caso de erro
	$sql2 = "UPDATE `{$dados_arr[0]}` SET {$values2} WHERE {$where};";

	if ($csrf_token != "NA" && $_SESSION["csrf_token"] == $csrf_token)	//	este foi o último FORM executado
		return "F5";
	else {
		try {
			$stmt = $DB->prepare($sql);
			
			foreach ($dados_arr[2] as $campo => &$val) {
				//	se não for uma função...
				if (! preg_match("/^function::/", $val ?? ''))
					$stmt->bindParam(":{$campo}", $val);
			}

			$stmt->execute(); 

			$_SESSION["csrf_token"] = $csrf_token;		//	atualizo o 'id' do último FORM executado

			return $return;
		}
		catch(Exception $e) {
			return array($sql2, $e->getMessage());
		}
	}
}

/**
 * Deleta um registro numa tabela do DB
 * --
 * @param array $dados_arr Informações necessárias para EXCLUIR o registro
 * @param string $csrf_token Para o controle de formulário (evitar duplicação com F5)
 * @return string 'F5' Se a página está sendo recarregada 
 * @return int $id O 'id' do registro alterado
 * @return int '1' Quando o campo a ser comparado não foi o 'id'
 * @return array Informações sobre o erro
 *   - '0' (string): A instrução SQL executada.
 *   - '1' (string): A mensagem de erro. 
 *
 * $dados_arr traz os dados necessários para EXCLUIR o registro:
 *   - [0] => o nome da tabela que se deseja alterar, 
 *   - [1] => o nome do campo ou uma lista de campos cujo valor deve ser consultado,
 *   - [2] => o valor que campo(s) deve(m) ter para que o registro seja excluído,
 *   - [3] => o ID do usuário que está excluindo o registro.
 *
 *  Ex: Apagar o(s) registro(s) da tabela `t_livro_extra` em que o campo `id` seja igual a $id_reg
 * 
 *    - $dados_arr = array (
 *        0 => "t_livro_extra",
 *        1 => "id"		OU		["id", "ref"],  // Suporta múltiplos campos
 *        2 => $id_reg,
 *        3 => $id_logado
 *    );
 *
 * NA = Não se aplica. Quando a função é chamada sem o POST
 */

 function sqlDelete($dados_arr, $csrf_token = "NA") 
 {
	 global $DB;
	 
	 if ($csrf_token != "NA" && $_SESSION["csrf_token"] == $csrf_token)	//	este foi o último FORM executado
		 return "F5";
	 else {
		 $table 	= $dados_arr[0];
		 $campos = $dados_arr[1];	//	// Pode ser um string (campo único) ou array (múltiplos campos)
		 $val 	= $dados_arr[2];
		 $autor 	= $dados_arr[3];
 
		 // Verifica se os dados são válidos
		 if (empty($table) || empty($campos) || empty($val)) {
			 return array('Erro', 'Parâmetros inválidos fornecidos');
		 }
 
		 // Se $campos for um array, construir a cláusula WHERE dinamicamente
		 if (is_array($campos)) {
			 $conditions = $conditions2 = [];
			 
			 foreach ($campos as $campo) {
				 $conditions[] = "`{$campo}` = :val";
				 $conditions2[] = "`{$campo}` = $val";
			 }
			 $whereClause = implode(' OR ', $conditions);
			 $whereClause2 = implode(' OR ', $conditions2);
		 } else {
			 // Caso seja um único campo, monta a cláusula WHERE
			 $whereClause = "`{$campos}` = :val";
			 $whereClause2 = "`{$campos}` = $val";
		 }
 
		 try {
			 //	sql
			 $sql = "UPDATE `{$table}` SET `excluso` = 1, `qm` = :qm WHERE {$whereClause}";
			 
			 //	$sql2 é para ser retornado em caso de erro
			 $sql2 = "UPDATE `{$table}` SET `excluso` = 1, `qm` = :qm WHERE {$whereClause2}";
 
			 $stmt = $DB->prepare($sql);
 
			 $stmt->bindParam(":qm", $autor);
			 $stmt->bindParam(":val", $val);
 
			 $stmt->execute(); 
 
			 $_SESSION["csrf_token"] = $csrf_token;		//	atualizo o 'id' do último FORM executado
 
			 if ($campo == "id")
				 return $val;		//	retorna o ID do registro 'deletado'
			 else {
				 return $stmt->rowCount();  // Retorna o número de registros afetados	
			 }
		 }
		 catch(Exception $e) {
			 return array($sql2, $e->getMessage());
		 }
 
	 }
 }


/**
 * Executa uma consulta SQL e retorna um array com os resultados.
 * --
 * @param string $sql A query SQL a ser executada.
 * @param array $params (Opcional) Parâmetros para bind na consulta preparada. 
 *                      Esses parâmetros serão associados aos marcadores na query SQL.
 * @return array|false Retorna um array associativo contendo os registros da consulta, ou um array vazio se nenhum resultado for encontrado.
 *                     Retorna false em caso de erro ao executar a consulta.
 * 
 * Exemplo de uso:
 * 
 * // Exemplo com parâmetros dinâmicos
 * $sql = "SELECT * FROM usuarios WHERE id = :id";
 * $params = [':id' => $user_id];
 * $result = getSelect($sql, $params);
 * 
 * // Iterando sobre os resultados
 * if ($result) {
 *     foreach ($result as $linha) {
 *         // Acessando os campos de cada linha retornada
 *         echo $linha['nome'];
 *     }
 * } else {
 *     // Tratamento de erro ou ausência de resultados
 *     echo "Nenhum registro encontrado ou erro na consulta.";
 * }
 * 
 * // Exemplo sem parâmetros (consulta estática)
 * $sql = "SELECT * FROM produtos";
 * $result = getSelect($sql);
 * 
 * // Verificação e manipulação dos resultados
 * if (!empty($result)) {
 *     foreach ($result as $linha) {
 *         // Exibir ou manipular os dados
 *     }
 * }
 * 
 * Nota:
 * Em caso de erro, a função registra a query SQL e a mensagem de erro em um log 
 * (função _log_sql()) e retorna false para indicar falha na execução.
 */

 function getSelect($sql, $params = []) 
 {
	 global $DB;
 
	 try {
		 $stmt = $DB->prepare($sql);
		 $stmt->execute($params);  // Executa com os parâmetros vinculados, se existirem
 
		 if ($stmt->rowCount() > 0) {
			 return $stmt->fetchAll(PDO::FETCH_ASSOC);
		 } else {
			 return [];
		 }
	 } catch (PDOException $e) {
		 _log_sql($sql, $e->getMessage());
		 return false;
	 }
 }


 /**
 * Função para realizar múltiplas consultas SQL e unir os resultados em uma única consulta
 * usando o operador UNION ALL. A função ajusta os campos de cada consulta para garantir
 * que todos os resultados tenham a mesma estrutura, preenchendo os campos ausentes com
 * NULL. Também permite a aplicação de uma cláusula ORDER BY opcional.
 * //
 * A função tem uma limitação importante: ela depende da existência dos campos 
 * que estão sendo referenciados nas consultas SQL passadas. Se você estiver utilizando um campo calculado 
 * ou um alias (como qdo no exemplo), e esse campo não existir nas tabelas referenciadas, a consulta 
 * pode falhar ao ser executada.
 *
 * @param array $sql_arr Array contendo as consultas SQL a serem executadas.
 * @param string|null $order (opcional) Cláusula ORDER BY para ordenar os resultados.
 * @return array|false Retorna um array com os resultados combinados, ou false se a execução falhar.
 */

function getSelectUnion($sql_arr, $order = null)
{
	global $DB;

	// Array para armazenar todos os campos únicos
	$allFields = [];

	// Loop por cada SQL para obter os campos
	foreach ($sql_arr as $sql) {
		// Executa a consulta com LIMIT 1 para obter os campos
		$stmt = $DB->query("$sql LIMIT 1");
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		// Mescla os campos no array $allFields
		if ($result) {
			$allFields = array_unique(array_merge($allFields, array_keys($result)));
		}
	}
	//	$allFields é um array com todos os campos resultantes no UNION
	//	Se não houver campos, retorna vazio
	if (empty($allFields)) {
		return [];
	}

	// Loop para ajustar cada consulta SQL
	$newSqlArr = [];

	//	para cada consulta original
	foreach ($sql_arr as $sql) {
		$stmt = $DB->query("$sql LIMIT 1");
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($result) {	// Monta um novo SELECT, incluindo os campos ausentes com "NULL AS campo"
			$fields = [];

			//	para cada campo mostrado no UNION
			foreach ($allFields as $field) {
				//	se o campo do UNION está presente na consulta original...
				if (array_key_exists($field, $result)) {
					$fields[] = "`{$field}` AS `{$field}`";  // Campo existente
				} else {
					$fields[] = "NULL AS `{$field}`";  // Campo ausente
				}
			}

			// Constrói a nova consulta com os campos ajustados
			$fieldList = implode(", ", $fields);
			$newSql = preg_replace('/SELECT\s+.*?\s+FROM/i', "SELECT $fieldList FROM", $sql);
			$newSqlArr[] = $newSql;
		}
	} 

	// Unir todas as consultas ajustadas com UNION ALL
	$unionSql = implode(' UNION ALL ', $newSqlArr);

	// Adicionar ORDER BY, se fornecido
	if ($order) {
		$unionSql .= " ORDER BY $order";
	}

	// Executar a consulta final usando getSelect
	return getSelect($unionSql) ?: false; // Retorna false se getSelect falhar
}
 

/**
 * Traz o valor de um único campo num registro especifico
 * --
 * @param string $sql A query da consulta
 * @param string $campo O nome do campo que se deseja consultar
 * @return false Se a consulta retornar mais de um registro
 * @return null Se a consulta retornar nenhum registro
 * @return array $reg Um array com os campos do registro consultado (se $campo não for especificado)
 * @return string $campo O valor do campo especificado
 * @return string 'SQL_ERROR' Se houver um erro no SQL.
 * 
 *	- Exemplo:
 *	-	 $grupo = sqlResult("select grupo from t_times where id='1'","grupo");
*/

function sqlResult($sql, $campo = null)
{
    global $DB;

    try {
        $consulta = $DB->query($sql);
        $result = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 1) 
            return false;

        if (empty($result)) 
            return null;

        // Apenas um registro foi retornado
        $reg = $result[0]; 
        return $campo === null ? $reg : $reg[$campo]; // Retorna o valor do campo especificado
    }
    catch (PDOException $e) {
        // Registre o erro de SQL no log
        _log_sql($sql, $e->getMessage());
        return "SQL_ERROR";
    }
}


/**
 * Pesquisa um determinado VALOR em um determinado CAMPO de uma determinada TABELA.
 * --
 * @param string $table O nome da TABELA a ser consultada
 * @param string $campo O nome do CAMPO a ser consultado dentro da TABELA
 * @param string $value O VALOR a ser comparado com o CAMPO
 * @param string $operador O tipo de operador na consulta: se exatamente igual "=" ou usando "LIKE" ou ainda REGEX "RLIKE"
 * @param boolean $exc Se considera os registro excluídos (`exclude` = 1)
 * @return boolean TRUE se encontrar ou FALSE caso contrário
*/

function existeValor($table, $campo, $value, $operador = "=", $exc = false) 
{
	
	$operador = trim($operador);
	
	if (! preg_match('/^=$|^like$|^rlike$/i', $operador)) {
		echo "Operador inválido! Use '=', 'LIKE' ou 'RLIKE'.";
		exit(0);
	}
	
	if ($exc === false)
		$EXC = "AND NOT `excluso`";
	else
		$EXC = null;
	
	$sql = "SELECT `{$campo}` FROM `{$table}` WHERE `{$campo}` {$operador} '{$value}' {$EXC} LIMIT 1";
	
	$result = sqlResult($sql, $campo);
	
	if ($result == "")
		return false;
	else
		return true;
}

/**
 * Verifica se DOIS valores (campo1 e campo2) coexistem no mesmo registro.
 * --
 * @param string $table O nome da TABELA a ser consultada
 * @param string $campo1 O nome do primeiro CAMPO a ser consultado dentro da TABELA
 * @param string $value1 O VALOR a ser comparado com o primeiro CAMPO
 * @param string $operador1 O tipo de operador na primeira consulta ("=", "LIKE" ou "RLIKE")
 * @param string $campo2 O nome do segundo CAMPO a ser consultado dentro da TABELA
 * @param string $value2 O VALOR a ser comparado com o segundo CAMPO
 * @param string $operador2 O tipo de operador na segunda consulta ("=", "LIKE" ou "RLIKE")
 * @param boolean $exc Se considera os registro excluídos (`exclude` = 1)
 * @param boolean $space Se considera ou não os espaços em branco na consulta
 * @return boolean TRUE se encontrar ou FALSE caso contrário
*/

function existeValor2($table, $campo1, $value1, $campo2, $value2, $operador1 = "=", $operador2 = "=", $exc = false, $space = true) 
{
	$operador1 = trim($operador1);
	$operador2 = trim($operador2);
	
	if (! preg_match('/^=$|^like$|^rlike$/i', $operador1) || ! preg_match('/^=$|^like$|^rlike$/i', $operador2)) {
		echo "Operador inválido! Use '=', 'LIKE' ou 'RLIKE'.";
		exit(0);
	}
	
	if ($exc === false)
		$EXC = "AND NOT `excluso`";
	else
		$EXC = null;
	
	$sql = "SELECT `id` FROM `{$table}` WHERE (`{$campo1}` {$operador1} '{$value1}') AND (`{$campo2}` {$operador2} '{$value2}') {$EXC} LIMIT 1";

	//	se for pra desconsiderar os espaços em branco na pesquisa
	if ($space === true) {
		$value1 = str_replace(" ", "", $value1);
		$value2 = str_replace(" ", "", $value2);
		
		$sql = "SELECT `id` FROM `{$table}` WHERE (REPLACE(`{$campo1}`, ' ', '') {$operador1} '{$value1}') AND (REPLACE(`{$campo2}`, ' ', '') {$operador2} '{$value2}') {$EXC} LIMIT 1";
	}
	////////////////////////////////////////////////////

	$result = sqlResult($sql, "id");
	
	if ($result == "")
		return false;
	else
		return true;
}


/**
 * Concatena os valores de um campo específico retornados por uma consulta SQL em uma única string, separados por um delimitador.
 * --
 * @param string $sql A query SQL que será executada para obter os resultados.
 * @param string $campo O nome do campo cujos valores serão concatenados. (Obrigatório)
 * @param string $separador O delimitador usado para separar os valores concatenados na string. (Padrão: '|')
 * @return string Uma string contendo os valores do campo especificado, separados pelo delimitador.
 * @return boolean Retorna FALSE se o parâmetro $campo não for informado ou estiver vazio.
 *
 * Exemplo de uso:
 * 
 * // Exemplo simples para concatenar valores do campo 'nomes' da tabela 't_usuarios'
 * $nomes = sqlCol2Str("SELECT nomes FROM t_usuarios WHERE id > 5", "nomes");
 * 
 * // Saída (supondo que haja 3 usuários): 'João|Maria|Carlos'
 * 
 * // Exemplo com outro separador
 * $nomes = sqlCol2Str("SELECT nomes FROM t_usuarios WHERE id > 5", "nomes", ", ");
 * // Saída: 'João, Maria, Carlos'
 * 
 * Nota:
 * - O separador padrão é o caractere '|', mas pode ser alterado.
 * - Certifique-se de usar um separador que não esteja presente nos valores do campo, para evitar confusão.
 * - A função remove o último separador ao final da string.
 */

 function sqlCol2Str($sql, $campo, $separador = '|') 
 {
	 global $DB;
	 
	 // $campo não pode ser Nulo nem em branco
	 $campo = trim($campo);
	 if (empty($campo)) return false;
 
	 $result = getSelect($sql);
 
	 if (empty($result)) {
		 return ''; // Retorna string vazia se não houver resultados
	 }
 
	 // Usa array_map para criar um array com os valores do campo e implode para juntar
	 $valores = array_map(fn($item) => $item[$campo], $result);
	 
	 return implode($separador, $valores); // Retorna a string concatenada
 }

/**
 * Retorna a quantidade de registros da consulta $query.
 * --
 * @param string $sql A query da consulta.
 * @return int A qunatidade de registros da resposta.
 * @return boolean Retorna FALSE se houver erro de SQL.
*/

function sqlCont($sql)
{
	global $DB;
	
	try {
		$consulta = $DB->query($sql);
		
		return $consulta->rowCount();		//	// Retorna a quantidade de registros
	} catch (PDOException $e) {
		_log_sql($sql, $e->getMessage());

		return false;
	}
}

/**
 * Verifica a existência de uma tabela ou view no BD
 * --
 * @param string $tabela A tabela a ser consultada.
 * @return boolean TRUE se a tabela existir, FALSE caso contrário.
 * 
 *	- Obs.:
*	-	A função também retorna FALSE se houver erro na consulta.
*/

function ExisteTab($tabela) 
{
	global $DB; // Assumindo que $DB é uma conexão PDO previamente estabelecida

	try {
		$query = "SHOW TABLES LIKE ?";
		$query2 = "SHOW TABLES LIKE '{$tabela}'";
		$stmt = $DB->prepare($query);
		$stmt->execute([$tabela]);

		// Verifica se a tabela existe
		return $stmt->rowCount() > 0;
	}
	catch (PDOException $e) {
		// Lida com erros, como por exemplo, se a tabela não puder ser verificada
		_log_sql($query2, $e->getMessage(). "Erro ao verificar a existência da tabela");
		
		return false; // Retorna false em caso de erro
	}
}

/**
 * Retorna uma consulta em um array com chave definida como um dos campos.
 * --
 * @param string $sql A query da consulta ou o nome da tabela. (ver obs.)
 * @param string $key é o campo que será o índice ['chave'] do array retornado.
 * @param array $campos é um array com os campos vinculados à chave.
 * @return array Um array com os valores desejados.
 * @return boolean Retorna FALSE se $sql for em branco ou tabela não existir ou houver erro no SQL.
 * 
 *	- Obs.:
*	-	$sql pode ser uma tabela ou view com nome iniciado por 't_' ou 'v_'. Nesse caso, todos os registros serão consulados.
*/

function sql2Arr($sql, $key, $campos) 
{
	if (trim($sql) == "") return false;

	//	veja se foi passado uma tabela ou view...
	if (preg_match("/^[tv]_/", $sql)) {

		// veja se a tabela existe
		if (! existeTab($sql))
			return false;

		$sql = "SELECT * FROM `{$sql}`";
	}
	
	if ($result = getSelect($sql)) {
		$output_arr = [];

		foreach($result as $linha) {
			$ind = $linha[$key];

			$campos_arr = [];
			
			foreach ($campos as $campo) {
				$campos_arr[$campo] = $linha[$campo];
			}

			$output_arr[$ind] = $campos_arr;
		}
		return $output_arr;
	}
	else return false;
}

/**
 * Pega o nome de usuário (POSTO/GRAD e NOME DE GUERRA) com base na data
 * 
 * @param int $id O ID do uauário.
 * @param string $data A data em questão (formato MySQL DATE ou DATETIME).
 * @return string O nome do usuário na data em questão.
 */

function nameByDate($id, $data) {
	$sql = "SELECT `usuario` FROM `t_usuarios_posto` WHERE `usuario_id`={$id} AND `qdo`<='{$data}' ORDER BY `qdo` DESC LIMIT 1";

	$nome = sqlResult($sql, "usuario");

	return $nome;
}

function temRegistro($table_arr) {
	// Itera sobre cada tabela no array
	foreach ($table_arr as $tabela) {

		$qt = sqlCont("SELECT `id` FROM `{$tabela}` WHERE `excluso` = 0"); //echo "qt = {$qt}; ";

		if ($qt === false) {
			// Se ocorreu um erro, assume que a tabela não tem registros
			return false;
			break;
		}

		if ($qt === 0) {
			// Se a contagem for zero, então a tabela está vazia
			return false;
			break;
		}
	}

	// Retorna o resultado
	return true;
}

/**
 * Reorganiza a tabela passada como parâmetro, redefinindo a coluna de ID para garantir que os registros estejam numerados de forma sequencial
 * @param string $tabela O nome da tabela a ser reorganizada
 * @return true Se a reorganização for bem-sucedida
 * @return string A mensagem de erro em caso de falha
 */
function reorganizarTabela($tabela) {
	global $DB;
	$logado_id = (isset($_SESSION["LOGADO_ID"]) ? $_SESSION["LOGADO_ID"] : 0); // Pegando o logado_id da sessão

	try {
		// Começa uma transação
		if (!$DB->inTransaction()) 
			$DB->beginTransaction();

		// Obter o tipo de dados da coluna `id`
		$stmt = $DB->prepare("SHOW COLUMNS FROM `{$tabela}` LIKE 'id'");
		$stmt->execute();
		$columnInfo = $stmt->fetch(PDO::FETCH_ASSOC);
		$idType = $columnInfo['Type']; // Tipo da coluna `id`, por ex: 'tinyint(3) unsigned'
		$stmt->closeCursor();

		// Passo 1: Remover a restrição de chave primária da coluna `id`
		$DB->exec("ALTER TABLE `{$tabela}` MODIFY `id` {$idType}");
		$DB->exec("ALTER TABLE `{$tabela}` DROP PRIMARY KEY");

		// Passo 2: Adicionar uma nova coluna para IDs reordenados após a coluna `id`
		$DB->exec("ALTER TABLE `{$tabela}` ADD COLUMN `novo_id` {$idType} AFTER `id`");

		// Passo 3: Atualizar a nova coluna `novo_id` com IDs sequenciais baseados na ordem da data
		$stmt = $DB->query("
			SET @id := 0;
			UPDATE `{$tabela}`
			SET `novo_id` = (@id := @id + 1)
			ORDER BY `data` ASC;
		");
		$stmt->closeCursor();

		// Passo 4: Excluir a coluna de ID original
		$DB->exec("ALTER TABLE `{$tabela}` DROP COLUMN `id`");

		// Passo 5: Renomear a nova coluna para o nome original
		$DB->exec("ALTER TABLE `{$tabela}` CHANGE COLUMN `novo_id` `id` {$idType} PRIMARY KEY");

		// Passo 6: Contar o número de registros para ajustar o valor de `AUTO_INCREMENT`
		$rowCount = $DB->query("SELECT COUNT(*) FROM `{$tabela}`")->fetchColumn();

		// Passo 7: Restaurar o AUTO_INCREMENT na coluna `id`
		$DB->exec("ALTER TABLE `{$tabela}` MODIFY COLUMN `id` {$idType} AUTO_INCREMENT");
		$DB->exec("ALTER TABLE `{$tabela}` AUTO_INCREMENT = " . ($rowCount + 1));

		// Passo 8: Registro de quem fez as alterações
		$DB->exec("UPDATE `{$tabela}` SET `qm` = {$logado_id}");

		// Confirma as alterações
		$DB->commit();
		
		return true; // Sucesso
	} catch (PDOException $e) {
		// Em caso de erro, desfaz as alterações
		if ($DB->inTransaction()) 
			$DB->rollBack();
		
		return $e->getMessage(); // Retorna a mensagem de erro
	}
}
