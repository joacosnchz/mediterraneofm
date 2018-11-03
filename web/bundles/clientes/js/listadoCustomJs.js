$(function() {
    $( document ).tooltip();
    
    $('#selectAll').toggle(
        function() {
            $('#list .tf').prop('checked', true);
        },
        function() {
            $('#list .tf').prop('checked', false);
        }
    );
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


