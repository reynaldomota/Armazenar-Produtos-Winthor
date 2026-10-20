<?php

    header('Content-Type: text/html; charset=UTF-8');


    require_once ("getProductBonusDate.php");
    require_once ("getConferente.php");
    require_once ("setItensBonus.php");
    require_once ("getConferentesArmazenagem.php");

    $resp = file_get_contents("php://input");
    $data = json_decode($resp, true);

    $codfilial = isset($data['codfilial']) ? $data['codfilial'] : null;

    $codconferente = isset($data['codconferente']) ? $data['codconferente'] : null;
    $listaItensArmazenar = isset($data['listaItensArmazenar']) ? $data['listaItensArmazenar'] : [];
    $listaConferente = isset($data['listaConferente']) ? $data['listaConferente'] : [];
    $acao = isset($data['acao']) ? $data['acao'] : null;



    if (!empty($listaItensArmazenar) && $acao === 'conferentes') {
        getConferentesArmazenagem($listaItensArmazenar);
    }
    else if (!empty($listaItensArmazenar) && in_array($acao, ['iniciar', 'finalizar'], true)) {
    
        setItensBonus($listaItensArmazenar, $listaConferente, $acao);
    } 
    else if(
        isset($codfilial)
        && in_array((int) $codfilial, [1, 2, 3], true)
        && $codconferente == null
    ){
        
        getProductBonusDate((int) $codfilial);

    }
    else if( $codconferente  !== null ){
        
        getConferente($codconferente);

    }


?>
