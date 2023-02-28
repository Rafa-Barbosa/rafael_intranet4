<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class produtos {
    var $funcoes_publicas = array(
		'index' 			=> true,
        'avisos'            => true,
        'incluir'           => true,
        'salvar'            => true,
	);


    // variavel que armazena a classe tabela01
    private $_tabela;

    // armazena os nomes dos fornecedores
    private $_fornecedores = [];

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['info'] = false;
		$param['filter'] = false;
		$param['ordenacao'] = false;
		$param['titulo'] = 'Produtos';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        // ================== BOTÕES =================
        $param = array(
			'texto' => 'Incluir novo produto',
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
        $this->_tabela->addColuna(array('campo' => 'produto', 'etiqueta' => 'Produto', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'marca', 'etiqueta' => 'Marca', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'modelo', 'etiqueta' => 'Modelo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cor', 'etiqueta' => 'Cor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ano', 'etiqueta' => 'Ano', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'preco', 'etiqueta' => 'Preço', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'quantidade', 'etiqueta' => 'Quantidade', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'fabricacao', 'etiqueta' => 'Fabricação', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'fornecedor', 'etiqueta' => 'Fornecedor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT pm_produtos.*, (SELECT pm_fornecedores.nome_fantasia FROM pm_fornecedores WHERE pm_fornecedores.id = pm_produtos.id_fornecedor) as fornecedor FROM pm_produtos";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']         = $row['id'];
                $temp['produto']    = $row['produto'];
                $temp['marca']      = $row['marca'];
                $temp['modelo']     = $row['modelo'];
                $temp['cor']        = $row['cor'];
                $temp['ano']        = $row['ano'];
                $temp['preco']      = $row['preco'];
                $temp['quantidade'] = $row['quantidade'];
                $temp['fabricacao'] = $row['fabricacao'];
                $temp['fornecedor'] = $row['fornecedor'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        $form = new incluir_produto();
        $ret = $form->cadastroProduto($id);

        return $ret;
    }

    public function salvar() {
        $id = $_GET['id'] ?? '';
        $form = new incluir_produto();

        if($id == '') {
            $form->salvar();
            $mensagem = "Produto cadastrado com sucesso";
        } else {
            $form->editar($id);
            $mensagem = "Produto editado com sucesso";
        }

        redireciona(getLink() . "avisos&mensagem=$mensagem");
    }
}