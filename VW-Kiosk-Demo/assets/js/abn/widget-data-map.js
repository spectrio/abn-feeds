/**
 *
 * Widget Weather Functions
 *
 */
var mapDisplayCallback;
loadWidgetDataMap();

function loadWidgetDataMap(displayCallback) {
    if (displayCallback && typeof displayCallback == 'function') {
        mapDisplayCallback = displayCallback;
    }
    var dataURL = 'https://developer.nrel.gov/api/alt-fuel-stations/v1.json?fuel_type=ELEC&access=public&country=US,CA&api_key=T160QihfiP8fudHdKR2goMdRMkUJRqOG9Ie3Rk1y&format=JSON';
    var dataExpirationInterval = 72;
    var logPrefix = 'WidgetDataMap: ';

    debug.log(logPrefix + 'Begin loading offline data...');
    debug.log(logPrefix + 'Map Storage Key: mapData');

    debug.log(logPrefix + 'Initial Data URL: ' + dataURL);
    checkData(); // End MAIN script

    function checkData() {
        if (getOfflineData('widgetMapExpiration') == null || getOfflineData('mapData') === null) {
            debug.log(logPrefix + 'Data not found in localStorage.  Retrieving data...');
            getData();
            return true;
        }

        debug.log(logPrefix + 'Storage Items Exist.  Beginning retrieval...');
        var widgetMapExpiration = getOfflineData('widgetMapExpiration');
        debug.log(logPrefix + 'Storage Expiration: ' + widgetMapExpiration);
        var storeData = getOfflineData('mapData');
        var mapData = JSON.parse(storeData);
        debug.log(logPrefix + 'Storage Weather Data Retrieved.');
        processDataForDisplay(mapData);
        var checkTime = new Date();
        debug.log(logPrefix + 'Check Map Data Expiration:');
        //debug.log('WidgetDataMap:Current Date/Time: ' + checkTime);
        //debug.log('WidgetDataMap:Expiration Date/Time: ' + widgetLogoExpiration);

        if (Date.parse(checkTime) > Date.parse(widgetMapExpiration)) {
            debug.log(logPrefix + 'Map Data has expired according to local expiration date.');
            getData();
            return;
        }
        debug.log(logPrefix + 'Map Data has NOT expired according to local expiration date.');
    }

    function processDataForDisplay(mapData) {
        stationPoints = mapData;
        debug.log(stationPoints);
        if (mapDisplayCallback) {
            mapDisplayCallback(mapData);
        }
    }

    function getData() {
        $.ajax({
            url: dataURL,
            cache: false,
            type: 'GET',
            success: function success(data) {
                debug.log(logPrefix + 'AJAX Success: ');
                debug.log(data);

                if (!data.fuel_stations) {
                    debug.log(logPrefix + 'No Feed Data');
                }

                if (data.fuel_stations) {
                    var mapData = JSON.stringify(processDataForStorage(data.fuel_stations));

                    var mapStored = moment();
                    debug.log(
                        logPrefix + 'Storing Map Data in Browser Local Storage with Creation Date of: ' +
                        mapStored.format('MMMM Do YYYY, h:mm:ss a')
                    );
                    storeOfflineData('mapData', mapData, 'mapData');
                    processDataForDisplay(JSON.parse(mapData));
                }
            },
            error: function error(xhr, status, _error) {
                debug.log(xhr);
                debug.log(logPrefix + 'Error in XHRs: ' + xhr.responseText);
                debug.log(logPrefix + 'Error Message: ' + _error);
                debug.log(logPrefix + 'No Feed Data, end of map data');
            }
        });
        setExpirationDate();
    }

    function processDataForStorage(data) {
        var arrayStations = data.map(function (location, i) {
            var phone;
            if (location.station_phone) {
                phone = location.station_phone;
                if (phone.includes("  ")) {
                    var splitPhone = phone.split("  ");
                    phone = '<i class="fas fa-phone"></i> ' + splitPhone[1];
                } else {
                    phone = '<i class="fas fa-phone"></i> ' + phone;
                }
            } else {
                phone = '<i class="fas fa-phone"></i> No Phone Listed';
            }

            var lvl1Chargers;
            if (location.ev_level1_evse_num) {
                var level1 = location.ev_level1_evse_num;
                if (level1 > 1) {
                    lvl1Chargers = '<span class="badge badge-primary">' + level1 + '</span> Level 1 Chargers';
                } else {
                    lvl1Chargers = '<span class="badge badge-primary">' + level1 + '</span> Level 1 Charger';
                }
            }

            var lvl2Chargers;
            if (location.ev_level2_evse_num) {
                var level2 = location.ev_level2_evse_num;
                if (level2 > 1) {
                    lvl2Chargers = '<span class="badge badge-primary">' + level2 + '</span> Level 2 Chargers';
                } else {
                    lvl2Chargers = '<span class="badge badge-primary">' + level2 + '</span> Level 2 Charger';
                }
            }

            var otherChargers;
            if (location.ev_other_evse) {
                var otherSplit = location.ev_other_evse.split(" ");
                var otherCount = otherSplit[0];
                otherSplit.shift();
                if (otherCount > 1) {
                    otherChargers = '<span class="badge badge-primary">' + otherCount + '</span> ' + otherSplit.join(" ") + ' Chargers';
                } else {
                    otherChargers = '<span class="badge badge-primary">' + otherCount + '</span> ' + otherSplit.join(" ") + ' Charger';
                }
            }

            var levelString;
            if (lvl1Chargers) {
                levelString = lvl1Chargers;
            }
            if (lvl2Chargers) {
                if (levelString) {
                    levelString += '<br />' + lvl2Chargers;
                } else {
                    levelString = lvl2Chargers;
                }
            }
            if (otherChargers) {
                if (levelString) {
                    levelString += '<br />' + otherChargers;
                } else {
                    levelString = otherChargers;
                }
            }
            if (levelString == null) {
                levelString = '';
            }

            var plugs;
            if (location.ev_connector_types) {
                var connectors = location.ev_connector_types;
                for (var i = 0; i < connectors.length; i++) {
                    var connCheck = connectors[i].toLowerCase();
                    var connImage;
                    switch (connCheck) {
                        case "nema515":
                        case "nema520":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_nema520.png" />';
                            break;
                        case "nema1450":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_nema1450.png" />';
                            break;
                        case "j1772":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_j1772.png" />';
                            break;
                        case "chademo":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_chademo.png" />';
                            break;
                        case "j1772combo":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_j1772_combo.png" />';
                            break;
                        case "tesla":
                            connImage = '<img class="maps-iw-plug-img" src="assets/images/plugs/plug_tesla.png" />';
                            break;
                    }
                    if (plugs == null) {
                        plugs = connImage + ' ' + connectors[i];
                    } else {
                        plugs += '<br />' + connImage + ' ' + connectors[i];
                    }
                }
            }

            if (plugs == null) {
                plugs = '';
            } else {
                plugs = '<hr class="maps-iw-hr" />Connectors Available:<br />' + plugs;
            }

            var contentWindow = '<div class="maps-iw">' +
                '<h1 class="maps-iw-heading">' + location.station_name + '</h1>' +
                '<div class="maps-iw-body">' +
                '<div class="maps-iw-address">' + location.street_address + '<br />' + location.city + ', ' + location.state + '&nbsp;&nbsp;' + location.zip + '</div>' +
                '<div class="maps-iw-phone">' + phone + '</div>' +
                '<div class="maps-iw-chargers">' + levelString + '</div>' +
                '<div class="maps-iw-plugs">' + plugs + '</div>' +
                '</div>' +
                '</div>';

            var marker = {
                position: {
                    lat: location.latitude,
                    lng: location.longitude
                },
                icon: 'assets/images/maps/map_marker_48.png',
                title: location.station_name,
                html: contentWindow
            };
            return marker;
        });
        debug.log(arrayStations);
        return arrayStations;
    }

    function setExpirationDate() {
        var baseDate = new Date();
        var interval = dataExpirationInterval; // Hours

        var startDate = new Date(Date.parse(baseDate));
        debug.log(logPrefix + 'baseDate: ' + startDate);
        var expirationDate = startDate;
        expirationDate.setHours(expirationDate.getHours() + interval);
        debug.log(logPrefix + 'expireDate: ' + expirationDate);
        storeOfflineData('widgetMapExpiration', expirationDate);
    }
}