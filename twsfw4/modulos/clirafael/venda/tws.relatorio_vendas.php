<?php

/*
 * Data Criacao: 07/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera relatório das vendas realizadas
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class relatorio_vendas {
    var $funcoes_publicas = array(
        'index'             => true,
		'avisos'			=> true,
        'detalhado'         => true,
        'vender'            => true,
        'ajax'              => true,
    );

    // variavel que armazena a classe tabela01
    private $_tabela;

    // Informações da tabela pm_venda
    private $_venda = [];

    // variavel que armazena a classe formFiltro01
    private $_filtro;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['ordenacao'] = true;
		$param['titulo'] = 'Vendas';
		$this->_tabela = new tabela01($param);

        $programa = 'pm_venda';
		$param = [];
		$param['botaoTexto'] = 'Enviar';
		$param['imprimePainel'] = false;
		$param['tamanho'] = 12;
		$param['colunas'] = 1;
		$param['layout'] = 'horizontal';
        $param['link'] = getLink() . 'index';
		$this->_filtro = new formFiltro01($programa, $param);
    }

    public function index() {
        $ret = '';
        $filtrar = $_GET['filtrar'] ?? 0;

        $filtro = $this->_filtro->getFiltro();

        if(empty($filtro['data_ini']) || $filtrar) {
            $ret .= $this->_filtro;
        }
        
        // =========== MONTA E APRESENTA A TABELA =================
        $this->montaColunas();
        $dados = [];
        if(!empty($filtro['data_ini'])) {
            $dados = $this->getDados($filtro['data_ini'], $filtro['data_fim']);
        }
        $this->_tabela->setDados($dados);

        $param = array(
            'texto' => 'Filtrar',
            'cor'   => 'padrão',
            'onclick' => "setLocation('" . getLink() . "index&filtrar=1')",
        );
        $this->_tabela->addBotaoTitulo($param);

        $param = array(
            'texto' => 'Nova venda',
            'cor'   => 'padrão',
            'onclick' => "setLocation('" . getLink() . "vender.incluir')",
        );
        $this->_tabela->addBotaoTitulo($param);

        $param = array(
            'texto' => 'Detalhado', //Texto no botão
            'link' => getLink() . 'detalhado&venda=', //Link da página para onde o botão manda
            'coluna' => ['id', 'data', 'cliente', 'valor'], //Coluna impressa no final do link
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
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];

        if(empty($data_fim) || $data_fim < $data_ini) {
            $data_fim =date('Ymd');
        }

        $sql = "SELECT * FROM pm_venda WHERE data >= '$data_ini' AND data <= '$data_fim'";
        $rows = query($sql);

        $clientes = [];
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($clientes[$row['cliente']]) && $row['cliente'] != 0) {
                    $sql = "SELECT nome FROM pm_clientes WHERE id = ".$row['cliente'];
                    $cliente = query($sql);

                    $clientes[$row['cliente']] = $cliente[0][0];
                }

                $temp = [];
                $temp['id'] = $row['id'];
                $temp['data'] = datas::dataS2D($row['data']);
                $temp['cliente'] = $clientes[$row['cliente']] ?? '';
                $temp['valor'] = number_format($row['valor'], 2, ',', '.');

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function detalhado() {
        $this->_venda = explode('|', $_GET['venda']);

        $html = $this->geraHtml($this->_venda[0]);

        $param = array();
        $p = array();
        $p['onclick'] = "setLocation('".getLink()."index')";
        $p['tamanho'] = 'pequeno';
        $p['cor'] = 'danger';
        $p['texto'] = 'Voltar';
        $param['botoesTitulo'][] = $p;
        $param['titulo'] = 'Venda realizada';
        $param['conteudo'] = $html;
        $ret = addCard($param);

        return $ret;
    }

    private function geraHtml($id) {
        $produtos = [];
        $produtos['0'] = 'não informado';

        $sql = "SELECT * FROM pm_venda_itens WHERE id_venda = $id";
        $rows = query($sql);

        $html = '<table class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Data: '.$this->_venda[1].'</th>
                            <th colspan="2">Cliente: '.$this->_venda[2].'</th>
                            <th colspan="2">Valor Total: R$ '.$this->_venda[3].'</th>
                        </tr>
                    </thead>
                    <tbody>';
        if(is_array($rows) && count($rows) > 0) {
            $html .= '<tr>
                        <th>Produto</th>
                        <th>Quantidade</th>
                        <th>Desconto por porcentagem</th>
                        <th>Desconto por valor</th>
                        <th>Valor</th>
                    </tr>';
            foreach($rows as $row) {
                if(!isset($produtos[$row['produto']]) && $row['produto'] != 0) {
                    $sql = "SELECT produto FROM pm_produtos WHERE id = ".$row['produto'];
                    $produto = query($sql);

                    $produtos[$row['produto']] = $produto[0][0];
                }
                $html .= '<tr>
                            <td>'.$produtos[$row['produto']].'</td>
                            <td>'.$row['quantidade'].'</td>
                            <td>'.$row['desconto_porcentagem'].'%</td>
                            <td>R$ '.number_format($row['desconto_valor'], 2, ',', '.').'</td>
                            <td>R$ '.number_format($row['valor'], 2, ',', '.').'</td>
                        </tr>';
            }
        }

        $html .= '  </tbody>
                </table>';

        return $html;
    }

    public function vender() {
        $ret = '';

        $op = getOperacao();
        $compra = new vendas();

        if($op == 'incluir') {
            $ret = $compra->index();
        } else if($op == 'salvar') {
            $ret = $compra->salvar();
        }

        return $ret;
    }

    public function ajax() {
	    $ret = array();
	    
	    $ret[] = array('valor' => '', 'etiqueta' => '');
	    $id = getParam($_GET, 'id', '');
	    
	    if($id != ''){
	        $sql = "SELECT preco FROM pm_produtos WHERE id = $id";
	        $rows = query($sql);
	        if(is_array($rows) && count($rows) > 0){
	            foreach ($rows as $row){
	                $temp = array(
	                    'valor' => $row['preco'],
	                    'etiqueta' => 'valor',
	                );
	                $ret[] = $temp;
	            }
	        }
	    }
	    return json_encode($ret);
	}
}