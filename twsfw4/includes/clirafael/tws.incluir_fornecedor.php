<?php

/*
 * Data Criacao: 05/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera formulário para criação de fornecedor
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class incluir_fornecedor {
    private $_form;

    function __construct() {
        
    }

    public function cadastroFornecedor($id = '') {
        $ret = '';
		$this->_form = new form01();

		if($id != '') {
			$sql = "SELECT * FROM pm_fornecedores WHERE id = $id";
			$row = query($sql);
		}

        $param = [];
		// $param['id'] = 'inputDestino';
		$param['campo'] = 'nome_fantasia';
		$param['etiqueta'] = 'Nome Fantasia';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        // $param['pasta'] = 1;
		$param['valor'] = $row[0]['nome_fantasia'] ?? '';
		$this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'razao_social';
		$param['etiqueta'] = 'Razão Social';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['razao_social'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'telefone';
		$param['etiqueta'] = 'Telefone';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['telefone'] ?? '';
        $this->_form->addCampo($param);

        $param = [];
        $param['campo'] = 'email';
		$param['etiqueta'] = 'E-mail';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['email'] ?? '';
        $this->_form->addCampo($param);

		$param = [];
        $param['campo'] = 'rua';
		$param['etiqueta'] = 'Rua';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['rua'] ?? '';
        $this->_form->addCampo($param);

		$param = [];
        $param['campo'] = 'nr';
		$param['etiqueta'] = 'N°';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
		$param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['nr'] ?? '';
        $this->_form->addCampo($param);

		$param = [];
        $param['campo'] = 'estado';
		$param['etiqueta'] = 'Estado';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['estado'] ?? '';
        $this->_form->addCampo($param);

		$param = [];
        $param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['cep'] ?? '';
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
		$param['titulo'] = 'Incluir Novo Fornecedor';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
		$_POST['ativo'] = 'S';
        $sql = montaSQL($_POST, 'pm_fornecedores');
		query($sql);
    }

	public function editar($id) {
		$sql = montaSQL($_POST, 'pm_fornecedores', 'UPDATE', "id = $id");
		query($sql);
	}
}