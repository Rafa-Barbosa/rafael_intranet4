<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class descartes {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['titulo'] = 'Descarte de Materiais';
		$this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        $ret .= $this->getFiltro();

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
                    </div>
                </div>";

        $param = array();
        $p = array();
        $p['onclick'] = "mostraFiltro('filtro_datas');";
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
    }

    private function getDados() {
        $ret = [];
        $where = '';
        $limite = 'LIMIT 100';

        if(isset($_POST['de']) && !empty($_POST['de'])) {
            $de = datas::dataD2S($_POST['de'], '-');
            $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

            $where = "WHERE data >= '$de' AND data <= '$ate'";
            $limite = '';
        }

        $sql = "SELECT * FROM descartes
                $where
                ORDER BY data DESC
                $limite";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['descarte_id'];
                $temp['data'] = str_replace('-', '', $row['data']);
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';
        $itens = [];

        if(!empty($id)) {
            $sql = "SELECT *
                    FROM descartes AS d
                    LEFT JOIN descarte_itens AS i USING(descarte_id)
                    WHERE d.descarte_id = $id AND i.ativo = 'S'";
            $rows = query($sql);

            if(is_array($rows) && count($rows) > 0) {
                $itens['formOS'] = [];

                foreach($rows as $row) {
                    $temp = [];
                    $temp['descarte_item_id']   = $row['descarte_item_id'];
                    $temp['produto_id']         = $row['produto_id'];
                    $temp['quantidade']         = $row['quantidade'];
                    $itens['formOS'][] = $temp;
                }
            }
        }

        $descartes = new descartes_itens();
        $ret = $descartes->index($itens);

        return $ret;        
    }

    public function salvar() {
        $descartes = new descartes_itens();
        $descartes->salvar();

        return $this->index();
    }
}