$(function() {
    $( "#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_desde, #mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_hasta").datepicker({ 
        dateFormat: "dd-mm-yy",
    });
    
    $( "#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_fecha_fecha_desde").datepicker({ 
        dateFormat: "dd-mm-yy",
        minDate: 0,
    });

    $( '#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_desde' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_desde' ).tooltip( "disable" );
    });
    
    $( '#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_hasta' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_hasta' ).tooltip( "disable" );
    });
    
    $( '#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_fecha_fecha_desde' ).focus(function() {
        $( '#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_fecha_fecha_desde' ).tooltip( "disable" );
    });

    $( '#mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_desde, #mediterraneofm_mediterraneofmbundle_insertliquidacionestype_fecha_fecha_hasta, #mediterraneofm_mediterraneofmbundle_insertliquidacionestype_idAgencia, #mediterraneofm_mediterraneofmbundle_agenciasfiltertype_idAgencia, #mediterraneofm_mediterraneofmbundle_agenciasfiltertype_fecha_fecha_desde' ).tooltip({
        position: {
            my: "top-35",
            at: "top-35",
        },
    });
    
    $('#dialog-generar').dialog({
        autoOpen: false,
        width: 450,
        modal:true,
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "fade",
            duration: 500
        }
    });
    
    $('#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_idAgencia').prop('disabled', true);
    
    $('#insertMovSubmit').live('click', function(event) {
        event.preventDefault();

        $('#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_idAgencia').prop('disabled', false);

        $('#insertMovimientoFrom').submit();
    });

    $('#generar').click(function() {
        $('#dialog-generar').dialog('open');
        $('#mediterraneofm_mediterraneofmbundle_agenciasfiltertype_fecha_fecha_desde').datepicker({
            dateFormat: "dd-mm-yy",
        });
    });
    
    $('#cl').click(function() {
        $('#dialog-generar').dialog('close');
    });
});

function number_format (number, decimals, dec_point, thousands_sep) {
  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + Math.round(n * k) / k;
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '').length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1).join('0');
  }
  return s.join(dec);
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

function justNumbers(e) {
    var keynum = window.event ? window.event.keyCode : e.which;
    if ((keynum == 8) || (keynum == 44) || (keynum == 9))
        return true;        

    return /\d/.test(String.fromCharCode(keynum));
}