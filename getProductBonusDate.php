<?php


function getProductBonusDate($codfilial, $dtinit, $dtfin){


    if ($codfilial == 1) {
        $title = 'KAPITAO ÁMERICA';
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
    } else if ($codfilial == 4) {
        $title = 'GUARDA VIDA EPI';
        $title_pal = 'title_gua';
        $fundo = 'bg-guarda';
        $tableName = 'table-gua';
    }



    include("/config/conexao.php");

    $query_kap = "


            SELECT 
            SUB.STATUS,
            SUB.NUMBONUS,
            SUB.codfilial,
            SUB.databonus,
            SUB.hora,
            SUB.minuto,
            SUB.dtmontagem,
            SUB.dtfechamento,
            SUB.codprod,
            SUB.descricao,
            SUB.qtnf,
            SUB.QTENTRADA,
            SUB.QTDISP,
            TO_CHAR(SUB.dtinicioemb, 'DD/MM/YYYY HH24:MI:SS') AS dtinicioemb,
            TO_CHAR(SUB.dtinicioconf, 'DD/MM/YYYY HH24:MI:SS') AS dtinicioconf,
            
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
                TO_CHAR('AUTOMÁTICO')
                
                
                WHEN STATUS = 3 THEN
                LPAD(TO_CHAR(FLOOR((SUB.dtfinalconf - SUB.dtinicioconf) * 24)), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtfinalconf - SUB.dtinicioconf) * 1440, 60))), 2, '0') ||
                ':' || LPAD(TO_CHAR(FLOOR(MOD((SUB.dtfinalconf - SUB.dtinicioconf) * 86400, 60))), 2, '0')
                
                
                ELSE NULL
                
            END AS TEMPO_CONF,
           
            SUB.dtarmazenagem,
            SUB.separador1,
            SUB.separador2,
            SUB.separador3,
            SUB.separador4,
            SUB.RECNUM
            
            
                
        
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
                   
                    TO_CHAR(arm.dtarmazenagem, 'DD/MM/YYYY HH24:MI:SS') AS dtarmazenagem,
                    SUBSTR(sep1.nome, 1, INSTR(sep1.nome, ' ') - 1) AS separador1,
                    SUBSTR(sep2.nome, 1, INSTR(sep2.nome, ' ') - 1) AS separador2,
                    SUBSTR(sep3.nome, 1, INSTR(sep3.nome, ' ') - 1) AS separador3,
                    SUBSTR(sep4.nome, 1, INSTR(sep4.nome, ' ') - 1) AS separador4,
                    
                    CASE
                        WHEN max(PCLANC.HISTORICO) LIKE 'VA ' || BONUS.NUMBONUS || '%' THEN SUM(RECNUM)
                        ELSE 0
                    END AS RECNUM,
                    
                    
                    CASE
                        WHEN iw.dtfinalconf IS NOT NULL     THEN 3
                        WHEN iw.dtinicioconf IS NOT NULL    THEN 2
                        WHEN iw.dtinicioemb IS NOT NULL     THEN 1
                        ELSE 0 
                    END AS STATUS
                
                                
                    FROM
                        pcbonusc bonus
                        INNER JOIN pcbonusi item ON bonus.numbonus = item.numbonus
                        INNER JOIN pcprodut prod ON item.codprod = prod.codprod
                        INNER JOIN pcmov mov ON item.codprod = mov.codprod
                        INNER JOIN pcest est ON bonus.codfilial = est.codfilial AND item.codprod = est.codprod
                        LEFT JOIN iwbonusarmazenar arm ON arm.numbonus = bonus.numbonus AND arm.codprod = item.codprod
                        LEFT JOIN iwbonusi iw ON iw.numbonus = bonus.numbonus AND iw.codprod = item.codprod
                        LEFT JOIN pclanc ON PCLANC.numtransent = MOV.numtransent AND PCLANC.HISTORICO LIKE 'VA ' || BONUS.NUMBONUS || '%'
                        LEFT JOIN pcempr emp ON bonus.codfuncrm = emp.matricula
                        LEFT JOIN pcempr sep1 ON sep1.matricula = arm.codseparador1 AND sep1.situacao = 'A'
                        LEFT JOIN pcempr sep2 ON sep2.matricula = arm.codseparador2 AND sep2.situacao = 'A'
                        LEFT JOIN pcempr sep3 ON sep3.matricula = arm.codseparador3 AND sep3.situacao = 'A'
                        LEFT JOIN pcempr sep4 ON sep4.matricula = arm.codseparador4 AND sep4.situacao = 'A'
                    WHERE
                        bonus.databonus BETWEEN TO_DATE(:dtinit, 'YYYY-MM-DD') and TO_DATE(:dtfin, 'YYYY-MM-DD')

                        AND bonus.codfilial = :codfilial
                    GROUP BY
                        bonus.numbonus,
                        sep1.nome,
                        sep2.nome,
                        sep3.nome,
                        sep4.nome,
                        arm.dtarmazenagem,
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
                        iw.dtfinalconf
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
            SUB.separador1,
            SUB.separador2,
            SUB.separador3,
            SUB.separador4,
            SUB.RECNUM

            ORDER BY
            SUB.numbonus, SUB.QTENTRADA asc


    ";

    $stid_kap = oci_parse($conn, $query_kap);

     // Associe os parâmetros da consulta
     oci_bind_by_name($stid_kap, ":dtinit", $dtinit);
     oci_bind_by_name($stid_kap, ":dtfin", $dtfin);
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
        echo "<th scope='col'>DT BONUS</th>";
        echo "<th scope='col'>CODIGO</th>";
        echo "<th scope='col'>DESCRICAO</th>";
        echo "<th scope='col'>CONF</th>";
        echo "<th scope='col'>DISP</th>";
        echo "<th scope='col'>EMBALAGEM</th>";
        echo "<th scope='col'>CONFERENCIA</th>";
        echo "<th scope='col'>DT ARMAZENAGEM</th>";

    echo "</tr>";
    echo "</thead>";

    echo "<tbody >";


    while ($row = oci_fetch_array($stid_kap, OCI_ASSOC)) {


        $databonus = isset($row['DATABONUS']) ? $row['DATABONUS'] : null;
        $qtentrada = isset($row['QTENTRADA']) ? $row['QTENTRADA'] : null;
        $conferenteBonus = isset($row['CONFERENTEBONUS']) ? $row['CONFERENTEBONUS'] : null;
        $codfilial = $row["CODFILIAL"];
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
        $recnum = isset($row["RECNUM"]) ? $row["RECNUM"] : null;
        $tempoEmb = isset($row["TEMPO_EMB"]) ? $row["TEMPO_EMB"] : null;

        if ($tempoEmb !== null && $tempoEmb < 0) {
            $tempoEmb = '<br/>EMB > CONF';
        } elseif ($tempoEmb !== null) {
            $tempoEmb = '(' . $tempoEmb . ')';
        }

        
        $tempoConf = isset($row["TEMPO_CONF"]) ? '('.$row["TEMPO_CONF"].')' : null;
    

        //$bgVendaAntecipada = ($row["RECNUM"] != 0) ? 'style="background-color: #fdf8c1;"' : 'style="background-color: #ffff"';
        $bgVendaAntecipadaValor = ($row["RECNUM"] != 0) ? ' style="text-decoration: line-through; font-weight: 600; color: red;"' : '';
        $dtfechaAtiv = (!$dtfecha && $databonus)  ? 'style="background-color: #ffd3d3"' : ' style="background-color: #ddffe2"'; 
        $bonusEmUso = (!$dtfecha && $qtentrada)  ? ' style=" background-color: #ffff87"' :' style="background-color: #ffd3d3"'; 

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

            if ($dtarmazenagem  ) {

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
                            data-dtarmazenagem='$dtarmazenagem' 
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
                            type='checkbox' 
                            data-dtarmazenagem='$dtarmazenagem' 
                            data-codprod='$codprod' data-numbonus='$numbonus' 
                            data-codfilial='$codfilial' data-qtddisp='$qtddisp' data-qtdent='$qntent' /> 
                        </button> 
                    </td>";

            }
            
            echo "<td  $cssCustom > " . $databonus . "</td>";
            echo "<td $cssCustom >" . $codprod . "</td>";
           

            if($row["RECNUM"] != 0){

                $query = " SELECT PCMOV.QT FROM PCLANC, PCMOV WHERE PCLANC.NUMTRANSENT = PCMOV.numtransent AND PCLANC.RECNUM = '{$recnum}'";
                $stid = oci_parse($conn, $query);
                oci_execute($stid, OCI_DEFAULT);
    
                $rowVA = oci_fetch_array($stid, OCI_ASSOC);
                $qt = $rowVA["QT"] ;

                $resultVa = $qntent - $qt;

                echo "<td $cssCustom> $descricao 
                <p style='margin-top: 10px;  margin-bottom: 0px; font-size: 12px; font-weight: 600; color: red;'> 
                VENDA ANTECIPADA DE $qt</p>
                </td>";


           }else{

                echo "<td $cssCustom> $descricao </td>";
           }


           if($row["RECNUM"] != 0){

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
            echo "<td  $cssCustom id='bodyArmazenagem'>  $dtarmazenagem " ;
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