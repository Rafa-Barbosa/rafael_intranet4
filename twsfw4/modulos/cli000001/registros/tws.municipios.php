<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class municipios {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['titulo'] = 'Municipios';
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
			'texto' => 'Incluir Município',
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

        $ret .= $this->_tabela;
        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'id', 'etiqueta' => 'Código', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'municipio', 'etiqueta' => 'Município', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM municipios ORDER BY descricao";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['municipio_id'];
                $temp['municipio'] = $row['descricao'];
                $temp['ativo'] = ($row['ativo'] == 'S') ? 'Sim' : 'Não';
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';
        $apenas_leitura = false;

        if(!empty($id)) {
            $sql = "SELECT * FROM municipios WHERE municipio_id = $id";
            $row = query($sql);
            $row = $row[0];

            $apenas_leitura = true;
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'municipio_id';
		$param['etiqueta'] = 'Código';
		$param['largura'] = '1';
		$param['tipo'] = 'N';
        $param['mascara'] = '0000000';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['municipio_id'] ?? '';
        $param['help'] = 'Utilizar a Tabela do IBGE (Seção 8.2 do MOC – Visão Geral, Tabela de UF, Município e País';
        $param['readonly'] = $apenas_leitura;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'descricao';
		$param['etiqueta'] = 'Município';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['descricao'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['ativo'] ?? '';
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formMunicipio');

        $ret .= $form;

        $titulo = isset($row['descricao']) ? "Município: ".$row['descricao'] : 'Novo Município';
        $titulo .= " <b>===>Utilizar a Tabela do IBGE<===</b>";

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

            if(!empty($id)) {
                $tipo = 'UPDATE';
                $where = "municipio_id = $id";
            } else {
                $tipo = 'INSERT';
                $where = '';
            }

            $temp = [];
            $temp['municipio_id']   = $_POST['municipio_id'];
            $temp['descricao']      = str_replace(["'", '"'], '', $_POST['descricao']);
            $temp['ativo']          = $_POST['ativo'];

            $sql = montaSQL($temp, 'municipios', $tipo, $where);
            query($sql);

            addPortalMensagem('Municípios atualizados com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações do munícipio', 'error');
        }

        return $this->index();
    }
}