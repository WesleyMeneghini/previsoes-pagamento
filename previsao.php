<?php

require_once("includes/connection.php");

$conect = conexaoMysql();

$salvar = true;
$log = false;

$anoAnterior = date('Y') - 1;
$anoAtual = date('Y');
$proximoAno = date('Y') + 1;

$mes = date('m');

$dataInicial = "$anoAnterior-$mes-01";
$dataFimDeMesAtual = "$anoAtual-$mes-" . cal_days_in_month(CAL_GREGORIAN, $mes, $anoAtual);
$dataFim = "$anoAtual-$mes-" . cal_days_in_month(CAL_GREGORIAN, $mes, $proximoAno);


// Variavel setada para testes
// $dataInicial = $anoAtual . "-" . "01" . "-" . "1";
// $dataFim = $anoAtual . "-" . "01" . "-" . "30";


$sql = "SELECT tbl_finalizado.*, operadora.titulo as nome_operadora FROM tbl_finalizado inner join tbl_operadora as operadora on tbl_finalizado.id_operadora = operadora.id  where (data_pagamento is not null or data_previsao_pagamento is not null) and vitalicio = 0 and id_operadora > 0  order by id desc;";

// echo "$sql\n";

$result = mysqli_query($conect, $sql) or die(mysqli_error($conect));
$nrows = mysqli_num_rows($result);
$countNrows = 1;
while ($rs = mysqli_fetch_assoc($result)) {

    echo "\n$countNrows/$nrows\n";
    $countNrows++;

    $data_previsao_pagamento = $rs['data_previsao_pagamento'];
    $data_pagamento = $rs['data_pagamento'];
    $valor = $rs['valor'];
    if (!$data_pagamento > 0) {
        $data_pagamento = $data_previsao_pagamento;
    }

    $data_venda = $rs['data_lancamento'];
    $nome_operadora = $rs['nome_operadora'];

    if ($data_venda == null) {
        $data_venda = 'null';
    } else {
        $data_venda = "'$data_venda'";
    }

    $descricao = $rs['razao_social'];
    $valor_contrato = $rs['valor'];


    /******DADOS SOBRE A PROPOSTA*****/

    //Infos úteis do contrato
    $id_finalizado = $rs['id'];
    $id_operadora = $rs['id_operadora'];
    $id_tipo_venda = $rs['id_tipo_venda'];
    $id_sindicato = $rs['id_sindicato'];
    $id_tipo_adesao = $rs['id_tipo_adesao'];
    $portabilidade = $rs['portabilidade'];
    $empresarial = 1;
    $acompanhado = 0;
    $id_treinador = 0;

    //Usuários base
    $usuario[] = $rs['id_corretor'];
    $usuario[] = $rs['id_produtor'];
    $usuario[] = $rs['id_companhia'];
    $usuario[] = $rs['id_account'];
    $usuario[] = $rs['id_implantador'];
    $usuario[] = $rs['id_call_center'];

    $id_call_center = $rs['id_call_center'];

    //Supervisores        
    $usuario[] = $rs['id_supervisor'];

    if ($rs['id_companhia'] > 0) {
        $acompanhado = 1;
    }

    if ($id_call_center > 0) {
        $usuario[] = 101;
    }

    //Área do treinador

    $sql = "select * from tbl_treinador_usuario where id_usuario = '" . $rs['id_corretor'] . "' and ($data_venda between dt_venda_inicio and if(dt_venda_fim is not null, dt_venda_fim, $data_venda));";
    $result_treinador = mysqli_query($conect, $sql);

    if ($rs_treinador = mysqli_fetch_array($result_treinador)) {
        $usuario[] = $rs_treinador['id_treinador'];
        $id_treinador = $rs_treinador['id_treinador'];
    } else {
        $usuario[] = 0;
    }

    if ($id_sindicato > 0) {
        $empresarial = 0;
        if ($portabilidade == 1) {
            $portabilidade = 0;
        } else {
            $portabilidade = 1;
        }
    }

    $usuario[] = $rs['id_supervisor_corretor'];

    //Área técnica
    $usuario[] = 26;
    $usuario[] = 60;
    $usuario[] = 142;
    $usuario[] = 143;
    $usuario[] = 144;

    $id_tipo_comissao_corretor = 0;

    $sql = "select u.id_tipo_comissao from tbl_usuario as u where id_usuario = '" . $rs['id_corretor'] . "'";
    $result3 = mysqli_query($conect, $sql) or die(mysqli_error($conect));
    if ($rs3 = mysqli_fetch_array($result3)) {
        $id_tipo_comissao_corretor = $rs3['id_tipo_comissao'];
    }

    if ($id_tipo_comissao_corretor == 3 && $data_venda >= '2020-10-01') {
        $id_tipo_comissao_corretor = 1;
    }

    echo "RAZÃO SOCIAL: $descricao ** ID: $id_finalizado \nOPERADORA: ($nome_operadora) ** Data Pagamento: $data_pagamento\n";

    $parcela = 0;
    $sqlUltimaParcela = "SELECT max(parcela) as ultima_parcela from tbl_transacoes where id_finalizado = '$id_finalizado' ;";

    $selectUltimaParcela = mysqli_query($conect, $sqlUltimaParcela) or die(mysqli_error($conect));

    if ($rs2 = mysqli_fetch_array($selectUltimaParcela)) {

        $parcela = $rs2['ultima_parcela'];

        if ($parcela >= 1) {

            if ($log)
                echo "jÁ TEM PELO MENOS UM PAGAMENTO NO SISTEMA";

            $sql2 = "SELECT max(parcela) as ultima_parcela, max(data_pagamento_operadora) as data_pagamento_operadora from tbl_transacoes where id_finalizado = '$id_finalizado' and  ( data between '$dataInicial' and '$dataFim') ;";
            // echo $sql2;
            // echo "Pegando valores maximos parcelas\n";
            $result2 = mysqli_query($conect, $sql2) or die(mysqli_error($conect));

            if ($rs2 = mysqli_fetch_array($result2)) {
                $parcela = $rs2['ultima_parcela'];
                if ($parcela >= 1) {

                    // $data_pagamento_operadora = $rs2['data_pagamento_operadora'];
                    $data_pagamento_operadora = $data_pagamento;

                    $mesContador = 1;

                    $valor_contrato = $rs['valor'];

                    while ($mesContador <= 12) {

                        $parcela_prevista = $parcela + $mesContador;

                        $data = date("Y-m-d", strtotime($data_pagamento_operadora . " + $mesContador month"));
                        $data_pagamento_prevista = calculoData($id_operadora, $data);


                        // echo "$data_pagamento_prevista, " .  date("w", strtotime($data_pagamento_prevista)) . "\n";

                        $sqlPorcentagem = "select sum(porcentagem) as porcentagem, parcela from tbl_porcentagem_comissoes where id_operadora = $id_operadora and if($parcela_prevista > 6, parcela = 6, parcela = $parcela_prevista);";

                        $selectporcentagem = mysqli_query($conect, $sqlPorcentagem);
                        if ($rsPorcentagem = mysqli_fetch_assoc($selectporcentagem)) {
                            $porcentagem = $rsPorcentagem['porcentagem'];
                        }
                        $rs['valor'] = $valor_contrato * ($porcentagem / 100);

                        $transacao = "INSERT INTO tbl_relatorio_recebimento 
                                (id_finalizado, id_operadora, parcela, operadora, tipo, titulo, valor, pago, empresarial, comissao, data, data_venda
                                ) values ($id_finalizado, $id_operadora, $parcela_prevista, '$nome_operadora', '', '$descricao', " . $rs['valor'] . ", 1, '1', '0', '$data_pagamento_prevista', $data_venda)";
                        // echo "$transacao\n";

                        $sqlTransacoes = "SELECT count(id) as qtt FROM tbl_relatorio_recebimento WHERE id_finalizado = $id_finalizado and parcela = $parcela_prevista and id_operadora = $id_operadora;";

                        $selectTransacoes = mysqli_query($conect, $sqlTransacoes);

                        if ($selectTransacoes === false) {
                            die(mysqli_error($conect));
                        }

                        // Verificar se o registro ja existe no sistema
                        while ($rsTransacao = mysqli_fetch_assoc($selectTransacoes)) {

                            if ($rsTransacao['qtt'] == 0) {

                                if ($salvar)
                                    $res = mysqli_query($conect, $transacao) or die(mysqli_error($conect));

                                if ($parcela_prevista <= 3) {

                                    // echo "Comececando distribuicao\n";
                                    distribuiComissao($rs, $parcela_prevista, $data_pagamento_prevista, "", 1);
                                    // echo "Termino distribuicao\n";
                                }
                            }
                        }

                        $rs['valor'] = $valor_contrato;

                        $mesContador++;
                    }
                }
            }
        } else {

            if ($log)
                echo "AINDA NÃO TEM PAGAMENTOS (Previsão a partir da 1ª parcela)\n";

            $mesContador = 1;
            $valor_contrato = $rs['valor'];

            while ($mesContador <= 12) {
                $parcela_prevista = $mesContador;

                $data = date("Y-m-d", strtotime($data_pagamento . " + " . ($mesContador - 1) . " month"));
                $data_pagamento_prevista = calculoData($id_operadora, $data);


                $sqlPorcentagem = "select sum(porcentagem) as porcentagem, parcela from tbl_porcentagem_comissoes where id_operadora = $id_operadora and if($parcela_prevista > 6, parcela = 6, parcela = $parcela_prevista);";

                $selectporcentagem = mysqli_query($conect, $sqlPorcentagem);
                if ($rsPorcentagem = mysqli_fetch_assoc($selectporcentagem)) {
                    $porcentagem = $rsPorcentagem['porcentagem'];
                }
                $rs['valor'] = $valor_contrato * ($porcentagem / 100);

                $transacao = "INSERT INTO tbl_relatorio_recebimento (id_finalizado, id_operadora, parcela, operadora, tipo, titulo, valor, pago, empresarial, comissao, data, data_venda) values ($id_finalizado, $id_operadora, $parcela_prevista, '$nome_operadora', '', '$descricao', " . $rs['valor'] . ", 0, '1', '0', '$data_pagamento_prevista', $data_venda)";
                // echo "$transacao\n";

                $sqlTransacoes = "SELECT count(id) as qtt FROM tbl_relatorio_recebimento WHERE id_finalizado = $id_finalizado and parcela = $parcela_prevista and id_operadora = $id_operadora;";

                $selectTransacoes = mysqli_query($conect, $sqlTransacoes);
                if ($selectTransacoes === false) {
                    die(mysqli_error($conect));
                }

                // Verificar se o registro ja existe no sistema
                if ($rsTransacao = mysqli_fetch_assoc($selectTransacoes)) {

                    if ($rsTransacao['qtt'] == 0) {

                        if ($salvar)
                            mysqli_query($conect, $transacao);

                        if ($parcela_prevista <= 3) {

                            distribuiComissao($rs, $parcela_prevista, $data_pagamento_prevista, "", 0);
                        }
                    }
                }


                $rs['valor'] = $valor_contrato;

                $mesContador++;
            }
        }
    }
}


function calculoData($id_operadora, $data_pagamento)
{
    global $log;
    $data_pagamento_prevista = $data_pagamento;

    $diaDaSemana = date("w", strtotime($data_pagamento_prevista));
    $dia = date("d", strtotime($data_pagamento_prevista));
    $mes = date("m", strtotime($data_pagamento_prevista));
    $ano = date("Y", strtotime($data_pagamento_prevista));

    if ($log)
        echo "\nData inicial:  $data_pagamento_prevista ** Dia da semana ($diaDaSemana)\n";

    if ($id_operadora == 1) {

        if ($dia < 15) {
            if ($mes == 2) {
                $data_pagamento_prevista = $ano . "-" . $mes . "-" .  cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            } else {
                $data_pagamento_prevista = $ano . "-" . $mes . "-30";
            }
        } else {
            $data_pagamento_prevista = date("Y-m-15", strtotime($data_pagamento_prevista . " +1 month"));
        }
        if (date("w", strtotime($data_pagamento_prevista)) == 0) {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +1 days"));
        } elseif (date("w", strtotime($data_pagamento_prevista)) == 6) {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +2 days"));
        }
    } elseif ($id_operadora == 2 || $id_operadora == 5 || $id_operadora == 8) {

        $baseDia = 7;
        if ($diaDaSemana == 0) {
            $baseDia = 5;
        } elseif ($diaDaSemana == 6) {
            $baseDia = 6;
        } else {
            $baseDia = $baseDia + (5 - $diaDaSemana);
        }
        $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +$baseDia days"));
    } else {
        $count = 1;
        while ($count <= 3) {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +1 days"));
            if (date("w", strtotime($data_pagamento_prevista)) != 0 && date("w", strtotime($data_pagamento_prevista)) != 6) {
                $count++;
            }
        }
    }

    $diaDaSemanaPrevista = date("w", strtotime($data_pagamento_prevista));

    // echo "--$data_pagamento_prevista   --$diaDaSemanaPrevista\n";

    // Verificar o dia da semana para o pagamento cair na sexta-feira(AMIL) ou no proximo dia util (OUTRAS OPERADORAS)
    if ($id_operadora == 2 || $id_operadora == 5 || $id_operadora == 8) {
        if ($diaDaSemanaPrevista == 0) {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " -2 days"));
        } elseif ($diaDaSemanaPrevista == 6) {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " -1 days"));
        } else {
            $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +" . (5 - $diaDaSemanaPrevista) . " days"));
        }
    } elseif ($diaDaSemanaPrevista == 0 || $diaDaSemanaPrevista == 6) {
        $data_pagamento_prevista = date("Y-m-d", strtotime($data_pagamento_prevista . " +2 days"));
    }

    $diaDaSemana = date("w", strtotime($data_pagamento_prevista));
    if ($log)
        echo "Data prevista: $data_pagamento_prevista ** Dia da semana ($diaDaSemana)\n";

    return $data_pagamento_prevista;
}





function distribuiComissao($rs, $parcela_prevista, $data_pagamento_prevista, $tipo_transacao, $pago)
{
    global $conect;
    global $salvar;

    $valor_calc = $rs['valor'];
    $descricao = $rs['razao_social'];
    $txt_parcela = $parcela_prevista;
    $txt_id_finalizado = $rs['id'];
    $nome_operadora = $rs['nome_operadora'];

    /******DADOS SOBRE A PROPOSTA*****/

    //Infos úteis do contrato
    $id_operadora = $rs['id_operadora'];

    $id_tipo_venda = $rs['id_tipo_venda'];
    $id_sindicato = $rs['id_sindicato'];
    $id_tipo_adesao = $rs['id_tipo_adesao'];
    $portabilidade = $rs['portabilidade'];
    $data_venda = $rs['data_lancamento'];
    if ($data_venda == null) {
        $data_venda = 'null';
    } else {
        $data_venda = "'$data_venda'";
    }

    $empresarial = 1;
    $acompanhado = 0;
    $id_treinador = 0;

    //Usuários base
    $usuario[] = $rs['id_corretor'];
    $usuario[] = $rs['id_produtor'];
    $usuario[] = $rs['id_companhia'];
    $usuario[] = $rs['id_account'];
    $usuario[] = $rs['id_implantador'];
    $usuario[] = $rs['id_call_center'];

    $id_call_center = $rs['id_call_center'];

    //Supervisores        
    $usuario[] = $rs['id_supervisor'];

    if ($rs['id_companhia'] > 0) {
        $acompanhado = 1;
    }

    if ($id_call_center > 0) {
        $usuario[] = 101;
    }

    //Área do treinador

    $sql = "select * from tbl_treinador_usuario where id_usuario = '" . $rs['id_corretor'] . "' and ($data_venda between dt_venda_inicio and if(dt_venda_fim is not null, dt_venda_fim, $data_venda));";
    //echo $sql;
    $result_treinador = mysqli_query($conect, $sql);

    if ($rs_treinador = mysqli_fetch_array($result_treinador)) {
        $usuario[] = $rs_treinador['id_treinador'];
        $id_treinador = $rs_treinador['id_treinador'];
        //echo $id_treinador;
    } else {
        $usuario[] = 0;
    }

    if ($id_sindicato > 0) {
        $empresarial = 0;
        if ($portabilidade == 1) {
            $portabilidade = 0;
        } else {
            $portabilidade = 1;
        }
    }

    $usuario[] = $rs['id_supervisor_corretor'];

    //Área técnica
    $usuario[] = 26;
    $usuario[] = 60;
    $usuario[] = 142;
    $usuario[] = 143;
    $usuario[] = 144;

    $id_tipo_comissao_corretor = 0;

    $sql = "select u.id_tipo_comissao from tbl_usuario as u where id_usuario = '" . $rs['id_corretor'] . "'";
    $result = mysqli_query($conect, $sql) or die(mysqli_error($conect));
    if ($rs = mysqli_fetch_array($result)) {
        $id_tipo_comissao_corretor = $rs['id_tipo_comissao'];
    }

    if ($id_tipo_comissao_corretor == 3 && $data_venda >= '2020-10-01') {
        $id_tipo_comissao_corretor = 1;
    }

    $contador = 0;
    while ($contador < count($usuario)) {
        $valor_venda = 0;

        $sql = "select cmc.* from tbl_usuario as u inner join tbl_config_meta_comissionamento as cmc on cmc.id_tipo_comissao = if(u.id_tipo_comissao = 3, if($data_venda >= '2020-10-01', 1, 3), u.id_tipo_comissao) where id_usuario = '" . $usuario[$contador] . "'";
        // echo $sql;
        $result = mysqli_query($conect, $sql) or die(mysqli_error($conect));
        while ($rs = mysqli_fetch_array($result)) {

            $sql2 = "select sum(valor) as valor_venda from tbl_finalizado where 
                data_lancamento like '" . date("Y-m-", strtotime($data_venda)) . "%' and  id_corretor = '" . $usuario[$contador] . "' and portabilidade = '" . $rs['empresarial'] . "' and id_tipo_venda = '" . $rs['id_tipo_venda'] . "'
                or data_lancamento like '" . date("Y-m-", strtotime($data_venda)) . "%' and id_call_center = '" . $usuario[$contador] . "' and portabilidade = '" . $rs['empresarial'] . "' and id_tipo_venda = '" . $rs['id_tipo_venda'] . "'
                or data_lancamento like '" . date("Y-m-", strtotime($data_venda)) . "%' and id_supervisor_corretor = '" . $usuario[$contador] . "' and id_status not in (17) and portabilidade = '" . $rs['empresarial'] . "' and id_tipo_venda = '" . $rs['id_tipo_venda'] . "';";

            if ($contador == 0) {
                $sql2 = "select sum(valor) as valor_venda from tbl_finalizado where 
                    data_lancamento like '" . date("Y-m-", strtotime($data_venda)) . "%' and id_corretor = '" . $usuario[$contador] . "' and portabilidade = '" . $rs['empresarial'] . "' and id_tipo_venda = '" . $rs['id_tipo_venda'] . "' and id_status not in (17);";
            }

            $result2 = mysqli_query($conect, $sql2) or die(mysqli_error($conect));

            if ($rs2 = mysqli_fetch_array($result2)) {
                $valor_venda += $rs2['valor_venda'];
            }
        }

        $corretor = 0;
        $produtor = 0;
        $call_center = 0;
        $closer = 0;
        $account = 0;
        $supervisor_adm = 0;
        $treinador = 0;

        if ($contador == 0) {
            $corretor = 1;
        }

        if ($contador == 1) {
            $produtor = 1;
        }

        if ($contador == 2) {
            $closer = 1;
        }

        if ($contador == 3) {
            $account = 1;
        }

        if ($contador == 5) {
            $call_center = 1;
        }

        if ($contador == 6) {
            $supervisor_adm = 1;
        }

        if ($contador == 7) {
            if ($id_call_center > 0) {
                $call_center = 1;
            }
        }

        if ($id_treinador > 0) {
            if ($id_call_center > 0) {
                if ($contador == 8) {
                    $treinador = 1;
                }
            } else {
                if ($contador == 7) {
                    $treinador = 1;
                }
            }
        }

        $sql = "select u.id_tipo_comissao, u.id_tipo_empresa, tcv.* from tbl_usuario as u inner join tbl_tipo_comissao_valor as tcv on tcv.id_tipo_comissao = if(u.id_tipo_comissao = 3, if($data_venda >= '2020-10-01', 1, 3), u.id_tipo_comissao) where u.id_usuario = '" . $usuario[$contador] . "' and tcv.parcela = '$txt_parcela' and tcv.id_tipo_venda = '$id_tipo_venda' and tcv.empresarial = '$empresarial' and tcv.portabilidade = '$portabilidade' and tcv.id_tipo_adesao = '$id_tipo_adesao' and tcv.corretor = '$corretor' and tcv.produtor = '$produtor' and tcv.closer = '$closer' and tcv.acompanhado = '$acompanhado' and tcv.treinador = '$treinador' and tcv.supervisor_adm = '$supervisor_adm' and tcv.account = '$account' and ('$valor_venda' between tcv.meta_min and if(tcv.meta_max > 0, tcv.meta_max, '$valor_venda')) and if(tcv.id_tipo_comissao_corretor = 0, '$id_tipo_comissao_corretor', tcv.id_tipo_comissao_corretor) = '$id_tipo_comissao_corretor'";
        //echo $sql."<br>";
        $result = mysqli_query($conect, $sql) or die(mysqli_error($conect));

        if ($rs = mysqli_fetch_array($result)) {
            $porcentagem = $rs['porcentagem'];

            if ($id_operadora == 12 && $contador == 0 && $txt_parcela == 1) {
                $sql = "select sum(valor) as valor_venda from tbl_finalizado where 
                    data_lancamento like '" . date("Y-m-", strtotime($data_venda)) . "%' and id_corretor = '" . $usuario[$contador] . "' and portabilidade = '" . $portabilidade . "' and id_tipo_venda = '$id_tipo_venda' and id_status not in (17) and id_operadora = '$id_operadora';";

                $result_alt = mysqli_query($conect, $sql) or die(mysqli_error($conect));

                if ($rs_alt = mysqli_fetch_array($result_alt)) {
                    if ($rs_alt['valor_venda'] > 10000 && $rs_alt['valor_venda'] <= 20000) {
                        $porcentagem += 10;
                    } else if ($rs_alt['valor_venda'] > 20000) {
                        $porcentagem += 20;
                    }
                }
            }

            $porcentagem = $porcentagem / 100;
            $valor_calc_base = $valor_calc * $porcentagem;
            $descricao_comissao = $rs['descricao'];
            $tipo_empresa = $rs['id_tipo_empresa'];

            $sql = "SELECT * FROM tbl_usuario_conta_comissao where id_usuario = '" . $usuario[$contador] . "'";
            $result = mysqli_query($conect, $sql) or die(mysqli_error($conect));

            while ($rs = mysqli_fetch_array($result)) {
                $id_conta_insert = $rs['id_conta'];
                $valor_calc_liquid = $valor_calc_base * $rs['porcentagem'] / 100;
                $irrf = 0;

                if ($tipo_empresa < 1) {
                    if (!$call_center) {
                        $valor_calc_liquid = $valor_calc_liquid * 0.915;
                    }
                } else {
                    $irrf = $valor_calc_liquid * 0.015;
                }

                //echo "<br>".$valor_calc_base." - ".$valor_calc_liquid." - ".$rs['porcentagem']." - ".$rs['imposto']." / ";
                $transacao = "INSERT INTO tbl_relatorio_recebimento 
                (id_finalizado, id_operadora, parcela, operadora, tipo, titulo, valor, pago, empresarial, comissao, data, data_venda
                ) values ($txt_id_finalizado, $id_operadora, $txt_parcela, '$nome_operadora', '$tipo_transacao', '$descricao $descricao_comissao', $valor_calc_liquid, $pago, '1', '1', '$data_pagamento_prevista', $data_venda)";
                // echo "$transacao\n";

                if ($salvar)
                    mysqli_query($conect, $transacao) or die(mysqli_error($conect));
            }
        }

        $contador++;
    }
}
