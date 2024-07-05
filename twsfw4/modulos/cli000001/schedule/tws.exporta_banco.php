<?php
/*
 * Data Criacao: 03/07/2024
 * Autor: Rafael Postal
 *
 * Descricao: Função que exporta o banco de dados para backup
 * 
 * Alterações: 
 * 				
 */

if (!defined('TWSiNet') || !TWSiNet) die('Esta nao e uma pagina de entrada valida!');

ini_set('display_errors', 1);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);

class exporta_banco {
    var $funcoes_publicas = array(
        'schedule'              => true,
    );

    public function schedule() {
        $ret = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
                START TRANSACTION;
                SET time_zone = \"+00:00\"; \n\n";

        $tabelas = query('SHOW TABLES');
        
        if(is_array($tabelas) && count($tabelas) > 0) {
            foreach($tabelas as $tabela) {
                $nome_tabela = $tabela[0];

                $create = query("SHOW CREATE TABLE $nome_tabela");
                $ret .= $create[0]['Create Table'] . ";\n\n";

                $rows = query("SELECT * FROM $nome_tabela");
                if(is_array($rows) && count($rows) > 0) {
                    $insert = [];
                    foreach($rows as $row) {
                        $campos = [];
                        $valores = [];
                        foreach($row as $campo => $valor) {
                            if(!is_numeric($campo)) {
                                $campos[] = "`$campo`";
                                $valor = str_replace("'", '"', $valor);

                                $valores[] = "'$valor'";
                            }
                        }

                        $valores = implode(', ', $valores);
                        $insert[] = "($valores)";
                    }

                    $campos = implode(', ', $campos);
                    $insert = implode(", \n", $insert);
                    $ret .= "INSERT INTO `$nome_tabela` ($campos) VALUES \n $insert;\n\n";
                }
            }
        }

        $caminho = "C:\\User\\Guilherme\\OneDrive\\backups_banco\\";
        if(!file_exists($caminho)) {
            mkdir($caminho, 0777, true);
            chMod($caminho, 0777);
        }
        $caminho .= date('Y_m_d').'.sql';
        file_put_contents($caminho, $ret);

        // echo $ret;
    }
}