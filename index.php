<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dias = $_POST['qtd_dias'];
    $dtinit = $_POST['dtinit'];
    $dtfin = $_POST['dtfin'];

    setcookie('dtinit', $dtinit,  time() + 172800);
    setcookie('dtfin', $dtfin,  time() + 172800);

}


if (!isset($_COOKIE['dtinit'])) {
    $currentDate = new DateTime();
    $currentDate->sub(new DateInterval('P15D'));
    $newDate = $currentDate->format('Y-m-d');
    setcookie('dtinit', $newDate,  time() + 172800);
    $_COOKIE['dtinit'] = $newDate;
}

if (!isset($_COOKIE['dtfin'])) {

    setcookie('dtfin', date('Y-m-d'),  time() + 172800);
    $_COOKIE['dtfin'] = date('Y-m-d');
    
}


?>


<html>


<head>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="src/_css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="src/_css/styles.css" type="text/css">
    <link rel="icon" href="src/_img/favicon.png" />
    <title>Entrada de Mercadoria</title>
</head>

<body background="src/_img/fundo_total.png">


    <div class="modal fade" id="staticBackdrop" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered modal-lg">

            <div class="modal-content">


                <div class="alert alert-warning alert-dismissible fade show" id="alertMsg" style="display:none;" role="alert">
                    <span id="msgAlert"></span>
                    <button type="button" class="btn-close" id="closeAlert"></button>
                </div>


                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Lista de Produtos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>



                <div class="modal-body">


                    <table id="tabelaDestino" class="table table-bordered table-hover bg-light" data-show-columns='true'>

                        <thead id='theader-kap' class="header bg-kapitao">

                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">DATA</th>
                                <th scope="col">CODIGO</th>
                                <th scope="col">DESCRICAO</th>
                                <th scope="col">CONF</th>
                                <th scope="col">DISP</th>
                                <th scope="col">EMB</th>
                                <th scope="col">CONF</th>
                                <th scope="col"></th>
                            </tr>

                        </thead>
                        <tbody id="tbodyDestino"> </tbody>

                    </table>




                    <h5 class="mt-5 mb-3 text-center">Responsáveis pelo armazenamento</h5>




                    <table class="table table-bordered table-hover" data-show-columns='true'>

                        <thead class="table-dark">

                            <tr>
                                <th scope="col">CÓDIGO</th>
                                <th scope="col">NOME DO CONFERENTE</th>
                            </tr>

                        </thead>
                        <tbody id="listConferente">
                        </tbody>


                    </table>



                </div>
                <div class="modal-footer">


                    <div class="row">
                        <div class="col-md-2">
                            <label for="codConferente">COD</label>
                            <input type="text" class="form-control" id="codConferente">
                        </div>
                        <div class="col-md-5">
                            <label for="nomeSeparador">NOME DO CONFERENTE</label>
                            <input type="text" disabled="" class="form-control" id="nomeConferente">
                        </div>
                        <div class="col-md-2">
                            <label for="codauxiliar">ADICIONAR</label>
                            <button type="button" id="btnAddConferente" class="btn btn-primary">+</button>
                        </div>

                        <div class="col-md-2">
                            <label for="btnConferir">GRAVAR</label>
                            <button type="button" id="btnEnviar" class="btn btn-primary">ENVIAR</button>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </div>


    <div style=" position: fixed; width: 100%;">

        <nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="    height: 50px; background-color: white; padding: 1px;">

            <div id="statusCon" class="container-fluid">


                <a class="navbar-brand" style=" padding-right: 20px; font-weight: 600;" href="/armazenar">Entrada de Mercadorias</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>


                <div id="filtroStatus" class="btn-group" >
                    <button type="button" data-status="disponivel" class="" style="background-color: #ddffe2; border: 1px solid #cdc7c7;">Disponível para venda</button>
                    <button type="button" data-status="conferindo" class="btn btn-sm" style="background-color: #ffff87; border: 1px solid #cdc7c7;">Conferência em andamento <i class="fas fa-caret-right iconeEspaco"></i></button>
                    <button type="button" data-status="semConferencia" class="btn  btn-sm" style="background-color: #ffd3d3; border: 1px solid #cdc7c7;">Sem conferência <i class="fas fa-caret-right iconeEspaco"></i> </button>
                    <button type="button" class="btn btn-sm" data-status="todos" style=" border: 1px solid #cdc7c7;">Todos</button>
                </div>

               
                <form id="formRelatorio" class="btn-group" style="color: rgb(255, 255, 255); margin-top: 15px;" method="POST" action="">
                    <i id="iconeCalendario" class="fas fa-calendar-alt" style="margin-right: 10px"> </i>
                    <input id="campoDtInicio" class="form-control form-control-sm" style="margin-right: 10px" name="dataIni" type="date">
                    <input id="campoDtFinal" class="form-control form-control-sm" name="dataFin" type="date">
                    <input class="btn btn-warning btn-sm"  onclick='setCookie()' style="margin-left: 10px; margin-right: 10px;" type="submit" value="Buscar" name="enviar">
                </form>
    

                <form class="d-flex m-0 ">
                    <button id="btnArmazenar" type="button" class="btn btn-sm"  style=" border: 1px solid #cdc7c7;">
                        Armazenar
                    </button>
                </form>


            </div>
        </nav>

    </div>


    <main class="container-fluid ">

        <div class="tabela_geral ">

            <div class="tabela_kap" id="tabela_kap"></div>
            <div class="tabela_pen" id="tabela_pen"></div>
            <div class="tabela_for" id="tabela_for"></div>
            <div class="tabela_gua" id="tabela_gua"></div>

        </div>

    </main>


    <script src="src/_js/jquery-3.6.0.min.js"></script>
    <script src="src/_js/bootstrap.bundle.min.js"></script>
    <script src="src/_js/bootstrap-notify.min.js"></script>
    <script src="src/_js/script.js"></script>


</body>


</html>