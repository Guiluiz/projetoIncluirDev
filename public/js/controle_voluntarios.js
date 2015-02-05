/**
 * Controle do gerenciamento de voluntários
 * @returns {undefined}
 */

var controle_voluntarios = (function() {

    var voluntario = {
        checkboxs_atividades: $('input[name="atividades[]"]'),
        checkboxs_disponibilidade: $('input[name="disponibilidade[]"]'),
        campo_curso: $('#curso_voluntario'),
        campo_disciplina: $('#disciplina_voluntario'),
        btn_incluir: $('#incluir_disciplina'),
        container: $('.form_incrementa'),
        campos_data: $('#data_nascimento, #data_inicio, #data_desligamento'),
        campo_nome: $('#nome'),
        url_ajax_verifica_voluntario: '',
        url_ajax_busca_disciplina: '',
        status_desligado: '',
        atividade_aula: '',
        url_img: '',
        action: '' // 1 - cadastro, 2 alteração, 3 exclusão
    };

    voluntario.setValues = function(url_verifica_voluntario, url_ajax, url_img, status, atividade, action) {
        voluntario.url_ajax_busca_disciplina = url_ajax;
        voluntario.url_ajax_verifica_voluntario = url_verifica_voluntario;
        voluntario.url_img = url_img;
        voluntario.status_desligado = status;
        voluntario.atividade_aula = atividade;
        voluntario.action = action;
        
        voluntario.ini();
    };

    voluntario.ini = function() {
        voluntario.container.append($('#opcoes_escolhidas').children()).show();
        voluntario.eventRemoveDisciplina();
        // em caso de alteração ou erro de cadastro, os pré requisitos selecionados vem populados em um container escondido

        if (voluntario.action != 3) { // se não for exclusão
            voluntario.campo_curso.change(function() {
                voluntario.campo_disciplina.html('');
                helpers.buscaDisciplinasByCurso(voluntario.url_ajax_busca_disciplina, $(this), voluntario.campo_disciplina);
            });

            voluntario.btn_incluir.click(function() {
                voluntario.addDisciplina();
            });

            voluntario.checkboxs_atividades.each(function() {
                helpers.mostraEscondeCheck($(this), $('.' + $(this).val()), true);
            });

            voluntario.campos_data.datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: voluntario.url_img
            });

            voluntario.campo_nome.autocomplete({
                source: voluntario.url_ajax_verifica_voluntario,
                minLength: 1
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                var $a = $("<a href='" + item.url + "'></a>").text(item.label);
                return $("<li></li>").append($a).append(item.desc).appendTo(ul);
            };

            voluntario.checkboxs_atividades.click(function() {
                helpers.mostraEscondeCheck($(this), $('.' + $(this).val()), true);

                if (!$(this).prop('checked') && $(this).val() == voluntario.atividade_aula) {
                    voluntario.limpaContainer();
                    voluntario.campo_disciplina.html('');
                }
            });
        }
        else {
            voluntario.checkboxs_atividades.attr('disabled', 'disabled');
            voluntario.checkboxs_disponibilidade.attr('disabled', 'disabled');
        }
    };

    voluntario.addDisciplina = function() {
        var option = voluntario.campo_disciplina.find('option:selected');
        var id_disciplina = $(option).val();

        if (voluntario.campo_disciplina.children().length > 0 && id_disciplina != "" && !voluntario.container.find('tr').hasClass(id_disciplina)) {
            var html = '';

            if (voluntario.container.children().length == 0) {
                voluntario.container.show();
                html = '<tr><th>Curso</th><th>Disciplina</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_disciplina + '"><input type="hidden" name="disciplinas[]" value="' + id_disciplina + '"/><td>' + voluntario.campo_curso.find('option:selected').html() + '</td><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            voluntario.container.append(html);
            voluntario.eventRemoveDisciplina();
        }
        else
            exibeMensagem('Nenhuma disciplina foi selecionada ou ela já foi incluída.', 'Inclusão de Disciplina');
    };

    voluntario.limpaContainer = function() {
        voluntario.container.hide().children().remove().hide();
    };

    voluntario.eventRemoveDisciplina = function() {
        $('.excluir_geral').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });
    };
    
    return {ini: voluntario.setValues};
})();