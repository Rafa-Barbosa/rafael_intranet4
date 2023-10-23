<?php

/*
 * Data Criacao: 06/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera formulário para inclusão de produto
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class incluir_produto {
    private $_form;

    function __construct() {
        
    }

    public function cadastroProduto($id = '') {
        $ret = '';
        $this->_form = new form01();

		if($id != '') {
			$sql = "SELECT * FROM pm_produtos WHERE id = $id";
			$row = query($sql);
		}

        $param = [];
        $param['campo'] = 'produto';
		$param['etiqueta'] = 'Produto';
		$param['largura'] = '6';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['produto'] ?? '';
		$param['maxtamanho'] = 100;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'marca';
		$param['etiqueta'] = 'Marca';
		$param['largura'] = '6';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['marca'] ?? '';
		$param['maxtamanho'] = 50;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'modelo';
		$param['etiqueta'] = 'Modelo';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['modelo'] ?? '';
		$param['maxtamanho'] = 100;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'cor';
		$param['etiqueta'] = 'Cor';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['cor'] ?? '';
		$param['maxtamanho'] = 50;
        $this->_form->addCampo($param);

		// $param = [];
        // $param['campo'] = 'ano';
		// $param['etiqueta'] = 'Ano';
		// $param['largura'] = '2';
		// $param['tipo'] = 'N';
		// $param['mascara'] = 'N';
		// // $param['obrigatorio'] = true;
		// $param['valor'] = $row[0]['ano'] ?? '';
        // $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'preco';
		$param['etiqueta'] = 'Preço';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['mascara'] = 'V';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['preco'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'quantidade';
		$param['etiqueta'] = 'Quantidade';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['quantidade'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'fabricacao';
		$param['etiqueta'] = 'Fabricação';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['fabricacao'] ?? '';
		$param['maxtamanho'] = 4;
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'id_fornecedor';
		$param['etiqueta'] = 'Fornecedor';
		$param['largura'] = '3';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['id_fornecedor'] ?? '';
        $param['tabela_itens'] = 'pm_fornecedores|id|nome_fantasia||ativo="S"';
        $this->_form->addCampo($param);


		$sql = "SELECT id, produto, apos_produto, antes_produto FROM pm_produtos WHERE ativo = 'S'";
		$produtos = query($sql);

		if(is_array($produtos) && count($produtos) > 0) {
			foreach($produtos as $prod) {
				$temp = [];
				$temp['id'] = $prod['id'];
				$temp['produto'] = $prod['produto'];
				$temp['antes_produto'] = $prod['antes_produto'];

				if($prod['apos_produto'] == 0) {
					$primeiro = $temp;
				} else {
					$op_produtos[$prod['id']] = $temp;
				}
			}

			// Organiza pela ordem escolhida
			$opcoes = [];
			$opcoes[0] = 'Antes de todos';
			if($primeiro['id'] != $id) {
				$opcoes[$primeiro['id']] = $primeiro['produto'];
			}
            $id_proximo = $primeiro['antes_produto'];
            while(isset($op_produtos[$id_proximo])) {
				if($op_produtos[$id_proximo]['id'] != $id) {
					$opcoes[$op_produtos[$id_proximo]['id']] = $op_produtos[$id_proximo]['produto'];
				}
                $id_proximo = $op_produtos[$id_proximo]['antes_produto'];
            }
		}

		$param = [];
        $param['campo'] = 'apos_produto';
		$param['etiqueta'] = 'Após o produto:';
		$param['largura'] = '3';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['apos_produto'] ?? '';
		$param['opcoes'] = $opcoes;
        $this->_form->addCampo($param);

        $this->_form->setEnvio(getLink() . "salvar&id=$id", 'formIncluir_fornecedor');

        $ret .= $this->_form;

        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir Novo Produto';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
		if(count($_POST) > 0) {
			// if($_POST['apos_produto'] != 0) {
			// 	$sql = "SELECT apos_produto, antes_produto FROM pm_produtos WHERE id = {$_POST['apos_produto']}";
			// 	$row = query($sql);
			// }

			$preco = str_replace('.', '', $_POST['preco']);
			$preco = str_replace(',', '.', $preco);

			$temp = [];
			$temp['produto'] 		= $_POST['produto'];
			$temp['marca'] 			= $_POST['marca'];
			$temp['modelo'] 		= $_POST['modelo'];
			$temp['cor'] 			= $_POST['cor'];
			// $temp['ano'] 			= str_replace('.', '', $_POST['ano']);
			$temp['preco'] 			= $preco;
			$temp['quantidade'] 	= $_POST['quantidade'];
			$temp['fabricacao'] 	= $_POST['fabricacao'];
			$temp['id_fornecedor'] 	= $_POST['id_fornecedor'];
			$temp['apos_produto']	= $_POST['apos_produto'];
			$temp['ativo'] 			= 'S';

			$sql = montaSQL($temp, 'pm_produtos');
			$inserido = query($sql);

			if($inserido) {
				// Recebe o ID do produto atual
				$sql = "SELECT id FROM pm_produtos WHERE produto = '{$temp['produto']}' AND marca = '{$temp['marca']}' AND cor = '{$temp['cor']}' AND ativo = 'S'";
				$id = query($sql);
				$id = $id[0]['id'];

				if($_POST['apos_produto'] == 0) {
					// Recebe o ID do primeiro produto
					$sql = "SELECT id FROM pm_produtos WHERE apos_produto = 0";
					$id_proximo = query($sql);
					$id_proximo = $id_proximo[0]['id'];
				} else {
	
					// Recebe o ID do próximo produto
					$sql = "SELECT antes_produto FROM pm_produtos WHERE id = {$_POST['apos_produto']}";
					$id_proximo = query($sql);
					$id_proximo = $id_proximo[0]['antes_produto'];
	
					// Altera o produto anterior
					$sql = montaSQL(['antes_produto' => $id], 'pm_produtos', 'UPDATE', "id = ".$_POST['apos_produto']);
					query($sql);
	
				}

				
				// Altera o produto atual
				$sql = montaSQL(['antes_produto' => $id_proximo], 'pm_produtos', 'UPDATE', "id = $id");
				query($sql);

				// Só altera caso não seja o último produto
				if($id_proximo != 0) {
					// Altera o próximo produto
					$sql = montaSQL(['apos_produto' => $id], 'pm_produtos', 'UPDATE', "id = $id_proximo");
					query($sql);
				}
			}
		}
    }

    public function editar($id) {
		if(count($_POST) > 0) {

			$sql = "SELECT apos_produto, antes_produto FROM pm_produtos WHERE id = $id";
			$produto_atual = query($sql);
			$produto_atual = $produto_atual[0];

			if($_POST['apos_produto'] != $produto_atual['apos_produto']) {
				// ====================== ATUALIZA O LOCAL ATUAL ======================
				// Atualiza o produto anterior
				if($produto_atual['apos_produto'] != 0) {
					$sql = montaSQL(['antes_produto' => $produto_atual['antes_produto']], 'pm_produtos', 'UPDATE', "id = ".$produto_atual['apos_produto']);
					query($sql);
				}

				// Atualiza o próximo produto
				if($produto_atual['antes_produto'] != 0) {
					$sql = montaSQL(['apos_produto' => $produto_atual['apos_produto']], 'pm_produtos', 'UPDATE', "id = ".$produto_atual['antes_produto']);
					query($sql);
				}
				
				
				// ====================== ATUALIZA O NOVO LOCAL ======================
				if($_POST['apos_produto'] == 0) {
					// Recebe o ID do primeiro produto
					$sql = "SELECT id FROM pm_produtos WHERE apos_produto = 0";
					$id_proximo = query($sql);
					$id_proximo = $id_proximo[0]['id'];
				} else {
					
					// Recebe o ID do próximo produto
					$sql = "SELECT antes_produto FROM pm_produtos WHERE id = {$_POST['apos_produto']}";
					$id_proximo = query($sql);
					$id_proximo = $id_proximo[0]['antes_produto'];
					
					// Altera o produto anterior
					$sql = montaSQL(['antes_produto' => $id], 'pm_produtos', 'UPDATE', "id = ".$_POST['apos_produto']);
					query($sql);
				
				}

				// Só altera caso não seja o último produto
				if($id_proximo != 0) {
					// Altera o próximo produto
					$sql = montaSQL(['apos_produto' => $id], 'pm_produtos', 'UPDATE', "id = $id_proximo");
					query($sql);
				}
			} else {
				$id_proximo = $produto_atual['antes_produto'];
			}



			$preco = str_replace('.', '', $_POST['preco']);
			$preco = str_replace(',', '.', $preco);

			$temp = [];
			$temp['produto'] 		= $_POST['produto'];
			$temp['marca'] 			= $_POST['marca'];
			$temp['modelo'] 		= $_POST['modelo'];
			$temp['cor'] 			= $_POST['cor'];
			// $temp['ano'] 			= str_replace('.', '', $_POST['ano']);
			$temp['preco'] 			= $preco;
			$temp['quantidade'] 	= $_POST['quantidade'];
			$temp['fabricacao'] 	= $_POST['fabricacao'];
			$temp['id_fornecedor'] 	= $_POST['id_fornecedor'];
			$temp['apos_produto']	= $_POST['apos_produto'];
			$temp['antes_produto']	= $id_proximo;

			$sql = montaSQL($temp, 'pm_produtos', 'UPDATE', "id = $id");
			query($sql);
		}
    }
}