/**
 * Controle do gerenciamento de disciplinas
 * @returns {undefined}
 */
var controle_disciplinas = (function() {
    var disciplina = {
        campo_pre_requisito: $('#pre_requisito'),
        campo_curso: $('#id_curso'),
        campo_disciplina: $('#id_disciplina'),
        container: $('.form_incrementa'),
        btn_incluir: $('#incluir_pre_requisito'),
        btn_cancelar: $('#cancelar'),
        action: '', // 1 - cadastro, 2 alteração, 3 exclusão
        url_ajax_disciplina: '',
        disciplinas_buscadas: null
    };

    disciplina.setValues = function(url_ajax_disciplina, action) {
        disciplina.action = action;
        disciplina.url_ajax_disciplina = url_ajax_disciplina;
    };

    disciplina.ini = function() {
        disciplina.container.append($('#opcoes_escolhidas').children()).show();
        // em caso de alteração ou erro de cadastro, os pré requisitos selecionados vem populados em um container escondido

        if (disciplina.action != 3) { // se não for exclusão

            disciplina.campo_curso.change(function() {
                disciplina.campo_pre_requisito.html('');
                disciplina.buscaPreRequisitos();
            });

            disciplina.btn_incluir.click(function() {
                disciplina.addPreRequisito();
            });
        }
    };

    disciplina.buscaPreRequisitos = function() {
        var opcao = disciplina.campo_curso.val();

        var parametros_requisicao = {
            id_curso: opcao
        };

        if (action == 2) { // se for alteracao
            parametros_requisicao = {
                id_curso: opcao,
                id_disciplina_exclude: disciplina.campo_disciplina.val()
            };
        }

        if (disciplina.campo_curso.children().length > 0 && opcao != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: parametros_requisicao,
                success: function(resultado) {
                    disciplina.disciplinas_buscadas = resultado;
                    disciplina.printPreRequisitos();
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        else
            disciplina.campo_pre_requisito.html('');
    };

    disciplina.printPreRequisitos = function() {
        var html = "";
        if (disciplina.disciplinas_buscadas instanceof Object) {
            if (disciplina.disciplinas_buscadas.length == 0)
                if (disciplina.action != 2)
                    exibeMensagem('Não há nenhuma disciplina cadastrada para esse curso.', 'Busca de Disciplinas');
                else
                    exibeMensagem('Não há nenhuma disciplina que você possa incluir para esse curso.', 'Busca de Disciplinas');

            else {
                html += '<option value="">Selecione</option>';
                for (var i = 0; i < disciplina.disciplinas_buscadas.length; i++)
                    html += "<option value='" + disciplina.disciplinas_buscadas[i].id_disciplina + "'>" + disciplina.disciplinas_buscadas[i].nome_disciplina + "</option>";
            }
        }
        else
            exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Disciplinas');

        disciplina.campo_pre_requisito.html(html);
    };

    disciplina.getIdPreRequisito = function() {
        return disciplina.campo_pre_requisito.find('option:selected').val();
    };

    disciplina.getOptionPreRequisito = function() {
        return disciplina.campo_pre_requisito.find('option:selected');
    }

    disciplina.addPreRequisito = function() {
        var option = disciplina.getOptionPreRequisito();
        var id_pre_requisito = $(option).val();

        if (id_pre_requisito != "" && disciplina.campo_pre_requisito.children().length > 0 && !disciplina.find('tr').hasClass(id_pre_requisito)) {
            var html = '';

            if (disciplina.container.children().length == 0) {
                disciplina.container.show();
                html = '<tr><th>Disciplina(Pré-Requisito)</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_pre_requisito + '"><input type="hidden" name="pre_requisitos[]" value="' + id_pre_requisito + '"/><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            
            $(container).append(html);
        }
        else
            exibeMensagem('Nenhuma disciplina foi selecionada ou ela já foi incluída.', 'Inclusão de Pré-Requisitos');
    };

    disciplina.eventRemovePreRequisito = function() {
        $('.excluir_geral').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });

    };

    return {ini: disciplina.setValues};
})();