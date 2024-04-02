<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class agenda {
    var $funcoes_publicas = array(
        'index'             => true,
        'incluir'           => true,
        'salvar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    function __construct() {
        date_default_timezone_set('America/Sao_Paulo');

        $param = [];
		$param['titulo'] = 'Agenda';
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
        $param['linha'] = 1;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'ate';
		$param['etiqueta'] = 'Até';
		$param['largura'] = '6';
		$param['tipo'] = 'D';
        $param['valor'] = $_POST['ate'] ?? '';
        $param['linha'] = 1;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'cliente';
		$param['etiqueta'] = 'Cliente';
		$param['largura'] = '6';
		$param['tipo'] = 'T';
        $param['maxtamanho'] = 100;
        $param['valor'] = $_POST['cliente'] ?? '';
        $param['linha'] = 2;
		$form->addCampo($param);

        $param = [];
        $param['campo'] = 'placa';
		$param['etiqueta'] = 'Placa';
		$param['largura'] = '6';
		$param['tipo'] = 'T';
        $param['maxtamanho'] = 7;
		$param['valor'] = $_POST['placa'] ?? '';
        $param['linha'] = 2;
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
        $this->_tabela->addColuna(array('campo' => 'dia', 'etiqueta' => 'Dia', 'tipo' => 'D', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'hora', 'etiqueta' => 'Hora', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'cliente', 'etiqueta' => 'Cliente', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'placa', 'etiqueta' => 'Placa', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    private function getDados() {
        $ret = [];
        $where = [];
        $hoje = date('Y-m-d');

        if(isset($_POST['de']) && !empty($_POST['de'])) {
            $de = datas::dataD2S($_POST['de'], '-');
            $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

            $where[] = "a.data >= '$de' AND a.data <= '$ate'";
        } else {
            $de = $hoje;
            $ate = $hoje;
            $where[] = "a.data >= '2024-01-01' AND a.data <= '2024-12-31'";
            $intervalo[0][0] = $hoje;
        }

        $where = implode(' AND ', $where);

        $sql = "SELECT a.*, c.nome
                FROM agenda AS a
                LEFT JOIN clientes AS c USING(cliente_id)
                WHERE a.ativo = 'S' AND $where
                ORDER BY a.data, a.hora";
        $rows = query($sql);

        $dados = [];
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                if(!isset($dados[$row['data']])) {
                    $dados[$row['data']] = [];
                }
                $dados[$row['data']][] = $row;

                if($row['tipo'] == 2) { // Detalhada
                    $data = explode('-', $row['data']);
                    $dia_seguinte = date('Y-m-d', mktime(0, null, null, $data[1], $data[2]+1, $data[0]));
                    $dados[$dia_seguinte] = $row;
                }
            }
        }

        $intervalo = datas::calendario($de, $ate, 'ext', '-');
        if(count($intervalo[0]) > 0) {
            $tipos = ['Não especificado', 'Simples', 'Detalhada'];
            foreach($intervalo[0] as $data) {
                $dado = $dados[$data] ?? [];
                
                if(isset($dado[0]['disponivel'])) {
                    $disponivel = $dado[0]['disponivel'] ?? true;
                } else {
                    // Se existir alguma detalhada agendada para o dia anterior, deve bloquear a agenda
                    $arr_data = explode('-', $data);
                    $dia_anterior = date('Y-m-d', mktime(0, null, null, $arr_data[1], $arr_data[2]-1, $arr_data[0]));

                    $sql = "SELECT * FROM agenda WHERE data = '$dia_anterior' AND tipo = 2 AND ativo = 'S'";
                    $agenda_anterior = query($sql);
                    $disponivel = (is_array($agenda_anterior) && count($agenda_anterior) > 0) ? false : true;
                }

                $hora_atual = '08';
                $min_atual = '00';

                $temp_pos = $dado[0]['hora'] ?? '17:00';
                $temp_pos = explode(':', $temp_pos);
                $hora_pos = $temp_pos[0];
                $min_pos = $temp_pos[1];

                $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));

                while($diferenca_hora >= '03:00' && $hora_atual <= '17:00' && $disponivel) {
                    $temp = [];
                    $temp['id'] = 0;
                    $temp['dia'] = str_replace('-', '', $data);
                    $temp['hora'] = $hora_atual.':'.$min_atual;
                    $temp['cliente'] = '<b>VAGO</b>';
                    $temp['placa'] = '<b>VAGO</b>';
                    $ret[] = $temp;

                    $temp_atual = date('H:i', mktime($hora_atual+3, $min_atual, '00', null, null, null));
                    $temp_atual = explode(':', $temp_atual);
                    $hora_atual = $temp_atual[0];
                    $min_atual = $temp_atual[1];

                    $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));
                }

                foreach($dado as $k => $inf) {
                    $temp = [];
                    $temp['id'] = $inf['agenda_id'];
                    $temp['dia'] = str_replace('-', '', $inf['data']);
                    $temp['hora'] = substr($inf['hora'], 0, 5);
                    $temp['cliente'] = $inf['nome'];
                    $temp['placa'] = $inf['placa'];
                    $temp['tipo'] = $tipos[$inf['tipo']];
                    $ret[] = $temp;

                    $disponivel = ($inf['tipo'] == 2) ? false : true; // Se tiver uma detalhada agendada, bloqueia a agenda

                    $temp_atual = explode(':', $inf['hora']);
                    $hora_atual = $temp_atual[0];
                    $min_atual = $temp_atual[1];

                    if(isset($dado[$k+1])) {
                        $temp_pos = explode(':', $dado[$k+1]['hora']);
                        $hora_pos = $temp_pos[0];
                        $min_pos = $temp_pos[1];
                    } else {
                        $hora_pos = '17';
                        $min_pos = '00';
                    }

                    $temp_atual = date('H:i', mktime($hora_atual+3, $min_atual, '00', null, null, null)); // Acrescenta o tempo de trabalho para considerar o proximo horario disponivel
                    $temp_atual = explode(':', $temp_atual);
                    $hora_atual = $temp_atual[0];
                    $min_atual = $temp_atual[1];

                    $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));

                    while($diferenca_hora >= '03:00' && $hora_atual <= '17:00' && $disponivel) {
                        $temp = [];
                        $temp['id'] = 0;
                        $temp['dia'] = str_replace('-', '', $inf['data']);
                        $temp['hora'] = $hora_atual.':'.$min_atual;
                        $temp['cliente'] = '<b>VAGO</b>';
                        $temp['placa'] = '<b>VAGO</b>';
                        $ret[] = $temp;

                        $temp_atual = date('H:i', mktime($hora_atual+3, $min_atual, '00', null, null, null));
                        $temp_atual = explode(':', $temp_atual);
                        $hora_atual = $temp_atual[0];
                        $min_atual = $temp_atual[1];

                        $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));
                    }
                }
            }
        }

        return $ret;
    }

    public function incluir() {
        $ret = '';
        $id = $_GET['id'] ?? '';
        $visualizar = $_GET['visualizar'] ?? false;

        if(!empty($id)) {
            $sql = "SELECT a.*, c.nome
                    FROM agenda AS a
                    LEFT JOIN clientes AS c USING(cliente_id)
                    WHERE a.agenda_id = $id";
            $row = query($sql);
            $row = $row[0];
        }

        $form = new form01();

        $param = [];
        $param['campo'] = 'cliente_id';
		$param['etiqueta'] = 'Cliente';
		$param['largura'] = '4';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = 'clientes|cliente_id|nome|nome|ativo="S"';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['cliente_id'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'tipo';
		$param['etiqueta'] = 'Tipo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['opcoes'] = ['-- Selecione --', 'Simples', 'Detalhada'];
		$param['obrigatorio'] = true;
		$param['valor'] = $row['tipo'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'data';
		$param['etiqueta'] = 'Dia';
		$param['largura'] = '2';
		$param['tipo'] = 'D';
		$param['obrigatorio'] = true;
		$param['valor'] = isset($row['data']) ? datas::dataMS2D($row['data']) : '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'hora';
		$param['etiqueta'] = 'Hora';
		$param['largura'] = '2';
		$param['tipo'] = 'T';
        $param['mascara'] = 'hora';
		$param['obrigatorio'] = true;
		$param['valor'] = isset($row['hora']) ? substr($row['hora'], 0, 5) : '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'placa';
		$param['etiqueta'] = 'Placa';
		$param['largura'] = '2';
		$param['tipo'] = 'T';
        $param['maxtamanho'] = 7;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['placa'] ?? '';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $param = [];
        $param['campo'] = 'ativo';
		$param['etiqueta'] = 'Ativo';
		$param['largura'] = '2';
		$param['tipo'] = 'A';
        $param['tabela_itens'] = '000003';
		$param['obrigatorio'] = true;
		$param['valor'] = $row['ativo'] ?? 'S';
        $param['readonly'] = $visualizar;
        $form->addCampo($param);

        $form->setEnvio(getLink() . "salvar&id=$id", 'formMunicipio');

        $ret .= $form;

        $titulo = isset($row['data']) ? "Editar <b>".datas::dataMS2D($row['data'])."</b> ".$row['nome'] : 'Novo Agendamento';

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
                $where = "agenda_id = $id";
            } else {
                $tipo = 'INSERT';
                $where = '';
            }

            $temp = [];
            $temp['cliente_id'] = $_POST['cliente_id'];
            $temp['tipo']       = $_POST['tipo'];
            $temp['data']       = datas::dataD2S($_POST['data'], '-');
            $temp['hora']       = $_POST['hora'];
            $temp['placa']      = strtoupper($_POST['placa']);
            $temp['data_inc']   = date('Y-m-d');
            $temp['ativo']      = $_POST['ativo'];
            $sql = montaSQL($temp, 'agenda', $tipo, $where);
            query($sql);

            addPortalMensagem('Agendamento realizado com sucesso');
        } else {
            addPortalMensagem('Erro ao receber as informações. Nenhum alteração realizada!', 'error');
        }

        return $this->index();
    }
}