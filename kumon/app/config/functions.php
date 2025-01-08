<?php

$diaSemana_arr = array('Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb');


/**
 * Elimina espaços e quebras de linhas em excesso na string ou array.
 *
 * Retira espaços no início e no fim da string, substitui múltiplos espaços por um único espaço e
 * remove quebras de linha excessivas. Se o parâmetro for um array, a função aplicará a limpeza
 * recursivamente a todos os elementos.
 *
 * @param string|array|null $input A string ou array a ser tratado. Pode ser null.
 * @return string|array|null A string ou array livre de espaços e quebras de linhas desnecessários, ou null se o input for vazio.
 */

 function trim2(string|null|array $input): string | array | null
 {
	if (empty($input)) 
		return null;
	
	if (is_string($input)) {
		// Remover espaços extras em strings
		$input = preg_replace("/[^\S\r\n]+/", " ", trim($input));		// retiro os espacos (preserva os lines breaks)
		$input = preg_replace("/(\r?\n){3,}/", "\n\n", $input);		// retiro o excesso de linhas em branco (para textarea)
		
		return $input;
	}
	elseif (is_array($input)) {
		// Se for um array, percorre e aplica a função recursiva
		foreach ($input as $key => $value) {
			$input[$key] = trim2($value);		//	considere "$input[trim($key)] = trim2($value);" para limpar os índices também...
		}
		return $input;		// Retorna o array modificado
	}
	return $input;			// Retorna o valor original se não for string nem array
}


/**
 * Mostra (ou retorna) um alerta em forma de mensagem
 * 
 * Pode escrever a mensagem ou retorná-la em uma variável
 * @param string $msg A mensagem a ser mostrada
 * @param string $tipo Seleciona a cor, de acordo com os padrões do bootstrap
 * @param bool $close Se mostra ou não o botão pra fechar a mensagem
 * @param string $type Se mostra o alerta (null) ou retorna pra uma variável ("return") 
 * @param string $classes Classes adicionais para a DIV com o alerta 
 * @return string DIV formatada com bootstrap
 */

function shAlert($msg, $tipo = "success", $close = true, $type = null, $classes = "mb-0")
{
	if ($close) {
		$bot = "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button>";
		$dismissible = "alert-dismissible";
	} else {
		$bot = null;
		$dismissible = null;
	}

	$output = "
		<div class='alert alert-{$tipo} {$dismissible} fade show text-center {$classes}' role='alert'>
		{$msg}{$bot}
		</div>";

	if ($type == null)
		echo $output;
	else
		return $output;
}

/**
 * Retorna o nome de uma variável
 * 
 * @param string $var Variável que se deseja pegar o nome 
 * @return string O nome da variável
 */

 function getVariableName($var)
 {
	 foreach ($GLOBALS as $varName => $value) {
		 // Melhorar a comparação para lidar com arrays e objetos
		 if (is_array($value) && is_array($var)) {
			 if ($value == $var) {
				 return $varName;
			 }
		 } elseif ($value === $var) {
			 return $varName;
		 }
	 }
	 return false;
 }

/**
 * Mostra o conteúdo de um array de forma amigável
 * 
 * @param array $arr Array a ser mostrado
 * @return string Conteúdo do array formatado
 */

function print_r2($arr, $var_name = null)
{
	if ($var_name == null)
		$var_name = getVariableName($arr);

	echo "<b>Conteúdo de \${$var_name}</b><br>";
	
	if ($var_name === false || ! is_array($arr)) {
		echo "Variável indefinida ou não é um Array!";
		return;
	}

	echo "<pre>";
	echo htmlspecialchars(print_r($arr, true));
	echo "</pre>";
}

/**
 * Pega o IP do cliente
 * 
 * @return string Endereço IP do cliente
 */

 function getUserIP()
 {
	 $client		=	@$_SERVER["HTTP_CLIENT_IP"];
	 $forward	=	@$_SERVER["HTTP_X_FORWARDED_FOR"];
	 $remote		=	$_SERVER["REMOTE_ADDR"];
 
	 if (filter_var($client, FILTER_VALIDATE_IP))
		 $IP = $client;
 
	 elseif (filter_var($forward, FILTER_VALIDATE_IP))
		 $IP = $forward;
 
	 else
		 $IP = $remote;
 
	 if (filter_var($IP, FILTER_VALIDATE_IP))
		 return $IP;
	 else
		 return "IP";
 }

/**
 * Registra um LOG do sistema
 * 
 * Grava a mensagem em arquivo
 * @param string $msg A mensaagem de LOG
 * @param int $tipo Tipo de log (0: Log de atividade; 1: Log de erro; 2: Log de acesso; 3: Log de alerta (segurança))
 */

function _log($msg, $tipo = 0)
{
	/*
		$tipo
				0	=>	Log de atividade
				1	=>	Log de erro
				2	=>	Log de acesso
				3	=>	Log de alerta (segurança)
				
		Arquivos de LOG definidos em 'definicoes.php'
	*/

	switch (true) {
		case preg_match('/1|erro|error/i', $tipo):
			$file = LOG_FILE_ERRO;
			break;

		case preg_match('/2|acesso|access/i', $tipo):
			$file = LOG_FILE_ACESSO;
			break;

		case preg_match('/3|alerta|alert/i', $tipo):
			$file = LOG_FILE_ALERTA;
			break;

		default:
			$file = LOG_FILE_ATIVIDADE;
	}

	$msg = preg_replace('~[[:cntrl:]]~', '', $msg); // remove all control chars (ex: \n \r)

	$datetime = new DateTime("now", new \DateTimeZone("UTC"));	//	ZULU
	$qdo = $datetime->format('Ymd_His.v\z');

	if (isset($_SESSION['LOGADO_NOME']))
		$u = $_SESSION['LOGADO_NOME'];
	else
		$u = "NO_LOGIN";

	$ip = getUserIP();

	$browser = $_SERVER['HTTP_USER_AGENT'];

	$quem = "{" . $u . " :: " . $ip . "}";
	
	//	se for log de acesso, registro o navegador
	if ($file == LOG_FILE_ACESSO)
		$quem = "{" . $u . " :: " . $ip . " :: " . $browser . "}";
	

	$log = sprintf("%s => %s. %s%s", $qdo, $quem, $msg, PHP_EOL);

	//	se o arquivo de LOG n existe, tento criar
	if (is_writable(DIR_LOG) && !file_exists($file)) {
		$arquivo = fopen($file, 'w');
	}

	if (is_writable($file)) {
		file_put_contents($file, $log, FILE_APPEND);	//	nova abordagem 28/05/22
	}
}

/**
 * LOG de tentativa de entrada direta nas páginas PHP
 * 
 * @param string $page A página tentando ser acessada
 */

function _log_ent_inv($page)
{
	_log("Tentativa de acesso direto em [{$page}]", 3);
}

/**
 * LOG de quando o valor de um campo não passa na validação (provável violação no HTML ou JS)
 * 
 * @param string $campo O campo a ser alterado
 * @param string $tab A tabela a qual o campo pertence
 */

function _log_campo_inv($campo, $tab = null)
{
	_log("Tentativa de registro de valor(es) inválido(s) {{$campo}} na tabela [{$tab}]", 3);
}

/**
 * LOG de quando quando há um erro de SQL
 * 
 * @param string $sql O SQL executado
 * @param string $erro O erro gerado pelo MySQL
 * @param string $msg A mensagem gravada no arquivo de LOG
 */

//	
function _log_sql($sql, $erro, $msg = "Erro na execução do SQL.")
{
	_log("{$msg}. SQL: [{$sql}]. Erro: [{$erro}]", 1);
}


/**
 * LOG de resposta a um comando shell
 * 
 * Transforma o Array com a saída da função 'exec()' em linhas de LOG
 * @param array $output_arr A saída do comando no shell
 * @param int $result Código do resultado do comando
 */

function _log_shell($output_arr, $result, $tipo = 0)
{
	_log("    Saída do comando (shell) executado:", $tipo);
	_log("        Código do resultado ($?): {$result}", $tipo);

	if (is_array($output_arr) && !empty($output_arr)) {
		if (count($output_arr) < 10)
			$digitos = 1;
		elseif (count($output_arr) < 100)
			$digitos = 2;
		else
			$digitos = 3;

		$i = 1;
		foreach ($output_arr as $linha) {
			$num = str_pad($i, $digitos, STR_PAD_LEFT);

			_log("        linha_{$num}: {$linha}", $tipo);

			$i++;
		}
	} else {
		_log("        Nada na saída do comando.", $tipo);
	}
}

/**
 * Retorna à página inicial em caso de perda de sessão
 * 
 * @return boolean
 * 
 *   - true: sessão válida (não perdeu)
 *   - false: perdeu a sessão
 */

function chkSession()
{
	if (! isset($_SESSION['LOGADO_NOME']) || ! isset($_SESSION['LOGADO_ID'])) {
		echo "<script>Menu('home');</script>";
		exit;
		return false;
	}

	return true;	
}

/**
 * Mostra uma dica sobre o preenchimento do campo
 * 
 * @param string $txt O texto explicativo
 * @return string O \<span\> estilizado
 */

function dicaCampo($txt)
{
	return "&emsp;<span class='halflings halflings-question-sign dica' data-bs-toggle='tooltip' data-bs-html='true' data-bs-placement='right' title='{$txt}'></span>";
}

/**
 * Ativar a validação de formulários
 * 
 * Função chamada ao final de arquivos AJAX que exibem formulários
 */

function formValidation()
{
	echo "
		<script language='javascript'>
			(function () {
			  'use strict'

			  // Fetch all the forms we want to apply custom Bootstrap validation styles to
			  var forms = document.querySelectorAll('.needs-validation')

			  // Loop over them and prevent submission
			  Array.prototype.slice.call(forms)
				.forEach(function (form) {
				  form.addEventListener('submit', function (event) {
					if (!form.checkValidity()) {
					  event.preventDefault()
					  event.stopPropagation()
					}

					form.classList.add('was-validated')
				  }, false)
				})
			})()
		</script>	
	";
}

/**
 * Exibe uma menssagem temporária de alerta
 * 
 * A mensagem aparece no canto superior direito
 * @param string $titulo O título da mensagem
 * @param string $msg O texto da mensagem
 * @param string $tipo O tipo de mensagem (bootstrab class)
 * @param int $tempo O tempo (em ms) que a mensagem ficará aparecendo
 * @return string A chamada JS (p_notify()) para mostrar a mensagem
 */

function PNotify($titulo, $msg, $tipo = "error", $tempo = 3000)
{
	//	/config/script.js
	echo "<script language='javascript'>p_notify('{$titulo}', '{$msg}', '{$tipo}', {$tempo});</script>";
}

/**
 * Retorna uma saudação conforme a hora do dia
 * 
 * Bom dia, boa tarde ou boa noite.
 * @return string Uma saudação
 */

function saudacao()
{
	$hora_atual = date('H');

	if ($hora_atual < 12) 
		return 'Bom dia';
	if ($hora_atual < 18) 
		return 'Boa tarde';
	return 'Boa noite';
}

/**
 * Formata uma string JSON
 * @param string $json_string JSON em uma única linha
 * @return string JSON
 */
function str2json($json_string) 
{
    // Decodifica o JSON para um array associativo
    $array = json_decode($json_string, true);

    if ($array === null) {
        // Se o JSON for inválido, retorna a string original
        return $json_string;
    }

    // Converte o array associativo formatado de volta para JSON com caracteres especiais não codificados
    return json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Validação de DATA
 * 
 * @param string $date_str A string com a data a ser testada
 * @param string $format O formato a ser testado
 * @param string $format_return O formato a ser retornado
 * @return false|string $data A data formatada 
 */

 function isValidDate($date_str, $format, $format_return = null)
 {
	 // Tentar criar o objeto DateTime a partir do formato 'd/m/y'
	 $date = DateTime::createFromFormat($format, $date_str);
	 
	 // Verificar se a data foi criada corretamente e se corresponde ao formato
	 if ($date && $date->format($format) === $date_str)	//	data válidada
		 if ( $format_return === null)
			 return true;
		 else
			 return $date->format($format_return);
	 else
		 return false;
 }