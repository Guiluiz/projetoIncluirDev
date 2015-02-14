/**
 * Controle do gerenciamento de turmas
 * @returns {undefined}
 */
var controle_turmas = (function() {
    var turma = {
        url_img: '',
        url_ajax_busca_disciplina: '',
        url_ajax_busca_professor: '',
        container: $('.form_incrementa'),
        campo_curso: $('#curso'),
        campo_disciplina: $('#disciplina'),
        campo_professor: $('#professor'),
        campo_data_ini: $('#data_inicio'),
        campo_data_fim: $('#data_fim'),
        btn_incluir_professor: $('#incluir_professor'),
        //btn_cancelar: $('#cancelar'),
        action: '', // 1 - cadastro, 2 alteração, 3 exclusão
        min_date: '',
        max_date: ''
    };

    turma.setValues = function(url_img, url_ajax_busca_disciplina, url_ajax_busca_professor, action, min_date, max_date) {
        turma.url_img = url_img;
        turma.url_ajax_busca_disciplina = url_ajax_busca_disciplina;
        turma.url_ajax_busca_professor = url_ajax_busca_professor;
        turma.action = action;
        turma.min_date = min_date;
        turma.max_date = max_date;
        
        turma.ini();
    };

    turma.buscaProfessores = function() {
        var opcao = turma.campo_disciplina.val();

        if (turma.campo_disciplina.children().length > 0 && opcao != '') {
            $.ajax({
                type: "POST",
                url: turma.url_ajax_busca_professor,
                dataType: "JSON",
                data: {
                    id_disciplina: opcao
                },
                success: function(professores) {
                    var html = "";

                    if (professores instanceof Object) {
                        if (professores.length == 0)
                            exibeMensagem('Não há nenhum professor cadastrado para ministrar essa disciplina.', 'Busca de Professores');

                        else {
                            html += '<option value="">Selecione</option>';
                            for (var i = 0; i < professores.length; i++)
                                html += "<option value='" + professores[i].id_professor + "'>" + professores[i].nome_professor + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Professores');

                    turma.campo_professor.html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            turma.campo_professor.html('');
    };

    turma.ini = function() {
        turma.container.append($('#opcoes_escolhidas').children()).show();
        turma.eventRemoveProfessor();
        // em caso de alteração ou erro de cadastro, os pré requisitos selecionados vem populados em um container escondido

        if (turma.action != 3) {
            turma.iniCalendario();

            turma.campo_curso.change(function() {
                turma.limpaContainer();

                helpers.buscaDisciplinasByCurso(turma.url_ajax_busca_disciplina, $(this), turma.campo_disciplina);
                turma.campo_professor.html('');
            });

            turma.btn_incluir_professor.click(function() {
                turma.addProfessor();
            });

            turma.campo_disciplina.change(function() {
                turma.limpaContainer();

                turma.buscaProfessores();
                turma.campo_professor.html('');
            });
        }
    };

    turma.limpaContainer = function() {
        turma.container.hide().children().remove().hide();
    };

    turma.iniCalendario = function() {
        turma.campo_data_ini.datepicker({
            buttonText: "Clique para selecionar a data inicial",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: turma.url_img,
            onSelect: function(data) { // A data de término do período só pode ser incluída após a escolha da data inicial
                turma.campo_data_fim.datepicker("setDate", null).val('');

                turma.campo_data_fim.datepicker({
                    buttonText: "Clique para selecionar a data inicial",
                    showOn: "button",
                    buttonImageOnly: true,
                    buttonImage: turma.url_img,
                    minDate: data
                });

                turma.campo_data_fim.datepicker('option', 'minDate', data);
                turma.campo_data_fim.datepicker('option', 'maxDate', turma.campo_data_ini.datepicker('option', 'maxDate'));
            }
        });

        if (turma.min_date != undefined && turma.max_date != undefined) {
            turma.campo_data_ini.datepicker('option', 'minDate', turma.min_date);
            turma.campo_data_ini.datepicker('option', 'maxDate', turma.max_date);
        }

        if (turma.campo_data_ini.val().length > 0) { // se já tiver uma data inicial setada, a data final pode ser incluída
            turma.campo_data_fim.datepicker({
                buttonText: "Clique para selecionar a data final",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: turma.url_img,
                minDate: turma.min_date
            });
        }
    };

    turma.addProfessor = function() {
        var option = turma.campo_professor.find('option:selected');
        var id_professor = $(option).val();

        if (turma.campo_professor.children().length > 0 && id_professor != "" && !turma.container.find('tr').hasClass(id_professor)) {
            var html = '';

            if (turma.container.children().length == 0) {
                turma.container.show();
                html = '<tr><th>Professor</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_professor + '"><input type="hidden" name="professores[]" value="' + id_professor + '"/><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            turma.container.append(html);
            turma.eventRemoveProfessor();
        }
        else
            exibeMensagem('Nenhum professor foi selecionado ou ele já foi incluído.', 'Inclusão de Professores');

    };

    turma.eventRemoveProfessor = function() {
        $('.excluir_geral').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });
    };

    return {ini: turma.setValues};
})();

