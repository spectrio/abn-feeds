<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<!-- FAVICON -->
	<link rel="icon" href="http://control.abnetwork.com/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="http://control.abnetwork.com/favicon.ico" type="image/x-icon">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />

	<title>Live TV - ABN</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta name="viewport" content="width=device-width" />
	
	<!-- Bootstrap core CSS     -->
	<link href="/static/assets/css/bootstrap.min.css" rel="stylesheet" />
	
	<!-- Rotating Card CSS -->
	<link href="/static/assets/css/rotating-card.css" rel="stylesheet" />
		
	<!-- Animation library for notifications   -->
	<link href="/static/assets/css/animate.min.css" rel="stylesheet"/>
	
	<!-- jQuery TimeEntry -->
	<link href="/static/assets/js/time-entry/jquery.timeentry.css" rel="stylesheet">
        
	<!--     Fonts and icons     -->
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet" />  
	<link href="/static/assets/css/pe-icon-7-stroke.css" rel="stylesheet" />

	<!-- CSS Overrides -->
	<link href="/static/assets/css/kiosk-overrides.css" rel="stylesheet" />
	
</head>
<body style="width: 100%; height: 100%;">
<div class="container ui-background">
	<div class="row">
		<div class="col-md-12">
			<div class="title-container">
				<img class="img-responsive title-image" src="/static/assets/img/TITLE.png" alt="Start Live TV for Duration" />
			</div>
			<div class="row notification-container">
				<div class="pttv-notification">Feedback on request results here</div>
			</div>
			<div class="row">
				<div class="button-container">
					<div class="col-md-1">
					</div>
					<div class="col-md-2">
						<input type="image" class="img-responsive duration-button" src="/static/assets/img/1_HOUR.png" alt="1 Hour" value="1" />
					</div>
					<div class="col-md-2">
						<input type="image" class="img-responsive duration-button" src="/static/assets/img/2_HOURS.png" alt="2 Hours" value="2" />
					</div>
					<div class="col-md-2">
						<input type="image" class="img-responsive duration-button" src="/static/assets/img/4_HOURS.png" alt="4 Hours" value="4" />
					</div>
					<div class="col-md-2">
						<input type="image" class="img-responsive duration-button" src="/static/assets/img/8_HOURS.png" alt="8 Hours" value="8" />
					</div>
					<div class="col-md-2">
						<input type="image" class="img-responsive duration-button" src="/static/assets/img/ALL_DAY.png" alt="All Day" value="16" />
					</div>
					<div class="col-md-1">
					</div>
				</div>
			</div>
			<div class="row">
				<div class="stop-container">
					<div class="col-md-3">
						<input type="hidden" class="time-range" id="start-live-tv-time" name="start-live-tv-time" val="" />
					</div>
					<div class="col-md-6">
						<input type="image" class="img-responsive stop-button" src="/static/assets/img/STOP.png" alt="Stop Live TV" value="Stop" />
					</div>
					<div class="col-md-3">
						<input type="hidden" class="time-range" id="end-live-tv-time" name="end-live-tv-time" val="" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!--   Core JS Files   -->
<script src="/static/assets/js/jquery-1.10.2.js" type="text/javascript"></script>
<script src="/static/assets/js/bootstrap.min.js" type="text/javascript"></script>

<!--  Checkbox, Radio & Switch Plugins -->
<script src="/static/assets/js/bootstrap-checkbox-radio-switch.js"></script>

<!--  Charts Plugin -->
<script src="/static/assets/js/chartist.min.js"></script>

<!--  Notifications Plugin    -->
<script src="/static/assets/js/bootstrap-notify.js"></script>

<!-- Light Bootstrap Table Core javascript and methods for Demo purpose -->
<script src="/static/assets/js/light-bootstrap-dashboard.js"></script>

<!-- jQuery TimeEntry -->
<script type="text/javascript" src="/static/assets/js/time-entry/jquery.plugin.min.js"></script> 
<script type="text/javascript" src="/static/assets/js/time-entry/jquery.timeentry.min.js"></script>


<script type="text/javascript">
$(document).ready(function(){

	$('.time-range').each(function(li) {
		$(this).timeEntry({
			beforeShow: customRange,
			spinnerImage: ''
		});
	});

	$('[rel="tooltip"]').tooltip();


	$("body").on("click", ".stop-button", function() {
		var sentEventDate = timeStamp();
		var href = document.URL;
		var channel =  href.substr(href.lastIndexOf('/') + 1);
		
		$(".pttv-notification").html('<i class="fa fa-ban"></i> Stop Live TV sent at ' + sentEventDate);
		$(".notification-container").fadeIn('fast').delay(6000).fadeOut('slow');

		$.ajax({
            type: "POST",
            url: "/kiosk/livetv/stop",
            data: { location_id: channel },
            cache: false,
            success: function(data, status) {
				//$("#livetv-result").fadeOut();
    			//$("#livetv-result").html('<font color="green"><i class="fa fa-picture-o"></i> Live TV Sent Stop</font>');
    			//$("#livetv-result").fadeIn().delay(6000).fadeOut();
            },
            error: function() {
				//$("#livetv-result").fadeOut();
    			//$("#livetv-result").html('<font color="red"><i class="fa fa-picture-o"></i> Live TV Sending Stop Error</font>');
    			//$("#livetv-result").fadeIn().delay(6000).fadeOut();
            }
	    });
	});

	$("body").on("click", ".duration-button", function() {
		$(".notification-container").clearQueue().stop();
		if ($(this).val() == "Stop") {
			return;
		}
		var sentEventDate = timeStamp();
		var href = document.URL;
		var channel =  href.substr(href.lastIndexOf('/') + 1);
		
		$(".pttv-notification").html('<i class="fa fa-bolt"></i> Start Live TV sent at ' + sentEventDate);
		$(".notification-container").fadeIn('fast').delay(6000).fadeOut('slow');
		
		var startAdd = null;
		var endAdd = null;
		var today = new Date();
		var hoursAdded = $(this).val().toString();
		
		if (startAdd === null) {
			$("#start-live-tv-time").timeEntry("setTime", formatAMPM(today));
			var startAdd = $("#start-live-tv-time").timeEntry("getTime");
		}

		if (endAdd === null) {
			$("#end-live-tv-time").timeEntry("setTime", formatAMPM(startAdd));
			$("#end-live-tv-time").timeEntry("setTime", "+" + hoursAdded + "h +0m");
			var endAdd = $("#end-live-tv-time").timeEntry("getTime");
		}

		var checkMidnight = endAdd.getHours();

		if (startAdd > endAdd || checkMidnight == 0) {
			$("#end-live-tv-time").timeEntry("setTime", "11:59PM");
		}
		
		var utcDate = (new Date()).toUTCString();
		var nowDate = new Date(utcDate);
		var curr_date = ("0" + nowDate.getDate()).slice(-2);
		var curr_month = ("0" + nowDate.getMonth()).slice(-2);
		var curr_year = nowDate.getFullYear();
		var startDate = curr_year + "-" + curr_month + "-" + curr_date;
		
		var startEntry = $("#start-live-tv-time").timeEntry("getTime");
		var endEntry = $("#end-live-tv-time").timeEntry("getTime");

		var workStart = startEntry.toString();
		var workEnd = endEntry.toString();

		var workStart = workStart.split(" ");
		var workEnd = workEnd.split(" ");
		
		var startTime = workStart[4];
		var endTime = workEnd[4];
		
		var startOffset = workStart[5].replace("GMT","");
		var endOffset = workEnd[5].replace("GMT","");

		var startOffset = startOffset.splice(3, 0, ":");
		var endOffset = endOffset.splice(3, 0, ":");

		var startTV = startDate + "T" + startTime + startOffset;
		var endTV = startDate + "T" + endTime + endOffset;
		
		var display = startTV.toString() + " - " + endTV.toString();

		var hourOffset = hoursAdded;

		var customerSplit = new Date().toString().split(" ");
		var customerOffset = customerSplit[customerSplit.length - 2] + " " + customerSplit[customerSplit.length - 1];

	    $.ajax({
            type: "POST",
            url: "/kiosk/livetv/timed",
            data: { location_id: channel, start_tv: startTV, end_tv: endTV, hour_offset: hourOffset, customer_offset: customerOffset },
            cache: false,
            success: function(data, status) {
            	//$(".notification-container").fadeOut();
            	//$(".pttv-notification").html('<font color="green"><i class="fa fa-bolt"></i> Live TV Sent Timed</font>');
            	//$(".notification-container").fadeIn().delay(6000).fadeOut();
            },
            error: function() {
            	//$(".notification-container").fadeOut();
            	//$(".pttv-notification").html('<font color="red"><i class="fa fa-bolt"></i> ERROR: Live TV Sending Problem</font>');
    			//$(".notification-container").fadeIn().delay(6000).fadeOut();
            }
	    });
	});
});

String.prototype.splice = function( idx, rem, s ) {
    return (this.slice(0,idx) + s + this.slice(idx + Math.abs(rem)));
};

function timeStamp() {
	var now = new Date();
	var date = [ now.getMonth() + 1, now.getDate(), now.getFullYear() ];
	var time = [ now.getHours(), now.getMinutes(), now.getSeconds() ];
	var suffix = ( time[0] < 12 ) ? "AM" : "PM";
	time[0] = ( time[0] < 12 ) ? time[0] : time[0] - 12;
	time[0] = time[0] || 12;
	for ( var i = 1; i < 3; i++ ) {
		if ( time[i] < 10 ) {
			time[i] = "0" + time[i];
		}
	}
	return date.join("/") + " " + time.join(":") + " " + suffix;
}
function formatAMPM(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours < 12 ? 'AM' : 'PM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0' + minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	return strTime;
}
function customRange(input) { 
	return {minTime: (input.id === 'end-live-tv-time' ? 
			$('#start-live-tv-time').timeEntry('getTime') : null),  
				maxTime: (input.id === 'start-live-tv-time' ? 
					$('#end-live-tv-time').timeEntry('getTime') : null)}; 
}

</script>
</body>
</html>