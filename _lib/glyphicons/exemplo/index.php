<?php
session_name("glyphicons");
session_start();

//	ENDEREÇO DO SERVIDOR (IP ou HOSTNAME)
define("SERVER_END", $_SERVER["SERVER_NAME"]);

// Tipos de fontes
$fontes = array();

$fontes[0] = "Filetypes";
$fontes[1] = "Halflings";
$fontes[2] = "Regular";
$fontes[3] = "Social";

//	fonte mostrada inicialmente
$fonte_preferencial = strtolower($fontes[1]);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="robots" content="noindex, nofollow">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<title>Glyphicons</title>

	<script type='text/javascript' src='/lib/jquery/3.6.0/jquery.min.js' charset='utf-8'></script>
	<script type='text/javascript' src='/lib/popper/2.10.2/popper.min.js' charset='utf-8'></script>
    <script type='text/javascript' src='/lib/bootstrap/5.1.3/bootstrap.min.js' charset='utf-8'></script>
    <script type='text/javascript' src='/lib/pnotify/3.2.0/pnotify.custom.min.js' charset='utf-8'></script>
    <script type='text/javascript' src='/lib/bootbox/5.5.2/bootbox.min.js' charset='utf-8'></script>
	
	<link rel='stylesheet' type='text/css' href='/lib/glyphicons/glyphicons-halflings.css'>
	<link rel='stylesheet' type='text/css' href='/lib/glyphicons/glyphicons-regular.css'>
	<link rel='stylesheet' type='text/css' href='/lib/glyphicons/glyphicons-filetypes.css'>
	<link rel='stylesheet' type='text/css' href='/lib/glyphicons/glyphicons-social.css'>
	<link rel='stylesheet' type='text/css' href='/lib/bootstrap/5.1.3/bootstrap.min.css'>
	<link rel='stylesheet' type='text/css' href='/lib/pnotify/3.2.0/pnotify.custom.min.css'>

	<style>
	.char {
		text-align: center;
		cursor: pointer;
		margin: 4px;
		width: 30px;
		display: inline-block;
		font-size: 14px;
	}
	#container {
		float: left;
		padding: 30px; 
		background: #FAFAFA; 
		width: 650px;
	}
	#exemplo {
		float: left;
		padding: 30px;
	}
	#code {
		font-family: "Courier New";
		font-size: 12px;
		padding: 5px 5px 5px 15px;
		background: #efefef;
		margin: 20px 0 20px 0;
	}
	#font {
		font-weight: bold;
	}
	#picture {
		font-weight: bold;
		font-size: 18px;
	}
	#filtro {
		background-image: url('lupa.png');
		background-position: 10px 7px;
		background-repeat: no-repeat;
		background-size: 25px;
		padding: 0 20px 0 40px;
	}
</style>
	
	<script>
	function chChar(char) {
		//	ex: char = halflings halflings-zoom-in
		$('#font').text(char);
		
		//	pegar só o nome da fonte
		var palavras = char.split("-");
		tam = palavras.length;
		
		var fig_name = "";
		for (i = 1; i < tam; i++) {
			fig_name += palavras[i]+" ";
		}
		//	ex: fig_name = zoom in
		
		var pic = "<p><span>"+fig_name+"</span></p><p><span class='"+char+"' style='font-size: 50px;'></span></p>";
		$('#picture').html(pic);
	}
	</script>
</head>
<body>

<div id='container'>

<?php
/*
Sobre as variáveis

$fontes		-	array com os tipos de fontes (nome como mostrado no SELECT)
$FONTE		=	é o nome da variável do campo SELECT
$fonte		=	é a fonte selecionada (em caixa baixa)
$Fonte		=	é a fonte selecionada (nome original)
 
*/

if (isset($_POST['FONTE'])) $FONTE = $_POST['FONTE']; else $FONTE = $fonte_preferencial;

$Options = null;

foreach ($fontes as $Fonte) {
	$fonte = strtolower($Fonte);
	$sel = ($fonte == $FONTE ? "selected" : "");
	$Options .= "<option value='{$fonte}' $sel>{$Fonte}</option>";
}
?>

<form name='' action='' method='POST' onkeypress='if(event.keyCode==13) return false;'>
<div class='form-group'>
	<div id='select' class='input-group' style='padding: 15px; background: #F2F2F2;'>
		<select name='FONTE' class='selectpicker form-control' onChange='this.form.submit()'>
			<?php echo $Options;?>
		</select>&nbsp;
		<input type='text' id='filtro' class='busca form-control input-insc' placeholder='Digite algo para filtrar...' />
	</div><!-- select -->
</div>
</form>


<center>
<div id='figuras'>	
<?php

$arq = "../glyphicons-{$FONTE}.css";
//	echo "arquivo: $arq <br /> fonte: $FONTE";


// Começa a leitura do arquivo de ".css"
$linhas = file($arq);

//	para cada linha do arquivo...
foreach($linhas as $linha => $conteudo){
	//echo "linha - ";
	// remove all control chars (ex: \n \r);
	$conteudo = preg_replace('~[[:cntrl:]]~', '', $conteudo); 
	
	//	se a linha do arquivo se refere a uma fonte,
	//	ela começa com ".halflings-" ou ".filetypes-" ou ".regular-" ou ".social-"
	
	$patern = "/^\.{$FONTE}-/";
	if (preg_match($patern, $conteudo)) {//echo "é linha de fonte: $conteudo<br>";
		
		preg_match('@^(?:\.)?([^:]+)@i',$conteudo, $matches);	// retorna o nome da fonte - o q estiver entre "." e ":" em $conteudo
		
		$char = $matches[1];	// o caracter (a figurinha)
		
		//echo "nome do caracter: {$char}<br>";
		
		$tooltip = "<span class='{$FONTE} {$char}'></span>";
		
		if ($FONTE == "filetypes") $sty = "style='font-size: 40px;'";
		else $sty = null;
		
		//	printo o caracter
		echo "<div class='char' $sty title=\"{$tooltip}\" onClick=\"chChar('{$FONTE} {$char}')\"><span class='{$FONTE} {$char}' title=\"{$tooltip}\"></span></div>";
	}
}

?>
</div><!-- figuras -->
</div><!-- container -->

<script languge='javascript'>
	$('#filtro').keyup(function () {
		var value = $(this).val().toLowerCase().trim();
		$('#figuras span').filter(function() {

			//	pegar só o nome da fonte
			var fonte = $(this).attr('class');

			var palavras = fonte.split("-");
			tam = palavras.length;
			
			var fig_name = "";
			for (i = 1; i < tam; i++) {
				fig_name += palavras[i]+" ";
			}
			//	ex: fig_name = zoom in

			$(this).toggle(fig_name.indexOf(value) > -1)
		});
	});
</script>

<div id='exemplo'>
Como usar: <br /><br />
Baixe os arquivos (CSS) das fontes juntamente com a pasta "\fonts".

<div id='code'>
<?php 
	echo htmlspecialchars("<head>")."<br />";
	echo htmlspecialchars("<link rel='stylesheet' href='./lib/glyphicons/glyphicons-{$FONTE}.css'>")."<br />";
	echo htmlspecialchars("</head>")."<br /><br />";
	echo htmlspecialchars("<html>")."<br />";
	echo htmlspecialchars("<span class='")."<span id='font'>clique numa imagem</span>".htmlspecialchars("'></span>")."<br />";
	echo htmlspecialchars("</html>");
?>
</div>
<div id='picture'>
</div>

</div><!-- exemplo -->

</body>
</html>