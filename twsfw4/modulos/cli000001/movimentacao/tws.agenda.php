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
        'baixar'            => true,
    );

    // Classe tabela01
    private $_tabela;

    // Filtros para a tabela
    private $_de;
    private $_ate;

    // Tipos de labvagem
    private $_tipos = ['Não especificado', 'Simples', 'Detalhada', 'Enchente'];

    function __construct() {
        date_default_timezone_set('America/Sao_Paulo');

        $this->gerarJs();
        $this->setFiltros();

        $param = [];
		$param['titulo'] = "Agenda <b>{$this->_de} - {$this->_ate}</b>";
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
			'texto' => 'Baixar', //Texto no botão
			'link' => getLink() . 'baixar&id=', //Link da página para onde o botão manda
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

    private function setFiltros() {
        if(isset($_POST['de']) && !empty($_POST['de'])) {
            $_SESSION['filtro_agenda']['de'] = $_POST['de'];
            $_SESSION['filtro_agenda']['ate'] = !empty($_POST['ate']) ? $_POST['ate'] : $_POST['de'];
        }

        $this->_de = $_SESSION['filtro_agenda']['de'] ?? date('d/m/Y');
        $this->_ate = $_SESSION['filtro_agenda']['ate'] ?? date('d/m/Y');
    }

    private function getFiltro() {
        $form = new form01(['botaoSubmit' => false]);

        $param = [];
		$param['campo'] = 'de';
		$param['etiqueta'] = 'De';
		$param['largura'] = '6';
		$param['tipo'] = 'D';
        $param['valor'] = $this->_de;
        $param['linha'] = 1;
		$form->addCampo($param);

        $param = [];
		$param['campo'] = 'ate';
		$param['etiqueta'] = 'Até';
		$param['largura'] = '6';
		$param['tipo'] = 'D';
        $param['valor'] = $this->_ate;
        $param['linha'] = 1;
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
        $p['onclick'] = "mostraFiltro()";
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
        $this->_tabela->addColuna(array('campo' => 'veiculo', 'etiqueta' => 'Veiculo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
        $this->_tabela->addColuna(array('campo' => 'tipo', 'etiqueta' => 'Tipo', 'tipo' => 'T', 'width' => 100, 'posicao' => 'E'));
    }

    // private function getDados() {
    //     $ret = [];
    //     $where = [];
    //     $hoje = date('Y-m-d');

    //     if(isset($_POST['de']) && !empty($_POST['de'])) {
    //         $de = datas::dataD2S($_POST['de'], '-');
    //         $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

    //         $where[] = "a.data >= '$de' AND a.data <= '$ate'";
    //     } else {
    //         $de = $hoje;
    //         $ate = $hoje;
    //         $where[] = "a.data >= '2024-01-01' AND a.data <= '2024-12-31'";
    //         $intervalo[0][0] = $hoje;
    //     }

    //     $where = implode(' AND ', $where);

    //     $sql = "SELECT a.*, c.nome, (SELECT COUNT(*) FROM agenda AS a2 WHERE a2.data = a.data AND a2.hora = a.hora AND a2.ativo = 'S') AS total_nessa_hora
    //             FROM agenda AS a
    //             LEFT JOIN clientes AS c USING(cliente_id)
    //             WHERE a.ativo = 'S' AND $where
    //             ORDER BY a.data, a.hora";
    //     $rows = query($sql);

    //     $dados = [];
    //     if(is_array($rows) && count($rows) > 0) {
    //         foreach($rows as $row) {
    //             if(!isset($dados[$row['data']])) {
    //                 $dados[$row['data']] = [];
    //             }
    //             $dados[$row['data']][] = $row;

    //             if($row['tipo'] == 2) { // Detalhada
    //                 $data = explode('-', $row['data']);
    //                 $dia_seguinte = date('Y-m-d', mktime(0, null, null, $data[1], $data[2]+1, $data[0]));
    //                 $dados[$dia_seguinte][] = $row;
    //             }
    //         }
    //     }

    //     $intervalo = datas::calendario($de, $ate, 'ext', '-');
    //     if(count($intervalo[0]) > 0) {
    //         $tipos = ['Não especificado', 'Simples', 'Detalhada'];
    //         foreach($intervalo[0] as $data) {
    //             $dado = $dados[$data] ?? [];
                
    //             if(isset($dado[0]['disponivel'])) {
    //                 $disponivel = $dado[0]['disponivel'] ?? true;
    //             } else {
    //                 // Se existir alguma detalhada agendada para o dia anterior, deve bloquear a agenda
    //                 $arr_data = explode('-', $data);
    //                 $dia_anterior = date('Y-m-d', mktime(0, null, null, $arr_data[1], $arr_data[2]-1, $arr_data[0]));

    //                 $sql = "SELECT * FROM agenda WHERE data = '$dia_anterior' AND tipo = 2 AND ativo = 'S'";
    //                 $agenda_anterior = query($sql);
    //                 $disponivel = (is_array($agenda_anterior) && count($agenda_anterior) > 0) ? false : true;
    //             }

    //             $hora_atual = '08';
    //             $min_atual = '00';

    //             $temp_pos = $dado[0]['hora'] ?? '17:00';
    //             $temp_pos = explode(':', $temp_pos);
    //             $hora_pos = $temp_pos[0];
    //             $min_pos = $temp_pos[1];

    //             $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));

    //             while($diferenca_hora >= '02:00' && $hora_atual <= '17:00' && $disponivel) {
    //                 $temp = [];
    //                 $temp['id'] = "$data|$hora_atual:$min_atual";
    //                 $temp['dia'] = str_replace('-', '', $data);
    //                 $temp['hora'] = $hora_atual.':'.$min_atual;
    //                 $temp['cliente'] = '<b>VAGO</b>';
    //                 $temp['placa'] = '<b>VAGO</b>';
    //                 $ret[] = $temp;

    //                 $temp_atual = date('H:i', mktime($hora_atual+2, $min_atual, '00', null, null, null));
    //                 $temp_atual = explode(':', $temp_atual);
    //                 $hora_atual = $temp_atual[0];
    //                 $min_atual = $temp_atual[1];

    //                 $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));
    //             }

    //             foreach($dado as $k => $inf) {
    //                 $temp = [];
    //                 $temp['id'] = $inf['agenda_id'];
    //                 $temp['dia'] = str_replace('-', '', $inf['data']);
    //                 $temp['hora'] = substr($inf['hora'], 0, 5);
    //                 $temp['cliente'] = $inf['nome'];
    //                 $temp['placa'] = $inf['placa'];
    //                 $temp['tipo'] = $tipos[$inf['tipo']];
    //                 $ret[] = $temp;

    //                 $disponivel = ($inf['tipo'] == 2) ? false : true; // Se tiver uma detalhada agendada, bloqueia a agenda

    //                 $temp_atual = explode(':', $inf['hora']);
    //                 $hora_atual = $temp_atual[0];
    //                 $min_atual = $temp_atual[1];

    //                 if(isset($dado[$k+1])) {
    //                     $temp_pos = explode(':', $dado[$k+1]['hora']);
    //                     $hora_pos = $temp_pos[0];
    //                     $min_pos = $temp_pos[1];
    //                 } else {
    //                     $hora_pos = '17';
    //                     $min_pos = '00';
    //                 }

    //                 $temp_atual = date('H:i', mktime($hora_atual+3, $min_atual, '00', null, null, null)); // Acrescenta o tempo de trabalho para considerar o proximo horario disponivel
    //                 $temp_atual = explode(':', $temp_atual);
    //                 $hora_atual = $temp_atual[0];
    //                 $min_atual = $temp_atual[1];

    //                 $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));

    //                 while($diferenca_hora >= '02:00' && $hora_atual <= '17:00' && $disponivel) {
    //                     $temp = [];
    //                     $temp['id'] = "$data|$hora_atual:$min_atual";
    //                     $temp['dia'] = str_replace('-', '', $inf['data']);
    //                     $temp['hora'] = $hora_atual.':'.$min_atual;
    //                     $temp['cliente'] = '<b>VAGO</b>';
    //                     $temp['placa'] = '<b>VAGO</b>';
    //                     $ret[] = $temp;

    //                     $temp_atual = date('H:i', mktime($hora_atual+2, $min_atual, '00', null, null, null));
    //                     $temp_atual = explode(':', $temp_atual);
    //                     $hora_atual = $temp_atual[0];
    //                     $min_atual = $temp_atual[1];

    //                     $diferenca_hora = date('H:i', mktime($hora_pos-$hora_atual, $min_pos-$min_atual, '00', null, null, null));
    //                 }
    //             }
    //         }
    //     }

    //     return $ret;
    // }

    private function getDados() {
        $ret = [];
        $where = [];

        $de = datas::dataD2S($this->_de, '-');
        $ate = datas::dataD2S($this->_ate, '-');

        // if(isset($_POST['de']) && !empty($_POST['de'])) {
        //     $de = datas::dataD2S($_POST['de'], '-');
        //     $ate = !empty($_POST['ate']) ? datas::dataD2S($_POST['ate'], '-') : $de;

        // } else {
        //     $intervalo[0][0] = $de = $ate = date('Y-m-d');
        // }
        // $where[] = "a.data >= '$de' AND a.data <= '$ate'";

        // $where = implode(' AND ', $where);

        $sql = "SELECT a.*, c.nome, p.quant_dias
                FROM agenda AS a
                LEFT JOIN clientes AS c USING(cliente_id)
                LEFT JOIN agenda_parametros_detalhadas AS p ON a.tipo >= 2 AND a.tipo = p.tipo
                WHERE a.ativo = 'S'
                    AND ((a.data >= '$de' AND a.data <= '$ate') OR a.data >= DATE_ADD('$de', INTERVAL -p.quant_dias DAY))
                ORDER BY a.data, a.hora";
        $rows = query($sql);

        $datas = [];
        $dia_todo = [];
        if(is_array($rows) && count($rows) > 0) {
            foreach($rows as $row) {
                $data_hora = $row['data'] . '|' . substr($row['hora'], 0, 5);
                if(!isset($datas[$data_hora])) {
                    $datas[$data_hora] = 0;
                }
                $datas[$data_hora]++;

                if($row['tipo'] == 1) {
                    $temp = [];
                    $temp['id'] = $row['agenda_id'];
                    $temp['dia'] = str_replace('-', '', $row['data']);
                    $temp['hora'] = substr($row['hora'], 0, 5);
                    $temp['cliente'] = $row['nome'];
                    $temp['placa'] = $row['placa'];
                    $temp['veiculo'] = $row['veiculo'];
                    $temp['tipo'] = $this->_tipos[$row['tipo']];
                    $ret[] = $temp;
                } else {
                    $quant_dias = $row['quant_dias'];
                    $dia_temp = $row['data'];

                    for($i = 1; $i <= $quant_dias; $i++) {
                        $dia_todo[$dia_temp] = true;

                        if($dia_temp >= $de && $dia_temp <= $ate) {
                            $temp = [];
                            $temp['id'] = $row['agenda_id'];
                            $temp['dia'] = str_replace('-', '', $dia_temp);
                            $temp['hora'] = substr($row['hora'], 0, 5);
                            $temp['cliente'] = $row['nome'];
                            $temp['placa'] = $row['placa'];
                            $temp['veiculo'] = $row['veiculo'];
                            $temp['tipo'] = $this->_tipos[$row['tipo']];
                            $ret[] = $temp;
                        }

                        $dia = explode('-', $dia_temp);
                        $dia_temp = date('Y-m-d', mktime(0, null, null, $dia[1], $dia[2]+1, $dia[0]));
                    }
                }

                // if($row['tipo'] == 2) {
                //     $dia = explode('-', $row['data']);
                //     $dia_seguinte = date('Y-m-d', mktime(0, null, null, $dia[1], $dia[2]+1, $dia[0]));

                //     $dia_todo[$row['data']] = true;
                //     $dia_todo[$dia_seguinte] = true;

                //     $temp = [];
                //     $temp['id'] = $row['agenda_id'];
                //     $temp['dia'] = str_replace('-', '', $dia_seguinte);
                //     $temp['hora'] = substr($row['hora'], 0, 5);
                //     $temp['cliente'] = $row['nome'];
                //     $temp['placa'] = $row['placa'];
                //     $temp['tipo'] = $this->_tipos[$row['tipo']];
                //     $ret[] = $temp;
                // }

            }
        }


        $intervalo = datas::calendario($de, $ate, 'ext', '-');
        if(count($intervalo[0]) > 0) {
            $sql = "SELECT * FROM agenda_parametros WHERE ativo = 'S'";
            $parametros = query($sql);

            foreach($intervalo[0] as $data) {
                if(!isset($dia_todo[$data])) {
                    foreach($parametros as $parametro) {
                        $data_hora = $data . '|' . $parametro['hora'];
                        $total = $datas[$data_hora] ?? 0;
                        $diferenca = $parametro['quant_carros'] - $total;

                        if($diferenca > 0) {
                            for($i = 1; $i <= $diferenca; $i++) {
                                $temp = [];
                                $temp['id'] = "$data|".$parametro['hora'];
                                $temp['dia'] = str_replace('-', '', $data);
                                $temp['hora'] = $parametro['hora'];
                                $temp['cliente'] = '<b>VAGO</b>';
                                $temp['placa'] = '<b>VAGO</b>';
                                $temp['veiculo'] = '<b>VAGO</b>';
                                $ret[] = $temp;
                            }
                        }
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
            if(is_numeric($id)) {
                $sql = "SELECT a.*, c.nome
                        FROM agenda AS a
                        LEFT JOIN clientes AS c USING(cliente_id)
                        WHERE a.agenda_id = $id";
                $row = query($sql);
                $row = $row[0];
            } else {
                $baixar = explode('|', $id);
                $row['data'] = $baixar[0];
                $row['hora'] = $baixar[1];
            }
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
        $param['opcoes'] = $this->_tipos;
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
        $param['campo'] = 'veiculo';
		$param['etiqueta'] = 'Veículo';
		$param['largura'] = '2';
		$param['tipo'] = 'T';
        $param['maxtamanho'] = 50;
		// $param['obrigatorio'] = true;
		$param['valor'] = $row['veiculo'] ?? '';
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

        $titulo = isset($row['nome']) ? "Editar <b>".datas::dataMS2D($row['data'])."</b> ".$row['nome'] : 'Novo Agendamento';

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
            $temp['veiculo']    = $_POST['veiculo'];
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

    public function baixar() {
        $ret = '';
        $id = $_GET['id'];

        if(is_numeric($id)) {
            redireciona("index.php?menu=movimentacao.servicos.incluir&id_baixa=$id");
        } else {
            $ret .= $this->incluir();
        }

        return $ret;
    }

    private function gerarJs() {
        $js = "
        function mostraFiltro() {
            var div_filtro = document.getElementById('filtro_datas');
            var escondido = div_filtro.classList.contains('collapsed-card');
            
            if(escondido) {
                div_filtro.classList.remove('collapsed-card');
            } else {
                div_filtro.classList.add('collapsed-card');
            }
        }";

        addPortaljavaScript($js, 'F');
    }
}