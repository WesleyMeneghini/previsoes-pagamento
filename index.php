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

        // Efeito para abrir e fechar as informaçoes do lançamento das comissoes
        const openCollapsible = () => {
            $(document).ready(function() {
                $('.collapsible').collapsible({
                    accordion: false
                });
            });
        }

        const relatorioPeriodo = (dataInicial, dataFinal) => {
            $.ajax({
                url: "relatorio.php",
                type: 'GET',
                data: {
                    "data_inicial": dataInicial,
                    "data_final": dataFinal
                },
                beforeSend: function() {
                    $("#resultado_dias").html("Carregando Total dos dias do mês...");
                }
            }).done(function(msg) {
                $("#resultado_dias").html(msg);

                openCollapsible();
            });
        }

        const totalOperadoras = (dataInicial, dataFinal) => {
            $.ajax({
                url: "total_operadoras.php",
                type: 'GET',
                data: {
                    "data_inicial": dataInicial,
                    "data_final": dataFinal
                },
                beforeSend: function() {
                    $("#resultado").html("Carregando Total das Operadoras do mês...");
                }
            }).done(function(msg) {
                let res = JSON.parse(msg);

                let totalOperadoras = res.map(e => (
                    `<tr>
                        <td>${e.operadora}</td>
                        <td> R$ ${e.total}</td>
                        <td> R$ ${e.previsto}</td>
                    </tr>`)
                );

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
                                ${totalOperadoras}
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
                <div class="input-field col s5 ">
                    <input type="date" id="data_inicial" name="data_inicial" value="<?= date("Y-m-01") ?>">

                    <label>Data inicial</label>
                </div>
                <div class="input-field col s5 ">
                    <input type="date" id="data_final" name="data_final" value="<?= date("Y-m-d") ?>">
                    <label>Data Final</label>
                </div>
                <div class="input-field col s2 ">
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

            if ($dataFinal < $dataInicial) {
                alert("Data inicial não pode ser maior que a final!");
            } else {
                relatorioPeriodo($dataInicial, $dataFinal);
                totalOperadoras($dataInicial, $dataFinal);
            }
        });
    </script>
</body>