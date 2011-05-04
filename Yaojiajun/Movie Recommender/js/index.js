$(document).ready(function(){
	$("a.fancybox").fancybox({
			'hideOnContentClick' : false,
			'centerOnscroll' : 'yes',
			'transitionIn' : 'elastic',
			'transitionOut' : 'elastic',
			'onClosed' : function(){
				$("#email").val('');
				$("#password").val('');
			}
	    });
	});