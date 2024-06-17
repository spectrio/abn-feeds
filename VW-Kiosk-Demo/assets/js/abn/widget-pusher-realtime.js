function startPusherComms(locationID) {
	// Enable pusher logging - don't include this in production
	if (isDebug) {
		Pusher.logToConsole = true;
	}

	var pusher = new Pusher('4490119c0eb7d0d7cafa', {
		cluster: 'mt1',
		forceTLS: true
	});

	var channel = pusher.subscribe(locationID);
	channel.bind('dealertv', function (data) {
		debug.log(data);
		if (data.payload) {
			data = data.payload;
		}
		debug.log(data);
		var eventsArray = [];
		if (data.events) {
			for (var z = 0; z < data.events.length; z++) {
				eventsArray.push(data.events[z]);
			}
		} else {
			eventsArray.push(data);
		}
		debug.log('eventsArray: ' + JSON.stringify(eventsArray));
		for (var i = 0; i < eventsArray.length; i++) {
			var data = eventsArray[i];
			debug.log('loopedEvents: ' + JSON.stringify(data));
			if (data.event == 'notification') {
				popToast(data.message);
			} else if (data.event == 'rss') {
				resetRSSExpiration();
				loadWidgetDataRSS();
			} else if (data.event == 'pttv') {
				if (data.method == 'start') {
					debug.log('WidgetPusherRealtime: Start PTTV Triggered');
					sendPTTVStartCommands();
					//startPTTV();
				}
				if (data.method == 'stop') {
					debug.log('WidgetPusherRealtime: Stop PTTV Triggered');
					sendPTTVDealerTVCommands();
					//stopPTTV();
				}
				if (data.method == 'setup') {
					debug.log('WidgetPusherRealtime: Setup PTTV Triggered');
					if (data.deviceip) {
						wsIP = data.deviceip;
						closeSerialDevice();
					}
					sendPTTVSetupCommands();
					//setupPTTV();
				}
			} else if (data.event == 'branding') {
				debug.log('WidgetPusherRealtime: Branding Change Triggered To: ' + data.branding);
				resetDTVExpiration();
				setOEMCSS(data.branding);
			} else if (data.event == 'refresh') {
				debug.log('WidgetPusherRealtime: Widget Refresh Triggered');
				location.reload();
			}
		}
	});

	function resetDTVExpiration() {
		var baseDate = new Date();
		var interval = -72; // Hours
		var startDate = new Date(Date.parse(baseDate));
		debug.log('WidgetPusherRealtime: Set New DTV Data Expiration - baseDate: ' + startDate);
		var expirationDate = startDate;
		expirationDate.setHours(expirationDate.getHours() + interval);
		debug.log('WidgetPusherRealtime: Set New DTV Data Expiration - expireDate: ' + expirationDate);
		storeOfflineData('widgetDTVExpiration', expirationDate);
	}

	function popToast(message) {
		$('#toast-bread').addClass("toast-ready");
		$('#toast-text').html(message);
		shrinkToastTextToFit();
		/*//playSound('coin');
		setTimeout(function () {
			//playSound('coin');
		}, 2500);
		setTimeout(function () {
			//playSound('coin');
		}, 5000);*/
		setTimeout(function () {
			$('#toast-bread').removeClass("toast-ready");
		}, 8500);
	}

	function shrinkToastTextToFit() {
		var textSpan = document.getElementById("toast-text");
		var textDiv = document.getElementById("toast-bread");
		textSpan.style.fontSize = 64;
		while (textSpan.offsetHeight > textDiv.offsetHeight) {
			textSpan.style.fontSize = parseInt(textSpan.style.fontSize) - 1;
		}
	}

	function playSound(name, volume) {
		var x = document.getElementById("audio");
		x.volume = 1;
		x.play();
	}

	function setupPTTV() {
		sendPTTVSetupCommands();
	}

	function startPTTV() {
		sendPTTVStartCommands();
	}

	function stopPTTV() {
		sendPTTVDealerTVCommands();
	}
}