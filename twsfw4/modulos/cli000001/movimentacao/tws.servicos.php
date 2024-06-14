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

    // Tipos de labvagem
    private $_tipos = ['Não especificado', 'Simples', 'Detalhada', 'Enchente'];

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
        $form = new form01(['botaoSubmit' => false]);

        $param = [];
		$param['campo'] = 'de';
		$param['etiqueta'] = 'De';
		$param['largura'] = '6';
		$param['tipo'] = 'D';
        $param['valor'] = $_POST['de'] ?? '';
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'ate';
		$param['etiqueta'] = 'Até';
		$param['largura'] = '6';
		$param['tipo'] = 'D';
        $param['valor'] = $_POST['ate'] ?? '';
		$form->addCampo($param);

        $form->setEnvio(getLink() . "index", 'formFiltro');

        $ret = "<div style='display: grid; place-items: center;'>
                    <div style='width: 30%;'>
                        $form
                    </div>
                    <div>
                        <input type='submit' onclick='document.getElementById(\"formFiltro\").submit();' value='Gerar' class='btn btn-primary'>
                        <input type='button' onclick='document.getElementById(\"filtro_datas\").classList.add(\"collapsed-card\");' value='Cancelar' class='btn btn-danger'>
                    </div>
                </div>";

        $param = array();
        $p = array();
        $p['onclick'] = "document.getElementById('filtro_datas').classList.remove('collapsed-card');";
        $p['tamanho'] = 'pequeno';
        $p['cor'] = 'success';
        $p['texto'] = 'Filtrar';
        $p2 = [];
        $param['botoesTitulo'][] = $p;
        $param['versao'] = 1;
        $param['titulo'] = 'Filtro';
        $param['conteudo'] = $ret;
        $param['cor'] = 'success';
        $param['iniciar_minimizado'] = true;
        $param['id'] = 'filtro_datas';
        $ret = addCard($param);

        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'telefone', 'etiqueta' => 'Telefone', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'placa', 'etiqueta' => 'Placa', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'veiculo', 'etiqueta' => 'Veículo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'RS', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];
        $where = '';
        $limite = 'LIMIT 100';

        if(isset($_POST['de']) && !empty($_POST['de'])) {
            $de = datas::dataD2S($_POST['de'], '-');
            $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

            $where = "WHERE data_inc >= '$de' AND data_inc <= '$ate'";
            $limite = '';
        }

        $sql = "SELECT vendas.*, clientes.nome, clientes.telefone
                FROM vendas
                LEFT JOIN clientes USING(cliente_id)
                $where
                ORDER BY venda_id DESC
                $limite";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['venda_id'];
                $temp['data'] = str_replace('-', '', $row['data']);
                $temp['cliente'] = $row['nome'];
                $temp['telefone'] = $row['telefone'];
                $temp['tipo'] = $this->_tipos[$row['tipo']];
                $temp['placa'] = $row['placa'];
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
        $id_baixa = $_GET['id_baixa'] ?? '';
        $visualizar = $_GET['visualizar'] ?? false;
        $url_cancelar = '';

        if(!empty($id)) {
            $sql = "SELECT vendas.*, clientes.nome
                    FROM vendas
                    LEFT JOIN clientes USING(cliente_id)
                    WHERE venda_id = $id";
            $row = query($sql);
            $row = $row[0];
        }
        else if(!empty($id_baixa)) {
            $sql = "SELECT cliente_id, tipo, data, placa, veiculo
                    FROM agenda
                    WHERE agenda_id = $id_baixa";
            $row = query($sql);
            $row = $row[0];

            $url_cancelar = "index.php?menu=movimentacao.agenda.index";
        }

        $param = [];
        $param['cancelar'] = $url_cancelar;
        $form = new form01($param);

        $param = [];
        $param['campo'] = 'data';
		$param['etiqueta'] = 'Data';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		$param['obrigatorio'] = true;
		$param['valor'] = isset($row['data']) ? datas::dataMS2D($row['data']) : date('d/m/Y');
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

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
        $param['opcoes'] = $this->_tipos;
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
        $param['campo'] = 'placa';
		$param['etiqueta'] = 'Placa';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 7;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['placa'] ?? '';
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
		$param['largura'] = '5';
		$param['tipo'] = 'C';
        $param['maxtamanho'] = 150;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['obs'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formServicos');

        $ret .= $form;

        $url_cancelar = empty($url_cancelar) ? getLink() . "index" : $url_cancelar;

        $param = array();
		$p = array();
		$p['onclick'] = "setLocation('$url_cancelar')";
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

            $temp['data']       = datas::dataD2S($_POST['data'], '-');
            $temp['cliente_id'] = $_POST['cliente_id'];
            $temp['tipo']       = $_POST['tipo'];
            $temp['placa']      = strtoupper($_POST['placa']);
            $temp['veiculo']    = $_POST['veiculo'];
            $temp['valor']      = $valor;
            $temp['obs']        = str_replace(["'", '"'], '', $_POST['obs']);

            $sql = montaSQL($temp, 'vendas', $tipo, $where);
            query($sql);

            addPortalMensagem('Vendas atualizados com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações do munícipio! Nenhuma alteração realizada.', 'error');
        }

        return $this->index();
    }
}