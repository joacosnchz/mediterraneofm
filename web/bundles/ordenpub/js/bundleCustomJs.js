/* Funcion que permite ingresar solo números a un input */
phpjs = new phpjs(); // Instancia de las funciones de php convertidas a js

function justNumbers(e) {
    var keynum = window.event ? window.event.keyCode : e.which;
    if ((keynum == 8) || (keynum == 44) || (keynum == 09))
        return true;

    return /\d/.test(String.fromCharCode(keynum));
}
/* EOF control de inputs */

$(function() {
    $( "#dialog" ).dialog({
        autoOpen: false,
        width: 600,
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "fade",
            duration: 500
        }
    });

    $( "#opener" ).click(function() {
        $( "#dialog" ).dialog( "open" );
    });

    $('#cl').click(function() {
        $( "#dialog" ).dialog( "close" );
    });

    $('#dialog2').dialog({
        autoOpen: false,
        resizable: false,
        height:350,
        modal: true,
        buttons: {
            "Cancelar": function() {
                $(this).dialog('close');
            },
            "Aceptar": function() {
                $('#form1').submit();
            }
        }
    });

    $('#conf').click(function(event) {
         event.preventDefault();
         $('#dialog2').dialog('open');
    });
});

/* Funciones de agregado y eliminado de los formularios embebidos */
/* function addTagForm($collectionHolder, $newLinkLi) {
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
    
    forms = ['insert', 'renovar'];
    var i = 0, j = 0;
    j = forms.length;
    
    for(i = 0;i < j;i++) {
        $( "#mediterraneofm_mediterraneofmbundle_" + forms[i] + "ordenpubtype_id_tarifa_" + index + "_fecha_desde, #mediterraneofm_mediterraneofmbundle_" + forms[i] + "ordenpubtype_id_tarifa_" + index + "_fecha_hasta" ).datepicker({ 
            dateFormat: "dd-mm-yy",
        });

        $( '#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_desde' ).focus(function() {
            $( '#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_desde' ).tooltip( "disable" );
        });

        $( '#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).focus(function() {
            $( '#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).tooltip( "disable" );
        });

        $( '#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_desde, #mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_fecha_hasta' ).tooltip({
            position: {
                my: "top-35",
                at: "top-35",
            },
        });

        $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_descuento').live('change paste keyup input', function() {
            calc(index);
        });

        $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_recargo').live('change paste keyup input', function() {
            calc(index);
        });

        $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_neto').live('change paste keyup input', function() {
            calc(index);
        });
    }
} */

/* function calc(index) {
    var recargo, descuento;
    forms = ['insert', 'renovar'];
    var i = 0, j = 0;
    j = forms.length;
    
    for(i = 0;i < j;i++) {
        var recPorc = $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_recargo').val();
        var descPorc = $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_descuento').val();
        var neto = $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_neto').val();
        
        Cambiamos el punto decimal ya que de esta forma se calcula con js
        neto = neto.replace(',', '.');
        recPorc = recPorc.replace(',', '.');
        descPorc = descPorc.replace(',', '.');

        recargo = (parseFloat(neto) * recPorc) / 100;
        descuento = (parseFloat(neto) * descPorc) / 100;

        var finalNeto = parseFloat(neto) + parseFloat(recargo) - parseFloat(descuento);
        $('#mediterraneofm_mediterraneofmbundle_' + forms[i] + 'ordenpubtype_id_tarifa_' + index + '_total').val(phpjs.numberformat(finalNeto, 2, ',', ''));
    }
} */

var $collectionHolder;

// setup an "add a tag" link
var $addTagLink = $('.add_tag_link');
var $newLinkLi = $('<li></li>').append($addTagLink);

$(function() {
    // Get the ul that holds the collection of tags
    $collectionHolder = $('#collectionHolder');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $('.add_tag_link').click(function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addTagForm($collectionHolder, $newLinkLi);
    });


});

function addTagFormDeleteLink($tagFormLi) {
    /* var $removeFormA = $('<tr><td><a href="#" id="underlined">Eliminar esta pauta</a></td></tr>');
    $tagFormLi.find('table tr:last').after($removeFormA); */
    var $removeFormA = $('<a href="#" id="underlined">Eliminar esta pauta</a>');
    $tagFormLi.append($removeFormA);
    

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the tag form
        $tagFormLi.remove();
    });
}

function submEmisoras(index) {
    var url = $("#form1").attr("action");

    if($('#emisoras'+index).val() != 0) {
        $.post(url, {'id_emisora': $('#emisoras'+index).val() }).done(function(data) {
            $('#tarifas'+index).html(data);
        });
    }
}

function subm(index) {
    var url = $("#form1").attr("action");

    if ($('#tarifas'+index).val() != 0) {
        $.post(url, {'id_tarifa': $('#tarifas'+index).val() })
        .done(function(data) {
            $("#pautas"+index).html(data);
        });
    }
}

function emisoras(id) {
    $('#tarifas'+id).html('');
    submEmisoras(id);
}

function tarifas(id) {
    $("#pautas"+id).html('');
    subm(id);
}

$(function() {
    $('.emisoras').live('change', function() {
        var id = $(this).attr('id');

        id = id.replace('emisoras', '');

        emisoras(id);
    });
});

$(function() {
    $('.tarifas').live('change', function() {
        var id = $(this).attr('id');

        id = id.replace('tarifas', '');

        tarifas(id);
    });
});

$(function() {
    $('.pautas').live('change', function() {
        var id = $(this).attr('id');

        id = id.replace('pautas', '');

        pautas(id);
    });
});
/* EOF funciones de formularios */