var action_cadastro = 1, action_alteracao = 2, action_exclusao = 3, trava_aluno = false;
var qt_min_alimentos_liberacao, valor_min_liberacao;
var trava_busca_alunos = false;
var busca_alunos, alunos_notas, voluntarios_frequencia;

var controle = {
    desabilitaEscondeCampos: function(elemento_container) {
        $(elemento_container).hide().find('input, select').attr('disabled', 'disabled');
    },
    iniOpcoesEscolhidas: function(container, opcoes) {
        if ($(opcoes).length == 1) {
            $(container).append($(opcoes).children()).show();
            controle.eventOpcaoExcluir();
        }
    },
    iniAlunoTurmas: function(container, opcoes, pagamento_turma) {
        if ($(opcoes).length == 1) {
            $(container).append($(opcoes).children()).show();
            controle.eventExcluirTurmaAluno(container, pagamento_turma);
        }
    },
    iniAlimentos: function(container, opcoes, pagamento_turma) {
        if ($(opcoes).length == 1) {
            $(container).append($(opcoes).children()).show();
            var aux = controle.retira_acentos(controle.trim($(pagamento_turma).find('option:selected').html())).toLowerCase();
            console.log(aux);
            $('#alimentos_' + aux).show();
            controle.eventExcluirAlimento($('#alimentos_' + aux));
        }
    },
    removeOpcoesEscolhidas: function(container) {
        $(container).html('');
    },
    iniDisciplinas: function(url_ajax, action, is_disciplina) {
        var container = $('.form_incrementa'),
                curso = (is_disciplina) ? $('#curso_voluntario') : $('#id_curso'),
                disciplina = (is_disciplina) ? $('#disciplina_voluntario') : $('#pre_requisito'),
                btn_incluir = (is_disciplina) ? $('#incluir_disciplina') : $('#incluir_pre_requisito'), btn_cancelar = $('#cancelar');

        controle.iniOpcoesEscolhidas(container, $('#opcoes_escolhidas'));

        if (action != action_exclusao) {
            $(curso).change(function() {
                controle.buscaDisciplinas(url_ajax, $(this), $(disciplina), action);
                if (is_disciplina != true)
                    controle.removeOpcoesEscolhidas(container);
            });

            $(btn_incluir).click(function() {
                if (is_disciplina)
                    controle.incrementaDisciplinas($(curso), $(disciplina), $(container));
                else
                    controle.incrementaPreRequisitos($(disciplina), $(container));
            });
        }
    },
    iniVoluntario: function(url_verifica_voluntario, url_ajax, url_cancelar, url_img, status, atividade, action) {
        controle.iniDisciplinas(url_ajax, url_cancelar, action, true);
        var check_atividades = $('input[name="atividades[]"]');

        if (action != action_exclusao) {
            $(check_atividades).each(function() {
                controle.mostraEscondeCheck($(this), $('.' + $(this).val()), true);
            });

            $('#data_nascimento, #data_inicio, #data_desligamento').datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: url_img
            });

            $('#nome').autocomplete({
                source: url_verifica_voluntario,
                minLength: 1,
                select: function(event, ui) {
                    console.log(ui);
                }
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                console.log(item);
                var $a = $("<a href='" + item.url + "'></a>").text(item.label);
                return $("<li></li>").append($a).append(item.desc).appendTo(ul);
            };

            $(check_atividades).click(function() {
                controle.mostraEscondeCheck($(this), $('.' + $(this).val()), true);

                if (!$(this).prop('checked') && $(this).val() == atividade) {
                    controle.limpaContainer($('.form_incrementa'));

                    $('#disciplina_voluntario').html('');
                }
            });

        }
        else {
            $(check_atividades).attr('disabled', 'disabled');
            $('input[name="disponibilidade[]"]').attr('disabled', 'disabled');
        }
    },
    iniTurma: function(url_img, url_ajax_disciplina, url_ajax_professor, action, periodo_inicio, periodo_termino) {
        var container = $('.form_incrementa'), btn_incluir = $('#incluir_professor'), btn_cancelar = $('#cancelar'), curso = $('#curso'), disciplina = $('#disciplina'), professor = $('#professor');

        controle.iniOpcoesEscolhidas(container, $('#opcoes_escolhidas'));

        if (action != action_exclusao) {
            controle.iniPeriodoCalendario(url_img, periodo_inicio, periodo_termino);

            $(curso).change(function() {
                controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), action);
                controle.limpaContainer(container);
                $(professor).html('');
            });

            $(btn_incluir).click(function() {
                controle.incrementaProfessor(professor, container);
            });

            $(disciplina).change(function() {
                controle.buscaProfessores(url_ajax_professor, $(this), $(professor));
                controle.limpaContainer(container);
                $(professor).html('');

            });
        }
    },
    iniAluno: function(url_verifica_aluno, url_img, url_ajax_alimentos, url_ajax_disciplina, url_ajax_turma, url_quantidade, url_verificacao_liberacao, action, qt_alimentos, valor_min) {
        var container_turma = $('#turmas_aluno'), container_pagamentos = $('#table_pagamentos_turmas'),
                btn_incluir_turma = $('#incluir_turma'), btn_incluir_pagamento = $('#registrar_pagamento'),
                btn_incluir_alimento = $('#incluir_alimento'), btn_cancelar = $('#cancelar'), curso = $('#curso'),
                disciplina = $('#disciplina'), turma = $('#turma'), turma_pagamento = $('#pagamento_turma'),
                valor = $('#valor_pago'), tipo_alimento = $('#alimento'), quantidade_alimento = $('#quantidade_alimento'),
                add_alimento = $('#add_alimento'), is_responsavel = $('#is_cpf_responsavel'), quantidade_alunos = $('#quant_alunos_cadastrados'),
                atualizar_alimentos = $('#atualizar_alimentos');

        $(curso).val('');

        qt_min_alimentos_liberacao = parseInt(qt_alimentos);
        valor_min_liberacao = parseFloat(valor_min);

        controle.iniAlunoTurmas(container_turma, $('#opcoes_escolhidas'), turma_pagamento);
        controle.iniAlimentos($('#container_alimentos'), $('#alimentos_escolhidos'), turma_pagamento);
        controle.iniOpcoesEscolhidas(container_pagamentos, $('#opcoes_escolhidas_pagamentos'));

        if (action != action_exclusao) {
            $('#data_nascimento, #data_registro').datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: url_img,
                changeMonth: true,
                changeYear: true
            });
        }

        $('#nome_aluno').autocomplete({
            source: url_verifica_aluno,
            minLength: 1,
            select: function(event, ui) {
                console.log(ui);
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            var $a = $("<a href='" + item.url + "'></a>").text(item.label);
            return $("<li></li>").append($a).append(item.desc).appendTo(ul);
        };

        $(curso).change(function() {
            controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), action);
            controle.limpaQuantidade(quantidade_alunos);
            $(turma).html('');
        });

        $(disciplina).change(function() {
            controle.limpaQuantidade(quantidade_alunos);
            controle.buscaTurmas(url_ajax_turma, $(this), $(turma));
        });

        $(turma).change(function() {
            controle.limpaQuantidade(quantidade_alunos);
            controle.getQuantidadeAlunos(url_quantidade, quantidade_alunos, $(this).find('option:selected').val());
        });

        $(btn_incluir_turma).click(function() {
            controle.verificaLiberacaoTurma(url_verificacao_liberacao, curso, disciplina, turma, container_turma, turma_pagamento);
        });

        $(btn_incluir_alimento).click(function() {
            controle.incrementaAlimentoTurma(turma_pagamento, tipo_alimento, quantidade_alimento);
        });

        $(btn_incluir_pagamento).click(function() {
            controle.incrementaPagamentoTurma(container_pagamentos, turma_pagamento, valor);
        });

        $(atualizar_alimentos).click(function() {
            controle.buscaAlimentos(url_ajax_alimentos, tipo_alimento);
        });

        $(turma_pagamento).change(function() {
            $('.ali_pag').hide();
            console.log(controle.retira_acentos(controle.trim($(this).find('option:selected').html())).toLowerCase());
            $('#alimentos_' + controle.retira_acentos(controle.trim($(this).find('option:selected').html())).toLowerCase()).show();
            $(tipo_alimento).val('');
            $(quantidade_alimento).val('');
            $(valor).val('00,00');

        });

        $(tipo_alimento).change(function() {
            $(quantidade_alimento).val('');
        });

        $(is_responsavel).click(function() {
            controle.mostraEscondeCheck($(this), $(this).parents('td').next().children('div'), true);

        });

        $('#enviar').click(function() {
            var aux = $(container_pagamentos).find('tr').length;
            var aux2 = $(turma_pagamento).find('option').length;

            if (aux > 0)
                aux -= 1;

            if (aux2 != aux || aux == 0) {
                exibeMensagem('Você deve incluir ao menos uma turma e registrar o pagamento dela.', 'Cadastro de Aluno');
                return false;
            }
            return true;
        });

        if ($(is_responsavel).prop('checked'))
            $(is_responsavel).parents('td').next().children('div').show().find('input').removeAttr('disabled');
    },
    mostraEscondeCheck: function(controle, elemento, is_container) {
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
    },
    mostraEscondeRadioSelect: function(controle, elemento, value, is_container) {
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
    },
    buscaDisciplinas: function(url, campo_curso, campo_disciplinas, action) {
        var opcao = $(campo_curso).val();
        var campo_disciplina_id = $('#id_disciplina'); // se é controller de disciplina
        var is_alteracao = false;

        var parametros_requisicao = {
            id_curso: opcao
        };

        if ($(campo_disciplina_id).length > 0 && action == action_alteracao) {
            parametros_requisicao = {
                id_curso: opcao,
                id_disciplina_exclude: $(campo_disciplina_id).val()
            };
            is_alteracao = true;
        }

        if ($(campo_curso).children().length > 0 && opcao != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: parametros_requisicao,
                success: function(disciplinas) {
                    var html = "";
                    if (disciplinas != null) {
                        if (disciplinas.length == 0)
                            if (!is_alteracao)
                                exibeMensagem('Não há nenhuma disciplina cadastrada para esse curso.', 'Busca de Disciplinas');
                            else
                                exibeMensagem('Não há nenhuma disciplina que você possa incluir para esse curso.', 'Busca de Disciplinas');

                        else {
                            html += '<option value="">Selecione</option>';
                            for (var i = 0; i < disciplinas.length; i++)
                                html += "<option value='" + disciplinas[i].id_disciplina + "'>" + disciplinas[i].nome_disciplina + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Disciplinas');
                    $(campo_disciplinas).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        else
            $(campo_disciplinas).html('');
    },
    buscaProfessores: function(url, campo_disciplina, campo_professores) {
        var opcao = $(campo_disciplina).val();

        if ($(campo_disciplina).children().length > 0 && opcao != '') {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: {
                    id_disciplina: opcao
                },
                success: function(professores) {
                    var html = "";

                    if (professores != null) {
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

                    $(campo_professores).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            $(campo_professores).html('');
    },
    buscaTurmas: function(url, campo_disciplina, campo_turmas, periodo) {
        var opcao = $(campo_disciplina).val();
        var parametros_requisicao = null;
        var mensagem = "";
        var is_busca_disciplina = false;

        if (periodo != undefined) {
            parametros_requisicao = {
                id_disciplina: opcao,
                id_periodo: periodo
            };
            mensagem = 'Não há nenhuma turma cadastrada para o período escolhido.';

        }

        else if ($(campo_disciplina).children().length > 0 && $(campo_disciplina).val() != '') {
            parametros_requisicao = {
                id_disciplina: opcao
            };
            is_busca_disciplina = true;
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
                            if (is_busca_disciplina)
                                html += '<option value="">Selecione</option>';

                            for (var i = 0; i < turmas.length; i++)
                                html += "<option value='" + turmas[i].id_turma + "'>" + turmas[i].nome_turma + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Turmas');

                    $(campo_turmas).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            $(campo_turmas).html('');
    },
    buscaAlimentos: function(url, campo_alimentos) {
        if ($(campo_alimentos).length > 0) {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                beforeSend: function() {
                    jQuery("#loading-alimentos").show();
                },
                complete: function() {
                    jQuery("#loading-alimentos").hide();
                },
                success: function(alimentos) {
                    var html = "";

                    if (alimentos != null) {
                        if (alimentos.length == 0)
                            exibeMensagem('Não há nenhum alimento cadastrado.', 'Busca de Alimentos');

                        else {
                            html += '<option value="">Selecione</option>';
                            for (var i = 0; i < alimentos.length; i++)
                                html += "<option value='" + alimentos[i].id_alimento + "'>" + alimentos[i].nome_alimento + "</option>";
                        }
                    }
                    else
                        exibeMensagem('Houve problemas ao realizar a busca.', 'Busca de Alimentos');

                    $(campo_alimentos).html(html);
                },
                error: function(error) {
                    console.log(error);
                }

            });
        }
        else
            $(campo_alimentos).html('');

    },
    relatorio_alunos_turma: function(url_turmas, url_relatorio) {
        iniPorcentagem(url_relatorio, 1);

        var id_turma = $('#periodo').find('option:selected').val();

        if (id_turma != undefined && id_turma.length > 0)
            controle.buscaTurmas(url_turmas, null, $('#turmas'), id_turma);

        if ($('input:radio[name="todas_turmas"]:checked').val() == 'sim')
            $('.linha').hide().find('select').attr('disabled', 'disabled').val('');
        else
            $('.linha').show().find('select').removeAttr('disabled');

        $('input:radio[name="todas_turmas"]').click(function() {
            if ($('input:radio[name="todas_turmas"]:checked').val() == 'sim')
                $('.linha').fadeOut('slow').find('select').attr('disabled', 'disabled').val('');
            else
                $('.linha').fadeIn('slow').find('select').removeAttr('disabled');
        });

        $('#periodo').change(function() {
            var id_turma = $(this).find('option:selected').val();
            $('#turmas').html('');

            if (id_turma != undefined && id_turma.length > 0) {
                controle.buscaTurmas(url_turmas, null, $('#turmas'), id_turma);
            }
        });
    },
    getQuantidadeAlunos: function(url, campo_quantidade, id_turma) {
        if ($(campo_quantidade).length > 0 && id_turma.length > 0) {
            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: {
                    id_turma: id_turma
                },
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data[0], function(key, value) {
                            if (key.indexOf('count') >= 0)
                                $(campo_quantidade).html('Alunos Cadastrados nessa Turma: <b>' + value + '</b>');
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }

            });
        }
        else
            $(campo_quantidade).html('');

    },
    limpaQuantidade: function(campo) {
        $(campo).html('');
    },
    verificaLiberacaoTurma: function(url, curso, disciplina, turma, container_turma, turma_pagamento) {
        if (!trava_aluno) {
            trava_aluno = true;

            if ($(turma).length > 0 && controle.verificaTurmasAluno(disciplina, turma, container_turma)) {
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType: "JSON",
                    data: {
                        id_turma: $(turma).find('option:selected').val(),
                        id_disciplina: $(disciplina).find('option:selected').val(),
                        id_aluno: $('#id_aluno').val()
                    },
                    success: function(liberacao) {
                        var tipo_liberacao = '';

                        if (liberacao.tipo != undefined) {
                            var pre_requisitos = '', tipo = liberacao.tipo;

                            delete liberacao.tipo;

                            if (tipo == 'sem_pre_requisito') {
                                for (var i in liberacao)
                                    pre_requisitos += liberacao[i].nome_pre_requisito + ' ';
                            }
                            else
                                pre_requisitos = 'O aluno foi reprovado na turma <b>' + liberacao.nome_turma + '</b> no período <b>' + liberacao.periodo + '</b>';

                            $('form').append('<div id="liberacao_msg"></div>');

                            $('#liberacao_msg').dialog({
                                modal: true,
                                resizable: false,
                                draggable: false,
                                title: 'Inclusão de Turmas',
                                closeOnEscape: false,
                                buttons: [{
                                        text: "Prova de Nivelamento",
                                        click: function() {
                                            tipo_liberacao = 'Prova de Nivelamento';
                                            $(this).dialog("close");
                                        }
                                    },
                                    {
                                        text: "Liberação",
                                        click: function() {
                                            tipo_liberacao = 'Liberado';
                                            $(this).dialog("close");
                                        }
                                    },
                                    {
                                        text: "Cancelar",
                                        click: function() {
                                            $(this).dialog("close");
                                            tipo_liberacao = 'cancelado';
                                        }
                                    }],
                                close: function() {
                                    if (tipo_liberacao.length > 0 && tipo_liberacao != 'cancelado') {
                                        controle.incrementaTurma(curso, disciplina, turma, container_turma, tipo_liberacao, turma_pagamento);
                                        controle.incrementaTurmasAluno(disciplina, turma, turma_pagamento);
                                    }
                                }
                            }).html('Aluno não possui pré-requisitos (<b>' + pre_requisitos + '</b>) para cursar essa disciplina. Favor Selecionar uma das opções abaixo.');
                        }
                        else {
                            controle.incrementaTurma(curso, disciplina, turma, container_turma, tipo_liberacao, turma_pagamento);
                            controle.incrementaTurmasAluno(disciplina, turma, turma_pagamento);
                        }
                        trava_aluno = false;
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
            else
                trava_aluno = false;
        }
    },
    verificaTurmasAluno: function(disciplina, turma, container) {
        var aux = $(disciplina).find('option:selected').html() + ' - ' + $(turma).find('option:selected').html();
        var option = $(turma).find('option:selected');

        if ($(turma).children().length > 0 && !$(container).find('tr').hasClass(controle.retira_acentos(controle.trim(aux)).toLowerCase()) && $(option).val() != "")
            return true;

        exibeMensagem('Nenhuma turma foi selecionada ou ela já foi incluída.', 'Inclusão de Turma');
        return false;
    },
    incrementaTurmasAluno: function(disciplina, turma, turma_pagamento) {
        var aux = $(disciplina).find('option:selected').html() + ' - ' + $(turma).find('option:selected').html();
        $(turma_pagamento).append('<option value="' + $(turma).find('option:selected').val() + '">' + aux + '</option>');
    },
    incrementaPreRequisitos: function(pre_requisito, container) {
        var option = $(pre_requisito).find('option:selected');
        var id_pre_requisito = $(option).val();

        if ($(pre_requisito).children().length > 0 && !$(container).find('tr').hasClass($(option).val()) && $(option).val() != "") {
            var html = '';

            if ($(container).children().length == 0) {
                $(container).show();
                html = '<tr><th>Disciplina(Pré-Requisito)</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_pre_requisito + '"><input type="hidden" name="pre_requisitos[]" value="' + id_pre_requisito + '"/><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            $(container).append(html);
            controle.eventOpcaoExcluir();
        }
        else
            exibeMensagem('Nenhuma disciplina foi selecionada ou ela já foi incluída.', 'Inclusão de Pré-Requisitos');

    },
    incrementaDisciplinas: function(curso, disciplina, container) {
        var option = $(disciplina).find('option:selected');
        var id_disciplina = $(option).val();

        if ($(disciplina).children().length > 0 && !$(container).find('tr').hasClass($(disciplina).find('option:selected').val()) && $(option).val() != "") {
            var html = '';

            if ($(container).children().length == 0) {
                $(container).show();
                html = '<tr><th>Curso</th><th>Disciplina</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_disciplina + '"><input type="hidden" name="disciplinas[]" value="' + id_disciplina + '"/><td>' + $(curso).find('option:selected').html() + '</td><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            $(container).append(html);
            controle.eventOpcaoExcluir();
        }
        else
            exibeMensagem('Nenhuma disciplina foi selecionada ou ela já foi incluída.', 'Inclusão de Disciplina');
    },
    incrementaProfessor: function(professor, container) {
        var option = $(professor).find('option:selected');
        var id_professor = $(option).val();

        if ($(professor).children().length > 0 && !$(container).find('tr').hasClass($(option).val()) && $(option).val() != "") {
            var html = '';

            if ($(container).children().length == 0) {
                $(container).show();
                html = '<tr><th>Professor</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + id_professor + '"><input type="hidden" name="professores[]" value="' + id_professor + '"/><td>' + $(option).html() + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
            $(container).append(html);
            controle.eventOpcaoExcluir();
        }
        else
            exibeMensagem('Nenhum professor foi selecionado ou ele já foi incluído.', 'Inclusão de Professores');
    },
    incrementaTurma: function(curso, disciplina, turma, container, liberacao_requisitos, turma_pagamento) {
        var aux = $(disciplina).find('option:selected').html() + ' - ' + $(turma).find('option:selected').html();
        var option = $(turma).find('option:selected');
        var id_turma = $(option).val();
        var html = '';

        if ($(container).children().length == 0) {
            $(container).show();
            html = '<tr><th>Curso</th><th>Disciplina</th><th>Turma</th><th>Liberação de Requisitos</th><th>Opções</th></tr>';
        }

        html += '<tr class="' + controle.retira_acentos(controle.trim(aux)).toLowerCase() + '"><input type="hidden" name="turmas[]" value="' + id_turma + '"/><td>' + $(curso).find('option:selected').html() + '</td><td>' + $(disciplina).find('option:selected').html() + '</td><td>' + $(turma).find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_turma + ']" value="' + liberacao_requisitos + '"/>' + liberacao_requisitos + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
        $(container).append(html);
        controle.eventExcluirTurmaAluno(container, turma_pagamento);
    },
    incrementaAlimentoTurma: function(turma, tipo_alimento, quantidade_alimento) {
        if ($(turma).children().length > 0) {
            var turma_option = $(turma).find('option:selected');
            var aux_id_turma = controle.retira_acentos(controle.trim($(turma_option).html())).toLowerCase();
            var id_container = '#alimentos_' + aux_id_turma;
            var container_alimentos_turma = $('' + id_container);
            var tipo_alimento_option = $(tipo_alimento).find('option:selected');
            var quantidade = controle.parseNumero($(quantidade_alimento).val());

            $('.ali_pag').hide();

            if ($(tipo_alimento).children().length > 0 && quantidade > 0 && $(tipo_alimento_option).val() != "" && !$(container_alimentos_turma).find('tr').hasClass($(tipo_alimento_option).val())) {
                if ($(container_alimentos_turma).length == 0) {
                    $('#container_alimentos').append('<table class="ali_pag form_incrementa" id="' + id_container.replace('#', '') + '" cellpadding="0" cellspacing="0"><tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr></table>');
                    container_alimentos_turma = $('#container_alimentos').find(id_container);
                }

                if ($(container_alimentos_turma).children().length == 0)
                    $(container_alimentos_turma).append('<tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr>');

                $(container_alimentos_turma).append('<tr class="' + $(tipo_alimento_option).val() + '"><input type="hidden" name="alimentos[' + $(turma_option).val() + '][' + $(tipo_alimento_option).val() + ']" value="' + quantidade + '"/><td>' + $(tipo_alimento_option).html() + '</td><td class="quantidade_alimento_turma">' + quantidade + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>');
                controle.atualizaAlimentosPagamento(quantidade, aux_id_turma);
                controle.eventExcluirAlimento(container_alimentos_turma);
            }
            else
                exibeMensagem('O alimento já foi incluído ou nenhum foi selecionado. Verifique também se a quantidade de alimentos foi preenchida corretamente (ex: <b>"0.5"</b>, <b>"1"</b>).', 'Inclusão de Alimentos');

            $(container_alimentos_turma).show();
        }
        else
            exibeMensagem('Nenhuma turma foi incluida.', 'Inclusão de Alimentos');
    },
    atualizaAlimentosPagamento: function(quantidade, class_turma) {
        var linha_pagamento = $('.pagamento_' + class_turma);

        if ($(linha_pagamento).length > 0) {
            var container_valor = $(linha_pagamento).find('.quant_alimento');
            var valor_pago = controle.parseNumero($(linha_pagamento).find('.valor_pago').html());
            var valor_atual = controle.parseNumero($(container_valor).html());
            var total_alimentos = valor_atual + quantidade;

            var situacao = ((total_alimentos >= qt_min_alimentos_liberacao && valor_pago >= valor_min_liberacao) ? 'Liberado' : 'Pendente');
            var situacao_container = $(linha_pagamento).find('.situacao');
            var id_turma = $(situacao_container).find('input').attr('name');

            $(container_valor).html(total_alimentos);
            $(situacao_container).html('<input type="hidden" name="' + id_turma + '" value="' + situacao + '"/>' + situacao);
        }
    },
    incrementaPagamentoTurma: function(container_pagamento, turma, valor) {
        var option = $(turma).find('option:selected');

        if ($(option).length > 0) {
            var id_turma = $(option).val();
            var pagamento_class = controle.retira_acentos(controle.trim($(option).html())).toLowerCase();
            var valor_pago = controle.parseNumero($(valor).val());
            var total_alimentos = 0.0;

            //soma quantidades de alimentos
            $('#alimentos_' + pagamento_class).find('.quantidade_alimento_turma').each(function() {
                var aux = controle.parseNumero($(this).html());
                if (aux != -1)
                    total_alimentos += aux;
                else
                    total_alimentos = -1;
            });

            if ($(turma).children().length > 0 && total_alimentos != -1 && valor_pago != -1 && !$(container_pagamento).find('tr').hasClass('pagamento_' + pagamento_class) && $(option).val() != "") {
                var html = '';
                var situacao;

                if ($(container_pagamento).children().length == 0) {
                    $(container_pagamento).show();
                    html = '<tr><th>Disciplina - Turma</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Situação</th><th>Opções</th></tr>';
                }

                situacao = ((total_alimentos >= qt_min_alimentos_liberacao && valor_pago >= valor_min_liberacao) ? 'Liberado' : 'Pendente');

                html += '<tr class="pagamento_' + pagamento_class + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + valor_pago + '"/><td>' + $(option).html() + '</td><td class="valor_pago">' + valor_pago + '</td><td class="quant_alimento">' + total_alimentos + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="' + situacao + '"/>' + situacao + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
                $(container_pagamento).append(html);
                controle.eventOpcaoExcluir();
            }
            else
                exibeMensagem('O pagamento dessa turma já foi inserido, Nesse caso, se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');
        }
        else
            exibeMensagem('Inclua primeiro a turma', 'Registro de Pagamento');
    },
    limpaContainer: function(container) {
        $(container).hide().children().remove().hide();
    },
    eventExcluirTurmaAluno: function(container_turma, turma_pagamento) {
        $(container_turma).find('.excluir_geral').click(function() {
            var aux_class = $(this).parents('tr').attr('class');
            var table = $(this).parents('table');
            var table_pagamento = $('.pagamento_' + aux_class).parents('table');
            var id_turma = $(this).parents('tr').children('input').val();

            $(turma_pagamento).find('option').each(function() {
                if ($(this).val() == id_turma)
                    $(this).remove();
            });

            $('#alimentos_' + aux_class).remove();
            $('.pagamento_' + aux_class).remove();

            if ($(table_pagamento).find('tr').length == 1)
                $(table_pagamento).html('').hide();

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();

        });
    },
    eventExcluirAlimento: function(container) {
        $(container).find('.excluir_geral').click(function() {
            var quantidade = controle.parseNumero($(this).parents('tr').find('.quantidade_alimento_turma').html());
            var table = $(this).parents('table');

            controle.atualizaAlimentosPagamento(quantidade * (-1), $(table).attr('id').replace('alimentos_', ''));

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();

        });
    },
    eventOpcaoExcluir: function() {
        $('.excluir_geral').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });
    },
    formDesligamento: function(url_img) {
        $('#data_desligamento').datepicker({
            buttonText: "Clique para selecionar uma data",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: url_img
        });
    },
    /**
     * Função responsável pelo gerenciamento dos campos de período
     * Inicialização dos calendários
     * @param {type} url_img
     * @param {type} min_date
     * @param {type} max_date
     * @returns {undefined}
     */
    iniPeriodoCalendario: function(url_img, min_date, max_date) {
        var data_inicio = $('#data_inicio');
        var data_termino = $('#data_fim');

        if (data_termino.length == 0)
            data_termino = $('#data_termino');

        $(data_inicio).datepicker({
            buttonText: "Clique para selecionar a data inicial",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: url_img,
            onSelect: function(data) { // A data de término do período só pode ser incluída após a escolha da data inicial
                $(data_termino).datepicker("setDate", null).val('');
                $(data_termino).datepicker({
                    buttonText: "Clique para selecionar a data inicial",
                    showOn: "button",
                    buttonImageOnly: true,
                    buttonImage: url_img,
                    minDate: data
                });
                $(data_termino).datepicker('option', 'minDate', data);
                $(data_termino).datepicker('option', 'maxDate', $('#data_inicio').datepicker('option', 'maxDate'));
            }
        });

        if (min_date != undefined && max_date != undefined) {
            $(data_inicio).datepicker('option', 'minDate', min_date);
            $(data_inicio).datepicker('option', 'maxDate', max_date);
        }

        if ($(data_inicio).val().length > 0) { // se já tiver uma data inicial setada, a data final pode ser incluída
            $(data_termino).datepicker({
                buttonText: "Clique para selecionar a data inicial",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: url_img,
                minDate: min_date
            });
        }
    },
    /*Calendário Acadêmico*/

    datas_atividades: function(url_img, min_date, max_date) {
        var container = $('#datas-escolhidas'), campo_data = $('#data'), container_datas_cadastradas = $('#container-datas');

        if ($(container_datas_cadastradas).children().length > 0)
            $(container).append($(container_datas_cadastradas).children());

        $('.remove').click(function() {
            $(this).parent().remove();
        });

        $('#todos_sabados').click(function() {
            if ($(this).prop('checked')) {
                var aux_min_date = controle.parseDate(min_date);
                var aux = aux_min_date.clone();
                var aux_max_date = controle.parseDate(max_date);

                if (aux_min_date instanceof Date && aux_max_date instanceof Date) {
                    while (aux.compareTo(aux_min_date) >= 0 && aux.compareTo(aux_max_date) <= 0) {
                        if (aux.toString('dddd') == "sábado") {
                            var data = aux.toString('dd/MM/yyyy');

                            if ($(container).find('div[valor="' + data + '"]').length == 0)
                                $(container).append('<div class="data-selecionada" valor="' + data + '">' + data + '<span title="Clique para remover essa data" class="remove">X</span></div>');

                            aux.addWeeks(1);
                        }
                        else
                            aux.addDays(1);

                    }
                    $('.remove').click(function() {
                        $(this).parent().remove();
                    });
                }
            }
            else {
                $(container).children().each(function() {
                    var data = controle.parseDate($(this).attr('valor'));

                    if (data instanceof Date) {
                        if (data.toString('dddd') == "sábado")
                            $(this).remove();
                    }
                });
            }
        });

        $(campo_data).datepicker({
            buttonText: "Clique para selecionar uma data",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: url_img,
            onSelect: function(data) {
                if ($(container).find('div[valor="' + data + '"]').length == 0)
                    $(container).append('<div class="data-selecionada" valor="' + data + '">' + data + '<span title="Clique para remover essa data" class="remove">X</span></div>');

                $('.remove').click(function() {
                    $(this).parent().remove();
                });
            }
        });

        if (min_date != undefined && max_date != undefined) {
            $(campo_data).datepicker('option', 'minDate', min_date);
            $(campo_data).datepicker('option', 'maxDate', max_date);
        }

        $('#enviar').click(function() {
            var selecionados = $('.data-selecionada');

            if ($(selecionados).length == 0) {
                exibeMensagem('Nenhuma data foi selecionada.', 'Datas de Atividades');
                return false;
            }
            else {
                $(selecionados).each(function(index) {
                    $('form').append('<input type="hidden" value="' + $(this).attr('valor') + '" name="data_' + index + '"/>');
                });
            }
        });

    },
    /* Frequencia */

    frequenciaAlunos: function(url_ajax_aluno, url_ajax_disciplina, url_ajax_turma, dates, data_atual) {
        var curso = $('#curso'), disciplina = $('#disciplina'), turma = $('#turma'), periodo = $('#periodo'), data = $('#data'), container = $('#calendario_frequencia'),
                container_frequencias = $('#frequencia');

        $(curso).val('');

        $(curso).change(function() {
            controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), 1);
            $(turma).html('');
            $(container_frequencias).html('');
            $(container).datepicker('destroy');
        });

        $(disciplina).change(function() {
            $(turma).html('');
            controle.buscaTurmas(url_ajax_turma, $(this), $(turma));
            $(container_frequencias).html('');
            $(container).datepicker('destroy');
        });

        $(turma).change(function() {
            var id_turma = $(this).find('option:selected').val();

            if (id_turma == undefined || id_turma == '') {
                exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Frequência');
                $(container_frequencias).html('');
                $(container).datepicker('destroy');
            }
            else
                controle.getAlunosTurma(url_ajax_aluno, container_frequencias, id_turma, $(container), data_atual, turma, dates);//, data_escolhida);

        });

    },
    getAlunosTurma: function(url, container, id_turma, container_calendario, data_atual, container_turma, dates) {//, data) {
        if ($(container).length > 0 && id_turma.length > 0) {

            if (!(data_atual instanceof Date))
                data_atual = controle.parseDate(data_atual);

            $.ajax({
                type: "POST",
                url: url,
                dataType: "JSON",
                data: {
                    id_turma: id_turma
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
                    var html = "<div id='title-frequencia' class='obs'>Escolha um dia para fazer / alterar o lançamento de frequencia</div>";

                    $(container_calendario).datepicker('destroy');

                    if (alunos instanceof Object) {
                        busca_alunos = alunos;

                        var min_date = controle.parseDate(alunos['turma']['data_inicio']),
                                max_date = controle.parseDate(alunos['turma']['data_termino']);

                        delete alunos['turma'];

                        html += '<table id="alunos_turma_frequencia" class="form_incrementa stripped"><tr><th>Aluno</th><th>Média de Frequência(%)</th><th>Presente</th>';
                        for (var key in alunos)
                            html += '<tr><td>' + alunos[key].nome_aluno + '</td><td>' + alunos[key].media_frequencia + '</td><td id="campo_frequencia_' + alunos[key].id_aluno + '"> - </td></tr>';
                        html += '</table>';

                        $(container_calendario).datepicker({
                            minDate: min_date,
                            maxDate: max_date,
                            beforeShowDay: function(calendar_date) {
                                var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);

                                if (dates[aux] != undefined && +data_atual >= +calendar_date)
                                    return [true, ''];

                                return [false, ''];
                            },
                            onSelect: function(data_escolhida) {
                                var id_turma = $(container_turma).find('option:selected').val();

                                if (id_turma == undefined || id_turma == '') {
                                    exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Frequência');
                                    $(container).html('');
                                }

                                else {
                                    $(data).val(data_escolhida);
                                    $('#title-frequencia').removeClass('obs').html('<h2>Lançamento do Dia: ' + data_escolhida + '</h2>');

                                    if (busca_alunos instanceof Object) {
                                        for (var key in busca_alunos) {
                                            var achou = false;

                                            for (var turma_faltas in busca_alunos[key].faltas) {
                                                if (turma_faltas == id_turma) {
                                                    for (var falta in busca_alunos[key].faltas[turma_faltas]) {
                                                        if (busca_alunos[key].faltas[turma_faltas][falta].data_funcionamento == data_escolhida) {
                                                            $(container).find('#campo_frequencia_' + busca_alunos[key].id_aluno).html('<label><input type="radio" class="radio-frequencia" name="aluno_' + busca_alunos[key].id_aluno + '" value="sim" />Sim</label><label><input type="radio" class="radio-frequencia" name="aluno_' + busca_alunos[key].id_aluno + '" checked="checked" value="não"/>Não</label><div class="observacao-frequencia"><label for="observacao_' + busca_alunos[key].id_aluno + '">Observação</label><input type="text" name="observacao_' + busca_alunos[key].id_aluno + '" value="' + busca_alunos[key].faltas[turma_faltas][falta].observacao + '"/></div>').find('.observacao-frequencia').show();
                                                            achou = true;
                                                            break;
                                                        }
                                                    }
                                                }

                                                if (achou)
                                                    break;
                                            }
                                            if (!achou)
                                                $(container).find('#campo_frequencia_' + busca_alunos[key].id_aluno).html('<label><input type="radio" class="radio-frequencia" name="aluno_' + busca_alunos[key].id_aluno + '" value="sim" checked="checked" />Sim</label><label><input type="radio" class="radio-frequencia" name="aluno_' + busca_alunos[key].id_aluno + '" value="não"/>Não</label><div class="observacao-frequencia"><label for="observacao_' + busca_alunos[key].id_aluno + '">Observação</label><input type="text" name="observacao_' + busca_alunos[key].id_aluno + '" value=""/></div>');
                                        }

                                        $('.radio-frequencia').click(function() {
                                            if ($(this).val() == 'não')
                                                $(this).parents('td').find('.observacao-frequencia').fadeIn('fast').find('input').removeAttr('disabled');
                                            else
                                                $(this).parents('td').find('.observacao-frequencia').fadeOut('fast').find('input').attr('disabled', 'disabled');
                                        });
                                    }
                                }
                            }
                        });

                    }
                    else
                        html += "Não há nenhum aluno cadastrado na turma selecionada";

                    $(container).html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            $(container).html('');

    },
    /*Atividade */

    atividade: function(url_ajax_disciplina, url_ajax_turma, url_ajax_atividade, url_img, data_inicio, data_fim, dates, action) {
        if (action != action_exclusao) {
            var curso = $('#curso'), disciplina = $('#disciplina'), turma = $('#turma'), data = $('#data_funcionamento'), container_atividades = $('#atividades-turma');

            $(data).datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: url_img,
                minDate: data_inicio,
                maxDate: data_fim,
                beforeShowDay: function(calendar_date) {
                    var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);

                    if (dates[aux] != undefined)
                        return [true, ''];
                    return [false, ''];
                }
            });

            $(curso).change(function() {
                controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), 1);
                $(turma).html('');
                $(container_atividades).html('');
            });

            $(disciplina).change(function() {
                $(turma).html('');
                controle.buscaTurmas(url_ajax_turma, $(this), $(turma));
                $(container_atividades).html('');
            });

            $(turma).change(function() {
                var id_turma = $(this).find('option:selected').val();

                if (id_turma == undefined || id_turma == '') {
                    exibeMensagem('Você deve escolher uma turma.', 'Atividades');
                    $(container_atividades).html('');
                }
                else {
                    $.ajax({
                        type: "POST",
                        url: url_ajax_atividade,
                        dataType: "JSON",
                        data: {
                            id_turma: id_turma
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
                }

            });
        }
    },
    /* Lançamentos de Notas */

    notasAlunos: function(url_ajax_alunos, url_ajax_disciplina, url_ajax_turma, data_atual, url_ajax_atividade) {
        var curso = $('#curso'), disciplina = $('#disciplina'), turma = $('#turma'), atividade = $('#container_atividade'),
                alunos = $('#notas_alunos');

        $(curso).val('');

        $(curso).change(function() {
            controle.buscaDisciplinas(url_ajax_disciplina, $(this), $(disciplina), 1);
            $(turma).html('');
            $(alunos).html('');
            $(atividade).find('select').html('');
            $(atividade).hide();
        });

        $(disciplina).change(function() {
            controle.buscaTurmas(url_ajax_turma, $(this), $(turma));
            $(turma).html('');
            $(alunos).html('');
            $(atividade).find('select').html('');
            $(atividade).hide();
        });

        $(turma).change(function() {
            var id_turma = $(this).find('option:selected').val();

            if (id_turma == undefined || id_turma == '') {
                exibeMensagem('Para fazer o lançamento, deve-se escolher uma turma.', 'Lançamento de Notas');
                $(alunos).html('');
                $(atividade).find('select').html('');
                $(atividade).hide();
            }
            else
                controle.getAtividadesAlunosTurma(url_ajax_atividade, url_ajax_alunos, id_turma, data_atual, atividade, alunos);
        });


    },
    getAtividadesAlunosTurma: function(url_ajax_atividade, url_ajax_alunos, turma, data, container_atividade, container_alunos) {
        $.ajax({
            type: "POST",
            url: url_ajax_atividade,
            dataType: "JSON",
            data: {
                id_turma: turma,
                data: data
            },
            success: function(atividades) {
                var html = '';

                if (atividades instanceof Object) {
                    if (atividades.length > 0) {
                        var html = '<option>Selecione</option>';

                        for (var key in atividades)
                            html += '<option value = "' + atividades[key].id + '" valor="' + atividades[key].valor + '">' + atividades[key].nome + '</option>';

                        $(container_atividade).find('select').html(html);
                        controle.getAlunosTurmaNotas(url_ajax_alunos, container_alunos, turma, container_atividade);
                    }
                    else {
                        $(container_alunos).html('');
                        $(container_atividade).find('select').html('');
                        $(container_atividade).hide();
                        exibeMensagem('Nenhuma atividade foi encontrada na turma indicada.', 'Lançamento de Notas');
                    }
                }
                else {
                    $(container_alunos).html('');
                    $(container_atividade).find('select').html('');
                    $(container_atividade).hide();
                    exibeMensagem('Nenhuma atividade foi encontrada na turma indicada.', 'Lançamento de Notas');
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    },
    getAlunosTurmaNotas: function(url_ajax_alunos, container_alunos, turma, container_atividade) {
        $.ajax({
            type: "POST",
            url: url_ajax_alunos,
            dataType: "JSON",
            data: {
                id_turma: turma
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
                var html = '';

                alunos_notas = alunos;

                if (alunos instanceof Object) {
                    html += "<div id='title-notas' class='obs'>Escolha uma atividade para fazer o lançamento de nota. Campos vazios serão considerados como nota 0</div> <div id='valor_atividade' class='obs'></div>";
                    html += '<table id="alunos_turma_notas" class="form_incrementa stripped"><tr><th>Aluno</th><th>Nota</th>';

                    for (var key in alunos)
                        html += '<tr><td>' + alunos[key].nome_aluno + '</td><td id="aluno_nota_' + alunos[key].id_aluno + '"> - </td></tr>';

                    html += '</table>';

                    $(container_atividade).show().change(function() {
                        var notas = $('#alunos_turma_notas').find('td[id*="aluno_nota"]');
                        var opcao = $(this).find('option:selected');
                        var max = parseFloat($(opcao).attr('valor'));
                        var atividade = opcao.val();

                        $('#valor_atividade').html('<strong>A atividade vale ' + max + ' pontos</strong>');

                        $(notas).each(function() {
                            var aluno = $(this).attr('id').replace('aluno_nota_', '');
                            var nota_lancada = "";

                            for (var key in alunos_notas) {
                                if (aluno == alunos_notas[key].id_aluno) {
                                    for (var key_notas in alunos_notas[key].notas) {
                                        for (var key_nota in alunos_notas[key].notas[key_notas]) {
                                            if (atividade == alunos_notas[key].notas[key_notas][key_nota].atividade) {
                                                nota_lancada = alunos_notas[key].notas[key_notas][key_nota].valor_nota;
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
                            $(this).html('<input type="text" value ="' + nota_lancada + '" name="aluno_' + aluno + '" id="aluno_' + aluno + '"/><span class="msg-error"></span>');

                            $(this).children().blur(function(event) {
                                var val = parseFloat($(this).val());

                                if (isNaN(val) || isNaN(max) || val > max || val < 0)
                                    $(this).val('').focus().next().html('<strong>Valor inválido</strong>');
                                else
                                    $(this).val(val).next().html('');

                                event.stopPropagation();
                                return false;

                            });
                        });
                    });
                }
                else {
                    $(container_atividade).find('select').html('');
                    html += "Não há nenhum aluno cadastrado na turma selecionada";
                }

                $(container_alunos).html(html);

            },
            error: function(error) {
                console.log(error);
            }
        });
    },
    /*Frequência de Voluntários*/

    frequenciaVoluntarios: function(url_ajax_voluntario, datas_calendario_academico, data_atual, data_ini_periodo, data_fim_periodo) {
        var calendario = $('#calendario'), setor = $('#setor'), voluntario_container = $('#voluntarios');

        $(setor).val('');

        if (!(data_atual instanceof Date))
            data_atual = controle.parseDate(data_atual);


        $(setor).change(function() {
            setor = $(this).find('option:selected').val();

            if (setor == undefined || setor == '')
                exibeMensagem('Você deve escolher uma das opções.', 'Frequência de Voluntários');

            else {
                $.ajax({
                    type: "POST",
                    url: url_ajax_voluntario,
                    dataType: "JSON",
                    data: {
                        setor: setor
                    },
                    beforeSend: function() {
                        jQuery('#mensagem-ajax').dialog({
                            dialogClass: "no-close",
                            closeOnEscape: false,
                            modal: true,
                            title: 'Busca de Voluntários'
                        });
                    },
                    complete: function() {
                        jQuery('#mensagem-ajax').dialog('destroy');
                    },
                    success: function(voluntarios) {
                        voluntarios_frequencia = voluntarios;
                        var html = '';

                        if (voluntarios instanceof Object) {
                            if (voluntarios.length > 0) {
                                html += "<div id='title-frequencia' class='obs'>Para fazer o lançamento escolha uma data no calendário acima.</div><div id='data_lancamento'></div>";
                                html += '<table id="frequencia_voluntario" class="form_incrementa stripped"><tr><th>Voluntário</th><th>Presente?</th><th>Hora de Entrada</th><th>Hora de Saída</th><th>Total de Horas</th>';

                                for (var key in voluntarios)
                                    html += '<tr voluntario="' + voluntarios[key].id_voluntario + '"><td>' + voluntarios[key].nome_voluntario + '</td><td class="is_presente"> - </td><td class="hora_entrada"> - </td><td class="hora_saida"> - </td><td class="total_horas"> ' + voluntarios[key].total_horas['horas'] + ' horas ' + voluntarios[key].total_horas['minutos'] + ' minutos</td>';

                                html += '</table>';

                                $(calendario).datepicker({
                                    buttonText: "Clique para selecionar uma data",
                                    minDate: data_ini_periodo,
                                    maxDate: data_fim_periodo,
                                    beforeShowDay: function(calendar_date) {
                                        var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);
                                        if (datas_calendario_academico[aux] != undefined && +data_atual >= +calendar_date)
                                            return [true, ''];
                                        return [false, ''];
                                    },
                                    onSelect: function(data_escolhida) {
                                        var hora_entrada = '', hora_saida = '', is_presente = 1;

                                        $('#data_lancamento').html('<h2>Lançamento do Dia: ' + data_escolhida + '</h2>');
                                        $('#data').val(data_escolhida);

                                        for (var key in voluntarios_frequencia) {
                                            for (var key_frequencia in voluntarios_frequencia[key].frequencia) {
                                                if (data_escolhida == voluntarios_frequencia[key].frequencia[key_frequencia].data_funcionamento) {
                                                    is_presente = voluntarios_frequencia[key].frequencia[key_frequencia].is_presente;

                                                    if (is_presente) {
                                                        hora_entrada = voluntarios_frequencia[key].frequencia[key_frequencia].hora_entrada;
                                                        hora_saida = voluntarios_frequencia[key].frequencia[key_frequencia].hora_saida;
                                                    }
                                                }

                                            }

                                            $('#frequencia_voluntario').find('tr[voluntario="' + voluntarios_frequencia[key].id_voluntario + '"]').each(function() {
                                                $(this).find('.is_presente').html('<input type="checkbox" class="input_is_presente" ' + ((is_presente != 0) ? 'checked="checked"' : '') + ' name="voluntario_presente_' + voluntarios_frequencia[key].id_voluntario + '" />');
                                                $(this).find('.is_presente').append('<input type="hidden" class="hidden_is_presente" value="' + ((is_presente != 0) ? 'on' : 'off') + '" name="voluntario_presente_' + voluntarios_frequencia[key].id_voluntario + '" />');
                                                $(this).find('.hora_entrada').html('<input type="text" class="input_hora_entrada" ' + ((is_presente != 0) ? '' : 'disabled = "disabled"') + ' value="' + hora_entrada + '" name="voluntario_entrada_' + voluntarios_frequencia[key].id_voluntario + '" />');
                                                $(this).find('.hora_saida').html('<input type="text" class="input_hora_saida" ' + ((is_presente != 0) ? '' : 'disabled = "disabled"') + ' value="' + hora_saida + '" name="voluntario_saida_' + voluntarios_frequencia[key].id_voluntario + '" />');
                                            });
                                        }

                                        $('.input_hora_entrada, .input_hora_saida').mask('99:99');
                                        $('.input_is_presente').click(function() {
                                            if (!$(this).prop('checked')) {
                                                $(this).parents('tr').find('.hidden_is_presente').val('off');
                                                $(this).parents('tr').find('.input_hora_entrada').val('').attr('disabled', 'disabled');
                                                $(this).parents('tr').find('.input_hora_saida').val('').attr('disabled', 'disabled');
                                            }
                                            else {
                                                $(this).parents('tr').find('.hidden_is_presente').val('on');
                                                $(this).parents('tr').find('.input_hora_entrada').val('').removeAttr('disabled');
                                                $(this).parents('tr').find('.input_hora_saida').val('').removeAttr('disabled');
                                            }

                                        });

                                        $('form').submit(function() {
                                            var envia = true;

                                            $('.input_is_presente').each(function() {
                                                if ($(this).prop('checked'))
                                                    $(this).parent().find('.hidden_is_presente').attr('disabled');
                                            });

                                            $('#frequencia_voluntario').find('tr').each(function() {
                                                if ($(this).attr('voluntario') !== undefined) {
                                                    if ($(this).find('.input_is_presente').prop('checked')) {
                                                        var hora_entrada = $(this).find('.input_hora_entrada');
                                                        var hora_saida = $(this).find('.input_hora_saida');

                                                        if (!(Date.parse('01/01/2014 ' + $(hora_entrada).val()) < Date.parse('01/01/2014 ' + $(hora_saida).val()))) {
                                                            $(hora_entrada).css('border', '1px solid red');
                                                            $(hora_saida).css('border', '1px solid red');

                                                            exibeMensagem('Há lançamentos com problemas, corrija-os e tente novamente', 'Frequência de Voluntários')
                                                            envia = false;
                                                        }
                                                    }
                                                }
                                            });

                                            if (!envia)
                                                return false;
                                        });

                                    }
                                });
                            }

                            else {
                                exibeMensagem('Nenhum voluntário foi encontrado', 'Frequência de Voluntários');
                            }
                        }
                        else {
                            exibeMensagem('Nenhum voluntário foi encontrado', 'Frequência de Voluntários');
                        }

                        $(voluntario_container).html(html);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
        });
    },
    retira_acentos: function(palavra) {
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
    },
    trim: function(vlr) {
        while (vlr.indexOf(" ") != - 1)
            vlr = vlr.replace(' - ', '_').replace(" ", "_");

        return vlr;
    },
    validaNumero: function(valor) {
        var aux = parseFloat(valor);
        if (isNaN(aux) || aux < 0)
            return false;
        return true;
    },
    parseNumero: function(valor) {
        if (valor.length == 0)
            return 0;

        valor = valor.replace(',', '.');

        if (controle.validaNumero(valor))
            return parseFloat(valor);

        return -1;
    },
    parseDate: function(str_date) {
        if (str_date != "") {
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
    }
}
