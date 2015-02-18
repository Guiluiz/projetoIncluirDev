/**
 * Controle do gerenciamento de Alunos
 * @returns {undefined}
 */
var controle_aluno = (function() {
    var aluno = {
        container_turma: $('#turmas_aluno'),
        container_pagamentos: $('#table_pagamentos_turmas'),
        container_alimentos: $('#container_alimentos'),
        btn_incluir_turma: $('#incluir_turma'),
        btn_incluir_pagamento: $('#registrar_pagamento'),
        btn_incluir_alimento: $('#incluir_alimento'),
        btn_atualizar_alimentos: $('#atualizar_alimentos'),
        btn_enviar: $('#enviar'),
        campo_quantidade_turmas: $('#quantidade_turmas'),
        campo_nome_aluno: $('#nome_aluno'),
        campo_curso: $('#curso'),
        campo_disciplina: $('#disciplina'),
        campo_turma: $('#turma'),
        campos_data: $('#data_nascimento, #data_registro'),
        select_turma_pagamento: $('#pagamento_turma'), // campo onde seleciona a turma para incluir o pagamento
        campo_valor_pagamento: $('#valor_pago'),
        campo_tipo_alimento: $('#alimento'),
        campo_quantidade_alimento: $('#quantidade_alimento'),
        check_is_responsavel: $('#is_cpf_responsavel'),
        container_quantidade_alunos_turma: $('#quant_alunos_cadastrados'),
        container_turmas_pre_definidas: $('#opcoes_escolhidas'), // Container com as escolhas do usuário já pré definidas
        container_alimentos_pre_definidos: $('#alimentos_escolhidos'), // Container com opções de alimentos dos pagamentos das turmas do usuário já pré definidas
        container_pagamentos_pre_definidos: $('#opcoes_escolhidas_pagamentos'), // Container com os pagamentos das turmas do usuário já pré definidas
        container_campo_quantidade_turmas: $('.quantidade_turmas'),
        container_btn_incluir_turma: $('.incluir_turma'),
        container_campos_escolha_turma: $('#busca_turmas'),
        class_container_alimentos_pagamento: '.ali_pag', // classe para as tabelas que armazenam os alimentos de um pagamento da turma
        url_ajax_verifica_aluno: '', // verifica a existência de alunos com nome próximo no bd
        url_img: '',
        url_ajax_alimentos: '',
        url_ajax_disciplina: '',
        url_ajax_turma: '',
        url_ajax_quantidade_alunos_turma: '', // busca a quantidade de alunos da turma selecionada
        url_ajax_verificacao_liberacao: '', // verifica se aluno pode ser matriculado na disciplina escolhida
        action: '', // 1- cadastro 2 - alteracao 3 - exclusao
        qt_min_alimentos: '', // quantidade mínima de alimentos para pagamento ser válido
        valor_min_pagamento: '', // valor mínimo para pagamento ser válido
        trava_busca_liberacao: false,
        liberacao_turma: null,
        horarios_turmas_incluidas: new Array() // utilizado para verificar se há inconsistências de horários nas turmas que serão escolhidas pelo usuário
    };

    aluno.setValues = function(url_verifica_aluno, url_img, url_ajax_alimentos, url_ajax_disciplina, url_ajax_turma, url_quantidade, url_verificacao_liberacao, action, qt_alimentos, valor_min) {
        aluno.url_ajax_verifica_aluno = url_verifica_aluno;
        aluno.url_img = url_img;
        aluno.url_ajax_alimentos = url_ajax_alimentos;
        aluno.url_ajax_disciplina = url_ajax_disciplina;
        aluno.url_ajax_turma = url_ajax_turma;
        aluno.url_ajax_quantidade_alunos_turma = url_quantidade;
        aluno.url_ajax_verificacao_liberacao = url_verificacao_liberacao;
        aluno.action = action;
        aluno.qt_min_alimentos = parseInt(qt_alimentos);
        aluno.valor_min_pagamento = parseFloat(valor_min);
//        aluno.verificaHorariosTurma();
        aluno.ini();
    };

    aluno.ini = function() {
        aluno.campo_curso.val('');

        // em caso de alterção de aluno, ou correção no cadastro após enviar a requisição, o servidor exibe os dados 
        // em um container, que deve ser movido para o lugar certo;
        if (aluno.container_turmas_pre_definidas.length == 1) {
            aluno.container_turma.append(aluno.container_turmas_pre_definidas.children()).show();
            aluno.eventExcluirTurmaAluno();
        }

        if (aluno.container_alimentos_pre_definidos.length == 1) {
            aluno.container_alimentos.append(aluno.container_alimentos_pre_definidos.children()).show();
            $(aluno.getIdAlimentosTurma()).show();
            aluno.eventExcluirAlimento($(aluno.getIdAlimentosTurma()).show());
        }

        if (aluno.container_pagamentos_pre_definidos.length == 1) {
            aluno.container_pagamentos.append(aluno.container_pagamentos_pre_definidos.children()).show();
            aluno.eventOpcaoExcluirPagamento();
        }

        if (aluno.action != 3) { //  se não for exclusão
            if (aluno.check_is_responsavel.prop('checked'))
                aluno.check_is_responsavel.parents('td').next().children('div').show().find('input').removeAttr('disabled');

            aluno.campos_data.datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: aluno.url_img,
                changeMonth: true,
                changeYear: true
            });

            aluno.campo_nome_aluno.autocomplete({
                source: aluno.url_ajax_verifica_aluno,
                minLength: 1
            }).data("ui-autocomplete")._renderItem = function(ul, item) {
                var $a = $("<a href='" + item.url + "'></a>").text(item.label);
                return $("<li></li>").append($a).append(item.desc).appendTo(ul);
            };

            aluno.campo_curso.change(function() {
                aluno.container_quantidade_alunos_turma.html('');
                aluno.campo_turma.html('');
                helpers.buscaDisciplinasByCurso(aluno.url_ajax_disciplina, $(this), aluno.campo_disciplina);
            });

            aluno.campo_disciplina.change(function() {
                aluno.container_quantidade_alunos_turma.html('');
                helpers.buscaTurmasByDisciplina(aluno.url_ajax_turma, $(this), aluno.campo_turma, null, true);
            });

            aluno.campo_turma.change(function() {
                aluno.container_quantidade_alunos_turma.html('');
                aluno.getQuantidadeAlunos();
            });

            aluno.btn_incluir_turma.click(function() {
                aluno.verificaLiberacaoTurma();
            });

            aluno.btn_incluir_alimento.click(function() {
                aluno.incrementaAlimentoTurma();
            });

            aluno.btn_incluir_pagamento.click(function() {
                aluno.incrementaPagamentoTurma();
            });

            aluno.btn_atualizar_alimentos.click(function() {
                aluno.buscaAlimentos();
            });

            aluno.select_turma_pagamento.change(function() {
                $(aluno.class_container_alimentos_pagamento).hide(); // esconde todas para mostrar somente a desejada
                aluno.campo_tipo_alimento.val('');
                aluno.campo_quantidade_alimento.val('');
                aluno.campo_valor_pagamento.val('00,00');
                $(aluno.getIdAlimentosTurma()).show();
            });

            aluno.campo_tipo_alimento.change(function() {
                aluno.campo_quantidade_alimento.val('');
            });

            aluno.check_is_responsavel.click(function() {
                helpers.mostraEscondeCheck($(this), $(this).parents('td').next().children('div'), true);
            });

            aluno.btn_enviar.click(function() {
                var quantidade_pagamentos = aluno.container_pagamentos.find('tr').length;
                var turmas_escolhidas = aluno.select_turma_pagamento.find('option').length;

                if (quantidade_pagamentos > 0)
                    quantidade_pagamentos--; // exclui a linha de cabeçalho

                if (turmas_escolhidas != quantidade_pagamentos || quantidade_pagamentos == 0) {
                    exibeMensagem('Você deve incluir ao menos uma turma e registrar o pagamento dela.', 'Cadastro de Aluno');
                    return false;
                }
                return true;
            });
        }
    };
    
    aluno.getNomeTurma = function(campo_turma) {
        var turma_horario;

        if (campo_turma == undefined)
            turma_horario = aluno.select_turma_pagamento.find('option:selected').html();
        else
            turma_horario = aluno.campo_turma.find('option:selected').html();

        var pos = turma_horario.indexOf(' | ');

        return turma_horario.substring(0, --pos);
    };
    
    /**
     * Retorna o identificador do container de alimentos da turma selecionada
     * @returns {String}
     */
    aluno.getIdAlimentosTurma = function() {
        return '#alimentos_' + helpers.retira_acentos(helpers.trim(aluno.getNomeTurma())).toLowerCase();
    };

    /**
     * Retorna o identificador do pagamento da turma selecionada 
     * @returns {String}
     */
    aluno.getClassPagamentoTurma = function() {
        return '.pagamento_' + helpers.retira_acentos(helpers.trim(aluno.getNomeTurma())).toLowerCase();
    };


    aluno.getNameTurmaAluno = function(not_filter) {
        if (not_filter == undefined)
            return helpers.retira_acentos(helpers.trim(aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.getNomeTurma(true))).toLowerCase();
        return aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.campo_turma.find('option:selected').html();
    };

    aluno.getIdTurma = function() {
        return aluno.campo_turma.find('option:selected').val();
    };

    aluno.getIdDisciplina = function() {
        return aluno.campo_disciplina.find('option:selected').val();
    };

    aluno.getQuantidadeTurmas = function() {
        return helpers.parseNumero(aluno.campo_quantidade_turmas.val());
    };

    aluno.addHorarioTurma = function(id_turma) {
        aluno.horarios_turmas_incluidas.push(
                {
                    id: id_turma,
                    horario_inicio: aluno.getHoraInicial(),
                    horario_fim: aluno.getHoraFinal(),
                    data_inicio: aluno.getDataInicial(),
                    data_fim: aluno.getDataFinal()
                }
        );
    };

    aluno.removeHorarioTurma = function(id_turma) {
        for (var i in aluno.horarios_turmas_incluidas) {
            if (aluno.horarios_turmas_incluidas[i].id_turma == id_turma) {
                delete aluno.horarios_turmas_incluidas[i];
                return true;
            }
        }
        return false;
    };

    aluno.getHoraInicial = function() {
        return Date.parse(aluno.campo_turma.find('option:selected').attr('hora_inicio'));
    };

    aluno.getHoraFinal = function() {
        return Date.parse(aluno.campo_turma.find('option:selected').attr('hora_fim'));
    };

    aluno.getDataInicial = function() {
        return helpers.parseDate(aluno.campo_turma.find('option:selected').attr('data_inicio'));
    };

    aluno.getDataFinal = function() {
        return helpers.parseDate(aluno.campo_turma.find('option:selected').attr('data_fim'));
    };


    aluno.verificaHorariosTurma = function() {
        var horario_inicio = aluno.getHoraInicial(),
                horario_fim = aluno.getHoraFinal();

        if (aluno.verificaInterfenciaPeriodosTurma()) {
            for (var i in aluno.horarios_turmas_incluidas) {
                if (// verifica se os horários das turmas interferem uns nos outros
                        (Date.compare(aluno.horarios_turmas_incluidas[i].horario_inicio, horario_inicio) >= 0 &&
                                Date.compare(aluno.horarios_turmas_incluidas[i].horario_fim, horario_fim) <= 0) ||
                        (Date.compare(aluno.horarios_turmas_incluidas[i].horario_inicio, horario_inicio) >= 0 &&
                                Date.compare(aluno.horarios_turmas_incluidas[i].horario_fim, horario_inicio) < 0) ||
                        (Date.compare(aluno.horarios_turmas_incluidas[i].horario_inicio, horario_fim) > 0 &&
                                Date.compare(aluno.horarios_turmas_incluidas[i].horario_fim, horario_fim) <= 0) ||
                        (Date.compare(aluno.horarios_turmas_incluidas[i].horario_inicio, horario_inicio) <= 0 &&
                                Date.compare(aluno.horarios_turmas_incluidas[i].horario_fim, horario_fim) >= 0)) {

                    return false;
                }
            }
        }
        return true;
    };

    aluno.verificaInterfenciaPeriodosTurma = function() {
        var data_inicio = aluno.getDataInicial(),
                data_fim = aluno.getDataFinal();

        for (var i in aluno.horarios_turmas_incluidas) {
            if (// verifica se os períodos das turmas interferem uns nos outros
                    (Date.compare(aluno.horarios_turmas_incluidas[i].data_inicio, data_inicio) >= 0 &&
                            Date.compare(aluno.horarios_turmas_incluidas[i].data_fim, data_fim) <= 0) ||
                    (Date.compare(aluno.horarios_turmas_incluidas[i].data_inicio, data_inicio) >= 0 &&
                            Date.compare(aluno.horarios_turmas_incluidas[i].data_fim, data_inicio) < 0) ||
                    (Date.compare(aluno.horarios_turmas_incluidas[i].data_inicio, data_fim) > 0 &&
                            Date.compare(aluno.horarios_turmas_incluidas[i].data_fim, data_fim) <= 0) ||
                    (Date.compare(aluno.horarios_turmas_incluidas[i].data_inicio, data_inicio) <= 0 &&
                            Date.compare(aluno.horarios_turmas_incluidas[i].data_fim, data_fim) >= 0)) {

                return true;
            }
        }
        return false;
    };


    aluno.getQuantidadeAlunos = function() {
        var id_turma = aluno.getIdTurma();

        if (aluno.container_quantidade_alunos_turma.length > 0 && id_turma.length > 0) {
            $.ajax({
                type: "POST",
                url: aluno.url_ajax_quantidade_alunos_turma,
                dataType: "JSON",
                data: {
                    id_turma: id_turma
                },
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data[0], function(key, value) {
                            if (key.indexOf('count') >= 0)
                                aluno.container_quantidade_alunos_turma.html('Alunos Cadastrados nessa Turma: <b>' + value + '</b>');
                        });
                    }
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            aluno.container_quantidade_alunos_turma.html('');
    };

    aluno.verificaTurmasAluno = function() {
        if (aluno.campo_turma.children().length > 0 && !aluno.container_turma.find('tr').hasClass(aluno.getNameTurmaAluno())) //&& $(option).val() != "")
            return true;

        exibeMensagem('Nenhuma turma foi selecionada ou ela já foi incluída.', 'Inclusão de Turma');
        return false;
    };

    aluno.verificaLiberacaoTurma = function(linha_turma_alterada) {
        if (!aluno.trava_busca_liberacao) {
            aluno.trava_busca_liberacao = true;

            if (aluno.campo_turma.length > 0 && aluno.verificaTurmasAluno()) {
                $.ajax({
                    type: "POST",
                    url: aluno.url_ajax_verificacao_liberacao,
                    dataType: "JSON",
                    data: {
                        id_turma: aluno.getIdTurma(),
                        id_disciplina: aluno.getIdDisciplina(),
                        id_aluno: $('#id_aluno').val()
                    },
                    success: function(liberacao) {
                        var tipo_liberacao = '';

                        if (liberacao.length > 0) {
                            var pre_requisitos = '';
                            for (var i = 0; i < liberacao.length; i++)
                                pre_requisitos += liberacao[i].nome_pre_requisito + ' ';

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
                                        aluno.liberacao_turma = tipo_liberacao;
                                        aluno.incrementaTurma();
                                        aluno.incrementaSelectTurmasAluno();
                                    }
                                }
                            }).html('Aluno não possui pré-requisitos (<b>' + pre_requisitos + '</b>) para cursar essa disciplina. Favor Selecionar uma das opções abaixo.');
                        }
                        else {
                            aluno.liberacao_turma = tipo_liberacao;
                            if (linha_turma_alterada == undefined) {
                                aluno.incrementaTurma();
                                aluno.incrementaSelectTurmasAluno();
                            }
                            else
                                aluno.alteraTurma(linha_turma_alterada);
                        }
                        aluno.trava_busca_liberacao = false;
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
            else
                aluno.trava_busca_liberacao = false;
        }
    };

    aluno.alteraTurma = function(linha_turma) {
        if (linha_turma != undefined) {
            var id_nova_turma = aluno.getIdTurma(), id_turma_antiga = linha_turma.children('input').val();
            var aux_class = linha_turma.attr('class'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma

            $('#alimentos_' + aux_class).attr('id', aluno.getIdAlimentosTurma());
            $('.pagamento_' + aux_class).attr('class', aluno.getClassPagamentoTurma());

            linha_turma.replaceWith('<tr class="' + aluno.getNameTurmaAluno() + '"><input type="hidden" name="turmas[]" value="' + id_nova_turma + '"/><td>' + aluno.campo_curso.find('option:selected').html() + '</td><td>' + aluno.campo_disciplina.find('option:selected').html() + '</td><td>' + aluno.campo_turma.find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_nova_turma + ']" value="' + aluno.liberacao_turma + '"/>' + aluno.liberacao_turma + '</td><td><div class="alterar_turma">Alterar</div><div class="excluir_turma" >Excluir</div></td></tr>');
            aluno.select_turma_pagamento.find('option[value="' + id_turma_antiga + '"]').replaceWith('<option value="' + id_nova_turma + '">' + aluno.getNameTurmaAluno(true) + '</option>');

            aluno.container_campos_escolha_turma.dialog("destroy");
            aluno.container_campo_quantidade_turmas.show();
            aluno.container_btn_incluir_turma.show();
        }
    };

    aluno.incrementaTurma = function() {
        if (!aluno.verificaHorariosTurma())
            exibeMensagem('Já existe uma turma do aluno que interefere no horário dessa turma. Por favor, escolha outra.', 'Inclusão de Turma');

        else if ((aluno.container_turma.find('tr').length - 1) < aluno.getQuantidadeTurmas()) { //exclui a linha de cabeçalho na verificação de turmas inseridas
            var id_turma = aluno.getIdTurma();
            var html = '';

            aluno.addHorarioTurma(id_turma);

            if (aluno.container_turma.children().length == 0) {
                aluno.container_turma.show();
                html = '<tr><th>Curso</th><th>Disciplina</th><th>Turma</th><th>Liberação de Requisitos</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + aluno.getNameTurmaAluno() + '"><input type="hidden" name="turmas[]" value="' + id_turma + '"/><td>' + aluno.campo_curso.find('option:selected').html() + '</td><td>' + aluno.campo_disciplina.find('option:selected').html() + '</td><td>' + aluno.campo_turma.find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_turma + ']" value="' + aluno.liberacao_turma + '"/>' + aluno.liberacao_turma + '</td><td><div class="alterar_turma">Alterar</div><div class="excluir_turma" >Excluir</div></td></tr>';
            aluno.container_turma.append(html);
            aluno.eventAlterarTurmaAluno();
            aluno.eventExcluirTurmaAluno();
        }
        else
            exibeMensagem('O limite de turmas especificado é: <b>' + aluno.getQuantidadeTurmas() + '</b>', 'Inclusão de Turmas');
    };

    aluno.incrementaSelectTurmasAluno = function() {
        aluno.select_turma_pagamento.append('<option value="' + aluno.getIdTurma() + '">' + aluno.getNameTurmaAluno(true) + '</option>');
    };

    aluno.incrementaAlimentoTurma = function() {
        if (aluno.select_turma_pagamento.children().length > 0) {
            var id_container = aluno.getIdAlimentosTurma();
            var container_alimentos_turma = $('' + id_container);
            var tipo_alimento_option = aluno.campo_tipo_alimento.find('option:selected');
            var quantidade = helpers.parseNumero(aluno.campo_quantidade_alimento.val());

            $(aluno.class_container_alimentos_pagamento).hide();

            if (aluno.campo_tipo_alimento.children().length > 0
                    && quantidade > 0
                    && $(tipo_alimento_option).val() != ""
                    && !$(container_alimentos_turma).find('tr').hasClass($(tipo_alimento_option).val())
                    && !aluno.container_pagamentos.find('tr').hasClass(aluno.getClassPagamentoTurma().replace('.', ''))) {

                if ($(container_alimentos_turma).length == 0) {
                    aluno.container_alimentos.append('<table class="ali_pag form_incrementa" id="' + id_container.replace('#', '') + '" cellpadding="0" cellspacing="0"><tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr></table>');
                    container_alimentos_turma = aluno.container_alimentos.find(id_container);
                }

                if ($(container_alimentos_turma).children().length == 0)
                    $(container_alimentos_turma).append('<tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr>');

                $(container_alimentos_turma).append('<tr class="' + $(tipo_alimento_option).val() + '"><input type="hidden" name="alimentos[' + aluno.getIdTurma() + '][' + $(tipo_alimento_option).val() + ']" value="' + quantidade + '"/><td>' + $(tipo_alimento_option).html() + '</td><td class="quantidade_alimento_turma">' + quantidade + '</td><td><div class="excluir_alimento" >Excluir</div></td></tr>');

                aluno.eventExcluirAlimento();
                $(container_alimentos_turma).show();
            }
            else
                exibeMensagem('O alimento já foi incluído ou nenhum foi selecionado. Verifique também se a quantidade de alimentos foi preenchida corretamente (ex: <b>"0.5"</b>, <b>"1"</b>).', 'Inclusão de Alimentos');

        }
        else
            exibeMensagem('Nenhuma turma foi incluida.', 'Inclusão de Alimentos');
    };

    aluno.incrementaPagamentoTurma = function() {
        var option = aluno.select_turma_pagamento.find('option:selected');

        if ($(option).length > 0) {
            var id_turma = $(option).val();
            var pagamento_class = aluno.getClassPagamentoTurma();//controle.retira_acentos(controle.trim($(option).html())).toLowerCase();
            var valor_pago = helpers.parseNumero(aluno.campo_valor_pagamento.val());
            var total_alimentos = 0.0;

            //soma quantidades de alimentos
            $(aluno.getIdAlimentosTurma()).find('.quantidade_alimento_turma').each(function() {
                var quantidade_alimento = helpers.parseNumero($(this).html());

                if (quantidade_alimento != -1)
                    total_alimentos += quantidade_alimento;
                else
                    total_alimentos = quantidade_alimento;
            });

            if (aluno.select_turma_pagamento.children().length > 0
                    && total_alimentos != -1
                    && valor_pago != -1
                    && !aluno.container_pagamentos.find('tr').hasClass(pagamento_class.replace('.', ''))
                    && $(option).val() != "") {

                var html = '';
                var situacao = ((total_alimentos >= aluno.qt_min_alimentos && valor_pago >= aluno.valor_min_pagamento) ? 'Liberado' : 'Pendente');

                if (aluno.container_pagamentos.children().length == 0) {
                    aluno.container_pagamentos.show();
                    html = '<tr><th>Disciplina - Turma</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Situação</th><th>Opções</th></tr>';
                }

                html += '<tr class="' + pagamento_class.replace('.', '') + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + valor_pago + '"/><td>' + $(option).html() + '</td><td class="valor_pago">' + valor_pago + '</td><td class="quant_alimento">' + total_alimentos + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="' + situacao + '"/>' + situacao + '</td><td><div class="excluir_pagamento" >Excluir</div></td></tr>';
                aluno.container_pagamentos.append(html);
                aluno.eventOpcaoExcluirPagamento();

            }
            else
                exibeMensagem('O pagamento dessa turma já foi inserido, Nesse caso, se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');
        }
        else
            exibeMensagem('Inclua primeiro a turma', 'Registro de Pagamento');
    };


    aluno.eventAlterarTurmaAluno = function() {
        aluno.container_turma.find('.alterar_turma').click(function() {
            var linha_turma = $(this).parents('tr'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma

            aluno.container_campo_quantidade_turmas.hide();
            aluno.container_btn_incluir_turma.hide();

            aluno.container_campos_escolha_turma.dialog({
                dialogClass: "no-close",
                modal: true,
                resizable: false,
                draggable: false,
                title: 'Alterar Turma',
                closeOnEscape: false,
                width: 600,
                buttons: {
                    Ok: function() {
                        aluno.verificaLiberacaoTurma(linha_turma);
                    },
                    Cancelar: function() {
                        $(this).dialog("destroy");
                        aluno.container_campo_quantidade_turmas.show();
                        aluno.container_btn_incluir_turma.show();
                    }
                }
            });
        });
    };

    aluno.eventExcluirTurmaAluno = function() {
        aluno.container_turma.find('.excluir_turma').click(function() {
            var confirma_exclusao = confirm('Deseja realmente retirar o aluno dessa turma?');

            if (confirma_exclusao) {
                var aux_class = $(this).parents('tr').attr('class'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma
                var table = $(this).parents('table');
                var id_turma = $(this).parents('tr').children('input').val();

                aluno.removeHorarioTurma(id_turma);

                aluno.select_turma_pagamento.find('option').each(function() {
                    if ($(this).val() == id_turma)
                        $(this).remove();
                });

                $('#alimentos_' + aux_class).remove();
                $('.pagamento_' + aux_class).remove();

                if (aluno.container_pagamentos.find('tr').length == 1)
                    aluno.container_pagamentos.html('').hide();

                if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                    $(this).parents('tr').remove();

                else
                    $(table).html('').hide();
            }
        });
    };

    aluno.eventExcluirAlimento = function() {
        $(aluno.getIdAlimentosTurma()).find('.excluir_alimento').click(function() {
            var table = $(this).parents('table');

            if (!aluno.container_pagamentos.find('tr').hasClass(aluno.getClassPagamentoTurma().replace('.', ''))) { // procura se já tem um pagamento registrado para a turma, se houver a remoção não é realizada
                if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                    $(this).parents('tr').remove();
                else
                    $(table).html('').hide();
            }
            else
                exibeMensagem('Para realizar a exclusão, você deve cancelar o pagamento dessa turma primeiro.', 'Exlusão de Alimentos');
        });
    };

    aluno.eventOpcaoExcluirPagamento = function() {
        $('.excluir_pagamento').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });
    };

    aluno.buscaAlimentos = function() {
        if (aluno.campo_tipo_alimento.length > 0) {
            $.ajax({
                type: "POST",
                url: aluno.url_ajax_alimentos,
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

                    aluno.campo_tipo_alimento.html(html);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }
        else
            aluno.campo_tipo_alimento.html('');
    };


    return {
        ini: aluno.setValues
    };
})();

