<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class produtos {
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
		$param['titulo'] = 'Produtos';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        $this->gerarAvisos();

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        // =============== BOTÕES NO TÍTULO ===============================
		$param = array(
			'texto' => 'Incluir Produto',
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

    private function gerarAvisos() {
        $sql = "SELECT descricao, quantidade, quantidade_minima FROM produtos WHERE quantidade <= quantidade_minima AND ativo = 'S'";
        $rows = query($sql);

        $limite = [];
        $abaixo = [];
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if($row['quantidade'] == $row['quantidade_minima']) {
                    $limite[] = $row['descricao'];
                } else {
                    $abaixo[] = $row['descricao'];
                }
            }
            
            if(!empty($limite)) {
                $limite = implode(', ', $limite);
                addPortalMensagem("Os produtos (<b>$limite</b>) estão no limite de seu estoque, recomenda-se comprar mais!", 'info');
            }
            if(!empty($abaixo)) {
                $abaixo = implode(', ', $abaixo);
                addPortalMensagem("Os produtos (<b>$abaixo</b>) estão abaixo do seu limite de estoque!", 'warning');
            }
        }
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'descricao', 'etiqueta' => 'Descrição', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'marca', 'etiqueta' => 'Marca', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'modelo', 'etiqueta' => 'Modelo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'quantidade', 'etiqueta' => 'Quantidade', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'quantidade_minima', 'etiqueta' => 'Quantidade Mínima', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'fornecedor', 'etiqueta' => 'Fornecedor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT p.*, f.nome_fantasia
                FROM produtos AS p
                LEFT JOIN fornecedores AS f USING(fornecedor_id)
                ORDER BY descricao";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']                 = $row['produto_id'];
                $temp['descricao']          = $row['descricao'];
                $temp['marca']              = $row['marca'];
                $temp['modelo']             = $row['modelo'];
                $temp['quantidade']         = $row['quantidade'];
                $temp['quantidade_minima']  = $row['quantidade_minima'];
                $temp['fornecedor']         = $row['nome_fantasia'];
                $temp['ativo']              = ($row['ativo'] == 'S') ? 'Sim' : 'Não';
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
            $sql = "SELECT * FROM produtos WHERE produto_id = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'descricao';
		$param['etiqueta'] = 'Descrição';
		$param['largura'] = '4';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['descricao'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'marca';
		$param['etiqueta'] = 'Marca';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['marca'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'modelo';
		$param['etiqueta'] = 'Modelo';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['modelo'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'quantidade';
		$param['etiqueta'] = 'Quantidade';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['quantidade'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'quantidade_minima';
		$param['etiqueta'] = 'Quantidade Mínima';
		$param['largura'] = '2';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['quantidade_minima'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'fornecedor_id';
		$param['etiqueta'] = 'Fornecedor';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = "fornecedores|fornecedor_id|nome_fantasia||ativo='S'";
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['fornecedor_id'] ?? '';
        $param['readonly'] = $visualizar;
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
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formMunicipio');

        $ret .= $form;

        $titulo = isset($row['descricao']) ? "Produto: <b>".$row['descricao'].'</b>' : 'Novo Produto';

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

            // Retira as aspas de todos os campos
            $_POST = array_map(function($v) {
                        return str_replace(['"', "'"], '', $v);
                    }, $_POST);

            if(!empty($id)) {
                $tipo = 'UPDATE';
                $where = "produto_id = $id";
            } else {
                $tipo = 'INSERT';
                $where = '';
            }

            $temp = [];
            $temp['descricao']          = $_POST['descricao'];
            $temp['marca']              = $_POST['marca'];
            $temp['modelo']             = $_POST['modelo'];
            $temp['quantidade']         = $_POST['quantidade'];
            $temp['quantidade_minima']  = $_POST['quantidade_minima'];
            $temp['fornecedor_id']      = $_POST['fornecedor_id'];
            $temp['ativo']              = $_POST['ativo'];

            $sql = montaSQL($temp, 'produtos', $tipo, $where);
            query($sql);

            addPortalMensagem('Produtos atualizados com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações do munícipio. Nenhum alteração realizada!', 'error');
        }

        return $this->index();
    }
}