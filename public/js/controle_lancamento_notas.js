/**
 * Controle do gerenciamento de notas
 * @returns {undefined}
 */

var controle_notas = (function() {
    var notas = {
        campo_curso: $('#curso'),
        campo_disciplina: $('#disciplina'),
        campo_turma: $('#turma'),
        container_atividade: $('#container_atividade'), // contém todas as informações da atividade que terá as notas lançadas
        container_alunos: $('#notas_alunos'),
        form: $('form'),
        confirmado: false,
        url_ajax_aluno: '',
        url_ajax_disciplina: '',
        url_ajax_turma: '',
        url_ajax_atividade: '',
        data_atual: '',
        alunos: null
    };

    notas.setValues = function(url_ajax_aluno, url_ajax_disciplina, url_ajax_turma, data_atual, url_ajax_atividade) {
        notas.url_ajax_aluno = url_ajax_aluno;
        notas.url_ajax_disciplina = url_ajax_disciplina;
        notas.url_ajax_turma = url_ajax_turma;
        notas.data_atual = data_atual;
        notas.url_ajax_atividade = url_ajax_atividade;

        notas.ini();
    };

    notas.ini = function() {
        notas.campo_curso.val('');

        notas.campo_curso.change(function() {
            helpers.buscaDisciplinasByCurso(notas.url_ajax_disciplina, $(this), notas.campo_disciplina);
            notas.campo_turma.html('');
            notas.container_alunos.html('');
            notas.container_atividade.find('select').html('');
            notas.container_atividade.hide();
        });

        notas.campo_disciplina.change(function() {
            helpers.buscaTurmasByDisciplina(notas.url_ajax_turma, $(this), notas.campo_turma, null, true);
            notas.campo_turma.html('');
            notas.container_alunos.html('');
            notas.container_atividade.find('select').html('');
            notas.container_atividade.hide();
        });

        notas.campo_turma.change(function() {
            var id_turma = notas.getIdTurma()

            if (id_turma == undefined || id_turma == '') {
                exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Notas');
                notas.container_alunos.html('');
                notas.container_atividade.find('select').html('');
                notas.container_atividade.hide();
            }
            else
                notas.getAtividadesTurma();
        });

        notas.form.submit(function(event) {
            if ($('input[id^="aluno_"]').length == 0) {
                exibeMensagem('Você deve escolher a ativadade e fazer os lançamentos', 'Lançamento de notas');
                event.preventDefault();
                return;
            }

            if (notas.confirmado == false) {
                notas.printConfirmacao();
                event.preventDefault();
            }
        });

    };

    notas.getIdTurma = function() {
        return notas.campo_turma.find('option:selected').val();
    };

    notas.getAtividadesTurma = function() {
        $.ajax({
            type: "POST",
            url: notas.url_ajax_atividade,
            dataType: "JSON",
            data: {
                id_turma: notas.getIdTurma(),
                data: notas.data_atual
            },
            success: function(atividades) {
                var html = '';

                if (atividades instanceof Object) {
                    if (atividades.length > 0) {
                        var html = '<option value="">Selecione</option>';

                        for (var key in atividades)
                            html += '<option value = "' + atividades[key].id + '" valor="' + atividades[key].valor + '">' + atividades[key].nome + '</option>';

                        notas.container_atividade.find('select').html(html);
                        notas.getAlunosTurmaNotas();

                        return;
                    }
                }
                notas.container_alunos.html('');
                notas.container_atividade.find('select').html('');
                notas.container_atividade.hide();
                exibeMensagem('Nenhuma atividade foi encontrada na turma indicada.', 'Lançamento de Notas');
            },
            error: function(error) {
                console.log(error);
            }
        });
    };

    notas.getAlunosTurmaNotas = function() {
        $.ajax({
            type: "POST",
            url: notas.url_ajax_aluno,
            dataType: "JSON",
            data: {
                id_turma: notas.getIdTurma()
            },
            beforeSend: function() {
                jQuery('#mensagem-ajax').dialog({
                    dialogClass: "no-close",
                    closeOnEscape: false,
                    modal: true,
                    title: 'Busca de Alunos'
                });
            },
            complete: function() {
                jQuery('#mensagem-ajax').dialog('destroy');
            },
            success: function(alunos) {
                notas.alunos = alunos;

                if (notas.alunos instanceof Object)
                    notas.printAlunos();

                else {
                    notas.container_atividade.find('select').html('');
                    notas.printMsg("Não há nenhum aluno cadastrado na turma selecionada");
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    };

    notas.printAlunos = function() {
        var html = '';

        html += "<div id='title-notas' class='obs'>Escolha uma atividade para fazer o lançamento de nota. Campos vazios serão considerados como nota 0</div> <div id='valor_atividade' class='obs'></div>";
        html += '<table id="alunos_turma_notas" class="form_incrementa stripped"><tr><th>Aluno</th><th>Nota</th>';

        for (var key in notas.alunos)
            html += '<tr><td>' + notas.alunos[key].nome_aluno + '</td><td id="aluno_nota_' + notas.alunos[key].id_aluno + '"> - </td></tr>';

        html += '</table>';

        notas.container_alunos.html(html);
        notas.printCamposNota();
    };

    notas.printMsg = function(msg) {
        notas.container_alunos.html(msg);
    };

    notas.printCamposNota = function() {
        notas.container_atividade.show();

        notas.container_atividade.find('select').change(function() { // exibe os campos para preenchimento de nota c/ as notas já preenchidas(se houver)
            if ($(this).find('option:selected').val().length > 0) {
                var notas_alunos = $('#alunos_turma_notas').find('td[id*="aluno_nota"]');
                var opcao = $(this).find('option:selected');
                var max = parseFloat($(opcao).attr('valor'));
                var atividade = opcao.val();

                $('#valor_atividade').html('<strong>A atividade vale ' + max + ' pontos</strong>');

                $(notas_alunos).each(function() {
                    var aluno = $(this).attr('id').replace('aluno_nota_', ''); // pega o id do aluno
                    var nota_lancada = "";

                    for (var key in notas.alunos) {
                        if (aluno == notas.alunos[key].id_aluno) { // verifica se já há nota lançada para a o aluno na aatividade solicitada
                            for (var key_notas in notas.alunos[key].notas) {
                                for (var key_nota in notas.alunos[key].notas[key_notas]) {
                                    if (atividade == notas.alunos[key].notas[key_notas][key_nota].atividade) {
                                        nota_lancada = notas.alunos[key].notas[key_notas][key_nota].valor_nota;
                                        break;
                                    }
                                }

                                if (nota_lancada)
                                    break;
                            }
                            if (nota_lancada)
                                break;
                        }
                    }
                    $(this).html('<input type="text" value="' + nota_lancada + '" name="aluno_' + aluno + '" id="aluno_' + aluno + '"/><span class="msg-error"></span>');

                    $(this).children().blur(function(event) { // valida os valores inseridos nos campos
                        var val = ($(this).val().length == 0) ? 0 : parseFloat($(this).val());

                        if (isNaN(val) || isNaN(max) || val > max || val < 0)
                            $(this).val('').focus().next().html('<strong>Valor inválido</strong>');
                        else
                            $(this).val(val).next().html('');

                        event.stopPropagation();
                        return false;

                    });
                });
            }

            else
                notas.printAlunos();
        });
    };

    notas.printConfirmacao = function() {
        var clone = notas.container_alunos.clone();

        clone.find('#title-notas').remove();

        clone.find('tr').each(function() {
            var ultima_coluna = $(this).children().last();
            var val = ultima_coluna.find('input').val();

            if (val != undefined) {
                if (isNaN(parseFloat(val)))
                    val = 0;

                ultima_coluna.html(val);
            }

        });

        $('body').append('<div style="display:none" id="confirm">' + clone.html() + '</div>');

        $("#confirm").dialog({
            resizable: false,
            modal: true,
            width: 650,
            title: 'Confirmação de lançamento',
            buttons: {
                'Confirmar o lançamento': function() {
                    notas.confirmado = true;
                    notas.form.submit();
                },
                Cancelar: function() {
                    notas.confirmado = false;
                    $(this).dialog('destroy');
                    $('#confirm').remove();
                }
            }
        });
    };


    return {ini: notas.setValues};
})();