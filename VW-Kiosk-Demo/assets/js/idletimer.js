/**
 * 
 * Idle Timer Section:
 * 15 minutes - notify of idle via modal
 * 16 minutes - reset kiosk via reload
 * Total 16 minutes idle before reset
 * 
 */

// Set global timeout variables.
var idleTime = 0;
var idleInterval = null;

// Start the overall idle timer
function startIdleTimer() {
    // Increment the idle time counter every minute using setInterval
    idleInterval = setInterval(timerIncrement, 60000); // 60000ms = 1 minute
}

// Increment the idle timer by 1 on every 1 minute interval
function timerIncrement() {
    idleTime = idleTime + 1;
    debug.log('Timer Reached: ' + idleTime);
    if (idleTime > 1) {
        var $video = $('video').first();
        if ($video.attr('src') != 'video/H_DS_VOLKSWAGEN_INTERACTIVE SCREEN_2019_DB.mp4') {
            $video.attr('src', 'video/H_DS_VOLKSWAGEN_INTERACTIVE SCREEN_2019_DB.mp4');
        }
        if ($('#videos').is(':visible')) {
            $('.sidenav-toggle').hide();
            if (!$video.paused) {
                $video.play();
                $video.muted = true;
            }
        }
    }
    if (idleTime > 14) {
        // At 15 minutes...
        // Show modal and ask to continue
        $("#timeout-modal").modal('show');
    }
    if (idleTime > 15) {
        // At 16 minutes...
        // Reload page if modal click does not occur
        window.location.reload();
    }
}

// Zero the idle timer on various global events.
$(document).on('click touchstart keypress mousemove', function () {
    // Zero idle timer on a click, touch, key press, or mouse movement
    idleTime = 0;
    $('.sidenav-toggle').show();
    checkTimeoutModal();

    // If timer has never been initiated, start on first interaction with kiosk
    if (!idleInterval) {
        startIdleTimer();
    }
});

$(document).on('load', '#page-loader', function () {
    // Zero idle timer on new page load in iFrame
    idleTime = 0;
    checkTimeoutModal();
});

function checkTimeoutModal() {
    // Check if modal is displayed and hide on timer reset
    if (isTimeoutModalShown($('#timeout-modal'))) {
        $('#timeout-modal').modal('hide');
    }
}

function isTimeoutModalShown(modal) {
    // Check if idle modal is currently displayed
    var modalIsShown = (modal.data('bs.modal') || {})._isShown;
    return !(typeof modalIsShown === 'undefined' || !modalIsShown);
}
// End Reset Idle Events and Functions