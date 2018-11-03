$(function() {
    $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_duracion_desde, #mediterraneofm_mediterraneofmbundle_programasfiltertype_duracion_hasta').timepicker({ 'timeFormat': 'H:i' });
    
    $( '#response' ).tooltip({
        position: {
            my: "top-35",
            at: "top-35",
        },
    });
    
    $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_id_programacion_nombre').live('change', function() {
        var url = $("#form1").attr("action");

        $.post(url, { id_emisora: $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_id_programacion_nombre').val() })
            .done(function(data) {
                $("#response").html(data);
            });
    });

    $('#response').live('change', function() {
       $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_id_programacion_programacion').val($('#response').val());
    });
    
    var id_prog = $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_id_programacion_programacion').val();
    if(id_prog != "") {
        var url = $("#form1").attr("action");
        
        $.post(url, { id_emisora: $('#mediterraneofm_mediterraneofmbundle_programasfiltertype_id_programacion_nombre').val() })
            .done(function(data) {
                $("#response").html(data);
                if($('#response').val != 'null') {
                    if($('#response option[value="' + id_prog + '"]').val() > 0) {
                        $('#response option[value="' + id_prog + '"]').attr('selected', 'selected');
                    }
                }
            });
    }
});


