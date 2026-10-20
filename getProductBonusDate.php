<?php


function getProductBonusDate($codfilial){


    if ($codfilial == 1) {
        $title = 'KAPITÃO AMÉRICA';
        $title_pal = 'title_kap';
        $fundo = 'bg-kapitao';
        $tableName = 'table-kap';
    } else if ($codfilial == 2) {
        $title = 'PENTAGONO EPI';
        $title_pal = 'title_pen';
        $fundo = 'bg-pentagono';
        $tableName = 'table-pen';
    } else if ($codfilial == 3) {
        $title = 'FORTE IMPERADOR';
        $title_pal = 'title_for';
        $fundo = 'bg-forte';
        $tableName = 'table-for';
    }



    require __DIR__ . "/config/conexao.php";

    $query_kap = "

            WITH bonus_filtrado AS (
                SELECT /*+ MATERIALIZE */ bonus_va.*
                FROM pcbonusc bonus_va
                WHERE bonus_va.databonus >= TRUNC(SYSDATE) - 180
                    AND bonus_va.dtcancel IS NULL
                    AND bonus_va.codfilial = :codfilial
            ),
            venda_antecipada AS (
                SELECT
                    bonus_va.numbonus,
                    mov_va.codprod,
                    COUNT(mov_va.codprod) AS contador,
                    SUM(mov_va.qt) AS qt
                FROM pclanc lanc_va
                INNER JOIN bonus_filtrado bonus_va
                    ON TO_NUMBER(REGEXP_SUBSTR(lanc_va.historico, '[0-9]+')) = bonus_va.numbonus
                INNER JOIN pcmov mov_va
                    ON mov_va.numtransent = lanc_va.numtransent
                WHERE lanc_va.historico LIKE 'VA %'
                AND lanc_va.dtlanc >= TRUNC(SYSDATE) - 60
                GROUP BY bonus_va.numbonus, mov_va.codprod
            )

            SELECT
            SUB.STATUS,
            SUB.NUMBONUS,
            SUB.codfilial,
            TO_CHAR(SUB.databonus, 'DD/MM/YY') AS databonus,
            SUB.hora,
            SUB.minuto,
            SUB.dtmontagem,
            TO_CHAR(SUB.dtfechamento, 'DD/MM/YY') AS dtfechamento,
            SUB.codprod,
            SUB.descricao,
            SUB.qtnf,
            SUB.QTENTRADA,
            SUB.QTDISP,
            TO_CHAR(SUB.dtinicioemb, 'DD/MM/YY HH24:MI:SS') AS dtinicioemb,
            TO_CHAR(SUB.dtinicioconf, 'DD/MM/YY HH24:MI:SS') AS dtinicioconf,
            
            LISTAGG(SUBSTR(emp.nome, 1, INSTR(emp.nome, ' ') - 1), ', ') WITHIN GROUP (ORDER BY emp.matricula) AS conferentes,

            
            CASE
                WHEN STATUS = 1 THEN
                
                LPAD(TO_CHAR(FLOOR((SYSDATE - SUB.dtinicioemb) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SYSDATE - SUB.dtinicioemb) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SYSDATE - SUB.dtinicioemb) * 86400, 60))), 2, '0') 
                
                WHEN STATUS = 2 AND  SUB.dtinicioemb IS NOT NULL THEN
                LPAD(TO_CHAR(FLOOR((SUB.dtinicioconf - SUB.dtinicioemb) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtinicioconf - SUB.dtinicioemb) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtinicioconf - SUB.dtinicioemb) * 86400, 60))), 2, '0')
                
                WHEN STATUS = 3  AND SUB.dtinicioemb > SUB.dtinicioconf  THEN
                TO_CHAR('EMB > CONF')

                WHEN STATUS = 3   AND SUB.dtinicioemb is not null  AND SUB.dtinicioconf is  null   THEN
                TO_CHAR('')
                
                
                WHEN STATUS = 3  AND SUB.dtinicioemb is not null THEN
                LPAD(TO_CHAR(FLOOR((SUB.dtinicioconf - SUB.dtinicioemb) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtinicioconf - SUB.dtinicioemb) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtinicioconf - SUB.dtinicioemb) * 86400, 60))), 2, '0')
        

                ELSE NULL
                
            END AS TEMPO_EMB,
            
            
            
            SUB.dtfinalconf,
            SUB.conferenteBonus,
            
            CASE
                WHEN STATUS = 2 THEN
                
                LPAD(TO_CHAR(FLOOR((SYSDATE - SUB.dtinicioconf) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SYSDATE - SUB.dtinicioconf) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SYSDATE - SUB.dtinicioconf) * 86400, 60))), 2, '0') 
                
                
                WHEN STATUS = 3 AND SUB.DTINICIOCONF IS NULL THEN
                TO_CHAR('AUTOMATICO')
                
                
                WHEN STATUS = 3 THEN
                LPAD(TO_CHAR(FLOOR((SUB.dtfinalconf - SUB.dtinicioconf) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtfinalconf - SUB.dtinicioconf) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtfinalconf - SUB.dtinicioconf) * 86400, 60))), 2, '0')
                
                
                ELSE NULL
                
            END AS TEMPO_CONF,
           
            SUB.dtarmazenagem,
            SUB.dtarmazenagemini,
            SUB.dtarmazenagemfin,
            SUB.separador1,
            SUB.separador2,
            SUB.separador3,
            SUB.separador4,
            SUB.VA_CONTADOR,
            SUB.VA_QT
            
            
                
        
        FROM (
        
                SELECT
                    bonus.numbonus,
                    bonus.codfilial,
                    bonus.databonus,
                    bonus.hora,
                    bonus.minuto,
                    bonus.dtmontagem,
                    bonus.dtfechamento,
                    item.codprod,
                    prod.descricao,
                    item.qtnf,
                    item.QTENTRADA,
                    PKG_ESTOQUE.ESTOQUE_DISPONIVEL(EST.CODPROD, bonus.codfilial, 'V') AS QTDISP,
                    
                    
                    iw.dtinicioemb AS dtinicioemb,
                    --TO_DATE(iw.dtinicioemb, 'DD-MM-YYYY HH24:MI:SS') AS dtinicioemb,
                    iw.codconferentes as codconfemb,
                      
                    iw.dtinicioconf AS dtinicioconf,
                    --TO_DATE(iw.dtinicioconf, 'DD-MM-YYYY HH24:MI:SS') AS dtinicioconf,
                    iw.dtfinalconf AS dtfinalconf,
                    SUBSTR(MAX(emp.nome), 1, INSTR(MAX(emp.nome), ' ') - 1) AS conferenteBonus,
                   
                    TO_CHAR(arm.dtarmazenagem, 'DD/MM/YY HH24:MI:SS') AS dtarmazenagem,
                    TO_CHAR(arm.dtarmazenagemini, 'DD/MM/YY HH24:MI:SS') AS dtarmazenagemini,
                    TO_CHAR(arm.dtarmazenagemfin, 'DD/MM/YY HH24:MI:SS') AS dtarmazenagemfin,
                    SUBSTR(sep1.nome, 1, INSTR(sep1.nome, ' ') - 1) AS separador1,
                    SUBSTR(sep2.nome, 1, INSTR(sep2.nome, ' ') - 1) AS separador2,
                    SUBSTR(sep3.nome, 1, INSTR(sep3.nome, ' ') - 1) AS separador3,
                    SUBSTR(sep4.nome, 1, INSTR(sep4.nome, ' ') - 1) AS separador4,
                    
                    NVL(va.contador, 0) AS VA_CONTADOR,
                    NVL(va.qt, 0) AS VA_QT,
                    
                    
                    CASE
                        WHEN iw.dtfinalconf IS NOT NULL     THEN 3
                        WHEN iw.dtinicioconf IS NOT NULL    THEN 2
                        WHEN iw.dtinicioemb IS NOT NULL     THEN 1
                        ELSE 0 
                    END AS STATUS
                
                                
                    FROM
                        bonus_filtrado bonus
                        INNER JOIN pcbonusi item ON bonus.numbonus = item.numbonus
                        INNER JOIN pcprodut prod ON item.codprod = prod.codprod
                        INNER JOIN pcest est ON bonus.codfilial = est.codfilial AND item.codprod = est.codprod
                        LEFT JOIN (
                            SELECT numbonus, codprod,
                                MAX(dtarmazenagem) AS dtarmazenagem,
                                MAX(dtarmazenagemini) AS dtarmazenagemini,
                                MAX(dtarmazenagemfin) AS dtarmazenagemfin,
                                MAX(codseparador1) KEEP (DENSE_RANK LAST ORDER BY NVL(dtarmazenagemfin, NVL(dtarmazenagemini, dtarmazenagem)) NULLS FIRST) AS codseparador1,
                                MAX(codseparador2) KEEP (DENSE_RANK LAST ORDER BY NVL(dtarmazenagemfin, NVL(dtarmazenagemini, dtarmazenagem)) NULLS FIRST) AS codseparador2,
                                MAX(codseparador3) KEEP (DENSE_RANK LAST ORDER BY NVL(dtarmazenagemfin, NVL(dtarmazenagemini, dtarmazenagem)) NULLS FIRST) AS codseparador3,
                                MAX(codseparador4) KEEP (DENSE_RANK LAST ORDER BY NVL(dtarmazenagemfin, NVL(dtarmazenagemini, dtarmazenagem)) NULLS FIRST) AS codseparador4
                            FROM iwbonusarmazenar
                            GROUP BY numbonus, codprod
                        ) arm ON arm.numbonus = bonus.numbonus AND arm.codprod = item.codprod
                        LEFT JOIN iwbonusi iw ON iw.numbonus = bonus.numbonus AND iw.codprod = item.codprod
                        LEFT JOIN venda_antecipada va ON va.numbonus = bonus.numbonus AND va.codprod = item.codprod
                        LEFT JOIN pcempr emp ON bonus.codfuncrm = emp.matricula
                        LEFT JOIN pcempr sep1 ON sep1.matricula = arm.codseparador1 AND sep1.situacao = 'A'
                        LEFT JOIN pcempr sep2 ON sep2.matricula = arm.codseparador2 AND sep2.situacao = 'A'
                        LEFT JOIN pcempr sep3 ON sep3.matricula = arm.codseparador3 AND sep3.situacao = 'A'
                        LEFT JOIN pcempr sep4 ON sep4.matricula = arm.codseparador4 AND sep4.situacao = 'A'
                    WHERE (
                        bonus.dtfechamento IS NULL
                        OR NOT EXISTS (
                            SELECT 1
                            FROM iwbonusarmazenar arm_finalizado
                            WHERE arm_finalizado.numbonus = bonus.numbonus
                              AND arm_finalizado.dtarmazenagem IS NOT NULL
                        )
                    )
                    GROUP BY
                        bonus.numbonus,
                        sep1.nome,
                        sep2.nome,
                        sep3.nome,
                        sep4.nome,
                        arm.dtarmazenagem,
                        arm.dtarmazenagemini,
                        arm.dtarmazenagemfin,
                        EST.CODPROD,
                        bonus.codfilial,
                        bonus.databonus,
                        bonus.hora,
                        bonus.minuto,
                        bonus.dtmontagem,
                        bonus.dtfechamento,
                        item.codprod,
                        prod.descricao,
                        item.qtnf,
                        item.QTENTRADA,
                        iw.dtinicioconf,
                        iw.dtinicioemb,
                        iw.codconferentes,
                        iw.dtfinalconf,
                        va.contador,
                        va.qt
                    ORDER BY
                        bonus.numbonus ASC
                
            ) SUB
            
            LEFT JOIN pcempr emp ON INSTR(',' || SUB.codconfemb || ',', ',' || emp.matricula || ',') > 0
            
            GROUP BY
            SUB.STATUS,
            SUB.NUMBONUS,
            SUB.codfilial,
            SUB.databonus,
            SUB.hora,
            SUB.codconfemb,
            SUB.minuto,
            SUB.dtmontagem,
            SUB.dtfechamento,
            SUB.codprod,
            SUB.descricao,
            SUB.qtnf,
            SUB.QTENTRADA,
            SUB.QTDISP,
            SUB.dtinicioemb,
            SUB.dtinicioconf,
            SUB.dtfinalconf,
            SUB.conferenteBonus,
            SUB.dtarmazenagem,
            SUB.dtarmazenagemini,
            SUB.dtarmazenagemfin,
            SUB.separador1,
            SUB.separador2,
            SUB.separador3,
            SUB.separador4,
            SUB.VA_CONTADOR,
            SUB.VA_QT

            ORDER BY
            SUB.dtfechamento DESC,
            SUB.numbonus ASC,
            SUB.QTENTRADA ASC


    ";

    $stid_kap = oci_parse($conn, $query_kap);

     // Associe os parâmetros da consulta
     oci_bind_by_name($stid_kap, ":codfilial", $codfilial);

     
    oci_execute($stid_kap);



    echo "<table class='table table-hover bg-body-secondary' 
    style='
    font-size: 14px;
    font-weight: 600;'
    id='$tableName' data-show-columns='true'>
   ";
    echo "<div class='$title_pal'>";
    echo "<h4>  $title </h4>";
    echo "</div>";

    echo "<thead class='header $fundo'>";
    echo "<tr>";
        echo "<th scope='col'>NUMBONUS</th>";
        echo "<th scope='col'>#</th>";
        echo "<th scope='col'>DT_BONUS</th>";
        echo "<th scope='col'>DT_FECH</th>";
        echo "<th scope='col'>CODIGO</th>";
        echo "<th scope='col'>DESCRICAO</th>";
        echo "<th scope='col'>CONF</th>";
        echo "<th scope='col'>DISP</th>";
        echo "<th scope='col'>EMBALAGEM</th>";
        echo "<th scope='col'>CONFERENCIA</th>";
        echo "<th scope='col'>DT_ARMAZENAGEM</th>";

    echo "</tr>";
    echo "</thead>";

    echo "<tbody >";


    while ($row = oci_fetch_array($stid_kap, OCI_ASSOC)) {


        $databonus = isset($row['DATABONUS']) ? $row['DATABONUS'] : null;
        $qtentrada = isset($row['QTENTRADA']) ? $row['QTENTRADA'] : null;
        $conferenteBonus = isset($row['CONFERENTEBONUS']) ? $row['CONFERENTEBONUS'] : null;
        $codfilial = $row["CODFILIAL"];
        $dtarmazenagemini = isset($row["DTARMAZENAGEMINI"]) ? $row["DTARMAZENAGEMINI"] : null;
        $dtarmazenagemfin = isset($row["DTARMAZENAGEMFIN"]) ? $row["DTARMAZENAGEMFIN"] : null;
        $dtarmazenagem = isset($row["DTARMAZENAGEM"]) ? $row["DTARMAZENAGEM"] : null;
        $dtinicioemb = isset($row["DTINICIOEMB"]) ? $row["DTINICIOEMB"] : null;
        $dtinicioconf = isset($row["DTINICIOCONF"]) ? $row["DTINICIOCONF"] : null;
        
        
        $separador1 = isset($row["SEPARADOR1"]) ? $row["SEPARADOR1"] : null;
        $separador2 = isset($row["SEPARADOR2"]) ? $row["SEPARADOR2"] : null;
        $separador3 = isset($row["SEPARADOR3"]) ? $row["SEPARADOR3"] : null;
        $separador4 = isset($row["SEPARADOR4"]) ? $row["SEPARADOR4"] : null;
        $numbonus = isset($row["NUMBONUS"]) ? $row["NUMBONUS"] : null;
        $dtfecha = isset($row["DTFECHAMENTO"]) ? $row["DTFECHAMENTO"] : null;
        $codprod = isset($row["CODPROD"]) ? $row["CODPROD"] : null;
        $descricao = isset($row["DESCRICAO"]) ? $row["DESCRICAO"] : null;
        $qntent = isset($row["QTNF"]) ? $row["QTNF"] : null;
        $conferentesEmb = isset($row["CONFERENTES"]) ? $row["CONFERENTES"] : null;
        $qtddisp = isset($row["QTDISP"]) ? $row["QTDISP"] : null;
        $vaContador = isset($row["VA_CONTADOR"]) ? (int) $row["VA_CONTADOR"] : 0;
        $vaQt = isset($row["VA_QT"]) ? $row["VA_QT"] : 0;
        $tempoEmb = isset($row["TEMPO_EMB"]) ? $row["TEMPO_EMB"] : null;

        if ($tempoEmb !== null && $tempoEmb < 0) {
            $tempoEmb = '<br/>EMB > CONF';
        } elseif ($tempoEmb !== null) {
            $tempoEmb = '(' . $tempoEmb . ')';
        }

        
        $tempoConf = isset($row["TEMPO_CONF"])
            ? '(' . str_replace('AUTOMATICO', 'AUTOMÁTICO', $row["TEMPO_CONF"]) . ')'
            : null;
    

        //$bgVendaAntecipada = ($row["RECNUM"] != 0) ? 'style="background-color: #fdf8c1;"' : 'style="background-color: #ffff"';
        $bgVendaAntecipadaValor = ($vaContador > 0) ? ' style="text-decoration: line-through; font-weight: 600; color: red;"' : '';
        $dtfechaAtiv = (!$dtfecha && $databonus)  ? 'style="background-color: #ffd3d3"' : ' style="background-color: #ddffe2"'; 
        $bonusEmUso = (!$dtfecha && $qtentrada)  ? ' style=" background-color: #ffff87"' :' style="background-color: #ffd3d3"'; 

        $bloqueioConferencia = !$dtfecha
            ? ($qtentrada
                ? "disabled title='Conferência em andamento'"
                : "disabled title='Sem conferência'")
            : '';

        $status = '';
        if (!$dtfecha && $qtentrada) {
            $status = 'data-status="conferindo"';
        } 
        elseif (!$dtfecha && $databonus) {
            $status = 'data-status="semConferencia"';
        } else {
            $status = 'data-status="disponivel"';
        }

        
       

        $cssCustom = ($dtfecha) ? $dtfechaAtiv : $bonusEmUso;
        //$cssCustom = $dtfechaAtiv;

        echo "<tr  $status >";

            echo "<td data-codfilial='$codfilial' data-numbonus='$numbonus' style='text-align: center'>" . $numbonus . "</td>";

            if ($dtarmazenagemfin || $dtarmazenagem) {

                echo "
                
                    <td> 
                        <button  class='btnCheck bg-light'> 
                            <input 
                            style='    width: 1em;
                            height: 1em;
                            vertical-align: top;
                            background-position: center;
                            background-size: contain;'

                            type='checkbox' disabled checked  
                            data-dtarmazenagem='$dtarmazenagem' data-dtarmazenagemini='$dtarmazenagemini' data-dtarmazenagemfin='$dtarmazenagemfin' 
                            data-codprod='$codprod'   data-numbonus='$numbonus' 
                            data-codfilial='$codfilial' data-qtddisp='$qtddisp' data-qtdent='$qntent' /> 
                        </button> 
                    </td>";

            }
            
            else {

                echo "

                    <td > 
                        <button  class='btnCheck bg-light'> 
                            <input
                            style='width: 1em;
                            height: 1em;
                            vertical-align: top;
                            background-position: center;
                            background-size: contain;'
                            type='checkbox' $bloqueioConferencia
                            data-dtarmazenagem='$dtarmazenagem' data-dtarmazenagemini='$dtarmazenagemini' data-dtarmazenagemfin='$dtarmazenagemfin' 
                            data-codprod='$codprod' data-numbonus='$numbonus' 
                            data-codfilial='$codfilial' data-qtddisp='$qtddisp' data-qtdent='$qntent' /> 
                        </button> 
                    </td>";

            }
            
            echo "<td  $cssCustom > " . $databonus . "</td>";
            echo "<td  $cssCustom > " . $dtfecha . "</td>";
            echo "<td $cssCustom >" . $codprod . "</td>";
           

            if($vaContador > 0){

                $resultVa = $qntent - $vaQt;

                echo "<td $cssCustom> $descricao 
                <p style='margin-top: 10px;  margin-bottom: 0px; font-size: 12px; font-weight: 600; color: red;'> 
                $vaContador VENDA ANTECIPADA DE $vaQt</p>
                </td>";


           }else{

                echo "<td $cssCustom> $descricao </td>";
           }


           if($vaContador > 0){

                echo "<td $cssCustom> 
                    <p $bgVendaAntecipadaValor> $qntent</p>
                    <p >$resultVa</p>
                </td>";
            
           }else{
            
            echo "<td $cssCustom> $qntent </td>";

           }

        


            echo "<td $cssCustom>" . $qtddisp . "</td>";



            // EMBALAGEM
            // ===============================================================
            echo "<td $cssCustom> $dtinicioemb 
                    <div style='font-size: 12px; font-weight: 600;'>
                    $conferentesEmb $tempoEmb
                    </div>
                </td>";




            // CONFERENCIA
            // ===============================================================
            echo "<td $cssCustom> $dtinicioconf
                    <div style='font-size: 12px; font-weight: 600;'>
                    $conferenteBonus $tempoConf
                    </div>
                </td>";



            // EQUIPE DE ARMAZENAGEM
            // ===============================================================
            echo "<td  $cssCustom id='bodyArmazenagem'> Início: $dtarmazenagemini <br> Fim: $dtarmazenagemfin <br> $dtarmazenagem " ;
                echo "<div style='font-size: 12px; font-weight: 600;'>";
                    if ($separador1 && !$separador2 && !$separador3 && !$separador4) {
                        echo "$separador1";
                    } else if ($separador1 && $separador2 && !$separador3 && !$separador4) {
                        echo " $separador1, $separador2";
                    } else if ($separador1 && $separador2 && $separador3 && !$separador4) {
                        echo "$separador1, $separador2, $separador3 ";
                    } else if ($separador1 && $separador2 && $separador3 && $separador4) {
                        echo " $separador1, $separador2, $separador3, $separador4";
                    }
                echo "   </div>";
            echo " </td>";

     

        echo "</tr>";   

              


    

    }

    oci_free_statement($stid_kap);
    oci_close($conn);
    
   
    echo "</tbody>";
    echo "</table>";
}