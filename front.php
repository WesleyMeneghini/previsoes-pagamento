<?php

ini_set('error_reporting', E_ALL); // mesmo resultado de: error_reporting(E_ALL);
ini_set('display_errors', 1);



require_once "../includes/functions.php";
require_once "../includes/connection.php";


$conect = conexaoMysql();

$anoAtual = date('Y');
$mes = date('m') + 0;

$dataInicial = "$anoAtual-$mes-01";
$dataFimDeMesAtual = "$anoAtual-$mes-" . cal_days_in_month(CAL_GREGORIAN, $mes, $anoAtual);
$qqtDiasMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anoAtual);

$totalPrevistoMes = (float) 0.0;
$totalPagoMes = (float) 0.0;



?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">



    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Previsao de Paga</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.tabs').tabs();
        });

        $(document).ready(function() {
            $('.modal').modal();
        });


        // Efeito para abrir e fechar as informaçoes do lançamento das comissoes
        $(document).ready(function() {
            $('.collapsible').collapsible({
                accordion: false
            });
        });
    </script>
    <style>
        .carousel .carousel-item {
            width: 100%;
        }
    </style>
</head>


<body>


    <ul class="collapsible popout expandable">
        <?php

        // while ($qqtDiasMes > 20) {
        //     $dia = $qqtDiasMes;

        //     $totalPrevisto = (float) 0.0;
        //     $totalPago = (float) 0.0;

        //     $dataPesquisa = "$anoAtual-$mes-$dia";

        //     echo '
        //     <div class="collapsible-header"><i class="material-icons">date_range</i>' . $dataPesquisa . '</div>
        //         <div class="collapsible-body">
        //             <li class="active">
        //                 <div class="row">';

        //     $sql = "SELECT * FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0;";
        //     $select = mysqli_query($conect, $sql);

        //     echo '<div class="col s6">';
        //     while ($rsRelatorio = mysqli_fetch_assoc($select)) {
        //         $descricao = $rsRelatorio['titulo'];
        //         $valor = $rsRelatorio['valor'];

        //         $totalPrevisto += $valor;
        //         $totalPrevistoMes += $valor;

        //         echo '<p>' . $descricao . '</p>';
        //     }
        //     echo '</div>';

        //     $sql = "SELECT * FROM tbl_transacoes WHERE data_pagamento_operadora = '$dataPesquisa' and id_destino = 1;";
        //     $select = mysqli_query($conect, $sql);

        //     echo '<div class="col s6">';
        //     while ($rsTransacao = mysqli_fetch_assoc($select)) {
        //         $descricao = $rsTransacao['descricao'];
        //         $valor = $rsTransacao['valor'];

        //         $totalPago += $valor;
        //         $totalPagoMes += $valor;

        //         echo '<p>' . $descricao . '</p>';
        //     }

        //     echo '  
        //                 </div>
        //             </div>
        //         </li>
        //     </div>';

        //     $totalDia = $totalPrevisto - $totalPago;


        //     $qqtDiasMes--;
        // }
        ?>

        <?php

        while ($qqtDiasMes > 1) {
            $dia = $qqtDiasMes;

            $totalPrevisto = (float) 0.0;
            $totalPago = (float) 0.0;

            $dataPesquisa = "$anoAtual-$mes-$dia";

            // $sqlContas = "SELECT * from tbl_contas where id_operadora is not null and id_plataforma is null order by id_operadora;";
            // $selectContas = mysqli_query($conect, $sql);
            // while ($rsContas = mysqli_fetch_assoc($selectContas)) {
            //     $idConta = $rsContas['id'];
            //     $idContaOperadora = $rsContas['id_operadora'];
        ?>


            <li>
                <div class="collapsible-header"><i class="material-icons">date_range</i><?= $dataPesquisa ?></div>
                <div class="collapsible-body">
                    <div class="row">
                        <div class="col s6">
                            <h5>Previsto</h5>
                            <table>

                                <thead>
                                    <tr>
                                        <th>ID Finalizado</th>
                                        <th>Parcela</th>
                                        <th>Valor</th>
                                        <th>Titulo</th>
                                        <th>Operadora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM tbl_relatorio_recebimento WHERE data = '$dataPesquisa' and comissao = 0  order by id_operadora, id_finalizado  ;";
                                    // echo "$sql";
                                    $select = mysqli_query($conect, $sql);
                                    while ($rsTransacao = mysqli_fetch_assoc($select)) {
                                        $valor = $rsTransacao['valor'];
                                        $totalPrevisto += $valor;
                                        $totalPrevistoMes += $valor;
                                        $color = obter_cor($rsTransacao['id_operadora']);
                                        $styleColorText = "style='color:$color;'";
                                    ?>
                                        <tr>
                                            <td><?= $rsTransacao['id_finalizado'] ?></td>
                                            <td><?= $rsTransacao['parcela'] ?></td>
                                            <td><?= number_format($valor, 2, ",", ".") ?></td>
                                            <td><?= $rsTransacao['titulo'] ?></td>
                                            <td <?= $styleColorText ?>> <?= $rsTransacao['operadora'] ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="col s6">
                            <h5>Pago</h5>
                            <table>

                                <thead>
                                    <tr>
                                        <th>ID Finalizado</th>
                                        <th>Parcela</th>
                                        <th>Valor</th>
                                        <th>Titulo</th>
                                        <th>Operadora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT descricao, id_operadora, valor, id_finalizado, parcela, data_pagamento_operadora, c.titulo as operadora  FROM tbl_transacoes as t  inner join tbl_contas as c on t.id_origem = c.id where data_pagamento_operadora = '$dataPesquisa'  and id_destino = 1 and dental = 0 order by id_operadora, id_finalizado;";

                                    // $sql = "SELECT * FROM tbl_transacoes WHERE data_pagamento_operadora = '$dataPesquisa' and id_destino = 1 order by id_operadora;";
                                    $select = mysqli_query($conect, $sql);
                                    while ($rsTransacao = mysqli_fetch_assoc($select)) {
                                        $valor = $rsTransacao['valor'];
                                        $totalPago += $valor;
                                        $totalPagoMes += $valor;
                                        $color = obter_cor($rsTransacao['id_operadora']);
                                        $styleColorText = "style='color:$color;'";
                                    ?>
                                        <tr>
                                            <td><?= $rsTransacao['id_finalizado'] ?></td>
                                            <td><?= $rsTransacao['parcela'] ?></td>
                                            <td><?= number_format($valor, 2, ",", ".") ?></td>
                                            <td><?= $rsTransacao['descricao'] ?></td>
                                            <td <?= $styleColorText ?>> <?= $rsTransacao['operadora'] ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h4>Previsto R$ <?= number_format($totalPrevisto, 2, ",", ".") ?></h4>
                    <h4>Pago R$ <?= number_format($totalPago, 2, ",", ".") ?></h4>
                </div>
            </li>


        <?php
            // }
            $qqtDiasMes--;
        }
        ?>

        <!-- <li>
            <div class="collapsible-header"><i class="material-icons">date_range</i>Date</div>
            <div class="collapsible-body">
                <div class="row">
                    <div class="col s6">
                        <p class="">1</p>
                        <p class="">1</p>
                        <p class="">1</p>
                        <p class="">1</p>
                    </div>
                    <div class="col s6">
                        <p class="">1</p>
                        <p class="">1</p>
                        <p class="">1</p>
                    </div>
                </div>
            </div>
        </li> -->

    </ul>

    <h3>Previsto mes R$ <?= number_format($totalPrevistoMes, 2, ",", ".") ?></h3>
    <h3>Pago mes R$ <?= number_format($totalPagoMes, 2, ",", ".") ?></h3>





    <!-- Efeito do load -->
    <script>
        //código usando jQuery
        $(document).ready(function() {
            $('.progress').hide();
        });
        $('#btn_pesquisa').click(function() {
            if ($('#data_inicial').val() != "") {
                $('.progress').show();
            }
        });
    </script>
</body>

</html>