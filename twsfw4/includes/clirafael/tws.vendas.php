<?php

/*
 * Data Criacao: 05/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: painel de inclusão de venda
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class vendas {
    // Produtos registrados no banco
    private $_produtos;

    function __construct() {
        $this->addJS_ListaPedidos();
    }

	public function index($itens = '') {
		$ret = '';
		$form = new form01();

		if(!empty($itens)) {
			$cliente = $itens['cliente'];
			$forma_pagamento = $itens['forma_pagamento'];
			$total = $itens['total'];
			$itens = $itens['formOS'];
		} else {
			$total = 'Total';
			$cliente = '';
			$forma_pagamento = '';
		}

		$param = [];
		$param['campo'] = 'cliente';
		$param['etiqueta'] = 'Cliente';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $cliente;
		$form->addCampo($param);

		$param = [];
		$param['campo'] = 'forma_pagamento';
		$param['etiqueta'] = 'Forma de Pagamento';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['opcoes'] = "Cartão=Cartão;Dinheiro=Dinheiro";
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $forma_pagamento;
		$form->addCampo($param);

		$form->addConteudoPastas(1, $this->getTabelaItens($total, $itens));
		
		$form->setEnvio(getLink() . "vender.salvar&", 'formIncluir_venda');
		$form->setPastas([1 => 'Pedidos']);
		
		$ret .= $form;

		return $ret;
	}

    private function getTabelaItens($total, $itens = []) {
        $ret = '';

        $num_tarefas = !empty($itens) ? count($itens) : 0;

	    $param = [];
	    $param['texto'] = 'Incluir Item';
	    $param['onclick'] = "incluiRat($num_tarefas);";
	    $param['id'] = 'myInput';
	    $ret .= formbase01::formBotao($param);
	    
	    $param = [];
	    $param['paginacao'] = false;
	    $param['scroll'] 	= false;
	    $param['scrollX'] 	= false;
	    $param['scrollY'] 	= false;
	    $param['ordenacao'] = false;
	    $param['filtro']	= false;
	    $param['info']		= false;
	    $param['id']		= 'tabRatID';
	    $param['width']		= '100%';
	    $tab = new tabela01($param);
	    
	    $tab->addColuna(array('campo' => 'id_produto'			, 'etiqueta' => 'Nome Produto'		    , 'tipo' => 'V', 'width' => '5'  , 'posicao' => 'C'));
	    $tab->addColuna(array('campo' => 'quantidade'			, 'etiqueta' => 'Quantidade'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'desconto_porcentagem'	, 'etiqueta' => 'Desconto Porcentagem'	, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'desconto_valor'		, 'etiqueta' => 'Desconto Valor'		, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'valor_produto'		, 'etiqueta' => 'Valor Produto'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'com_desconto'			, 'etiqueta' => 'Valor com desconto'	, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'					, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));

		// $campos = ['id_produto', 'quantidade', 'desconto_porcentagem', 'desconto_valor'];

		if(!empty($itens) && count($itens) > 0){
			$dados = [];
			$num = 0;
			$cont = "var cont = [];";
			foreach($itens as $item){
				$temp = array();
				$cont.= "cont[$num] = $num;";
				//$temp['id_item'] = "<input type='text' name='formOS[id_item][]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";
				// $ret .= "<input type='text' name='formOS[$num][id_produto]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";

				$temp['id_produto'] = "<select onchange='callAjax($num)' name='formOS[$num][id_produto]' style='width:100%;text-align: right;' id='" . ($num) . "campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect($item['id_produto'])."</select>";
				$temp['quantidade'] = "<input onblur='atualizaTotal($num)' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][quantidade]' value='{$item['quantidade']}' style='width:100%;text-align: right;' id='" . ($num) . "campoquantidade' class='form-control  form-control-sm'          >";
				$temp['desconto_porcentagem'] = "<input onblur='atualizaTotal($num)' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][desconto_porcentagem]' value='{$item['desconto_porcentagem']}' style='width:100%;text-align: right;' id='" . ($num) . "campoporcentagem' class='form-control  form-control-sm'          >";
				$temp['desconto_valor'] = "<input onblur='atualizaTotal($num)' type='number' name='formOS[$num][desconto_valor]' value='{$item['desconto_valor']}' style='width:100%;text-align: right;' id='" . ($num) . "campovalor' class='form-control  form-control-sm'          >";
				$temp['valor_produto'] = "<input onblur='callAjax($num)' type='text' name='formOS[$num][valor_produto]' value='{$item['valor_produto']}' style='width:100%;text-align: right;' id='" . ($num) . "campovalortotal' class='form-control  form-control-sm'         >";
				$temp['com_desconto'] = "<input onblur='atualizaTotal($num)' type='text' name='formOS[$num][com_desconto]' value='{$item['com_desconto']}' style='width:100%;text-align: right;' id='" . ($num) . "campocomdesconto' class='form-control  form-control-sm'         >";
				$temp['bt'] = "<button type='button' id='".$num."campoexcluir' class='btn btn-xs btn-danger' onclick='excluirRat('$num');'>Excluir</button>";
				
				$dados[] = $temp;
				$num++;
			}
			$tab->setDados($dados);
			addPortaljavaScript("$cont
			calculaTotalItens();");
		}
		$tab .= "<input type='text' name='total' id='total' value='$total' onblur='calculaTotalItens();' style='text-align: right;'>";

		// foreach($dados as $d) {
		// 	$ret .= $d['id_item'];
		// }

	    $ret .= $tab;
	    
	    return $ret;
    }

    private function criarCampoSelect($id_produto = ''){
		if(empty($this->_produtos)) {
			$sql = "SELECT id, produto FROM pm_produtos WHERE ativo = 'S'";
			$this->_produtos = query($sql);
		}

		$html = "<option value=''>Escolha uma opção</option>";
		if(is_array($this->_produtos) && count($this->_produtos) > 0) {
			foreach($this->_produtos as $row) {
				if($id_produto == $row['id']) {
					$selecionado = 'selected';
				} else {
					$selecionado = '';
				}
				$html .= "<option value='{$row['id']}' $selecionado>{$row['produto']}</option>";
			}
		}

		return $html;
	}

	public function salvar() {
		if(is_array($_POST['formOS']) && count($_POST['formOS']) > 0) {
			$id = base64_encode(time());
			$id = base64_decode($id);

			foreach($_POST['formOS'] as $item) {
				if(!empty($item['valor_produto'])) {
					$preco = str_replace(['R$ ', '.'], '', $item['valor_produto']);
					$preco = str_replace(',', '.', $preco);

					$valor_item = str_replace(['R$ ', '.'], '', $item['com_desconto']);
					$valor_item = str_replace(',', '.', $valor_item);

					$quantidade = !empty($item['quantidade']) ? $item['quantidade'] : 1;
					$desconto_valor = !empty($item['desconto_valor']) ? $item['desconto_valor'] : 0;
					$desconto_porcentagem = !empty($item['desconto_porcentagem']) ? $item['desconto_porcentagem'] : 0;

					if($valor_item > 0) {
						$param = [];
						$param['id_venda'] = $id;
						$param['produto'] = $item['id_produto'];
						$param['quantidade'] = $quantidade;
						$param['desconto_porcentagem'] = $desconto_porcentagem;
						$param['desconto_valor'] = $desconto_valor;
						$param['valor'] = $valor_item;
						$sql = montaSQL($param, 'pm_venda_itens');
						query($sql);
		
						$sql = "UPDATE pm_produtos SET quantidade = quantidade - ".$item['quantidade']." WHERE id = ".$item['id_produto'];
						query($sql);
					} else {
						addPortalMensagem("Erro: O desconto está acima do permitido", 'error');

						return $this->index($_POST);
					}
				} else {
					addPortalMensagem("Erro: Ao carregar o produto. Por favor, refaça o processo", 'error');

					return $this->index($_POST);
				}
			}

			$total = str_replace(['R$ ', '.'], '', $_POST['total']);
			$total = str_replace(',', '.', $total);

			$param = [];
			$param['id'] 				= $id;
			$param['data'] 				= date('Ymd');
			$param['cliente'] 			= $_POST['cliente'] ?? null;
			$param['valor'] 			= $total;
			$param['forma_pagamento'] 	= $_POST['forma_pagamento'];
			$sql = montaSQL($param, 'pm_venda');
			query($sql);

			$mensagem = "Venda registrada com sucesso!";
			$tipo = '';
		} else {
			$mensagem = 'Erro ao registrar a venda';
			$tipo = 'erro';
		}

		redireciona(getLink() . "avisos&mensagem=$mensagem&tipo=$tipo");
	}

    private function addJS_ListaPedidos(){
		$ret = '';
		
		$ret .= "
		
		function excluirRat(id){
				var t = $('#tabRatID').DataTable();
				t.row( $('#'+id+'campoexcluir').parents('tr') ).remove().draw();

				cont.splice(id, 1);
				calculaTotalItens();
			}

		var cont = [];
		function incluiRat(valor){
			var id = valor;
			cont[valor] = valor;
						var t = $('#tabRatID').DataTable();
				
						var bt = \"<button type='button' id='\"+valor+\"campoexcluir' class='btn btn-xs btn-danger'  onclick='excluirRat(\"+valor+\");'>Excluir</button>\";
				
						var id_produto = \"<select name='formOS[\"+valor+\"][id_produto]' onchange='callAjax(\"+id+\")' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect()."</select>\";
						// var id_produto = \"<input  type='text' name='formOS[\"+valor+\"][id_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'          >\";
						var quantidade = \"<input onblur='atualizaTotal(\"+valor+\")' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][quantidade]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoquantidade' class='form-control  form-control-sm'          >\";
						var desconto_porcentagem = \"<input onblur='atualizaTotal(\"+valor+\")' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][desconto_porcentagem]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoporcentagem' class='form-control  form-control-sm'          >\";
						var desconto_valor = \"<input onblur='atualizaTotal(\"+valor+\")' type='number' name='formOS[\"+valor+\"][desconto_valor]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalor' class='form-control  form-control-sm'          >\";
						var valor_produto = \"<input onblur='callAjax(\"+valor+\");' type='text' name='formOS[\"+valor+\"][valor_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalortotal' class='form-control  form-control-sm'         >\";
						var com_desconto = \"<input onblur='atualizaTotal(\"+valor+\")' type='text' name='formOS[\"+valor+\"][com_desconto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campocomdesconto' class='form-control  form-control-sm'         >\";

						// var texto = \"<input  type='text' name='formOS[descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
				
							t.row.add( [id_produto, quantidade, desconto_porcentagem, desconto_valor, valor_produto, com_desconto, bt] ).draw( false );
							$('#'+valor+'tabelacampohora');
				
							valor = valor + 1;
							$('#myInput').attr('onclick', 'incluiRat('+valor+');' );
		}
	
		function callAjax(iddois){
			// var entidade = document.getElementById('inputOrigem').value;
			var entidade = document.getElementById(iddois+'campoidproduto');
			
			$.getJSON('" . getLinkAjax('ajax') . "&id='+entidade.value+'&entidade=' + entidade, function (dados){
				if (dados.length > 0){
					$.each(dados, function(i, obj){
						var valor = parseFloat(obj.valor);
						$('#'+iddois+'campovalortotal').val('R$ '+valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
						atualizaTotal(iddois);
					});
				}
			})
		}

		function atualizaTotal(id) {
			var total = document.getElementById(id+'campovalortotal').value.replace(/\./g, '').replace(',', '.').replace('R$', '');
			var quantidade = document.getElementById(id+'campoquantidade').value;
			var porcentagem = document.getElementById(id+'campoporcentagem').value;
			var desconto_valor = document.getElementById(id+'campovalor').value;
			
			if(quantidade == '') {
				quantidade = 1;
			}
			var valor_total = total * quantidade;

			if(porcentagem == '') {
				porcentagem = 0;
			} else {
				porcentagem = (porcentagem * valor_total) / 100;
			}
			
			if(desconto_valor == '') {
				desconto_valor = 0;
			}

			var com_desconto = valor_total - porcentagem - desconto_valor
			$('#'+id+'campocomdesconto').val('R$ '+com_desconto.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

			calculaTotalItens();
		}

		function calculaTotalItens(total = 0) {
			if(total != 0) {
				cont = total;
			}
			// console.log('cont: '+cont);
			var valor_total = 0;
			cont.forEach(function(i) {
				var valor = document.getElementById(i+'campocomdesconto').value;
				if(valor != null && valor != '') {
					valor = parseFloat(valor.replace(/\./g, '').replace(',', '.').replace('R$', ''));
					valor_total += valor;
				}
			});

			$('#total').val('R$ '+valor_total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
		}
			";
		
		addPortaljavaScript($ret);
		
		return $ret;
	}
}