(function($){
	'use scrict';

	$(document).ready(function () {

		$(".wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li").prepend('<span class="tick"></span>');


		$(".wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li").click(function(){
		    $(this).find("span").toggleClass("ticked");
		    $(this).toggleClass("ticked");
		});

	});

})(jQuery);