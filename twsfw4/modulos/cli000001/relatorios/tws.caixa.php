<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class caixa {
    var $funcoes_publicas = array(
        'index'             => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        $param = [];
        $param['titulo'] = 'Caixa';
        $param['ordenacao'] = false;
        $param['info'] = false;
        $param['filtro'] = false;
        $this->_tabela = new tabela01($param);
    }

    public function index() {
        $ret = '';

        $ret .= $this->getFiltro();

        // =========== MONTA E APRESENTA A TABELA =================
		$this->montaColunas();
		$dados = $this->getDados();
		$this->_tabela->setDados($dados);

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
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'T', 'width' => 100, 'posicao' => 'C'));
        $this->_tabela->addColuna(array('campo' => 'entrada', 'etiqueta' => 'Entrada', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'saida', 'etiqueta' => 'Saída', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];
        $where_compra = '';
        $where_venda = '';
        $total_entrada = 0;
        $total_saida = 0;

        if(!empty($_POST['de'])) {
            $de = datas::dataD2S($_POST['de'], '-');
            $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

            $where_compra = "WHERE c.data <= '$de' AND c.data >= '$ate'";
            $where_venda = "WHERE data_inc <= '$de' AND data_inc >= '$ate'";
        }

        $limite = empty($where_venda) ? 'LIMIT 100' : '';

        $sql = "((SELECT (SELECT SUM(ci.valor * ci.quantidade) FROM compra_itens AS ci WHERE ci.compra_id = c.compra_id) AS valor, c.data, 'S' AS tipo FROM compras AS c $where_compra)
                UNION
                (SELECT valor, data_inc AS data, 'E' AS tipo FROM vendas $where_venda))
                ORDER BY data
                $limite";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $valor = 'R$ ' . number_format($row['valor'], 2, ',', '.');

                $temp = [];
                $temp['data'] = datas::dataMS2D($row['data']);
                if($row['tipo'] == 'S') { // Saída
                    $total_saida += $row['valor'];
                    $temp['saida'] = $valor;
                } else { // Entrada
                    $total_entrada += $row['valor'];
                    $temp['entrada'] = $valor;
                }

                $ret[] = $temp;
            }
        }

        // TOTAL
        $temp = [];
        $temp['saida'] = '<b>R$ ' . number_format($total_saida, 2, ',', '.') . '</b>';
        $temp['entrada'] = '<b>R$ ' . number_format($total_entrada, 2, ',', '.') . '</b>';
        $temp['negrito'] = true;
        $ret[] = $temp;

        $total = $total_entrada - $total_saida;
        $temp = [];
        $temp['entrada'] = '<b>Total</b>';
        $temp['saida'] = '<b>R$' . number_format($total, 2, ',',' .') . '</b>';
        $temp['cor'] = ($total > 0) ? 'primary' : 'danger';
        $ret[] = $temp;

        $this->_tabela->setCorLinha('cor'); // Campo que será usado para definir a cor da linha

        return $ret;
    }
}