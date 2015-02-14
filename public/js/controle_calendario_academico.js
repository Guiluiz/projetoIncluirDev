/**
 * Controle do gerenciamento do calendário academico
 * @returns {undefined}
 */

var controle_calendario = (function() {

    var calendario = {
        container: $('#datas-escolhidas'),
        campo_data: $('#data'),
        container_datas_cadastradas: $('#container-datas'),
        btn_enviar: $('#enviar'),
        checkbox_todos_sabados: $('#todos_sabados'),
        selecionados: 'data-selecionada',
        remove_btn: 'remove',
        min_date: '',
        max_date: '',
        url_img: ''
    };

    calendario.setValues = function(url_img, min_date, max_date) {
        calendario.url_img = url_img;
        calendario.min_date = min_date;
        calendario.max_date = max_date;
        
        calendario.ini();
    };

    calendario.ini = function() {
        // as datas escolhidas previamente, inicialmente  são mantidas nesse container
        if (calendario.container_datas_cadastradas.children().length > 0)
            calendario.container.append(calendario.container_datas_cadastradas.children());

        $('.' + calendario.remove_btn).click(function() {
            $(this).parent().remove();
        });

        calendario.checkbox_todos_sabados.click(function() {
            if ($(this).prop('checked')) {
                var aux_min_date = helpers.parseDate(calendario.min_date);
                var aux = aux_min_date.clone();
                var aux_max_date = helpers.parseDate(calendario.max_date);

                if (aux_min_date instanceof Date && aux_max_date instanceof Date) {
                    while (aux.compareTo(aux_min_date) >= 0 && aux.compareTo(aux_max_date) <= 0) {
                        if (aux.toString('dddd') == "sábado") {
                            var data = aux.toString('dd/MM/yyyy');

                            if (calendario.container.find('div[valor="' + data + '"]').length == 0)
                                calendario.container.append('<div class="data-selecionada" valor="' + data + '">' + data + '<span title="Clique para remover essa data" class="remove">X</span></div>');

                            aux.addWeeks(1);
                        }
                        else
                            aux.addDays(1);

                    }
                    $('.' + calendario.remove_btn).click(function() {
                        $(this).parent().remove();
                    });
                }
            }
            else {
                calendario.container.children().each(function() {
                    var data = helpers.parseDate($(this).attr('valor'));

                    if (data instanceof Date) {
                        if (data.toString('dddd') == "sábado")
                            $(this).remove();
                    }
                });
            }
        });

        calendario.campo_data.datepicker({
            buttonText: "Clique para selecionar uma data",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: calendario.url_img,
            onSelect: function(data) {
                if (calendario.container.find('div[valor="' + data + '"]').length == 0)
                    calendario.container.append('<div class="data-selecionada" valor="' + data + '">' + data + '<span title="Clique para remover essa data" class="remove">X</span></div>');

                $('.' + calendario.remove_btn).click(function() {
                    $(this).parent().remove();
                });
            }
        });

        if (calendario.min_date != undefined && calendario.max_date != undefined) {
            calendario.campo_data.datepicker('option', 'minDate', calendario.min_date);
            calendario.campo_data.datepicker('option', 'maxDate', calendario.max_date);
        }

        calendario.btn_enviar.click(function() {
            var selecionados = $('.' + calendario.selecionados);

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
    };

    return {ini: calendario.setValues};

})();