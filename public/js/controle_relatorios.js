/**
 * Controle do gerenciamento de relatórios
 * @returns {undefined}
 */
var controle_relatorios = (function() {
    var relatorio = {
        url_ajax_turma: '',
        url_ajax_relatorio: '',
        campo_periodo: $('#periodo'),
        campo_turmas: $('#turmas'),
        radio_todas_turmas:$('input:radio[name="todas_turmas"]'),
        radio_checked_todas_turmas:$('input:radio[name="todas_turmas"]:checked'),
        container_turmas: $('.linha'),
        porcentagem: $("#porcentagem"),
        container_porcentagem: $("#container-porcentagem"),
        bt_enviar: $('#enviar'),
        controle: null
    };

    relatorio.setValues = function(url_ajax_relatorio, url_ajax_turma) {
        relatorio.url_ajax_relatorio = url_ajax_relatorio;
        relatorio.url_ajax_turma = url_ajax_turma;
        relatorio.ini();
    };
    
    relatorio.ini = function() {
        relatorio.iniPorcentagem();
        relatorio.buscaTurmasByPeriodo();
        helpers.mostraEscondeRadioSelect(relatorio.radio_checked_todas_turmas, relatorio.container_turmas, 'nao', true);
        
        relatorio.radio_todas_turmas.click(function() {
            helpers.mostraEscondeRadioSelect($(this), relatorio.container_turmas, 'nao', true);
        });

        relatorio.campo_periodo.change(function() {
            relatorio.buscaTurmasByPeriodo();
        });
    };

    
    relatorio.getPorcentagem = function() {
        $.ajax({
            type: "POST",
            url: relatorio.url_ajax_relatorio,
            dataType: "JSON",
            async: true
        }).success(function(data) {
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
            if ($('input:radio[name="todas_turmas"]:checked').val() == 'nao' && $('.linha').find('option:selected').length == 0) {
                exibeMensagem('É necessário incluir ao menos uma turma.', 'Relatório');
                return false;
            }

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
        var periodo = relatorio.campo_periodo.find('option:selected').val();
        
        if (periodo != undefined && periodo.length > 0)
            helpers.buscaTurmasByDisciplina(relatorio.url_ajax_turma, null, relatorio.campo_turmas, periodo);
    };
    
    return {ini: relatorio.setValues};
})();

