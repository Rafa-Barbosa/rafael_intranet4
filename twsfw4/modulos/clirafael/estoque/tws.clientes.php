<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class clientes {
    var $funcoes_publicas = array(
        'index'             => true,
        'avisos'            => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Clientes';
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
			'texto' => 'Incluir novo cliente',
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
			'cor' => 'padrão', //padrão: azul; danger: vermelho; success: verde
			'pos' => 'F',
		);
		$this->_tabela->addAcao($param);

        $ret .= $this->_tabela;

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

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'ID#', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'nome', 'etiqueta' => 'Nome', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'sobrenome', 'etiqueta' => 'Sobrenome', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'aniversario', 'etiqueta' => 'Aniversário', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'contato', 'etiqueta' => 'Contato', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM pm_clientes WHERE ativo = 'S'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['id'];
                $temp['nome'] = $row['nome'];
                $temp['sobrenome'] = $row['sobrenome'];
                $temp['aniversario'] = datas::dataS2D($row['aniversario']);
                $temp['contato'] = $row['contato'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        $form = new incluir_cliente();
        $ret = $form->cadastroCliente($id);

        return $ret;
    }

    public function salvar() {
        $id = $_GET['id'] ?? '';
        $form = new incluir_cliente();

        if($id == '') {
            $form->salvar();
            $mensagem = "Cliente cadastrado com sucesso";
        } else {
            $form->editar($id);
            $mensagem = "Cliente editado com sucesso";
        }

        redireciona(getLink() . "avisos&mensagem=$mensagem");
    }
}