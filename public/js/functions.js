/**
 * Contendo métodos auxiliares que são usados com maior frequencia
 * @returns {undefined}
 */

var helpers = (function() {

    var auxiliares = {};

    auxiliares.buscaDisciplinasByCurso = function(url, campo_curso, campo_disciplina) {
        var opcao = $(campo_curso).val();

        if (opcao != undefined && opcao != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: {
                    id_curso: opcao
                },
                success: function(disciplinas) {
                    var html = "";

                    if (disciplinas instanceof Object) {
                        if (disciplinas.length == 0)
                            exibeMensagem('Não há nenhuma disciplina cadastrada para esse curso.', 'Busca de Disciplinas');
                        else {
                            html += '<option value="">Selecione</option>';
                            for (var i = 0; i < disciplinas.length; i++)
                                html += "<option value='" + disciplinas[i].id_disciplina + "'>" + disciplinas[i].nome_disciplina + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Disciplinas');

                    $(campo_disciplina).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        else
            $(campo_disciplina).html('');
    };

    auxiliares.buscaTurmasByDisciplina = function(url, campo_disciplina, campo_turma, periodo, opcao_default) {
        var opcao = $(campo_disciplina).val();
        var parametros_requisicao = null;
        var mensagem = "";

        if (periodo != undefined) {
            parametros_requisicao = {
                id_disciplina: null,
                id_periodo: periodo
            };
            mensagem = 'Não há nenhuma turma cadastrada para o período escolhido.';
        }

        else if (opcao != undefined && opcao.length > 0) {
            parametros_requisicao = {
                id_disciplina: opcao
            };
            mensagem = 'Não há nenhuma turma cadastrada para essa disciplina.';
        }

        if (parametros_requisicao != null) {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: parametros_requisicao,
                success: function(turmas) {
                    var html = "";

                    if (turmas != null) {
                        if (turmas.length == 0)
                            exibeMensagem(mensagem, 'Busca de Turmas');

                        else {
                            if (opcao_default)
                                html += '<option value="">Selecione</option>';

                            for (var i = 0; i < turmas.length; i++)
                                html += "<option hora_inicio='" + turmas[i].horario_inicio + "' hora_fim='" + turmas[i].horario_fim + "' data_inicio='" + turmas[i].data_inicio + "' data_fim='" + turmas[i].data_fim + "' value='" + turmas[i].id_turma + "'>" + turmas[i].nome_turma  + ' | ' + turmas[i].horario_inicio + ' - ' + turmas[i].horario_fim + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Turmas');

                    $(campo_turma).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            $(campo_turma).html('');
    };

    auxiliares.desabilitaEscondeCampos = function(elemento_container) {
        $(elemento_container).hide().find('input, select').attr('disabled', 'disabled');
    };

    auxiliares.removeOpcoesEscolhidas = function(container) {
        $(container).html('');
    };

    auxiliares.mostraEscondeCheck = function(controle, elemento, is_container) {
        if ($(controle).prop('checked')) {
            if (!is_container)
                $(elemento).removeAttr('disabled').fadeIn('fast');
            else
                $(elemento).fadeIn('fast').find('input, select').removeAttr('disabled').fadeIn('fast');
        }
        else {
            if (!is_container)
                $(elemento).fadeOut('fast').attr('disabled', 'disabled').val('');
            else
                $(elemento).fadeOut('fast').find('input, select').attr('disabled', 'disabled').val('');
        }
    };

    auxiliares.mostraEscondeRadioSelect = function(controle, elemento, value, is_container) {
        if ($(controle).val() == value) {
            if (!is_container)
                $(elemento).removeAttr('disabled').fadeIn('fast');
            else
                $(elemento).fadeIn('fast').find('input, select').removeAttr('disabled').fadeIn('fast');
        }
        else {
            if (!is_container)
                $(elemento).fadeOut('fast').attr('disabled', 'disabled').val('');
            else
                $(elemento).fadeOut('fast').find('input, select').attr('disabled', 'disabled').val('');
        }
    };

    auxiliares.retira_acentos = function(palavra) {
        var com_acento = 'áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÖÔÚÙÛÜÇ';
        var sem_acento = 'aaaaaeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUC';
        var invalids = '\\\'\"#¨~^´`,/[]{}+=()@!$%*|&.-';

        var nova = '';

        for (var i = 0; i < palavra.length; i++) {
            if (com_acento.search(palavra.substr(i, 1)) >= 0)
                nova += sem_acento.substr(com_acento.search(palavra.substr(i, 1)), 1);

            else if (invalids.search(palavra.substr(i, 1)) < 0)
                nova += palavra.substr(i, 1);
        }
        return nova;
    };

    auxiliares.trim = function(vlr) {
        while (vlr.indexOf(" ") != - 1)
            vlr = vlr.replace(' - ', '_').replace(" ", "_");

        return vlr;
    };

    auxiliares.validaNumero = function(valor) {
        var aux = parseFloat(valor);
        if (isNaN(aux) || aux < 0)
            return false;
        return true;
    };

    auxiliares.parseNumero = function(valor) {
        if (valor.length == 0)
            return 0;

        valor = valor.replace(',', '.');

        if (auxiliares.validaNumero(valor))
            return parseFloat(valor);

        return -1;
    };

    auxiliares.parseDate = function(str_date) {
        if (str_date != undefined && str_date != "") {
            var split_data = str_date.toString().split('/');

            if (split_data != null && split_data.length == 3) {
                var data = new Date(split_data[2] + '/' + split_data[1] + '/' + split_data[0] + " 00:00:00");

                if (!isNaN(Date.parse(data))) {
                    if (data.getDate() == split_data[0] && data.getMonth() + 1 == split_data[1] && data.getFullYear() == split_data[2])
                        return data;
                }
            }
        }
        return null;
    };

    return auxiliares;
})();