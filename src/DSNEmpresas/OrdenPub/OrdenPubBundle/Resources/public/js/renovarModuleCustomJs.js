/* Funciones que limita la cantidad de caracteres de un textarea */
function limita(elEvento, maximoCaracteres) {  
    var elemento = document.getElementById("mediterraneofm_mediterraneofmbundle_renovarordenpubtype_texto_publicidad");

    // Obtener la tecla pulsada 
    var evento = elEvento || window.event;
    var codigoCaracter = evento.charCode || evento.keyCode;

    // Permitir utilizar las teclas con flecha horizontal
    if(codigoCaracter == 37 || codigoCaracter == 39) {
      return true;
    }

    // Permitir borrar con la tecla Backspace y con la tecla Supr.
    if(codigoCaracter == 8 || codigoCaracter == 46) {
      return true;
    }
    else if(elemento.value.length >= maximoCaracteres ) {
      return false;
    }
    else {
      return true;
    }
}

function actualizaInfo(maximoCaracteres) {
    var elemento = document.getElementById("mediterraneofm_mediterraneofmbundle_renovarordenpubtype_texto_publicidad");
    var info = document.getElementById("info");

    if(elemento.value.length >= maximoCaracteres ) {
      info.innerHTML = "Máximo "+maximoCaracteres+" caracteres";
    }
    else {
      info.innerHTML = "Quedan "+(maximoCaracteres-elemento.value.length)+" caracteres";
    }
}
/* EOF CONTROL textarea */

$(function() {
    $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_fecha').datepicker({
        dateFormat: "dd-mm-yy",
    });
});

/* Funciones de agregado y eliminado de los formularios embebidos */
function addTagForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    var pautasForm = $('#pautasForm').clone(true);
    pautasForm.find('.emisoras').attr('id', 'emisoras'+index);
    pautasForm.find('.tarifas').attr('id', 'tarifas'+index);
    pautasForm.find('.pautas').attr('id', 'pautas'+index);

    var htmlPautasForm = pautasForm.html();

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<li></li>').append(htmlPautasForm+newForm);
    $newLinkLi.before($newFormLi);
    addTagFormDeleteLink($newFormLi);
    
    $( "#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_" + index + "_fecha_desde, #mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_" + index + "_fecha_hasta" ).datepicker({ 
        dateFormat: "dd-mm-yy",
    });

    $( '#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_desde' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_desde' ).tooltip( "disable" );
    });

    $( '#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).tooltip( "disable" );
    });

    $( '#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_desde, #mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).tooltip({
        position: {
            my: "top-35",
            at: "top-35",
        },
    });

    $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_descuento').live('change paste keyup input', function() {
        calc(index);
    });

    $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_recargo').live('change paste keyup input', function() {
        calc(index);
    });

    $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_neto').live('change paste keyup input', function() {
        calc(index);
    });
}

function calc(index) {
    var recargo, descuento;

    var recPorc = $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_recargo').val();
    var descPorc = $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_descuento').val();
    var neto = $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_neto').val();

    /* Cambiamos el punto decimal ya que de esta forma se calcula con js */
    neto = neto.replace(',', '.');
    recPorc = recPorc.replace(',', '.');
    descPorc = descPorc.replace(',', '.');

    recargo = (parseFloat(neto) * recPorc) / 100;
    descuento = (parseFloat(neto) * descPorc) / 100;

    var finalNeto = parseFloat(neto) + parseFloat(recargo) - parseFloat(descuento);
    $('#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_' + index + '_total').val(phpjs.numberformat(finalNeto, 2, ',', ''));
}

function pautas(id) {
    var prec = $('#pautas'+id).val(); /* id de pauta y costo */
    var prec2 = prec.split('-'); /* separo id de pauta y costo */

    $("#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_" + id + "_neto").val(phpjs.numberformat(prec2[1], 2, ',', '')); /* selecciono costo */
    calc(id);
    $( "#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_" + id + "_id_costotarifas" ).val('');
    $( "#mediterraneofm_mediterraneofmbundle_renovarordenpubtype_id_tarifa_" + id + "_id_costotarifas" ).val(prec2[0]);
}


