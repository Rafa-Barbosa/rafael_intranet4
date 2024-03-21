<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class fornecedores {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['paginacao'] = true;
		$param['titulo'] = 'Fornecedores';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        // =============== BOTÕES NO TÍTULO ===============================
		$param = array(
			'texto' => 'Incluir Fornecedor',
			'onclick' => "setLocation('" . getLink() . "incluir')",
		);
		$this->_tabela->addBotaoTitulo($param);

        $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluir&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'success', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

        $param = array(
			'texto' => 'Visualizar', //Texto no botão
			'link' => getLink() . 'incluir&visualizar=1&id=', //Link da página para onde o botão manda
			'coluna' => 'id', //Coluna impressa no final do link
			'width' => 100, //Tamanho do botão
			'flag' => '',
			'tamanho' => 'pequeno', //Nenhum fez diferença?
			'cor' => 'info', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

        $ret .= $this->_tabela;
        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'telefone', 'etiqueta' => 'Telefone', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'email', 'etiqueta' => 'E-mail', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM fornecedores ORDER BY nome_fantasia";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']         = $row['fornecedor_id'];
                $temp['nome']       = $row['nome_fantasia'];
                $temp['telefone']   = $row['telefone'];
                $temp['email']      = $row['email'];
                $temp['ativo']      = ($row['ativo'] == 'S') ? 'Sim' : 'Não';
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';
        $visualizar = $_GET['visualizar'] ?? false;

        if(!empty($id)) {
            $sql = "SELECT * FROM fornecedores
                    LEFT JOIN enderecos USING(endereco_id)
                    WHERE fornecedor_id = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();

        // DADOS GERAIS
        $param = [];
        $param['campo'] = 'nome_fantasia';
		$param['etiqueta'] = 'Nome Fantasia';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['nome_fantasia'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 1;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'razao_social';
		$param['etiqueta'] = 'Razão Social';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['razao_social'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 1;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'cpf_cnpj';
		$param['etiqueta'] = 'CPF/CNPJ';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['cpf_cnpj'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 1;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['ativo'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 1;
        $form->addCampo($param);

        // CONTATO
        $param = [];
        $param['campo'] = 'telefone';
		$param['etiqueta'] = 'Telefone';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['mascara'] = 'telefone';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['telefone'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 2;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'email';
		$param['etiqueta'] = 'E-mail';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['email'] ?? '';
        $param['readonly'] = $visualizar;
        $param['pasta'] = 2;
        $form->addCampo($param);

        // ENDEREÇO
        $endereco_id = $row['endereco_id'] ?? '';
        $form->addHidden('endereco_id', $endereco_id);

        $param = [];
        $param['campo'] = 'logradouro';
		$param['etiqueta'] = 'Logradouro';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 150;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['logradouro'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'numero';
		$param['etiqueta'] = 'Número';
		$param['largura'] = '1';
		$param['tipo'] = 'N';
        $param['mascara'] = 'I';
        $param['maxtamanho'] = 11;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['numero'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'complemento';
		$param['etiqueta'] = 'Complemento';
		$param['largura'] = '3';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 100;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['complemento'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'bairro';
		$param['etiqueta'] = 'Bairro';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 150;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['bairro'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'municipio_id';
		$param['etiqueta'] = 'Município';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = "municipios|municipio_id|descricao||ativo='S'";
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['municipio_id'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'uf';
		$param['etiqueta'] = 'UF';
		$param['largura'] = '1';
		$param['tipo'] = 'C';
        $param['mascara'] = 'AA';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['uf'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'cep';
		$param['etiqueta'] = 'CEP';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['mascara'] = 'cep';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['cep'] ?? '';
        $param['pasta'] = 3;
        $param['readonly'] = $visualizar;
        $form->addCampo($param);


        $form->setEnvio(getLink() . "salvar&id=$id", 'formFornecedor');
        $form->setPastas([1 => 'Dados Gerais', 2 => 'Contato', 3 => 'Endereço']);

        $ret .= $form;

        $titulo = isset($row['nome_fantasia']) ? "Fornecedor: <b>".$row['nome_fantasia'].'</b>' : 'Novo Fornecedor';

        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = $titulo;
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
        if(!empty($_POST)) {
            $id = $_GET['id'];
            $endereco_id = $_POST['endereco_id'];

            // Retira as aspas de todos os campos
            $_POST = array_map(function($v) {
                        return str_replace(['"', "'"], '', $v);
                    }, $_POST);

            // Insere/altera Endereço
            $endereco_validado = $this->validaEndereco();
            if($endereco_validado['validado']) { // Esta inserindo/editando o endereco
                if(empty($endereco_id)) {
                    $sql = "SELECT max(endereco_id) AS max FROM enderecos";
                    $endereco_id = query($sql);
                    $endereco_id = $endereco_id[0]['max'] ?? 0;
                    $endereco_id++;

                    $tipo_endereco = 'INSERT';
                    $where_endereco = '';
                } else {
                    $tipo_endereco = 'UPDATE';
                    $where_endereco = "endereco_id = $endereco_id";
                }

                $temp = [];
                $temp['endereco_id']    = $endereco_id;
                $temp['logradouro']     = $_POST['logradouro'];
                $temp['numero']         = $_POST['numero'];
                $temp['complemento']    = $_POST['complemento'];
                $temp['bairro']         = $_POST['bairro'];
                $temp['municipio_id']   = $_POST['municipio_id'];
                $temp['uf']             = $_POST['uf'];
                $temp['cep']            = $_POST['cep'];
                $sql = montaSQL($temp, 'enderecos', $tipo_endereco, $where_endereco);
                query($sql);
            } else if(!$endereco_validado['nenhum_campo']) { // Só imprime a mensagem caso o usuário esteja tentando cadastrar um endereço
                $campos = implode(', ', $endereco_validado['erros']);
                addPortalMensagem("Erro: O endereço não foi salvo pois os campos $campos estão inválidos", 'error');
            }

            // insere/altera Cliente
            if(empty($id)) {
                $tipo = 'INSERT';
                $where = '';
            } else {
                $tipo = 'UPDATE';
                $where = "fornecedor_id = $id";
            }

            $temp = [];
            $temp['nome_fantasia']  = $_POST['nome_fantasia'];
            $temp['razao_social']   = $_POST['razao_social'];
            $temp['cpf_cnpj']       = $_POST['cpf_cnpj'];
            $temp['telefone']       = $_POST['telefone'];
            $temp['email']          = $_POST['email'];
            $temp['endereco_id']    = $endereco_id;
            $temp['ativo']          = $_POST['ativo'] ?? 'S';
            $sql = montaSQL($temp, 'fornecedores', $tipo, $where);
            query($sql);

            addPortalMensagem("Cadastro atualizado com sucesso");
        } else {
            addPortalMensagem("Erro ao atualizar as informações. Nenhuma alteração realizada!", 'error');
        }

        return $this->index();
    }

    private function validaEndereco() {
        $ret = [];
        $validado = true;
        $erros = [];
        $nenhum_campo = true;

        if(empty($_POST['logradouro']) || strlen($_POST['logradouro']) < 3) {
            $validado = false;
            $erros[] = 'Logradouro';
        } else {
            $nenhum_campo = false;
        }
        if(empty($_POST['numero']) || !is_numeric($_POST['numero'])) {
            $validado = false;
            $erros[] = 'Número';
        } else {
            $nenhum_campo = false;
        }
        if(empty($_POST['bairro']) || strlen($_POST['bairro']) < 3) {
            $validado = false;
            $erros[] = 'Bairro';
        } else {
            $nenhum_campo = false;
        }
        if(empty($_POST['municipio_id']) || !is_numeric($_POST['municipio_id'])) {
            $validado = false;
            $erros[] = 'Município';
        } else {
            $nenhum_campo = false;
        }
        if(empty($_POST['uf']) || strlen($_POST['uf']) != 2) {
            $validado = false;
            $erros[] = 'UF';
        } else {
            $nenhum_campo = false;
        }
        if(empty($_POST['cep']) || strlen($_POST['cep']) != 9) {
            $validado = false;
            $erros[] = 'CEP';
        } else {
            $nenhum_campo = false;
        }

        $ret = [
            'validado' => $validado,
            'erros' => $erros,
            'nenhum_campo' => $nenhum_campo
        ];
        return $ret;
    }
}