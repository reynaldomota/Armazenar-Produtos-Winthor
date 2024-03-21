
 $(document).ready(function() {
    $('#closeAlert').click(function() {
        document.getElementById('alertMsg').style.display = 'none';
    });
});


const btnArmazenar = document.getElementById('btnArmazenar')

function getCookie(nomeCookie) {
    var cookies = document.cookie.split('; ');
    for (var i = 0; i < cookies.length; i++) {
        var cookie = cookies[i].split('=');
        if (cookie[0] === nomeCookie) {
            return cookie[1];
        }
    }
    return null;
}


var dtinit = getCookie('dtinit');
var dtfin = getCookie('dtfin');


if (dtinit || dtfin) {
 
    $('#campoDtInicio').val(dtinit);
    $('#campoDtFinal').val(dtfin);

} else {

    setCookie(dtinit, dtfin)

}


function setCookie(dtinit, dtfin) {

    document.cookie = "dtinit=" + dtinit + "; max-age=" + (60 * 60 * 48);
    document.cookie = "dtfin=" + dtfin + "; max-age=" + (60 * 60 * 48);
    location.reload();

}



btnArmazenar.addEventListener('click', () => {




    listaItensArmazenar = []

    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked:not([disabled])');


    if (checkboxes.length > 0) {


        const modal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        modal.show();

        const tbodyDestino = document.getElementById('tbodyDestino');
        tbodyDestino.innerHTML = '';


        checkboxes.forEach((checkbox) => {


            const data = [];
            const row = checkbox.closest('tr');
            row.querySelectorAll('td').forEach((cell) => {

                data.push(cell.textContent);

            });
            const newRow = document.createElement('tr');

            const numbonus = checkbox.getAttribute('data-numbonus');
            const codfilial = checkbox.getAttribute('data-codfilial');
            const codprod = checkbox.getAttribute('data-codprod');
            const qtddisp = checkbox.getAttribute('data-qtddisp');
            const qtdent = checkbox.getAttribute('data-qtdent');

            listaItensArmazenar.push({
                codfilial: parseInt(codfilial),
                numbonus: parseInt(numbonus),
                codprod: parseInt(codprod),
                qtddisp: parseInt(qtddisp),
                qtdent: parseInt(qtdent)
            });

            // Começa a partir do índice 1 para evitar adicionar a primeira coluna
            for (let i = 1; i < data.length; i++) {
                const cell = document.createElement('td');
                cell.textContent = data[i];
                newRow.appendChild(cell);
            }

            tbodyDestino.appendChild(newRow);


        });


    } else {


        $.notify({
            icon: 'fa fa-exclamation-circle',
            message: "Selecione no mínimo um produto."
        }, {
            type: "warning",
            placement: {
                from: 'top',
                align: 'right',
            }
        });


    }



});




$(async function() {

        
    $('#formRelatorio').submit(async function(e){

        e.preventDefault();
    
        let dtinitAlter = $('#campoDtInicio').val();
        let dtfinAlter = $('#campoDtFinal').val();

        setCookie(dtinitAlter, dtfinAlter)

        location.reload()
        /*           
        if(dtIni == '' || dtFin == ''){

            $('#erroCampoRelatorio').html('<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Atenção!</strong> Você precisa preencher os dois campos com uma data.<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span></button></div>');

        }
        else{

            $.ajax({
                type: "POST",
                url: "relatorio.php",
                data: dados,
                success: function(data){
                    $('#principal').html(data);

                    $('#campoDtInicio').css('background-color', '#ffc107');
                    $('#campoDtInicio').css('color', 'black');
                    $('#campoDtFinal').css('background-color', '#ffc107');
                    $('#campoDtFinal').css('color', 'black');
                }

            });
        }
            
        */

    })


    $('.alert').hide()


    getBonusKap = await getProductBonus(1, dtinit, dtfin)
    $("#tabela_kap").html(getBonusKap);


    getBonusPen = await getProductBonus(2, dtinit, dtfin)
    $("#tabela_pen").html(getBonusPen);


    getBonusFor = await getProductBonus(3, dtinit, dtfin)
    $("#tabela_for").html(getBonusFor);


    getBonusGua = await getProductBonus(4, dtinit, dtfin)
    $("#tabela_gua").html(getBonusGua);


    async function getProductBonus(codfilial, dtinit, dtfin) {


        const jsonData = {
            codfilial,
            dtinit,
            dtfin
        };

        if (codfilial == 1) {
            divLoja = '#tabela_kap';
        } else if (codfilial == 2) {
            divLoja = '#tabela_pen';
        } else if (codfilial == 3) {
            divLoja = '#tabela_for';
        } else if (codfilial == 4) {
            divLoja = '#tabela_gua';
        }

        const data = await $.ajax({
            url: "function.php",
            type: 'POST',
            data: JSON.stringify(jsonData),
            beforeSend: function() {

                $(divLoja).html("<img style=' display: block; margin: auto; height: 150px;' src='SRC/_img/loading1.gif'>");

            },

            success: async function() {


                
                setTimeout(async function () {
                    await agrupar('table-kap')
                }, 100);

                setTimeout(async function () {
                    await agrupar('table-pen')
                }, 100);

                setTimeout(async function () {
                    await agrupar('table-for')
                }, 100);

                setTimeout(async function () {
                    await agrupar('table-gua')
                }, 100);

                

                
            }


        });

        return data;



    }


    
    async function agrupar(tableFilial){

        table = document.getElementById(tableFilial);
        numRows = table.rows.length;
        colNumbonus = {};

        for (let i = 0; i < numRows; i++) {
            let currentCell = table.rows[i].cells[0]; 

            let numbonus = currentCell.textContent;

            
            if (colNumbonus.hasOwnProperty(numbonus)) {
                colNumbonus[numbonus]++;
                currentCell.setAttribute('style', 'display: none'); 
            } else {

                colNumbonus[numbonus] = 1;

                let rowspan = 1;
                for (let j = i + 1; j < numRows; j++) {
                    let nextCell = table.rows[j].cells[0];
                    if (nextCell.textContent === numbonus) {
                        rowspan++;
                        nextCell.setAttribute('style', 'display: none'); 
                    } else {
                        break;
                    }
                }
                if (rowspan > 1) {
                    currentCell.setAttribute('rowspan', rowspan);
                    currentCell.style.textAlign = 'center';
                    currentCell.style.verticalAlign = 'middle';
                    currentCell.style.height = '100px';

                }
            }
        }

    } 
    

    listaConferente = []
    qtdConferente = 0;
    $('#codConferente').keypress(function(e) {


        var key = e.which;


        if (key == 13) {



            codconferente = $(this).val()


            if (qtdConferente > 5) {


                qtdConferente = qtdConferente + 1;
                console.log(qtdConferente);


                document.getElementById('alertMsg').style.display = 'block';
                $('#msgAlert').html('Só é permitido 4 conferentes.')

                $('#codConferente').val('')
                $('#nomeConferente').val('')



                throw new Error("Só é permitido 4 conferentes.")
            }



            $.ajax({
                type: "POST",
                url: "function.php",
                data: JSON.stringify({
                    codconferente
                }),


                success: function(data) {


                    $('[data-bs-toggle="popover"]').popover();

                    dados = JSON.parse(data);

                    if (dados.erro == true) {


                        document.getElementById('alertMsg').style.display = 'block';
                        $('#msgAlert').html(dados.msg)
                        $('#codConferente').val('')


                    } else {

                        $('#nomeConferente').val(dados.nome)
                        $('#codauxiliar').focus()


                    }


                }


            }).done(function() {});




        }



    });



    qtdConf = 0
    $('#btnAddConferente').click(function(e) {

        codConferenteUp = document.getElementById('codConferente')
        nomeConferenteUp = document.getElementById('nomeConferente')
        conferente = document.getElementById('codConferente').value

        qtdConf = qtdConf + 1;

        if (qtdConf > 4) {
            /* 
                document.getElementById('alertMsg').style.display = 'block';
                $('#msgAlert').html('Quantidade máximo de conferente: 4')
                $('#codConferente').val('')
                $('#nomeConferente').val('') 
            */
            $('#codConferente').attr('disabled', true)
            $('#nomeConferente').attr('disabled', true)

            throw new Error("Quantidade máximo de conferente: 4")
        }




        if (listaConferente.includes(conferente)) {


            document.getElementById('alertMsg').style.display = 'block';
            $('#msgAlert').html('Este conferente já esta na lista de responsáveis.')

            codConferenteUp.value = ''
            nomeConferenteUp.value = ''


            throw new Error("Este conferente já esta na lista.")

        } else {


            divRow = document.createElement("tr");
            div1 = document.createElement("td");
            div2 = document.createElement("td");
            div1.textContent = codConferenteUp.value;
            div2.textContent = nomeConferenteUp.value;
            divRow.appendChild(div1);
            divRow.appendChild(div2);

            document.getElementById("listConferente").appendChild(divRow);

            listaConferente.push(conferente)

            console.log(`${conferente} - Adicionado na lista de conferentes.`);

        }



        codConferenteUp.value = ''
        nomeConferenteUp.value = ''
        codConferenteUp.removeAttribute('disabled');
        nomeConferenteUp.removeAttribute('disabled');



    });



    $('#btnEnviar').click((e) => {


        console.log('Enviado lista de itens: ' + listaItensArmazenar);


        if (listaConferente.length == 0) {

            document.getElementById('alertMsg').style.display = 'block';
            $('#msgAlert').html('Informe um conferente responsável pelo armazenamento.')

        } else {

            $.ajax({
                type: "POST",
                url: "function.php",
                data: JSON.stringify({
                    listaItensArmazenar,
                    listaConferente
                }),


                success: function(data) {



                    dados = JSON.parse(data);

                    if (dados.erro == false) {

                        $(".modal").modal("hide");


                        $.notify({
                            icon: 'fa fa-exclamation-circle',
                            message: dados.msg
                        }, {
                            type: dados.tipo,
                            placement: {
                                from: 'top',
                                align: 'right',
                            }
                        });



                    } else {


                        $(".modal").modal("hide");


                        $.notify({
                            icon: 'fa fa-exclamation-circle',
                            message: dados.msg
                        }, {
                            type: dados.tipo,
                            placement: {
                                from: 'top',
                                align: 'right',
                            }
                        });


                        setTimeout(function() {
                            location.reload();
                        }, 2000);


                    }


                }


            }).done(function() {

                setInterval(() => {
                    location.reload();
                }, 2000);

            });

        }


    })



    
    $('#filtroStatus button').click(function() {

        $('table tbody tr').show();


        
        var statusFiltro = $(this).data('status');
        if (statusFiltro === 'todos') {
            $('table tbody tr').show();
        } else {
            $('table tbody tr').each(function() {
                var status = $(this).data('status');
                if (status === statusFiltro) {
                    var filial = $(this).data('codfilial');
                    var numbonus = $(this).find('td').data('numbonus');
                    var numbonusVisivel = 0;
                    $('table tbody tr').each(function() {
                        if (    $(this).is(':visible') && 
                                $(this).data('status') === statusFiltro && 
                                $(this).data('codfilial') === filial && 
                                $(this).find('td[data-numbonus="' + numbonus + '"]').length > 0
                            ) { 
                                numbonusVisivel++; 
                                 
                                /* if ($(this).find('td[data-numbonus="' + numbonus + '"]').index() === 0) {
                                    $(this).find('td[data-numbonus="' + numbonus + '"]').attr('rowspan', numbonusVisivel).show();
                                }  */

                            }
                    });
                    $(this).find('td[data-numbonus="' + numbonus + '"]').attr('rowspan', numbonusVisivel);
                } else {
                    $(this).hide();
                }
            });
        }
    });




});