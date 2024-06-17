window.open = function (url, name, features, replace) {
    document.getElementById('#page-loader').src = url;
}

$(document).on('onload', 'iframe', function () {
    if ($(this).attr('id') == 'page-loader') {
        var iframe = $('#page-loader').contents();
        console.debug(iframe);
        iframe.find("input").keyboard();
    }
    if ($(this).hasClass('twitter-timeline')) {
        var ifr = $('.twitter-timeline').first();
        console.log($(this).sandbox);
        ifr.sandbox('allow-scripts allow-same-origin');
    }
});

$(function () {
    $(document).click(function (event) {
        if ($('.sidenav').hasClass('open')) {
            closeSideMenu();
        }
    });

    $('.sidenav-toggle, .sidenav-close').on('click', function (e) {
        e.stopPropagation();
        if ($(this).hasClass('sidenav-toggle')) {
            $('.sidenav').toggleClass('open');
            $(this).toggleClass('open');
            $("i", this).toggleClass("fa-chevron-down fa-chevron-up");
            //debug.log('Clicked sidenav-toggle');
            //openSideMenu();
        } else {
            debug.log('Clicked sidenav-close');
            closeSideMenu();
        }
    });

    $('#videos').on('click', function (e) {
        e.stopPropagation();
        $('.sidenav-toggle').trigger('click');
        var $video = $('video').first();
        if ($video.attr('src') != 'video/H_DS_VOLKSWAGEN_INTERACTIVE SCREEN-LOGO_ONLY-2019_DB.mp4') {
            $video.attr('src', 'video/H_DS_VOLKSWAGEN_INTERACTIVE SCREEN-LOGO_ONLY-2019_DB.mp4');
            if ($video.length) {
                $video.play();
                $video.muted = true;
            }
        }
    });

    $('.sidenav a').on('click', function (e) {
        e.stopPropagation();
        hideLinkLandings();
        var $clicked = $(this).closest('li');
        var $links = $('.sidenav-menu').find('li');
        $links.removeClass('activated-link');
        $clicked.addClass('activated-link');
        var $video = $('video:visible');
        $('#videos').hide();
        if ($video.length) {
            $video[0].pause();
        }
        if ($clicked.hasClass('charging-map-link')) {
            $('#charging-map').show();
            $('.charging-map-holder').show();
            setupMap();
        } else if ($clicked.hasClass('ev-incentives-link')) {
            $('#ev-incentives').show();
            $('.ev-incentives-holder').show();
        } else if ($clicked.hasClass('ev-calculator-link')) {
            $('#ev-calculator').show();
            $('.ev-calculator-holder').show();
        } else if ($clicked.hasClass('vw-accessories-link')) {
            $('#page-loader').attr('src', 'https://vw.oeaccessories.com/vwaccessorybuilder/index.aspx');
            $('.iframe-holder').show();
        } else if ($clicked.hasClass('social-link')) {
            if ($clicked.hasClass('instagram-link')) {
                $('.social-frame-header').text('Volkswagen USA Instagram');
                loadInstagramFeed();
                $('#vw-instagram-feed').show();
            } else if ($clicked.hasClass('twitter-link')) {
                $('.social-frame-header').text('Volkswagen USA Twitter');
                $('#vw-twitter-feed').html('<a class="twitter-timeline" data-width="1200" data-height="1005" \
                    href="https://twitter.com/VW?ref_src=twsrc%5Etfw"><b>Tweets by Volkswagen USA (@VW)</b></a> \
                    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>');
                $('#vw-twitter-feed').show();
            }
            $('.social-holder').show();
        } else if ($clicked.hasClass('vw-website-link')) {
            $('#page-loader').attr('src', 'https://www.vw.com/content/vwcom/en/electric-concepts/');
            $('.iframe-holder').show();
        } else if ($clicked.hasClass('go-home-link')) {
            returnToHome();
        } else if ($clicked.hasClass('reload-kiosk-link')) {
            window.location.reload();
        } else {
            $('.iframe-holder').show();
        }
        closeSideMenu();
    });

    function hideLinkLandings() {
        $('.iframe-holder').hide();
        $('#page-loader').attr('src', 'about:blank');
        $('.charging-map-holder').hide();
        $('#charging-map').hide();
        $('.ev-incentives-holder').hide();
        $('#ev-incentives').hide();
        $('.ev-calculator-holder').hide();
        $('#ev-calculator').hide();
        $('.social-holder').hide();
        $('#vw-instagram-feed').empty();
        $('#vw-instagram-feed').hide();
        $('#vw-twitter-feed').empty();
        $('#vw-twitter-feed').hide();
    }

    function toggleSideMenu() {
        $("i", this).toggleClass("fa-chevron-down fa-chevron-up");
    }

    function openSideMenu() {
        $('.sidenav').addClass('open');
        $('.sidenav-toggle').addClass('open');
        $('i', '.sidenav-toggle').toggleClass('fa-chevron-down fa-chevron-up');
    }

    function closeSideMenu() {
        $('.sidenav').removeClass('open');
        $('.sidenav-toggle').removeClass('open');
        $('i', '.sidenav-toggle').toggleClass('fa-chevron-down fa-chevron-up');
    }

    $('header').on('click', function () {
        var $links = $('.sidenav-menu').find('li');
        $links.removeClass('activated-link');
        hideLinkLandings();
        $('#videos').show();
        var $video = $('video:visible');
        if ($video.length) {
            $video[0].play();
            $video[0].muted = true;
        }
    });

    // Increment the idle time counter every minute using setInterval
    /*var colorInterval = setInterval(function () {
        $('.base-colors').toggleClass('flip-blue-colors');
        $('.reverse-base-colors').toggleClass('reverse-flip-blue-colors');
    }, 60000); // 60000ms = 60 seconds*/

    var connectionInterval = setInterval(function () {
        if (checkNetConnection()) {
            $('.sidenav').show();
            $('.sidenav-toggle').show();
        } else {
            $('.sidenav').hide();
            $('.sidenav-toggle').hide();
            hideLinkLandings();
            $('#videos').show();
            var $video = $('video:visible');
            if ($video.length) {
                $video[0].play();
                $video[0].muted = true;
            }
        }
    }, 30000);

    function checkNetConnection() {
        var xhr = new XMLHttpRequest();
        var file = "https://abncdn.s3.amazonaws.com/DEALER_TV_CONTENT/DTV_ASSETS/WEATHER_ICONS/SUNNY_icon.png";
        var r = Math.round(Math.random() * 10000);
        xhr.open('HEAD', file + "?subins=" + r, false);
        try {
            xhr.send();
            if (xhr.status >= 200 && xhr.status < 304) {
                return true;
            } else {
                return false;
            }
        } catch (e) {
            return false;
        }
    }

    function returnToHome() {
        var $links = $('.sidenav-menu').find('li');
        $links.removeClass('activated-link');
        hideLinkLandings();
        $('#videos').show();
        var $video = $('video:visible');
        if ($video.length) {
            $video[0].play();
            $video[0].muted = true;
        }
    }
});