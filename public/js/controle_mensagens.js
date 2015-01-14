function exibeMensagem(mensagem, titulo){

    if(mensagem.length>0){
        $('#mensagem').html(mensagem);
        
        $("#mensagem").dialog({
            modal: true,
            resizable: false,
            draggable: false,
            title: titulo,
            closeOnEscape: false
        });
              
        /*if(excluir.length > 0)
            $("#mensagem").dialog("option", {
                dialogClass: "no-close"
            });
        
        else*/
        
        $("#mensagem").dialog("option", {
            buttons: {
                Ok: function() {
                    $(this).dialog("close");
                }
            }
                
        });
    }
    
}


