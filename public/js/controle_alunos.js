/**
 * Controle do gerenciamento de Alunos
 * @returns {undefined}
 */
(function() {
    var aluno = {
        container_turma: $('#turmas_aluno'),
        container_pagamentos: $('#table_pagamentos_turmas'),
        container_alimentos: $('#container_alimentos'),
        btn_incluir_turma: $('#incluir_turma'),
        btn_incluir_pagamento: $('#registrar_pagamento'),
        btn_incluir_alimento: $('#incluir_alimento'),
        btn_atualizar_alimentos: $('#atualizar_alimentos'),
        btn_enviar: $('#enviar'),
        //btn_cancelar: $('#cancelar'),
        ///btn_add_alimento: $('#add_alimento'),
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
        liberacao_turma: null
    };

    aluno.aetValues = function(url_verifica_aluno, url_img, url_ajax_alimentos, url_ajax_disciplina, url_ajax_turma, url_quantidade, url_verificacao_liberacao, action, qt_alimentos, valor_min) {
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
    };

    aluno.ini = function() {
        aluno.campo_curso.val('');

        if (aluno.container_turmas_pre_definidas.length == 1) {
            aluno.container_turma.append(aluno.container_turmas_pre_definidas.children()).show();
            aluno.eventExcluirTurmaAluno();
        }

        if (aluno.container_alimentos_pre_definidos.length == 1) {
            aluno.container_alimentos.append(aluno.container_alimentos_pre_definidos.children()).show();
            //var aux = controle.retira_acentos(controle.trim($(pagamento_turma).find('option:selected').html())).toLowerCase();
            //console.log(aux);
            //$('#alimentos_' + aux).show();
            $(aluno.getIdAlimentosTurma()).show();
            aluno.eventExcluirAlimento($(aluno.getIdAlimentosTurma()).show());
        }

        if (aluno.container_pagamentos_pre_definidos.length == 1) {
            aluno.container_pagamentos.append(aluno.container_pagamentos_pre_definidos.children()).show();
            aluno.eventOpcaoExcluirPagamento();
        }

        if (aluno.action != 3) {
            if (aluno.check_is_responsavel.prop('checked'))
                aluno.check_is_responsavel.parents('td').next().children('div').show().find('input').removeAttr('disabled');

            aluno.campos_data.datepicker({
                buttonText: "Clique para selecionar uma data",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: url_img,
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
                aluno.quantidade_alunos_turma.html('');
                aluno.campo_turma.html('');
                helpers.buscaDisciplinasByCurso(aluno.url_ajax_disciplina, $(this), aluno.campo_disciplina);
            });

            aluno.campo_disciplina.change(function() {
                aluno.quantidade_alunos_turma.html('');
                helpers.buscaTurmasByDisciplina(aluno.url_ajax_turma, $(this), aluno.campo_turma);
            });

            aluno.campo_turma.change(function() {
                aluno.quantidade_alunos_turma.html('');
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

    aluno.getIdAlimentosTurma = function() {
        return '#alimentos_' + helpers.retira_acentos(helpers.trim(aluno.select_turma_pagamento.find('option:selected').html())).toLowerCase();
    };

    aluno.getClassPagamentoTurma = function() {
        return '.pagamento_' + helpers.retira_acentos(helpers.trim(aluno.select_turma_pagamento.find('option:selected').html())).toLowerCase();
    };

    aluno.getNameTurmaAluno = function() {
        return helpers.retira_acentos(helpers.trim(aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.campo_turma.find('option:selected').html())).toLowerCase();
    };

    aluno.getIdTurma = function() {
        aluno.campo_turma.find('option:selected').val();
    };

    aluno.getIdDisciplina = function() {
        aluno.campo_disciplina.find('option:selected').val();
    };

    aluno.incrementaTurma = function() {
        //var aux = $(disciplina).find('option:selected').html() + ' - ' + $(turma).find('option:selected').html();
        //var option = aluno.campo_turma.find('option:selected');
        var id_turma = aluno.getIdTurma();
        var html = '';

        if (aluno.container_turma.children().length == 0) {
            aluno.container_turma.show();
            html = '<tr><th>Curso</th><th>Disciplina</th><th>Turma</th><th>Liberação de Requisitos</th><th>Opções</th></tr>';
        }

        html += '<tr class="' + aluno.getNameTurmaAluno() + '"><input type="hidden" name="turmas[]" value="' + id_turma + '"/><td>' + aluno.campo_curso.find('option:selected').html() + '</td><td>' + aluno.campo_disciplina.find('option:selected').html() + '</td><td>' + aluno.campo_turma.find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_turma + ']" value="' + aluno.liberacao_turma + '"/>' + aluno.liberacao_turma + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
        aluno.container_turma.append(html);
        aluno.eventExcluirTurmaAluno();
    };

    aluno.incrementaTurmasAluno = function() {
        //var aux = $(disciplina).find('option:selected').html() + ' - ' + $(turma).find('option:selected').html();
        aluno.select_turma_pagamento.append('<option value="' + aluno.getIdTurma() + '">' + aluno.getNameTurmaAluno() + '</option>');
    };

    aluno.incrementaAlimentoTurma = function() {
        if (aluno.select_turma_pagamento.children().length > 0) {
            //var turma_option = $(turma).find('option:selected');
            //var aux_id_turma = controle.retira_acentos(controle.trim($(turma_option).html())).toLowerCase();
            var id_container = aluno.getNameTurmaAluno();
            var container_alimentos_turma = $('' + id_container);
            var tipo_alimento_option = aluno.campo_tipo_alimento.find('option:selected');
            var quantidade = helpers.parseNumero(aluno.campo_quantidade_alimento.val());

            $(aluno.class_container_alimentos_pagamento).hide();

            if (aluno.campo_tipo_alimento.children().length > 0
                    && quantidade > 0
                    && $(tipo_alimento_option).val() != ""
                    && !$(container_alimentos_turma).find('tr').hasClass($(tipo_alimento_option).val())) {

                if ($(container_alimentos_turma).length == 0) {
                    aluno.container_alimentos.append('<table class="ali_pag form_incrementa" id="' + id_container.replace('#', '') + '" cellpadding="0" cellspacing="0"><tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr></table>');
                    container_alimentos_turma = aluno.container_alimentos.find(id_container);
                }

                if ($(container_alimentos_turma).children().length == 0)
                    $(container_alimentos_turma).append('<tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr>');

                $(container_alimentos_turma).append('<tr class="' + $(tipo_alimento_option).val() + '"><input type="hidden" name="alimentos[' + aluno.getIdTurma() + '][' + $(tipo_alimento_option).val() + ']" value="' + quantidade + '"/><td>' + $(tipo_alimento_option).html() + '</td><td class="quantidade_alimento_turma">' + quantidade + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>');

                //          aluno.atualizaAlimentosPagamento(quantidade);
                aluno.eventExcluirAlimento();
            }
            else
                exibeMensagem('O alimento já foi incluído ou nenhum foi selecionado. Verifique também se a quantidade de alimentos foi preenchida corretamente (ex: <b>"0.5"</b>, <b>"1"</b>).', 'Inclusão de Alimentos');

            $(container_alimentos_turma).show();
        }
        else
            exibeMensagem('Nenhuma turma foi incluida.', 'Inclusão de Alimentos');
    };

    /*aluno.atualizaAlimentosPagamento = function(quantidade) {
     var linha_pagamento = $(aluno.getClassPagamentoTurma());
     
     if ($(linha_pagamento).length > 0) {
     var container_valor = $(linha_pagamento).find('.quant_alimento');
     
     var valor_pago = helpers.parseNumero($(linha_pagamento).find('.valor_pago').html());
     var valor_atual = helpers.parseNumero($(container_valor).html());
     
     var total_alimentos = valor_atual + quantidade;
     
     var situacao = ((total_alimentos >= aluno.qt_min_alimentos && valor_pago >= aluno.valor_min_pagamento) ? 'Liberado' : 'Pendente');
     var situacao_container = $(linha_pagamento).find('.situacao');
     var id_turma = $(situacao_container).find('input').attr('name');
     
     $(container_valor).html(total_alimentos);// atualiza o total de alimentos e a situação 
     $(situacao_container).html('<input type="hidden" name="' + id_turma + '" value="' + situacao + '"/>' + situacao);
     }
     };*/

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

                html += '<tr class="pagamento_' + pagamento_class.replace('.', '') + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + valor_pago + '"/><td>' + $(option).html() + '</td><td class="valor_pago">' + valor_pago + '</td><td class="quant_alimento">' + total_alimentos + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="' + situacao + '"/>' + situacao + '</td><td><div class="excluir_geral" >Excluir</div></td></tr>';
                aluno.container_pagamentos.append(html);
                aluno.eventOpcaoExcluirPagamento();
            }
            else
                exibeMensagem('O pagamento dessa turma já foi inserido, Nesse caso, se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');
        }
        else
            exibeMensagem('Inclua primeiro a turma', 'Registro de Pagamento');
    };

    aluno.eventExcluirTurmaAluno = function() {
        aluno.container_turma.find('.excluir_geral').click(function() {
            var aux_class = $(this).parents('tr').attr('class'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma
            var table = $(this).parents('table');
            //var table_pagamento = $('.pagamento_' + aux_class).parents('table');
            var id_turma = $(this).parents('tr').children('input').val();

            aluno.select_turma_pagamento.find('option').each(function() {
                if ($(this).val() == id_turma)
                    $(this).remove();
            });

            $('#alimentos_' + aux_class).remove();
            $('.pagamento_' + aux_class).remove();

            /*if ($(table_pagamento).find('tr').length == 1)
             $(table_pagamento).html('').hide();*/

            if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                $(this).parents('tr').remove();

            else
                $(table).html('').hide();

        });
    };

    aluno.eventExcluirAlimento = function() {
        $(aluno.getIdAlimentosTurma()).find('.excluir_geral').click(function() {
            //var quantidade = helpers.parseNumero($(this).parents('tr').find('.quantidade_alimento_turma').html());
            var table = $(this).parents('table');
            var id_container_alimentos = $(table).attr('id');

            if (!aluno.container_pagamentos.find('tr').hasClass(id_container_alimentos.replace('alimentos_', ''))) { // procura se já tem um pagamento registrado para a turma, se houver a remoção não é realizada
                //aluno.atualizaAlimentosPagamento(quantidade * (-1));

                if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                    $(this).parents('tr').remove();
                else
                    $(table).html('').hide();
            }
            else
                exibeMensagem('Para realizar a exclusão, você deve cancelar o pagamento dessa turma primeiro.');
        });
    };

    aluno.eventOpcaoExcluirPagamento = function() {
        $('.excluir_geral').click(function() {
            var table = $(this).parents('table');

            if ($(table).find('tr').length > 2)
                $(this).parents('tr').remove();
            else
                $(table).html('').hide();
        });
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
        //var aux = aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.campo_turma.find('option:selected').html();
        //var option = aluno.container_turma.find('option:selected');

        if (aluno.campo_turma.children().length > 0 && !aluno.container_turma.find('tr').hasClass(helpers.retira_acentos(helpers.trim(aluno.getNameTurmaAluno())).toLowerCase())) //&& $(option).val() != "")
            return true;

        exibeMensagem('Nenhuma turma foi selecionada ou ela já foi incluída.', 'Inclusão de Turma');
        return false;
    };

    aluno.verificaLiberacaoTurma = function() {
        if (!aluno.trava_busca_liberacao) {
            aluno.trava_busca_liberacao = true;

            if (aluno.campo_turma.length > 0 && aluno.verificaTurmasAluno()) {
                $.ajax({
                    type: "POST",
                    url: url,
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
                            aluno.incrementaTurma();
                            aluno.incrementaSelectTurmasAluno();
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
})();

