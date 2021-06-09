<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../includes/functions.php";
require_once "../includes/connection.php";

function styleColorText($color){
    return 'style="color:'.$color.';"';
}

function formatMoeda($number){
    return number_format($number, 2, ",", ".");
}

$conect = conexaoMysql();


$operadoras = array();

$sqlOperadoras = "SELECT * from tbl_operadora;";
$selectOperadoras = mysqli_query($conect, $sqlOperadoras);
while ($rsOperadora = mysqli_fetch_assoc($selectOperadoras)) {
    array_push($operadoras, $rsOperadora);
}

$html = "";

if (isset($_GET['data_inicial']) && isset($_GET['data_final'])) {

    $dataInicial = $_GET['data_inicial'];
    $dataFinal = $_GET['data_final'];

    $diferencaDias = floor(
        (strtotime($dataFinal) - strtotime($dataInicial)) / (60*60*24)
    );

    $totalPrevistoMes = (float) 0.0;
    $totalPagoMes = (float) 0.0;

    $pesquisaPorDatas = "'$dataInicial' AND '$dataFinal'";

    $sqlTotalMes = "SELECT 
                        SUM(relatorio.valor) AS valor_total_previsto_mes,
                    (SELECT 
                            SUM(comissao)
                        FROM
                            busca_comissoes 
                        WHERE
                            (data_pagamento BETWEEN $pesquisaPorDatas)
                                AND dental >= 0 ) AS valor_total_pago_mes
                    FROM
                        tbl_relatorio_recebimento as relatorio INNER JOIN tbl_finalizado as f on relatorio.id_finalizado = f.id
                    WHERE
                        (relatorio.data BETWEEN $pesquisaPorDatas) AND comissao = 0 AND f.vitalicio = 0;";

    $selectTotalMes = mysqli_query($conect, $sqlTotalMes);
    if ($rsTotalMes = mysqli_fetch_assoc($selectTotalMes)){
        $valorTotalPrevistoMes = $rsTotalMes['valor_total_previsto_mes'];
        $valorTotalPagoMes = $rsTotalMes['valor_total_pago_mes'];
    }
    // echo $sqlTotalMes;

    $html .= '
    <h5>
        <span '.styleColorText('#8f8f8f').'>
            Valor Previsto Mês: R$ '.formatMoeda($valorTotalPrevistoMes).'
        </span>
        &nbsp
        | 
        &nbsp
        <span '.styleColorText('green').'>
            Valor Pago Mês: R$ '.formatMoeda($valorTotalPagoMes).'
        </span>
    </h5>';


    $html .= '<ul class="collapsible">';

    $count = 0;
    while ( $diferencaDias >= $count) {


        $totalPrevisto = (float) 0.0;
        $totalPago = (float) 0.0;

        $dataPesquisa = date("Y-m-d", strtotime($dataInicial . " +$count days"));

        $sql = "SELECT id FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0";
        $select = mysqli_query($conect, $sql);

        if (mysqli_num_rows($select) > 0) {

            $sqlTotalDia = "SELECT 
                                SUM(valor) AS valor_total_dia_previsto,
                                (SELECT 
                                        SUM(comissao)
                                    FROM
                                        busca_comissoes
                                    WHERE
                                        data_pagamento = '$dataPesquisa'
                                            AND dental = 0) AS valor_total_pago_dia
                            FROM
                                tbl_relatorio_recebimento
                            WHERE
                                data = '$dataPesquisa' AND comissao = 0;";
            $selectTotalDia = mysqli_query($conect, $sqlTotalDia);
            if ($rsTotalDia = mysqli_fetch_assoc($selectTotalDia)){
                $valorTotalPrevistoDia = $rsTotalDia['valor_total_dia_previsto'];
                $valorTotalPagoDia = $rsTotalDia['valor_total_pago_dia'];
            }

            $html .= '
            <li class="">
                <div class="collapsible-header"><i class="material-icons">date_range</i>'.$dataPesquisa.' 
                    <div style="margin-left: auto;">
                        <span '.styleColorText("#8f8f8f").'>
                            Valor Previsto: R$ '.formatMoeda($valorTotalPrevistoDia).'
                        </span>
                        &nbsp
                        | 
                        &nbsp
                        <span '.styleColorText("green").'>
                            Valor Pago: R$ '.formatMoeda($valorTotalPagoDia).'
                        </span>
                    </div>
                </div>
                <div class="collapsible-body">
                    <ul class="collapsible">';

            foreach ($operadoras as $operadora) {
                $idOperadora = $operadora['id'];
                $nomeOperadora = $operadora['titulo'];

                

                $sql = "SELECT 
                            r.id as id_relatorio,
                            id_finalizado,
                            r.id_operadora as id_operadora,
                            parcela,
                            r.operadora as nome_operadora,
                            r.valor as valor,
                            r.data as data_previsao,
                            r.titulo as titulo,
                            f.data_pagamento as data_pagamento_inicial
                        FROM tbl_relatorio_recebimento as r
                            inner join tbl_finalizado as f on r.id_finalizado = f.id
                        WHERE r.data = '$dataPesquisa' and r.comissao = 0 and r.id_operadora = $idOperadora and f.vitalicio = 0
                        order by id_operadora, id_finalizado;";
                
                // echo "$sql\n____";
                $select = mysqli_query($conect, $sql);

                if (mysqli_num_rows($select) > 0) {

                    $sqlTotalDiaOperadora = "SELECT 
                                                SUM(valor) AS valor_total_dia_previsto,
                                                (SELECT 
                                                        SUM(comissao)
                                                    FROM
                                                        busca_comissoes
                                                    WHERE
                                                        data_pagamento = '$dataPesquisa'
                                                            AND dental = 0 AND id_operadora = $idOperadora) AS valor_total_pago_dia
                                            FROM
                                                tbl_relatorio_recebimento
                                            WHERE
                                                data = '$dataPesquisa' AND id_operadora = $idOperadora AND comissao = 0;";
                    // echo "$sqlTotalDiaOperadora";
                    $selectTotalDiaOperadora = mysqli_query($conect, $sqlTotalDiaOperadora);

                    if ($rsTotalDiaOperadora = mysqli_fetch_assoc($selectTotalDiaOperadora)){
                        $valorTotalPrevistoDiaOperadora = $rsTotalDiaOperadora['valor_total_dia_previsto'];
                        $valorTotalPagoDiaOperadora = $rsTotalDiaOperadora['valor_total_pago_dia'];
                    }


                    $color = obter_cor($idOperadora);

                    $html .= '
                    <li class="">
                        <div class="collapsible-header"><i class="material-icons" '.styleColorText($color).'>description</i>'.$nomeOperadora.'
                            <div style="margin-left: auto;">
                                <span '.styleColorText("#8f8f8f").'>
                                    Valor Previsto: R$ '.formatMoeda($valorTotalPrevistoDiaOperadora).'
                                </span>
                                &nbsp
                                | 
                                &nbsp
                                <span '.styleColorText("green").'>
                                    Valor Pago: R$ '.formatMoeda($valorTotalPagoDiaOperadora).'
                                </span>
                            </div>
                        </div>
                        <div class="collapsible-body">
                            <table>
                                <thead>
                                    <tr>
                                        <th>IdFinalizado</th>
                                        <th>Data Inicial de Pagamento</th>
                                        <th>Data Prevista</th>
                                        <th>Parcela</th>
                                        <th>Valor Previsto</th>
                                        <th>Descrição</th>
                                        <th>Valor Pago data certa</th>
                                        <th>Outra data de pagamento</th>
                                        <th>Valor Pago fora data</th>
                                    </tr>
                                </thead>
                                <tbody>';

                    $select = mysqli_query($conect, $sql);
                    while ($rsPrevisto = mysqli_fetch_assoc($select)) {
                        $idFinalizado = $rsPrevisto['id_finalizado'];
                        $idOperadora = $rsPrevisto['id_operadora'];
                        $parcela = $rsPrevisto['parcela'];
                        $operadora = $rsPrevisto['nome_operadora'];
                        $valor = $rsPrevisto['valor'];
                        $data = $rsPrevisto['data_previsao'];
                        $titulo = $rsPrevisto['titulo'];
                        $dataPagamentoInicial = $rsPrevisto['data_pagamento_inicial'];

                        // echo "\nPrevisto $dataPesquisa: $titulo\n";

                        $html .= "<tr>
                                <td>$idFinalizado</td>
                                <td ".styleColorText("#8f8f8f").">$dataPagamentoInicial</td>
                                <td>$dataPesquisa</td>
                                <td>$parcela</td>
                                <td>".formatMoeda($valor)."</td>
                                <td>$titulo</td>";


                        $sqlTransacao = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  
                        FROM tbl_transacoes as t inner join tbl_contas as c on t.id_origem = c.id 
                        WHERE data_pagamento_operadora = '$dataPesquisa' 
                        and id_destino = 1 AND id_finalizado = $idFinalizado 
                        AND (IF(id_operadora = 1, parcela = $parcela OR parcela = $parcela - 1 , parcela = $parcela)) and dental = 0 
                        order by id_operadora, id_finalizado;";
                        // echo "$sqlTransacao ____";

                        $selectTransacao = mysqli_query($conect, $sqlTransacao);
                        if ($selectTransacao = mysqli_fetch_array($selectTransacao)) {
                            $descricao = $selectTransacao['descricao'];
                            $valorPago = $selectTransacao['valor'];

                            // echo "Pago $dataPesquisa: $descricao\n";

                            $html .= "
                                <td ".styleColorText("green").">".formatMoeda($valorPago)."</td>
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
                                $valorPago = $selectTransacao['valor'];
                                // echo "Pago $dataPagamentoOperadora: $descricao\n";

                                $html .= "
                                    <td></td>
                                    <td>$dataPagamentoOperadora</td>
                                    <td ".styleColorText("red").">".formatMoeda($valorPago)."</td>";
                            }
                        }
                        $html .= "</tr>";
                    }

                    $html .= "
                                </tbody>
                            </table>
                        </div>
                    </li>
                    ";
                }

                
            }

            $html .= "
                    </ul>
                </div>
            </li>";
        }




        $count++;
    }

    $html .= "</ul>";
}

echo $html;
