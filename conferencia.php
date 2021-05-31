<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/functions.php";


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

function styleColorText($color){
    return "style='color:$color;'";
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


echo '<ul class="collapsible">';

while ($qqtDiasMes > 1) {


    $dia = $qqtDiasMes;

    $totalPrevisto = (float) 0.0;
    $totalPago = (float) 0.0;

    $dataPesquisa = "$anoAtual-$mes-$dia";

    $sql = "SELECT id FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0";
    $select = mysqli_query($conect, $sql);

    if (mysqli_num_rows($select) > 0) {

        echo '
        <li class="">
            <div class="collapsible-header"><i class="material-icons">date_range</i>'.$dataPesquisa.'</div>
            <div class="collapsible-body">
                <ul class="collapsible">';

        foreach ($operadoras as $operadora) {
            $idOperadora = $operadora['id'];
            $nomeOperadora = $operadora['titulo'];

            

            $sql = "SELECT id FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0 and id_operadora =  $idOperadora order by id_operadora, id_finalizado;";
            $select = mysqli_query($conect, $sql);

            if (mysqli_num_rows($select) > 0) {

                $color = obter_cor($idOperadora);

                echo '
                <li class="">
                    <div class="collapsible-header"><i class="material-icons" '.styleColorText($color).'>description</i>'.$nomeOperadora.'</div>
                    <div class="collapsible-body">
                        <table>
                            <thead>
                                <tr>
                                    <th>Data Prevista</th>
                                    <th>IdFinalizado</th>
                                    <th>Parcela</th>
                                    <th>Valor Previsto</th>
                                    <th>Descrição</th>
                                    <th>Valor Pago data certa</th>
                                    <th>Outra data de pagamento</th>
                                    <th>Valor Pago fora data</th>
                                </tr>
                            </thead>
                            <tbody>';


                $sql = "SELECT * FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0 and id_operadora =  $idOperadora order by id_operadora, id_finalizado ;";



                $select = mysqli_query($conect, $sql);
                while ($rsPrevisto = mysqli_fetch_assoc($select)) {
                    $idFinalizado = $rsPrevisto['id_finalizado'];
                    $idOperadora = $rsPrevisto['id_operadora'];
                    $parcela = $rsPrevisto['parcela'];
                    $operadora = $rsPrevisto['operadora'];
                    $valor = $rsPrevisto['valor'];
                    $data = $rsPrevisto['data'];
                    $titulo = $rsPrevisto['titulo'];

                    // echo "\nPrevisto $dataPesquisa: $titulo\n";

                    echo "<tr>
                            <td>$dataPesquisa</td>
                            <td>$idFinalizado</td>
                            <td>$parcela</td>
                            <td>$valor</td>
                            <td>$titulo</td>";


                    $sqlTransacao = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  
                    FROM tbl_transacoes as t inner join tbl_contas as c on t.id_origem = c.id 
                    WHERE data_pagamento_operadora = '$dataPesquisa' and id_destino = 1 AND id_finalizado = $idFinalizado and parcela = $parcela  and dental = 0 order by id_operadora, id_finalizado;";

                    $selectTransacao = mysqli_query($conect, $sqlTransacao);
                    if ($selectTransacao = mysqli_fetch_array($selectTransacao)) {
                        $descricao = $selectTransacao['descricao'];
                        $valorPago = $selectTransacao['valor'];

                        // echo "Pago $dataPesquisa: $descricao\n";

                        echo "
                            <td ".styleColorText("green").">$valorPago</td>
                            <td></td>
                            <td></td>";
                    } else {
                        $sqlTransacao = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  
                            FROM tbl_transacoes as t inner join tbl_contas as c on t.id_origem = c.id 
                            WHERE  id_destino = 1 AND id_finalizado = $idFinalizado and parcela = $parcela  and dental = 0 order by id_operadora, id_finalizado;";

                        $selectTransacao = mysqli_query($conect, $sqlTransacao);
                        if ($selectTransacao = mysqli_fetch_array($selectTransacao)) {
                            $descricao = $selectTransacao['descricao'];
                            $dataPagamentoOperadora = $selectTransacao['data_pagamento_operadora'];
                            $dataPagamentoOperadora = $selectTransacao['data_pagamento_operadora'];
                            $valorPago = $selectTransacao['valor'];
                            // echo "Pago $dataPagamentoOperadora: $descricao\n";

                            echo "
                                <td></td>
                                <td>$dataPagamentoOperadora</td>
                                <td ".styleColorText("red").">$valorPago</td>";
                        }
                    }
                    echo "</tr>";
                }

                echo "
                            </tbody>
                        </table>
                    </div>
                </li>
                ";
            }

            
        }

        echo "
                </ul>
            </div>
        </li>";
    }




    $qqtDiasMes--;
}

echo "</ul>";
