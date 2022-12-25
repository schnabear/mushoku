/**
 * @author Hidehito NOZAWA
 * @author schnabear
 * @copyright 2010 Hidehito NOZAWA
 * @licence GNU GPL v3
 */

jQuery(function($)
{
	var postAction = null;
	var getAction  = null;

	var containerElement = null;
	var formElement     = null;
	var whisperElement  = null;
	var textareaElement = null;
	var whisperTextElement = null;
	var talksElement    = null;
	var murmursElement   = null;
	var membersElement  = null;
	var inviteElement   = null;
	var logoutElement   = null;
	var buttonElement   = null;
	var whisperButtonElement = null;
	var countElement	= null;
	var whisperCountElement = null;
	var iconElement     = null;
	var menuElement     = null;
	var roomNameElement = null;
	var roomLimitElement = null;
	var roomTotalElement = null;
	var roomCapacityElement = null;
	var settingPannelElement = null;
	var userListElement = null;
	var dropListElement = null;
	var whisperBox      = null;
	var windowElement   = null;
	var topButtonElement = null;
	var userProfileElement = null;

	var lastMessage  = '';
	var lastWhisperMessage = '';
	var lastUpdate   = 0;
	var lastRoomName = '';
	var notificationUID = '';
	var chatLog      = '';
	var isWindowActive = true;
	var isSubmitting = false;
	var isLoggedOut  = false;
	var isLoading    = false;
	var isShowingSettinPannel = false;

	var isUseAnime   = true;
	var isUseSound   = true;
	var isShowMember = false;
	var isUseWhisper = false;
	var isLock       = false;
	var isUseMention = false;
	var isShowInvite = false;
	var isUseNotification = false;

	var userId   = null;
	var userName = null;
	var userIcon = null;
	var userCode = null;

	var siteTitle = document.title;

	var messageLimit = 50;

	var construct = function()
	{
		var url = location.href.replace(/#/, '');

		if ( url.replace(/\?/, '') != url )
		{
			postAction = url+"&ajax=1";
			getAction  = duraUrl+'/index.php?controller=room&action=ajax';
		}
		else
		{
			postAction = url+"?ajax=1";
			getAction  = duraUrl+'/room/ajax/';
		}

		if ( isInFrame() )
		{
			window.top.location = window.location;

			return false;
		}

		containerElement = $("#body");
		formElement     = $("#message");
		textareaElement = $("#message textarea");
		whisperElement  = $("#whisper");
		whisperTextElement = $("#whisper textarea");
		talksElement    = $("#talks");
		murmursElement  = $("#murmurs");
		membersElement  = $("#members");
		inviteElement   = $("#invite");
		logoutElement   = $("input[name=logout]");
		buttonElement   = $("#message input[name=post]");
		whisperButtonElement = $("#whisper input[name=post]");
		countElement    = $("#counter");
		whisperCountElement = $("#whister");
		iconElement     = $("dl.talk dt");
		menuElement     = $("ul.menu");
		roomNameElement = $("#room_name");
		roomLimitElement = $("#room_limit");
		roomTotalElement = $("#room_total");
		roomCapacityElement = $("#room_capacity");
		blockListElement = $("#blocked_ip");
		settingPannelElement = $("#setting_pannel");
		userListElement = $("#user_list");
		dropListElement = $("ul.menu select[name=recipient]");
		whisperBox      = $("#whisper_box");
		windowElement   = $(window);
		topButtonElement = $("#pagetop");
		userProfileElement = $("#message div.user_profile");

		userId   = trim($("#user_id").text());
		userName = trim($("#user_name").text());
		userIcon = trim($("#user_icon").text());
		userCode = trim($("#user_code").text());

		messageMaxLength = 140;

		if ( typeof(GlobalMessageMaxLength) != 'undefined' )
		{
			messageMaxLength = GlobalMessageMaxLength;
		}

		appendEvents();
		separateMemberList();
		roundBalloons();
		shadowBalloons();
		populateChatLog();
		showControlPanel();
		toggleWhisperMessage();

		if ( typeof window.callPhantom === 'function' || navigator.webdriver )
		{
			logoutElement.click();

			return false;
		}

		if ( !window.XMLHttpRequest )
		{
			$("div.message_box").css({
				'position' : 'absolute',
				'width' : $(window).width() + 'px'
			});
			$("#talks_box").css('margin-top', '150px');
			topButtonElement.css({
				'position' : 'absolute'
			});
		}

		if ( useComet && !isIE() )
		{
			getMessages();
		}
		else
		{
			var timer = setInterval(function(){getMessagesOnce();}, 1500);
		}

		$.each($(".bubble"), addTail);
	}

	var appendEvents = function()
	{
		formElement.submit(submitMessage);
		whisperElement.submit(submitMessage);
		textareaElement.keyup(keyListener);
		whisperTextElement.keyup(keyListener);
		logoutElement.click(logout);
		iconElement.click(addUserNameToTextarea);
		menuElement.find("li.refresh").click(toggleRefresh);
		menuElement.find("li.sound").click(toggleSound);
		menuElement.find("li.member").click(toggleMember);
		menuElement.find("li.animation").click(toggleAnimation);
		menuElement.find("li.whisper").click(toggleWhisper);
		menuElement.find("li.lock").click(toggleLock);
		menuElement.find("li.mention").click(toggleMention);
		menuElement.find("li.invite").click(toggleInvite);
		menuElement.find("li.notification").click(toggleNotification);
		menuElement.find("li.chatlog").click(toggleChatLog);
		menuElement.find("li.setting").click(toggleSettingPannel);
		settingPannelElement.find("input[name=save]").click(changeRoomDetail);
		settingPannelElement.find("input[name=update]").click(changeRoomLimit);
		settingPannelElement.find("input[name=set]").click(changeRoomPassword);
		settingPannelElement.find("input[name=remove]").click(removeBlock);
		settingPannelElement.find("input[name=handover]").click(handoverHost);
		settingPannelElement.find("input[name=ban]").click(banUser);
		settingPannelElement.find("input[name=kick]").click(kickUser);
		dropListElement.click(toggleWhisperMessage);
		$("#talks_box .talk dt").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});
		$("#murmurs_box .talk dt").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});
		whisperBox.draggable({ cursor: 'move', appendTo: 'body', handle: 'div.whisper_message_box', snap: 'body' });
		whisperBox.resizable({ maxWidth: 730, minWidth: 713, minHeight: 500,
			resize: function()
			{
				var murmurHeight = whisperBox.height() - 147;
				$("#murmurs_box").css("height", murmurHeight);
			},
			stop: function()
			{
				var boxTop = whisperBox.offset().top;
				var boxLeft = whisperBox.offset().left;

				if ( !window.XMLHttpRequest )
				{
					whisperBox.css('position', 'absolute');
				}
				else
				{
					whisperBox.css("position", "fixed");
				}

				whisperBox.offset({ top: boxTop, left: boxLeft });
			}
		});
		windowElement.blur(function()
			{
				isWindowActive = false;
			}
		).focus(function()
			{
				isWindowActive = true;
			}
		);
		topButtonElement.hide();
		windowElement.scroll(function ()
			{
				if ( $(this).scrollTop() > 350 )
				{
					topButtonElement.fadeIn("slow");
				}
				else {
					topButtonElement.fadeOut("slow");
				}
			}
		);
		topButtonElement.click(function()
		{
			var target         = containerElement;
			var targetPosition = target.offset().top;

			if (target.length == 0)
			{
				return;
			}

			$('html, body').animate({scrollTop: targetPosition});

			return false;
		});
	}

	var submitMessage = function()
	{
		var isWhisper = ( $(this).attr('id') == "whisper" ) ? true : false;
		var message = isWhisper ? whisperTextElement.val() : textareaElement.val();
		message = message.replace(/[\r\n]+/g, "");

		if ( message.replace(/^[ \n]+$/, '') == '' )
		{
			if ( isWhisper )
			{
				whisperTextElement.val('');
			}
			else
			{
				textareaElement.val('');
			}

			return false;
		}

		if ( isSubmitting )
		{
			return false;
		}

		var data = isWhisper ? whisperElement.serialize() : formElement.serialize();

		if ( ( message == lastMessage && !isWhisper ) || ( message == lastWhisperMessage && isWhisper ) )
		{
			if ( confirm(t("Will you stop sending the same message? If you click 'Cancel' you can send it again.")) )
			{
				if ( isWhisper )
				{
					whisperTextElement.val('');
				}
				else
				{
					textareaElement.val('');
				}

				return false;
			}
		}

		isSubmitting = true;

		if ( isWhisper )
		{
			whisperTextElement.val('');
			whisperCountElement.html(messageMaxLength);
			whisperButtonElement.val(t("Sending..."));

			lastWhisperMessage = message;
		}
		else
		{
			textareaElement.val('');
			countElement.html(messageMaxLength);
			buttonElement.val(t("Sending..."));

			lastMessage = message;
		}

		if ( message.length - 1 > messageMaxLength )
		{
			message = message.substring(0, messageMaxLength)+"...";
		}

		writeSelfMessage(message, isWhisper);

		$.post(postAction, data,
			function()
			{
				isSubmitting = false;
				if ( isWhisper )
				{
					whisperButtonElement.val(t("POST!"));
				}
				else
				{
					buttonElement.val(t("POST!"));
				}
			}
		).fail(function()
			{
				isSubmitting = false;
				if ( isWhisper )
				{
					whisperButtonElement.val(t("POST!"));
				}
				else
				{
					buttonElement.val(t("POST!"));
				}

				alert(t("Server error."));
			}
		);

		return false;
	}

	var getMessagesOnce = function()
	{
		if ( isLoading || isLoggedOut )
		{
			return;
		}

		isLoading = true;

		$.get(getAction, {'fast':'1', 'access':accessTime},
			function(data)
			{
				isLoading = false;
				updateProccess(data);
			}
		, 'json').fail(function()
			{
				isLoading = false;
			}
		);
	}

	var getMessages = function()
	{
		if ( isLoggedOut )
		{
			return;
		}

		$.get(getAction, {'fast':'1', 'access':accessTime},
			function(data)
			{
				updateProccess(data);
				loadMessages();
			}
		, 'json').fail(function()
			{
				setTimeout(function(){getMessages();}, 5000);
			}
		);
	}

	var loadMessages = function()
	{
		if ( isLoggedOut )
		{
			return;
		}

		$.get(getAction, {'access':accessTime},
			function(data)
			{
				updateProccess(data);
				loadMessages();
			}
		, 'json').fail(function()
			{
				setTimeout(function(){loadMessages();}, 5000);
			}
		);
	}

	var updateProccess = function(data)
	{
		if ( Object.keys(data).length == 0 )
		{
			return;
		}

		validateResult(data);

		accessTime = data.access;
		var update = data.update;

		if ( lastUpdate == update )
		{
			return;
		}

		lastUpdate = update;

		writeRoomLimit(data);
		writeRoomName(data);
		writeMessages(data);
		writeUserList(data);
		writeBlockList(data);
		writeRoomCapacity(data);
		markHost(data);
		markWhisper(data);
	}

	var writeRoomName = function(data)
	{
		var roomName = trim(data.name);

		document.title = roomName + ' | ' + siteTitle;
		roomNameElement.text(roomName);

		if ( roomName != lastRoomName )
		{
			$.each(inviteElement.find('li > a'),
				function()
				{
					// lastRoomName = lastRoomName.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
					var string = encodeURL(t("I'm now chatting at room '{1}'!", lastRoomName)).replace(/\+/g, "\\+").replace(/\./g, "\\.");
					var pattern = new RegExp(string);
					var replaceText = encodeURL(t("I'm now chatting at room '{1}'!", roomName));
					var link = $(this).attr('href');
					// console.log(link.replace(pattern, replaceText));
					$(this).attr('href', link.replace(pattern, replaceText));
				}
			);

			lastRoomName = roomName;
		}
	}

	var encodeURL = function(string)
	{
		string = encodeURIComponent(string);
		string = string.replace(/~/g, "%7E");
		string = string.replace(/!/g, "%21");
		string = string.replace(/\*/g, "%2A");
		string = string.replace(/\(/g, "%28");
		string = string.replace(/\)/g, "%29");
		string = string.replace(/'/g, "%27");
		string = string.replace(/%20/g, "+");
		return string;
	}

	var writeRoomLimit = function(data)
	{
		var min = data.min;
		var max = data.max;
		var limit = data.limit;

		if ( roomLimitElement.attr('disabled') === undefined && max == 0 && min == 0 )
		{
			roomLimitElement.attr('disabled', 'disabled');
		}

		if ( roomLimitElement.attr('disabled') !== undefined && max > 0 && min > 0 )
		{
			roomLimitElement.removeAttr('disabled');
		}

		var optionLength = roomLimitElement.find('option').length;

		if ( optionLength != ( max - min - 1 ) )
		{
			roomLimitElement.html('');

			for ( i = min; i <= max; i++ )
			{
				roomLimitElement.append('<option value="' + i + '"' + (( limit == i )?' selected="selected"':'') + '>' + i + '</option>');
			}
		}
	}

	var writeBlockList = function(data)
	{
		if ( !data.hasOwnProperty('bans') || !data.bans )
		{
			data.bans = [];
		}

		var blockCheck = false;

		if ( data.bans.length > 0 )
		{
			blockCheck = true;

			$.each(data.bans,
				function()
				{
					var id = this.id;

					if ( blockListElement.find("option[value="+id+"]").length == 0 )
					{
						blockCheck = false;
						return false;
					}
				}
			);
		}

		if ( blockCheck == true )
		{
			return;
		}

		blockListElement.html('');

		$.each(data.bans,
			function()
			{
				var id = this.id;
				var ip = this.ip;

				if ( blockListElement.find("option[value="+id+"]").length == 0 )
				{
					blockListElement.append('<option value="'+id+'">'+ip+'</option>');
				}
			}
		);

		if ( blockListElement.find("option").length == 0 )
		{
			blockListElement.append('<option value="">'+t("N/A")+'</option>');
		}
	}

	var writeRoomCapacity = function(data)
	{
		var total = data.users.length;
		var capacity = data.limit;

		if ( isNaN(capacity) )
		{
			capacity = 0;
		}

		if ( parseInt(roomTotalElement.html()) != total )
		{
			roomTotalElement.html(total);
		}
		if ( parseInt(roomCapacityElement.html()) != capacity )
		{
			roomCapacityElement.html(capacity);
		}
	}

	var writeMessages = function(data)
	{
		$.each(data.talks, writeMessage);

		if ( !data.hasOwnProperty('whispers') || !data.whispers )
		{
			data.whispers = [];
		}

		$.each(data.whispers, writeMessage);
	}

	var writeMessage = function()
	{
		var id = this.id;

		if ( $("#"+id).length > 0 )
		{
			return;
		}

		var uid     = this.uid;
		var name    = this.name;
		var message = this.message;
		var icon    = this.icon;
		var time    = this.time;
		var code    = this.code;

		if ( this.hasOwnProperty('rid') ) var rid = this.rid;

		var options         = {};
		var isNotifyMessage = true;

		name    = escapeHTML(name);
		message = escapeHTML(message);

		if ( uid == 0 || uid == '' )
		{
			var content = '<div class="talk system" id="'+id+'">'+message+'</div>';
			talksElement.prepend(content);

			if ( isUseMention )
			{
				isNotifyMessage = false;
			}

			writeChatLog('SYSTEM', message);

			options = {
				title: "SYSTEM"
			};
		}
		else if ( uid != userId )
		{
			if ( rid == userId )
			{
				if ( !isUseWhisper && !isLock )
				{
					toggleWhisper();
				}

				if ( isLock )
				{
					isNotifyMessage = false;
				}

				var content = '<dl class="talk '+icon+'" id="'+id+'">';
				content += '<dt'+( code.length > 0 ? ' title="'+code+'"' : '' )+'>'+name+'</dt>';
				content += '<dd title="'+time+'"><div class="bubble">';
				content += '<p class="body">'+message+'</p>';
				content += '<span title="from" class="hide">'+uid+'</span>';
				content += '<span title="to" class="hide">'+rid+'</span>';
				content += '</div></dd></dl>';
				dropListElement.val(uid)
				murmursElement.prepend(content);
				toggleWhisperMessage();
				if ( !isUseWhisper && isLock )
				{
					$("li.whisper").removeClass("whisper_off");
					$("li.whisper").addClass("whisper_notify");

					$.each($("#murmurs_box .bubble .body:first").parent(), addTail);
					$.each($("#murmurs_box .bubble .body:first"), roundBalloon);
					$("#murmurs_box dl.talk:first dt").click(addUserNameToTextarea);
				}
				else
				{
					effectBalloon(true);
				}

				$.each($("#murmurs_box .bubble .body:first"), shadowBalloon);
				$("#murmurs_box .talk dt:first").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});

				writeChatLog('[P] ' + name, message);

				options = {
					iconUrl: duraUrl+"/css/whisper/"+icon+".png"
				};
			}
			else if ( rid == null )
			{
				var content = '<dl class="talk '+icon+'" id="'+id+'">';
				content += '<dt'+( code.length > 0 ? ' title="'+code+'"' : '' )+'>'+name+'</dt>';
				content += '<dd title="'+time+'"><div class="bubble">';
				content += '<p class="body">'+message+'</p>';
				content += '</div></dd></dl>';
				talksElement.prepend(content);
				effectBalloon(false);

				$.each($("#talks_box .bubble .body:first"), shadowBalloon);
				$("#talks_box .talk dt:first").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});

				writeChatLog(name, message);

				options = {
					iconUrl: duraUrl+"/css/icon/"+icon+".png"
				};
			}
			else
			{
				return;
			}

			if ( unescapeHTML(message).indexOf('@' + userName) < 0 && isUseMention )
			{
				isNotifyMessage = false;
			}
		}
		else
		{
			return;
		}

		if ( !isWindowActive && isUseNotification && isNotifyMessage )
		{
			notificationUID = GUID();

			var defaults = {
				title: unescapeHTML(name),
				body: unescapeHTML(message),
				tag: notificationUID,
				timeout: 10000,
				onclick: function()
				{
					window.focus();
				}
			};

			options = $.extend({}, defaults, options);

			$.notification(options);
		}

		weepMessages();
	}

	var writeUserList = function(data)
	{
		membersElement.find("li").remove();
		var userListSelect = userListElement.find("li.select").attr("name");
		userListElement.find("li").remove();
		var dropListSelect = dropListElement.val();
		dropListElement.find("option").remove();

		var total = data.users.length;
		membersElement.append('<li>['+total+']</li>');

		var host = data.host;

		$.each(data.users,
			function()
			{
				var name = this.name;
				var id   = this.id;
				var icon = this.icon;
				var code = this.code;
				var ip   = this.ip;
				var hostMark = "";

				name = escapeHTML(name);

				if ( host == id ) hostMark = " "+t("[Host]");

				membersElement.append('<li>'+name+hostMark+'</li>');

				var whisperCount = 0;
				$.each($('#murmurs .talk'), function()
					{
						if ( $(this).find("span[title=from]").text() == id || $(this).find("span[title=to]").text() == id )
						{
							whisperCount++;
						}
					}
				);
				if ( userId != id ) dropListElement.append('<option value="'+id+'">'+name+( code.length > 0 ? ' ('+code+')' : '' )+' ['+icon+']'+( ( whisperCount > 0 ) ? ' ('+whisperCount+')' : '')+'</option>');

				if ( host == id ) return;

				userListElement.append('<li title="'+ip+( code.length > 0 ? ' '+code : '' )+'">'+name+'</li>');
				userListElement.find("li:last").css({
					'background':'transparent url("'+duraUrl+'/css/icon/'+icon+'.png") center top no-repeat'
				}).attr('name', id).click(
					function()
					{
						if ( $(this).hasClass('select') )
						{
							userListElement.find("li").removeClass('select');
							settingPannelElement.find("input[name=handover], input[name=ban], input[name=kick]").attr('disabled', 'disabled');
						}
						else
						{
							userListElement.find("li").removeClass('select');
							$(this).addClass('select');
							settingPannelElement.find("input[name=handover], input[name=ban], input[name=kick]").removeAttr('disabled');
						}
					}
				);
			}
		);

		userListElement.find("li[name="+userListSelect+"]").addClass('select');

		userListElement.find("li").tipTip({maxWidth: "auto", edgeOffset: 2, defaultPosition: "top"});

		if ( userListElement.find("li.select").length == 0 )
		{
			settingPannelElement.find("input[name=handover], input[name=ban], input[name=kick]").attr('disabled', 'disabled');
		}

		if ( dropListElement.find("option[value="+dropListSelect+"]").length == 1 )
		{
			dropListElement.val(dropListSelect);
		}
		else
		{
			toggleWhisperMessage();
		}

		if ( total == 1 && isUseWhisper )
		{
			toggleWhisper();
		}

		separateMemberList();
	}

	var writeSelfMessage = function(message, isWhisper)
	{
		var name    = escapeHTML(userName);
		var message = escapeHTML(message);

		message = trim(message);

		var content = '<dl class="talk '+userIcon+'" id="'+userId+'">';
		content += '<dt'+( userCode.length > 0 ? ' title="'+userCode+'"' : '' )+'>'+name+'</dt>';
		content += '<dd><div class="bubble">';
		content += '<p class="body">'+message+'</p>';
		content += isWhisper ? '<span title="from" class="hide">'+userId+'</span>' : '';
		content += isWhisper ? '<span title="to" class="hide">'+dropListElement.val()+'</span>' : '';
		content += '</div></dd></dl>';

		if ( isWhisper )
		{
			murmursElement.prepend(content);
			$("#murmurs_box .talk#"+userId+" dt:first").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});

			writeChatLog('[P] ' + userName, message);
		}
		else
		{
			talksElement.prepend(content);
			$("#talks_box .talk#"+userId+" dt:first").tipTip({maxWidth: "auto", edgeOffset: 5, defaultPosition: "top"});

			writeChatLog(userName, message);
		}

		effectBalloon(isWhisper);
		weepMessages();
	}

	var validateResult = function(data)
	{
		var error = data.error;

		if ( error == 0 || isLoggedOut )
		{
			return;
		}
		else if ( error == 1 )
		{
			isLoggedOut = true;
			alert(t("Session time out."));
		}
		else if ( error == 2 )
		{
			isLoggedOut = true;
			alert(t("Room was deleted."));
		}
		else if ( error == 3 )
		{
			isLoggedOut = true;
			alert(t("Login error."));
		}

		location.href = duraUrl;
	}

	var effectBalloon = function(isWhisper)
	{
		var thisBubble = $(( isWhisper ? "#murmurs_box" : "#talks_box" ) + " .bubble .body:first");
		var thisBubbleParent = thisBubble.parent();
		var oldWidth  = ( 1 + thisBubble.width() ) + 'px';
		var oldHeight = thisBubble.height() + 'px';
		var newWidth  = ( 5 + thisBubble.width() ) + 'px';
		var newHeight = ( 5 + thisBubble.height() ) + 'px';

		ringSound("comment");

		$(( isWhisper ? "#murmurs_box" : "#talks_box" ) + " dl.talk:first dt").click(addUserNameToTextarea);

		if ( !isUseAnime )
		{
			$.each(thisBubbleParent, addTail);
			$.each(thisBubble, roundBalloon);
			return;
		}

		if ( !isIE() )
		{
			$.each(thisBubbleParent, addTail);

			thisBubbleParent.css({
				'opacity' : '0',
				'width': '0px',
				'height': '0px'
			});
			thisBubbleParent.animate({
				'opacity' : 1,
				'width': '22px',
				'height': '16px'
			}, 200, "easeInQuart");
		}

		thisBubbleParent.parents("dl.talk").find('dt').css({
			'opacity' : '0'
		}).animate({
			'opacity' : 1
		}, 200, "easeInQuart");

		thisBubble.css({
			'border-width' : '0px',
			'font-size' : '0px',
			'text-indent' : '-100000px',
			'opacity' : '0',
			'width': '0px',
			'height': '0px'
		});

		thisBubble.animate({
			'fontSize': "1em",
			'borderWidth': "4px",
			'width': newWidth,
			'height': newHeight,
			'opacity': 1,
			'textIndent': 0
		}, 200, "easeInQuart",
			function()
			{
				$.each(thisBubble, roundBalloon);

				if ( isIE() )
				{
					thisBubbleParent.animate({
						'width': thisBubbleParent.width() - 5 + "px"
					}, 100);
				}
				else
				{
					thisBubbleParent.css({"width": "auto", "height": "auto"});
				}

				thisBubble.animate({
					'width': oldWidth,
					'height': oldHeight
				}, 100);
			}
		);
	}

	var ringSound = function(mode)
	{
		if ( !isUseSound )
		{
			return;
		}

		try
		{
			if ( $("a#sound").length && mode == "comment" ) messageSound.play();
			if ( $("a#private").length && mode == "whisper" ) whisperSound.play();
		}
		catch(e)
		{
		}
	}

	var escapeHTML = function(ch)
	{
		ch = ch.replace(/&/g,"&amp;");
		ch = ch.replace(/"/g,"&quot;");
		ch = ch.replace(/'/g,"&#039;");
		ch = ch.replace(/</g,"&lt;");
		ch = ch.replace(/>/g,"&gt;");
		return ch;
	}

	var unescapeHTML = function(ch)
	{
		return (typeof ch === 'undefined') ? '' : $('<div/>').html(ch).text();
	}

	var GUID = function()
	{
		// GUID / UID rfc4122 version 4 compliant
		// https://stackoverflow.com/q/105034

		if ( typeof(window.crypto) != 'undefined' && typeof(window.crypto.getRandomValues) != 'undefined' )
		{
			var buf = new Uint16Array(8);
			window.crypto.getRandomValues(buf);
			var S4 = function(num)
			{
				var ret = num.toString(16);
				while ( ret.length < 4 )
				{
					ret = "0"+ret;
				}
				return ret;
			};
			return (S4(buf[0])+S4(buf[1])+"-"+S4(buf[2])+"-"+S4(buf[3])+"-"+S4(buf[4])+"-"+S4(buf[5])+S4(buf[6])+S4(buf[7]));
		}
		else
		{
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,
				function(c)
				{
					var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
					return v.toString(16);
				}
			);
		}
	}

	var keyListener = function(e)
	{
		var isWhisper = ( $(this).closest('#whisper').length ) ? true : false;

		if ( isWhisper )
		{
			whisperCountElement.html(messageMaxLength - whisperTextElement.val().length);
		}
		else
		{
			countElement.html(messageMaxLength - textareaElement.val().length);
		}

		var content = isWhisper ? whisperTextElement.val() : textareaElement.val();
		if ( content != content.replace(/[\r\n]+/g, "") )
		{
			if ( isWhisper )
			{
				whisperElement.submit();
			}
			else
			{
				formElement.submit();
			}
			return false;
		}
	}

	var logout = function()
	{
		isLoggedOut = true;

		$.post(postAction, {'logout':'logout'},
			function(result)
			{
				location.href = duraUrl;
			}
		);
	}

	var weepMessages = function()
	{
		while ( $("#talks_box .talk").length > messageLimit )
		{
			$("#talks_box .talk:last").remove();
		}

		while ( $("#murmurs_box .talk").not(":hidden").length > messageLimit )
		{
			$("#murmurs_box .talk:last").remove();
		}
	}

	var separateMemberList = function()
	{
		membersElement.find('li:first').append(' = ');
		membersElement.find('li:not(:last):not(:first)').each(
			function()
			{
				$(this).append(', ');
			}
		);
	}

	var addUserNameToTextarea = function()
	{
		var isWhisper = ( $(this).closest('#murmurs').length ) ? true : false;
		var name = $(this).text();
		var text = isWhisper ? whisperTextElement.val() : textareaElement.val();
		if ( isWhisper )
		{
			whisperTextElement.focus();
			if ( text.replace(/\s+$/, '').length > 0 )
			{
				whisperTextElement.val(text.replace(/\s+$/, '')+' @'+name);
			}
			else
			{
				whisperTextElement.val('@'+name+' ');
			}
			whisperCountElement.html(messageMaxLength - whisperTextElement.val().length);
		}
		else
		{
			textareaElement.focus();
			if ( text.replace(/\s+$/, '').length > 0 )
			{
				textareaElement.val(text.replace(/\s+$/, '')+' @'+name);
			}
			else
			{
				textareaElement.val('@'+name+' ');
			}
			countElement.html(messageMaxLength - textareaElement.val().length);
		}
	}

	var trim = function(string)
	{
		return string.replace(/^\s+|\s+$/g, '');
	}

	var roundBalloons = function()
	{
		$("#talks dl.talk dd div.bubble p.body").each(roundBalloon);
		$("#murmurs dl.talk dd div.bubble p.body").each(roundBalloon);
	}

	var roundBalloon = function()
	{
		// IE 7 only...
		if ( !isIE() || !window.XMLHttpRequest || document.querySelectorAll )
		{
			return;
		}

		var isWhisper = ( $(this).closest('#murmurs').length ) ? true : false;

		$(this).css({
			"max-width" : "100%"
		});

		if ( isWhisper && !isUseWhisper )
		{
			whisperBox.css({
				"visibility" : "hidden",
				"display" : "block"
			});
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

		if ( isWhisper && !isUseWhisper )
		{
			whisperBox.removeAttr('style');
		}
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
		var isWhisper = ( $(this).closest('#murmurs').length ) ? true : false;

		if ( rand == 1 )
		{
			tailTop = "-17px";
		}
		else if ( rand == 2 )
		{
			tailTop = "-34px";
		}

		top = top + 1;

		if ( isWhisper )
		{
			var tail = ( rand == 1 ) ? "bottom" : (( rand == 2 ) ? "middle" : "top");

			$(this).css({"margin":"-16px 0 0 0"}).find(".body").css({"margin": "16px 0 0 15px"}).addClass(tail);
		}
		else
		{
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
	}

	var shadowBalloons = function()
	{
		$("#talks dl.talk dd div.bubble p.body").each(shadowBalloon);
		$("#murmurs dl.talk dd div.bubble p.body").each(shadowBalloon);
	}

	var shadowBalloon = function()
	{
		// var name = escapeHTML(userName);

		if ( $(this).text().indexOf('@' + userName) >= 0 && isUseMention )
		{
			if ( !isIE() || !window.XMLHttpRequest || document.querySelectorAll )
			{
				$(this).addClass('mention');
			}
			else
			{
				$(this).parent().addClass('mention');
			}
		}
	}

	var showControlPanel = function()
	{
		if ( isIE() )
		{
			isUseSound = false;
			isUseAnime = false;
		}

		menuElement.find("li:hidden:not(.setting, .whisper, .lock, .notification)").show();
		var soundClass     = ( isUseSound ) ? "sound_on" : "sound_off" ;
		var memberClass    = ( isShowMember ) ? "member_on" : "member_off" ;
		var animationClass = ( isUseAnime ) ? "animation_on" : "animation_off" ;
		var whisperClass   = ( isUseWhisper ) ? "whisper_on" : "whisper_off" ;
		var lockClass      = ( isLock ) ? "lock_on" : "lock_off" ;
		var mentionClass   = ( isUseMention ) ? "mention_on" : "mention_off" ;
		var inviteClass    = ( isShowInvite ) ? "invite_on" : "invite_off" ;
		var notificationClass = ( isUseNotification ) ? "notification_on" : "notification_off" ;
		menuElement.find("li.sound").addClass(soundClass);
		menuElement.find("li.member").addClass(memberClass);
		menuElement.find("li.animation").addClass(animationClass);
		menuElement.find("li.whisper").addClass(whisperClass);
		menuElement.find("li.lock").addClass(lockClass);
		menuElement.find("li.mention").addClass(mentionClass);
		menuElement.find("li.invite").addClass(inviteClass);
		menuElement.find("li.notification").addClass(notificationClass);

		if ( $.notification.permissionLevel() !== "unsupported" )
		{
			menuElement.find("li.notification").show();
		}
	}

	var toggleRefresh = function()
	{
		window.location.reload();
	}

	var toggleSound = function()
	{
		if ( isUseSound )
		{
			$(this).removeClass("sound_on");
			$(this).addClass("sound_off");
			isUseSound = false;
		}
		else
		{
			$(this).removeClass("sound_off");
			$(this).addClass("sound_on");
			isUseSound = true;
		}
	}

	var toggleMember = function()
	{
		if ( isShowMember )
		{
			$(this).removeClass("member_on");
			$(this).addClass("member_off");
			membersElement.slideUp("slow");
			isShowMember = false;
		}
		else
		{
			$(this).removeClass("member_off");
			$(this).addClass("member_on");
			membersElement.slideDown("slow");
			isShowMember = true;
		}
	}

	var toggleInvite = function()
	{
		if ( isShowInvite )
		{
			$(this).removeClass("invite_on");
			$(this).addClass("invite_off");
			inviteElement.slideUp("slow");
			isShowInvite = false;
		}
		else
		{
			$(this).removeClass("invite_off");
			$(this).addClass("invite_on");
			inviteElement.slideDown("slow");
			isShowInvite = true;
		}
	}

	var toggleNotification = function()
	{
		if ( isUseNotification )
		{
			$(this).removeClass("notification_on");
			$(this).addClass("notification_off");
			isUseNotification = false;
		}
		else
		{
			var self = $(this);

			$.notification.requestPermission(
				function()
				{
					var permission = $.notification.permissionLevel();

					if ( permission === "granted" )
					{
						self.removeClass("notification_off");
						self.addClass("notification_on");
						isUseNotification = true;
					}
					else
					{
						alert(t("Notifications are '{1}'.", permission));
					}
				}
			);
		}
	}

	var toggleChatLog = function()
	{
		var windowContainer = window.open('', '_blank');

		windowContainer.document.write('<textarea style="width:100%; height:100%;">' + chatLog + '</textarea>');
	}

	var writeChatLog = function(name, message)
	{
		var now = new Date();

		message = message.replace(/[\r\n\t]+/g, " ");
		chatLog += '[' + now.toUTCString() + '] ' + name + ': ' + message + "\n";
	}

	var populateChatLog = function()
	{
		$($("#talks .talk").get().reverse()).each(
			function()
			{
				var name    = '';
				var message = '';

				if ( $(this).hasClass('system') )
				{
					name    = "SYSTEM";
					message = trim($(this).text());
				}
				else
				{
					name    = trim($(this).find("dt").text());
					message = trim($(this).find("p.body").text());
				}

				name    = escapeHTML(name);
				message = escapeHTML(message);

				writeChatLog(name, message);
			}
		);
		$($("#murmurs dl.talk").get().reverse()).each(
			function()
			{
				var name    = trim($(this).find("dt").text());
				var message = trim($(this).find("p.body").text());

				name    = escapeHTML(name);
				message = escapeHTML(message);

				writeChatLog("[P] " + name, message);
			}
		);
	}

	var toggleAnimation = function()
	{
		if ( isUseAnime )
		{
			$(this).removeClass("animation_on");
			$(this).addClass("animation_off");
			isUseAnime = false;
		}
		else
		{
			$(this).removeClass("animation_off");
			$(this).addClass("animation_on");
			isUseAnime = true;
		}
	}

	var toggleMention = function()
	{
		if ( isUseMention )
		{
			$(this).removeClass("mention_on");
			$(this).addClass("mention_off");
			isUseMention = false;
			if ( !isIE() || !window.XMLHttpRequest || document.querySelectorAll )
			{
				$("#talks dl.talk dd div.bubble p.mention").removeClass('mention');
				$("#murmurs dl.talk dd div.bubble p.mention").removeClass('mention');
			}
			else
			{
				$("#talks dl.talk dd div.mention").removeClass('mention');
				$("#murmurs dl.talk dd div.mention").removeClass('mention');
			}
		}
		else
		{
			$(this).removeClass("mention_off");
			$(this).addClass("mention_on");
			isUseMention = true;
			shadowBalloons();
		}
	}

	var toggleWhisper = function()
	{
		var whisperWidth = whisperBox.width();
		var whisperHeight = whisperBox.height();

		if ( isUseWhisper )
		{
			$("li.whisper").removeClass("whisper_on");
			$("li.whisper").addClass("whisper_off");
			// $("body").removeClass("fullsolute");
			ringSound("whisper");
			isUseWhisper = false;

			if ( isUseAnime )
			{
				whisperBox.animate({
					'opacity' : '0'
				}, 100, "linear",
					function()
					{
						whisperBox.hide();
					}
				);
			}
			else
			{
				whisperBox.hide();
			}
		}
		else
		{
			if ( $("li.whisper").hasClass("whisper_notify") )
			{
				$("li.whisper").removeClass("whisper_notify");
			}
			else
			{
				$("li.whisper").removeClass("whisper_off");
			}
			$("li.whisper").addClass("whisper_on");
			// $("body").addClass("fullsolute");
			ringSound("whisper");
			isUseWhisper = true;

			// IE 6 doesn't support fixed!
			if ( !window.XMLHttpRequest )
			{
				whisperBox.css('position', 'absolute');
			}

			whisperBox.css({
				'left' : (($(window).width() / 2) - (whisperWidth / 2)) + 'px',
				'top' : '140px',
				'opacity' : '1'
			});
			whisperBox.show();

			if ( isUseAnime )
			{
				whisperBox.css({
					'opacity' : '0',
					'width' : '0px',
					'height' : '0px'
				});
				whisperBox.animate({
					'opacity' : '1',
					'width' : (whisperWidth + 5) + 'px',
					'height' : (whisperHeight + 5) + 'px'
				}, 150, "easeInQuart",
					function()
					{
						whisperBox.animate({
							'width' : whisperWidth + 'px',
							'height' : whisperHeight + 'px'
						}, 100);
					}
				);
			}
		}
	}

	var toggleWhisperMessage = function()
	{
		var recipient = dropListElement.children(':selected').val();
		var murmurMessages = murmursElement.find("dl.talk");
		murmurMessages.each(
			function() {
				var from = $(this).find("span[title=from]");
				var to = $(this).find("span[title=to]");
				if ( (recipient == from.text() && to.text() == userId) || (recipient == to.text() && from.text() == userId) )
				{
					$(this).show();
				}
				else
				{
					$(this).hide();
				}
			}
		);
	}

	var toggleLock = function()
	{
		if ( isLock )
		{
			$(this).removeClass("lock_on");
			$(this).addClass("lock_off");
			isLock = false;
		}
		else
		{
			$(this).removeClass("lock_off");
			$(this).addClass("lock_on");
			isLock = true;
		}
	}

	var toggleSettingPannel = function()
	{
		settingPannelElement.find("input[name=handover], input[name=ban], input[name=kick]").attr('disabled', 'disabled');
		userListElement.find("li").removeClass('select');
		// buttonElement.slideToggle();
		// textareaElement.slideToggle();
		// userProfileElement.slideToggle();
		countElement.fadeToggle();
		formElement.find("div.message_area").slideToggle();
		settingPannelElement.slideToggle();
	}

	var markHost = function(data)
	{
		if ( data.host == userId )
		{
			menuElement.find("li.setting").show();
		}
		else
		{
			menuElement.find("li.setting").hide();
		}
	}

	var markWhisper = function(data)
	{
		if ( data.users.length > 1 )
		{
			menuElement.find("li.whisper, li.lock").show();
		}
		else
		{
			menuElement.find("li.whisper, li.lock").hide();
		}
	}

	var changeRoomDetail = function()
	{
		var roomName = settingPannelElement.find("input[name=room_name]").val();
		var roomLanguage = settingPannelElement.find("select[name=room_language]").val();

		$.post(postAction, {'room_name':roomName, 'room_language':roomLanguage},
			function(result)
			{
				alert(result);
				toggleSettingPannel();
			}
		).fail(function()
			{
				alert(t("Server error."));
				toggleSettingPannel();
			}
		);
	}

	var changeRoomLimit = function()
	{
		var roomLimit = settingPannelElement.find("select[name=room_limit]").val();

		$.post(postAction, {'room_limit':roomLimit},
			function(result)
			{
				alert(result);
				toggleSettingPannel();
			}
		).fail(function()
			{
				alert(t("Server error."));
				toggleSettingPannel();
			}
		);
	}

	var changeRoomPassword = function()
	{
		var roomPassword = settingPannelElement.find("input[name=room_password]").val();

		$.post(postAction, {'room_password':roomPassword},
			function(result)
			{
				alert(result);
				toggleSettingPannel();
			}
		).fail(function()
			{
				alert(t("Server error."));
				toggleSettingPannel();
			}
		);
	}

	var removeBlock = function()
	{
		var blockIP = settingPannelElement.find("select[name=blocked_ip]").val();

		if ( blockIP == null )
		{
			blockIP = '';
		}

		$.post(postAction, {'blocked_ip':blockIP},
			function(result)
			{
				alert(result);
				toggleSettingPannel();
			}
		).fail(function()
			{
				alert(t("Server error."));
				toggleSettingPannel();
			}
		);
	}

	var handoverHost = function()
	{
		var id = userListElement.find("li.select").attr("name");

		if ( id === undefined )
		{
			id = '';
		}

		if ( confirm(t("Are you sure to handover host rights?")) )
		{
			$.post(postAction, {'new_host':id},
				function(result)
				{
					alert(result);
					toggleSettingPannel();
				}
			).fail(function()
				{
					alert(t("Server error."));
					toggleSettingPannel();
				}
			);
		}
	}

	var banUser = function()
	{
		var id = userListElement.find("li.select").attr("name");

		if ( id === undefined )
		{
			id = '';
		}

		if ( confirm(t("Are you sure to ban this user?")) )
		{
			$.post(postAction, {'ban_user':id},
				function(result)
				{
					alert(result);
					toggleSettingPannel();
				}
			).fail(function()
				{
					alert(t("Server error."));
					toggleSettingPannel();
				}
			);
		}
	}

	var kickUser = function()
	{
		var id = userListElement.find("li.select").attr("name");

		if ( id === undefined )
		{
			id = '';
		}

		if ( confirm(t("Are you sure to kick this user?")) )
		{
			$.post(postAction, {'kick_user':id},
				function(result)
				{
					alert(result);
					toggleSettingPannel();
				}
			).fail(function()
				{
					alert(t("Server error."));
					toggleSettingPannel();
				}
			);
		}
	}

	var isInFrame = function()
	{
		var windowSelf   = window.location;
		var windowParent = window.parent.location;
		var windowTop    = window.top.location;
		return windowSelf !== windowParent || windowSelf !== windowTop || windowParent !== windowTop;
	}

	var isIE = function()
	{
		var isMSIE = /*@cc_on!@*/false;
		return isMSIE;
	}

	var dump = function($val)
	{
		talksElement.prepend($val);
	}

	construct();
});
