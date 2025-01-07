var DEBUG = false;
// DEBUG = true;

if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/service-worker.js').then(() => {
	//console.log('Service Worker registrado com sucesso.');
	}).catch((error) => {
	console.log('Erro ao registrar o Service Worker:', error);
	});
}

////////////////////////////////////////////////////////////////////////////////
/* TRIM */
////////////////////////////////////////////////////////////////////////////////
// serve para excluir os espacos em excesso da string

String.prototype.trim = function() {
	var trim1 = this.replace(/^\s+|\s+$/g, "");  // elimina espacos antes e depois
	return trim1.replace(/\s+/g, " "); // elimina espacos no meio
}
////////////////////////////////////////////////////////////////////////////////

function Eh_par(numero){
   	var resto = numero % 2
   	if (resto == 0)
      	 return true
   	else
      	 return false
}

/**
 * Máscara para campos numéricos
 * --
 * @param mask string Máscara
 * @param campo O elemento input
 * @param k char Tecla pressionada
 * 
 *   ex: onKeyPress="return Mask('DD.DDD-DD',this,event)";
 */

function Mask(mask, campo, K)
{
	if(window.event) var key = K.keyCode;
	else var key = K.which;

	var TypeOfMask = "DBIHXZ";
	/*
		D - Só dígitos (0-9)
		B - Só Binário (0-1)
		I - Só Digitos, exceto o zero (1-9)
		H - Só caracteres Hexadecimais (0-F)
		X - Só letras
		Z - Letras e Numeros
	*/
	var B = ((key>47)&&(key<50));
	var D = ((key>47)&&(key<58));
	var I = ((key>48)&&(key<58));
	var H = (((key>47)&&(key<58))||((key>64)&&(key<71))||((key>96)&&(key<103)));
	var X = (((key>96)&&(key<123))||((key>64)&&(key<91)));
	var Z = (((key>47)&&(key<58))||((key>96)&&(key<123))||((key>64)&&(key<91)));

	var allowkey = {};
		allowkey["B"] = B;
		allowkey["D"] = D;
		allowkey["I"] = I;
		allowkey["H"] = H;
		allowkey["X"] = X;
		allowkey["Z"] = Z;

	var tam = campo.value.length; // tamanho do texto digitado no campo

	if (tam >= mask.length) return false;

	if (TypeOfMask.indexOf(mask[tam])>=0) {
		return allowkey[mask[tam]];
	}
	else {   // acrescenta o caracter
		campo.value = campo.value+mask[tam];
		return allowkey[mask[tam+1]];
	}
}

function forgotPasswd(){
	var msg = "Utilize os mesmos <b><i>usuário</b></i> e <b><i>senha</b></i> usados para acessar a rede do CINDACTA I.";
	msg += "<br/><br/>";
	msg += "Caso não se lembre, favor entrar em contato com o HELPDESK (ramal 8545).";
	
	new PNotify({
		type: 'info',
		title: 'Autenticação',
		text: msg,
		icon: 'halflings halflings-info-sign',
		styling: 'bootstrap3'
	});
}

/*
	Para ativar os ToolTips
	Não funciona para elementos oriundos de AJAX. Para estes caso, deve ser carregado no JS que chama o AJAX.
*/
$(function () {
	$('[data-bs-toggle="tooltip"]').tooltip()
})

/*
	desativa o SUBMIT com o pressionar de ENTER
*/
$('form input:not([type="submit"])').keydown(function (e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        return false;
    }
});


/**
 *	Ajusta a tabela usando DataTables
 * --
 *	@param tab string é o id da tabela (com o identificador da propriedade - id(#), classe(.) ou simplesmente o nome)
 *	@param pl int (Page Length) é a qtde de registros por página (pl = 0 : sem paginação)
 *	@param sw in (Select Width) é a largura do SELECT com a qtde de registros por página
*/
function datatable(tab, pl=25, sw=50) 
{
    /*
	 * 09/12/24 - sugestão do ChatoGPT
	 * destrói qualquer instância exixtente (evita duplicações)
	 */

	if ($.fn.DataTable.isDataTable(tab)) {
		$(tab).DataTable().destroy();
	}

	if (pl == 0) {
		paging = false;
		pl = 1000;
	}
	else
		paging = true;
	
	$(tab).DataTable({
        "paging":		paging,
		"ordering":		false,
		"info":     	true,
		"pageLength":	pl,
		"autoWidth": false,		// Desativa o ajuste automático de largura
		"language": {
            "lengthMenu":	"Mostrando _MENU_ registros por página",
            "zeroRecords":	"Nada encontrado - lamento",
            "info": 		"Página _PAGE_ de _PAGES_",
            "infoEmpty": 	"Sem registros disponíveis",
            "infoFiltered": "(filtrado de um total de _MAX_ registros)",
			"thousands":      ".",
			"loadingRecords": "Carregando...",
			"processing":     "Processando...",
			"search":         "Filtro:",
			"zeroRecords":    "Sem correspondência no filtro",
			"paginate": {
				"first":      "Primeira",
				"last":       "Última",
				"next":       "Próxima",
				"previous":   "Anterior"
			}
        }
	});

	if (sw != 0)
		$("select[name$='_length']").width(sw);
}

function p_notify(titulo, msg, tipo="error", tempo=3000) {
	//console.log(msg);
	
	if (tipo == "notice")
		ico = "halflings halflings-exclamation-sign";
	else if (tipo == "info")
		ico = "halflings halflings-info-sign";
	else if (tipo == "success")
		ico = "halflings halflings-ok-sign";
	else if (tipo == "error")
		ico = "halflings halflings-remove-sign";
	
	if (tempo == 0)
		hide = false;
	else
		hide = true;
	
	$(document).ready(function() {
		new PNotify({ 
			type: tipo,
			title: titulo,
			text: msg,
			delay: tempo,
			hide: hide 
		});	
	});
}

/**
 * Abre um MODAL com todos os contatos
 * @return void
 */
function shContatos()
{
	$.ajax({
		url     : "ajax/contatos.ajax.php",
		type    : "post",
		data    : {
			validate: "YES"	//	para validar a chamada da página AJAX
		},
		success: function(html){ 
			if (html == "session_error")
				window.location.reload(true);
			else {
				$("#cttModal").html(html);
				$('#cttModal').modal('show');

				datatable("#tab_ctt", 10);
			}
		},
		error: function(html){
			new PNotify({ 
				title: 'Ooops!',
				text: 'Infelizmente, não foi possível executar a ação solicitada.',
				type: 'error',
				styling: 'bootstrap3',
				animation: "fade",
				delay: 3000
			});
		}
	});

}

/**
 * Abre um MODAL com uma imagem
 * @param string filename
 */
function shImage(filename)
{
	$.ajax({
		url     : "ajax/image.ajax.php",
		type    : "post",
		data    : {
			validate: "YES",	//	para validar a chamada da página AJAX
			filename: filename
		},
		success: function(html){ 
			$("#cttModal").html(html);		//	usa o mesmo modal dos contatos
			$('#cttModal').modal('show');
		},
		error: function(html){
			new PNotify({ 
				title: 'Ooops!',
				text: 'Infelizmente, não foi possível executar a ação solicitada.',
				type: 'error',
				styling: 'bootstrap3',
				animation: "fade",
				delay: 3000
			});
		}
	});
}

function highlight(word, text, className = 'destak') {
	// Função para substituir caracteres acentuados
	const substituirAcentos = (str) => {
		const acentos = ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'];
		const semAcentos = ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'];
		return str.replace(new RegExp(acentos.join('|'), 'g'), (match) => semAcentos[acentos.indexOf(match)]);
	};

	// Substituir acentuação da palavra e do texto
	const wordSemAcentos = substituirAcentos(word.toLowerCase());
	const textSemAcentos = substituirAcentos(text.toLowerCase());

	// Encontrar todas as ocorrências da palavra ignorando a capitalização
	const positions = [];
	let pos = textSemAcentos.indexOf(wordSemAcentos);

	while (pos !== -1) {
		positions.push(pos);
		pos = textSemAcentos.indexOf(wordSemAcentos, pos + 1);
	}

	// Ordenar as posições em ordem decrescente
	positions.sort((a, b) => b - a);

	// Substituir a palavra no texto pelo HTML desejado
	positions.forEach((pos) => {
		// Inserir a tag <span class='destak'>
		text = text.slice(0, pos) + `<span class='${className}'>` + text.slice(pos, pos + word.length) + `</span>` + text.slice(pos + word.length);
	});

	return text;
}

/**
 * Constrói um select dinamicamente de acordo com a opção selecionada num select principal
 * 
 * @param string select_principal ID do select principal
 * @param array selects_afetados Os IDs (html) dos selects afetados (['id1', 'id2'])
 * @param string opc O que considerar na comparação
 * 		- 'valor' - considera o valor do OPTION selecionado
 * 		- 'texto' - considera o texto do OPTION selecionado
 * @return void
 */

function adjOptions(select_principal, selects_afetados, opc = "valor") {
	//	melhorada pelo chatGPT
	let selecionado;

	// Obter o valor ou o texto do select principal
	if (opc === "texto" || opc === "text") {
		selecionado = $(select_principal).find('option:selected').text();
	} else {
		selecionado = $(select_principal).val();
	}
	
	// Prepare o texto a ser comparado (entre colchetes)
	selecionado = "[" + selecionado + "]";

	// Iterar sobre os selects afetados
	selects_afetados.forEach(function(select_id_afetado) {
		let $select = $("#" + select_id_afetado);  // Cachear o seletor jQuery para eficiência
		
		// Ocultar todas as opções e limpar seleção
		$select.find("option").hide();  // Esconde todas as opções

		// Mostrar as opções correspondentes com base no valor selecionado
		if (selecionado !== "") {
			$select.find("option[data-tag*='" + selecionado + "']").show();  // Mostra as correspondentes
		}

		// Atualizar o selectpicker apenas se necessário
		if ($select.data('selectpicker')) {
			$select.selectpicker('destroy');  // Destroi a instância existente

			// Recriar o selectpicker e ajustar o layout
			$select.selectpicker();
		}
	});
}

/**
 * Obtém o valor (texto) do OPTION selecionado em um SELECT, atribuindo-o a um INPUT HIDEN
 * 
 * @param {*} selectElement O ID do SELECT (use 'this' para chamada da função no próprio SELECT)
 * @param {*} hiddenInputId O ID do campo oculto que receberá o texto
 */

function getOptionText(selectElement, hiddenInputId) {
	var selectedText = $(selectElement).find('option:selected').text();
	$(`#${hiddenInputId}`).val(selectedText);
}

/**
 * Habilita e desabilita SELECTs filhos dependentes de um SELECT pai
 * 
 * @param {*} mainSelects ID dos SELECT pai (separados por ',' se houver mais de um)
 */

function toggleDependentSelects(mainSelects) {
	var allSelected = mainSelects.every(function(selectId) {
		var selectedValue = $(`#${selectId.trim()}`).val();
		//console.log("valor selecionado: " + selectedValue);
		return selectedValue !== '0' && selectedValue !== '';
	});

	// Função recursiva para desabilitar os selects dependentes
	function disableDependents(selectId) {
		$(`[data-depend*='${selectId.trim()}']`).each(function() {
			$(this).prop('disabled', true).val('').trigger('change'); // Desabilita e limpa o select dependente
			//console.log('desabilitou '+ $(this).attr('id'));

			// Verifica se o select tem a classe _selectpicker e aplica o destroy e reinit
			if ($(this).data('selectpicker')) {
				//console.log($(this).attr('id') + ' tem selectpicker');
				$(this).selectpicker('destroy');
				$(this).selectpicker();
			}

			disableDependents($(this).attr('id')); // Desabilita os dependentes deste select
		});
	}

	mainSelects.forEach(function(mainSelectId) {
		$(`[data-depend*='${mainSelectId.trim()}']`).each(function() {
			if (!allSelected) {
				$(this).prop('disabled', true).val('').trigger('change'); // Desabilita e limpa o select dependente
				// Se o select que chamou a função tiver a classe _selectpicker
				if ($(this).data('selectpicker')) {
					$(this).selectpicker('destroy');
					$(this).selectpicker();
				}
				
				disableDependents($(this).attr('id')); // Desabilita os dependentes deste select
			} else {
				$(this).prop('disabled', false);

				// Se o select que chamou a função tiver a classe _selectpicker
				if ($(this).data('selectpicker')) {
					$(this).selectpicker('destroy');
					$(this).selectpicker();
				}
			}
		});
	});
}

