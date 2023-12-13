<?php

/*
 * Data Criacao: 28/02/2023
 * Autor: Rafael Postal Barbosa
 *
 * Descricao: gera relatório dos lucros por período
 *
 * Alterações:
 *
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class relatorio_rendimentos {
    var $funcoes_publicas = array(
        'index'             => true,
    );

    // variavel que armazena a classe tabela01
    private $_tabela;

    // variavel que armazena a classe formFiltro01
    private $_filtro;

    // Rendimento somado
    private $_rendimento;

    // Identifica se será um relatório geral ou por produto
    private $_relatorio_geal;

    // Produto filtrado
    private $_produto;

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['ordenacao'] = true;
		$param['titulo'] = 'Relatório por período';
        $param['ordenacao'] = false;
        $param['filtro'] = false;
        $param['imprimeZero'] = false;
		$this->_tabela = new tabela01($param);
        $this->_tabela->setCorLinha('corlinha');

        $programa = 'pm_extrato';
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

        $this->_relatorio_geal = (empty($filtro['id_produto']) || $filtro['id_produto'] == 0) ? true : false;

        // =========== MONTA E APRESENTA A TABELA =================
        $this->montaColunas();
        $dados = [];
        if(!empty($filtro['data_ini'])) {
            if(empty($filtro['data_fim']) || $filtro['data_fim'] < $filtro['data_ini']) {
                $filtro['data_fim'] =date('Ymd');
            }

            $dados = $this->_relatorio_geal ? $this->getDados($filtro) : $this->getDadosPorProduto($filtro);

            $cor = ($this->_rendimento > 0) ? 'primary' : (($this->_rendimento == 0) ? 'info' : 'danger');
            $produto = $this->_relatorio_geal ? '' : " - Produto: <b>{$this->_produto}</b>";

            $ret .= "<div class='alert alert-$cor' role='alert' style='text-align: center;'><h4><b>R$ $this->_rendimento</b> - Período <b>".datas::dataS2D($filtro['data_ini'])."</b> até <b>".datas::dataS2D($filtro['data_fim'])."</b>$produto</h4></div>";
        } else {
            $ret .= "<div class='alert alert-secondary' role='alert' style='text-align: center;'><h4>Selecione um período</h4></div>";
        }
        $this->_tabela->setDados($dados);

        $param = array(
            'texto' => 'Filtrar',
            'cor'   => 'padrão',
            'onclick' => "setLocation('" . getLink() . "index&filtrar=1')",
        );
        $this->_tabela->addBotaoTitulo($param);
        
        $ret .= $this->_tabela;
        
        return $ret;
    }

    private function montaColunas() {
        $this->_tabela->addColuna(array('campo' => 'data', 'etiqueta' => 'Data', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        if($this->_relatorio_geal) {
            $this->_tabela->addColuna(array('campo' => 'participante', 'etiqueta' => 'Participante', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        } else {
            $this->_tabela->addColuna(array('campo' => 'produto', 'etiqueta' => 'Produto', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
            $this->_tabela->addColuna(array('campo' => 'quantidade', 'etiqueta' => 'Quantidade', 'tipo' => 'N', 'width' => 100, 'posicao' => 'E'));
        }
        $this->_tabela->addColuna(array('campo' => 'saida', 'etiqueta' => 'Saída', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'entrada', 'etiqueta' => 'Entrada', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados($param) {
        $ret = [];
        $saida = 0;
        $entrada = 0;

        $sql = "SELECT compra.*, fornecedor.nome_fantasia FROM pm_compra AS compra
                LEFT JOIN pm_fornecedores AS fornecedor ON compra.id_fornecedor = fornecedor.id
                WHERE compra.data >= '{$param['data_ini']}' AND compra.data <= '{$param['data_fim']}'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['data'] = $row['data'];
                $temp['participante'] = !empty($row['nome_fantasia']) ? $row['nome_fantasia'] : 'Fornecedor não informado';
                $temp['saida'] = 'R$ ' . number_format($row['custo'], 2, ',', '.');
                $temp['entrada'] = '----';
                $ret[] = $temp;

                $saida += $row['custo'];
            }
        }

        $sql = "SELECT venda.*, cliente.nome FROM pm_venda AS venda
                LEFT JOIN pm_clientes AS cliente ON venda.cliente = cliente.id
                WHERE venda.data >= '{$param['data_ini']}' AND venda.data <= '{$param['data_fim']}'";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $temp = [];
                $temp['data'] = $row['data'];
                $temp['participante'] = !empty($row['nome']) ? $row['nome'] : 'Cliente não informado';
                $temp['saida'] = '----';
                $temp['entrada'] = 'R$ ' . number_format($row['valor'], 2, ',', '.');
                $ret[] = $temp;

                $entrada += $row['valor'];
            }
        }

        usort($ret, function($a, $b) {
            return strtotime($a['data']) - strtotime($b['data']);
        });

        $temp = [];
        $temp['saida'] = '<b>R$ ' . number_format($saida, 2, ',', '.') . '</b>';
        $temp['entrada'] = '<b>R$ ' . number_format($entrada, 2, ',', '.') . '</b>';
        $ret[] = $temp;

        $this->_rendimento = number_format($entrada - $saida, 2, ',', '.');

        $temp = [];
        $temp['saida'] = "<b>TOTAL</b>";
        $temp['entrada'] = '<b>R$ ' . $this->_rendimento . '</b>';
        $ret[] = $temp;

        return $ret;
    }

    private function getDadosPorProduto($param) {
        $ret = [];
        $saida = 0;
        $entrada = 0;
        $quantidade_total = 0;

        // puxa todas as saidas
        $sql = "SELECT itens.*, compra.data, produto.produto AS nome_produto
                FROM pm_compra_itens AS itens
                    LEFT JOIN pm_compra AS compra ON compra.id = itens.id_compra
                    LEFT JOIN pm_produtos AS produto ON produto.id = itens.produto
                WHERE compra.data >= '{$param['data_ini']}' AND compra.data <= '{$param['data_fim']}'
                    AND itens.produto = {$param['id_produto']}";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            $this->_produto = $rows[0]['nome_produto'];

            foreach($rows as $row) {
                $temp = [];
                $temp['data']       = $row['data'];
                $temp['produto']    = $row['nome_produto'];
                $temp['quantidade'] = $row['quantidade'];
                $temp['saida']      = 'R$ ' . number_format($row['total'], 2, ',', '.');
                $temp['entrada']    = '----';
                $ret[] = $temp;

                $saida += $row['total'];
                $quantidade_total += $row['quantidade'];
            }
        }

        // puxa todas as entradas
        $sql = "SELECT itens.*, venda.data, produto.produto AS nome_produto FROM pm_venda_itens AS itens
                    LEFT JOIN pm_venda AS venda ON venda.id = itens.id_venda
                    LEFT JOIN pm_produtos AS produto ON produto.id = itens.produto
                WHERE venda.data >= '{$param['data_ini']}' AND venda.data <= '{$param['data_fim']}'
                    AND itens.produto = {$param['id_produto']}";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            $this->_produto = $rows[0]['nome_produto'];

            foreach($rows as $row) {
                $temp = [];
                $temp['data']       = $row['data'];
                $temp['produto']    = $row['nome_produto'];
                $temp['quantidade'] = $row['quantidade'];
                $temp['saida']      = '----';
                $temp['entrada']    = 'R$ ' . number_format($row['valor'], 2, ',', '.');
                $ret[] = $temp;

                $entrada += $row['valor'];
                $quantidade_total += $row['quantidade'];
            }
        }

        // ordena pela data
        usort($ret, function($a, $b) {
            return strtotime($a['data']) - strtotime($b['data']);
        });

        $this->_rendimento = number_format($entrada - $saida, 2, ',', '.');
        
        $cor = (($entrada - $saida) >= 0) ? 'success' : 'danger';

        $temp = [];
        $temp['quantidade'] = $quantidade_total;
        $temp['saida']      = 'R$ ' . number_format($saida, 2, ',', '.');
        $temp['entrada']    = 'R$ ' . number_format($entrada, 2, ',', '.');
        $temp['corlinha']   = $cor;
        $temp['negrito']    = true;
        $ret[] = $temp;

        $temp = [];
        $temp['saida']      = "TOTAL";
        $temp['entrada']    = 'R$ ' . $this->_rendimento;
        $temp['corlinha']   = $cor;
        $temp['negrito']    = true;
        $ret[] = $temp;

        return $ret;
    }

}

function getProdutos() {
    $ret = [];
    $ret[] = ['0', 'Todos'];

    $sql = "SELECT id, produto, apos_produto, antes_produto FROM pm_produtos WHERE ativo = 'S'";
    $rows = query($sql);

    if(is_array($rows) && count($rows) > 0) {
        foreach($rows as $row) {
            $temp = [];
            $temp['id'] = $row['id'];
            $temp['produto'] = $row['produto'];
            $temp['antes_produto'] = $row['antes_produto'];

            if($row['apos_produto'] == 0) {
                $primeiro = $temp;
            } else {
                $produtos[$row['id']] = $temp;
            }
        }

        // Organiza pela ordem escolhida
        $ret[] = [$primeiro['id'], $primeiro['produto']];
        $id_proximo = $primeiro['antes_produto'];
        while(isset($produtos[$id_proximo])) {
            $ret[] = [$produtos[$id_proximo]['id'], $produtos[$id_proximo]['produto']];
            $id_proximo = $produtos[$id_proximo]['antes_produto'];
        }
    }

    return $ret;
}