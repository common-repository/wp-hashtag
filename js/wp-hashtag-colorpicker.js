(function($){
	var initLayout = function() {
		var hash = window.location.hash.replace('#', '');
		$('#wpht_anchor_colorSelector').ColorPicker({
			color: '#'+$('#wpht_anchor_color').val(),
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('#wpht_anchor_colorSelector div').css('backgroundColor', '#' + hex);
				$('#wpht_anchor_color').val(hex);
			}
		});
		
		$('#wpht_anchor_backgroundSelector').ColorPicker({
			color: '#'+$('#wpht_anchor_background').val(),
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('#wpht_anchor_backgroundSelector div').css('backgroundColor', '#' + hex);
				$('#wpht_anchor_background').val(hex);
			}
		});
		
		$('#wpht_anchor_color_hoverSelector').ColorPicker({
			color: '#'+$('#wpht_anchor_color_hover').val(),
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('#wpht_anchor_color_hoverSelector div').css('backgroundColor', '#' + hex);
				$('#wpht_anchor_color_hover').val(hex);
			}
		});
		
		$('#wpht_anchor_background_hoverSelector').ColorPicker({
			color: '#'+$('#wpht_anchor_background_hover').val(),
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('#wpht_anchor_background_hoverSelector div').css('backgroundColor', '#' + hex);
				$('#wpht_anchor_background_hover').val(hex);
			}
		});
	};
	
	
	EYE.register(initLayout, 'init');
})(jQuery)