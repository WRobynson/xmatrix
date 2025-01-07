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
	<meta name="theme-color" content="#9BCCF0">
	<meta name="apple-touch-icon" content="logo/logo192.png">
	<title>Desempenho no Kumon</title>
	<script type="text/javascript" src="/lib/jquery/3.6.0/jquery.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/blockUI/2.7.0/jquery.blockUI.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/ui/1.13.2/jquery-ui.js"></script>
	<script type="text/javascript" src="/lib/popper/2.10.2/popper.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/5.1.3/bootstrap.min.js"></script>
	<script type="text/javascript" src="/lib/highcharts/10.1.0/code/highcharts.js"></script>
	
	<script type="text/javascript" src="config/script.js"></script>

	<link rel="manifest" href="/manifest.json">
	<link rel="stylesheet" type="text/css" href="config/reset.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-halflings.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-filetypes.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap/5.1.3/bootstrap.min.css">
	<link rel="shortcut icon" type="image/x-icon" href="./logo/favicon.ico" />
	
	<link rel="stylesheet" type="text/css" href="config/estilos.css">

</head>
<body>
<div class='container'>
<h2 class='text-center mb-2'>Desempenho KUMON</h2>

<?php

/**
 * Para impedir duplicação com F5
 * $csrf_token é um valor aleatório que é enviado via POST nos formulários.
 * Quando os dados são gravados, este valor é atribuído a $_SESSION['csrf_token']
 * Quando $_SESSION['csrf_token'] == $POST['csrf_token'], é pq a página foi recaregada (F5)
 */
$csrf_token = bin2hex(random_bytes(32));

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
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = null;

//	pegue o estágio atual
$estagio_atual = sqlResult("SELECT MAX(`estagio`) `est` FROM `t_desempenho`", "est");

//	OPTIONS dos estágios a serem alcançados

for ($i = $estagio_atual + 1; $i <= count($estagio_arr) - 1; $i++) {
	$op_est = null;

	$sel = ($meta_est == $i ? "selected" : null);

	$op_estagio .= "<option value='$i' {$sel}>{$estagio_arr[$i]}</option>";
}

$op_folha = null;

for ($i = 10; $i <= 200; $i+=10) {
	$sel_folha = null;

	$sel = ($meta_folha == $i ? "selected" : null);

	$op_folha .= "<option value='$i' {$sel}>{$i}</option>";
}

echo "
	<div id='metaForm'>
	<form method='POST' action='index.php'>
		<div class='form-group'>
			<h4 class='text-center'>Definir Meta</h4>
			<input type='hidden' name='csrf_token' value='{$csrf_token}'>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='meta_dia' class='form-label'>Data</label>
					<input type='date' id='meta_dia' name='meta_dia' class='form-control' value='{$meta_dia}'>
				</div>
			</div>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='estagio' class='form-label'>Estágio</label>
					<select id='estagio' name='estagio' class='form-select'>{$op_estagio}</select>
				</div>
			</div>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='folha' class='form-label'>Folha</label>
					<select id='folha' name='folha' class='form-select'>{$op_folha}</select>
				</div>
			</div>
			<div class='form-group'>
				<button type='submit' class='btn btn-success' name='CLICOU' value='SALVAR_META'>Salvar</button>
				<button type='button' class='btn btn-primary float-end' onclick=\"window.location.href='/'\">Voltar</button>
			</div>
		</div>
	</form>
	</div>
";

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
