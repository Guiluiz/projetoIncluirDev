/**
 * Controle do gerenciamento de Alunos
 * @returns {undefined}
 */
(function() {
    var aluno = {
        container_turma: $('#turmas_aluno'),
        container_pagamentos: $('#table_pagamentos_turmas'),
        btn_incluir_turma: $('#incluir_turma'),
        btn_incluir_pagamento: $('#registrar_pagamento'),
        btn_incluir_alimento: $('#incluir_alimento'),
        btn_atualizar_alimentos: $('#atualizar_alimentos'),
        //btn_cancelar: $('#cancelar'),
        ///btn_add_alimento: $('#add_alimento'),
        campo_nome_aluno: $('#nome_aluno'),
        campo_curso: $('#curso'),
        campo_disciplina: $('#disciplina'),
        campo_turma: $('#turma'),
        campos_data: $('#data_nascimento, #data_registro'),
        turma_pagamento: $('#pagamento_turma'),
        campo_valor_pagamento: $('#valor_pago'),
        campo_tipo_alimento: $('#alimento'),
        campo_quantidade_alimento: $('#quantidade_alimento'),
        check_is_responsavel: $('#is_cpf_responsavel'),
        quantidade_alunos_turma: $('#quant_alunos_cadastrados'),
        url_ajax_verifica_aluno: '', // verifica a existência de alunos com nome próximo no bd
        url_img: '',
        url_ajax_alimentos: '',
        url_ajax_disciplina: '',
        url_ajax_turma: '',
        url_ajax_quantidade_alunos_turma: '', // busca a quantidade de alunos da turma selecionada
        url_ajax_verificacao_liberacao: '', // verifica se aluno pode ser matriculado na disciplina escolhida
        action: '', // 1- cadastro 2 - alteracao 3 - exclusao
        qt_min_alimentos: '', // quantidade mínima de alimentos para pagamento ser válido
        valor_min_pagamento: '' // valor mínimo para pagamento ser válido
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
        if (aluno.action != 3) {
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

            aluno.turma_pagamento.change(function() {
                $('.ali_pag').hide();
                //console.log(controle.retira_acentos(controle.trim($(this).find('option:selected').html())).toLowerCase());
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
        }
    };

})();

