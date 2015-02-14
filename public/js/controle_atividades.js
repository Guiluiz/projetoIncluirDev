/**
 * Controle do gerenciamento de atividades
 * @returns {undefined}
 */

var controle_atividades = (function() {

    var atividade = {
        campo_curso: $('#curso'),
        campo_disciplina: $('#disciplina'),
        campo_turma: $('#turma'),
        campo_data: $('#data_funcionamento'),
        container_atividades: $('#atividades-turma'),
        url_ajax_disciplina: '',
        url_ajax_turma: '',
        url_ajax_atividade: '',
        url_img: '',
        data_inicio: '',
        data_fim: '',
        datas_calendario_academico: '',
        action: ''// 1 - cadastro, 2 alteração, 3 exclusão
    };

    atividade.setValues = function(url_ajax_disciplina, url_ajax_turma, url_ajax_atividade, url_img, data_inicio, data_fim, datas, action) {
        atividade.url_ajax_atividade = url_ajax_atividade;
        atividade.url_ajax_disciplina = url_ajax_disciplina;
        atividade.url_ajax_turma = url_ajax_turma;
        atividade.url_img = url_img;
        atividade.data_inicio = data_inicio;
        atividade.data_fim = data_fim;
        atividade.datas_calendario_academico = datas;
        atividade.action = action;
        
        atividade.ini();
    };

    atividade.ini = function() {
        atividade.campo_data.datepicker({
            buttonText: "Clique para selecionar uma data",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: atividade.url_img,
            minDate: atividade.data_inicio,
            maxDate: atividade.data_fim,
            beforeShowDay: function(calendar_date) {
                var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);

                if (atividade.datas_calendario_academico[aux] != undefined)
                    return [true, ''];
                return [false, ''];
            }
        });

        atividade.campo_curso.change(function() {
            atividade.campo_turma.html('');
            atividade.container_atividades.html('');
            helpers.buscaDisciplinasByCurso(atividade.url_ajax_disciplina, $(this), atividade.campo_disciplina);

        });

        atividade.campo_disciplina.change(function() {
            atividade.campo_turma.html('');
            atividade.container_atividades.html('');
            helpers.buscaTurmasByDisciplina(atividade.url_ajax_turma, $(this), atividade.campo_turma, null, true);
        });

        atividade.getIdTurma = function() {
            return atividade.campo_turma.find('option:selected').val();
        };

        atividade.campo_turma.change(function() {
            var id_turma = $(this).find('option:selected').val();

            if (id_turma == undefined || id_turma == '') {
                exibeMensagem('Você deve escolher uma turma.', 'Atividades');
                atividade.container_atividades.html('');
            }
            else
                atividade.buscaAtividadesTurma();
        });

        atividade.buscaAtividadesTurma = function() {
            $.ajax({
                type: "POST",
                url: atividade.url_ajax_atividade,
                dataType: "JSON",
                data: {
                    id_turma: atividade.getIdTurma()
                },
                success: function(atividades) {
                    var html = '';

                    if (atividades instanceof Object) {
                        if (atividades.length > 0) {
                            var html = '<h2>Atividades da Turma</h2><table class="form_incrementa stripped"><tr><th>Nome</th><th>Data</th><th>Valor</th></tr>';

                            for (var key in atividades)
                                html += '<tr><td>' + atividades[key].nome + '</td><td>' + atividades[key].data + '</td><td>' + atividades[key].valor + '</td></tr>';

                            html += '</table>';
                        }

                        else
                            exibeMensagem('Nenhuma atividade foi encontrada para a turma escolhida', 'Atividade');
                    }
                    else
                        exibeMensagem('Houve problemas ao buscar a atividade', 'Atividade');

                    $(container_atividades).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        };
    };

    return {ini: atividade.setValues};
})();