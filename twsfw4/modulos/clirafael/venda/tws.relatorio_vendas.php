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
        'editar'            => true,
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
            'link' => getLink() . 'detalhado&id=', //Link da página para onde o botão manda
            'coluna' => 'id', //Coluna impressa no final do link
            'width' => 100, //Tamanho do botão
            'flag' => '',
            'tamanho' => 'pequeno', //Nenhum fez diferença?
            'cor' => 'padrao', //padrão: azul; danger: vermelho; success: verde
            'pos' => 'F',
        );
        $this->_tabela->addAcao($param);

        $param = array(
            'texto' => 'Editar', //Texto no botão
            'link' => getLink() . 'editar&id=', //Link da página para onde o botão manda
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
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'V', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'forma_pagamento', 'etiqueta' => 'Forma de Pagamento', 'tipo' => 'T', 'width' => 200, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];

        if(empty($data_fim) || $data_fim < $data_ini) {
            $data_fim =date('Ymd');
        }

        $sql = "SELECT pm_venda.*, pm_clientes.nome AS nome_cliente FROM pm_venda
                LEFT JOIN pm_clientes ON pm_venda.cliente = pm_clientes.id
                WHERE pm_venda.data >= '$data_ini' AND pm_venda.data <= '$data_fim'";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['id']                 = $row['id'];
                $temp['data']               = $row['data'];
                $temp['cliente']            = $row['nome_cliente'] ?? '';
                $temp['valor']              = $row['valor'];
                $temp['forma_pagamento']    = $row['forma_pagamento'];

                $ret[] = $temp;
            }
        }

        return $ret;
    }

    public function detalhado() {
        $id = $_GET['id'];

        $html = $this->geraHtml($id);

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
        $sql = "SELECT itens.quantidade, itens.desconto_porcentagem, itens.desconto_valor, itens.valor AS valor_item,
                venda.data, venda.valor AS valor_total, venda.forma_pagamento, cliente.nome, produto.produto
                FROM pm_venda_itens AS itens
                LEFT JOIN pm_venda AS venda ON venda.id = itens.id_venda
                LEFT JOIN pm_clientes AS cliente ON cliente.id = venda.cliente
                LEFT JOIN pm_produtos AS produto ON produto.id = itens.produto
                WHERE itens.id_venda = $id";
        $rows = query($sql);

        $html = '<table class="table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Data: '.Datas::dataS2D($rows[0]['data']).'</th>
                            <th colspan="2">Cliente: '.$rows[0]['nome'].'</th>
                            <th>Valor Total: R$ '.number_format($rows[0]['valor_total'], 2, ',', '.').'</th>
                            <th>Forma de Pagamento: '.$rows[0]['forma_pagamento'].'</th>
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
                $html .= '<tr>
                            <td>'.$row['produto'].'</td>
                            <td>'.$row['quantidade'].'</td>
                            <td>'.$row['desconto_porcentagem'].'%</td>
                            <td>R$ '.number_format($row['desconto_valor'], 2, ',', '.').'</td>
                            <td>R$ '.number_format($row['valor_item'], 2, ',', '.').'</td>
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

    public function editar() {
        $ret = '';
        $id = $_GET['id'];

        $sql = "SELECT venda.cliente, venda.forma_pagamento, itens.produto as id_produto, itens.id AS id_item, itens.quantidade,
                    itens.desconto_porcentagem, itens.desconto_valor, itens.valor AS com_desconto, venda.valor AS total_venda
                    , produto.preco
                FROM pm_venda as venda
                LEFT JOIN pm_venda_itens as itens ON itens.id_venda = venda.id
                LEFT JOIN pm_produtos AS produto ON produto.id = itens.produto
                WHERE venda.id = $id";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $param = [];
            $param['id_venda'] = $id;
            $param['cliente'] = $rows[0]['cliente'];
            $param['forma_pagamento'] = $rows[0]['forma_pagamento'];
            $param['total'] = $rows[0]['total_venda'];

            $param['formOS'] = [];
            foreach($rows as $row) {
                $temp = [];
                $temp['id_item']                = $row['id_item'];
                $temp['id_produto']             = $row['id_produto'];
                $temp['quantidade']             = $row['quantidade'];
                $temp['desconto_porcentagem']   = $row['desconto_porcentagem'];
                $temp['desconto_valor']         = $row['desconto_valor'];
                $temp['valor_produto']          = 'R$ ' . number_format($row['preco'], 2, ',', '.');
                $temp['com_desconto']           = 'R$ ' . number_format($row['com_desconto'], 2, ',', '.');

                $param['formOS'][] = $temp;
            }

            $venda = new vendas();
            $ret = $venda->index($param);
        } else {
            addPortalMensagem('Erro ao encontrar a venda', 'error');
            $ret = $this->index();
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