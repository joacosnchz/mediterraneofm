$(function() {
    $( "#mediterraneofm_mediterraneofmbundle_liquidaciones_fecha, #mediterraneofm_mediterraneofmbundle_liquidaciones_fechaHasta" ).datepicker({
        dateFormat: "dd-mm-yy",
    });
    
    $( "#mediterraneofm_mediterraneofmbundle_liquidaciones_fecha, #mediterraneofm_mediterraneofmbundle_liquidaciones_fechaHasta" ).tooltip();
    
    $("#mediterraneofm_mediterraneofmbundle_liquidaciones_fecha").focus(function() {
        $( "#mediterraneofm_mediterraneofmbundle_liquidaciones_fecha" ).tooltip('disable');
    });
    
    $("#mediterraneofm_mediterraneofmbundle_liquidaciones_fechaHasta").focus(function() {
        $('#mediterraneofm_mediterraneofmbundle_liquidaciones_fechaHasta').tooltip('disable');
    });
});