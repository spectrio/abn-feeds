// check the console for output and note that the line numbers in c
// odepen are not correct. It will be correct in your code!
var isDebug = false;
var Debugger = function (gState, klass) {
    this.debug = {}
    if (!window.console) return function () {}
    if (gState && klass.isDebug) {
        for (var m in console) {
            if (typeof console[m] == 'function') {
                this.debug[m] = console[m].bind(window.console, klass.toString() + ": ");
            }
        }
    } else {
        for (var m in console) {
            if (typeof console[m] == 'function') {
                this.debug[m] = function () {};
            }
        }
    }
    return this.debug;
}

// we instantiate with the global switch and a ref to this for the local 
// this must have it's own isDebug defined for local controll
var debug = Debugger(isDebug, this);

if (isDebug) {
    if (sentryKey) {
        Sentry.init({
            dsn: sentryKey
        });
    }
}

/*
 *
 * All functions of console:
 * debug.log, debug.info, debug.debug, debug.error,
 * and debug.trace for stack trace
 * 
 */
/**
 *
 * Widget Weather Functions
 *
 */
var mapDisplayCallback;
var logPrefix = 'WidgetDataMapWork: ';
$.ajax({
    url: 'https://developer.nrel.gov/api/alt-fuel-stations/v1.json?fuel_type=ELEC&access=public&country=US,CA&api_key=T160QihfiP8fudHdKR2goMdRMkUJRqOG9Ie3Rk1y&format=JSON',
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
            processDataForDisplay(mapData);
        }
    },
    error: function error(xhr, status, _error) {
        debug.log(xhr);
        debug.log(logPrefix + 'Error in XHRs: ' + xhr.responseText);
        debug.log(logPrefix + 'Error Message: ' + _error);
        debug.log(logPrefix + 'No Feed Data, end of map data');
    }
});

function processDataForDisplay(data) {
    var contentType = 'application/octet-stream';
    var a = document.createElement('a');
    var content = JSON.parse(data);
    var blob = new Blob([content], {
        'type': contentType
    });
    a.href = window.URL.createObjectURL(blob);
    a.download = 'charging-points.json';
    a.click();
}

var textFile = null,
    makeTextFile = function (text) {
        var data = new Blob([text], {
            type: 'text/plain'
        });

        // If we are replacing a previously generated file we need to
        // manually revoke the object URL to avoid memory leaks.
        if (textFile !== null) {
            window.URL.revokeObjectURL(textFile);
        }

        textFile = window.URL.createObjectURL(data);

        // returns a URL you can use as a href
        return textFile;
    };

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

var stateDataArray = [
    ["AL", "Alabama", "Montgomery", "Yellowhammer", "Camellia", "4849377", "Birmingham", "32.738772", "86.638184", "52423", "December 14, 1819", "2,407 feet", "Sea level", "500 feet", "907", "Audemus Jura Nostra Defendere", "7", " Yellowhammer State", "George Washington Carver, who discovered more than 300 uses for peanuts", "Longleaf Pine Pinaceae Pinus palustris ", "Based on the Choctaw word albah amo meaning 'thicket clearers'"],
    ["AK", "Alaska", "Juneau", "Willow Ptarmigan", "ForgetMeNot", "736732", "Anchorage", "66.160507", "153.369141", "656424", "January 3, 1959", "20,320 feet", "Sea level", "1,900 feet", "205, 251, 256, 334", "North to the Future", "7", "The Last Frontier ", "The longest coastline in the U.S., 6,640 miles, greater than that of all other states combined", "Sitka Spruce (Picea sitchensis) ", "Based on an Aleut word alaxsxaq literally meaning 'object toward which the action of the sea is directed' or more simply, 'the mainland'"],
    ["AZ", "Arizona", "Phoenix", "Cactus Wren", "Saguaro Cactus Blossom", "6731484", "Phoenix", "34.048927", "111.093735", "114006", "February 14, 1912", "12,633 feet", "70 feet", "4,100 feet", "479, 501, 870", "Ditat Deus", "7", "The Grand Canyon State", "The most telescopes in the world, in Tucson", "Yellow Palo Verde (Parkinsonia microphylla) ", "Based on the Basque word aritz onak meaning 'good oak' or the Spanish word Arizonac meaning 'having a little spring'"],
    ["AR", "Arkansas", "Little Rock", "Mockingbird", "Apple Blossom", "2966369", "Little Rock", "34.820568", "91.999512", "53182", "June 15, 1836", "2,753 feet", "55 feet", "650 feet", "480, 520, 602, 623, 928", "Regnat Populus", "7", "The Natural State", "The only active diamond mine in the U.S.", "Loblolly Pine (Pinus taeda) ", "French interpretation of a Sioux word acansa meaning 'downstream people'"],
    ["CA", "California", "Sacramento", "California Valley Quail", "Golden Poppy", "38802500", "Los Angeles", "36.778259", "119.417931", "163707", "September 9, 1850", "14,494 feet", "282 feet", "2,900 feet", "209, 213, 310, 323, 408, 415, 510, 530, 559, 562, 619, 626, 650, 661, 707, 714, 760, 805, 818, 831, 858, 909, 916, 925, 949, 951", "Eureka", "6L", "Golden State", "'General Sherman', a 3,500yearold tree, and a stand of bristlecone pines 4,000 years old are the world's oldest living things", "California Redwood (Sequoia sempervirens & Sequoia gigantea) ", "Named by the Spanish after Califia, a mythical paradise in a Spanish romance, written by Montalvo in 1510. "],
    ["CO", "Colorado", "Denver", "Lark Bunting", "Rocky Mountain Columbine", "5355866", "Denver", "39.113014", "105.358887", "104100", "August 1, 1876", "14,440 feet", "3,315 feet", "6,800 feet", "303, 719, 970", "Nil Sine Numine", "6", "Centennial State", "The world's largest silver nugget (1,840 pounds) found in 1894 near Aspen", "Colorado Blue Spruce (Picea pugens) ", "Taken from the Spanish for the color red, referring to the banks of the Colorado river. "],
    ["CT", "Connecticut", "Hartford", "Robin", "Mountain Laurel", "3596677", "Bridgeport", "41.481833", "72.663574", "5544", "January 9, 1788", "2,380 feet", "Sea level", "500 feet", "203, 860", "Qui Transtulit Sustinet", "6", "Constitution State", "The first American cookbook, published in Hartford in 1796: American Cookery by Amelia Simmons", "White Oak(Quercus alba) ", "From the Eastern Algonquin Indian word quinnitukqut meaning 'at the long tidal river', referring to the Connecticut River "],
    ["DE", "Delaware", "Dover", "Blue Hen Chicken", "Peach Blossom", "935614", "Wilmington", "39.102357", "75.388184", "2489", "December 7, 1787", "450 feet", "Sea level", "60 feet", "302", "Liberty and independence", "6L", "Blue Hen State", "The first log cabins in North America, built in 1683 by Swedish immigrants", "American Holly(Ilex opaca) ", "Named after an early Virginia governor, Lord de la Warr "],
    ["FL", "Florida", "Tallahassee", "Mockingbird", "Orange Blossom", "19893297", "Jacksonville", "28.659261", "81.89209", "59988", "March 3, 1845", "345 feet", "Sea level", "100 feet", "239, 305, 321, 352, 386, 407, 561, 727, 772, 813, 850, 863, 904, 941, 954", "In God we trust", "7", "Sunshine State", "U.S. spacecraft launchings from Cape Canaveral, formerly Cape Kennedy", "Sabal Palm (Sabal palmetto) ", "Named on Easter 1513 by Ponce de Leon for Pascua Florida, meaning 'Flowery Easter' "],
    ["GA", "Georgia", "Atlanta", "Brown Thrasher", "Cherokee Rose", "10097343", "Atlanta", "33.247875", "83.441162", "59441", "January 2, 1788", "4,784 feet", "Sea level", "600 feet", "229, 404, 478, 706, 770, 912", "Wisdom, Justice, and Moderation", "7", "Peach State", "The Girl Scouts, founded in Savannah by Juliette Gordon Low in 1912", "Southern Live Oak(Quercus virginiana) ", "Named for King George II of England "],
    ["HI", "Hawaii", "Honolulu", "Nene", "Hawaiian Hibiscus", "1419561", "Honolulu", "21.289373", "157.91748", "6459", "August 21, 1959", "13,796 feet", "Sea level", "3,030 feet", "808", "Agriculture and Commerce", "", "Aloha State", "The only royal palace in the U.S. (Iolani)", "Candlenut Tree (Aleurites moluccana) ", "Could be based on native Hawaiian word for homeland, Owhyhee "],
    ["ID", "Idaho", "Boise", "Mountain Bluebird", "Syringa, mock orange", "1634464", "Boise", "44.068203", "114.742043", "83574", "July 3, 1890", "12,662 feet", "710 feet", "5,000 feet", "319, 515, 563, 641, 712", "Esto Perpetua", "7", "Gem State", "The longest main street in America, 33 miles, in Island Park", "Western White Pine(Pinus monticola) ", "Idaho is a coined, or invented, word "],
    ["IL", "Illinois", "Springfield", "Cardinal", "Violet", "12880580", "Chicago", "40.185168", "89.143066", "57918", "December 3, 1818", "1,235 feet", "279 feet", "600 feet", "208", "State sovereignty, national union", "7", "Prairie State", "The tallest building in the U.S., Sears Tower, in Chicago", "White Oak(Quercus alba) ", "Based on the Algonquin Indian word ilenweewa meaning 'warriors'"],
    ["IN", "Indiana", "Indianapolis", "Cardinal", "Peony", "6596855", "Indianapolis", "40.267193", "86.134903", "36420", "December 11, 1816", "1,257 feet", "320 feet", "700 feet", "217, 309, 312, 618, 630, 708, 773, 815, 847", "The crossroads of America", "", "Hoosier State", "The famous car race: the Indy 500", "Tulip Tree (Liriodendron tulipifera) ", "Latin for 'Land of the Indians'"],
    ["IA", "Iowa", "Des Moines", "Eastern Goldfinch", "Wild Prairie Rose", "3107126", "Des Moines", "42.032974", "93.581543", "56276", "December 28, 1846", "1,670 feet", "480 feet", "1,100 feet", "219, 260, 317, 574, 765, 812", "Our liberties we prize and our rights we will maintain", "7", "Hawkeye State", "The shortest and steepest railroad in the U.S., Dubuque: 60 incline, 296 feet", "Bur Oak (Quercus macrocarpa) ", "The name Iowa comes from Ioway, the French word for the Bahkhoje Indian tribe that lived in the area. "],
    ["KS", "Kansas", "Topeka", "Western Meadowlark", "Sunflower", "2904021", "Wichita", "38.588967", "98.415527", "82282", "January 29, 1861", "4,039 feet", "679 feet", "2,000 feet", "316, 620, 785, 913", "Ad Astra per Aspera", "6", "Sunflower State", "Helium discovered in 1905 at the University of Kansas", "Eastern Cottonwood(Populus deltoides) ", "From the Sioux Indian for 'south wind people'"],
    ["KY", "Kentucky", "Frankfort", "Cardinal", "Goldenrod", "4413457", "Louisville", "37.839333", "84.27002", "40411", "June 1, 1792", "4,139 feet", "257 feet", "750 feet", "270, 502, 606, 859", "United we stand, divided we shall fall", "", "Bluegrass State", "The largest underground cave in the world: 300 miles long, the MammothFlint Cave system", "Tulip Tree (Liriodendron tulipifera) ", "Based on the Iroquois Indian word Kentahten meaning 'land of tomorrow' "],
    ["LA", "Louisiana", "Baton Rouge", "Eastern Brown Pelican", "Magnolia", "4649676", "New Orleans", "31.398191", "92.658691", "51843", "April 30, 1812", "535 feet", "8 feet", "100 feet", "225, 318, 337, 504, 985", "Union, justice, confidence ", "7", "Pelican State", "The most crayfish: 98% of the world's crayfish", "Bald Cypress(Taxodium distichum) ", "Named in honor of France's King Louis XIV "],
    ["ME", "Maine", "Augusta", "Chickadee", "Pine Cone and Tassel", "1330089", "Portland", "45.187844", "68.972168", "35387", "March 15, 1820", "5,276 feet", "Sea level", "600 feet", "413, 508, 617, 781, 978", "Dirigo", "", "Pine Tree State", "The most easterly point in the U.S., West Quoddy Head1", "Eastern White Pine(Pinus strobus) ", "Assumed to be a reference to the state region being a mainland, different from its many surrounding islands "],
    ["MD", "Maryland", "Annapolis", "Baltimore Oriole", "BlackEyed Susan", "5976407", "Baltimore", "39.045753", "76.641273", "12407", "April 28, 1788", "3,360 feet", "Sea level", "350 feet", "301, 410", "Fatti Maschii Parole Femine", "", "Old Line State", "The first umbrella factory in the U.S., 1928, Baltimore", "White Oak(Quercus alba) ", "Named to honor Henrietta Maria, wife of England's King Charles "],
    ["MA", "Massachusetts", "Boston", "Chickadee", "Mayflower", "6745408", "Boston", "42.407211", "71.382439", "10555", "February 6, 1788", "3,487 feet", "Sea level", "500 feet", "207", "Ense Petit Placidam Sub Libertate Quietem", "", "Bay State", "The first World Series, 1903: the Boston 'Americans' became the Red Sox in 1908) vs. the Pittsburg Pirates (Pittsburgh had no 'h' between 1890 1911)", "American Elm(Ulmus americana) ", "Based on the Algonquin Indian word massachussett meaning 'near the great little mountain'"],
    ["MI", "Michigan", "Lansing", "Robin", "Apple Blossom", "9909877", "Detroit", "43.522663", "84.70459", "96810", "Jan 26, 1837", "1,979 feet", "572 feet", "900 feet", "231, 248, 269, 313, 517, 586, 616, 734, 810, 906, 989", "Si Quaeris Peninsulam Amoenam Circumspice", "", "Wolverine State", "The Cereal Bowl of America, Battle Creek, produces most cereal in the U.S.", "Eastern White Pine(Pinus strobus) ", "Based on the Ojibwa Indian word misshikama meaning 'great water', referring to the Great Lakes "],
    ["MN", "Minnesota", "Saint Paul", "Common Loon", "Pink and White Lady's Slipper", "5457173", "Minneapolis", "46.392410", "94.63623", "86943", "May 11, 1858", "2,301 feet", "602 feet", "1,200 feet", "218, 320, 507, 612, 651, 763, 952", "L'Etoile du Nord", "7R", "Land of 10,000 Lakes", "The oldest rock in the world, 3.8 billion years old, found in Minnesota River valley", "Red Pine(Pinus resinosa) ", "Based on the Dakota Sioux Indian word mnisota for 'skytinted water', referring to the Minnesota River "],
    ["MS", "Mississippi", "Jackson", "Mockingbird", "Magnolia", "2994079", "Jackson", "32.701800", "89.626465", "48434", "December 10, 1817", "806 feet", "Sea level", "300 feet", "314, 417, 573, 636, 660, 816", "Virtute et Armis", "7", "Magnolia State", "CocaCola, first bottled in 1894 in Vicksburg", "Southern Magnolia(Magnolia grandiflora) ", "Based on the Ojibwa Indian word messipi meaning 'big river' "],
    ["MO", "Missouri", "Jefferson City", "Bluebird", "Hawthorn", "6063589", "Kansas City", "38.313646", "92.702637", "69709", "August 10, 1821", "1,772 feet", "230 feet", "800 feet", "228, 601, 662", "Salus Populi Suprema Lex Esto", "7", "Show Me State", "Mark Twain and some of his characters, such as Tom Sawyer and Huckleberry Finn", "Flowering Dogwood(Cornus florida) ", "Named after Missouri Indian tribe whose name means 'town of the large canoes'"],
    ["MT", "Montana", "Helena", "Western Meadowlark", "Bitterroot", "1023579", "Billings", "46.965260", "109.533691", "147046", "November 8, 1889", "12,799 feet", "1,800 feet", "3,400 feet", "406", "Oro y Plata", "6", "Treasure State", "Grasshopper Glacier, named for the grasshoppers that can still be seen frozen in ice", "Ponderosa Pine (Pinus ponderosa) ", "Based on the Spanish word monta a meaning 'mountain'"],
    ["NE", "Nebraska", "Lincoln", "Western Meadowlark", "Goldenrod", "1881503", "Omaha", "41.317013", "99.558105", "77358", "March 1, 1867", "5,424 feet", "840 feet", "2,600 feet", "252, 336, 704, 828, 910, 919", "Equality before the law", "6", "Cornhusker State", "The only roller skating museum in the world, in Lincoln", "Eastern Cottonwood(Populus deltoides) ", "Name based on an Otoe Indian word meaning 'flat water', referring to the Platte River "],
    ["NV", "Nevada", "Carson City", "Mountain Bluebird", "Sagebrush", "2839099", "Las Vegas", "39.442557", "116.784668", "110567", "October 31, 1864", "13,140 feet", "479 feet", "5,500 feet", "701", "All for our country", "6L", "The Silver State", "Rare fish such as the Devils Hole pup, found only in Devils Hole, and other rare fish from prehistoric lakes; also the driest state", "SingleLeaf Pinon & Bristlecone Pine (Pinus monophylla & Pinus longaeva) ", " Nevada is from the Spanish word meaning 'snowcapped'."],
    ["NH", "New Hampshire", "Concord", "Purple Finch", "Purple Lilac", "1326813", "Manchester", "43.458900", "71.696777", "9351", "June 21, 1788", "6,288 feet", "Sea level", "1,000 feet", "308, 402", "Live free or die", "", "Granite State", "Artificial rain, first used near Concord in 1947 to fight a forest fire", "Paper Birch (Betula papyrifera) ", "From the English county of Hampshire"],
    ["NJ", "New Jersey", "Trenton", "Eastern Goldfinch", "Violet", "8938175", "Newark", "39.833851", "74.871826", "8722", ".incember 18, 1787", "1,803 feet", "Sea level", "250 feet", "603", "Liberty and Prosperity", "", "Gar.inn State", "The world's first drivein movie theater, built in 1933 near Cam.inn", "Northern Red Oak (Quercus rubra) ", "From the Channel Isle of Jersey"],
    ["NM", "New Mexico", "Albany", "Roadrunner", "Yucca Flower", "2085572", "Albuquerque", "34.307144", "106.018066", "121598", "January 6, 1912", "13,161 feet", "2,842 feet", "5,700 feet", "201, 609, 732, 856, 908, 973", "Crescit Eundo", "7", "Land of Enchantment", "'Smokey Bear', a cub orphaned by fire in 1950, buried in Smokey Bear Historical State Park in1976", "Pi on Tree (Pinus edulis) ", "Named by the Spanish for lands north of the Rio Grande River "],
    ["NY", "New York", "Santa Fe", "Bluebird", "Rose", "19746227", "New York", "42.882002", "75.256348", "54475", "July 26, 1788", "5,344 feet", "Sea level", "1,000 feet", "505, 575", "Excelsior", "7", "Empire State", "The first presidential inauguration: George Washington took the oath of office in New York City on April 30, 1789.", "Sugar Maple(Acer saccharum) ", "In honor of the Duke of York"],
    ["NC", "North Carolina", "Raleigh", "Cardinal", "Flowering Dogwood", "9943964", "Charlotte", "35.782169", "80.793457", "52672", "November 21, 1789", "6,684 feet", "Sea level", "700 feet", "702, 775", "Esse Quam Videri", "7", "Tar Heel State", "Virginia Dare, the first English child born in America, on Roanoake Island in 1587", "Longleaf Pine(Pinus palustris) ", "Taken from Carolus, the Latin word for Charles, and named after England's King Charles I "],
    ["ND", "North Dakota", "Bismark", "Western Meadowlark", "Wild Praire Rose", "739482", "Fargo", "47.650589", "100.437012", "70704", "November 2, 1889", "3,506 feet", "750 feet", "1,900 feet", "212, 315, 516, 518, 585, 607, 631, 716, 718, 845, 914", "Liberty and union, now and forever, one and inseparable", "6", "Peace Garden State", "The geographic center of North America, in Pierce County, near Balta", "American Elm (Ulmus americana) ", "Based on the Sioux Indian word dakhota meaning 'friend'"],
    ["OH", "Ohio", "Columbus", "Cardinal", "Scarlet Carnation", "11594163", "Columbus", "40.620207", "82.770996", "44828", "March 1, 1803", "1,549 feet", "455 feet", "850 feet", "216, 330, 419, 440, 513, 614, 740, 937", "With God, all things are possible", "7", "Buckeye State", "The first electric traffic lights, invented and installed in Cleveland in 1914", "Ohio Buckeye (Aesculus glabra) ", "From an Iroquoian word meaning 'great river'"],
    ["OK", "Oklahoma", "Oklahoma City", "Scissortailed Flycatcher", "Oklahoma Rose", "3878051", "Oklahoma City", "36.084621", "96.921387", "69903", "November 16, 1907", "4,973 feet", "289 feet", "1,300 feet", "405, 580, 918", "Labor Omnia Vincit", "6", "Sooner State", "The first parking meter, installed in Oklahoma City in 1935", "Eastern Redbud(Cercis canadensis) ", "From two Choctaw Indian words meaning 'red people'."],
    ["OR", "Oregon", "Salem", "Western Meadowlark", "Oregon Grape", "3970239", "Portland", "43.745305", "120.739746", "98386", "February 14, 1859", "11,239 feet", "Sea level", "3,300 feet", "503, 541", "Alis Volat Propriis", "6", "Beaver State", "The world's smallest park, totaling 452 inches, created in Portland on St. Patrick's Day for leprechauns and snail races", "Douglas Fir(Pseudotsuga menziesii) ", "Unknown. However, it is generally accepted that the name, first used by Jonathan Carver in 1778, was taken from the writings of Maj. Robert Rogers, an English army officer."],
    ["PA", "Pennsylvania", "Harrisburg", "Ruffed Grouse", "Mountain Laurel", "12787209", "Philadelphia", "41.203323", "77.194527", "46058", "December 12, 1787", "3,213 feet", "Sea level", "1,100 feet", "215, 412, 570, 610, 717, 724, 814", "Virtue, liberty and independence", "7", "Keystone State", "The first magazine in America: the American Magazine, published in Philadelphia for 3 months in1741", "Eastern Hemlock(Tsuga canadensis) ", "In honor of Adm. Sir William Penn, father of William Penn. It means 'Penn's Woodland.'"],
    ["RI", "Rhode Island", "Providence", "Rhode Island Red", "Violet", "1055173", "Providence", "41.514747", "71.38916", "1545", "May 29, 1790", "812 feet", "Sea level", "200 feet", "401", "Hope", "", "The Ocean State", "Rhode Island Red chickens, first bred in 1854; the start of poultry as a major American industry", "Red Maple(Acer rubrum) ", "Possibly named in honor of the Greek Island of Rhodes or was named Roode Eylandt by Adriaen Block, Dutch explorer, because of its red clay "],
    ["SC", "South Carolina", "Columbia", "Great Carolina Wren", "Yellow Jessamine", "4832482", "Columbia", "33.836082", "81.163727", "32007", "May 23, 1788", "3,560 feet", "Sea level", "350 feet", "803, 843, 864", "Dum spiro spero Animis opibusque parati", "", "Palmetto State", "The first tea farm in the U.S., created in 1890 near Summerville", "Sabal Palm (Sabal palmetto) ", "Taken from Carolus, the Latin word for Charles, and named after England's King Charles I "],
    ["SD", "South Dakota", "Pierre", "Ringnecked Pheasant", "Pasque Flower", "853175", "Sioux Falls", "44.251101", "100.217285", "77121", "November 2, 1889", "7,242 feet", "966 feet", "2,200 feet", "605", "Under God, the people rule", "6", "Mount Rushmore State", "The world's largest natural, indoor warmwater pool, Evans' Plunge in Hot Springs", "White Spruce(Picea glauca) ", "From the Sioux tribe, meaning 'allies'"],
    ["TN", "Tennessee", "Nashville", "Mockingbird", "Purple Passionflower", "6549352", "Memphis", "35.860119", "86.660156", "42146", "June 1, 1796", "6,643 feet", "178 feet", "900 feet", "423, 615, 731, 865, 901, 931", "Agriculture and commerce", "7", "Volunteer State", "Graceland, the estate and gravesite of Elvis Presley", "Tulip Tree (Liriodendron tulipfera) ", "Of Cherokee origin; the exact meaning is unknown"],
    ["TX", "Texas", "Austin", "Mockingbird", "Bluebonnet Sp.", "26956958", "Houston", "31.360673", "99.338379", "268601", "December 29, 1845", "8,749 feet", "Sea level", "1,700 feet", "210, 214, 254, 281, 325, 361, 409, 432, 512, 713, 806, 817, 830, 903, 915, 936, 940, 956, 972, 979", "Friendship", "", "Lone Star State", "NASA, in Houston, headquarters for all piloted U.S. space projects", "Pecan(Carya illinoinensis) ", "From an Indian word meaning 'friends'"],
    ["UT", "Utah", "Salt Lake City", "Common American Gull", "Sego Lily", "2942902", "Salt Lake City", "39.419220", "111.950684", "84904", "January 4, 1896", "13,528 feet", "2,000 feet", "6,100 feet", "435, 801", "Industry", "7", "The Beehive State", "Rainbow Bridge, the largest natural stone bridge in the world, 290 feet high, 275 feet across", "Quaking Aspen (Populus tremuloides) ", " "],
    ["VT", "Vermont", "Montpelier", "Hermit Thrush", "Red Clover", "626562", "Burlington", "43.903829", "72.79541", "9615", "March 4, 1791", "4,393 feet", "95 feet", "1,000 feet", "276, 434, 540, 703, 757, 804", "Freedom and Unity", "", "Green Mountain State", "The largest production of maple syrup in the U.S.", "Sugar Maple (Acer saccharum) ", "From the French 'vert mont', meaning 'green mountain'"],
    ["VA", "Virginia", "Richmond", "Cardinal", "American Dogwood", "8326289", "Virginia Beach", "37.926868", "78.024902", "42769", "June 25, 1788", "5,729 feet", "Sea level", "950 feet", "802", "Sic semper tyrannis ", "7", "Old Dominion State", "The only fulllength statue of George Washington, placed in capitol in 1796", "Flowering Dogwood(Cornus florida) ", "In honor of Elizabeth 'Virgin Queen' of England"],
    ["WA", "Washington", "Olympia", "Willow Goldfinch", "Coast Rhododendrum", "7061530", "Seattle", "47.751076", "120.740135", "71303", "November 11, 1889", "14,410 feet", "Sea level", "1,700 feet", "206, 253, 360, 425, 509", "Alki", "6", "The Evergreen State", "Lunar Rover, the vehicle used by astronauts on the moon; Boeing, in Seattle, makes aircraft and spacecraft", "Western Hemlock(Tsuga heterophylla) ", "In honor of George Washington"],
    ["WV", "West Virginia", "Charleston", "Cardinal", "Rhododendron", "1850326", "Charleston", "38.657633", "80.617676", "24231", "June 20, 1863", "4,863 feet", "240 feet", "1,500 feet", "262, 414, 608, 715, 920", "Montani Semper Liberi", "", "Mountain State", "Marbles; most of the country's glass marbles made around Parkersburg", "Sugar Maple(Acer saccharum) ", "In honor of Elizabeth, 'Virgin Queen' of England"],
    ["WI", "Wisconsin", "Madison", "Robin", "Wood Violet", "5757564", "Milwaukee", "44.376877", "89.758301", "65503", "May 29, 1848", "1,951 feet", "581 feet", "1,050 feet", "304", "Forward", "7", "Badger State", "The typewriter, invented in Milwaukee in 1867", "Sugar Maple(Acer saccharum) ", "Based on an Indian word Ouisconsin believed to mean 'grassy place'"],
    ["WY", "Wyoming", "Cheyenne", "Western Meadowlark", "Indian Paintbrush", "584153", "Cheyenne", "43.075970", "107.290283", "97818", "July 10, 1890", "13,804 feet", "3,099 feet", "6,700 feet", "307", "Equal rights", "6", "Equality State", "The 'Register of the Desert' a huge granite boulder covering 27 acres with 5,000 early pioneer names carved on it", "Eastern Cottonwood (Populus deltoides) ", "From the Delaware Indian word, meaning 'mountains and valleys alternating'; the same as the Wyoming Valley in Pennsylvania"]
];

var stateMapData = stateDataArray.map(function (location, i) {
    var stateEntry = {
        abbreviation: location[0],
        stateName: location[1],
        capitol: location[2],
        bird: location[3],
        flower: location[4],
        population: location[5],
        largestCity: location[6],
        latitude: location[7],
        longitude: location[8],
        squareMiles: location[9],
        admittedToUnion: location[10],
        highestPoint: location[11],
        lowestPoint: location[12],
        medianLatitude: location[13],
        areaCodes: location[14],
        motto: location[15],
        magnify: location[16],
        nickname: location[17],
        facts: location[18],
        tree: location[19],
        originOfName: location[20]
    };
    return stateEntry;
});

debug.log(stateMapData);

var usMap = '<div id="map-control-info-box"></div> \
<svg xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="us-map" preserveAspectRatio="xMinYMin meet" sodipodi:docname="Republican_Party_presidential_primaries_results,_2016.svg" inkscape:version="0.91 r13725" x="0px" y="0px" width="959px" height="593px" viewBox="174 100 959 593" enable-background="new 174 100 959 593" xml:space="preserve"> \
<sodipodi:namedview bordercolor="#666666" objecttolerance="10" pagecolor="#ffffff" borderopacity="1" gridtolerance="10" guidetolerance="10" inkscape:cx="509.19152" inkscape:cy="282.2353" inkscape:zoom="1.2137643" showgrid="false" id="namedview71" inkscape:current-layer="g5" inkscape:window-maximized="1" inkscape:window-y="-8" inkscape:window-x="-8" inkscape:pageopacity="0" inkscape:window-height="1017" inkscape:window-width="1920" inkscape:pageshadow="2"> \
    </sodipodi:namedview> \
<g id="g5"> \
    <path id="HI" data-info="<div>State: Hawaii</div><div>Capital: Honolulu</div>" fill="#D3D3D3" stroke="#0057A7" d="M407.1,619.3l1.9-3.6l2.3-0.3l0.3,0.8l-2.1,3.1H407.1z M417.3,615.6l6.1,2.6l2.1-0.3l1.6-3.9   l-0.6-3.4l-4.2-0.5l-4,1.8L417.3,615.6z M448,625.6l3.7,5.5l2.4-0.3l1.1-0.5l1.5,1.3l3.7-0.2l1-1.5l-2.9-1.8l-1.9-3.7l-2.1-3.6   l-5.8,2.9L448,625.6z M468.2,634.5l1.3-1.9l4.7,1l0.6-0.5l6.1,0.6l-0.3,1.3l-2.6,1.5l-4.4-0.3L468.2,634.5z M473.5,639.7l1.9,3.9   l3.1-1.1l0.3-1.6l-1.6-2.1l-3.7-0.3V639.7z M480.5,638.5l2.3-2.9l4.7,2.4l4.4,1.1l4.4,2.7v1.9l-3.6,1.8l-4.8,1l-2.4-1.5   L480.5,638.5z M497.1,654.1l1.6-1.3l3.4,1.6l7.6,3.6l3.4,2.1l1.6,2.4l1.9,4.4l4,2.6l-0.3,1.3l-3.9,3.2l-4.2,1.5l-1.5-0.6l-3.1,1.8   l-2.4,3.2l-2.3,2.9l-1.8-0.2l-3.6-2.6l-0.3-4.5l0.6-2.4l-1.6-5.7l-2.1-1.8l-0.2-2.6l2.3-1l2.1-3.1l0.5-1l-1.6-1.8L497.1,654.1z"/> \
    <path id="AK" data-info="<div>State: Alaska</div><div>Capital: Juneau</div>" fill="#D3D3D3" stroke="#0057A7" d="M332.1,553.7l-0.3,85.4l1.6,1l3.1,0.2l1.5-1.1h2.6l0.2,2.9l7,6.8l0.5,2.6l3.4-1.9l0.6-0.2l0.3-3.1   l1.5-1.6l1.1-0.2l1.9-1.5l3.1,2.1l0.6,2.9l1.9,1.1l1.1,2.4l3.9,1.8l3.4,6l2.7,3.9l2.3,2.7l1.5,3.7l5,1.8l5.2,2.1l1,4.4l0.5,3.1   l-1,3.4l-1.8,2.3l-1.6-0.8l-1.5-3.1l-2.7-1.5l-1.8-1.1l-0.8,0.8l1.5,2.7l0.2,3.7l-1.1,0.5l-1.9-1.9l-2.1-1.3l0.5,1.6l1.3,1.8   l-0.8,0.8c0,0-0.8-0.3-1.3-1c-0.5-0.6-2.1-3.4-2.1-3.4l-1-2.3c0,0-0.3,1.3-1,1c-0.6-0.3-1.3-1.5-1.3-1.5l1.8-1.9l-1.5-1.5v-5h-0.8   l-0.8,3.4l-1.1,0.5l-1-3.7l-0.6-3.7l-0.8-0.5l0.3,5.7v1.1l-1.5-1.3l-3.6-6l-2.1-0.5l-0.6-3.7l-1.6-2.9l-1.6-1.1v-2.3l2.1-1.3   l-0.5-0.3l-2.6,0.6l-3.4-2.4l-2.6-2.9l-4.8-2.6l-4-2.6l1.3-3.2v-1.6l-1.8,1.6l-2.9,1.1l-3.7-1.1l-5.7-2.4h-5.5l-0.6,0.5l-6.5-3.9   l-2.1-0.3l-2.7-5.8l-3.6,0.3l-3.6,1.5l0.5,4.5l1.1-2.9l1,0.3l-1.5,4.4l3.2-2.7l0.6,1.6l-3.9,4.4l-1.3-0.3l-0.5-1.9l-1.3-0.8   l-1.3,1.1l-2.7-1.8l-3.1,2.1l-1.8,2.1l-3.4,2.1l-4.7-0.2l-0.5-2.1l3.7-0.6v-1.3l-2.3-0.6l1-2.4l2.3-3.9v-1.8l0.2-0.8l4.4-2.3l1,1.3   h2.7l-1.3-2.6l-3.7-0.3l-5,2.7l-2.4,3.4l-1.8,2.6l-1.1,2.3l-4.2,1.5l-3.1,2.6l-0.3,1.6l2.3,1l0.8,2.1l-2.7,3.2l-6.5,4.2l-7.8,4.2   l-2.1,1.1l-5.3,1.1l-5.3,2.3l1.8,1.3l-1.5,1.5l-0.5,1.1l-2.7-1l-3.2,0.2l-0.8,2.3h-1l0.3-2.4l-3.6,1.3l-2.9,1l-3.4-1.3l-2.9,1.9   h-3.2l-2.1,1.3l-1.6,0.8l-2.1-0.3l-2.6-1.1l-2.3,0.6l-1,1l-1.6-1.1v-1.9l3.1-1.3l6.3,0.6l4.4-1.6l2.1-2.1l2.9-0.6l1.8-0.8l2.7,0.2   l1.6,1.3l1-0.3l2.3-2.7l3.1-1l3.4-0.6l1.3-0.3l0.6,0.5h0.8l1.3-3.7l4-1.5l1.9-3.7l2.3-4.5l1.6-1.5l0.3-2.6l-1.6,1.3l-3.4,0.6   l-0.6-2.4l-1.3-0.3l-1,1l-0.2,2.9l-1.5-0.2l-1.5-5.8l-1.3,1.3l-1.1-0.5l-0.3-1.9l-4,0.2l-2.1,1.1l-2.6-0.3l1.5-1.5l0.5-2.6   l-0.6-1.9l1.5-1l1.3-0.2l-0.6-1.8v-4.4l-1-1l-0.8,1.5h-6.1l-1.5-1.3l-0.6-3.9l-2.1-3.6v-1l2.1-0.8l0.2-2.1l1.1-1.1l-0.8-0.5   l-1.3,0.5l-1.1-2.7l1-5l4.5-3.2l2.6-1.6l1.9-3.7l2.7-1.3l2.6,1.1l0.3,2.4l2.4-0.3l3.2-2.4l1.6,0.6l1,0.6h1.6l2.3-1.3l0.8-4.4   c0,0,0.3-2.9,1-3.4c0.6-0.5,1-1,1-1l-1.1-1.9l-2.6,0.8l-3.2,0.8l-1.9-0.5l-3.6-1.8l-5-0.2l-3.6-3.7l0.5-3.9l0.6-2.4l-2.1-1.8   l-1.9-3.7l0.5-0.8l6.8-0.5h2.1l1,1h0.6l-0.2-1.6l3.9-0.6l2.6,0.3l1.5,1.1l-1.5,2.1l-0.5,1.5l2.7,1.6l5,1.8l1.8-1l-2.3-4.4l-1-3.2   l1-0.8l-3.4-1.9l-0.5-1.1l0.5-1.6l-0.8-3.9l-2.9-4.7l-2.4-4.2l2.9-1.9h3.2l1.8,0.6l4.2-0.2l3.7-3.6l1.1-3.1l3.7-2.4l1.6,1l2.7-0.6   l3.7-2.1l1.1-0.2l1,0.8l4.5-0.2l2.7-3.1h1.1l3.6,2.4l1.9,2.1l-0.5,1.1l0.6,1.1l1.6-1.6l3.9,0.3l0.3,3.7l1.9,1.5l7.1,0.6l6.3,4.2   l1.5-1l5.2,2.6l2.1-0.6l1.9-0.8l4.8,1.9L332.1,553.7z M217,582.6l2.1,5.3l-0.2,1l-2.9-0.3l-1.8-4l-1.8-1.5H210l-0.2-2.6l1.8-2.4   l1.1,2.4l1.5,1.5L217,582.6z M214.4,616.1l3.7,0.8l3.7,1l0.8,1l-1.6,3.7l-3.1-0.2l-3.4-3.6L214.4,616.1z M193.7,602l1.1,2.6   l1.1,1.6l-1.1,0.8l-2.1-3.1V602H193.7z M180,675.1l3.4-2.3l3.4-1l2.6,0.3l0.5,1.6l1.9,0.5l1.9-1.9l-0.3-1.6l2.7-0.6l2.9,2.6   l-1.1,1.8l-4.4,1.1l-2.7-0.5l-3.7-1.1l-4.4,1.5l-1.6,0.3L180,675.1z M228.9,670.6l1.6,1.9l2.1-1.6l-1.5-1.3L228.9,670.6z    M231.8,673.6l1.1-2.3l2.1,0.3l-0.8,1.9H231.8z M255.4,671.7l1.5,1.8l1-1.1l-0.8-1.9L255.4,671.7z M264.2,659.2l1.1,5.8l2.9,0.8   l5-2.9l4.4-2.6l-1.6-2.4l0.5-2.4l-2.1,1.3l-2.9-0.8l1.6-1.1l1.9,0.8l3.9-1.8l0.5-1.5l-2.4-0.8l0.8-1.9l-2.7,1.9l-4.7,3.6l-4.8,2.9   L264.2,659.2z M306.5,639.4l2.4-1.5l-1-1.8l-1.8,1L306.5,639.4z"/> \
    <path id="FL" data-info="<div>State: Florida</div><div>Capital: Tallahassee</div>" fill="#D3D3D3" stroke="#0057A7" d="M929.4,545.5l2.3,7.3l3.7,9.7l5.3,9.4l3.7,6.3l4.8,5.5l4,3.7l1.6,2.9l-1.1,1.3L953,593l2.9,7.4   l2.9,2.9l2.6,5.3l3.6,5.8l4.5,8.2l1.3,7.6l0.5,12l0.6,1.8l-0.3,3.4l-2.4,1.3l0.3,1.9l-0.6,1.9l0.3,2.4l0.5,1.9l-2.7,3.2l-3.1,1.5   l-3.9,0.2l-1.5,1.6l-2.4,1l-1.3-0.5l-1.1-1l-0.3-2.9l-0.8-3.4l-3.4-5.2l-3.6-2.3l-3.9-0.3l-0.8,1.3l-3.1-4.4l-0.6-3.6l-2.6-4   l-1.8-1.1l-1.6,2.1l-1.8-0.3l-2.1-5l-2.9-3.9l-2.9-5.3l-2.6-3.1l-3.6-3.7l2.1-2.4l3.2-5.5l-0.2-1.6l-4.5-1l-1.6,0.6l0.3,0.6l2.6,1   l-1.5,4.5l-0.8,0.5l-1.8-4l-1.3-4.8l-0.3-2.7l1.5-4.7v-9.5L910,585l-1.3-3.1l-5.2-1.3l-1.9-0.6l-1.6-2.6l-3.4-1.6l-1.1-3.4l-2.7-1   l-2.4-3.7l-4.2-1.5l-2.9-1.5h-2.6l-4,0.8l-0.2,1.9l0.8,1l-0.5,1.1l-3.1-0.2l-3.7,3.6l-3.6,1.9h-3.9l-3.2,1.3l-0.3-2.7l-1.6-1.9   l-2.9-1.1l-1.6-1.5l-8.1-3.9l-7.6-1.8l-4.4,0.6l-6,0.5l-6,2.1l-3.5,0.6l-0.2-8l-2.6-1.9l-1.8-1.8l0.3-3.1l10.2-1.3l25.5-2.9   l6.8-0.6l5.4,0.3l2.6,3.9l1.5,1.5l8.1,0.5l10.8-0.6l21.5-1.3l5.4-0.7l4.6,0l0.2,2.9l3.8,0.8l0.3-4.8l-1.6-4.5l1-0.7l5.1,0.5   L929.4,545.5z M941.9,677.9l2.4-0.6l1.3-0.2l1.5-2.3l2.3-1.6l1.3,0.5l1.7,0.3l0.4,1.1l-3.5,1.2l-4.2,1.5l-2.3,1.2L941.9,677.9z    M955.4,672.9l1.2,1.1l2.7-2.1l5.3-4.2l3.7-3.9l2.5-6.6l1-1.7l0.2-3.4l-0.7,0.5l-1,2.8l-1.5,4.6l-3.2,5.3l-4.4,4.2l-3.4,1.9   L955.4,672.9z"/> \
    <path id="SC" data-info="<div>State: South Carolina</div><div>Capital: Columbia</div>" fill="#D3D3D3" stroke="#0057A7" d="M935.2,512.9l-1.8,1l-2.6-1.3l-0.6-2.1l-1.3-3.6l-2.3-2.1l-2.6-0.6l-1.6-4.8l-2.7-6l-4.2-1.9   l-2.1-1.9l-1.3-2.6L910,485l-2.3-1.3l-2.3-2.9l-3.1-2.3l-4.5-1.8l-0.5-1.5l-2.4-2.9l-0.5-1.5l-3.4-5.2l-3.4,0.2l-4-2.4l-1.3-1.3   l-0.3-1.8l0.8-1.9l2.3-1l-0.3-2.1l6.1-2.6l9.1-4.5l7.3-0.8l16.5-0.5l2.3,1.9l1.6,3.2l4.4-0.5l12.6-1.5l2.9,0.8l12.6,7.6l10.1,8.1   l-5.4,5.5l-2.6,6.1l-0.5,6.3l-1.6,0.8l-1.1,2.7l-2.4,0.6l-2.1,3.6l-2.7,2.7l-2.3,3.4l-1.6,0.8l-3.6,3.4l-2.9,0.2l1,3.2l-5,5.5   L935.2,512.9z"/> \
    <path id="GA" data-info="<div>State: Georgia</div><div>Capital: Atlanta</div>" fill="#D3D3D3" stroke="#0057A7" d="M863.6,458l-4.8,0.8l-8.4,1.1l-8.6,0.9v2.2l0.2,2.1l0.6,3.4l3.4,7.9l2.4,9.9l1.5,6.1l1.6,4.8l1.5,7   l2.1,6.3l2.6,3.4l0.5,3.4l1.9,0.8l0.2,2.1l-1.8,4.8l-0.5,3.2l-0.2,1.9l1.6,4.4l0.3,5.3l-0.8,2.4l0.6,0.8l1.5,0.8l0.6,3.4l2.6,3.9   l1.5,1.5l7.9,0.2l10.8-0.6l21.5-1.3l5.4-0.7l4.6,0l0.2,2.9l2.6,0.8l0.3-4.4l-1.6-4.5l1.1-1.6l5.8,0.8l5,0.3l-0.8-6.3l2.3-10   l1.5-4.2l-0.5-2.6l3.3-6.2l-0.5-1.4l-1.9,0.7l-2.6-1.3l-0.6-2.1l-1.3-3.6l-2.3-2.1l-2.6-0.6l-1.6-4.8l-2.9-6.3l-4.2-1.9l-2.1-1.9   l-1.3-2.6l-2.1-1.9l-2.3-1.3l-2.3-2.9l-3.1-2.3l-4.5-1.8l-0.5-1.5l-2.4-2.9l-0.5-1.5l-3.4-4.9l-3.4,0.2l-4.1-3l-1.3-1.3l-0.3-1.8   l0.8-1.9l2.4-1.2l-1.1-1.2l0.1-0.3l-5.8,1l-7,0.8L863.6,458z"/> \
    <path id="AL" data-info="<div>State: Alabama</div><div>Capital: Montgomery</div>" fill="#D3D3D3" stroke="#0057A7" d="M799.6,566.8l-1.6-15.2l-2.7-18.8l0.2-14.1l0.8-31l-0.2-16.7l0.2-6.4l7.8-0.4l27.8-2.6l8.9-0.7   l-0.1,2.2l0.2,2.1l0.6,3.4l3.4,7.9l2.4,9.9l1.5,6.1l1.6,4.8l1.5,7l2.1,6.3l2.6,3.4l0.5,3.4l1.9,0.8l0.2,2.1l-1.8,4.8l-0.5,3.2   l-0.2,1.9l1.6,4.4l0.3,5.3l-0.8,2.4l0.6,0.8l1.5,0.8l1,2.5h-6.3l-6.8,0.6l-25.5,2.9l-10.4,1.4l-0.1,3.8l1.8,1.8l2.6,1.9l0.6,7.9   l-5.5,2.6l-2.7-0.3l2.7-1.9v-1l-3.1-6l-2.3-0.6l-1.5,4.4l-1.3,2.7l-0.6-0.2H799.6z"/> \
    <path id="NC" data-info="<div>State: North Carolina</div><div>Capital: Raleigh</div>" fill="#D3D3D3" stroke="#0057A7" d="M1006.1,398.5l1.7,4.7l3.6,6.5l2.4,2.4l0.6,2.3l-2.4,0.2l0.8,0.6l-0.3,4.2l-2.6,1.3l-0.6,2.1   l-1.3,2.9l-3.7,1.6l-2.4-0.3l-1.5-0.2l-1.6-1.3l0.3,1.3v1h1.9l0.8,1.3l-1.9,6.3h4.2l0.6,1.6l2.3-2.3l1.3-0.5l-1.9,3.6l-3.1,4.8   h-1.3l-1.1-0.5l-2.7,0.6l-5.2,2.4l-6.5,5.3l-3.4,4.7l-1.9,6.5l-0.5,2.4l-4.7,0.5l-5.5,1.3l-9.9-8.2l-12.6-7.6l-2.9-0.8l-12.6,1.5   l-4.3,0.8l-1.6-3.2l-3-2.1l-16.5,0.5l-7.3,0.8l-9.1,4.5l-6.1,2.6l-1.6,0.3l-5.8,1l-7,0.8l-6.8,0.5l0.5-4.1l1.8-1.5l2.7-0.6l0.6-3.7   l4.2-2.7l3.9-1.5l4.2-3.6l4.4-2.1l0.6-3.1l3.9-3.9l0.6-0.2c0,0,0,1.1,0.8,1.1c0.8,0,1.9,0.3,1.9,0.3l2.3-3.6l2.1-0.6l2.3,0.3   l1.6-3.6l2.9-2.6l0.5-2.1v-4l4.5,0.7l7.1-1.3l15.8-1.9l17.1-2.6l19.9-4l19.7-4.2l11.4-2.8L1006.1,398.5z M1010,431.5l2.6-2.5   l3.2-2.6l1.5-0.6l0.2-2l-0.6-6.1l-1.5-2.3l-0.6-1.9l0.7-0.2l2.7,5.5l0.4,4.4l-0.2,3.4l-3.4,1.5l-2.8,2.4l-1.1,1.2L1010,431.5z"/> \
    <path id="TN" data-info="<div>State: Tennessee</div><div>Capital: Nashville</div>" fill="#D3D3D3" stroke="#0057A7" d="M871.1,420.6l-51.9,5l-15.8,1.8l-4.6,0.5l-3.9,0v3.9l-8.4,0.5l-7,0.6l-11.1,0.1l-0.3,5.8l-2.1,6.3   l-1,3l-1.3,4.4l-0.3,2.6l-4,2.3l1.5,3.6l-1,4.4l-1,0.8l7.3-0.2l24.1-1.9l5.3-0.2l8.1-0.5l27.8-2.6l10.2-0.8l8.4-1l8.4-1.1l4.8-0.8   l-0.1-4.5l1.8-1.5l2.7-0.6l0.6-3.7l4.2-2.7l3.9-1.5l4.2-3.6l4.4-2.1l0.9-3.5l4.3-3.9l0.6-0.2c0,0,0,1.1,0.8,1.1s1.9,0.3,1.9,0.3   l2.3-3.6l2.1-0.6l2.3,0.3l1.6-3.6l2.1-2.2l0.6-1l0.2-3.9l-1.5-0.3l-2.4,1.9l-7.9,0.2l-12,1.9L871.1,420.6z"/> \
    <path id="RI" data-info="<div>State: Rhode Island</div><div>Capital: Providence</div>" fill="#D3D3D3" stroke="#0057A7" d="M1048.1,279.8l-0.5-4.2l-0.8-4.4l-1.7-5.9l5.7-1.5l1.6,1.1l3.4,4.4l2.9,4.4l-2.9,1.5l-1.3-0.2   l-1.1,1.8l-2.4,1.9L1048.1,279.8z"/> \
    <path id="CT" data-info="<div>State: Connecticut</div><div>Capital: Hartford</div>" fill="#D3D3D3" stroke="#0057A7" d="M1047.2,280.1l-0.6-4.2l-0.8-4.4l-1.6-6l-4.2,0.9l-21.8,4.8l0.6,3.3l1.5,7.3v8.1l-1.1,2.3l1.8,2.1   l5-3.4l3.6-3.2l1.9-2.1l0.8,0.6l2.7-1.5l5.2-1.1L1047.2,280.1z"/> \
    <path id="MA" data-info="<div>State: Massachusetts</div><div>Capital: Boston</div>" fill="#D3D3D3" stroke="#0057A7" d="M1074,273.9l2.2-0.7l0.5-1.7l1,0.1l1,2.3l-1.3,0.5l-3.9,0.1L1074,273.9z M1064.6,274.7l2.3-2.6h1.6   l1.8,1.5l-2.4,1l-2.2,1L1064.6,274.7z M1029.8,252.7l17.5-4.2l2.3-0.6l2.1-3.2l3.7-1.7l2.9,4.4l-2.4,5.2l-0.3,1.5l1.9,2.6l1.1-0.8   h1.8l2.3,2.6l3.9,6l3.6,0.5l2.3-1l1.8-1.8l-0.8-2.7l-2.1-1.6l-1.5,0.8l-1-1.3l0.5-0.5l2.1-0.2l1.8,0.8l1.9,2.4l1,2.9l0.3,2.4   l-4.2,1.5l-3.9,1.9l-3.9,4.5l-1.9,1.5v-1l2.4-1.5l0.5-1.8l-0.8-3.1l-2.9,1.5l-0.8,1.5l0.5,2.3l-2.1,1l-2.7-4.5l-3.4-4.4l-2.1-1.8   l-6.5,1.9l-5.1,1.1l-21.8,4.8l-0.4-4.9l0.6-10.6l5.2-0.9L1029.8,252.7z"/> \
    <path id="ME" data-info="<div>State: Maine</div><div>Capital: Augusta</div>" fill="#D3D3D3" stroke="#0057A7" d="M1097.2,177.3l1.9,2.1l2.3,3.7v1.9l-2.1,4.7l-1.9,0.6l-3.4,3.1l-4.8,5.5c0,0-0.6,0-1.3,0   c-0.6,0-1-2.1-1-2.1l-1.8,0.2l-1,1.5l-2.4,1.5l-1,1.5l1.6,1.5l-0.5,0.6l-0.5,2.7l-1.9-0.2v-1.6l-0.3-1.3l-1.5,0.3l-1.8-3.2   l-2.1,1.3l1.3,1.5l0.3,1.1l-0.8,1.3l0.3,3.1l0.2,1.6l-1.6,2.6l-2.9,0.5l-0.3,2.9l-5.3,3.1l-1.3,0.5l-1.6-1.5l-3.1,3.6l1,3.2   l-1.5,1.3l-0.2,4.4l-1.1,6.3l-2.5-1.2l-0.5-3.1l-3.9-1.1l-0.3-2.7l-7.3-23.4l-4.2-13.6l1.4-0.1l1.5,0.4v-2.6l0.8-5.5l2.6-4.7l1.5-4   l-1.9-2.4v-6l0.8-1l0.8-2.7l-0.2-1.5l-0.2-4.8l1.8-4.8l2.9-8.9l2.1-4.2h1.3l1.3,0.2v1.1l1.3,2.3l2.7,0.6l0.8-0.8v-1l4-2.9l1.8-1.8   l1.5,0.2l6,2.4l1.9,1l9.1,29.9h6l0.8,1.9l0.2,4.8l2.9,2.3h0.8l0.2-0.5l-0.5-1.1L1097.2,177.3z M1076.3,207.5l1.5-1.5l1.4,1.1   l0.6,2.4l-1.7,0.9L1076.3,207.5z M1083,201.6l1.8,1.9c0,0,1.3,0.1,1.3-0.2s0.2-2,0.2-2l0.9-0.8l-0.8-1.8l-2,0.7L1083,201.6z"/> \
    <path id="NH" data-info="<div>State: New Hampshire</div><div>Capital: Concord</div>" fill="#D3D3D3" stroke="#0057A7" d="M1054.8,242.4l0.9-1.1l1.1-3.3l-2.5-0.9l-0.5-3.1l-3.9-1.1l-0.3-2.7l-7.3-23.4l-4.6-14.5l-0.9,0   l-0.6,1.6l-0.6-0.5l-1-1l-1.5,1.9l0,5l0.3,5.7l1.9,2.7v4l-3.7,5.1l-2.6,1.1v1.1l1.1,1.8v8.6l-0.8,9.2l-0.2,4.8l1,1.3l-0.2,4.5   l-0.5,1.8l1.5,0.9l16.4-4.7l2.3-0.6l1.5-2.6L1054.8,242.4z"/> \
    <path id="VT" data-info="<div>State: Vermont</div><div>Capital: Montpelier</div>" fill="#D3D3D3" stroke="#0057A7" d="M1018.3,253.7l-0.8-5.7l-2.4-10l-0.6-0.3l-2.9-1.3l0.8-2.9l-0.8-2.1l-2.7-4.6l1-3.9l-0.8-5.2   l-2.4-6.5l-0.8-4.9l26.2-6.7l0.3,5.8l1.9,2.7v4l-3.7,4l-2.6,1.1v1.1l1.1,1.8v8.6l-0.8,9.2l-0.2,4.8l1,1.3l-0.2,4.5l-0.5,1.8   l0.7,1.6l-7,1.4L1018.3,253.7z"/> \
    <path id="NY" data-info="<div>State: New York</div><div>Capital: Albany</div>" fill="#D3D3D3" stroke="#0057A7" d="M1002.6,289.4l-1.1-1l-2.6-0.2l-2.3-1.9l-1.6-6.1l-3.5,0.1l-2.4-2.7l-19.4,4.4l-43,8.7l-7.5,1.2   l-0.7-6.5l1.4-1.1l1.3-1.1l1-1.6l1.8-1.1l1.9-1.8l0.5-1.6l2.1-2.7l1.1-1l-0.2-1l-1.3-3.1l-1.8-0.2l-1.9-6.1l2.9-1.8l4.4-1.5l4-1.3   l3.2-0.5l6.3-0.2l1.9,1.3l1.6,0.2l2.1-1.3l2.6-1.1l5.2-0.5l2.1-1.8l1.8-3.2l1.6-1.9h2.1l1.9-1.1l0.2-2.3l-1.5-2.1l-0.3-1.5l1.1-2.1   v-1.5h-1.8l-1.8-0.8l-0.8-1.1l-0.2-2.6l5.8-5.5l0.6-0.8l1.5-2.9l2.9-4.5l2.7-3.7l2.1-2.4l2.4-1.8l3.1-1.2l5.5-1.3l3.2,0.2l4.5-1.5   l7.6-2.1l0.5,5l2.4,6.5l0.8,5.2l-1,3.9l2.6,4.5l0.8,2.1l-0.8,2.9l2.9,1.3l0.6,0.3l3.1,11l-0.5,5.1l-0.5,10.8l0.8,5.5l0.8,3.6   l1.5,7.3v8.1l-1.1,2.3l1.8,2l0.8,1.7l-1.9,1.8l0.3,1.3l1.3-0.3l1.5-1.3l2.3-2.6l1.1-0.6l1.6,0.6l2.3,0.2l7.9-3.9l2.9-2.7l1.3-1.5   l4.2,1.6l-3.4,3.6l-3.9,2.9l-7.1,5.3l-2.6,1l-5.8,1.9l-4,1.1l-1.2-0.5l-0.2-3.7l0.5-2.7l-0.2-2.1l-2.8-1.7l-4.5-1l-3.9-1.1   L1002.6,289.4z"/> \
    <path id="NJ" data-info="<div>State: New Jersey</div><div>Capital: Trenton</div>" fill="#D3D3D3" stroke="#0057A7" d="M1002.2,290.3l-2.1,2.4v3.1l-1.9,3.1l-0.2,1.6l1.3,1.3l-0.2,2.4l-2.3,1.1l0.8,2.7l0.2,1.1l2.7,0.3   l1,2.6l3.6,2.4l2.4,1.6v0.8l-3.2,3.1l-1.6,2.3l-1.5,2.7l-2.3,1.3l-1.2,0.7l-0.2,1.2l-0.6,2.6l1.1,2.2l3.2,2.9l4.8,2.3l4,0.6   l0.2,1.5l-0.8,1l0.3,2.7h0.8l2.1-2.4l0.8-4.8l2.7-4l3.1-6.5l1.1-5.5l-0.6-1.1l-0.2-9.4l-1.6-3.4l-1.1,0.8l-2.7,0.3l-0.5-0.5l1.1-1   l2.1-1.9l0.1-1.1l-0.4-3.4l0.5-2.7l-0.2-2.1l-2.6-1.1l-4.5-1l-3.9-1.1L1002.2,290.3z"/> \
    <path id="PA" data-info="<div>State: Pennsylvania</div><div>Capital: Harrisburg</div>" fill="#D3D3D3" stroke="#0057A7" d="M996.2,326.5l1.1-0.6l2.3-0.6l1.5-2.7l1.6-2.3l3.2-3.1v-0.8l-2.4-1.6l-3.6-2.4l-1-2.6l-2.7-0.3   l-0.2-1.1l-0.8-2.7l2.3-1.1l0.2-2.4l-1.3-1.3l0.2-1.6l1.9-3.1v-3.1l2.3-2.4l0.2-1.1l-2.6-0.2l-2.3-1.9l-2.4-5.3l-3-0.9l-2.3-2.1   l-18.6,4l-43,8.7l-8.9,1.5l-0.5-7.1l-5.5,5.6l-1.3,0.5l-4.2,3l2.9,19.1l2.5,9.7l3.6,19.3l3.3-0.6l11.9-1.5l37.9-7.7l14.9-2.8   l8.3-1.6l0.3-0.2l2.1-1.6L996.2,326.5z"/> \
    <path id="DE" data-info="<div>State: Delaware</div><div>Capital: Dover</div>" fill="#D3D3D3" stroke="#0057A7" d="M996.4,330.4l0.6-2.1l0-1.2l-1.3-0.1l-2.1,1.6l-1.5,1.5l1.5,4.2l2.3,5.7l2.1,9.7l1.6,6.3l5-0.2   l6.1-1.2l-2.3-7.4l-1,0.5l-3.6-2.4l-1.8-4.7l-1.9-3.6l-2.3-1l-2.1-3.6L996.4,330.4z"/> \
    <path id="MD" data-info="<div>State: Maryland</div><div>Capital: Annapolis</div>" fill="#D3D3D3" stroke="#0057A7" d="M1011,355.3l-6.1,1.3l-5.8,0.2l-1.8-7.1l-2.1-9.7l-2.3-5.7l-1.3-4.4l-7.5,1.6l-14.9,2.8l-37.5,7.6   l1.1,5l1,5.7l0.3-0.3l2.1-2.4l2.3-2.6l2.4-0.6l1.5-1.5l1.8-2.6l1.3,0.6l2.9-0.3l2.6-2.1l2-1.5l1.8-0.5l1.6,1.1l2.9,1.5l1.9,1.8   l1.2,1.5l4.1,1.7v2.9l5.5,1.3l1.1,0.5l1.4-2l2.9,2l-1.3,2.5l-0.8,4l-1.8,2.6v2.1l0.6,1.8l5.1,1.4l4.3-0.1l3.1,1l2.1,0.3l1-2.1   l-1.5-2.1v-1.8l-2.4-2.1l-2.1-5.5l1.3-5.3l-0.2-2.1l-1.3-1.3c0,0,1.5-1.6,1.5-2.3c0-0.6,0.5-2.1,0.5-2.1l1.9-1.3l1.9-1.6l0.5,1   l-1.5,1.6l-1.3,3.7l0.3,1.1l1.8,0.3l0.5,5.5l-2.1,1l0.3,3.6l0.5-0.2l1.1-1.9l1.6,1.8l-1.6,1.3l-0.3,3.4l2.6,3.4l3.9,0.5l1.6-0.8   l3.2,4.2l1.4,0.5l6.7-2.8l2-4L1011,355.3z M994.3,364.3l1.1,2.5l0.2,1.8l1.1,1.9c0,0,0.9-0.9,0.9-1.2c0-0.3-0.7-3.1-0.7-3.1   l-0.7-2.3L994.3,364.3z"/> \
    <path id="WV" data-info="<div>State: West Virginia</div><div>Capital: Charleston</div>" fill="#D3D3D3" stroke="#0057A7" d="M930.6,342l1.1,4.9l1.1,6.9l3.6-2.7l2.3-3.1l2.5-0.6l1.5-1.5l1.8-2.6l1.2,0.6l2.9-0.3l2.6-2.1   l2-1.5l1.8-0.5l1.3,1l2.2,1.1l1.9,1.8l1.4,1.3l-0.1,4.7l-5.7-3.1l-4.5-1.8l-0.2,5.3l-0.5,2.1l-1.6,2.7l-0.6,1.6l-3.1,2.4l-0.5,2.3   l-3.4,0.3l-0.3,3.1l-1.1,5.5h-2.6l-1.3-0.8l-1.6-2.7l-1.8,0.2l-0.3,4.4l-2.1,6.6l-5,10.8l0.8,1.3l-0.2,2.7l-2.1,1.9l-1.5-0.3   l-3.2,2.4l-2.6-1l-1.8,4.7c0,0-3.7,0.8-4.4,1c-0.6,0.2-2.4-1.3-2.4-1.3l-2.4,2.3l-2.6,0.6l-2.9-0.8l-1.3-1.3l-2.2-3l-3.1-2   l-2.6-2.7l-2.9-3.7l-0.6-2.3l-2.6-1.5l-0.8-1.6l-0.2-5.3l2.2-0.1l1.9-0.8l0.2-2.7l1.6-1.5l0.2-5l1-3.9l1.3-0.6l1.3,1.1l0.5,1.8   l1.8-1l0.5-1.6l-1.1-1.8v-2.4l1-1.3l2.3-3.4l1.3-1.5l2.1,0.5l2.3-1.6l3.1-3.4l2.3-3.9l0.3-5.7l0.5-5v-4.7l-1.1-3.1l1-1.5l1.3-1.3   l3.5,19.8l4.6-0.8L930.6,342z"/> \
    <path id="KY" data-info="<div>State: Kentucky</div><div>Capital: Frankfort</div>" fill="#D3D3D3" stroke="#0057A7" d="M895.8,397.8l-2.3,2.7l-4.2,3.6L885,410l-1.8,1.8v2.1l-3.9,2.1l-5.7,3.4l-3.5,0.4l-51.9,4.9   l-15.8,1.8l-4.6,0.5l-3.9,0l-0.2,4.2l-8.2,0.1l-7,0.6l-10.4,0.2l1.9-0.2l2.2-1.8l2.1-1.1l0.2-3.2l0.9-1.8l-1.6-2.5l0.8-1.9l2.3-1.8   l2.1-0.6l2.7,1.3l3.6,1.3l1.1-0.3l0.2-2.3l-1.3-2.4l0.3-2.3l1.9-1.5l2.6-0.6l1.6-0.6l-0.8-1.8l-0.6-1.9l1.1-0.8l1.1-3.3l3-1.7   l5.8-1l3.6-0.5l1.5,1.9l1.8,0.8l1.8-3.2l2.9-1.5l1.9,1.6l0.8,1.1l2.1-0.5l-0.2-3.4l2.9-1.6l1.1-0.8l1.1,1.6h4.7l0.8-2.1l-0.3-2.3   l2.9-3.6l4.7-3.9l0.5-4.5l2.7-0.3l3.9-1.8l2.7-1.9l-0.3-1.9l-1.5-1.5l0.6-2.2l4.1-0.2l2.4-0.8l2.9,1.6l1.6,4.4l5.8,0.3l1.8,1.8   l2.1,0.2l2.4-1.5l3.1,0.5l1.3,1.5l2.7-2.6l1.8-1.3h1.6l0.6,2.7l1.8,1l2.4,2.2l0.2,5.5l0.8,1.6l2.6,1.5l0.6,2.3l2.9,3.7l2.6,2.7   L895.8,397.8z"/> \
    <path id="OH" data-info="<div>State: Ohio</div><div>Capital: Columbus</div>" fill="#D3D3D3" stroke="#0057A7" d="M905.4,295l-6.1,4.1l-3.9,2.3l-3.4,3.7l-4,3.9l-3.2,0.8l-2.9,0.5l-5.5,2.6l-2.1,0.2l-3.4-3.1   l-5.2,0.6l-2.6-1.5l-2.4-1.4l-4.9,0.7l-10.2,1.6l-7.8,1.2l1.3,14.6l1.8,13.7l2.6,23.4l0.6,4.8l4.1-0.1l2.4-0.8l3.4,1.5l2.1,4.4   l5.1,0l1.9,2.1l1.8-0.1l2.5-1.3l2.5,0.4l2,1.5l1.7-2.1l2.3-1.3l2.1-0.7l0.6,2.7l1.8,1l3.5,2.3l2.2-0.1l1.1-1.1l-0.1-1.4l1.6-1.5   l0.2-5l1-3.9l1.5-1.4l1.5,0.9l0.8,1.2l1.2-0.2l-0.4-2.4l-0.6-0.6v-2.4l1-1.3l2.3-3.4l1.3-1.5l2.1,0.5l2.3-1.6l3.1-3.4l2.3-3.9   l0.2-5.4l0.5-5v-4.7l-1.1-3.1l1-1.5l0.9-1l-1.4-9.8L905.4,295z"/> \
    <path id="MI" data-info="<div>State: Michigan</div><div>Capital: Lansing</div>" fill="#D3D3D3" stroke="#0057A7" d="M755.6,182.1l1.8-2.1l2.2-0.8l5.4-3.9l2.3-0.6l0.5,0.5l-5.1,5.1l-3.3,1.9l-2.1,0.9L755.6,182.1z    M841.8,214.2l0.6,2.5l3.2,0.2l1.3-1.2c0,0-0.1-1.5-0.4-1.6c-0.3-0.2-1.6-1.9-1.6-1.9l-2.2,0.2l-1.6,0.2l-0.3,1.1L841.8,214.2z    M871.9,277.2l-3.2-8.2l-2.3-9.1l-2.4-3.2l-2.6-1.8l-1.6,1.1l-3.9,1.8l-1.9,5l-2.7,3.7l-1.1,0.6l-1.5-0.6c0,0-2.6-1.5-2.4-2.1   c0.2-0.6,0.5-5,0.5-5l3.4-1.3l0.8-3.4l0.6-2.6l2.4-1.6l-0.3-10l-1.6-2.3l-1.3-0.8l-0.8-2.1l0.8-0.8l1.6,0.3l0.2-1.6L850,231   l-1.3-2.6h-2.6l-4.5-1.5l-5.5-3.4h-2.7l-0.6,0.6l-1-0.5l-3.1-2.3l-2.9,1.8l-2.9,2.3l0.3,3.6l1,0.3l2.1,0.5l0.5,0.8l-2.6,0.8   l-2.6,0.3l-1.5,1.8l-0.3,2.1l0.3,1.6l0.3,5.5l-3.6,2.1l-0.6-0.2v-4.2l1.3-2.4l0.6-2.4l-0.8-0.8l-1.9,0.8l-1,4.2l-2.7,1.1l-1.8,1.9   l-0.2,1l0.6,0.8l-0.6,2.6l-2.3,0.5v1.1l0.8,2.4l-1.1,6.1l-1.6,4l0.6,4.7l0.5,1.1l-0.8,2.4l-0.3,0.8l-0.3,2.7l3.6,6l2.9,6.5l1.5,4.8   l-0.8,4.7l-1,6l-2.4,5.2l-0.3,2.7l-3.3,3.1l4.4-0.2l21.4-2.3l7.3-1l0.1,1.7l6.9-1.2l10.3-1.5l3.9-0.5l0.1-0.6l0.2-1.5l2.1-3.7   l2-1.7l-0.2-5.1l1.6-1.6l1.1-0.3l0.2-3.6l1.5-3l1.1,0.6l0.2,0.6l0.8,0.2l1.9-1L871.9,277.2z M741.5,211.2l0.7-0.6l2.7-0.8l3.6-2.3   v-1l0.6-0.6l6-1l2.4-1.9l4.4-2.1l0.2-1.3l1.9-2.9l1.8-0.8l1.3-1.8l2.3-2.3l4.4-2.4l4.7-0.5l1.1,1.1l-0.3,1l-3.7,1l-1.5,3.1   l-2.3,0.8l-0.5,2.4l-2.4,3.2l-0.3,2.6l0.8,0.5l1-1.1l3.6-2.9l1.3,1.3h2.3l3.2,1l1.5,1.1l1.5,3.1l2.7,2.7l3.9-0.2l1.5-1l1.6,1.3   l1.6,0.5l1.3-0.8h1.1l1.6-1l4-3.6l3.4-1.1l6.6-0.3l4.5-1.9l2.6-1.3l1.5,0.2v5.7l0.5,0.3l2.9,0.8l1.9-0.5l6.1-1.6l1.1-1.1l1.5,0.5v7   l3.2,3.1l1.3,0.6l1.3,1l-1.3,0.3l-0.8-0.3l-3.7-0.5l-2.1,0.6l-2.3-0.2l-3.2,1.5h-1.8l-5.8-1.3l-5.2,0.2l-1.9,2.6l-7,0.6l-2.4,0.8   l-1.1,3.1l-1.3,1.1l-0.5-0.2l-1.5-1.6l-4.5,2.4h-0.6l-1.1-1.6l-0.8,0.2l-1.9,4.4l-1,4l-3.2,7l-1.2-1l-1.4-1l-1.9-10.3l-3.5-1.4   l-2.1-2.3l-12.1-2.7l-2.9-1l-8.2-2.2l-7.9-1.1L741.5,211.2z"/> \
    <path id="WY" data-info="<div>State: Wyoming</div><div>Capital: Cheyenne</div>" fill="#D3D3D3" stroke="#0057A7" d="M528.3,243.8l-10.5-0.8l-32.1-3.3l-16.2-2.1l-28.3-4.1l-19.9-3l-1.4,11.2l-3.8,24.3l-5.3,30.4   l-1.5,10.5l-1.7,11.9l6.5,0.9l25.9,2.5l20.6,2.3l36.8,4.1l23.8,2.9l4.5-44.2l1.4-25.4L528.3,243.8z"/> \
    <path id="MT" data-info="<div>State: Montana</div><div>Capital: Helena</div>" fill="#D3D3D3" stroke="#0057A7" d="M530.7,222.3l0.6-11.2l2.3-24.8c0.5-5,1.1-8.5,1.4-15.4l0.9-14.6l-30.7-2.8L476,150l-29.3-4   l-32.3-5.3l-18.4-3.4l-32.7-6.9l-4.5,21.3l3.4,7.5l-1.4,4.6l1.8,4.6l3.2,1.4l4.6,10.8l2.7,3.2l0.5,1.1l3.4,1.1l0.5,2.1l-7.1,17.6   v2.5l2.5,3.2h0.9l4.8-3l0.7-1.1l1.6,0.7l-0.2,5.3l2.7,12.6l3,2.5l0.9,0.7l1.8,2.3l-0.5,3.4l0.7,3.4l1.1,0.9l2.3-2.3h2.7l3.2,1.6   l2.5-0.9h4.1l3.7,1.6l2.7-0.5l0.5-3l3-0.7l1.4,1.4l0.5,3.2l1.8,1.4l1.5-11.6l20.7,3l28.2,4l16.6,1.9l31.4,3.5l11,1.5l1.1-15.4   L530.7,222.3z"/> \
    <path id="ID" data-info="<div>State: Idaho</div><div>Capital: Boise</div>" fill="#D3D3D3" stroke="#0057A7" d="M336.1,281c-22.6-4.3-14.1-2.8-21.1-4.4l4.4-17.5l4.3-17.7l1.4-4.2l2.5-5.9l-1.3-2.3l-2.5,0.1   l-0.8-1l0.5-1.1l0.3-3.1l4.5-5.5l1.8-0.5l1.1-1.1l0.6-3.2l0.9-0.7l3.9-5.8l3.9-4.3l0.2-3.8l-3.4-2.6l-1.3-4.4l0.4-9.7l3.7-16.5   l4.5-20.8l3.8-13.5l0.8-3.8l13,2.5l-4.2,21.5l2.9,7.7l-1.1,4.6l2,4.6l3.2,1.7l4.5,9.8l2.7,3.8l0.6,1.1l3.4,1.1l0.5,2.5l-6.9,16.8   l0.3,3.3l2.7,2.9l1.9,0.5l4.8-3.6l0.4-0.5l0.2,0.8l0.3,4.1l2.6,12.9l3.5,2.7l0.4,0.8l2.1,2.4l-0.8,2.8l0.7,3.8l1.9,0.9l2.1-1.6   l2.6-0.5l3.4,1.6l2.5-0.6l3.8-0.2l4,1.6l2.7-0.3l0.9-2.3l2.5-1.6l0.7,1.7l0.6,2.2l2.3,2.5l-3.8,24l-5.1,29l-4.2-0.3l-8.2-1.5   l-9.8-1.8l-12.2-2.4l-12.5-2.5l-8.5-1.8l-9.3-1.7L336.1,281z"/> \
    <path id="WA" data-info="<div>State: Washington</div><div>Capital: Olympia</div>" fill="#D3D3D3" stroke="#0057A7" d="M267.6,106.4l4.4,1.5l9.7,2.7l8.6,1.9l20,5.7l23,5.7l15.2,3.4l-1,3.9l-4.1,13.8l-4.5,20.8   l-3.2,16.1l-0.4,9.4l-13.2-3.9l-15.6-3.4l-13.7,0.6l-1.6-1.5l-5.3,1.9l-4-0.3l-2.7-1.8l-1.6,0.5l-4.2-0.2l-1.9-1.4l-4.8-1.7   l-1.4-0.2l-5-1.3l-1.8,1.5l-5.7-0.3l-4.8-3.8l0.2-0.8l0.1-7.9l-2.1-3.9l-4.1-0.7l-0.4-2.4l-2.5-0.6l-2.9-0.5l-1.8,1l-2.3-2.9   l0.3-2.9l2.7-0.3l1.6-4l-2.6-1.1l0.2-3.7l4.4-0.6l-2.7-2.7l-1.5-7.1l0.6-2.9v-7.9l-1.8-3.2l2.3-9.4l2.1,0.5l2.4,2.9l2.7,2.6   l3.2,1.9l4.5,2.1l3.1,0.6l2.9,1.5l3.4,1l2.3-0.2v-2.4l1.3-1.1l2.1-1.3l0.3,1.1l0.3,1.8l-2.3,0.5l-0.3,2.1l1.8,1.5l1.1,2.4l0.6,1.9   l1.5-0.2l0.2-1.3l-1-1.3l-0.5-3.2l0.8-1.8l-0.6-1.5V119l1.8-3.6l-1.1-2.6l-2.4-4.8l0.3-0.8L267.6,106.4z M258.1,112.3l2-0.2   l0.5,1.4l1.5-1.6h2.3l0.8,1.5l-1.5,1.7l0.6,0.8l-0.7,2l-1.4,0.4c0,0-0.9,0.1-0.9-0.2s1.5-2.6,1.5-2.6l-1.7-0.6l-0.3,1.5l-0.7,0.6   l-1.5-2.3L258.1,112.3z"/> \
    <path id="TX" data-info="<div>State: Texas</div><div>Capital: Austin</div>" fill="#D3D3D3" stroke="#0057A7" d="M531.1,433.4l22.7,1.1l31.1,1.1l-2.3,23.5l-0.3,18.2l0.1,2.1l4.3,3.8l1.7,0.8l1.8,0.3l0.7-1.3   l0.9,0.9l1.7,0.5l1.6-0.7l1.1,0.4l-0.3,3.4l4.3,1l2.7,0.8l4,0.5l2.2,1.8l3.2-1.6l2.8,0.4l2,2.8l1.1,0.3l-0.2,2l3.1,1.2l2.8-1.8   l1.5,0.4l2.4,0.2l0.4,1.9l4.6,2l2.7-0.2l2-4.1h0.3l1.1,1.9l4.4,1l3.3,1.2l3.3,0.8l2.1-0.8l0.8-2.5h3.7l1.9,0.8l3.1-1.6h0.7l0.4,1.1   h4.3l2.4-1.3l1.7,0.3l1.4,1.9l2.9,1.7l3.5,1.1l2.7,1.4l2.4,1.6l3.3-0.9l1.9,1l0.5,10.1l0.3,9.7l0.7,9.5l0.5,4l2.7,4.6l1.1,4.1   l3.9,6.3l0.5,2.9l0.5,1l-0.7,7.5l-2.7,4.4l1,2.9l-0.4,2.5l-0.8,7.3l-1.4,2.7l0.6,4.4l-5.7,1.6l-9.9,4.5l-1,1.9l-2.6,1.9l-2.1,1.5   l-1.3,0.8l-5.7,5.3l-2.7,2.1l-5.3,3.2l-5.7,2.4l-6.3,3.4l-1.8,1.5l-5.8,3.6l-3.4,0.6l-3.9,5.5l-4,0.3l-1,1.9l2.3,1.9l-1.5,5.5   l-1.3,4.5l-1.1,3.9l-0.8,4.5l0.8,2.4l1.8,7l1,6.1l1.8,2.7l-1,1.5l-3.1,1.9l-5.7-3.9l-5.5-1.1l-1.3,0.5l-3.2-0.6l-4.2-3.1l-5.2-1.1   l-7.6-3.4l-2.1-3.9l-1.3-6.5l-3.2-1.9l-0.6-2.3l0.6-0.6l0.3-3.4l-1.3-0.6l-0.6-1l1.3-4.4l-1.6-2.3l-3.2-1.3l-3.4-4.4l-3.6-6.6   l-4.2-2.6l0.2-1.9l-5.3-12.3l-0.8-4.2l-1.8-1.9l-0.2-1.5l-6-5.3l-2.6-3.1v-1.1l-2.6-2.1l-6.8-1.1l-7.4-0.6l-3.1-2.3l-4.5,1.8   l-3.6,1.5l-2.3,3.2l-1,3.7l-4.4,6.1l-2.4,2.4l-2.6-1l-1.8-1.1l-1.9-0.6l-3.9-2.3v-0.6l-1.8-1.9l-5.2-2.1l-7.4-7.8l-2.3-4.7v-8.1   l-3.2-6.5l-0.5-2.7l-1.6-1l-1.1-2.1l-5-2.1l-1.3-1.6l-7.1-7.9l-1.3-3.2l-4.7-2.3l-1.5-4.4l-2.6-2.9l-1.9-0.5l-0.6-4.7l8,0.7l29,2.7   l29,1.6l2.3-23.8l3.9-55.6l1.6-18.7l1.4,0 M631.2,667.3l-0.6-7.1l-2.7-7.2l-0.6-7l1.5-8.2l3.3-6.9l3.5-5.4l3.2-3.6l0.6,0.2   l-4.8,6.6l-4.4,6.5l-2,6.6l-0.3,5.2l0.9,6.1l2.6,7.2l0.5,5.2l0.2,1.5L631.2,667.3z"/> \
    <path id="CA" data-info="<div>State: California</div><div>Capital: Sacramento</div>" fill="#D3D3D3" stroke="#0057A7" d="M310.7,486.8l3.8-0.5l1.5-2l0.7-1.9l-3.2-0.1l-1.1-1.8l0.8-1.7l0-6.2l2.2-1.3l2.7-2.6l0.4-4.9   l1.6-3.5l1.9-2.1l3.3-1.7l1.3-0.7l0.8-1.5l-0.9-0.9l-1-1.5l-0.9-5.3l-2.9-5.2l0.1-2.8l-2.2-3.2l-15-23.2l-19.4-28.7l-22.4-33   l-12.7-19.5l1.8-7.2l6.8-25.9l8.1-31.4l-12.4-3.3l-13.5-3.4l-12.6-4.1l-7.5-2.1l-11.4-3l-7.1-2.4l-1.6,4.7l-0.2,7.4l-5.2,11.8   l-3.1,2.6l-0.3,1.1l-1.8,0.8l-1.5,4.2l-0.8,3.2l2.7,4.2l1.6,4.2l1.1,3.6l-0.3,6.5l-1.8,3.1l-0.6,5.8l-1,3.7l1.8,3.9l2.7,4.5   l2.3,4.8l1.3,4l-0.3,3.2l-0.3,0.5v2.1l5.7,6.3l-0.5,2.4l-0.6,2.3l-0.6,1.9l0.2,8.2l2.1,3.7l1.9,2.6l2.7,0.5l1,2.7l-1.1,3.6   l-2.1,1.6h-1.1l-0.8,3.9l0.5,2.9l3.2,4.4l1.6,5.3l1.5,4.7l1.3,3.1l3.4,5.8l1.5,2.6l0.5,2.9l1.6,1v2.4l-0.8,1.9l-1.8,7.1l-0.5,1.9   l2.4,2.7l4.2,0.5l4.5,1.8l3.9,2.1h2.9l2.9,3.1l2.6,4.8l1.1,2.3l3.9,2.1l4.8,0.8l1.5,2.1l0.6,3.2l-1.5,0.6l0.3,1l3.2,0.8l2.7,0.2   l2.9,4.7l3.9,4.2l0.8,2.3l2.6,4.2l0.3,3.2v9.4l0.5,1.8l10,1.5l19.7,2.7L310.7,486.8z M222.8,437l1.3,1.5l-0.2,1.3l-3.2-0.1   l-0.6-1.2l-0.6-1.5L222.8,437z M224.7,437l1.2-0.6l3.6,2.1l3.1,1.2l-0.9,0.6l-4.5-0.2l-1.6-1.6L224.7,437z M245.4,456.8l1.8,2.3   l0.8,1l1.5,0.6l0.6-1.5l-1-1.8l-2.7-2l-1.1,0.2V456.8z M244,465.5l1.8,3.2l1.2,1.9l-1.5,0.2l-1.3-1.2c0,0-0.7-1.5-0.7-1.9   s0-2.2,0-2.2L244,465.5z"/> \
    <path id="AZ" data-info="<div>State: Arizona</div><div>Capital: Phoenix</div>" fill="#D3D3D3" stroke="#0057A7" d="M311.7,487.5l-2.6,2.2l-0.3,1.5l0.5,1l18.9,10.7l12.1,7.6l14.7,8.6l16.8,10l12.3,2.4l25.1,2.7   l2.5-12.5l3.8-27.2l7-52.9l4.3-31l-24.6-3.7l-27.2-4.6l-33.4-6.3l-2.9,18.1l-0.5,0.5l-1.7,2.6l-2.5-0.1l-1.3-2.7l-2.7-0.3l-0.9-1.1   h-0.9l-0.9,0.6l-1.9,1l-0.1,7l-0.2,1.7l-0.6,12.6l-1.5,2.2l-0.6,3.3l2.7,4.9l1.3,5.8l0.8,1l1,0.6l-0.1,2.3l-1.6,1.4l-3.4,1.7   l-1.9,1.9l-1.5,3.7l-0.6,4.9l-2.9,2.7l-2.1,0.7l-0.1,5.8l-0.5,1.7l0.5,0.8l3.7,0.6l-0.6,2.7l-1.5,2.2L311.7,487.5z"/> \
    <path id="NV" data-info="<div>State: Nevada</div><div>Capital: Carson City</div>" fill="#D3D3D3" stroke="#0057A7" d="M314.7,277.6l21,4.5l9.7,1.9l9.3,1.8l6.6,1.6l-0.6,5.9l-3.5,17.3l-4.1,20l-1.9,9.7l-2.2,13.3   l-3.2,16.4l-3.5,15.7l-2,10.2l-2.5,16.8l-0.5,1.1l-1.1,2.5l-1.9-0.1l-1.1-2.7l-2.7-0.5l-1.4-1l-2,0.3l-0.9,0.7l-1.3,1.3l-0.4,7   l-0.5,1.7l-0.4,12.1l-1.3,1.7l-1.9-2.3l-14.5-22.7l-19.4-29L263.6,349l-12.4-18.6l1.6-6.6l7-25.9l7.9-31.3l33.6,8.1l13.7,3"/> \
    <path id="UT" data-info="<div>State: Utah</div><div>Capital: Salt Lake City</div>" fill="#D3D3D3" stroke="#0057A7" d="M427,409.3l-24.6-3.5l-26.6-4.9l-33.8-6l1.6-9.2l3.2-15.2L350,354l2.2-13.6l1.9-8.9l3.8-20.5   l3.5-17.5l1.1-5.6l12.7,2.3l12,2.1l10.3,1.8l8.3,1.4l3.7,0.5l-1.5,10.6l-2.3,13.2l7.8,0.9l16.4,1.8l8.2,0.9l-2.1,22l-3.2,22.6   l-3.8,27.8l-1.7,11.1L427,409.3z"/> \
    <path id="CO" data-info="<div>State: Colorado</div><div>Capital: Denver</div>" fill="#D3D3D3" stroke="#0057A7" d="M552.6,356.8l1.4-21.3l-32.1-3.1l-24.5-2.7l-37.3-4.1l-20.7-2.5l-2.6,22.2l-3.2,22.4l-3.8,28   l-1.5,11.1l-0.3,2.8l33.9,3.8l37.7,4.3l32,3.2l16.6,0.8"/> \
    <path id="NM" data-info="<div>State: New Mexico</div><div>Capital: Santa Fe</div>" fill="#D3D3D3" stroke="#0057A7" d="M456.7,531l-0.7-6.1l8.6,0.5l29.5,3.1l28.4,1.4l2-22.3l3.7-55.9l1.1-19.4l2,0.3l0-11.1l-32.2-2.4   l-36.9-4.4l-34.5-4.1l-4.2,30.8l-7,53.2l-3.8,26.9l-2,13.3l15.5,2l1.3-10l16.7,2.6L456.7,531z"/> \
    <path id="OR" data-info="<div>State: Oregon</div><div>Capital: Salem</div>" fill="#D3D3D3" stroke="#0057A7" d="M314.3,276.7l4.3-17.9l4.7-17.9l1.1-4.2l2.4-5.6l-0.6-1.2l-2.5,0l-1.3-1.7l0.5-1.5l0.5-3.2l4.5-5.5   l1.8-1.1l1.1-1.1l1.5-3.6l4-5.7l3.6-3.9l0.2-3.5l-3.3-2.5l-1.2-4.5l-13.2-3.7l-15.1-3.5l-15.4,0.1l-0.5-1.4l-5.5,2.1l-4.5-0.6   l-2.4-1.6l-1.3,0.7L273,184l-1.7-1.4l-5.3-2.1l-0.8,0.1l-4.3-1.5l-1.9,1.8l-6.2-0.3l-5.9-4.1l0.7-0.8l0.2-7.8l-2.3-3.9l-4.1-0.6   l-0.7-2.5l-2.4-0.5l-5.8,2.1l-2.3,6.5l-3.2,10l-3.2,6.5l-5,14.1l-6.5,13.6l-8.1,12.6l-1.9,2.9l-0.8,8.6l-1.3,6l2.7,3.5l6.7,2.3   l11.6,3.3l7.9,2.5l12.4,3.6l13.3,3.6l13.2,3.6"/> \
    <path id="ND" data-info="<div>State: North Dakota</div><div>Capital: Bismarck</div>" fill="#D3D3D3" stroke="#0057A7" d="M645.3,227.7l-0.4-7.5l-2-7.3l-1.8-13.6l-0.5-9.8l-2-3.1l-1.6-5.4v-10.3l0.7-3.9l-2.1-5.5   l-28.4-0.6l-18.6-0.6l-26.5-1.3l-24.9-1.9l-1.3,14.2l-1.4,15.1l-2.3,24.9l-0.5,11l56.8,3.8L645.3,227.7z"/> \
    <path id="SD" data-info="<div>State: South Dakota</div><div>Capital: Pierre</div>" fill="#D3D3D3" stroke="#0057A7" d="M646.8,303.2l-1-1.1l-1.5-3.6l1.8-3.7l1.1-5.6l-2.6-2.1l-0.3-2.7l0.6-3l2.2-0.8l0.3-5.7l-0.1-30.1   l-0.6-3l-4.1-3.6l-1-2v-1.9l1.9-1.3l1.5-1.9l0.2-2.7l-57.4-1.6l-56.2-3.9l-0.8,5.3l-1.6,15.9l-1.3,17.9l-1.6,24.6l16,1l19.6,1.1   l18,1.3l23.8,1.3l10.7-0.8l2.9,2.3l4.3,3l1,0.8l3.5-0.9l4-0.3l2.7-0.1l3.1,1.2l4.5,1.4l3.1,1.8l0.6,1.9l0.9,1.9l0.7-0.5   L646.8,303.2z"/> \
    <path id="NE" data-info="<div>State: Nebraska</div><div>Capital: Lincoln</div>" fill="#D3D3D3" stroke="#0057A7" d="M658.2,347l1.4,2.7l0.1,2.1l2.4,3.7l2.7,3.2h-5l-43.5-0.9l-40.8-0.9l-21.2-1l1.1-21.3l-33.4-2.7   l4.3-44l15.5,1L562,290l17.8,1.1l23.8,1.1l10.7-0.5l2.1,2.3l4.8,3l1.1,0.9l4.3-1.4l3.9-0.5l2.7-0.2l1.8,1.4l5,1.6l3,1.6l0.5,1.6   l0.9,2.1h1.8l0.8,0l1,5.2l2.7,8l1.2,4.6l2.1,3.8l0.5,4.9l1.4,4.3l0.5,6.5"/> \
    <path id="IA" data-info="<div>State: Iowa</div><div>Capital: Des Moines</div>" fill="#D3D3D3" stroke="#0057A7" d="M740.6,301.6l0.2,1.9l2.3,1.1l1.1,1.3l0.3,1.3l3.9,3.2l0.7,2.2l-0.8,2.9l-1.5,3.5l-0.8,2.7   l-2.2,1.6l-1.7,0.6l-5.5,1.5l-0.7,2.3l-0.8,2.3l0.6,1.4l1.7,1.7l0,3.7l-2.2,1.6l-0.5,1.5v2.5l-1.5,0.5l-1.7,1.4l-0.5,1.5l0.5,1.7   l-1.4,1.2l-2.3-2.7l-1.5-2.6l-8.3,0.8l-10.2,0.6l-25,0.7l-13,0.2l-9.4,0.2l-1.3,0.1l-1.7-4.5l-0.2-6.6l-1.6-4.1l-0.7-5.3l-2.3-3.7   l-0.9-4.8l-2.7-7.5l-1.1-5.4l-1.4-2.2l-1.6-2.7l1.8-4.3l1.4-5.7l-2.7-2.1l-0.5-2.7l0.9-2.5h1.7h11.5l49.6-0.7l19.9-0.7l1.9,2.7   l1.8,2.6l0.5,0.8l-1.8,2.7l0.5,4.2l2.5,3.9l3,1.8l2.4,0.2L740.6,301.6z"/> \
    <path id="MS" data-info="<div>State: Mississippi</div><div>Capital: Jackson</div>" fill="#D3D3D3" stroke="#0057A7" d="M798.6,567l-0.3,1.3h-5.2l-1.5-0.8l-2.1-0.3l-6.8,1.9l-1.8-0.8l-2.6,4.2l-1.1,0.8l-1.1-2.5   l-1.1-3.9l-3.4-3.2l1.1-7.5l-0.7-0.9l-1.8,0.2l-8.2,0.7l-24.2,0.7l-0.5-1.6l0.7-8l3.4-6.2l5.3-9.1l-0.9-2.1h1.1l0.7-3.2l-2.3-1.8   l0.2-1.8l-2.1-4.6l-0.3-5.3l1.4-2.7l-0.4-4.3l-1.4-3l1.4-1.4l-1.4-2.1l0.5-1.8l0.9-6.2l3-2.7l-0.7-2.1l3.7-5.3l2.7-0.9v-2.5   l-0.7-1.4l2.7-5.3l2.7-1.1l0.1-3.4l8.7-0.1l24.1-1.9l4.6-0.2l0,6.4l0.2,16.7l-0.8,31l-0.2,14.1l2.7,18.8L798.6,567z"/> \
    <path id="IN" data-info="<div>State: Indiana</div><div>Capital: Indianapolis</div>" fill="#D3D3D3" stroke="#0057A7" d="M792.4,400.9l0.1-2.9l0.5-4.5l2.3-2.9l1.8-3.9l2.6-4.2l-0.5-5.8l-1.8-2.7l-0.3-3.2l0.8-5.5l-0.5-7   l-1.3-16l-1.3-15.4l-1-11.7l3.1,0.9l1.5,1l1.1-0.3l2.1-1.9l2.8-1.6l5.1-0.2l22-2.3l5.6-0.5l1.5,16l4.3,36.8l0.6,5.8L843,371   l1.2,1.8l0.1,1.4l-2.5,1.6l-3.5,1.6l-3.2,0.6l-0.6,4.9l-4.6,3.3l-2.8,4l0.3,2.4l-0.6,1.5h-3.3l-1.6-1.6l-2.5,1.3l-2.7,1.5l0.2,3.1   l-1.2,0.3l-0.5-1l-2.2-1.5l-3.3,1.3l-1.6,3l-1.4-0.8l-1.5-1.6l-4.5,0.5l-5.6,1L792.4,400.9z"/> \
    <path id="IL" data-info="<div>State: Illinois</div><div>Capital: Springfield</div>" fill="#D3D3D3" stroke="#0057A7" d="M791.8,401.6V398l0.3-4.9l2.4-3.1l1.8-3.8l2.6-3.9l-0.4-5.3l-2-3.5l-0.1-3.3l0.7-5.3l-0.8-7.2   l-1.1-15.8l-1.3-15l-0.9-11.6l-0.3-0.9l-0.8-2.6l-1.3-3.7l-1.6-1.8l-1.5-2.6l-0.2-5.5l-9.9,1.3l-27.2,1.7l-8.7-0.4l0.2,2.4l2.3,0.7   l0.9,1.1l0.5,1.8l3.9,3.4l0.7,2.3l-0.7,3.4l-1.8,3.7l-0.7,2.5l-2.3,1.8l-1.8,0.7l-5.3,1.4l-0.7,1.8L736,330l0.7,1.4l1.8,1.6   l-0.2,4.1l-1.8,1.6l-0.7,1.6v2.7l-1.8,0.5l-1.6,1.1l-0.2,1.4l0.2,2.1l-1.7,1.3l-1,2.8l0.5,3.7l2.3,7.3l7.3,7.5l5.5,3.7l-0.2,4.3   l0.9,1.4l6.4,0.5l2.7,1.4l-0.7,3.7l-2.3,5.9l-0.7,3.2l2.3,3.9l6.4,5.3l4.6,0.7l2.1,5l2.1,3.2l-0.9,3l1.6,4.1l1.8,2.1l1.9-0.8   l0.7-2.2l2-1.4l3.2-1.1l3.1,1.2l2.9,1.1l0.8-0.2l-0.1-1.2l-1.1-2.8l0.4-2.4l2.3-1.6l2.4-1l1.2-0.4l-0.6-1.3l-0.8-2.2l1.2-1.3   L791.8,401.6z"/> \
    <path id="MN" data-info="<div>State: Minnesota</div><div>Capital: Saint Paul</div>" fill="#D3D3D3" stroke="#0057A7" d="M645.9,228.5l-0.5-8.5l-1.8-7.3l-1.8-13.5l-0.5-9.8l-1.8-3.4l-1.6-5v-10.3l0.7-3.9l-1.8-5.5l30.1,0   l0.3-8.2l0.6-0.2l2.3,0.5l1.9,0.8l0.8,5.5l1.5,6.1l1.6,1.6h4.8l0.3,1.5l6.3,0.3v2.1h4.8l0.3-1.3l1.1-1.1l2.3-0.6l1.3,1h2.9l3.9,2.6   l5.3,2.4l2.4,0.5l0.5-1l1.5-0.5l0.5,2.9l2.6,1.3l0.5-0.5l1.3,0.2v2.1l2.6,1h3.1l1.6-0.8l3.2-3.2l2.6-0.5l0.8,1.8l0.5,1.3h1l1-0.8   l8.9-0.3l1.8,3.1h0.6l0.7-1.1l4.4-0.4l-0.6,2.3l-3.9,1.8l-9.2,4.1l-4.8,2l-3.1,2.6l-2.4,3.6l-2.3,3.9l-1.8,0.8l-4.5,5l-1.3,0.2   l-3.8,2.9l-2.8,3.2l-0.2,3l0.2,7.8l-1.6,1.6L704,228l-1.8,5.7l2.5,3.6l0.5,2.5l-1.1,3l-0.2,3.7l0.5,7.1l3.4,4.1h3l2.5,2.3l3.2,1.4   l3.7,5l7.1,5l1.8,2.1l0.2,5.5l-20.6,0.7l-60.2,0.5l-0.3-35.7l-0.5-3l-4.1-3.4l-1.1-1.8v-1.6l2.1-1.6l1.4-1.4L645.9,228.5z"/> \
    <path id="WI" data-info="<div>State: Wisconsin</div><div>Capital: Madison</div>" fill="#D3D3D3" stroke="#0057A7" d="M786.9,297.2l0.4-3l-1.6-4.5l-0.6-6.1l-1.1-2.4l1-3.1l0.8-2.9l1.5-2.6l-0.6-3.4l-0.6-3.6l0.5-1.8   l1.9-2.4l0.2-2.7l-0.8-1.3l0.6-2.6l0.5-3.2l2.7-5.7l2.9-6.8l0.2-2.3l-0.3-1l-0.8,0.5l-4.2,6.3l-2.7,4l-1.9,1.8l-0.8,2.3l-1.5,0.8   l-1.1,1.9l-1.5-0.3l-0.2-1.8l1.3-2.4l2.1-4.7l1.8-1.6l1.1-2.3l-1.6-0.9l-1.4-1.4l-1.6-10.3l-3.7-1.1l-1.4-2.3l-12.6-2.7l-2.5-1.1   l-8.2-2.3l-8.2-1.1l-4.2-5.4l-0.5,1.3l-1.1-0.2l-0.6-1.1l-2.7-0.8l-1.1,0.2l-1.8,1l-1-0.6l0.6-1.9l1.9-3.1l1.1-1.1l-1.9-1.5   l-2.1,0.8l-2.9,1.9l-7.4,3.2l-2.9,0.6l-2.9-0.5l-1-0.9l-2.1,2.8l-0.2,2.7v8.5l-1.1,1.6l-5.3,3.9l-2.3,5.9l0.5,0.2l2.5,2.1l0.7,3.2   l-1.8,3.2v3.9l0.5,6.6l3,3h3.4l1.8,3.2l3.4,0.5l3.9,5.7l7.1,4.1l2.1,2.7l0.9,7.4l0.7,3.3l2.3,1.6l0.2,1.4l-2.1,3.4l0.2,3.2l2.5,3.9   l2.5,1.1l3,0.5l1.3,1.4l9.2,0l26.1-1.5L786.9,297.2z"/> \
    <path id="MO" data-info="<div>State: Missouri</div><div>Capital: Jefferson City</div>" fill="#D3D3D3" stroke="#0057A7" d="M729.8,349.5l-2.5-3.1l-1.1-2.3l-7.8,0.7l-9.8,0.5l-25.4,0.9l-13.5,0.2l-7.9,0.1l-2.3,0.1l1.3,2.5   l-0.2,2.3l2.5,3.9l3.1,4.1l3.1,2.7l2.3,0.2l1.4,0.9v3l-1.8,1.6l-0.5,2.3l2.1,3.4l2.5,3l2.5,1.8l1.4,11.7l-0.7,35.3l0.2,4.7l0.5,5.4   l23.4-0.1l23.2-0.7l20.8-0.8l11.7-0.2l2.2,3.4l-0.7,3.3l-3.1,2.4l-0.6,1.8l5.4,0.5l3.9-0.7l1.7-5.5l0.7-5.9l2.3-2l1.7-1.5l2.1-1   l0.1-2.9l0.6-1.7l-1-1.7l-2.7,0.1l-2.2-2.6l-1.4-4.2l0.8-2.5l-1.9-3.4l-1.8-4.6l-4.8-0.8l-7-5.6l-1.7-4.1l0.8-3.2l2.1-6.1l0.5-2.9   l-1.9-1l-6.9-0.8l-1-1.7l-0.1-4.2l-5.5-3.4l-7-7.8l-2.3-7.3l-0.2-4.2L729.8,349.5z"/> \
    <path id="AR" data-info="<div>State: Arkansas</div><div>Capital: Little Rock</div>" fill="#D3D3D3" stroke="#0057A7" d="M765,445l-3.8,0.9l-6.2-0.5l0.7-3l3.2-2.7l0.5-2.3l-1.8-3l-11,0.5l-20.8,0.9l-23.3,0.7L679,437   l1.6,6.9v8.2l1.4,11l0.2,37.8l2.3,1.9l3-1.4l2.7,1.1l0.4,10.3l22.9-0.1l18.9-0.8l10.1-0.2l1.1-2.1l-0.3-3.5l-1.8-3l1.6-1.5   l-1.6-2.5l0.7-2.5l1.4-5.6l2.5-2.1l-0.7-2.3l3.7-5.4l2.7-1.4l-0.1-1.5l-0.3-1.8l2.9-5.6l2.4-1.3l0.4-3.4l1.8-1.2l0.9-4.2l-1.3-4   l4-2.4l0.6-2l1.2-4.3L765,445z"/> \
    <path id="OK" data-info="<div>State: Oklahoma</div><div>Capital: Oklahoma City</div>" fill="#D3D3D3" stroke="#0057A7" d="M549.3,422.6l-10.7-0.5l-6.4-0.5l0.3,0.2l-0.7,10.4l22,1.4l32.1,1.3l-2.3,24.4l-0.5,17.8l0.2,1.6   l4.3,3.7l2.1,1.1l0.7-0.2l0.7-2.1l1.4,1.8h2.1v-1.4l2.7,1.4l-0.5,3.9l4.1,0.2l2.5,1.1l4.1,0.7l2.5,1.8l2.3-2.1l3.4,0.7l2.5,3.4h0.9   v2.3l2.3,0.7l2.3-2.3l1.8,0.7h2.5l0.9,2.5l4.8,1.8l1.4-0.7l1.8-4.1h1.1l1.1,2.1l4.1,0.7l3.7,1.4l3,0.9l1.8-0.9l0.7-2.5h4.3l2.1,0.9   l2.7-2.1h1.1l0.7,1.6h4.1l1.6-2.1l1.8,0.5l2.1,2.5l3.2,1.8l3.2,0.9l1.9,1.1l-0.4-37.2l-1.4-11l-0.2-8.9l-1.4-6.5l-0.8-7.2l-0.1-3.8   l-12.1,0.3l-46.4-0.5l-45-2.1L549.3,422.6z"/> \
    <path id="KS" data-info="<div>State: Kansas</div><div>Capital: Topeka</div>" fill="#D3D3D3" stroke="#0057A7" d="M677.4,425.1l-12.6,0.2l-46.1-0.5l-44.6-2.1l-24.6-1.3l4.1-64.7l21.8,0.8l40.5,1.4l44.1,0.5h5.1   l3.2,3.2l2.8,0.2l0.9,1.1v2l-1.8,1.6l-0.5,2.6l2.2,3.6l2.5,3.1l2.5,2l1.1,11.2L677.4,425.1z"/> \
    <path id="LA" data-info="<div>State: Louisiana</div><div>Capital: Baton Rouge</div>" fill="#D3D3D3" stroke="#0057A7" d="M776.2,573l-1-2.6l-1.1-3.1l-3.3-3.5l0.9-6.8l-0.1-1.1l-1.3,0.3l-8.2,0.9l-25,0.5l-0.7-2.4l0.9-8.5   l3.3-5.9l5-8.7l-0.6-2.4l1.3-0.7l0.5-2l-2.3-2.1l-0.1-1.9l-1.8-4.3l-0.5-5.9l-9.7,0.1l-19.2,0.9l-22.2,0l0,9.6l0.7,9.4l0.7,3.9   l2.5,4.1l0.9,5l4.3,5.5l0.2,3.2l0.7,0.7l-0.7,8.5l-3,5l1.6,2.1l-0.7,2.5l-0.7,7.3l-1.4,3.2l0.1,3.6l4.7-1.5l8.1-0.3l10.3,3.6   l6.5,1.1l3.7-1.5l3.2,1.1l3.2,1l0.8-2.1l-3.2-1.1l-2.6,0.5l-2.7-1.6c0,0,0.2-1.3,0.8-1.5c0.6-0.2,3.1-1,3.1-1l1.8,1.5l1.8-1   l3.2,0.6l1.5,2.4l0.3,2.3l4.5,0.3l1.8,1.8l-0.8,1.6l-1.3,0.8l1.6,1.6l8.4,3.6l3.6-1.3l1-2.4l2.6-0.6l1.8-1.5l1.3,1l0.8,2.9   l-2.3,0.8l0.6,0.6l3.4-1.3l2.3-3.4l0.8-0.5l-2.1-0.3l0.8-1.6l-0.2-1.5l2.1-0.5l1.1-1.3l0.6,0.8c0,0-0.2,3.1,0.6,3.1   c0.8,0,4.2,0.6,4.2,0.6l4,1.9l1,1.5h2.9l1.1,1l2.3-3.1v-1.5h-1.3l-3.4-2.7l-5.8-0.8l-3.2-2.3l1.1-2.7l2.3,0.3l0.2-0.6l-1.8-1v-0.5   h3.2l1.8-3.1l-1.3-1.9l-0.3-2.7l-1.5,0.2l-1.9,2.1l-0.6,2.6l-3.1-0.6l-1-1.8l1.8-1.9l2-1.8L776.2,573z"/> \
    <path id="VA" data-info="<div>State: Virginia</div><div>Capital: Richmond</div>" fill="#D3D3D3" stroke="#0057A7" d="M1002.9,369.2l-0.1-1.9l6.5-2.5l-0.8,3.2l-2.9,3.8l-0.4,4.6l0.5,3.4l-1.8,5l-2.2,1.9l-1.5-4.6   l0.4-5.4l1.6-4.2L1002.9,369.2z M1005.2,397.5L947,410.1l-37.4,5.3l-6.7-0.4l-2.6,1.9l-7.3,0.2l-8.4,1l-8.9,1l8.5-4.9l0-2.1   l1.5-2.1l10.6-11.5l3.9,4.5l3.8,1l2.5-1.1l2.2-1.3l2.5,1.3l3.9-1.4l1.9-4.6l2.6,0.5l2.9-2.1l1.8,0.5l2.8-3.7l0.3-2.1l-1-1.3l1-1.9   l5.3-12.3l0.6-5.7l1.2-0.5l2.2,2.4l3.9-0.3l1.9-7.6l2.8-0.6l1-2.7l2.6-2.3l1.3-2.3l1.5-3.4l0.1-5.1l9.8,3.8   c0.7,0.3,0.7-4.8,0.7-4.8l4.1,1.4l-0.5,2.6l8.2,2.9l1.3,1.8l-0.9,3.7l-1.3,1.3l-0.5,1.7l0.5,2.4l2,1.3l3.9,1.4l2.9,1l4.9,0.9   l2.2,2.1l3.2,0.4l0.9,1.2l-0.4,4.7l1.4,1.1l-0.5,1.9l1.2,0.8l-0.2,1.4l-2.7-0.1l0.1,1.6l2.3,1.5l0.1,1.4l1.8,1.8l0.5,2.5l-2.6,1.4   l1.6,1.5l5.8-1.7L1005.2,397.5z"/> \
    <g id="DC"> \
        <path id="path58" fill="#D3D3D3" stroke="#0057A7" d="M975.8,353.8l-1.1-1.6l-1-0.8l1.1-1.6l2.2,1.5L975.8,353.8z"/> \
        <circle id="circle60" data-info="<div>Washington DC</div>" fill="#D3D3D3" stroke="#0057A7" stroke="#FFFFFF" stroke-width="1.5" cx="975.3" cy="351.8" r="5"/> \
    </g> \
     \
     \
     \
     \
     \
</g> \
<path id="path67" fill="none" stroke="#A9A9A9" stroke-width="2" d="M385,593v55l36,45 M174,525h144l67,68h86l53,54v46"/> \
 \
</svg>';