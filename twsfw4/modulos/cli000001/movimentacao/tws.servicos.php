<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class servicos {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['titulo'] = 'Serviços';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        // Cria o campo de filtro
        $ret .= $this->getFiltro();

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        // =============== BOTÕES NO TÍTULO ===============================
		$param = array(
			'texto' => 'Incluir Venda',
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

    private function getFiltro() {
        $ret = '';
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'telefone', 'etiqueta' => 'Telefone', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'veiculo', 'etiqueta' => 'Veículo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'RS', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];
        $tipos = ['Nenhum Especificado', 'Simples', 'Detalhada'];

        $sql = "SELECT vendas.*, clientes.nome, clientes.telefone
                FROM vendas
                LEFT JOIN clientes USING(cliente_id)
                ORDER BY venda_id DESC";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['venda_id'];
                $temp['data'] = str_replace('-', '', $row['data_inc']);
                $temp['cliente'] = $row['nome'];
                $temp['telefone'] = $row['telefone'];
                $temp['tipo'] = $tipos[$row['tipo']];
                $temp['veiculo'] = $row['veiculo'];
                $temp['valor'] = $row['valor'];
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
            $sql = "SELECT vendas.*, clientes.nome
                    FROM vendas
                    LEFT JOIN clientes USING(cliente_id)
                    WHERE venda_id = $id";
            $row = query($sql);
            $row = $row[0];

            $apenas_leitura = true;
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'cliente_id';
		$param['etiqueta'] = 'Cliente';
		$param['largura'] = '3';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = "clientes|cliente_id|nome||ativo='S'";
		$param['obrigatorio'] = true;
		$param['valor'] = $row['cliente_id'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'tipo';
		$param['etiqueta'] = 'Tipo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['opcoes'] = ['-- Selecione --', 'Simples', 'Detalhada'];
		$param['obrigatorio'] = true;
		$param['valor'] = $row['tipo'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'valor';
		$param['etiqueta'] = 'Valor';
		$param['largura'] = '2';
		$param['tipo'] = 'V';
        $param['mascara'] = 'V';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['valor'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'veiculo';
		$param['etiqueta'] = 'Veículo';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 50;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['veiculo'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'obs';
		$param['etiqueta'] = 'Obs';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 150;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['obs'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formServicos');

        $ret .= $form;

        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('" . getLink() . "index')";
		$p['tamanho'] = 'pequeno';
		$p['cor'] = 'danger';
		$p['texto'] = 'Voltar';
		$param['botoesTitulo'][] = $p;
		$param['titulo'] = isset($row['nome']) ? "Editar Venda" : 'Incluir Venda';
		$param['conteudo'] = $ret;
		$ret = addCard($param);

		return $ret;
    }

    public function salvar() {
        if(!empty($_POST)) {
            $id = $_GET['id'];
            $temp = [];

            if(!empty($id)) {
                $tipo = 'UPDATE';
                $where = "venda_id = $id";
            } else {
                $tipo = 'INSERT';
                $where = '';

                $temp['data_inc'] = date('Y-m-d');
            }

            $valor = str_replace('.', '', $_POST['valor']);
            $valor = str_replace(',', '.', $valor);

            $temp['cliente_id'] = $_POST['cliente_id'];
            $temp['tipo']       = $_POST['tipo'];
            $temp['veiculo']    = $_POST['veiculo'];
            $temp['valor']      = $valor;
            $temp['obs']  = str_replace(["'", '"'], '', $_POST['obs']);

            $sql = montaSQL($temp, 'vendas', $tipo, $where);
            query($sql);

            addPortalMensagem('Vendas atualizados com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações do munícipio! Nenhuma alteração realizada.', 'error');
        }

        return $this->index();
    }
}