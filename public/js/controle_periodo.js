/**
 * Controle do gerenciamento do Período
 * @returns {undefined}
 */
var controle_periodo = (function() {
    var periodo = {
        min_date: '',
        max_date: '',
        url_img: '',
        campo_data_ini: $('#data_inicio'),
        campo_data_fim: $('#data_termino')
    };

    periodo.setValues = function(url_img, min_date, max_date) {
        periodo.url_img = url_img;
        periodo.min_date = min_date;
        periodo.max_date = max_date;

        periodo.ini();
    };

    periodo.ini = function() {
        periodo.campo_data_ini.datepicker({
            buttonText: "Clique para selecionar a data inicial",
            showOn: "button",
            buttonImageOnly: true,
            buttonImage: periodo.url_img,
            onSelect: function(data) { // A data de término do período só pode ser incluída após a escolha da data inicial
                periodo.campo_data_fim.datepicker("setDate", null).val('');
                
                periodo.campo_data_fim.datepicker({
                    buttonText: "Clique para selecionar a data inicial",
                    showOn: "button",
                    buttonImageOnly: true,
                    buttonImage: periodo.url_img,
                    minDate: data
                });
                
                periodo.campo_data_fim.datepicker('option', 'minDate', data);
                periodo.campo_data_fim.datepicker('option', 'maxDate', periodo.campo_data_ini.datepicker('option', 'maxDate'));
            }
        });

        if (periodo.min_date != undefined && periodo.max_date != undefined) {
            periodo.campo_data_ini.datepicker('option', 'minDate', periodo.min_date);
            periodo.campo_data_ini.datepicker('option', 'maxDate', periodo.max_date);
        }

        if (periodo.campo_data_ini.val().length > 0) { // se já tiver uma data inicial setada, a data final pode ser incluída
            periodo.campo_data_fim.datepicker({
                buttonText: "Clique para selecionar a data final",
                showOn: "button",
                buttonImageOnly: true,
                buttonImage: periodo.url_img,
                minDate: periodo.min_date
            });
        }
    };
    
    return {ini: periodo.setValues};
    
})();

