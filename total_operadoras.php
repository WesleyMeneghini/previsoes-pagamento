<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/functions.php";
require_once "../includes/connection.php";

$conect = conexaoMysql();

$operadoras = array();
$total = array();

function formatMoeda($number)
{
    return number_format($number, 2, ",", ".");
}

$sqlOperadoras = "SELECT * from tbl_operadora;";
$selectOperadoras = mysqli_query($conect, $sqlOperadoras);
while ($rsOperadora = mysqli_fetch_assoc($selectOperadoras)) {
    array_push($operadoras, $rsOperadora);
}

if (isset($_GET['data_inicial']) && isset($_GET['data_final'])) {

    $dataInicial = $_GET['data_inicial'];
    $dataFinal = $_GET['data_final'];

    $pesquisaPorDatas = "'$dataInicial' AND '$dataFinal'";

    foreach ($operadoras as $operadora) {

        $idOperadora = $operadora['id'];
        $nomeOperadora = $operadora['titulo'];

        $valorPagoPeriodoOperadora = 0;
        $valorPrevistoPeriodoOperadora = 0;

        $sql = "SELECT 
                    sum(comissao) as comissao
                FROM
                    busca_comissoes 
                WHERE
                    (data_pagamento BETWEEN $pesquisaPorDatas)
                AND id_operadora = $idOperadora;";

        $select = mysqli_query($conect, $sql);

        if ($rsPago = mysqli_fetch_assoc($select)) {
            $valorPagoPeriodoOperadora = $rsPago['comissao'];
        }
        
        $sqlPrevisto = "SELECT 
                            sum(valor) as valor_previsto 
                        from 
                            tbl_relatorio_recebimento 
                        where 
                            id_operadora = $idOperadora 
                        and comissao = 0 AND (data BETWEEN $pesquisaPorDatas);";

        $selectPrevisto = mysqli_query($conect, $sqlPrevisto);

        
        if ($rsPrevisto = mysqli_fetch_assoc($selectPrevisto)) {
            $valorPrevistoPeriodoOperadora = $rsPrevisto['valor_previsto'];
        }

        if ($valorPagoPeriodoOperadora > 0.0 || $valorPrevistoPeriodoOperadora > 0.0) {
            
            if ($valorPagoPeriodoOperadora == null) {
                $valorPagoPeriodoOperadora = 0;
            }
            if ($valorPrevistoPeriodoOperadora == null) {
                $valorPrevistoPeriodoOperadora = 0;
            }

            $totalOperadoraPeriodo = [
                'operadora' => $nomeOperadora,
                'total' => $valorPagoPeriodoOperadora,
                'previsto' => $valorPrevistoPeriodoOperadora
            ];
            array_push($total, $totalOperadoraPeriodo);
        }
    }

    echo json_encode($total, JSON_UNESCAPED_UNICODE);
}
