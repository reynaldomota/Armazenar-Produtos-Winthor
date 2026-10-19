
 $(document).ready(function() {
    $('#closeAlert').click(function() {
        document.getElementById('alertMsg').style.display = 'none';
    });
});


let checkboxesArmazenar = [];
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

function dataParaExibicao(iso) {
    const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
    return partes ? `${partes[3]}/${partes[2]}/${partes[1].slice(-2)}` : '';
}

function dataParaConsulta(valor) {
    const partes = /^(\d{2})\/(\d{2})\/(\d{2})$/.exec(valor);
    if (!partes) return null;
    const dia = Number(partes[1]), mes = Number(partes[2]), ano = 2000 + Number(partes[3]);
    const data = new Date(Date.UTC(ano, mes - 1, dia));
    if (data.getUTCFullYear() !== ano || data.getUTCMonth() !== mes - 1 || data.getUTCDate() !== dia) return null;
    return `${ano}-${partes[2]}-${partes[1]}`;
}

$('#campoDtInicio, #campoDtFinal').on('input', function() {
    const numeros = this.value.replace(/\D/g, '').slice(0, 6);
    this.value = numeros.replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2}\/\d{2})(\d)/, '$1/$2');
    this.setCustomValidity('');
});

if (dtinit || dtfin) {
 
    $('#campoDtInicio').val(dataParaExibicao(dtinit));
    $('#campoDtFinal').val(dataParaExibicao(dtfin));

} else {

    setCookie(dtinit, dtfin)

}


function setCookie(dtinit, dtfin) {

    document.cookie = "dtinit=" + dtinit + "; max-age=" + (60 * 60 * 48);
    document.cookie = "dtfin=" + dtfin + "; max-age=" + (60 * 60 * 48);
    location.reload();

}



btnArmazenar.addEventListener('click', async () => {




    listaItensArmazenar = []

    const checkboxes = document.querySelectorAll('.tabela_geral input[type="checkbox"]:checked:not([disabled])');


    if (checkboxes.length > 0) {
        const selecionados = Array.from(checkboxes);
        const temIniciado = selecionados.some(item => item.dataset.dtarmazenagemini);
        const temNaoIniciado = selecionados.some(item => !item.dataset.dtarmazenagemini);
        if (temIniciado && temNaoIniciado) {
            $.notify({
                icon: 'fa fa-exclamation-circle',
                message: 'Selecione apenas produtos já iniciados ou apenas produtos não iniciados. Não é permitido selecionar os dois juntos.'
            }, {
                type: 'warning',
                placement: { from: 'top', align: 'right' }
            });
            return;
        }
        listaConferente = [];
        document.getElementById('listConferente').innerHTML = '';
        $('#codConferente, #nomeConferente').val('');
        checkboxesArmazenar = Array.from(checkboxes);
        const todosIniciados = checkboxesArmazenar.every(item => item.dataset.dtarmazenagemini);
        document.getElementById('btnIniciar').disabled = todosIniciados || listaConferente.length === 0;
        document.getElementById('btnFinalizar').disabled = !todosIniciados;
        document.getElementById('alertMsg').style.display = 'none';
        $('#codConferente, #btnAddConferente').prop('disabled', todosIniciados);


        const tbodyDestino = document.getElementById('tbodyDestino');
        tbodyDestino.innerHTML = '';


        checkboxes.forEach((checkbox) => {


            const data = [];
            const row = checkbox.closest('tr');
            row.querySelectorAll('td').forEach((cell) => {

                data.push(cell.textContent.replace(/\s+/g, ' ').trim());

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

            // Copia somente as colunas correspondentes aos cabeçalhos do modal.
            // Exclui NUMBONUS, DT_FECHAMENTO e DT_ARMAZENAGEM.
            const colunasModal = [1, 2, 4, 5, 6, 7, 8, 9];
            for (const i of colunasModal) {
                const cell = document.createElement('td');
                cell.textContent = data[i];
                newRow.appendChild(cell);
            }

            tbodyDestino.appendChild(newRow);


        });

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('staticBackdrop'));
        modal.show();

        if (checkboxesArmazenar.some(item => item.dataset.dtarmazenagemini)) {
            btnArmazenar.disabled = true;
            $('#btnFinalizar, #btnAddConferente, #codConferente').prop('disabled', true);
            try {
                const resposta = await $.ajax({
                    type: 'POST', url: 'function.php', contentType: 'application/json', dataType: 'json',
                    data: JSON.stringify({ acao: 'conferentes', listaItensArmazenar })
                });
                if (resposta.erro) throw new Error(resposta.msg);
                resposta.conferentes.forEach(conferente => {
                    const row = document.createElement('tr');
                    [conferente.codigo, conferente.nome].forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = value;
                        row.appendChild(cell);
                    });
                    document.getElementById('listConferente').appendChild(row);
                });
            } catch (erro) {
                $('#alertMsg').show();
                $('#msgAlert').text(erro.message || 'Não foi possível carregar os conferentes.');
            } finally {
                btnArmazenar.disabled = false;
                $('#btnFinalizar').prop('disabled', !todosIniciados);
                $('#codConferente, #btnAddConferente').prop('disabled', todosIniciados);
            }
        }


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
    
        let dtinitAlter = dataParaConsulta($('#campoDtInicio').val());
        let dtfinAlter = dataParaConsulta($('#campoDtFinal').val());
        for (const [id, data] of [['campoDtInicio', dtinitAlter], ['campoDtFinal', dtfinAlter]]) {
            const campo = document.getElementById(id);
            campo.setCustomValidity(data ? '' : 'Informe uma data válida no formato dd/mm/yy.');
            if (!data) { campo.reportValidity(); return; }
        }

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


    const filiais = [
        { codfilial: 1, container: '#tabela_kap', tabela: 'table-kap' },
        { codfilial: 2, container: '#tabela_pen', tabela: 'table-pen' },
        { codfilial: 3, container: '#tabela_for', tabela: 'table-for' }
    ];

    filiais.forEach(async function(filial) {
        try {
            const html = await getProductBonus(filial.codfilial, dtinit, dtfin, filial.container);
            $(filial.container).html(html);
            agrupar(filial.tabela);
        } catch (erro) {
            $(filial.container).html(
                "<div class='alert alert-danger m-3'>Não foi possível carregar esta filial.</div>"
            );
            console.error(`Erro ao carregar a filial ${filial.codfilial}:`, erro);
        }
    });


    async function getProductBonus(codfilial, dtinit, dtfin, divLoja) {


        const jsonData = {
            codfilial,
            dtinit,
            dtfin
        };

        const data = await $.ajax({
            url: "function.php",
            type: 'POST',
            data: JSON.stringify(jsonData),
            beforeSend: function() {

                $(divLoja).html(
                    "<div class='loading-tabela'>" +
                        "<img src='src/_img/loading1.gif' alt='Carregando'>" +
                    "</div>"
                );

            }
        });

        return data;



    }


    
    let statusFiltroAtual = 'todos';

    function agrupar(tableFilial) {
        const table = document.getElementById(tableFilial);
        if (!table) return;

        const numbonusFiltro = document.getElementById('filtroNumbonus').value.trim();
        const grupos = new Map();
        const andamentoBonus = new Map();

        table.querySelectorAll('tbody tr').forEach(row => {
            const cell = row.querySelector('td[data-numbonus]');
            const checkbox = row.querySelector('input[type="checkbox"]');
            if (!cell || !checkbox) return;
            const numbonus = cell.dataset.numbonus;
            if (!andamentoBonus.has(numbonus)) {
                andamentoBonus.set(numbonus, { iniciado: false, pendente: false });
            }
            const andamento = andamentoBonus.get(numbonus);
            andamento.iniciado = andamento.iniciado || Boolean(checkbox.dataset.dtarmazenagemini);
            andamento.pendente = andamento.pendente
                || (!checkbox.dataset.dtarmazenagemfin && !checkbox.dataset.dtarmazenagem);
        });

        table.querySelectorAll('tbody tr').forEach(row => {
            const cell = row.querySelector('td[data-numbonus]');
            if (!cell) return;

            const numbonus = cell.dataset.numbonus;
            const andamento = andamentoBonus.get(numbonus);
            const bonusEmAndamento = andamento && andamento.iniciado && andamento.pendente;
            const correspondeStatus = statusFiltroAtual === 'todos'
                || (statusFiltroAtual === 'iniciados' ? bonusEmAndamento : row.dataset.status === statusFiltroAtual);
            const visivel = (!numbonusFiltro || numbonus === numbonusFiltro)
                && correspondeStatus;

            row.hidden = !visivel;
            cell.removeAttribute('rowspan');
            cell.style.display = 'none';
            if (!visivel) return;

            if (!grupos.has(numbonus)) grupos.set(numbonus, []);
            grupos.get(numbonus).push(cell);
        });

        grupos.forEach(cells => {
            const first = cells[0];
            first.style.display = '';
            first.rowSpan = cells.length;
            first.style.textAlign = 'center';
            first.style.verticalAlign = 'middle';
        });
    }

    function aplicarFiltros() {
        filiais.forEach(filial => agrupar(filial.tabela));
    }

    $('#filtroNumbonus').on('input', aplicarFiltros);
    $('#filtroNumbonus').on('keydown', function(e) {
        if (e.key === 'Enter') e.preventDefault();
    });

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
                        $('#btnAddConferente').focus()


                    }


                }


            }).done(function() {});




        }



    });



    $('#btnAddConferente').click(function() {
        const codigo = $('#codConferente').val().trim();
        const nome = $('#nomeConferente').val().trim();
        if (!codigo || !nome || listaConferente.includes(codigo) || listaConferente.length >= 4) {
            $('#alertMsg').show();
            $('#msgAlert').text('Informe um conferente válido, sem repetição. Máximo de 4 conferentes.');
            return;
        }
        const row = document.createElement('tr');
        [codigo, nome].forEach(value => {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.appendChild(cell);
        });
        document.getElementById('listConferente').appendChild(row);
        listaConferente.push(codigo);
        const todosIniciados = checkboxesArmazenar.every(item => item.dataset.dtarmazenagemini);
        $('#btnIniciar').prop('disabled', gravandoArmazenagem || todosIniciados);
        $('#codConferente, #nomeConferente').val('');
        $('#codConferente').focus();
    });

    let gravandoArmazenagem = false;
    async function gravarArmazenagem(acao) {
        if (gravandoArmazenagem) return;
        if (acao === 'iniciar' && listaConferente.length === 0) {
            $('#alertMsg').show();
            $('#msgAlert').text('Informe um conferente responsável pelo armazenamento.');
            return;
        }
        gravandoArmazenagem = true;
        $('#btnIniciar, #btnFinalizar').prop('disabled', true);
        try {
            const resposta = await $.ajax({
                type: 'POST', url: 'function.php', contentType: 'application/json', dataType: 'json',
                data: JSON.stringify({ acao, listaItensArmazenar, listaConferente })
            });
            if (resposta.erro) throw new Error(resposta.msg);
            $('#alertMsg').show();
            $('#msgAlert').text(resposta.msg);
            if (acao === 'iniciar') {
                checkboxesArmazenar.forEach(item => { item.dataset.dtarmazenagemini = 'iniciado'; });
                $('#codConferente, #btnAddConferente').prop('disabled', true);
            } else {
                location.reload();
            }
        } catch (erro) {
            $('#alertMsg').show();
            $('#msgAlert').text(erro.message || 'Não foi possível gravar. Tente novamente.');
        } finally {
            gravandoArmazenagem = false;
            const todosIniciados = checkboxesArmazenar.every(item => item.dataset.dtarmazenagemini);
            $('#btnIniciar').prop('disabled', todosIniciados || listaConferente.length === 0);
            $('#btnFinalizar').prop('disabled', !todosIniciados);
        }
    }
    $('#btnIniciar').click(() => gravarArmazenagem('iniciar'));
    $('#btnFinalizar').click(() => gravarArmazenagem('finalizar'));

    $('#filtroStatus button').click(function() {
        statusFiltroAtual = $(this).data('status');
        aplicarFiltros();
    });




});
