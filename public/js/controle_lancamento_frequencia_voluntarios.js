/**
 * Controle do gerenciamento de lanççaento de frequencia de voluntários
 * @returns {_L4.Anonym$5}
 */
var controle_frequencia_voluntario = (function() {
    var frequencia_vol = {
        calendario: $('#calendario'),
        setor: $('#setor'),
        voluntario_container: $('#voluntarios'),
        url_ajax_voluntario: '',
        datas_calendario_academico: '',
        data_atual: '',
        data_ini_periodo: '',
        data_fim_periodo: '',
        campo_data: $('#data'),
        voluntarios: null,
        data_escolhida: ''
    };

    frequencia_vol.setValues = function(url_ajax_voluntario, datas_calendario_academico, data_atual, data_ini_periodo, data_fim_periodo) {
        frequencia_vol.url_ajax_voluntario = url_ajax_voluntario;
        frequencia_vol.datas_calendario_academico = datas_calendario_academico;
        frequencia_vol.data_atual = (data_atual instanceof Date) ? data_atual : helpers.parseDate(data_atual);
        frequencia_vol.data_ini_periodo = data_ini_periodo;
        frequencia_vol.data_fim_periodo = data_fim_periodo;
        
        frequencia_vol.ini();
    };


    frequencia_vol.getSetor = function() {
        return frequencia_vol.setor.find('option:selected').val();
    };

    frequencia_vol.ini = function() {
        frequencia_vol.setor.val('');

        frequencia_vol.setor.change(function() {
            var id_setor = frequencia_vol.getSetor();

            if (id_setor == undefined || id_setor == '')
                exibeMensagem('Você deve escolher uma das opções.', 'Frequência de Voluntários');

            else {
                frequencia_vol.getVoluntarios();
            }
        });
    };

    frequencia_vol.getVoluntarios = function() {
        $.ajax({
            type: "POST",
            url: frequencia_vol.url_ajax_voluntario,
            dataType: "JSON",
            data: {
                setor: frequencia_vol.getSetor()
            },
            beforeSend: function() {
                jQuery('#mensagem-ajax').dialog({
                    dialogClass: "no-close",
                    closeOnEscape: false,
                    modal: true,
                    title: 'Busca de Voluntários'
                });
            },
            complete: function() {
                jQuery('#mensagem-ajax').dialog('destroy');
            },
            success: function(voluntarios) {
                frequencia_vol.voluntarios = voluntarios;

                if (frequencia_vol.voluntarios instanceof Object) {
                    if (frequencia_vol.voluntarios.length > 0) {
                        frequencia_vol.printVoluntarios();

                        frequencia_vol.calendario.datepicker({
                            buttonText: "Clique para selecionar uma data",
                            minDate: frequencia_vol.data_ini_periodo,
                            maxDate: frequencia_vol.data_fim_periodo,
                            beforeShowDay: function(calendar_date) {
                                var aux = $.datepicker.formatDate('dd/mm/yy', calendar_date);
                                
                                if (frequencia_vol.datas_calendario_academico[aux] != undefined
                                        && +frequencia_vol.data_atual >= +calendar_date)
                                    return [true, ''];

                                return [false, ''];
                            },
                            onSelect: function(data_escolhida) {
                                frequencia_vol.data_escolhida = data_escolhida;

                                frequencia_vol.setDataLancamento();
                                frequencia_vol.campo_data.val(data_escolhida);
                                frequencia_vol.printCampos();
                            }
                        });

                        return;
                    }

                }
                exibeMensagem('Nenhum voluntário foi encontrado', 'Frequência de Voluntários');
                frequencia_vol.printMensagem('');
            },
            error: function(error) {
                console.log(error);
            }
        });
    };

    frequencia_vol.printMensagem = function(msg) {
        frequencia_vol.voluntario_container.html(msg);
    };

    frequencia_vol.printVoluntarios = function() {
        var html = '';

        html += "<div id='title-frequencia' class='obs'>Para fazer o lançamento escolha uma data no calendário acima.</div><div id='data_lancamento'></div>";
        html += '<table id="frequencia_voluntario" class="form_incrementa stripped"><tr><th>Voluntário</th><th>Presente?</th><th>Hora de Entrada</th><th>Hora de Saída</th><th>Total de Horas</th>';

        for (var key in frequencia_vol.voluntarios)
            html += '<tr voluntario="' + frequencia_vol.voluntarios[key].id_voluntario + '"><td>' + frequencia_vol.voluntarios[key].nome_voluntario + '</td><td class="is_presente"> - </td><td class="hora_entrada"> - </td><td class="hora_saida"> - </td><td class="total_horas"> ' + frequencia_vol.voluntarios[key].total_horas['horas'] + ' horas ' + frequencia_vol.voluntarios[key].total_horas['minutos'] + ' minutos</td>';

        html += '</table>';
        
        frequencia_vol.voluntario_container.html(html);
    };

    frequencia_vol.setDataLancamento = function() {
        $('#data_lancamento').html('<h2>Lançamento do Dia: ' + frequencia_vol.data_escolhida + '</h2>');
    };

    frequencia_vol.printCampos = function() {
        var hora_entrada = '', hora_saida = '', is_presente = 1;

        for (var key in frequencia_vol.voluntarios) {
            for (var key_frequencia in frequencia_vol.voluntarios[key].frequencia) {
                if (frequencia_vol.data_escolhida == frequencia_vol.voluntarios[key].frequencia[key_frequencia].data_funcionamento) {
                    is_presente = frequencia_vol.voluntarios[key].frequencia[key_frequencia].is_presente;

                    if (is_presente) {
                        hora_entrada = frequencia_vol.voluntarios[key].frequencia[key_frequencia].hora_entrada;
                        hora_saida = frequencia_vol.voluntarios[key].frequencia[key_frequencia].hora_saida;
                    }
                }
            }

            $('#frequencia_voluntario').find('tr[voluntario="' + frequencia_vol.voluntarios[key].id_voluntario + '"]').each(function() {
                $(this).find('.is_presente').html('<input type="checkbox" class="input_is_presente" ' + ((is_presente != 0) ? 'checked="checked"' : '') + ' name="voluntario_presente_' + frequencia_vol.voluntarios[key].id_voluntario + '" />');
                $(this).find('.is_presente').append('<input type="hidden" class="hidden_is_presente" value="' + ((is_presente != 0) ? 'on' : 'off') + '" name="voluntario_presente_' + frequencia_vol.voluntarios[key].id_voluntario + '" />');
                $(this).find('.hora_entrada').html('<input type="text" class="input_hora_entrada" ' + ((is_presente != 0) ? '' : 'disabled = "disabled"') + ' value="' + hora_entrada + '" name="voluntario_entrada_' + frequencia_vol.voluntarios[key].id_voluntario + '" />');
                $(this).find('.hora_saida').html('<input type="text" class="input_hora_saida" ' + ((is_presente != 0) ? '' : 'disabled = "disabled"') + ' value="' + hora_saida + '" name="voluntario_saida_' + frequencia_vol.voluntarios[key].id_voluntario + '" />');
            });
        }

        $('.input_hora_entrada, .input_hora_saida').mask('99:99');

        $('.input_is_presente').click(function() {
            if (!$(this).prop('checked')) {
                $(this).parents('tr').find('.hidden_is_presente').val('off');
                $(this).parents('tr').find('.input_hora_entrada').val('').attr('disabled', 'disabled');
                $(this).parents('tr').find('.input_hora_saida').val('').attr('disabled', 'disabled');
            }
            else {
                $(this).parents('tr').find('.hidden_is_presente').val('on');
                $(this).parents('tr').find('.input_hora_entrada').val('').removeAttr('disabled');
                $(this).parents('tr').find('.input_hora_saida').val('').removeAttr('disabled');
            }

        });

        $('form').submit(function() {
            var envia = true;

            $('.input_is_presente').each(function() {
                if ($(this).prop('checked'))
                    $(this).parent().find('.hidden_is_presente').attr('disabled');
            });

            $('#frequencia_voluntario').find('tr').each(function() {
                if ($(this).attr('voluntario') !== undefined) {
                    if ($(this).find('.input_is_presente').prop('checked')) {
                        var hora_entrada = $(this).find('.input_hora_entrada');
                        var hora_saida = $(this).find('.input_hora_saida');

                        if (!(Date.parse('01/01/2014 ' + $(hora_entrada).val()) < Date.parse('01/01/2014 ' + $(hora_saida).val()))) {
                            $(hora_entrada).css('border', '1px solid red');
                            $(hora_saida).css('border', '1px solid red');

                            exibeMensagem('Há lançamentos com problemas, corrija-os e tente novamente', 'Frequência de Voluntários')
                            envia = false;
                        }
                    }
                }
            });

            if (!envia)
                return false;
        });
    };

    return {ini: frequencia_vol.setValues};

})();