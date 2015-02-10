/**
 * Controle do gerenciamento de relatórios
 * @returns {undefined}
 */
(function() {
    var relatorio = {
        url_ajax_turma: '',
        url_ajax_relatorio: '',
        campo_periodo: $('#periodo'),
        campo_turmas: $('#turmas'),
        porcentagem: $("#porcentagem"),
        container_porcentagem: $("#container-porcentagem"),
        bt_enviar: $('#enviar'),
        controle: null
    };

    relatorio.setValues = function(url_ajax_relatorio, url_ajax_turma) {
        relatorio.url_ajax_relatorio = url_ajax_relatorio;
        relatorio.url_ajax_turma = url_ajax_turma;
    };

    relatorio.getPorcetagem = function() {
        $.ajax({
            type: "POST",
            url: relatorio.url_ajax_relatorio,
            dataType: "JSON",
            async: false
        }).success(function(data) {
            //console.log(data);
            if (data == 'Relatório Finalizado') {
                clearInterval(relatorio.controle);
                relatorio.porcentagem.progressbar("destroy");
                relatorio.container_porcentagem.dialog("destroy").find('span').hide();
            }
        }).error(function(error) {
            console.log(error);
        });
    };

    relatorio.iniPorcentagem = function() {
        relatorio.bt_enviar.click(function() {
            //switch (tipo) {
            //  case 1://alunos por turma
            if ($('input:radio[name="todas_turmas"]:checked').val() == 'nao' && $('.linha').find('option:selected').length == 0) {
                exibeMensagem('É necessário incluir ao menos uma turma.', 'Relatório');
                return false;
            }
            //    break;
            //default:
            //  return false;
            //}

            relatorio.container_porcentagem.dialog({
                dialogClass: "no-close",
                modal: true,
                resizable: false,
                draggable: false,
                title: 'Gerando Relatório...',
                closeOnEscape: false
            }).find('span').show();

            relatorio.porcentagem.progressbar({
                value: false
            });

            relatorio.controle = setInterval(function() {
                relatorio.getPorcentagem();
            }, 3000);
        });
    };

    relatorio.buscaTurmasByPeriodo = function() {
        var periodo = $('#periodo').find('option:selected').val();

        if (periodo != undefined && periodo.length > 0)
            helpers.buscaTurmasByDisciplina(relatorio.url_ajax_turma, null, relatorio.campo_periodo, periodo);
    };

    relatorio.ini = function() {
        relatorio.iniPorcentagem();
        relatorio.buscaTurmasByPeriodo();

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
            relatorio.buscaTurmasByPeriodo();
        });
    };


})();

