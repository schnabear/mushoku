/**
 * @author Hidehito NOZAWA
 * @author schnabear
 * @copyright 2010 Hidehito NOZAWA
 * @licence GNU GPL v3
 */

jQuery(function($)
{
	var formElement     = null;
	var textareaElement = null;

	var construct = function()
	{
		formElement     = $("#message");
		textareaElement = $("#message textarea");

		roundBalloons();
		textareaElement.keyup(keyListener);

		$.each($(".bubble"), addTail);

		$("#talks .talk dt").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});
	}

	var roundBalloons = function()
	{
		$("#talks dl.talk dd div.bubble p.body").each(roundBalloon);
	}

	var roundBalloon = function()
	{
		// IE 7 only...
		if ( !isIE() || !window.XMLHttpRequest || document.querySelectorAll )
		{
			return;
		}

		var width = $(this).width();
		var borderWidth = $(this).css('border-width');
		var padding = $(this).css('padding-left');
		var color = $(this).css('border-color');
		width = width + padding.replace(/px/, '') * 2;

		$(this).corner("round 10px cc:"+color)
			.parent().css({
				"background" : color,
				"padding" : borderWidth,
				"width" : width
			}).corner("round 13px");
	}

	var addTail = function()
	{
		if ( isIE() )
		{
			return;
		}

		var height = $(this).find(".body").height() + 30 + 8;
		var top = (Math.round((200 - height) / 2) + 24) * -1;
		var bgimg  = $(this).find(".body").css("background-image");
		var rand = Math.floor(Math.random()*3); // Tail I Choose You
		var tailTop = "0px";

		if ( rand == 1 )
		{
			tailTop = "-17px";
		}
		else if ( rand == 2 )
		{
			tailTop = "-34px";
		}

		top = top + 1;

		$(this).find(".body").css({"margin": "0 0 0 15px"});

		$(this).prepend('<div><div></div></div>')
			.css({"margin":"-16px 0 0 0"});
		$(this).children("div").css({
			"position":"relative",
			"float":"left",
			"margin":"0 0 0 0",
			"top":"36px",
			"left":"-3px",
			"width":"22px",
			"height":"16px",
			"background":"transparent "+bgimg+" left "+top+"px repeat-x"
		});
		$(this).children("div").children("div").css({
			"width":"100%",
			"height":"100%",
			"background":"transparent url('"+duraUrl+"/css/tail.png') left "+tailTop+" no-repeat"
		});
	}

	var keyListener = function(e)
	{
		var content = textareaElement.val();
		if ( content != content.replace(/[\r\n]+/g, "") )
		{
			formElement.submit();
			return false;
		}
	}

	var isIE = function()
	{
		var isMSIE = /*@cc_on!@*/false;
		return isMSIE;
	}

	construct();
});
