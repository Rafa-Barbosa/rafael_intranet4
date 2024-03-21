<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class compras {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
		$param['titulo'] = 'Compras de Materiais';
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
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'fornecedor', 'etiqueta' => 'Fornecedor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];

        $sql = "SELECT c.compra_id, c.data, f.nome_fantasia
                FROM compras AS c
                LEFT JOIN fornecedores AS f USING(fornecedor_id)
                ORDER BY data";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id'] = $row['compra_id'];
                $temp['data'] = str_replace('-', '', $row['data']);
                $temp['fornecedor'] = $row['nome_fantasia'];
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
                    FROM compras AS c
                    LEFT JOIN compra_itens AS i USING(compra_id)
                    WHERE c.compra_id = $id AND i.ativo = 'S'";
            $rows = query($sql);

            if(is_array($rows) && count($rows) > 0) {
                $itens['fornecedor_id'] = $rows[0]['fornecedor_id'];
                $itens['total'] = 0;
                $itens['formOS'] = [];

                foreach($rows as $row) {
                    $temp = [];
                    $temp['compra_item_id'] = $row['compra_item_id'];
                    $temp['produto_id'] = $row['produto_id'];
                    $temp['valor_produto'] = $row['valor'];
                    $temp['quantidade'] = $row['quantidade'];
                    $temp['valor_total'] = $row['valor'] + $row['quantidade'];
                    $itens['formOS'][] = $temp;

                    $itens['total'] += $temp['valor_total'];
                }
            }
        }

        $compras = new compras_itens();
        $ret = $compras->index($itens);

        return $ret;        
    }

    public function salvar() {
        $compras = new compras_itens();
        $compras->salvar();

        return $this->index();
    }
}