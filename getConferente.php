<?php


    // BUSCAR CONFERE
      //===========================================================================
      function getConferente($codConferente){

        include("/config/conexao.php"); 


        $query = " select matricula, nome, codsetor,situacao from pcempr where matricula = '{$codConferente}' and situacao = 'A'";
        $stid = oci_parse($conn, $query);
        oci_execute($stid, OCI_DEFAULT);

        $row = oci_fetch_array($stid, OCI_ASSOC);
        $nome = $row["NOME"];
        $situacao = $row["SITUACAO"];
        $matricula = $row["MATRICULA"];
        $codsetor = $row["CODSETOR"];

        if(!empty($nome)){

              if( ($codsetor == 15) && ($situacao == 'A')){

  
                    $retorno = array(
                          "matricula" => $matricula, 
                          "nome" => $nome,
                          "erro"=>false,
                          );
                          
                    echo json_encode($retorno);
              

              }else{

                    $retorno = array(
                          "msg" => 'Este usuário não é um conferente ou esta inativado',
                          "erro"=>true
                          );
                          
                    echo json_encode($retorno);

              }


              


        }else{
              $retorno = array(
                    "msg"=>"Não existe um separador com este código!",
                    "erro"=>true
              );
                    
              echo json_encode($retorno);
        }
        
        


  }
