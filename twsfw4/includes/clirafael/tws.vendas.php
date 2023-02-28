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

		$param = [];
		$param['campo'] = 'cliente';
		$param['etiqueta'] = 'Cliente';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'pm_clientes|id|nome||ativo="S"';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $pedido[0]['cliente'] ?? '';
		$form->addCampo($param);

		$form->addConteudoPastas(1, $this->getTabelaItens($itens));
		
		$form->setEnvio(getLink() . "vender.salvar&", 'formIncluir_venda');
		$form->setPastas([1 => 'Pedidos']);
		
		$ret .= $form;


		return $ret;
	}

	public function avisos() {
        $tipo = $_GET['tipo'] ?? '';

		if($tipo == 'erro') {
			addPortalMensagem('Erro: ' . $_GET['mensagem'], 'error');
		} else {
			addPortalMensagem($_GET['mensagem']);
		}

		return $this->index();
	}

    private function getTabelaItens($itens = []) {
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
		$tab->addColuna(array('campo' => 'desconto_valor'		, 'etiqueta' => 'desconto_valor'		, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'					, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));

		// $campos = ['id_produto', 'quantidade', 'desconto_porcentagem', 'desconto_valor'];

		if(!empty($itens) && count($itens) > 0){
			$dados = [];
			$num = 0;
			foreach($itens as $item){
				$temp = array();
				//$temp['id_item'] = "<input type='text' name='formOS[id_item][]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";
				// $ret .= "<input type='text' name='formOS[$num][id_produto]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";

				$temp['id_produto'] = "<select name='formOS[$num][id_produto]' style='width:100%;text-align: right;' id='" . ($num+1) . "campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect($item['id_produto'])."</select>";
				$temp['quantidade'] = "<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][quantidade]' value='{$item['quantidade']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoquantidade' class='form-control  form-control-sm'          >";
				$temp['desconto_porcentagem'] = "<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][desconto_porcentagem]' value='{$item['desconto_porcentagem']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoporcentagem' class='form-control  form-control-sm'          >";
				$temp['desconto_valor'] = "<input type='number' name='formOS[$num][desconto_valor]' value='{$item['desconto_valor']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campovalor' class='form-control  form-control-sm'          >";
				$temp['bt'] = "<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>";
				
				$dados[] = $temp;
				$num++;
			}
			$tab->setDados($dados);
		}

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

			$valor = 0;
			foreach($_POST['formOS'] as $item) {
				if(!empty($item['quantidade'])) {
					$sql = "SELECT preco FROM pm_produtos WHERE id = ".$item['id_produto'];
					$preco = query($sql);
					$preco = !empty($preco[0][0]) ? $preco[0][0] : 0;

					$desconto_valor = !empty($item['desconto_valor']) ? $item['desconto_valor'] : 0;
					$desconto_porcentagem = !empty($item['desconto_porcentagem']) ? ($preco * $item['desconto_porcentagem']) / 100 : 0;
					$valor_item = ($preco * $item['quantidade']) - $desconto_porcentagem - $desconto_valor;

					if($valor_item > 0) {
						$valor += $valor_item;
	
						$param = [];
						$param['id_venda'] = $id;
						$param['produto'] = $item['id_produto'];
						$param['quantidade'] = $item['quantidade'];
						$param['desconto_porcentagem'] = $desconto_porcentagem;
						$param['desconto_valor'] = $desconto_valor;
						$param['valor'] = $valor_item;
						$sql = montaSQL($param, 'pm_venda_itens');
						query($sql);
		
						$sql = "UPDATE pm_produtos SET quantidade = quantidade - ".$item['quantidade']." WHERE id = ".$item['id_produto'];
						query($sql);
					} else {
						addPortalMensagem("Erro: O desconto está acima do permitido", 'error');

						return $this->index($_POST['formOS']);
					}
				} else {
					addPortalMensagem("Erro: A quantidade está vazia", 'error');

					return $this->index($_POST['formOS']);
				}
			}

			$param = [];
			$param['id'] = $id;
			$param['data'] = date('Ymd');
			$param['cliente'] = $_POST['cliente'] ?? null;
			$param['valor'] = $valor;
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
		
		function excluirRat(e){
						var t = $('#tabRatID').DataTable();
						t.row( $(e).parents('tr') ).remove().draw();
			}
		function incluiRat(valor){
						var t = $('#tabRatID').DataTable();
				
						var bt = \"<button type='button' class='btn btn-xs btn-danger'  onclick='excluirRat(this);'>Excluir</button>\";
				
						var id_produto = \"<select name='formOS[\"+valor+\"][id_produto]' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect()."</select>\";
						// var id_produto = \"<input  type='text' name='formOS[\"+valor+\"][id_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'          >\";
						var quantidade = \"<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][quantidade]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoquantidade' class='form-control  form-control-sm'          >\";
						var desconto_porcentagem = \"<input onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][desconto_porcentagem]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoporcentagem' class='form-control  form-control-sm'          >\";
						var desconto_valor = \"<input  type='number' name='formOS[\"+valor+\"][desconto_valor]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalor' class='form-control  form-control-sm'          >\";

						// var texto = \"<input  type='text' name='formOS[descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
				
							t.row.add( [id_produto, quantidade, desconto_porcentagem, desconto_valor, bt] ).draw( false );
							$('#'+valor+'tabelacampohora');
				
							valor = valor + 1;
							$('#myInput').attr('onclick', 'incluiRat('+valor+');' );
		}
	
			";
		
		addPortaljavaScript($ret);
		
		return $ret;
	}
}