var controle;

function getPorcentagem(url_acesso) {
    $.ajax({
        type: "POST",
        url: url_acesso,
        dataType: "JSON",
        async: false
    }).success(function(data) {
        console.log(data);
        if (data == 'Relatório Finalizado') {
            clearInterval(controle);
            $("#porcentagem").progressbar("destroy");
            $("#container-porcentagem").dialog("destroy").find('span').hide();
        }
    }).error(function(error) {
        console.log(error);
    });
}

function iniPorcentagem(url, tipo) {
    $('#enviar').click(function() {
        switch (tipo) {
            case 1://alunos por turma
                if ($('input:radio[name="todas_turmas"]:checked').val() == 'nao' && $('.linha').find('option:selected').length == 0) {
                    exibeMensagem('É necessário incluir ao menos uma turma.', 'Relatório Alunos por Turma');
                    return false;
                }
                break;
            default:
                return false;
        }

        $('#container-porcentagem').dialog({
            dialogClass: "no-close",
            modal: true,
            resizable: false,
            draggable: false,
            title: 'Gerando Relatório...',
            closeOnEscape: false
        }).find('span').show();

        $('#porcentagem').progressbar({
            value: false
        });

        controle = setInterval(function() {
            getPorcentagem(url);
        },3000);
    });
}