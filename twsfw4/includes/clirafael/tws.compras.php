<?php

/*
 * Data Criacao: 23/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: painel de inclusão de compras
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class compras {
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
			$total = $itens['total'];
			$itens = $itens['formOS'];
		} else {
			$total = 'Total';
			$cliente = '';
		}

		$param = [];
		$param['campo'] = 'id_fornecedor';
		$param['etiqueta'] = 'Fornecedor';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'pm_fornecedores|id|nome_fantasia||ativo="S"';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['id_fornecedor'] ?? '';
		$form->addCampo($param);

		$form->addConteudoPastas(1, $this->getTabelaItens($total, $itens));
		
		$form->setEnvio(getLink() . "vender.salvar&", 'formIncluir_venda');
		$form->setPastas([1 => 'Pedidos']);
		
		$ret .= $form;


		return $ret;
	}

    private function getTabelaItens($total, $item = []) {
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
        $tab->addColuna(array('campo' => 'custo'		        , 'etiqueta' => 'custo'		            , 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
		$tab->addColuna(array('campo' => 'total_item'	        , 'etiqueta' => 'Total'		            , 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'					, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));

		// $campos = ['id_produto', 'quantidade'];

		if(!empty($itens) && count($itens) > 0){
			$dados = [];
			$num = 0;
			$cont = "var cont = []; ";
			foreach($itens as $item){
				$temp = array();
				$cont.= "cont[$num] = $num; ";
				//$temp['id_item'] = "<input type='text' name='formOS[id_item][]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";
				$ret .= "<input type='text' name='formOS[$num][id_item]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num) . "campoiditem' class='form-control  form-control-sm' hidden          >";

				$temp['id_produto'] = "<select name='formOS[$num][id_produto]' style='width:100%;text-align: right;' id='" . ($num) . "campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect($item['id_produto'])."</select>";
				$temp['quantidade'] = "<input onblur='atualizaTotal($num)' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][quantidade]' value='{$item['quantidade']}' style='width:100%;text-align: right;' id='" . ($num) . "campoquantidade' class='form-control  form-control-sm'          >";
                $temp['custo'] = "<input onblur='atualizaTotal($num)' type='number' name='formOS[$num][custo]' value='{$item['custo']}' style='width:100%;text-align: right;' id='" . ($num) . "campovalor' class='form-control  form-control-sm'          >";
				$temp['total_item'] = "<input onblur='atualizaTotal($num)' type='text' name='formOS[$num][total_item]' value='{$item['valor_item']}' style='width:100%;text-align: right;' id='" . $num . "campo_total_item' class='form-control  form-control-sm'>";
				$temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>";
				
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
				if(!empty($item['quantidade'])) {

					$temp = [];
					$temp['id_compra'] 	= $id;
					$temp['produto'] 	= $item['id_produto'];
					$temp['quantidade'] = $item['quantidade'];
					$temp['custo'] 		= $item['custo'];
					$temp['total'] 		= $item['total_item'];

					$sql = montaSQL($temp, 'pm_compra_itens');
					query($sql);

					$sql = "UPDATE pm_produtos SET quantidade = quantidade + ".$item['quantidade']." WHERE id = ".$item['id_produto'];
					query($sql);
				} else {
					addPortalMensagem("Erro: A quantidade está vazia", 'error');

					return $this->index($_POST['formOS']);
				}
			}

			$total = str_replace(['R$ ', '.'], '', $_POST['total']);
			$total = str_replace(',', '.', $total);

			$temp = [];
			$temp['id'] 			= $id;
			$temp['data'] 			= date('Ymd');
			$temp['id_fornecedor'] 	= $_POST['id_fornecedor'];
			$temp['custo'] 			= $total;
			$sql = montaSQL($temp, 'pm_compra');
			query($sql);

			$mensagem = "Compra registrada com sucesso!";
			$tipo = '';
		} else {
			$mensagem = 'Erro ao registrar a compra';
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
			cont[valor] = valor;

						var t = $('#tabRatID').DataTable();
				
						var bt = \"<button type='button' id='\"+valor+\"campoexcluir' class='btn btn-xs btn-danger'  onclick='excluirRat(\"+valor+\");'>Excluir</button>\";
				
						var id_produto = \"<select name='formOS[\"+valor+\"][id_produto]' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect()."</select>\";
						// var id_produto = \"<input  type='text' name='formOS[\"+valor+\"][id_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'          >\";
						var quantidade = \"<input onblur='atualizaTotal(\"+valor+\")' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][quantidade]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoquantidade' class='form-control  form-control-sm'          >\";
                        var custo = \"<input onblur='atualizaTotal(\"+valor+\")' type='number' name='formOS[\"+valor+\"][custo]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalor' class='form-control  form-control-sm'          >\";
						var total_item = \"<input onblur='atualizaTotal(\"+valor+\")' type='text' name='formOS[\"+valor+\"][total_item]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campo_total_item' class='form-control  form-control-sm'>\";

						// var texto = \"<input  type='text' name='formOS[descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
				
							t.row.add( [id_produto, quantidade, custo, total_item, bt] ).draw( false );
							$('#'+valor+'tabelacampohora');
				
							valor = valor + 1;
							$('#myInput').attr('onclick', 'incluiRat('+valor+');' );
		}

		function atualizaTotal(id) {
			var custo = document.getElementById(id+'campovalor').value.replace(/\./g, '').replace(',', '.');
			var quantidade = document.getElementById(id+'campoquantidade').value;
			
			if(quantidade == '') {
				quantidade = 1;
			}
			if(custo == '') {
				custo = 0;
			}
			
			var total = custo * quantidade;
			$('#'+id+'campo_total_item').val('R$ '+total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

			calculaTotalItens();
		}

		function calculaTotalItens(total = 0) {
			if(total != 0) {
				cont = total;
			}

			var valor_total = 0;
			cont.forEach(function(i) {
				var valor = document.getElementById(i+'campo_total_item').value;
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