<?php


if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class ordem_produtos {
    var $funcoes_publicas = array(
        'index'             => true,
        'reloadProdutos'    => true,
    );


    public function index() {
        $ret = '';

        $html = "
            <table class='table'>
                <tr>
                    <td>Redefinir ordenação de produtos?</td>
                    <td><a href='".getLink()."reloadProdutos' class='btn btn-primary'>Sim</a></td>
                </tr>
            </table>";

        $param = [
            'titulo' => 'Reloads',
            'conteudo' => $html,
            'cor' => 'secondary'
        ];
        $ret .= addCard($param);

        return $ret;
    }

    public function reloadProdutos() {
        $sql = "SELECT * FROM pm_produtos";
        $rows = query($sql);

        if(is_array($rows) && count($rows) > 0) {
            $apos = 0;

            foreach($rows as $k => $row) {
                $temp = [];
                $temp['apos_produto'] = $apos;
                $temp['antes_produto'] = $rows[$k+1]['id'] ?? 0;

                $sql = montaSQL($temp, 'pm_produtos', 'UPDATE', "id = ".$row['id']);
                query($sql);

                $apos = $row['id'];
            }

            $msgm = "Reload de produtos concluído!";
            $cor = 'success';
        } else {
            $msgm = "Erro ao realizar o reload";
            $cor = 'error';
        }

        addPortalMensagem($msgm, $cor);

        return $this->index();
    }
}