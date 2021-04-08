jQuery(document).on('ready', function() {
	jQuery(".regular").slick({
		slidesToShow: logo_show,
		slidesToScroll: logo_slide,
		autoplay: true,
  		// autoplaySpeed: 1000,
  		arrows: logo_arrows,
  		// dots: true,
		// infinite: true,
	});
});

jQuery(window).on('load', function() {
	console.log('hello');
	jQuery('.slider-section').show();
});