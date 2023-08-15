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
        $param['pasta'] = 1;
		$param['valor'] = $row[0]['nome'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'cpf_cnpj';
		$param['etiqueta'] = 'CPF/CNPJ';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = $row[0]['cpf_cnpj'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'aniversario';
		$param['etiqueta'] = 'Aniversário';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 1;
		$param['valor'] = isset($row[0]['aniversario']) ? datas::dataS2D($row[0]['aniversario']) : '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'rua';
		$param['etiqueta'] = 'Rua';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['rua'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'numero';
		$param['etiqueta'] = 'N°';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['numero'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'bairro';
		$param['etiqueta'] = 'Bairro';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['bairro'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'municipio';
		$param['etiqueta'] = 'Município';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['municipio'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'uf';
		$param['etiqueta'] = 'UF';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['uf'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['cep'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'pais';
		$param['etiqueta'] = 'País';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 2;
		$param['valor'] = $row[0]['pais'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'telefone';
		$param['etiqueta'] = 'Telefone';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
        $param['pasta'] = 3;
		$param['valor'] = $row[0]['telefone'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'email';
		$param['etiqueta'] = 'E-mail';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
        $param['pasta'] = 3;
		$param['valor'] = $row[0]['email'] ?? '';
        $form->addCampo($param);

        if(!empty($id)) {
            $param = [];
            $param['campo'] = 'ativo';
            $param['etiqueta'] = 'Ativo';
            $param['largura'] = '2';
            $param['tipo'] = 'A';
            $param['obrigatorio'] = true;
            $param['tabela_itens'] = '000003';
            $param['pasta'] = 1;
            $param['valor'] = $row[0]['ativo'] ?? 'S';
            $form->addCampo($param);
        }

        $form->setEnvio(getLink() . "salvar&id=$id", 'formIncluir_fornecedor');
        $form->setPastas([1 => 'Dados Gerais', 2 => 'Endereço', 3 => 'Contato']);

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
            $param['nome']          = $_POST['nome'];
            $param['cpf_cnpj']      = str_replace(['.', '/', '-'], '', $_POST['cpf_cnpj']);
            $param['aniversario']   = datas::dataD2S($_POST['aniversario']);
            $param['telefone']      = str_replace(['(', ')', ' ', '-'], '', $_POST['telefone']);
            $param['email']         = $_POST['email'];
            $param['rua']           = $_POST['rua'];
            $param['numero']        = $_POST['numero'];
            $param['bairro']        = $_POST['bairro'];
            $param['municipio']     = $_POST['municipio'];
            $param['uf']            = $_POST['uf'];
            $param['cep']           = str_replace(['-', '.'], '', $_POST['cep']);
            $param['pais']          = $_POST['pais'];
            $param['ativo']         = 'S';

            $sql = montaSQL($param, 'pm_clientes');
            query($sql);
        }
    }

    public function editar($id) {
        if(is_array($_POST) && count($_POST) > 0) {
            $param = [];
            $param['nome']          = $_POST['nome'];
            $param['cpf_cnpj']      = str_replace(['.', '/', '-'], '', $_POST['cpf_cnpj']);
            $param['aniversario']   = datas::dataD2S($_POST['aniversario']);
            $param['telefone']      = str_replace(['(', ')', ' ', '-'], '', $_POST['telefone']);
            $param['email']         = $_POST['email'];
            $param['rua']           = $_POST['rua'];
            $param['numero']        = $_POST['numero'];
            $param['bairro']        = $_POST['bairro'];
            $param['municipio']     = $_POST['municipio'];
            $param['uf']            = $_POST['uf'];
            $param['cep']           = str_replace(['-', '.'], '', $_POST['cep']);
            $param['pais']          = $_POST['pais'];
            $param['ativo']         = $_POST['ativo'];

            $sql = montaSQL($param, 'pm_clientes', 'UPDATE', "id = $id");
            query($sql);
        }
	}
}