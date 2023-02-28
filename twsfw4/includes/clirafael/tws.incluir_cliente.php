<?php

/*
 * Data Criacao: 07/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera formulário para criação de clientes
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class incluir_cliente {
    

    public function cadastroCliente($id = '') {
        $ret = '';
		$form = new form01();

		if($id != '') {
			$sql = "SELECT * FROM pm_clientes WHERE id = $id";
			$row = query($sql);
		}

        $param = [];
        $param['campo'] = 'nome';
		$param['etiqueta'] = 'Nome';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['nome'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'sobrenome';
		$param['etiqueta'] = 'Sobrenome';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['sobrenome'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'aniversario';
		$param['etiqueta'] = 'Aniversário';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		$param['obrigatorio'] = true;
		$param['valor'] = isset($row[0]['aniversario']) ? datas::dataS2D($row[0]['aniversario']) : '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'contato';
		$param['etiqueta'] = 'Contato';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row[0]['contato'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
		$param['obrigatorio'] = true;
        $param['tabela_itens'] = '000003';
		$param['valor'] = $row[0]['ativo'] ?? 'S';
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formIncluir_fornecedor');

        $ret .= $form;

        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = 'Incluir Novo Cliente';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
        if(is_array($_POST) && count($_POST) > 0) {
            $param = [];
            $param['nome'] = $_POST['nome'];
            $param['sobrenome'] = $_POST['sobrenome'];
            $param['aniversario'] = datas::dataD2S($_POST['aniversario']);
            $param['contato'] = $_POST['contato'];
            $param['ativo'] = 'S';

            $sql = montaSQL($param, 'pm_clientes');
            query($sql);
        }
    }

    public function editar($id) {
        if(is_array($_POST) && count($_POST) > 0) {
            $param = [];
            $param['nome'] = $_POST['nome'];
            $param['sobrenome'] = $_POST['sobrenome'];
            $param['aniversario'] = datas::dataD2S($_POST['aniversario']);
            $param['contato'] = $_POST['contato'];
            $param['ativo'] = 'S';

            $sql = montaSQL($param, 'pm_clientes', 'UPDATE', "id = $id");
            query($sql);
        }
	}
}