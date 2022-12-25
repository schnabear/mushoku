/**
 * @author ???
 * @author schnabear
 * @licence MIT
 */

$(function() {
	var fatOnHover = function(elements, zoomSize, animationSpeed) {
		elements.each(function() {
			var wrap, options, tempZ, currFontSize, zIndexOffset = 900, element = $(this);
			tempZ = element.css('z-index');
			currFontSize = element.css('font-size')
			options = {
				height : element.height(),
				width : element.width(),
				fontSize : parseFloat(currFontSize),
				fontEnding : currFontSize.slice(-2),
				currZIndex : (tempZ === undefined || tempZ === 'auto') ? 100 : parseInt(tempZ)
			};
			$.extend(options, {
				bigWidth : (options.width / 100) * zoomSize,
				bigHeight : (options.height / 100) * zoomSize,
				bigZIndex : options.currZIndex + zIndexOffset,
				bigFontSize : (options.fontSize / 100) * zoomSize
			});
			$.extend(options, {
				left : (options.bigWidth - options.width) / 2,
				top : (options.bigHeight - options.height) / 2
			});
			wrap = ['<div style="height:',options.height,'px; width:',options.width,'px; position:relative;"></div>'].join('');
			element.data(options).wrap(wrap);
		})
		// Define function/behaviours for focus/hover
		.bind('mouseenter mouseover focus', function() {
			var element = $(this);
			element
				.css('z-index', element.data('bigZIndex'))
				.stop()
				.animate({
					'width':element.data('bigWidth'),
					'height':element.data('bigHeight'),
					'left':-element.data('left'),
					'top':-element.data('top'),
					'fontSize':[
						element.data('bigFontSize'),
						element.data('fontEnding')
					].join('')
				}, animationSpeed);
		})
		// Define function/behaviours for loss of focus/hover (normal)
		.bind('mouseleave mouseout blur', function() {
				var element = $(this);
				element
					.css('z-index', element.data('currZIndex'))
					.stop()
					.animate({
						'width':element.data('width'),
						'height':element.data('height'),
						'left':'0',
						'top':'0',
						'fontSize':[
							element.data('fontSize'),
							element.data('fontEnding')
						].join('')
					}, animationSpeed);
		})
		// Assigns position absolute to the item to be enlarged
		.css('position', 'absolute')
		.css('left', '0')
		.css('top', '0')
	};
	fatOnHover($('#avatar li label img'), 100, 100);
});
