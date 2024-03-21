<?php


    require_once ("getProductBonusDate.php");
    require_once ("getConferente.php");
    require_once ("setItensBonus.php");

    $resp = file_get_contents("php://input");
    $data = json_decode($resp, true);

    $codfilial = isset($data['codfilial']) ? $data['codfilial'] : null;
    $dias = isset($data['dias']) ? $data['dias'] : null;
    $dtinit = isset($data['dtinit']) ? $data['dtinit'] : null;
    $dtfin = isset($data['dtfin']) ? $data['dtfin'] : null;

    $codconferente = isset($data['codconferente']) ? $data['codconferente'] : null;
    $listaItensArmazenar = isset($data['listaItensArmazenar']) ? $data['listaItensArmazenar'] : [];
    $listaConferente = isset($data['listaConferente']) ? $data['listaConferente'] : [];



    if (!empty($listaItensArmazenar)  && !empty($listaConferente)) {
    
        setItensBonus($listaItensArmazenar, $listaConferente);
    } 
    else if( isset($codfilial)  && $codfilial !== null  && $codconferente == null ){
        
        getProductBonusDate($codfilial, $dtinit, $dtfin);

    }
    else if( $codconferente  !== null ){
        
        getConferente($codconferente);

    }


?>