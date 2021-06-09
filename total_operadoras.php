<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/functions.php";
require_once "../includes/connection.php";

$conect = conexaoMysql();

$operadoras = array();
$total = array();


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

        $sql = "SELECT 
                sum(comissao) as comissao
            FROM
                busca_comissoes 
            WHERE
                (data_pagamento BETWEEN $pesquisaPorDatas)
            AND id_operadora = $idOperadora;";

        $select = mysqli_query($conect, $sql);

        if ($rs = mysqli_fetch_assoc($select)) {
            $totalMesOperadora = $rs['comissao'];
            if ($totalMesOperadora > 0.0) {
                $totalOperadora = ['operadora' => $nomeOperadora, 'total' => $totalMesOperadora];
                array_push($total, $totalOperadora);
                // echo "$nomeOperadora => R$ $totalMesOperadora\n";
            }
        }
    }

    echo json_encode($total, JSON_UNESCAPED_UNICODE);
}
