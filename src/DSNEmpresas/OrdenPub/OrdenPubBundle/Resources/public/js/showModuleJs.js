$(function() {
    var id_costo = $('#mediterraneofm_mediterraneofmbundle_ordenpubfiltertype_id_costotarifas').val();
    if(id_costo != "") {
        var url = $("#formFilter").attr("action");

        $.post(url, {'idTarifa': $('.tarifas').val()}).done(function(content) {
            $('.pautas').html(content);
            if($('.pautas').val != 'null') {
                if($('.pautas option[value="' + id_costo + '"]').length > 0) {
                    $('.pautas option[value="' + id_costo + '"]').attr('selected', 'selected');
                }
            }
        });
    }
    
    $('.tarifas').change(function() {
        var url = $("#formFilter").attr("action");
        
        $('#mediterraneofm_mediterraneofmbundle_ordenpubfiltertype_id_costotarifas').val('null');
        
        $.post(url, {'idTarifa': $(this).val()}).done(function(content) {
            $('.pautas').html(content);
        });
    });
    
    $('.pautas').change(function() {
        var pauta = $(this).val();
        
        $('#mediterraneofm_mediterraneofmbundle_ordenpubfiltertype_id_costotarifas').val(pauta);
        
        $('#formFilter').submit();
    });
    
    $( '.pautas' ).tooltip({
        position: {
            my: "top-35",
            at: "top-35",
        },
    });
    
    $( "#mediterraneofm_mediterraneofmbundle_ordenpubfiltertype_fecha_desde, #mediterraneofm_mediterraneofmbundle_ordenpubfiltertype_fecha_hasta" ).datepicker({ 
        dateFormat: "dd-mm-yy"
    });
});

function checkAll(checkname, bx) {
    for (i = 0; i < checkname.length; i++){
        checkname[i].checked = bx.checked? true:false;
    }
}
function checkPage(bx){


    var bxs = document.getElementByTagName ( "table" ).getElementsByTagName ( "link[]" ); 

    for(i = 0; i < bxs.length; i++){
        bxs[i].checked = bx.checked? true:false;
    }
}