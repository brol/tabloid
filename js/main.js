$(function() {

	// Slider - Cycle Plugin
    $('#slider').cycle({
		fx: 'fade' // choose your transition type, ex: fade, scrollUp, shuffle, etc...
	});


	// Jquery - Autocomplete Plugin
	$('#q,#query').autocomplete({
		minLength: 2,
		source: website.ajaxsearch
	});

});