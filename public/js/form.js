var url_ajax;
var form = {
    iniForm: function(url_cep) {
        var type;
        var has_cpf = false, has_cnpj = false, has_data = false, has_email = false, has_file = false, has_time = false;
        var estados = new Array(), cidades = new Array();
        url_ajax = url_cep;

        $('form').find('select').each(function() {
            if ($(this).hasClass('estado'))
                estados.push($(this));

            else if ($(this).hasClass('cidade'))
                cidades.push($(this));
        });

        if (estados.length > 0 && cidades.length > 0) {
            var cidade_escolhida = $('#cidade_escolhida').val(), estado_escolhido = $('#estado_escolhido').val();

            if ($(cidade_escolhida) != "" && $(estado_escolhido) != "")
                this.buscaEstadosCidades(cidades, estados, estado_escolhido, cidade_escolhida);

            else
                this.buscaEstadosCidades(cidades, estados, '', '');
        }
        $('form').find('input').each(function() {
            type = $(this).attr('type');

            if (type != undefined)
                type = type.toLowerCase();
            else
                console.log('O campo "' + $(this).attr('name') + '" está sem nome');

            if (type == 'text') {
                if ($(this).hasClass('telefone'))
                    $(this).mask('(999)9999-9999');

                else if ($(this).hasClass('cpf')) {
                    $(this).mask('999.999.999-99');
                    if (!has_cpf) {
                        has_cpf = true;
                        form.addCPFValidator();
                    }
                }

                else if ($(this).hasClass('dinheiro'))
                    $(this).mask('99?,99', {placeholder:"00,00"});

                else if ($(this).hasClass('carga_horaria'))
                    $(this).mask('9 HORA(S)');

                else if ($(this).hasClass('cnpj')) {
                    $(this).mask('99.999.999/9999-99');
                    if (!has_cnpj) {
                        has_cnpj = true;
                        form.addCNPJValidator();
                    }
                }
                else if ($(this).hasClass('time')) {
                    $(this).mask('99:99');
                    if (!has_time) {
                        has_time = true;
                        form.addTimeValidator();
                    }
                }

                else if ($(this).hasClass('data')) {
                    $(this).mask('99/99/9999');
                    /*if (!has_data) {
                        has_data = true;
                        form.addDataValidator();
                    }*/
                }

                else if ($(this).hasClass('cep')) {
                    $(this).mask('99.999-999').blur(function() {
                        form.controleCEP($(this));
                    });
                }

                else if ($(this).hasClass('mail')) {
                    if (!has_email) {
                        form.addMailValidator();
                        has_email = true;
                    }
                }
            }
            else if (type == 'file') {
                if (!has_file) {
                    form.addFileValidator();
                    has_file = true;
                }
            }

        });
        this.iniValidator();
    },
    controleCEP: function(cep_element) {
        var controle = $(cep_element).attr('controle');
        var endereco = null, bairro = null, cidade = null, estado = null, num = null;

        if (controle != null) {
            $('input[controle=' + controle + '], select[controle=' + controle + ']').each(function() {
                if ($(this).hasClass('bairro'))
                    bairro = $(this);
                else if ($(this).hasClass('cidade'))
                    cidade = $(this);
                else if ($(this).hasClass('estado'))
                    estado = $(this);
                else if ($(this).hasClass('endereco'))
                    endereco = $(this);
                else if ($(this).hasClass('num'))
                    num = $(this);
            });

            $(num).focus();

            if (endereco != null && bairro != null && cidade != null && estado != null && num != null)
                form.buscaDadosCEP($(cep_element), endereco, bairro, cidade, estado, num);
        }
    },
    buscaDadosCEP: function(cep, endereco, bairro, cidade, estado) {
        var cep_val = cep.val().toString().replace('-', '').replace('.', '');
        if (cep_val.length > 0) {
            $.ajax({
                url: url_ajax,
                type: 'POST',
                data: 'cep=' + cep_val,
                dataType: 'json',
                beforeSend: function() {
                    jQuery("#loading").show();
                },
                complete: function() {
                    jQuery("#loading").hide();
                },
                success: function(data) {
                    if (parseInt(data.sucesso) == 1) {
                        endereco.val(data.rua);
                        bairro.val(data.bairro);
                        form.buscaEstadosCidades(cidade, estado, data.estado, data.cidade);
                    }
                    else
                        exibeMensagem('CEP não encontrado', "Busca de CEP");
                },
                error: function(error) {
                    console.log(error);
                }

            });
        }
    },
    buscaEstadosCidades: function(cidades, estados, val_estado, val_cidade) {
        if ($.isArray(cidades) && $.isArray(estados)) {
            var tam = cidades.length;

            if (tam == estados.length) {
                for (var i = 0; i < tam; i++) {
                    new dgCidadesEstados({
                        estado: $(estados[i]).get(0),
                        cidade: $(cidades[i]).get(0),
                        estadoVal: val_estado,
                        cidadeVal: val_cidade
                    });
                }
            }
        }

        else
            new dgCidadesEstados({
                estado: $(estados).get(0),
                cidade: $(cidades).get(0),
                estadoVal: val_estado,
                cidadeVal: val_cidade
            });
    },
    iniValidator: function() {
        $.extend($.validator.messages, {
            required: "Este campo &eacute; requerido.",
            remote: "Por favor, corrija este campo.",
            email: "Por favor, forne&ccedil;a um endere&ccedil;o eletr&ocirc;nico v&aacute;lido.",
            url: "Por favor, forne&ccedil;a uma URL v&aacute;lida.",
            date: "Por favor, forne&ccedil;a uma data v&aacute;lida.",
            dateISO: "Por favor, forne&ccedil;a uma data v&aacute;lida (ISO).",
            number: "Por favor, forne&ccedil;a um n&uacute;mero v&aacute;lido.",
            digits: "Por favor, forne&ccedil;a somente d&iacute;gitos.",
            creditcard: "Por favor, forne&ccedil;a um cart&atilde;o de cr&eacute;dito v&aacute;lido.",
            equalTo: "Por favor, forne&ccedil;a o mesmo valor novamente.",
            accept: "Por favor, forne&ccedil;a um valor com uma extens&atilde;o v&aacute;lida.",
            maxlength: $.validator.format("Por favor, forne&ccedil;a n&atilde;o mais que {0} caracteres."),
            minlength: $.validator.format("Por favor, forne&ccedil;a ao menos {0} caracteres."),
            rangelength: $.validator.format("Por favor, forne&ccedil;a um valor entre {0} e {1} caracteres de comprimento."),
            range: $.validator.format("Por favor, forne&ccedil;a um valor entre {0} e {1}."),
            max: $.validator.format("Por favor, forne&ccedil;a um valor menor ou igual a {0}."),
            min: $.validator.format("Por favor, forne&ccedil;a um valor maior ou igual a {0}.")
        });

        $.validator.addClassRules({
            obrigatorio: {
                required: true
            },
            numero: {
                number: true
            }
        });

        $('form').validate({
            errorPlacement: function(error, element) {
                var next = $(element).next();

                if (element.attr('type') === 'radio' || element.attr('type') === 'checkbox')
                    error.insertAfter(element.parents('ul'));

                else if (next.length > 0)
                    error.insertAfter(next);
                else
                    error.insertAfter(element);
            }
        });
    },
    addTimeValidator: function() {
        $.validator.addMethod("time", function(value, element) {
            return this.optional(element) || /^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/i.test(value);
        }, "Hora inválida!");
    },
    addCPFValidator: function() {
        $.validator.addMethod("CPF", function(value, element) {
            value = $.trim(value);

            value = value.replace('.', '');
            value = value.replace('.', '');
            cpf = value.replace('-', '');

            while (cpf.length < 11)
                cpf = "0" + cpf;
            var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
            var a = [];
            var b = 0;
            var c = 11;
            for (i = 0; i < 11; i++) {
                a[i] = cpf.charAt(i);
                if (i < 9)
                    b += (a[i] * --c);
            }
            if ((x = b % 11) < 2) {
                a[9] = 0;
            } else {
                a[9] = 11 - x;
            }
            b = 0;
            c = 11;
            for (y = 0; y < 10; y++)
                b += (a[y] * c--);
            if ((x = b % 11) < 2) {
                a[10] = 0;
            } else {
                a[10] = 11 - x;
            }

            var retorno = true;
            if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg))
                retorno = false;

            return this.optional(element) || retorno;

        }, "Informe um CPF válido.");

        $.validator.addClassRules({
            cpf: {
                CPF: true
            }
        });
    },
    addCNPJValidator: function() {
        $.validator.addMethod("CNPJ", function(cnpj, element) {
            var retorno = null;

            cnpj = $.trim(cnpj);

            cnpj = cnpj.replace('/', '');
            cnpj = cnpj.replace('.', '');
            cnpj = cnpj.replace('.', '');
            cnpj = cnpj.replace('-', '');

            var numeros, digitos, soma, i, resultado, pos, tamanho, digitos_iguais;
            digitos_iguais = 1;

            if (cnpj.length < 14 && cnpj.length < 15) {
                retorno = false;
            }
            for (i = 0; i < cnpj.length - 1; i++) {
                if (cnpj.charAt(i) != cnpj.charAt(i + 1)) {
                    digitos_iguais = 0;
                    break;
                }
            }

            if (!digitos_iguais) {
                tamanho = cnpj.length - 2;
                numeros = cnpj.substring(0, tamanho);
                digitos = cnpj.substring(tamanho);
                soma = 0;
                pos = tamanho - 7;

                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

                if (resultado != digitos.charAt(0))
                    retorno = false;

                tamanho = tamanho + 1;
                numeros = cnpj.substring(0, tamanho);
                soma = 0;
                pos = tamanho - 7;

                for (i = tamanho; i >= 1; i--) {
                    soma += numeros.charAt(tamanho - i) * pos--;
                    if (pos < 2)
                        pos = 9;
                }
                
                resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
                if (resultado != digitos.charAt(1)) {
                    retorno = false;
                }
                retorno = true;
            }
            else
                retorno = false;

            return this.optional(element) || retorno;

        }, "Informe um CNPJ válido.");

        $.validator.addClassRules({
            cnpj: {
                CNPJ: true
            }
        });
    },
    addDataValidator: function() {
        $.validator.addMethod("data_BR", function(value, element) {
            var split_data = value.toString().split('/');

            if (split_data != null && split_data.length == 3) {
                if (split_data[2].length > 0 && split_data[1].length > 0 && split_data[0].length > 0) {
                    console.log(split_data[2]);
                    var data = new Date(split_data[2] + '/' + split_data[1] + '/' + split_data[0] + " 00:00:00");

                    if (!isNaN(Date.parse(data))) {
                        if (data.getDate() == split_data[0] && data.getMonth() + 1 == split_data[1] && data.getFullYear() == split_data[2])
                            return true;
                    }
                    return false;
                }
                return true;
            }
            return false;

        }, "Informe uma data válida");

        $.validator.addClassRules({
            data: {
                data_BR: true
            }
        });
    },
    addFileValidator: function() {
        $.validator.addClassRules({
            file_img: {
                accept: "image/jpg, image/gif, image/jpeg, image/png"
            },
            file_doc: {
                accept: "application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            },
            file_arq: {
                accept: "image/jpg, image/gif, image/jpeg, image/png, application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            }


        });
    },
    addMailValidator: function() {
        $.validator.addClassRules({
            mail: {
                email: true
            }
        });
    }
};