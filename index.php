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
        $(document).ready(function () {
            $('.tabs').tabs();
        });

        $(document).ready(function () {
            $('.modal').modal();
        });


        // Efeito para abrir e fechar as informaçoes do lançamento das comissoes
        $(document).ready(function () {
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
    <!-- <ul class="collapsible">
        <li class="">
            <div class="collapsible-header"><i class="material-icons">date_range</i>Data</div>
            <div class="collapsible-body">
                <ul class="collapsible">
                    <li class="">
                        <div class="collapsible-header"><i class="material-icons">date_range</i>Teste 01</div>
                        <div class="collapsible-body">
                            
                        </div>
                    </li>
                    <li class="">
                        <div class="collapsible-header"><i class="material-icons">date_range</i>Teste 02</div>
                        <div class="collapsible-body">
                            
                        </div>
                    </li>
                </ul>
            </div>
        </li>
    </ul> -->
    <?php
        require_once "./conferencia.php";
    ?>
</body>