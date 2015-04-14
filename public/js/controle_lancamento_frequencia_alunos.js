/**
 * Controle do lançamento de frequência dos alunos
 * @type Function|_L5.Anonym$5
 */

var controle_frequencia_aluno = (function() {
    var frequencia = {
        curso: $('#curso'),
        disciplina: $('#disciplina'),
        turma: $('#turma'),
        periodo: $('#periodo'),
        data: $('#data'),
        container: $('#calendario_frequencia'),
        container_frequencias: $('#frequencia'),
        form: $('form'),
        confirmado: false,
        url_ajax_aluno: '',
        url_ajax_disciplina: '',
        url_ajax_turma: '',
        datas_calendario_academico: '',
        data_atual: '',
        alunos: null,
        min_date: '',
        max_date: '',
        nome_campo: 'campo_frequencia'
    };

    frequencia.setValues = function(url_ajax_aluno, url_ajax_disciplina, url_ajax_turma, datas_calendario_academico, data_atual) {
        frequencia.url_ajax_aluno = url_ajax_aluno;
        frequencia.url_ajax_disciplina = url_ajax_disciplina;
        frequencia.url_ajax_turma = url_ajax_turma;
        frequencia.datas_calendario_academico = datas_calendario_academico;
        frequencia.data_atual = data_atual;

        frequencia.ini();
    };

    frequencia.ini = function() {
        frequencia.curso.val('');

        frequencia.curso.change(function() {
            helpers.buscaDisciplinasByCurso(frequencia.url_ajax_disciplina, $(this), frequencia.disciplina);

            frequencia.turma.html('');
            frequencia.container_frequencias.html('');
            frequencia.container.datepicker('destroy');
        });

        frequencia.disciplina.change(function() {
            frequencia.turma.html('');
            helpers.buscaTurmasByDisciplina(frequencia.url_ajax_turma, $(this), frequencia.turma, null, true);

            frequencia.container_frequencias.html('');
            frequencia.container.datepicker('destroy');
        });

        frequencia.turma.change(function() {
            var id_turma = frequencia.getIdTurma();

            if (id_turma == undefined || id_turma == '') {
                exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Frequência');

                frequencia.container_frequencias.html('');
                frequencia.container.datepicker('destroy');
            }
            else
                frequencia.getAlunosFrequencias();
        });

        frequencia.form.submit(function(event) {
            if ($('input[class="check_frequencia"]').length == 0) {
                exibeMensagem('Você deve escolher a data e fazer os lançamentos', 'Lançamento de Frequência');
                event.preventDefault();
                return;
            }


            if (frequencia.confirmado == false) {
                frequencia.printConfirmacao();
                event.preventDefault();
            }
        });
    };

    frequencia.getAlunosFrequencias = function() {
        if (!(frequencia.data_atual instanceof Date))
            frequencia.data_atual = helpers.parseDate(frequencia.data_atual);

        $.ajax({
            type: "POST",
            url: frequencia.url_ajax_aluno,
            dataType: "JSON",
            data: {
                id_turma: frequencia.getIdTurma()
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
                frequencia.container.datepicker('destroy');

                if (alunos instanceof Object) {
                    frequencia.min_date = helpers.parseDate(alunos['turma']['data_inicio']);
                    frequencia.max_date = helpers.parseDate(alunos['turma']['data_termino']);

                    delete alunos['turma'];

                    frequencia.alunos = alunos;
                    frequencia.printAlunos();

                    frequencia.container.datepicker({
                        minDate: frequencia.min_date,
                        maxDate: frequencia.max_date, beforeShowDay: function(calendar_date) {
                            var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);

                            if (frequencia.datas_calendario_academico[aux] != undefined && +frequencia.data_atual >= +calendar_date)
                                return [true, ''];

                            return [false, ''];
                        },
                        onSelect: function(data_escolhida) {
                            var id_turma = frequencia.getIdTurma();

                            if (id_turma == undefined || id_turma == '') {
                                exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Frequência');
                                frequencia.container_frequencias.html('');
                            }
                            else
                                frequencia.printCampos(data_escolhida);
                        }
                    });
                }
                else
                    frequencia.printMensagem('Não há nenhum aluno cadastrado na turma indicada');
            },
            error: function(error) {
                console.log(error);
            }
        });
    };

    frequencia.printAlunos = function() {
        var html = "<div id='title-frequencia' class='obs'>Escolha um dia para fazer / alterar o lançamento de frequencia</div>";

        html += '<table id="alunos_turma_frequencia" class="form_incrementa stripped"><tr><th>Aluno</th><th>Média de Frequência(%)</th><th>Ausente?</th>';

        for (var key in frequencia.alunos)
            html += '<tr><td>' + frequencia.alunos[key].nome_aluno + '</td><td>' + frequencia.alunos[key].media_frequencia + '</td><td id="' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno + '"> - </td></tr>';

        html += '</table>';

        frequencia.container_frequencias.html(html);
    };

    frequencia.printCampos = function(data_escolhida) {
        var id_turma = frequencia.getIdTurma();

        frequencia.data.val(data_escolhida);

        $('#title-frequencia').removeClass('obs').html('<h2>Lançamento do Dia: ' + data_escolhida + '</h2>');

        for (var key in frequencia.alunos) {
            var achou = false;

            for (var turma_faltas in frequencia.alunos[key].faltas) {
                if (turma_faltas == id_turma) {
                    // se as faltas da turma forem encontradas
                    for (var falta in frequencia.alunos[key].faltas[turma_faltas]) {
                        //se forem as faltas do dia indicado
                        if (frequencia.alunos[key].faltas[turma_faltas][falta].data_funcionamento == data_escolhida) {
                            // inclui os campos já preenchidos, já que a falta foi encontrada
                            frequencia.container_frequencias.find('#' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno)
                                    .html('<label><input type="hidden" value="nao" disabled="disabled" name="aluno_' + frequencia.alunos[key].id_aluno + '"/><input type="checkbox" class="check_frequencia" checked="checked" name="aluno_' + frequencia.alunos[key].id_aluno + '" /></label><div class="observacao-frequencia"><label for="observacao_' + frequencia.alunos[key].id_aluno + '">Observação</label><input type="text" name="observacao_' + frequencia.alunos[key].id_aluno + '" value="' + frequencia.alunos[key].faltas[turma_faltas][falta].observacao + '"/></div>')
                                    .find('.observacao-frequencia').show() // css default o mantém escondido;
                            achou = true;
                            break;
                        }
                    }
                }
                if (achou)
                    break;
            }
            // se não achou nenhuma falta, exibe o campo normal
            if (!achou)
                frequencia.container_frequencias.find('#' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno)
                        .html('<label><input type="hidden" value="nao" name="aluno_' + frequencia.alunos[key].id_aluno + '"/><input type="checkbox" name="aluno_' + frequencia.alunos[key].id_aluno + '" class="check_frequencia" /></label><div class="observacao-frequencia"><label for="observacao_' + frequencia.alunos[key].id_aluno + '">Observação</label><input type="text" name="observacao_' + frequencia.alunos[key].id_aluno + '" value=""/></div>');
        }

        // evento de clique para mostrar/esconder as observações de faltas
        $('.check_frequencia').click(function() {
            if ($(this).prop('checked')) {
                $(this).parents('td').find('.observacao-frequencia').fadeIn('fast').find('input').removeAttr('disabled');
                $(this).prev().attr('disabled', 'disabled');
            }
            else {
                $(this).parents('td').find('.observacao-frequencia').fadeOut('fast').find('input').attr('disabled', 'disabled');
                $(this).prev().removeAttr('disabled');
            }

            frequencia.printContador();
        });

        frequencia.printContador();
    }

    frequencia.printContador = function() {
        $('div').remove('#inf_presentes');
        $('#title-frequencia').append('<div id="inf_presentes" class="obs">Ausentes: <b>' + $('.check_frequencia:checked').length + '</b> / Presentes: <b>' + $('.check_frequencia:not(:checked)').length + '</b></div>');
    };

    frequencia.printMensagem = function(msg) {
        frequencia.container_frequencias.html(msg);
    };

    frequencia.printConfirmacao = function() {
        var clone = frequencia.container_frequencias.clone();

        clone.find('tr').each(function() {
            var ultima_coluna = $(this).children().last();

            if (ultima_coluna.find('input[type="checkbox"]').length > 0) {
                if (ultima_coluna.find('input[type="checkbox"]').prop('checked'))
                    ultima_coluna.html('Ausente');
                else
                    ultima_coluna.html('Presente');
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
                    frequencia.confirmado = true;
                    frequencia.form.submit();
                },
                Cancelar: function() {
                    frequencia.confirmado = false;
                    $(this).dialog('destroy');
                    $('#confirm').remove();
                }
            }
        });
    };

    frequencia.getIdTurma = function() {
        return frequencia.turma.find('option:selected').val();
    };

    return {ini: frequencia.setValues};
})();
