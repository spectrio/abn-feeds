$(document).ready(function () {
    $('.map-col').html('<div id="US" class="state-area federal-incentive-link badge badge-primary" data-info="">Federal<br />Incentives</div>');
    $('.map-col').append(usMap);
    $('.map-col').find('.state-area').each(function () {
        var stateID = $(this).attr('id');
        if (stateID == 'circle60') {
            stateID = 'DC';
        }
        var stateData = getStateMapData(stateID);
        $(this).attr('data-info', stateData);
        if (stateID == 'NY') {
            //$('#card-testing').html(stateData);
        }
    });

    $('input[type="range"]').rangeslider({
        polyfill: false,
        onSlide: function (position, value) {
            $('.inc-calc-mpg-counter').text(value + ' MPG');
            updateFuelSavings(value);
        },
        onSlideEnd: function (position, value) {
            $('.inc-calc-mpg-counter').text(value + ' MPG');
            updateFuelSavings(value);
        }
    });

    function updateFuelSavings(mpg) {
        var mpgInt = parseInt(mpg);
        var savings = 0;
        var milesPerYearValue = 15000 || 0;
        var gallonsPerYear = milesPerYearValue / mpgInt;
        var gallonsPerYearE = milesPerYearValue * 0.29;
        var priceGas = parseFloat(2.45);
        var priceE = parseFloat(0.13);

        if (mpgInt > 0) {
            savings = Math.floor((gallonsPerYear * priceGas) - (gallonsPerYearE * priceE));
        } else {
            savings = 0;
        }

        if (savings < 0) {
            savings = 0;
        }
        $('#inc-calc-fuel-savings-one').text(savings);
        $('#inc-calc-fuel-savings-two').text(savings);
    }

    updateFuelSavings(24);
    totalIncentives(0);
    colorIncentives();
    /*<
           div class = "savingsCalculatorSettings"
       data - federal - credit - value = "7500"
       data - combined - savings - text = "Estimated Federal and State Incentives"
       data - federal - only - text = "Estimated Federal Incentives"
       data - miles - per - year - value = "15000"
       data - egolf - kwh = "0.29"
       data - slider - mpg - min = "12"
       data - slider - mpg - max = "45"
       data - slider - mpg -
           default = "24"
       data - price - regular - fallback = "2.45"
       data - price - electric - fallback = "0.13" > < /div>*/
    /*polyfill: true,
    rangeClass: 'rangeslider',
    disabledClass: 'rangeslider--disabled',
    horizontalClass: 'rangeslider--horizontal',
    verticalClass: 'rangeslider--vertical',
    fillClass: 'rangeslider__fill',
    handleClass: 'rangeslider__handle',
    onInit: function () {},
    onSlide: function (position, value) {},
    onSlideEnd: function (position, value) {}*/

    /*$(".map-col path, .map-col circle").mouseenter(function (e) {
        $('#state-info-popup').css('display', 'block');
        $('#state-info-popup').html($(this).data('info'));
    });

    $(".map-col path, .map-col circle").click(function (e) {
        var stateAbbr = $(this).attr('id');
        if (stateAbbr == 'circle60') {
            stateAbbr = 'DC';
        }
        $('#incentives-state-title').html(abbrState(stateAbbr, 'name') + ' Incentives');
        $('#incentives-detail-heading').html('');
        $('#incentives-detail-text').html('');
        $('#incentives-detail-references').html('');

        fillIncentives(stateAbbr);
    });

    $(".map-col path, .map-col circle").mouseleave(function (e) {
        //$('#state-info-popup').css('display', 'none');
    });*/

    $(document).on('mouseenter', '.state-area', function () {
        $('#state-info-popup').css('display', 'block');
        $('#state-info-popup').html($(this).data('info'));
    });

    $('body').on('click', '.state-area', function () {
        $('#state-info-popup').css('display', 'block');
        $('#state-info-popup').html($(this).data('info'));
        $(document).off('mouseenter', '.state-area');
        var stateAbbr = $(this).attr('id');
        if (stateAbbr == 'circle60') {
            stateAbbr = 'DC';
        }
        if (stateAbbr == 'US') {
            $('#incentives-state-title').html(abbrState(stateAbbr, 'name') + ' Federal Incentives and Legislation');
        } else {
            $('#incentives-state-title').html(abbrState(stateAbbr, 'name') + ' Incentives and Legislation');
        }
        $('#incentives-detail-heading').html('');
        $('#incentives-detail-text').html('');
        $('#incentives-detail-references').html('');
        fillIncentives(stateAbbr);
    });

    $('body').on('click', '.state-card', function () {
        $(this).find('.state-details').toggleClass('show-state-details');
    });

    $('body').on('click', '.incentive-link', function () {
        $('#incentives-detail-heading').html($(this).attr('data-inctitle'));
        $('#incentives-detail-text').html($(this).attr('data-inctext'));
        $('#incentives-detail-references').html('<hr class="maps-iw-hr"></hr>' + $(this).attr('data-increferences'));
        $('#incentives-modal').modal('show');
    });

    $('body').on('click', '.electric-calculator-link', function () {
        $('#calculator-iframe').attr('src', 'https://afdc.energy.gov/calc/');
        $('#calculator-modal').modal('show');
    })

    function fillIncentives(stateAbbr) {
        getStateIncentive(stateAbbr);
        $.ajax({
            url: 'https://developer.nrel.gov/api/transportation-incentives-laws/v1.json?jurisdiction=' + stateAbbr + '&expired=false&technology=ELEC&user_type=IND&api_key=T160QihfiP8fudHdKR2goMdRMkUJRqOG9Ie3Rk1y&format=JSON',
            success: function (result, status, xhr) {
                //console.log(result);
                $('#incentives-list').html('');
                if (result.result) {
                    if (result.result.length > 0) {
                        for (var i = 0; i < result.result.length; i++) {
                            var incTitle = 'No Title for the Incentive';
                            if (result.result[i].title) {
                                incTitle = result.result[i].title;
                            }
                            var incText = 'No Detail for the Incentive';
                            if (result.result[i].plaintext) {
                                incText = result.result[i].plaintext.replace(/\\/g, '').replace(/\((?:https?|ftp):\/\/[\n\S\()]+/g, '');
                            }
                            var incReferences = 'No Legal References for the Incentive';
                            if (result.result[i].references) {
                                if (result.result[i].references.length > 0) {
                                    incReferences = '';
                                    for (var z = 0; z < result.result[i].references.length; z++) {
                                        if (result.result[i].references[z].description) {
                                            incReferences += result.result[i].references[z].description + '<br />';
                                        }
                                    }
                                }
                            }
                            var builtLink = '<div class="incentive-link" data-inctitle="' + incTitle + '" data-inctext="' + incText + '" data-increferences="' + incReferences + '">' + incTitle + '</div><hr class="maps-iw-hr">';
                            $('#incentives-list').append(builtLink);
                            //console.log(result.result[i]);
                        }
                    } else {
                        $('#incentives-list').html('<div>No Incentives for the Selected Area</div><hr class="maps-iw-hr">');
                    }
                }
            }
        });
    }

    function getStateIncentive(input) {
        var stateInc = 0;
        if (stateIncentives[input]) {
            stateInc = parseInt(stateIncentives[input].value, 10);
            $('#inc-calc-state-incentives').text(stateInc);
            $('#inc-calc-state-incentives-text').text(stateIncentives[input].text);
        } else {
            $('#inc-calc-state-incentives').text(0);
            $('#inc-calc-state-incentives-text').text("There are currently no additional incentives");
        }
        totalIncentives(stateInc);
    }

    function totalIncentives(stateInc) {
        var fedInc = parseInt($('#inc-calc-federal-incentives > .odometer-inside').find('.odometer-digit-inner').text(), 10);
        var totalInc = fedInc + stateInc;
        $('#inc-calc-federal-and-state-incentives').text(totalInc);
    }

    function colorIncentives() {
        var p = stateIncentives;
        var obj = stateIncentives;
        Object.keys(obj).forEach(function (key) {
            var checkValue = parseInt(obj[key].value, 10);
            if (key == 'DC') {
                var el = $('#' + key + ' circle');
            } else {
                var el = $('#' + key);
            }
            if (checkValue > 0) {
                el.css('fill', '#3fb53f');
            } else {
                el.css('fill', '#317dce');
            }
        });
        /*        for (var key in p) {
                    console.log(key);
                    if (p[key].value > 0) {
                        $('#' + key).css('stroke', '#00FF00');
                        $('#' + key).css('fill', '#00FF00');
                    }
                }*/
    }

    // USAGE:
    // abbrState('ny', 'name');
    // --> 'New York'
    // abbrState('New York', 'abbr');
    // --> 'NY'

    function abbrState(input, to) {
        var states = [
            ['Alabama', 'AL'],
            ['Alaska', 'AK'],
            ['Arizona', 'AZ'],
            ['Arkansas', 'AR'],
            ['California', 'CA'],
            ['Colorado', 'CO'],
            ['Connecticut', 'CT'],
            ['Delaware', 'DE'],
            ['Florida', 'FL'],
            ['Georgia', 'GA'],
            ['Hawaii', 'HI'],
            ['Idaho', 'ID'],
            ['Illinois', 'IL'],
            ['Indiana', 'IN'],
            ['Iowa', 'IA'],
            ['Kansas', 'KS'],
            ['Kentucky', 'KY'],
            ['Louisiana', 'LA'],
            ['Maine', 'ME'],
            ['Maryland', 'MD'],
            ['Massachusetts', 'MA'],
            ['Michigan', 'MI'],
            ['Minnesota', 'MN'],
            ['Mississippi', 'MS'],
            ['Missouri', 'MO'],
            ['Montana', 'MT'],
            ['Nebraska', 'NE'],
            ['Nevada', 'NV'],
            ['New Hampshire', 'NH'],
            ['New Jersey', 'NJ'],
            ['New Mexico', 'NM'],
            ['New York', 'NY'],
            ['North Carolina', 'NC'],
            ['North Dakota', 'ND'],
            ['Ohio', 'OH'],
            ['Oklahoma', 'OK'],
            ['Oregon', 'OR'],
            ['Pennsylvania', 'PA'],
            ['Rhode Island', 'RI'],
            ['South Carolina', 'SC'],
            ['South Dakota', 'SD'],
            ['Tennessee', 'TN'],
            ['Texas', 'TX'],
            ['Utah', 'UT'],
            ['United States', 'US'],
            ['Vermont', 'VT'],
            ['Virginia', 'VA'],
            ['Washington', 'WA'],
            ['Washington, D.C.', 'DC'],
            ['West Virginia', 'WV'],
            ['Wisconsin', 'WI'],
            ['Wyoming', 'WY'],
        ];

        if (to == 'abbr') {
            input = input.replace(/\w\S*/g, function (txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
            for (i = 0; i < states.length; i++) {
                if (states[i][0] == input) {
                    return (states[i][1]);
                }
            }
        } else if (to == 'name') {
            input = input.toUpperCase();
            for (i = 0; i < states.length; i++) {
                if (states[i][1] == input) {
                    return (states[i][0]);
                }
            }
        }
    }
});