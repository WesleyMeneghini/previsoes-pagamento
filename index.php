<!DOCTYPE php>
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

        // const adicionarOption = () => {

        // }

        $(document).ready(function(){
            $.ajax({
                url: "select_operadoras.php",
                type: 'GET',
                data: {
                    "id": 0,
                },
                
            }).done( function(res) {
                var operadoras =  JSON.parse(res);
                // console.log(operadoras);
                operadoras.forEach(operadora => {
                    $('#slc_operadora').append(`<option value='${operadora.id}'>${operadora.titulo}</option>`);
                })
                    
            });
        })

        // Efeito para abrir e fechar as informaçoes do lançamento das comissoes
        const openCollapsible = () => {
            $(document).ready(function() {
                $('.collapsible').collapsible({
                    accordion: false
                });
            });
        }

        const loaderCircle = () => (
            `<div class="preloader-wrapper big active">
                <div class="spinner-layer spinner-blue-only">
                <div class="circle-clipper left">
                    <div class="circle"></div>
                </div><div class="gap-patch">
                    <div class="circle"></div>
                </div><div class="circle-clipper right">
                    <div class="circle"></div>
                </div>
                </div>
            </div>`
        )

        const formatMoedaReal = (number) => {
            return (parseFloat(number)).toLocaleString('pt-br', {
                style: 'currency',
                currency: 'BRL'
            });
        }

        const relatorioPeriodo = (dataInicial, dataFinal, tipo, idOperadora) => {
            $.ajax({
                url: "relatorio.php",
                type: 'GET',
                data: {
                    "data_inicial": dataInicial,
                    "data_final": dataFinal,
                    "id_operadora": idOperadora,
                    "tipo": tipo,
                },
                beforeSend: function() {
                    $("#resultado_dias").html(loaderCircle());
                }
            }).done(function(msg) {
                $("#resultado_dias").html(msg);

                openCollapsible();
            });
        }

        const totalOperadoras = (dataInicial, dataFinal, idOperadora) => {
            $.ajax({
                url: "total_operadoras.php",
                type: 'GET',
                data: {
                    "data_inicial": dataInicial,
                    "data_final": dataFinal,
                    "id_operadora": idOperadora
                },
                beforeSend: function() {
                    $("#resultado_total_operadoras").html(loaderCircle());
                    relatorioPeriodo(dataInicial, dataFinal, "previsto", idOperadora);
                }
            }).done(function(msg) {
                let res = JSON.parse(msg);
                console.log(msg);

                var totalPago = 0;
                var totalPrevisto = 0;

                let totalOperadorasTrs = res.map(e => (
                    `<tr>
                        <td>${e.operadora}</td>
                        <td> ${formatMoedaReal(e.total)}</td>
                        <td> ${formatMoedaReal(e.previsto)}</td>
                    </tr>`));


                res.map(e => {
                    totalPago += parseFloat(e.total);
                    totalPrevisto += parseFloat(e.previsto)
                });


                let tableResultTotalOperadoras =
                    `<table>
                            <thead>
                                <tr>
                                    <th>Operadora</th>
                                    <th>Total Pago</th>
                                    <th>Total Previsto</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${totalOperadorasTrs}
                                <tr>
                                    <td>Total</td>
                                    <td> ${formatMoedaReal(totalPago)}</td>
                                    <td> ${formatMoedaReal(totalPrevisto)}</td>
                                </tr>
                            </tbody>
                        </table>`;

                $("#resultado_total_operadoras").html(tableResultTotalOperadoras);

            });
        }
    </script>


    <style>
        .carousel .carousel-item {
            width: 100%;
        }
    </style>
</head>


<body>
    <div class="container">

        <div class="row">
            <form>
                <div class="input-field col s3 ">
                    <input type="date" id="data_inicial" name="data_inicial" value="<?= date("Y-07-01") ?>">

                    <label>Data inicial</label>
                </div>
                <div class="input-field col s3 ">
                    <input type="date" id="data_final" name="data_final" value="<?= date("Y-07-31") ?>">
                    <label>Data Final</label>
                </div>
                <div class="input-field col s2">
                    <select class="browser-default" id="slc_operadora">
                        <option value="0" selected>Selecione uma Operadora</option>
                    </select>
                    
                </div>
                <div class="input-field col s3 ">
                    <!-- Switch -->
                    <div class="switch">
                        <label>
                            Previsto/Pago
                            <input type="checkbox" id="pago_previsto">
                            <span class="lever"></span>
                            Pago/Previsto
                        </label>
                    </div>
                </div>
                <div class="input-field col s1 ">
                    <button class="btn waves-effect waves-light" id="btn_pesquisa" type="submit" value="PESQUISAR">ENVIAR</button>
                </div>


            </form>
        </div>

        <div id="resultado_total_operadoras"></div>
        <div id="resultado_dias"></div>
    </div>

    <script>
        $("form").submit(function(e) {
            e.preventDefault();
            const $dataInicial = $("#data_inicial").val();
            const $dataFinal = $("#data_final").val();
            const $idOperadora = $("#slc_operadora").val();
            const $pagoPrevisto = $("#pago_previsto");
            let tipo = "";

            console.log($idOperadora);

            if ($dataFinal < $dataInicial) {
                alert("Data inicial não pode ser maior que a final!");
            } else {
                if ($pagoPrevisto.is(":checked")) {
                    tipo = "pago";
                } else {
                    tipo = "previsto";
                }

                // relatorioPeriodo($dataInicial, $dataFinal, tipo);

                totalOperadoras($dataInicial, $dataFinal, $idOperadora);
            }
        });
    </script>
</body>