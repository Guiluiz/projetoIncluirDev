/**
 * Controle do gerenciamento de atividades
 * @returns {undefined}
 */

var controle_distribuicao = (function() {

    var distribuicao = {
        campos_curso: $('select[id^="curso"'),
        campos_disciplina: $('select[id^="disciplina"'),
        campos_horario_inicio: $('input[id^="horario_inicio"'),
        campos_horario_fim: $('input[id^="horario_fim"'),
        campos_tipo_divisao: $('select[id^="dividir_por"'),
        campos_ordem_divisao: $('select[id^="order"'),
        campos_divisao_igualitaria: $('input[id^="divisao_igualitaria"'),
        campos_quantidade: $('input[id^="quantidade"'),
        index:0,
        btn_add: $('#add'),
        btn_remove: $('#remove'),
        url_ajax_disciplina: null
    };

    distribuicao.setValues = function(url_ajax_disciplina) {
        distribuicao.url_ajax_disciplina = url_ajax_disciplina;
        distribuicao.ini();
    };

    distribuicao.ini = function() {
        campos_curso.each(function(){
        
        });
        
    };

    return {ini: distribuicao.setValues};
})();