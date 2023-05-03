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
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['produto'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'marca';
		$param['etiqueta'] = 'Marca';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['marca'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'modelo';
		$param['etiqueta'] = 'Modelo';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['modelo'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'cor';
		$param['etiqueta'] = 'Cor';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['cor'] ?? '';
        $this->_form->addCampo($param);

		$param = [];
        $param['campo'] = 'ano';
		$param['etiqueta'] = 'Ano';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
		$param['mascara'] = 'N';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row[0]['ano'] ?? '';
        $this->_form->addCampo($param);

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
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'id_fornecedor';
		$param['etiqueta'] = 'Fornecedor';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['id_fornecedor'] ?? '';
        $param['tabela_itens'] = 'pm_fornecedores|id|nome_fantasia||ativo="S"';
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
			$preco = str_replace('.', '', $_POST['preco']);
			$preco = str_replace(',', '.', $preco);

			$temp = [];
			$temp['produto'] 		= $_POST['produto'];
			$temp['marca'] 			= $_POST['marca'];
			$temp['modelo'] 		= $_POST['modelo'];
			$temp['cor'] 			= $_POST['cor'];
			$temp['ano'] 			= str_replace('.', '', $_POST['ano']);
			$temp['preco'] 			= $preco;
			$temp['quantidade'] 	= $_POST['quantidade'];
			$temp['fabricacao'] 	= $_POST['fabricacao'];
			$temp['id_fornecedor'] 	= $_POST['id_fornecedor'];
			$temp['ativo'] 			= 'S';

			$sql = montaSQL($temp, 'pm_produtos');
			query($sql);
		}
    }

    public function editar($id) {
		if(count($_POST) > 0) {
			$preco = str_replace('.', '', $_POST['preco']);
			$preco = str_replace(',', '.', $preco);

			$temp = [];
			$temp['produto'] 		= $_POST['produto'];
			$temp['marca'] 			= $_POST['marca'];
			$temp['modelo'] 		= $_POST['modelo'];
			$temp['cor'] 			= $_POST['cor'];
			$temp['ano'] 			= str_replace('.', '', $_POST['ano']);
			$temp['preco'] 			= $preco;
			$temp['quantidade'] 	= $_POST['quantidade'];
			$temp['fabricacao'] 	= $_POST['fabricacao'];
			$temp['id_fornecedor'] 	= $_POST['id_fornecedor'];

			$sql = montaSQL($temp, 'pm_produtos', 'UPDATE', "id = $id");
			query($sql);
		}
    }
}