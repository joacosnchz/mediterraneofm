$(function() {
	$('#searchDialog').dialog({
		autoOpen: false,
		modal: true,
	});

	$('.sear').click(function() {
		$('#searchDialog').dialog('open');
	});

	$('#buscar').click(function() {
		var url = $('.act').val();

		$.post(url, {'property': $('.property').val(), 'search': $('.search').val(), 'class': $('.property').attr('searchOn')}).done(function(data) {
			$('#response').html(data);
		});
	});

	$('.search').live('keydown', function(event) {
		var keycode = event.keyCode;
		if(keycode == 13) {
			var url = $('.act').val();

			$.post(url, {'property': $('.property').val(), 'search': $('.search').val(), 'class': $('.property').attr('searchOn')}).done(function(data) {
				$('#response').html(data);
			});		
		}
	});

	$('.selected').click(function() {
		$('#searchDialog').dialog('close');
	});
});

function onSelection(value) {
	$('.result').val(value);
	$('#searchDialog').dialog('close');
}