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
						$Msg = shAlert("<b>Parabéns!</b> Você concluiu {$qtde} novas folhas.", "success", true, "return", "mb-1");
						_log("Adicionou novs folhas ({#$resp} em `t_desempenho`) [Dia = {$dia}; Folhas = {$qtde}]");
					}
					else {
						$Msg = shAlert("<b>ERRO</b>. Não foi possível adicionar novas folhas.", "danger", false, "return", "mb-1");
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
						$Msg = shAlert("<b>Sucesso!</b> Sua meta foi alterada!.", "success", true, "return", "mb-1");
						_log("atualizou a meta: [Dia = {$meta_dia}; Estágio: {$estagio}; Folhas = {$folha}]");
					}
					else {
						$Msg = shAlert("<b>ERRO</b>. Não foi possível atualizar a meta.", "danger", false, "return", "mb-1");
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
	<title>Desempenho no Kumon</title>
	<script type="text/javascript" src="/lib/jquery/3.6.0/jquery.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/blockUI/2.7.0/jquery.blockUI.min.js"></script>
	<script type="text/javascript" src="/lib/jquery/ui/1.13.2/jquery-ui.js"></script>
	<script type="text/javascript" src="/lib/popper/2.10.2/popper.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap/5.1.3/bootstrap.min.js"></script>
	<script type="text/javascript" src="/lib/bootstrap-select/1.14.0-beta3/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="/lib/pnotify/3.2.0/pnotify.js"></script>
	<script type="text/javascript" src="/lib/bootbox/6.0.0/bootbox.min.js"></script>
	<script type="text/javascript" src="/lib/datatables/1.11.5/dataTables.min.js"></script>
	<script type="text/javascript" src="/lib/datatables/1.11.5/dataTables.bootstrap5.min.js"></script>
	<script type="text/javascript" src="/lib/highcharts/10.1.0/code/highcharts.js"></script>
	
	<script type="text/javascript" src="config/script.js"></script>

	<link rel="stylesheet" type="text/css" href="config/reset.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-halflings.css">
	<link rel="stylesheet" type="text/css" href="/lib/glyphicons/glyphicons-filetypes.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap/5.1.3/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap-select/1.14.0-beta3/bootstrap-select.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/pnotify/3.2.0/pnotify.custom.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/datatables/1.11.5/dataTables.min.css">
	<link rel="stylesheet" type="text/css" href="/lib/datatables/1.11.5/dataTables.bootstrap5.min.css">
	
	<link rel="stylesheet" type="text/css" href="config/estilos.css">

</head>
<body>

<?php

echo "<div class='container'>
		<div class='row'>
		<div class='col-3'>";

$dow_arr = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"];

//	pegue o último dia registrado
$ult_dia = sqlResult("SELECT MAX(`dia`) `dia` FROM `t_desempenho`", "dia");
//echo $ult_dia;

if (! isValidDate($ult_dia, 'Y-m-d'))
	$px_dia = date('Y-m-d');	//	hoje
else
	$px_dia = date('Y-m-d', strtotime($ult_dia . ' +1 day'));

$dow_n = date('w',  strtotime($px_dia));

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
$csrf_token2 = bin2hex(random_bytes(32));

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

	$serie_meta = "
		{
			name: 'Meta',
			type: 'scatter', // Define que é um ponto isolado
			data: [
				{
					x: {$meta_ts},
					y: {$meta_valor},
					marker: {
						symbol: 'circle',
						radius: 5,
						fillColor: 'red'
					},
					dataLabels: {
						enabled: true, 
						format: 'META', 
						style: {
							color: 'red',
							fontWeight: 'bold'
						}
					}
				}
			],
			color: 'red',
			tooltip: {
				pointFormatter: function () {
					const estagio = calcularEstagio(this.y);
					const dataFormatada = Highcharts.dateFormat('%d/%b', this.x);
					return `
						Dia: \${dataFormatada}<br>
						Estágio: \${estagio.Atual}<br>
						Folha: \${estagio.Folhas}
					`;
				}
			}
		}
	";
}
else
	$meta_dia = $meta_ts = $meta_est =  $meta_folha =  $meta_valor = $serie_meta = null;

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
	<form method='POST' action=''>
		<div class='form-group'>
			<h4 class='text-center'>Meta</h4>
			<input type='hidden' name='csrf_token' value='{$csrf_token2}'>
			<div class='form-group row mb-2'>
				<div class='form-group'>
					<label for='meta_dia' class='form-label'>Data</label>
					<input type='date' id='meta_dia' name='meta_dia' class='form-control' value='{$meta_dia}'>
				</div>
			</div>
			<div class='form-group row mb-2'>
				<div class='form-group col-md-6'>
					<label for='estagio' class='form-label'>Estágio</label>
					<select id='estagio' name='estagio' class='form-select'>{$op_estagio}</select>
				</div>
				<div class='form-group col-md-6'>
					<label for='folha' class='form-label'>Folha</label>
					<select id='folha' name='folha' class='form-select'>{$op_folha}</select>
				</div>
			</div>
			<div class='form-group'>
				<button type='submit' class='btn btn-primary' name='CLICOU' value='SALVAR_META'>Salvar</button>
			</div>
		</div>
	</form>
	</div>
";

//	Tabela para exibir dados

$tab_av_diario = "
	<div class='table-container'>
		<h2>Avanço diário</h2>
		<table class='table table-sm table-striped table-bordered'>
			<thead>
				<tr>
					<th>Dia</th>
					<th>Est</th>
					<th>Qtde</th>
					<th>Folha</th>
				</tr>
			</thead>
			<tbody id='dataTable'></tbody>";

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

/**
 * v_series = 
 * 
	CREATE OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_series` AS
	SELECT
		id,
		legenda,
		dia_ini,
		UNIX_TIMESTAMP(dia_ini) * 1000 ts_ini,
		folha_ini,
		estagio_ini,
		estagio_ini * 200 + folha_ini valor_ini,
		dia_fim,
		UNIX_TIMESTAMP(dia_fim) * 1000 ts_fim,
		estagio_fim,
		estagio_fim * 200 + folha_fim valor_fim,
		cor,
		estilo
	FROM
		`t_serires`
	WHERE
		mostrar = 1
	ORDER BY
		id;
*/

/**
 * Construção das séries definidas em t_series
 */

$result = getSelect("SELECT * FROM `v_series`");

$series = null;

foreach ($result as $linha) {
	$legenda = $linha["legenda"];
	$ts_ini = $linha["ts_ini"];
	$val_ini = $linha["valor_ini"];
	$ts_fim = $linha["ts_fim"];
	$val_fim = $linha["valor_fim"];
	$cor = $linha["cor"];
	$estilo = $linha["estilo"];

	$series .= "
		{
			name: '{$legenda}',
			data: [
				[{$ts_ini}, {$val_ini}],
				[{$ts_fim}, {$val_fim}],
			],
			color: '{$cor}',
			dashStyle: '{$estilo}',
			marker: {
				enabled: false
			}
		},
	";

}

//	FIM DA CONSTRUÇÃO DAS SERIES


//	peque o ID do primeiro registro
$pri_id = sqlResult("SELECT MIN(`id`) AS `pri_id` FROM `t_desempenho`","pri_id");

//	peque o ID do último registro
$ult_id = sqlResult("SELECT MAX(`id`) AS `ult_id` FROM `t_desempenho`","ult_id");

$sql = "SELECT * FROM `v_desempenho`";

$result = getSelect($sql);

$TR = null;
$dados_desempenho = null;

foreach ($result as $linha) {
	$id = $linha["id"];
	$dia = $linha["dia"];
	$dia2 = $linha["dia2"];
	$ts = $linha["ts"];         //  TimeStamp do dia
	$qtde = $linha["qtde"];
	$folha = $linha["folha"];
	$estagio = $linha["estagio"];
	$valor = $linha["valor"];

	if ($id == $ult_id || date('d', strtotime($dia)) == '01')
		$dados_desempenho .= "[$ts, $valor],";

	$estagio_n = $estagio_arr[$estagio];

	$TR .= "<tr><td>{$dia2}</td><td>{$estagio_n}</td><td>{$qtde}</td><td>{$folha}</td></tr>";
}

$tab_av_diario .= "{$TR}</table></div>";

echo "</div>";	//	.col-3

echo "
		<div class='col-9'>
			{$Msg}
			<div class='chart-container' style='border: 1px solid red'>
				<div id='chartContainer'></div>
			</div>
		</div>
	</div><!-- .row-->";


?>
</div>
</body>
</html>





<script>

const estagios = [
	"A1", "A2", "B1", "B2", "C1", "C2", "D1", "D2", "E1", "E2", 
	"F1", "F2", "G1", "G2", "H1", "H2", "I1", "I2", "J1", "J2",
	"K1", "K2", "L1", "L2", "M1", "M2", "N1", "N2", "O1", "O2", 
	"P1", "P2", "Q1", "Q2", "R1", "R2", "S1", "S2", "T1", "T2",
	"U1", "U2", "V1", "V2", "W1", "W2", "X1", "X2", "Y1", "Y2", 
	"Z1", "Z2"
];

function calcularEstagio(folhas) {
	const folhasPorEstagio = 200; // Cada estágio tem 200 folhas

	const totalEstagios = estagios.length;
	const folhasConcluidas = Math.floor(folhas / folhasPorEstagio); // Quantos estágios completos
	const Folhas = folhas % folhasPorEstagio; // Folhas no estágio atual

	const Concluido = folhasConcluidas > 0 && folhasConcluidas <= totalEstagios
		? estagios[folhasConcluidas - 1]
		: null;

	const Atual = folhasConcluidas < totalEstagios
		? estagios[folhasConcluidas]
		: "Finalizado"; // Considera que não há mais estágios após o último

	return {
		Concluido: Concluido || "Nenhum",
		Atual,
		Folhas
	};
}

/*
// Exemplo de uso
const estagio = calcularEstagio(2400);
console.log("Estágio concluído:", estagio.Concluido);
console.log("Estágio atual:", estagio.Atual);
console.log("Folhas no estágio atual:", estagio.Folhas);
*/


Highcharts.setOptions({
	lang: {
		months: [
			'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
			'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
		],
		weekdays: [
			'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
			'Quinta-feira', 'Sexta-feira', 'Sábado'
		],
		shortMonths: [
			'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 
			'jul', 'ago', 'set', 'out', 'nov', 'dez'
		],
		decimalPoint: ',',
		thousandsSep: '.'
	}
});

Highcharts.chart('chartContainer', {
	chart: {
		type: 'line'  // Gráfico de linha
	},
	title: {
		text: 'Desempenho Diário'
	},
	subtitle: {
		text: 'Aluna: <b>Sofia Robynson</b> (Língua Pátria)'
	},
	xAxis: {
		title: {
			text: 'Meses'  // Título do eixo X
		},
		type: 'datetime',
		tickInterval: 30 * 24 * 3600 * 1000,  // Intervalo de 1 mês em milissegundos
		labels: {
			formatter: function() {
				// Formatação da data para exibir mês e ano
				return Highcharts.dateFormat('%b/%y', this.value);  // Exemplo: "Sep 24"
			},
			x: 0,  // Alinhamento horizontal do texto (0 = centralizado)
			style: {
				textAlign: 'center'  // Garante que o texto esteja centralizado
			}
		},
		// min: Date.UTC(2024, 8, 1),  // Data mínima (01/09/2024)
		//max: Date.UTC(2025, 5, 30), // Data máxima (30/06/2025)
		gridLineWidth: 1,  // Linha de grade visível
		gridLineDashStyle: 'Dash', // Estilo da linha de grade (opcional)
	},
	yAxis: {
		title: {
			text: 'Estágio'  // Título do eixo Y
		},
		tickInterval: 200, // Define o intervalo fixo no eixo Y
		//min: 0 // Opcional: Define o valor mínimo no eixo Y
		labels: {
			formatter: function () {
				const index = Math.floor(this.value / 200);
				return estagios[index] || '?'; // Retorna o estágio correspondente ou vazio se o valor exceder a lista
			},
			style: {
				fontFamily: 'monospace', // Define a fonte como monoespaçada
				fontSize: '10px'        // Define o tamanho da fonte (opcional)
			}
		}
	},
	tooltip: {
		shared: true,
		crosshairs: true,
		valueSuffix: ' folhas'
	},
	series: [
		<?php echo $series;?>
		//	Série do desepenho 
		{
			name: 'Desempenho',
			data: [
				<?php echo $dados_desempenho; ?>,  // Dados vindo do PHP
			],
			color: '#f45b5b',
			tooltip: {
				headerFormat: '', // Remove o cabeçalho padrão
				pointFormatter: function () {
					const estagio = calcularEstagio(this.y);
					const dataFormatada = Highcharts.dateFormat('%d/%b', this.x);
					return `
						<b>${this.series.name}</b><br>
						Dia: ${dataFormatada}<br>
						Estágio: ${estagio.Atual}<br>
						Folhas: ${estagio.Folhas}
					`;
				}
			}
		},
		<?php echo $serie_meta;?>,
	]
});



</script>