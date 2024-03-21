
<?php

function setItensBonus($listaItensArmazenar, $listaConferente){

      include("/config/conexao.php");

      $codseparador1 = isset($listaConferente[0]) ? $listaConferente[0] : null;
      $codseparador2 = isset($listaConferente[1]) ? $listaConferente[1] : null;
      $codseparador3 = isset($listaConferente[2]) ? $listaConferente[2] : null;
      $codseparador4 = isset($listaConferente[3]) ? $listaConferente[3] : null;


      foreach ($listaItensArmazenar as $item) {
            $numbonus = $item['numbonus'];
            $codprod = $item['codprod'];


            //$qtddisp = $item['qtddisp'];
            //$qtdent = $item['qtdent'];


            $queryExiste = "
                  select 
                        arm.numbonus
                  from iwbonusarmazenar arm
                  where arm.numbonus = '$numbonus'  
                  and  arm.codprod = '$codprod'  
            ";
            $stidExiste = oci_parse($conn, $queryExiste);
            oci_execute($stidExiste, OCI_DEFAULT);
            $rowExiste = oci_fetch_array($stidExiste, OCI_ASSOC);

            if ($rowExiste["NUMBONUS"] == $numbonus) {

                  $retorno = array(
                        "tipo" => "warning",
                        "msg" => "Este item deste bônus já foi armazenado",
                        "erro" => false
                  );

                  echo json_encode($retorno);

                  exit; 
                  
            } else {

                  $query = "INSERT INTO iwbonusarmazenar (dtarmazenagem, codprod, numbonus, codseparador1, codseparador2, codseparador3, codseparador4) VALUES (SYSDATE, '$codprod', '$numbonus', '$codseparador1', '$codseparador2', '$codseparador3', '$codseparador4')";
                  $stid = oci_parse($conn, $query);
                  oci_execute($stid, OCI_DEFAULT);
                  oci_commit($conn);

            
            }
      }

      $retorno = array(
            "tipo" => "success",
            "msg" => "Conferência gravada com sucesso",
            "erro" => false
      );

      echo json_encode($retorno); 


}








?>