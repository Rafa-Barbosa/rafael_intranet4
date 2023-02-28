<?php

/*
 * Data Criacao: 23/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera relatório das compras realizadas
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class relatorio_compras {
    var $funcoes_publicas = array(
        'index'             => true,
		'avisos'			=> true,
        'vender'            => true,
        'detalhado'         => true,
    );

    // variavel que armazena a classe tabela01
    private $_tabela;

    // variavel que armazena a classe formFiltro01
    private $_filtro;

    // detalhe compra
    private $_compra;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['ordenacao'] = true;
		$param['titulo'] = 'Compras';
		$this->_tabela = new tabela01($param);

        $programa = 'pm_venda';
		$param = [];
		$param['botaoTexto'] = 'Enviar';
		$param['imprimePainel'] = false;
		$param['tamanho'] = 12;
		$param['colunas'] = 1;
		$param['layout'] = 'horizontal';
		$this->_filtro = new formFiltro01($programa, $param);
    }

    public function index() {
        $ret = '';

        $filtro = $this->_filtro->getFiltro();

        $ret .= $this->_filtro;

        // =========== MONTA E APRESENTA A TABELA =================
        $this->montaColunas();
        $dados = [];
        if(!empty($filtro['data_ini'])) {
            $dados = $this->getDados($filtro['data_ini'], $filtro['data_fim']);
        }
        $this->_tabela->setDados($dados);

        $param = array(
            'texto' => 'Nova compra',
            'cor'   => 'success',
            'onclick' => "setLocation('" . getLink() . "vender.incluir')",
        );
        $this->_tabela->addBotaoTitulo($param);

        $param = array(
            'texto' => 'Detalhado', //Texto no botão
            'link' => getLink() . 'detalhado&compra=', //Link da página para onde o botão manda
            'coluna' => ['id', 'data', 'fornecedor', 'custo'], //Coluna impressa no final do link
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
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'fornecedor', 'etiqueta' => 'Fornecedor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'custo', 'etiqueta' => 'Custo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];
        $fornecedores = [];

        if(empty($data_fim) || $data_fim < $data_ini) {
            $data_fim =date('Ymd');
        }

        $sql = "SELECT * FROM pm_compra WHERE data >= '$data_ini' AND data <= '$data_fim'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($fornecedores[$row['id_fornecedor']])) {
                    $sql = "SELECT nome_fantasia FROM pm_fornecedores WHERE id = ".$row['id_fornecedor'];
                    $forn = query($sql);

                    $fornecedores[$row['id_fornecedor']] = $forn[0]['nome_fantasia'];
                }

                $temp = [];
                $temp['id']         = $row['id'];
                $temp['data']       = datas::dataS2D($row['data']);
                $temp['fornecedor'] = $fornecedores[$row['id_fornecedor']];
                $temp['custo']      = number_format($row['custo'], 2, ',', '.');
                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function vender() {
        $ret = '';

        $op = getOperacao();
        $venda = new compras();

        if($op == 'incluir') {
            $ret = $venda->index();
        } else if($op == 'salvar') {
            $ret = $venda->salvar();
        }

        return $ret;
    }

    public function detalhado() {
        $this->_compra = explode('|', $_GET['compra']);

        $html = $this->geraHtml();

        $param = array();
        $p = array();
        $p['onclick'] = "setLocation('".getLink()."index')";
        $p['tamanho'] = 'pequeno';
        $p['cor'] = 'danger';
        $p['texto'] = 'Voltar';
        $param['botoesTitulo'][] = $p;
        $param['titulo'] = 'Compra realizada';
        $param['conteudo'] = $html;
        $ret = addCard($param);

        return $ret;
    }

    private function geraHtml() {
        $id = $this->_compra[0];
        $data = $this->_compra[1];
        $fornecedor = $this->_compra[2];
        $custo = $this->_compra[3];

        $produtos = [];
        $produtos['0'] = 'não informado';

        $sql = "SELECT * FROM pm_compra_itens WHERE id_compra = $id";
        $rows = query($sql);

        $html = '<table class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Data: '.$data.'</th>
                            <th>Cliente: '.$fornecedor.'</th>
                            <th>Custo Total: R$ '.$custo.'</th>
                        </tr>
                    </thead>
                    <tbody>';

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($produtos[$row['produto']])) {
                    $sql = "SELECT produto FROM pm_produtos WHERE id = ".$row['produto'];
                    $prod = query($sql);

                    $produtos[$row['produto']] = $prod[0]['produto'];
                }
                $html .= '<tr>
                            <td><strong>Produto:</strong> '.$produtos[$row['produto']].'</td>
                            <td><strong>Quantidade:</strong> '.$row['quantidade'].'</td>
                            <td><strong>Custo:</strong> '.number_format($row['custo'], 2, ',', '.').'</td>
                        </tr>';
            }
        } else {
            $html .= '<tr><td colspan="3">Erro, não foram encontrados produtos para está venda</td></tr>';
        }

        $html .= '  </tbody>
                </table>';

                return $html;
    }
}