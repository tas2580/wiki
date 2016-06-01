$('.from').change(function(e) {
	var from = e.target.id;
	from = from.replace('from', '');
	$('.to').hide();
	var i = 0;
	while (i < from) {
		$('#to'+i).show();
		i++;
	}
});
$('.to').change(function(e) {
	var to = e.target.id;
	to = to.replace('to', '');
	$('.from').show();
	var i = to;
	while (i > 0) {
		$('#from'+i).hide();
		i--;
	}
});

$( document ).ready(function() {
    $('.to').hide();
	$('#to1').show();
	$('#from1').hide();
});
