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

    function __construct() {
        $param = [];
		$param['width'] = 'AUTO';

		$param['ordenacao'] = true;
		$param['titulo'] = 'Relatório por período';
        $param['ordenacao'] = false;
        $param['filtro'] = false;
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
            if(empty($filtro['data_fim']) || $filtro['data_fim'] < $filtro['data_ini']) {
                $filtro['data_fim'] =date('Ymd');
            }

            $dados = $this->getDados($filtro['data_ini'], $filtro['data_fim']);

            $cor = ($this->_rendimento > 0) ? 'primary' : (($this->_rendimento == 0) ? 'info' : 'danger');

            $ret .= "<div class='alert alert-$cor' role='alert' style='text-align: center;'><h4><b>R$ $this->_rendimento</b> - Período <b>".datas::dataS2D($filtro['data_ini'])."</b> até <b>".datas::dataS2D($filtro['data_fim'])."</b></h4></div>";
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
        $this->_tabela->addColuna(array('campo' => 'participante', 'etiqueta' => 'Participante', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'saida', 'etiqueta' => 'Saída', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'entrada', 'etiqueta' => 'Entrada', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados($data_ini, $data_fim) {
        $ret = [];
        $fornecedores[0] = 'Fornecedor não informado';
        $clientes[0] = 'Cliente não informado';
        $saida = 0;
        $entrada = 0;

        $sql = "SELECT * FROM pm_compra WHERE data >= $data_ini AND data <= $data_fim";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($fornecedores[$row['id_fornecedor']])) {
                    $sql = "SELECT nome_fantasia FROM pm_fornecedores WHERE id = ".$row['id_fornecedor'];
                    $fornecedor = query($sql);

                    $fornecedores[$row['id_fornecedor']] = $fornecedor[0][0];
                }

                $temp = [];
                $temp['data'] = $row['data'];
                $temp['participante'] = $fornecedores[$row['id_fornecedor']];
                $temp['saida'] = 'R$ ' . number_format($row['custo'], 2, ',', '.');
                $temp['entrada'] = '-';
                $ret[] = $temp;

                $saida += $row['custo'];
            }
        }

        $sql = "SELECT * FROM pm_venda WHERE data >= $data_ini AND data <= $data_fim";
        $rows = query($sql);
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($clientes[$row['cliente']])) {
                    $sql = "SELECT nome FROM pm_clientes WHERE id = ".$row['cliente'];
                    $cliente = query($sql);

                    $clientes[$row['cliente']] = $cliente[0][0];
                }

                $temp = [];
                $temp['data'] = $row['data'];
                $temp['participante'] = $clientes[$row['cliente']];
                $temp['saida'] = '-';
                $temp['entrada'] = 'R$ ' . number_format($row['valor'], 2, ',', '.');
                $ret[] = $temp;

                $entrada += $row['valor'];
            }
        }

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

}