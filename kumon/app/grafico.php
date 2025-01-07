<?php
	include("session.php");						//	dados da sessão
	include("header.php");						//	definiçÕes de cabeçalho PHP
	
	/**
	 * Funcões específicas para o sistema
	 */

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
	<link rel="stylesheet" type="text/css" href="/lib/bootstrap/5.1.3/bootstrap.min.css">
	<link rel="shortcut icon" type="image/x-icon" href="./logo/favicon.ico" />
	
	<link rel="stylesheet" type="text/css" href="config/estilos.css">

</head>
<body>
<div class='container'>
<h2 class='text-center mb-2'>Desempenho KUMON</h2>

<?php

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

	//	só interessa os valores do dia primeiro de cada mês
	if ($id == $ult_id || date('d', strtotime($dia) == '01'))
		$dados_desempenho .= "[$ts, $valor],";

	$estagio_n = $estagio_arr[$estagio];

}

echo "
	<div class='chart-container' style='border: 1px solid red'>
		<div id='chartContainer'></div>
	</div>";

echo "<button type='button' class='btn btn-primary mt-2' onclick=\"window.location.href='/'\">Voltar</button>";


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
	credits: {
        enabled: false // Desativa os créditos do Highcharts
    },
	chart: {
		type: 'line'  // Gráfico de linha
	},
	title: {
		text: 'Em busca da meta'
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