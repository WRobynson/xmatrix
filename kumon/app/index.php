<?php
	include("session.php");						//	dados da sessão
	include("header.php");						//	definiçÕes de cabeçalho PHP
	include("definicoes.php");					//	variáveis de ambiente e informações sobre o servidor
	
	/**
	 * Funcões específicas para o sistema
	 */
	include("config/functions.php");

	/**
	 * Conecta no banco usando PD e geranco a instância '$DB' e
	 * carrega funções específicas para o tratamento com a Base de Dados
	 */
	include("config/db_functions.php");

	//print_r2($_POST);

	// MENSAGEM PARA O USUARIO
	$Msg = null;

	if (isset($_POST["CLICOU"])) {
		$csrf_token = filter_input(INPUT_POST, "csrf_token");
		$CLICOU = filter_input(INPUT_POST, "CLICOU");

		switch($CLICOU) {
			case "NOVO_DIA" :
				$dia = filter_input(INPUT_POST, "dia");
				$qtde = filter_input(INPUT_POST, "qtde");

				//	obter o estágio e a última folha lançados
				$result = getSelect("SELECT `folha`, `estagio` FROM `t_desempenho` ORDER BY `dia` DESC LIMIT 1");

				$ult_est = $result[0]["estagio"];
				$ult_folha = $result[0]["folha"];

				$estagio = $ult_est;
				$folha = $ult_folha + $qtde;

				if ($folha > 200) {
					$estagio++;
					$folha = $folha - 200;
				}

				$dados_arr = ["t_desempenho", 
								["dia" => $dia, "qtde" => $qtde, "folha" => $folha, "estagio" => $estagio]
							];

				$resp = sqlInsert($dados_arr, $csrf_token);

				if ($resp != "F5") {
					if (is_numeric($resp)) {
						$Msg = shAlert("<b>Parabéns!</b> Você concluiu {$qtde} novas folhas.", "success", true, "return", "mb-3");
						_log("Adicionou novs folhas ({#$resp} em `t_desempenho`) [Dia = {$dia}; Folhas = {$qtde}]");
					}
					else {
						$Msg = shAlert("<b>ERRO</b>. Não foi possível adicionar novas folhas.", "danger", false, "return", "mb-3");
						_log_sql($resp[0], $resp[1], "Erro na tentativa de adicionar folhas concluídas.");
					}
				}

			break;
		

			case "SALVAR_META":
				$meta_dia = filter_input(INPUT_POST, "meta_dia");
				$estagio = filter_input(INPUT_POST, "estagio");
				$folha = filter_input(INPUT_POST, "folha");
				
				$valor = $estagio * 200 + $folha;

				$dados_arr = ["t_meta", 1, ["dia" => $meta_dia, "estagio" => $estagio, "folha" => $folha]];

				$resp = sqlUpdate($dados_arr, $csrf_token);

				if ($resp != "F5") {
					if (is_numeric($resp)) {
						$Msg = shAlert("<b>Sucesso!</b> Sua meta foi alterada!.", "success", true, "return", "mb-3");
						_log("atualizou a meta: [Dia = {$meta_dia}; Estágio: {$estagio}; Folhas = {$folha}]");
					}
					else {
						$Msg = shAlert("<b>ERRO</b>. Não foi possível atualizar a meta.", "danger", false, "return", "mb-3");
						_log_sql($resp[0], $resp[1], "Erro na tentativa de atualizar a meta.");
					}
				}
			break;
		}
	}

	$estagio_arr = [
		null, "A1", "A2", "B1", "B2", "C1", "C2", "D1", "D2", "E1", "E2", 
		"F1", "F2", "G1", "G2", "H1", "H2", "I1", "I2", "J1", "J2", "K1", 
		"K2", "L1", "L2", "M1", "M2", "N1", "N2", "O1", "O2", "P1", "P2", 
		"Q1", "Q2", "R1", "R2", "S1", "S2", "T1", "T2", "U1", "U2", "V1", 
		"V2", "W1", "W2", "X1", "X2"
	];

?>

<!DOCTYPE html>
<html lang="pt-BR"> <!-- Alterado para português -->
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="blue">
	<meta name="apple-touch-icon" content="logo/logo192.png">
	<title>Desempenho no Kumon</title>
	<script type="text/javascript" src="/lib/jquery/3.6.0/jquery.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/blockUI/2.7.0/jquery.blockUI.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/ui/1.13.2/jquery-ui.js"></script>
	<script type="text/javascript" src="/lib/popper/2.10.2/popper.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/5.1.3/bootstrap.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap-select/1.14.0-beta3/bootstrap-select.min.js"></script>
	
	<script type="text/javascript" src="config/script.js"></script>

	<link rel="manifest" href="/manifest.json">
	<link rel="stylesheet" type="text/css" href="config/reset.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-halflings.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-filetypes.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap/5.1.3/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap-select/1.14.0-beta3/bootstrap-select.min.css">
	<link rel="shortcut icon" type="image/x-icon" href="./logo/favicon.ico" />
	
	<link rel="stylesheet" type="text/css" href="config/estilos.css">

</head>
<body>
<div class='container'>
<h2 class='text-center mb-2'>Desempenho KUMON</h2>

<?php

$dow_arr = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

//	pegue o último registro
$result = getSelect("SELECT * FROM `t_desempenho` ORDER BY `dia` DESC LIMIT 1");

$ult_dia = $result[0]["dia"];
$folha_atual = $result[0]["folha"];
$estagio_atual = $result[0]["estagio"];



//$ult_dia = sqlResult("SELECT MAX(`dia`) `dia` FROM `t_desempenho`", "dia");
//echo $ult_dia;

if (! isValidDate($ult_dia, 'Y-m-d'))
	$px_dia = date('Y-m-d');	//	hoje
else
	$px_dia = date('Y-m-d', strtotime($ult_dia . ' +1 day'));

$dow_n = date('w', strtotime($px_dia));

$px_dia_sh = $dow_arr[$dow_n] . date(', d/m/Y', strtotime($px_dia));

$hoje = date('Y-m-d');	//	hoje

$bgc = ($px_dia < $hoje ? "bg-warning" : "bg-success text-white");

/**
 * Para impedir duplicação com F5
 * $csrf_token é um valor aleatório que é enviado via POST nos formulários.
 * Quando os dados são gravados, este valor é atribuído a $_SESSION['csrf_token']
 * Quando $_SESSION['csrf_token'] == $POST['csrf_token'], é pq a página foi recaregada (F5)
 */
$csrf_token = bin2hex(random_bytes(32));

//	OPTIONS da quantidade de folhas feitas

$op_folhas = null;

for ($i = 0; $i <= 10; $i++) {
	$sel = ($i == 5 ? "selected" : null);
	$op_folhas .= "<option value='$i' {$sel}>{$i}</option>";
}
			
echo "
	<div id='dataForm'>
	<form method='POST' action=''>
		<div class='form-group'>
			<h4 class='text-center'>Registro diário</h4>
			<input type='hidden' name='csrf_token' value='{$csrf_token}'>
			<div class='form-group'>
				<label for='day' class='form-label'>Data</label>
				<input type='text' id='day' class='form-control {$bgc}' value='{$px_dia_sh}' readonly>
				<input type='hidden' name='dia' value='{$px_dia}'>
			</div>
			<div class='form-group mb-2'>
				<label for='value' class='form-label'>Quantidade de Folhas</label>
				<select id='value' name='qtde' class='form-select'>{$op_folhas}</select>
			</div>
			<div class='form-group'>
				<button type='submit' class='btn btn-primary' name='CLICOU' value='NOVO_DIA'>Adicionar</button>
			</div>
		</div>
	</form>
	</div>
";

// Mensagem para o usuário
echo $Msg;

//	Formulário da META

/**
 * Obtenção dos dados da META
 */

$meta = getSelect("SELECT * FROM `v_meta` ORDER BY `id` DESC LIMIT 1");

if (! empty($meta)) {
	$meta_dia = $meta[0]["dia"];
	$meta_ts = $meta[0]["ts"];
	$meta_est = $meta[0]["estagio"];
	$meta_folha = $meta[0]["folha"];
	$meta_valor = $meta[0]["valor"];
}
else
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = $serie_meta = null;

$meta_dia = date('d M. Y',strtotime($meta_dia));

echo "
	<form>
	<h4 class='text-center'>Meta</h4>
		<p class='text-center'>No dia <b>{$meta_dia}</b>, eu quero <br>concluir a folha <b>{$meta_folha}</b> do estágio <b>{$estagio_arr[$meta_est]}</b>.</p>
		<p class='text-center text-danger'>Hoje estou na folha <b>{$folha_atual}</b> do estágio <b>{$estagio_arr[$estagio_atual]}</b>.</p>
		<div class='form-group'>
			<button type='button' class='btn btn-primary' onclick=\"window.location.href='/meta.php'\">Alterar</button>
		</div>
	</div>
";

echo "<button type='button' class='btn btn-success' onclick=\"window.location.href='/grafico.php'\">Gráfico</button>";

/**
 *  v_desempenho = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_desempenho` AS
	SELECT
		`t_desempenho`.`id` AS `id`,
		`t_desempenho`.`dia` AS `dia`,
		DATE_FORMAT(`t_desempenho`.`dia`, '%d/%m') AS `dia2`,
		UNIX_TIMESTAMP(`dia`)*1000 AS `ts`,
		`t_desempenho`.`qtde` AS `qtde`,
		`t_desempenho`.`folha` AS `folha`,
		`t_desempenho`.`estagio` AS `estagio`,
		(
			(
				(`t_desempenho`.`estagio` - 1) * 200
			) + `t_desempenho`.`folha`
		) AS `valor`
	FROM
		`t_desempenho`;
*/

/**
 *  v_meta = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_meta` AS
	SELECT
		id,
		dia,
		UNIX_TIMESTAMP(`dia`) * 1000 AS `ts`,
		estagio,
		folha,
		(((`estagio` - 1) * 200) + folha) AS `valor`
	FROM
		`t_meta`;
*/

?>
</div>
</body>
</html>
