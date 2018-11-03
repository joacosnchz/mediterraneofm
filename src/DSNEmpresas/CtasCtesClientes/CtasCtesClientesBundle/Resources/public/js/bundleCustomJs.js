phpjs = new phpjs(); // Instancia de las funciones de php convertidas a js

function in_array(needle, haystack) {
    for(var i in haystack) {
        if(haystack[i] == needle) return true;
    }
    return false;
}

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

$(function() {
    $( "#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_desde, #mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_hasta").datepicker({ 
        dateFormat: "dd-mm-yy",
    });
    
    $( "#mediterraneofm_mediterraneofmbundle_movctacteclientetype_fecha").datepicker({ 
        dateFormat: "dd-mm-yy",
        minDate: 0,
    });

    $( '#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_desde' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_desde' ).tooltip( "disable" );
    });

    $( '#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_hasta' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_hasta' ).tooltip( "disable" );
    });
    
    $( '#mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_desde, #mediterraneofm_mediterraneofmbundle_clientesfiltertype_fecha_fecha_hasta, #mediterraneofm_mediterraneofmbundle_movctacteclientetype_idTipoDocumento, #mediterraneofm_mediterraneofmbundle_movctacteclientetype_idCliente, #mediterraneofm_mediterraneofmbundle_movctacteclientetype_haber' ).tooltip({
    	position: {
    		my: "top-35",
    		at: "top-35",
    	},
    });
    
    /* Generar PAGO */
    $( "#form3" ).submit(function(event) {
        var url2 = $("#form3").attr("action");
        event.preventDefault();

        $.post(url2, {dat: ids, dat2: ids2, dat3: ids3}).done(function(content) {
            if(content == 'Solo se pueden generar pagos de ordenes de publicidad.') {
                alert(content);
                $('#formI').dialog('close');
                $('#mediterraneofm_mediterraneofmbundle_movctacteclientetype_fecha').datepicker('hide');
            }
            else {
                var array = content.split('-'); /* Content devuelve el string con el total y tambien el id de los docs */
                window.param = array[0]; /* El parametro para definir si se debe generar nd o otro doc nuevo */
                $('#mediterraneofm_mediterraneofmbundle_movctacteclientetype_haber').val(phpjs.numberformat(array[0], 2, ',', ''));
                $('#docs').val(array[1]);
                $('#docs2').val(array[2]);
            }
        });
    });
    /* EOF GENERAR PAGO */

    /* CREACION DEL FORMULARIO DE PAGO */
    window.BA = false;
    $( "#nuev" ).click(function() { /* Si el boton de generar nuevo movimiento es clickeado */
        window.BA = true;

        window.ids = $('input[type=checkbox]:checked').map(function() {
            return $(this).attr('value');
        }).get(); /* declaro la variable como global para poder utilizarla en la funcion que hace $.post */

        window.ids2 = $('input[type=checkbox]:checked').map(function() {
            return $(this).attr('value2'); /* devuelve ids de documentos seleccionados */
        }).get();

        window.ids3 = $('input[type=checkbox]:checked').map(function() {
            return $(this).attr('value3'); /* devuelve nros. de documentos seleccionados */
        }).get();
        
        
        var idsStr = window.ids;
        
        if(!in_array('no', idsStr)) {
            if (window.ids.length > 0) {
                $('#form3').submit();
                $( "#formI" ).dialog( "open" );
            }
            else {
                $('#response').html('<div id="error">Seleccione un elemento de la lista.</div>').fadeIn( 100 ).delay( 1000 ).slideUp( 800 );
            }
        }
        else {
            alert('Solo se pueden generar pagos de ordenes de publicidad.');
        }
    });
    
    $( "#formI" ).dialog({
        autoOpen: false,
        modal: true,
        heigth: 350,
        width: 550,
    });
    /* EOF FORMULARIO DE PAGO */

    /* BORRAR MOVIMIENTO */
    $('#borrar').click(function() {
        var ids;

        ids = $('input[type=checkbox]:checked').map(function() {
            return $(this).attr('value2');
        }).get();

        if(ids.length > 0) {
            $( "#borrar-confirm" ).dialog( "open" );
        }
        else {
            $( '#response' ).html('<div id="error">Debe seleccionar un elemento de la lista.</div>').fadeIn( 100 ).delay( 1000 ).slideUp( 800 );
        }
    });
    /* EOF BORRAR MOVIMIENTO */
});