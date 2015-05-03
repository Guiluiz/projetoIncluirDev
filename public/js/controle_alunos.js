/**
 * Controle do gerenciamento de Alunos
 * @returns {undefined}
 */
var controle_aluno = (function() {
    var aluno = {
        container_turmas_aluno: $('#turmas_aluno'), // container das turmas escolhidas para o aluno
        container_pagamentos_aluno: $('#table_pagamentos_turmas'), // container dos pagamentos registrados
        container_alimentos_turmas: $('#container_alimentos_turmas'), //container das tabelas de alimentos das turmas selecionadas

        container_opcoes_turmas_aluno: $('#container_turmas_escolhidas'),
        container_pagamentos_registrados: $('#container_pagamentos_registrados'),
        container_alimento: $('#container_alimentos'), // container dos campos de alimento e da tabela de alimentos inseridos
        container_pagamento: $('#container_pagamentos'), // container dos campos de pagamento 

        // necessário para esconder a tabela de gerenciamento após o registro de pagamento
        table_gerenciamento_alimentos: $('#table_gerenciamento_alimentos'),
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
        select_condicao_matricula: $('#condicao_matricula'),
        campo_num_recibo: $('#num_recibo'),
        select_tipo_isencao_pendencia: $('#tipo_isencao_pendencia'),
        container_quantidade_alunos_turma: $('#quant_alunos_cadastrados'), // container da área onde a quantidade de alunos da turma escolhida é especificada

        container_turmas_pre_definidas: $('#opcoes_escolhidas'), // Container com as escolhas do usuário já pré definidas
        container_alimentos_pre_definidos: $('#alimentos_escolhidos'), // Container com opções de alimentos dos pagamentos das turmas do usuário já pré definidas
        container_pagamentos_pre_definidos: $('#opcoes_escolhidas_pagamentos'), // Container com os pagamentos das turmas do usuário já pré definidas

        container_campo_quantidade_turmas: $('.quantidade_turmas'), //container de campos que são escondidos na alteração de turma
        container_btn_incluir_turma: $('.incluir_turma'),
        container_campos_escolha_turma: $('#busca_turmas'),
        container_campo_isencao_pendencia: $('#container_tipo_isencao_pendencia'),
        container_campo_condicao_matricula: $('#container_condicao_matricula'),
        // mesmo tendo o container é necessário manipular os campos separados pois no caso de total, somente alguns ficam dos campos ficam escondidos
        container_recibo: $('#container_recibo'),
        container_botao_pagamento: $('#container_btn_registro_pagamento'),
        container_valor_pagamento: $('#container_valor_pago'),
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
        horarios_turmas_incluidas: new Array(), // utilizado para verificar se há inconsistências de horários nas turmas que serão escolhidas pelo usuário
        turmas_condicoes: new Array() // turma que está sendo modificada no momento
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
        aluno.ini();
    };

    aluno.ini = function() {
        aluno.campo_curso.val('');

        // em caso de alterção de aluno, ou correção no cadastro após enviar a requisição, o servidor exibe os dados 
        // em um container, que deve ser movido para o lugar certo;

        if (aluno.container_turmas_pre_definidas.length == 1) {
            aluno.container_turmas_aluno.append(aluno.container_turmas_pre_definidas.children()).show();

            aluno.campo_quantidade_turmas.val(aluno.container_turmas_aluno.find('tr').length - 1);
            aluno.eventExcluirTurmaAluno();
            aluno.eventAlterarTurmaAluno();

            aluno.container_opcoes_turmas_aluno.show();
            aluno.container_campo_condicao_matricula.hide(); // nesse caso pagamento já foi registrado, nâo é necessário exibir o campo de condição

            //inclui os horários das turmas para comparação

            $('.turma_aluno').each(function() {
                aluno.addHorarioAlteracaoTurma($(this).attr('id'),
                        Date.parse($(this).attr('hora_inicio')),
                        Date.parse($(this).attr('hora_fim')),
                        helpers.parseDate($(this).attr('data_inicio')),
                        helpers.parseDate($(this).attr('data_fim')))
            });
        }

        if (aluno.container_alimentos_pre_definidos.length == 1) {
            aluno.container_alimentos_turmas.append(aluno.container_alimentos_pre_definidos.children()).show();
            aluno.eventExcluirAlimento();

            // exibe o container para mostrar os alimentos selecionados, contudo esconde os campos pois os pagamentos já foram registrados
            aluno.container_alimento.show();
            aluno.table_gerenciamento_alimentos.hide();
            aluno.container_alimentos_turmas.show();

            //Exibe a tabela de alimentos da turma indicada
            $(aluno.getIdAlimentosTurma()).show();
        }

        if (aluno.container_pagamentos_pre_definidos.length == 1) {
            aluno.container_pagamentos_aluno.append(aluno.container_pagamentos_pre_definidos.children()).show();
            aluno.eventOpcaoExcluirPagamento();

            aluno.container_pagamentos_registrados.show();
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

            aluno.select_condicao_matricula.change(function() {
                aluno.gerenciaCamposCondicaoMatricula(true);
            });

            aluno.select_tipo_isencao_pendencia.change(function() {
                aluno.gerenciaTipoIsencaoPendencia();
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
                aluno.gerenciaMudancaTurmaAluno();
            });

            aluno.campo_tipo_alimento.change(function() {
                aluno.campo_quantidade_alimento.val('');
            });

            aluno.check_is_responsavel.click(function() {
                helpers.mostraEscondeCheck($(this), $(this).parents('td').next().children('div'), true);
            });

            aluno.btn_enviar.click(function() {
                var quantidade_pagamentos = aluno.container_pagamentos_aluno.find('tr').length;
                var turmas_escolhidas = aluno.select_turma_pagamento.find('option').length;

                if (quantidade_pagamentos > 0)
                    quantidade_pagamentos--; // exclui a linha de cabeçalho

                if (turmas_escolhidas != quantidade_pagamentos) {
                    exibeMensagem('Se inseriu uma turma você deve registrar o pagamento dela.', 'Cadastro de Aluno');
                    return false;
                }

                if (aluno.campo_quantidade_turmas.val() != quantidade_pagamentos) {
                    exibeMensagem('Você não inseriu o total de turmas indicado.', 'Cadastro de Aluno');
                    return false;
                }
                return true;
            });
        }
    };

    /**
     * Retorna o nome da turma, sem o horário dela. Utilizado para saber nomes de tabelas de pagamentos e de alimentos de uma determinada turma
     * @param bool campo_turma Indica a busca será feita no select de turmas do aluno, ou o select de turmas da disciplina selecionada 
     * @returns string
     */

    aluno.getNomeTurma = function(campo_turma) {
        var turma_horario;

        if (campo_turma == undefined)
            turma_horario = aluno.select_turma_pagamento.find('option:selected').html();
        else
            turma_horario = aluno.campo_turma.find('option:selected').html();

        var pos = turma_horario.indexOf(' | ');

        return turma_horario.substring(0, pos);
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


    /**
     * Retorna o nome da turma do aluno junto com a disciplina  
     * @param {type} not_filter Indica se o retorno vai ser fitrado ou não (filtrado é utilizado como indicador dos pagamentos e alimentos da turma)
     * @returns {String}
     */
    aluno.getNameTurmaAluno = function(not_filter) {
        if (not_filter == undefined)
            return helpers.retira_acentos(helpers.trim(aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.getNomeTurma(true))).toLowerCase();

        return aluno.campo_disciplina.find('option:selected').html() + ' - ' + aluno.campo_turma.find('option:selected').html();
    };

    /**
     * retorna o id da turma indicada no campo de turmas da disciplina selecionada
     * @returns {unresolved}
     */
    aluno.getIdTurma = function() {
        return aluno.campo_turma.find('option:selected').val();
    };

    aluno.getIdDisciplina = function() {
        return aluno.campo_disciplina.find('option:selected').val();
    };

    aluno.getCondicaoMatricula = function() {
        return aluno.select_condicao_matricula.find('option:selected').val();
    };

    aluno.getRecibo = function() {
        return aluno.campo_num_recibo.val();
    };

    aluno.getTipoIsencaoPendencia = function() {
        return aluno.select_tipo_isencao_pendencia.find('option:selected').val();
    };

    aluno.gerenciaCamposCondicaoMatricula = function(remover_conteudo) {
        //if (!aluno.verificaExistenciaPagamento()) { 
        aluno.incrementaCondicaoTurmas();

        if (remover_conteudo != undefined) {
            $(aluno.getClassPagamentoTurma()).remove();
            $(aluno.getIdAlimentosTurma()).remove();
        }

        switch (aluno.getCondicaoMatricula()) {
            case '1': // normal
                aluno.container_alimento.find('input,select,button').removeAttr('disabled');
                aluno.container_pagamento.find('input,select,button').removeAttr('disabled');

                aluno.table_gerenciamento_alimentos.show();
                aluno.container_pagamentos_aluno.show();

                aluno.container_campo_isencao_pendencia.hide().find('select').attr('disabled', 'disabled');

                aluno.container_recibo.show();
                aluno.container_valor_pagamento.show();
                aluno.container_botao_pagamento.show();

                aluno.container_alimento.show();
                aluno.container_pagamento.show();
                break;

            case '2':
            case '5':// isento parcial e pendente parcial
                aluno.container_alimento.hide().find('input,select,button').attr('disabled', 'disabled');
                aluno.container_pagamento.hide().find('input,select,button').attr('disabled', 'disabled');

                aluno.container_campo_isencao_pendencia.find('select').removeAttr('disabled').val('');
                aluno.container_campo_isencao_pendencia.show();
                break;

            case '3':
            case '4': // isento total e pendente total
                aluno.container_alimento.hide().find('input,select,button').attr('disabled', 'disabled');
                aluno.container_campo_isencao_pendencia.hide().find('select').attr('disabled', 'disabled');

                aluno.container_botao_pagamento.find('button').removeAttr('disabled');
                aluno.container_botao_pagamento.show();

                aluno.container_valor_pagamento.hide().find('input').attr('disabled', 'disabled');
                aluno.container_recibo.hide().find('select').attr('disabled', 'disabled');

                aluno.container_pagamento.show();
                break;

            default:
                aluno.container_campo_condicao_matricula.find('select').removeAttr('disabled');
                aluno.container_campo_condicao_matricula.show();
                aluno.container_pagamento.find('input,select,button').removeAttr('disabled');
                aluno.container_alimento.hide().find('input,select,button').attr('disabled', 'disabled');
                aluno.container_campo_isencao_pendencia.hide().find('select').attr('disabled', 'disabled');

        }
    };

    aluno.gerenciaTipoIsencaoPendencia = function() {
        if (aluno.select_tipo_isencao_pendencia.val().length > 0) {
            aluno.container_alimento.find('input,select,button').removeAttr('disabled');
            aluno.container_pagamento.find('input,select,button').removeAttr('disabled');

            aluno.table_gerenciamento_alimentos.show();

            aluno.container_recibo.show();
            aluno.container_valor_pagamento.show();
            aluno.container_botao_pagamento.show();

            aluno.container_alimento.show();
            aluno.container_pagamento.show();
        }
    };

    aluno.gerenciaMudancaTurmaAluno = function() {
        $(aluno.class_container_alimentos_pagamento).hide(); // esconde todas para mostrar somente a desejada
        $(aluno.getIdAlimentosTurma()).show();

        if (aluno.verificaExistenciaPagamento()) {
            aluno.container_campo_isencao_pendencia.hide().find('select').attr('disabled', 'disabled').val('');
            aluno.container_campo_condicao_matricula.hide().find('select').attr('disabled', 'disabled').val('');

            aluno.table_gerenciamento_alimentos.hide().find('select,input,button').attr('disabled', 'disabled');
            aluno.container_pagamento.hide();

            aluno.container_pagamentos_registrados.show();
        }
        else {
            aluno.select_condicao_matricula.val(aluno.getCondicaoTurma(aluno.select_turma_pagamento.find('option:selected').val()));
            aluno.campo_tipo_alimento.val(aluno.getTipoIsencaoPendenciaTurma(aluno.select_turma_pagamento.find('option:selected').val()));

            aluno.campo_quantidade_alimento.val('');
            aluno.campo_num_recibo.val('');
            aluno.campo_valor_pagamento.val('00,00');

            aluno.gerenciaCamposCondicaoMatricula();
            aluno.gerenciaTipoIsencaoPendencia();

        }
    };

    /**
     * Retorna a quantidade de turmas em que o aluno pode ser matriculado 
     * @returns {Number}
     */
    aluno.getQuantidadeTurmas = function() {
        return helpers.parseNumero(aluno.campo_quantidade_turmas.val());
    };

    /**
     * Adiciona o horário da turma do aluno, utilizado para fazer verificação se há aulas que interferem em seus horários
     * @param {type} id_turma
     * @returns {undefined}
     */
    aluno.addHorarioTurma = function() {
        aluno.horarios_turmas_incluidas.push(
                {
                    id_turma: aluno.getIdTurma(),
                    horario_inicio: aluno.getHoraInicial(),
                    horario_fim: aluno.getHoraFinal(),
                    data_inicio: aluno.getDataInicial(),
                    data_fim: aluno.getDataFinal()
                }
        );
    };

    /**
     * Adiciona o horário de uma turma do aluno, já incluída e salva. Utilizado para fazer verificação se há aulas que interferem em seus horários
     * @param {type} id_turma
     * @returns {undefined}
     */
    aluno.addHorarioAlteracaoTurma = function(id, hora_inicial, hora_final, data_inicial, data_final) {
        aluno.horarios_turmas_incluidas.push(
                {
                    id_turma: id,
                    horario_inicio: hora_inicial,
                    horario_fim: hora_final,
                    data_inicio: data_inicial,
                    data_fim: data_final
                }
        );
    };

    /**
     * Remove o horário da turma do aluno, utilizado quando o aluno é retirado de uma turma
     * @param {type} id_turma
     * @returns {Boolean}
     */
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

    aluno.verificaHorariosTurma = function(exclude) {
        var horario_inicio = aluno.getHoraInicial(),
                horario_fim = aluno.getHoraFinal();

        if (aluno.verificaInterfenciaPeriodosTurma(exclude)) {
            for (var i in aluno.horarios_turmas_incluidas) {
                if (aluno.horarios_turmas_incluidas[i].id_turma != exclude) {
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
        }
        return true;
    };

    aluno.verificaInterfenciaPeriodosTurma = function(exclude) {
        var data_inicio = aluno.getDataInicial(),
                data_fim = aluno.getDataFinal();

        for (var i in aluno.horarios_turmas_incluidas) {
            if (aluno.horarios_turmas_incluidas[i].id_turma != exclude) {
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

    /**
     * Verifica se a turma selecionada já foi incluída para o aluno
     * @returns {Boolean}
     */
    aluno.verificaTurmasAluno = function() {
        if (aluno.campo_turma.children().length > 0 && aluno.getIdTurma() != "" && !aluno.container_turmas_aluno.find('tr').hasClass(aluno.getNameTurmaAluno())) //&& $(option).val() != "")
            return true;

        exibeMensagem('Nenhuma turma foi selecionada ou ela já foi incluída.', 'Inclusão de Turma');
        return false;
    };

    aluno.verificaExistenciaPagamento = function() {
        if (aluno.container_pagamentos_aluno.find('tr').hasClass(aluno.getClassPagamentoTurma().replace('.', ''))) // procura se já tem um pagamento registrado para a turma, se houver a remoção não é realizada
            return true;

        return false;
    };

    /**
     * Verifica se a turma a ser inserida/alterada é válida
     * @param {type} linha_turma_alterada Indica a linha da tabela que contém as informações da turma a ser alterada. Em caso de cadastro o parâmetro é nulo
     * @returns {undefined}
     */
    aluno.verificaLiberacaoTurma = function(linha_turma_alterada) {
        if (!aluno.trava_busca_liberacao) {
            aluno.trava_busca_liberacao = true;

            if (aluno.verificaTurmasAluno()) {
                $.ajax({
                    type: "POST",
                    url: aluno.url_ajax_verificacao_liberacao,
                    dataType: "JSON",
                    data: {
                        id_turma: aluno.getIdTurma(),
                        id_disciplina: aluno.getIdDisciplina(),
                        id_aluno: $('#id_aluno').val()
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
                                        aluno.liberacao_turma = tipo_liberacao;

                                        if (linha_turma_alterada == undefined)
                                            aluno.incrementaTurma();
                                        else
                                            aluno.alteraTurma(linha_turma_alterada);

                                    }
                                }
                            }).html('Aluno não possui pré-requisitos (<b>' + pre_requisitos + '</b>) para cursar essa disciplina. Favor Selecionar uma das opções abaixo.');
                        }
                        else {
                            aluno.liberacao_turma = tipo_liberacao;
                            if (linha_turma_alterada == undefined)
                                aluno.incrementaTurma();
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


    /**
     * Faz a alteração da turma, mantendo os pagamentos e alimentos inseridos para a turma
     * @param {type} linha_turma
     * @returns {undefined}
     */
    aluno.alteraTurma = function(linha_turma) {
        if (linha_turma != undefined) {
            var id_turma_antiga = linha_turma.children('input').val();

            if (!aluno.verificaHorariosTurma(id_turma_antiga))
                exibeMensagem('Já existe uma turma do aluno que interfere no horário dessa turma. Por favor, escolha outra.', 'Alteração de Turma');

            else {
                var id_nova_turma = aluno.getIdTurma();
                var aux_class = linha_turma.attr('class'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma
                var linha_pagamento = $('.pagamento_' + aux_class);

                linha_turma.replaceWith('<tr class="' + aluno.getNameTurmaAluno() + '"><input type="hidden" name="turmas[]" value="' + id_nova_turma + '"/><td>' + aluno.campo_curso.find('option:selected').html() + '</td><td>' + aluno.campo_disciplina.find('option:selected').html() + '</td><td>' + aluno.campo_turma.find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_nova_turma + ']" value="' + aluno.liberacao_turma + '"/>' + aluno.liberacao_turma + '</td><td><div class="alterar_turma">Alterar</div><div class="excluir_turma" >Excluir</div></td></tr>');
                aluno.select_turma_pagamento.find('option[value="' + id_turma_antiga + '"]').replaceWith('<option value="' + id_nova_turma + '">' + aluno.getNameTurmaAluno(true) + '</option>');

                aluno.removeHorarioTurma(id_turma_antiga);
                aluno.removeCondicaoTurma(id_turma_antiga);

                aluno.addHorarioTurma();
                aluno.incrementaCondicaoTurmas();

                $('#alimentos_' + aux_class).attr('id', aluno.getIdAlimentosTurma()); // altera os alimentos para a nova turma especificada

                linha_pagamento.find('.nome_turma').text(aluno.getNameTurmaAluno(true)); // altera os nomes e os campos que armazenam dados da turma para a nova especificada
                linha_pagamento.attr('class', aluno.getClassPagamentoTurma().replace('.', '')).find('input').each(function() {
                    $(this).attr('name', 'pagamento_turmas[' + id_nova_turma + ']');
                });

                aluno.container_campos_escolha_turma.dialog("destroy");
                aluno.container_campo_quantidade_turmas.show();
                aluno.container_btn_incluir_turma.show();

                aluno.eventAlterarTurmaAluno();
                aluno.eventExcluirTurmaAluno();
            }
        }
    };

    /**
     * Acrescenta uma nova turma para o aluno
     * @returns {undefined}
     */

    aluno.incrementaTurma = function() {
        if (!aluno.verificaHorariosTurma())
            exibeMensagem('Já existe uma turma do aluno que interfere no horário dessa turma. Por favor, escolha outra.', 'Inclusão de Turma');

        else if ((aluno.container_turmas_aluno.find('tr').length - 1) < aluno.getQuantidadeTurmas()) { //exclui a linha de cabeçalho na verificação de turmas inseridas
            var id_turma = aluno.getIdTurma();
            var html = '';

            aluno.addHorarioTurma(id_turma);

            if (aluno.container_turmas_aluno.children().length == 0) {
                aluno.container_turmas_aluno.show();
                html = '<tr><th>Curso</th><th>Disciplina</th><th>Turma</th><th>Liberação de Requisitos</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + aluno.getNameTurmaAluno() + '"><input type="hidden" name="turmas[]" value="' + id_turma + '"/><td>' + aluno.campo_curso.find('option:selected').html() + '</td><td>' + aluno.campo_disciplina.find('option:selected').html() + '</td><td>' + aluno.campo_turma.find('option:selected').html() + '</td><td><input type="hidden" name="liberacao[' + id_turma + ']" value="' + aluno.liberacao_turma + '"/>' + aluno.liberacao_turma + '</td><td><div class="alterar_turma">Alterar</div><div class="excluir_turma" >Excluir</div></td></tr>';
            aluno.container_turmas_aluno.append(html);
            aluno.eventAlterarTurmaAluno();
            aluno.eventExcluirTurmaAluno();

            aluno.container_opcoes_turmas_aluno.find('select').removeAttr('disabled');
            aluno.container_opcoes_turmas_aluno.show();

            aluno.incrementaSelectTurmasAluno();
            aluno.gerenciaMudancaTurmaAluno();
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
            var container_alimentos_turma = $(id_container);
            var tipo_alimento_option = aluno.campo_tipo_alimento.find('option:selected');
            var quantidade = helpers.parseNumero(aluno.campo_quantidade_alimento.val());

            if (aluno.campo_tipo_alimento.children().length > 0
                    && quantidade > 0
                    && $(tipo_alimento_option).val() != ""
                    && !$(container_alimentos_turma).find('tr').hasClass($(tipo_alimento_option).val())//verifica se o alimento já não foi incluido
                    && !aluno.verificaExistenciaPagamento()) {

                if ($(container_alimentos_turma).length == 0) {
                    aluno.container_alimentos_turmas.append('<table class="ali_pag form_incrementa" id="' + id_container.replace('#', '') + '" cellpadding="0" cellspacing="0"><tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr></table>');
                    container_alimentos_turma = aluno.container_alimentos_turmas.find(id_container);
                }

                if ($(container_alimentos_turma).children().length == 0)
                    $(container_alimentos_turma).append('<tr><th>Alimento</th><th>Quantidade(kg)</th><th>Opções</th></tr>');

                $(container_alimentos_turma).append('<tr class="' + $(tipo_alimento_option).val() + '"><input type="hidden" name="alimentos[' + aluno.select_turma_pagamento.find('option:selected').val() + '][' + $(tipo_alimento_option).val() + ']" value="' + quantidade + '"/><td>' + $(tipo_alimento_option).html() + '</td><td class="quantidade_alimento_turma">' + quantidade + '</td><td><div class="excluir_alimento" >Excluir</div></td></tr>');

                aluno.eventExcluirAlimento();
                $(container_alimentos_turma).show();
            }
            else
                exibeMensagem('O alimento já foi incluído, ou nenhum foi selecionado, ou o pagamento da turma já foi incluído. Verifique também se a quantidade de alimentos foi preenchida corretamente (ex: <b>"0.5"</b>, <b>"1"</b>).', 'Inclusão de Alimentos');

        }
        else
            exibeMensagem('Nenhuma turma foi incluida.', 'Inclusão de Alimentos');
    };

    aluno.incrementaPagamentoTurma = function() {
        var tipo = aluno.getCondicaoMatricula();
        var flag = false;

        switch (tipo) {
            case '1': // normal
                flag = aluno.incluiPagamentoNormal();
                break;

            case '2':// isento parcial
            case '5':// pendente parcial
                flag = aluno.incluiPagamentoIsentoPendente(tipo);
                break;

            case '3': // isento total
            case '4': //pendente total
                flag = aluno.incluiPagamentoIsentoPendenteTotal(tipo);
                break;

            default:
                exibeMensagem('Para confirmar o pagamento você deve definir a condição da matrícula do aluno', 'Inclusão de Turma');
        }

        if (flag) {
            // após incluir o pagamento, não há a necessidade de exibir os campos
            aluno.container_campo_isencao_pendencia.hide().find('select').attr('disabled', 'disabled');
            aluno.container_campo_condicao_matricula.hide().find('select').attr('disabled', 'disabled');

            aluno.table_gerenciamento_alimentos.hide().find('select,input,button').attr('disabled', 'disabled');
            aluno.container_pagamento.hide();

            aluno.container_pagamentos_registrados.show();
            aluno.incrementaCondicaoTurmas();
        }
    };

    /**
     * Armazena as condições das turmas. Necessário caso o usuário altere a turma sem definir o pagamento
     * @returns {undefined}
     */

    aluno.incrementaCondicaoTurmas = function() {
        aluno.removeCondicaoTurma(aluno.select_turma_pagamento.find('option:selected').val());

        aluno.turmas_condicoes.push({
            turma: aluno.select_turma_pagamento.find('option:selected').val(),
            condicao: aluno.select_condicao_matricula.find('option:selected').val(),
            tipo_isencao: aluno.select_tipo_isencao_pendencia.find('option:selected').val()
        });
    };

    aluno.removeCondicaoTurma = function(id_turma) {
        for (var i in aluno.turmas_condicoes) {
            if (aluno.turmas_condicoes[i].turma == id_turma) {
                delete aluno.turmas_condicoes[i];
                return true;
            }
        }
        return false;
    };

    aluno.getCondicaoTurma = function(id_turma) {
        for (var i in aluno.turmas_condicoes) {
            if (aluno.turmas_condicoes[i].turma == id_turma) {
                return aluno.turmas_condicoes[i].condicao;
            }
        }
        return '';
    };

    aluno.getTipoIsencaoPendenciaTurma = function(id_turma) {
        //console.log(aluno.turmas_condicoes);
        for (var i in aluno.turmas_condicoes) {
            if (aluno.turmas_condicoes[i].turma == id_turma) {
                return aluno.turmas_condicoes[i].tipo_isencao;
            }
        }
        return '';
    };


    aluno.getTotalAlimentos = function() {
        var total_alimentos = 0.0;
        $(aluno.getIdAlimentosTurma()).find('.quantidade_alimento_turma').each(function() {
            var quantidade_alimento = helpers.parseNumero($(this).html());

            if (quantidade_alimento != -1)
                total_alimentos += quantidade_alimento;
            else
                total_alimentos = quantidade_alimento;
        });

        return total_alimentos;
    };

    aluno.incluiPagamentoNormal = function() {
        var option = aluno.select_turma_pagamento.find('option:selected');

        if ($(option).length > 0) {
            var id_turma = $(option).val();
            var valor_pago = helpers.parseNumero(aluno.campo_valor_pagamento.val());
            var total_alimentos = aluno.getTotalAlimentos();
            var num_recibo = aluno.getRecibo();

            if (aluno.select_turma_pagamento.children().length > 0
                    && total_alimentos != -1
                    && valor_pago != -1
                    && !aluno.verificaExistenciaPagamento()
                    && $(option).val() != ""
                    && num_recibo.length > 0) {

                if (total_alimentos >= aluno.qt_min_alimentos && valor_pago >= aluno.valor_min_pagamento) {
                    var html = '';

                    if (aluno.container_pagamentos_aluno.children().length == 0) {
                        aluno.container_pagamentos_aluno.show();
                        html = '<tr><th>Disciplina - Turma</th><th>Nº Recibo</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Condição</th><th>Situação</th><th>Opções</th></tr>';
                    }

                    html += '<tr class="' + aluno.getClassPagamentoTurma().replace('.', '') + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + valor_pago + '"/><td class="nome_turma">' + $(option).text() + '</td><td><input type="hidden" name="recibos_turmas[' + id_turma + ']" value="' + num_recibo + '"/>' + num_recibo + '</td><td class="valor_pago">' + valor_pago + '</td><td class="quant_alimento">' + total_alimentos + '</td><td class="condicao"><input type="hidden" name="condicao_turmas[' + id_turma + ']" value="' + aluno.select_condicao_matricula.find('option:selected').val() + '"/><input type="hidden" name="tipo_isencao_pendencia_turmas[' + id_turma + ']" value=""/>' + aluno.select_condicao_matricula.find('option:selected').text() + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="Liberado"/>Liberado</td><td><div class="excluir_pagamento" >Excluir</div></td></tr>';
                    aluno.container_pagamentos_aluno.append(html);
                    aluno.eventOpcaoExcluirPagamento();

                    return true;
                }
                else
                    exibeMensagem('Condição de pagamento incompatível', 'Inclusão de Pagamento de Turmas');
            }
            else
                exibeMensagem('Ou pagamento dessa turma já foi inserido, ou existem ,informações não preenchidas. Se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');
        }
        else
            exibeMensagem('Inclua primeiro a turma', 'Registro de Pagamento');

        return false;
    };


    aluno.incluiPagamentoIsentoPendente = function(tipo) {
        var option = aluno.select_turma_pagamento.find('option:selected');

        if ($(option).length > 0) {
            var id_turma = $(option).val();
            var valor_pago = helpers.parseNumero(aluno.campo_valor_pagamento.val());
            var total_alimentos = aluno.getTotalAlimentos();
            var num_recibo = aluno.getRecibo();
            var tipo_isencao = aluno.getTipoIsencaoPendencia();

            if (aluno.select_turma_pagamento.children().length > 0
                    && total_alimentos != -1
                    && valor_pago != -1
                    && !aluno.verificaExistenciaPagamento()
                    && $(option).val() != ""
                    && num_recibo.length > 0) {

                //console.log(tipo_isencao, total_alimentos, valor_pago);

                if ((tipo_isencao == '1' && total_alimentos < aluno.qt_min_alimentos && valor_pago >= aluno.valor_min_pagamento)
                        || (tipo_isencao == '2' && total_alimentos >= aluno.qt_min_alimentos && valor_pago < aluno.valor_min_pagamento)
                        || (tipo_isencao == '3' && total_alimentos < aluno.qt_min_alimentos && valor_pago < aluno.valor_min_pagamento)) {

                    var html = '';
                    var situacao = (tipo == 2) ? 'Liberado' : 'Pendente';

                    if (aluno.container_pagamentos_aluno.children().length == 0) {
                        aluno.container_pagamentos_aluno.show();
                        html = '<tr><th>Disciplina - Turma</th><th>Nº Recibo</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Condição</th><th>Situação</th><th>Opções</th></tr>';
                    }

                    html += '<tr class="' + aluno.getClassPagamentoTurma().replace('.', '') + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + valor_pago + '"/><td class="nome_turma">' + $(option).text() + '</td><td><input type="hidden" name="recibos_turmas[' + id_turma + ']" value="' + num_recibo + '"/>' + num_recibo + '</td><td class="valor_pago">' + valor_pago + '</td><td class="quant_alimento">' + total_alimentos + '</td><td class="condicao"><input type="hidden" name="condicao_turmas[' + id_turma + ']" value="' + aluno.select_condicao_matricula.find('option:selected').val() + '"/><input type="hidden" name="tipo_isencao_pendencia_turmas[' + id_turma + ']" value="' + aluno.select_tipo_isencao_pendencia.find('option:selected').val() + '"/>' + aluno.select_condicao_matricula.find('option:selected').text() + " - " + aluno.select_tipo_isencao_pendencia.find('option:selected').text() + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="' + situacao + '"/>' + situacao + '</td><td><div class="excluir_pagamento" >Excluir</div></td></tr>';
                    aluno.container_pagamentos_aluno.append(html);
                    aluno.eventOpcaoExcluirPagamento();

                    return true;
                }
                else
                    exibeMensagem('Condição de pagamento incompatível', 'Inclusão de Pagamento de Turmas');
            }
            else
                exibeMensagem('Ou pagamento dessa turma já foi inserido, ou existem ,informações não preenchidas. Se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');
        }
        else
            exibeMensagem('Inclua primeiro a turma', 'Registro de Pagamento');

        return false;
    };

    aluno.incluiPagamentoIsentoPendenteTotal = function(tipo) {
        var option = aluno.select_turma_pagamento.find('option:selected');

        if ($(option).length > 0
                && aluno.select_turma_pagamento.children().length > 0
                && !aluno.verificaExistenciaPagamento()) {

            var html = '';
            var id_turma = $(option).val();
            var situacao = (tipo == 3) ? 'Liberado' : 'Pendente';

            if (aluno.container_pagamentos_aluno.children().length == 0) {
                aluno.container_pagamentos_aluno.show();
                html = '<tr><th>Disciplina - Turma</th><th>Nº Recibo</th><th>Total Pago(R$)</th><th>Total de Alimentos(kg)</th><th>Condição</th><th>Situação</th><th>Opções</th></tr>';
            }

            html += '<tr class="' + aluno.getClassPagamentoTurma().replace('.', '') + '"><input type="hidden" name="pagamento_turmas[' + id_turma + ']" value="' + 0 + '"/><td class="nome_turma">' + $(option).text() + '</td><td><input type="hidden" name="recibos_turmas[' + id_turma + ']" value=""/></td><td class="valor_pago">0</td><td class="quant_alimento">0</td><td class="condicao"><input type="hidden" name="condicao_turmas[' + id_turma + ']" value="' + aluno.select_condicao_matricula.find('option:selected').val() + '"/><input type="hidden" name="tipo_isencao_pendencia_turmas[' + id_turma + ']" value=""/>' + aluno.select_condicao_matricula.find('option:selected').text() + " - " + aluno.select_tipo_isencao_pendencia.find('option:selected').text() + '</td><td class="situacao"><input type="hidden" name="situacao_turmas[' + id_turma + ']" value="' + situacao + '"/>' + situacao + '</td><td><div class="excluir_pagamento" >Excluir</div></td></tr>';
            aluno.container_pagamentos_aluno.append(html);
            aluno.eventOpcaoExcluirPagamento();

            return true;
        }
        else
            exibeMensagem('Ou pagamento dessa turma já foi inserido, ou existem ,informações não preenchidas. Se quiser fazer alguma alteração, exclua esse pagamento, faça as alterações e registre-o novamente.', 'Inclusão de Pagamento');

        return false;
    };

    aluno.eventAlterarTurmaAluno = function() {
        aluno.container_turmas_aluno.find('.alterar_turma').off('click');
        aluno.container_turmas_aluno.find('.alterar_turma').click(function(evt) {
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

            evt.stopPropagation();
        });
    };

    aluno.eventExcluirTurmaAluno = function() {
        aluno.container_turmas_aluno.find('.excluir_turma').off('click');
        aluno.container_turmas_aluno.find('.excluir_turma').click(function(evt) {
            var confirma_exclusao = confirm('Deseja realmente retirar o aluno dessa turma?');
            if (confirma_exclusao) {
                var aux_class = $(this).parents('tr').attr('class'); // a linha correspondente a turma armazena o id para retirar os pagamentos/alimentos da turma
                var table = $(this).parents('table');
                var id_turma = $(this).parents('tr').children('input').val();

                aluno.removeHorarioTurma(id_turma);
                aluno.removeCondicaoTurma(id_turma);

                aluno.select_turma_pagamento.find('option').each(function() {
                    if ($(this).val() == id_turma)
                        $(this).remove();
                });

                $('#alimentos_' + aux_class).remove();
                $('.pagamento_' + aux_class).remove();

                if (aluno.container_pagamentos_aluno.find('tr').length == 1)
                    aluno.container_pagamentos_aluno.html('').hide();

                if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                    $(this).parents('tr').remove();

                else {
                    $(table).html('').hide(); // a tabela de turmas é limpa e como não há nenhuma turma os campos são desabilitados e limpos
                    aluno.container_alimento.hide().find('select,input,button').attr('disabled', 'disabled');
                    aluno.container_pagamentos_registrados.hide();
                    aluno.container_turmas_aluno.hide();
                    aluno.container_opcoes_turmas_aluno.hide().find('select,input,button').attr('disabled', 'disabled'); // se o usuário removeu todas as turmas, não é necessário exibir os campos
                    aluno.container_pagamento.hide().find('select,input,button').attr('disabled', 'disabled');
                }

                aluno.select_condicao_matricula.val('');
                aluno.campo_quantidade_alimento.val('');
                aluno.campo_num_recibo.val('');
                aluno.campo_valor_pagamento.val('00,00');

                aluno.gerenciaMudancaTurmaAluno();
            }
            evt.stopPropagation();
            return false;
        });
    };

    aluno.eventExcluirAlimento = function() {
        $(aluno.getIdAlimentosTurma()).find('.excluir_alimento').click(function(evt) {
            var table = $(this).parents('table');

            if (!aluno.verificaExistenciaPagamento()) { // procura se já tem um pagamento registrado para a turma, se houver a remoção não é realizada
                if ($(table).find('tr').length > 2) // se tiver somente uma turma, remove somente a linha, caso contrário, pra n ficar feio, remove a tabela toda
                    $(this).parents('tr').remove();
                else
                    $(table).html('').hide();
            }
            else
                exibeMensagem('Para realizar a exclusão, você deve cancelar o pagamento dessa turma primeiro.', 'Exlusão de Alimentos');

            evt.stopPropagation();
        });
    };

    aluno.eventOpcaoExcluirPagamento = function() {
        $('.excluir_pagamento').off('click');
        $('.excluir_pagamento').click(function(evt) {
            var table = $(this).parents('table');
            var class_turma = $(this).parents('tr').attr('class').replace('pagamento_', ''); // necessario para descobrir a turma a qual o pagamento faz parte
            var id_turma = $('.' + class_turma).children('input').val();

            var condicao_matricula = $(table).find('.condicao').children('input[name*="condicao_turmas"]').val(); // é necessário pegar a condição da matricula para voltar com os campos certos
            var tipo_isencao_pendencia = $(table).find('.condicao').children('input[name*="tipo_isencao_pendencia_turmas"]').val(); // é necessário pegar a o tipo de isencao/pendencia para voltar com os campos certos

            if (condicao_matricula != undefined && id_turma.length > 0) {
                if ($(table).find('tr').length > 2)
                    $(this).parents('tr').remove();
                else {
                    aluno.container_pagamentos_registrados.hide();
                    $(table).html('').hide();
                }

                aluno.select_turma_pagamento.val(id_turma);
                aluno.container_campo_isencao_pendencia.find('select').val(tipo_isencao_pendencia).removeAttr('disabled');
                aluno.container_campo_condicao_matricula.find('select').val(condicao_matricula).removeAttr('disabled').show();

                aluno.container_campo_condicao_matricula.show();

                aluno.gerenciaMudancaTurmaAluno();
                aluno.gerenciaCamposCondicaoMatricula(); // chama a função que exibe os campos de acordo com a condição de matrícula definida
                aluno.gerenciaTipoIsencaoPendencia();
            }
            evt.stopPropagation();
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

