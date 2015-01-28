var controle_frequencia_aluno = (function() {
    var frequencia = {
        curso: $('#curso'),
        disciplina: $('#disciplina'),
        turma: $('#turma'),
        periodo: $('#periodo'),
        data: $('#data'),
        container: $('#calendario_frequencia'),
        container_frequencias: $('#frequencia'),
        url_ajax_aluno: '',
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
//            controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), 1);

            frequencia.turma.html('');
            frequencia.container_frequencias.html('');
            frequencia.container.datepicker('destroy');
        });

        frequencia.disciplina.change(function() {
            frequencia.turma.html('');
//            controle.buscaTurmas(url_ajax_turma, $(this), $(turma));

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
                frequencia.getAlunosNotas();
        });
    };

    frequencia.getAlunosNotas = function() {
        if (!(frequencia.data_atual instanceof Date))
            frequencia.data_atual = controle.parseDate(frequencia.data_atual);

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
                    frequencia.min_date = controle.parseDate(alunos['turma']['data_inicio']);
                    frequencia.max_date = controle.parseDate(alunos['turma']['data_termino']);

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

        html += '<table id="alunos_turma_frequencia" class="form_incrementa stripped">\n\
                    <tr><th>Aluno</th><th>Média de Frequência(%)</th><th>Ausente?</th>';

        for (var key in frequencia.alunos)
            html += '<tr><td>' + frequencia.alunos[key].nome_aluno + '</td>\n\\n\
                <td>' + frequencia.alunos[key].media_frequencia + '</td>\n\
                <td id="' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno + '"> - </td></tr>';

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
                            $(container).find('#' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno).
                                    html('<label><input type="checkbox" class="check_frequencia" checked="checked" name="aluno_' + frequencia.alunos[key].id_aluno + '" /></label>\n\
                                            <div class="observacao-frequencia">\n\
                                                <label for="observacao_' + frequencia.alunos[key].id_aluno + '">Observação\n\
                                                </label><input type="text" name="observacao_' + frequencia.alunos[key].id_aluno + '" value="' + frequencia.alunos[key].faltas[turma_faltas][falta].observacao + '"/></div>'
                                            ).find('.observacao-frequencia').show() // css default o mantém escondido;
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
                $(container).find('#' + frequencia.nome_campo + '_' + frequencia.alunos[key].id_aluno)
                        .html('<label>\n\
                                <input type="checkbox" name="aluno_' + frequencia.alunos[key].id_aluno + '" class="check_frequencia" />\n\
                            </label>\n\
                            <div class="observacao-frequencia"><label for="observacao_' + frequencia.alunos[key].id_aluno + '">Observação</label>\n\
                                <input type="text" name="observacao_' + frequencia.alunos[key].id_aluno + '" value=""/>\n\
                            </div>'
                                );
        }

        // evento de clique para mostrar/esconder as observações de faltas
        $('.check_frequencia').click(function() {
            if ($(this).prop('checked'))
                $(this).parents('td').find('.observacao-frequencia').fadeIn('fast').find('input').removeAttr('disabled');
            else
                $(this).parents('td').find('.observacao-frequencia').fadeOut('fast').find('input').attr('disabled', 'disabled');
        });
    }

    frequencia.printMensagem = function(msg) {
        frequencia.container_frequencias.html(msg);
    }

    frequencia.getIdTurma = function() {
        return frequencia.turma.find('option:selected').val();
    };

    return {ini: frequencia.setValues()};
})();