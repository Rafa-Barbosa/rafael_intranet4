<?php

/*
 * Data Criacao: 20/03/2024
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

class compras_itens {
    // Produtos registrados no banco
    private $_produtos;

	// se o usuario está somente visualizando ou editando
	private $_visualizar;

    function __construct() {
        $this->addJS_ListaPedidos();
    }

	public function index($itens = []) {
		$ret = '';
        $id = $_GET['id'] ?? '';
		$this->_visualizar = $_GET['visualizar'] ?? false;
		$form = new form01();

		$param = [];
		$param['campo'] = 'fornecedor_id';
		$param['etiqueta'] = 'Fornecedor';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'fornecedores|fornecedor_id|nome_fantasia||ativo="S"';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $itens['fornecedor_id'] ?? '';
		$param['readonly'] = $this->_visualizar;
		$form->addCampo($param);

        $total = $itens['total'] ?? 0;
        $itens = $itens['formOS'] ?? [];
		$form->addConteudoPastas(1, $this->getTabelaItens($itens, $total));
		
		$form->setEnvio(getLink() . "salvar&id=$id", 'formIncluirCompra');
		$form->setPastas([1 => 'Pedidos']);
		
		$ret .= $form;

		return $ret;
	}

    private function getTabelaItens($itens = [], $total = 0) {
        $ret = '';

        $num_tarefas = !empty($itens) ? count($itens) : 0;

	    $param = [];
	    $param['texto'] = 'Incluir Item';
		if(!$this->_visualizar) {
			$param['onclick'] = "incluiRat($num_tarefas);";
		}
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
	    
	    $tab->addColuna(array('campo' => 'produto_id'			, 'etiqueta' => 'Nome Produto'		    , 'tipo' => 'V', 'width' => '5'  , 'posicao' => 'C'));
		$tab->addColuna(array('campo' => 'valor_produto'		, 'etiqueta' => 'Valor Produto'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'quantidade'			, 'etiqueta' => 'Quantidade'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
        $tab->addColuna(array('campo' => 'valor_total'		    , 'etiqueta' => 'Valor Total'			, 'tipo' => 'V', 'width' => '10', 'posicao' => 'E'));
	    $tab->addColuna(array('campo' => 'bt'					, 'etiqueta' => ''						, 'tipo' => 'V', 'width' => ' 50', 'posicao' => 'D'));

		$cont = "var cont = []; ";
		$mask = '';
		if(!empty($itens) && count($itens) > 0){
			$dados = [];
			$num = 0;
			$disabled = $this->_visualizar ? 'disabled="disabled"' : '';
			foreach($itens as $item){
				$temp = array();
				$cont.= "cont[$num] = $num; ";
				//$temp['id_item'] = "<input type='text' name='formOS[id_item][]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";
				// $ret .= "<input type='text' name='formOS[$num][produto_id]' value='{$item['id']}' style='width:100%;text-align: right;' id='" . ($num+1) . "campoiditem' class='form-control  form-control-sm' hidden          >";

                $temp['produto_id'] = "<input $disabled type='hidden' value='{$item['compra_item_id']}' name='formOS[$num][compra_item_id]' id='" . ($num) . "campoiditem'>";
				$temp['produto_id'] .= "<select $disabled name='formOS[$num][produto_id]' style='width:100%;text-align: right;' id='" . ($num) . "campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect($item['produto_id'])."</select>";
				$temp['valor_produto'] = "<input $disabled onkeyup='atualizaTotal($num)' type='text' name='formOS[$num][valor_produto]' value='{$item['valor_produto']}' style='width:100%;text-align: right;' id='" . ($num) . "campovalorproduto' class='form-control  form-control-sm'         >";
				$temp['quantidade'] = "<input $disabled onkeyup='atualizaTotal($num)' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[$num][quantidade]' value='{$item['quantidade']}' style='width:100%;text-align: right;' id='" . ($num) . "campoquantidade' class='form-control  form-control-sm'          >";
                $temp['valor_total'] = "<input $disabled type='text' name='formOS[$num][valor_total]' value='{$item['valor_total']}' style='width:100%;text-align: right;' id='" . ($num) . "campovalortotal' class='form-control  form-control-sm'         >";
				$temp['bt'] = "<button $disabled type='button' id='".$num."campoexcluir' class='btn btn-xs btn-danger float-left' onclick='excluirRat(\"$num\");'>Excluir</button>";
				
				$dados[] = $temp;
				$mask .= "$( '#{$num}campovalorproduto' ).mask('###.###.###.###.##0,00',{reverse: true});";
				$num++;
			}
			$tab->setDados($dados);
			
		}
		$total = 'R$ ' . number_format($total, 2, ',', '.');
        $tab .= "<input type='text' name='total' id='total' value='$total' onblur='calculaTotalItens();' style='text-align: right;' disabled='disabled'>";

		// foreach($dados as $d) {
		// 	$ret .= $d['id_item'];
		// }

	    $ret .= $tab;

		addPortaljavaScript("$cont
			$mask
			calculaTotalItens();", 'F');
	    
	    return $ret;
    }

    private function criarCampoSelect($produto_id = ''){
		if(empty($this->_produtos)) {
			$sql = "SELECT produto_id, descricao FROM produtos WHERE ativo = 'S'";
			$this->_produtos = query($sql);
		}

		$html = "<option value=''>Escolha uma opção</option>";
		if(is_array($this->_produtos) && count($this->_produtos) > 0) {
			foreach($this->_produtos as $row) {
				if($produto_id == $row['produto_id']) {
					$selecionado = 'selected';
				} else {
					$selecionado = '';
				}
				$html .= "<option value='{$row['produto_id']}' $selecionado>{$row['descricao']}</option>";
			}
		}

		return $html;
	}

	public function salvar() {
        if(!empty($_POST) && count($_POST['formOS']) > 0) {
            $id = $_GET['id'] ?? '';
            $temp = [];

            if(empty($id)) {
                $sql = "SELECT MAX(compra_id) AS max FROM compras";
                $id = query($sql);
                $id = $id[0]['max'] + 1;

                $tipo = 'INSERT';
                $where = '';

                $temp['compra_id'] = $id;
                $temp['data'] = date('Y-m-d');
            } else {
                $tipo = 'UPDATE';
                $where = "compra_id = $id";
            }

            $temp['fornecedor_id'] = $_POST['fornecedor_id'];
            $sql = montaSQL($temp, 'compras', $tipo, $where);
            query($sql);


            // Insere/Edita os itens
            $ids_itens = [];
            foreach($_POST['formOS'] as $item) {
                $id_item = $item['compra_item_id'] ?? '';
                $quantidade_antes = 0;

                if(empty($id_item)) {
                    $tipo = 'INSERT';
                    $where = '';

                    $sql = "SELECT MAX(compra_item_id) AS max FROM compra_itens";
                    $id_item = query($sql);
                    $id_item = $id_item[0]['max'] + 1;
                } else {
                    $tipo = 'UPDATE';
                    $where = "compra_item_id = $id_item";

                    // Armazena a quantidade de antes da edição
                    $sql = "SELECT quantidade FROM compra_itens WHERE compra_item_id = $id_item";
                    $quantidade_antes = query($sql);
                    $quantidade_antes = $quantidade_antes[0]['quantidade'];
                }

                $valor = str_replace('.', '', $item['valor_produto']);
                $valor = str_replace(',', '.', $valor);

                $temp = [];
                $temp['compra_item_id'] = $id_item;
                $temp['compra_id']      = $id;
                $temp['produto_id']     = $item['produto_id'];
                $temp['quantidade']     = $item['quantidade'];
                $temp['valor']          = $valor;
                $temp['ativo']          = 'S';
                $sql = montaSQL($temp, 'compra_itens', $tipo, $where);
                query($sql);

                // Armazena a quantidade no estoque
                if($item['quantidade'] != $quantidade_antes) {
                    $diferenca = $item['quantidade'] - $quantidade_antes;
                    $sql = "UPDATE produtos SET quantidade = quantidade + $diferenca WHERE produto_id = ".$item['produto_id'];
                    query($sql);
                }

                $ids_itens[] = $id_item;
            }

            // Inativa os itens excluidos
            $sql = "SELECT compra_item_id, produto_id, quantidade FROM compra_itens WHERE compra_id = $id AND ativo = 'S'";
            $rows = query($sql);
            foreach($rows as $row) {
                if(!in_array($row['compra_item_id'], $ids_itens)) { // Está no banco mas não foi passado pelo formulário
                    $sql = "UPDATE compra_itens SET ativo = 'N' WHERE compra_item_id = ".$row['compra_item_id'];
                    query($sql);

                    // Diminui a quantidade no estoque que esse item representava
                    $sql = "UPDATE produtos SET quantidade = quantidade - {$row['quantidade']} WHERE produto_id = ".$row['produto_id'];
                    query($sql);
                }
            }

            addPortalMensagem('Compras registradas com sucesso!');
        } else {
            addPortalMensagem('Erro ao receber as informações. Nenhuma alteração registrada!', 'error');
        }
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
    
            var produto_id = \"<select name='formOS[\"+valor+\"][produto_id]' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'>".$this->criarCampoSelect()."</select>\";
            // var produto_id = \"<input  type='text' name='formOS[\"+valor+\"][produto_id]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoidproduto' class='form-control  form-control-sm'          >\";
            var valor_produto = \"<input onkeyup='atualizaTotal(\"+valor+\")' type='text' name='formOS[\"+valor+\"][valor_produto]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalorproduto' class='form-control  form-control-sm'         >\";
            var quantidade = \"<input onkeyup='atualizaTotal(\"+valor+\")' onkeypress='return event.charCode >= 48 && event.charCode <= 57' type='number' name='formOS[\"+valor+\"][quantidade]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campoquantidade' class='form-control  form-control-sm'          >\";
            var valor_total = \"<input type='text' name='formOS[\"+valor+\"][valor_total]' value='' style='width:100%;text-align: right;' id='\"+valor+\"campovalortotal' class='form-control  form-control-sm'         >\";
            
            // var texto = \"<input  type='text' name='formOS[descricao][]' value='' style='width:100%;' id='\"+valor+\"tabelacampotexto' class='form-control  form-control-sm'          >\";
    
            t.row.add( [produto_id, valor_produto, quantidade, valor_total, bt] ).draw( false );
            $('#'+valor+'tabelacampohora');

            $( '#'+valor+'campovalorproduto' ).mask('###.###.###.###.##0,00',{reverse: true});

            valor = valor + 1;
            $('#myInput').attr('onclick', 'incluiRat('+valor+');' );
		}

		function atualizaTotal(id) {
			var total = document.getElementById(id+'campovalorproduto').value.replace(/\./g, '').replace(',', '.').replace('R$', '');
			var quantidade = document.getElementById(id+'campoquantidade').value;
			
			if(quantidade == '') {
				quantidade = 1;
			}
			var valor_total = total * quantidade;

			$('#'+id+'campovalortotal').val('R$ '+valor_total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

			calculaTotalItens();
		}

		function calculaTotalItens(total = 0) {
			if(total != 0) {
				cont = total;
			}
			// console.log('cont: '+cont);
			var valor_total = 0;
			cont.forEach(function(i) {
				var valor = document.getElementById(i+'campovalortotal').value;
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