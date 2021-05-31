<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);

function conexaoMysqlTest()
{

    $server = (string) "10.253.0.10";
    $user = (string) "maktubseguros_homol";
    $password = (string) "b6TU3RsoqTWt";
    $database = (string) "maktubseguros_homol";

    $conect = mysqli_connect($server, $user, $password, $database);
    @mysqli_set_charset($conect, 'utf8');

    return $conect;
}

$conect = conexaoMysqlTest();


$operadoras = array();

$sqlOperadoras = "SELECT * from tbl_operadora;";
$selectOperadoras = mysqli_query($conect, $sqlOperadoras);
while ($rsOperadora = mysqli_fetch_assoc($selectOperadoras)) {
    array_push($operadoras, $rsOperadora);
}

$anoAtual = date('Y');
$mes = date('m') - 1;

$dataInicial = "$anoAtual-$mes-01";
$dataFimDeMesAtual = "$anoAtual-$mes-" . cal_days_in_month(CAL_GREGORIAN, $mes, $anoAtual);
$qqtDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anoAtual);

$totalPrevistoMes = (float) 0.0;
$totalPagoMes = (float) 0.0;





// $sql = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  FROM tbl_transacoes as t  inner join tbl_contas as c on t.id_origem = c.id where data_pagamento_operadora = '$dataPesquisa'  and id_destino = 1 and dental = 0 order by id_operadora, id_finalizado;";
// $select = mysqli_query($conect, $sql);
// while ($rsTransacao = mysqli_fetch_assoc($select)) {
//     $valor = $rsTransacao['valor'];
//     $totalPago += $valor;
//     $totalPagoMes += $valor;
//     $color = obter_cor($rsTransacao['id_operadora']);
//     $styleColorText = "style='color:$color;'";
// }

while ($qqtDiasMes > 1) {


    $dia = $qqtDiasMes;

    $totalPrevisto = (float) 0.0;
    $totalPago = (float) 0.0;

    $dataPesquisa = "$anoAtual-$mes-$dia";

    foreach ($operadoras as $operadora) {
        $idOperadora = $operadora['id'];
        $sql = "SELECT * FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0 and id_operadora =  $idOperadora order by id_operadora, id_finalizado ;";

        $select = mysqli_query($conect, $sql);
        while ($rsTransacao = mysqli_fetch_assoc($select)) {
            $idFinalizado = $rsTransacao['id_finalizado'];
            $idOperadora = $rsTransacao['id_operadora'];
            $parcela = $rsTransacao['parcela'];
            $operadora = $rsTransacao['operadora'];
            $valor = $rsTransacao['valor'];
            $data = $rsTransacao['data'];
            $titulo = $rsTransacao['titulo'];

            echo "\nPrevisto $dataPesquisa: $titulo\n";

            $sqlTransacao = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  
            FROM tbl_transacoes as t inner join tbl_contas as c on t.id_origem = c.id 
            WHERE data_pagamento_operadora = '$dataPesquisa' and id_destino = 1 AND id_finalizado = $idFinalizado and parcela = $parcela  and dental = 0 order by id_operadora, id_finalizado;";

            $selectTransacao = mysqli_query($conect, $sqlTransacao);
            if ($selectTransacao = mysqli_fetch_array($selectTransacao)) {
                $descricao = $selectTransacao['descricao'];
                
                echo "Pago $dataPesquisa: $descricao\n";
            } else {
                $sqlTransacao = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  
                    FROM tbl_transacoes as t inner join tbl_contas as c on t.id_origem = c.id 
                    WHERE  id_destino = 1 AND id_finalizado = $idFinalizado and parcela = $parcela  and dental = 0 order by id_operadora, id_finalizado;";

                $selectTransacao = mysqli_query($conect, $sqlTransacao);
                if ($selectTransacao = mysqli_fetch_array($selectTransacao)) {
                    $descricao = $selectTransacao['descricao'];
                    $dataPagamentoOperadora = $selectTransacao['data_pagamento_operadora'];
                    echo "Pago $dataPagamentoOperadora: $descricao\n";
                }
            }
        }
    }





    $qqtDiasMes--;
}
