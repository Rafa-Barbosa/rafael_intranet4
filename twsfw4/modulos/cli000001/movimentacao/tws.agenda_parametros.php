<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class agenda_parametros {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
        'incluirDetalhadas' => true,
        'salvarDetalhadas'  => true,
    );

    // Classe tabela01
    private $_tabela;

    // Tipos de labvagem
    private $_tipos = ['Não especificado', 'Simples', 'Detalhada', 'Enchente'];

    function __construct() {
        
    }

    private function instanciarTabela($titulo) {
        $param = [];
		$param['titulo'] = $titulo;
        $param['ordenacao'] = false;
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        $ret .= $this->tabelaSimples();
        $ret .= $this->tabelaDetalhadas();

        return $ret;
    }

    private function tabelaSimples() {
        $ret = '';

        $this->instanciarTabela('Parâmetros Lavagem Simples');

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

        // =============== BOTÕES NO TÍTULO ===============================
		$param = array(
			'texto' => 'Incluir',
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
        $this->_tabela->addColuna(array('campo' => 'quant_carros', 'etiqueta' => 'Quantidade de Carros', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'hora', 'etiqueta' => 'Hora', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'ativo', 'etiqueta' => 'Ativo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT * FROM agenda_parametros ORDER BY hora";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['agenda_parametros_id'];
                $temp['quant_carros'] = $row['quant_carros'];
                $temp['hora'] = $row['hora'];
                $temp['ativo'] = ($row['ativo'] == 'S') ? 'Sim' : 'Não';
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        if(!empty($id)) {
            $sql = "SELECT * FROM agenda_parametros WHERE agenda_parametros_id = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'quant_carros';
		$param['etiqueta'] = 'Quantidade de Carros';
		$param['largura'] = '3';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['quant_carros'] ?? 1;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'hora';
		$param['etiqueta'] = 'Hora';
		$param['largura'] = '2';
		$param['tipo'] = 'C';
        $param['mascara'] = '00:00';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['hora'] ?? '';
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['ativo'] ?? 'S';
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formParametrosSimples');

        $ret .= $form;

        $titulo = isset($row['hora']) ? "Parâmetro: ".$row['hora'] : 'Novo Parâmetro';

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
                $where = "agenda_parametros_id = $id";
            } else {
                $tipo = 'INSERT';
                $where = '';
            }

            $temp = [];
            $temp['quant_carros']   = $_POST['quant_carros'];
            $temp['hora']           = $_POST['hora'];
            $temp['ativo']          = $_POST['ativo'];

            $sql = montaSQL($temp, 'agenda_parametros', $tipo, $where);
            query($sql);

            addPortalMensagem('Parâmetros atualizados com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações do munícipio', 'error');
        }

        return $this->index();
    }

    private function tabelaDetalhadas() {
        $ret = '';

        $this->instanciarTabela('Parâmetros Lavagens Detalhadas');

        $this->montaColunasDetalhadas();
        $dados = $this->getDadosDetalhadas();
        $this->_tabela->setDados($dados);

        $param = array(
			'texto' => 'Editar', //Texto no botão
			'link' => getLink() . 'incluirDetalhadas&id=', //Link da página para onde o botão manda
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

    private function montaColunasDetalhadas() {
        $this->_tabela->addColuna(array('campo' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'quant_dias', 'etiqueta' => 'Quantidade de Dias', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDadosDetalhadas() {
        $ret = [];

        $sql = "SELECT * FROM agenda_parametros_detalhadas WHERE ativo = 'S'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['agenda_parametros_detalhadas_id'];
                $temp['tipo'] = $this->_tipos[$row['tipo']];
                $temp['quant_dias'] = $row['quant_dias'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluirDetalhadas() {
        $ret = '';
        $id = $_GET['id'] ?? '';

        if(!empty($id)) {
            $sql = "SELECT * FROM agenda_parametros_detalhadas WHERE agenda_parametros_detalhadas_id = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'tipo';
		$param['etiqueta'] = 'Tipo';
		$param['largura'] = '3';
		$param['tipo'] = 'T';
		$param['obrigatorio'] = true;
		$param['valor'] = isset($row['tipo']) ? $this->_tipos[$row['tipo']] : '';
        $param['readonly'] = true;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'quant_dias';
		$param['etiqueta'] = 'Quantidade de Dias';
		$param['largura'] = '3';
		$param['tipo'] = 'N';
        $param['mascara'] = 'N';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['quant_dias'] ?? '';
        $form->addCampo($param);

        // $param = [];
        // $param['campo'] = 'ativo';
		// $param['etiqueta'] = 'Ativo';
		// $param['largura'] = '2';
		// $param['tipo'] = 'A';
        // $param['tabela_itens'] = '000003';
		// $param['obrigatorio'] = true;
		// $param['valor'] = $row['ativo'] ?? 'S';
        // $form->addCampo($param);

        $form->setEnvio(getLink() . "salvarDetalhadas&id=$id", 'formParametrosDetalhadas');

        $ret .= $form;

        $titulo = "Parâmetros <b>{$this->_tipos[$row['tipo']]}</b>";

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

    public function salvarDetalhadas() {
        $ret = '';

        if(!empty($_POST)) {
            $id = $_GET['id'] ?? '';

            if(empty($id)) {
                $tipo = 'INSERT';
                $where = '';
            } else {
                $tipo = 'UPDATE';
                $where = "agenda_parametros_detalhadas_id = $id";
            }

            $temp = [];
            $temp['quant_dias'] = $_POST['quant_dias'];
            // $temp['ativo'] = $_POST['ativo'];
            $sql = montaSQL($temp, 'agenda_parametros_detalhadas', $tipo, $where);
            query($sql);

            addPortalMensagem('Parâmetros salvos com sucesso!');
            $ret = $this->index();
        } else {
            addPortalMensagem('Erro ao receber as informações. Nenhuma alteração realizada!', 'error');
            $ret = $this->incluirDetalhadas();
        }

        return $ret;
    }
}