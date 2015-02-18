function exibeMensagem(mensagem, titulo) {

    if (mensagem.length > 0) {
        $('#mensagem').html(mensagem);

        $("#mensagem").dialog({
            dialogClass: "no-close",
            modal: true,
            resizable: false,
            draggable: false,
            title: titulo,
            closeOnEscape: false
        });

        $("#mensagem").dialog("option", {
            buttons: {
                Ok: function() {
                    $(this).dialog("close");
                }
            }

        });
    }

}


