var osName = checkOS();
var cacheGetURL = 'http://localhost:3128/cache/get';
var cacheSetURL = 'http://localhost:3128/cache/set';

function checkOS() {
    var osName = 'Unknown';
    if (window.navigator.userAgent.indexOf('Windows NT 10.0') != -1) osName = 'Windows 10';
    if (window.navigator.userAgent.indexOf('Windows NT 6.2') != -1) osName = 'Windows 8';
    if (window.navigator.userAgent.indexOf('Windows NT 6.1') != -1) osName = 'Windows 7';
    if (window.navigator.userAgent.indexOf('Windows NT 6.0') != -1) osName = 'Windows Vista';
    if (window.navigator.userAgent.indexOf('Windows NT 5.1') != -1) osName = 'Windows XP';
    if (window.navigator.userAgent.indexOf('Windows NT 5.0') != -1) osName = 'Windows 2000';
    if (window.navigator.userAgent.indexOf('Mac') != -1) osName = 'Mac/iOS';
    if (window.navigator.userAgent.indexOf('X11') != -1) osName = 'UNIX';
    if (window.navigator.userAgent.indexOf('Linux') != -1) osName = 'Linux';
    debug.log('OS Name: ' + osName);
    if (osName.indexOf('Windows') != -1) {
        return 'windows';
    } else {
        return 'other';
    }
}

function getOfflineData(storageKey) {
    debug.log('WidgetOfflineStorage: Get Offline Data in localStorage...');
    debug.log('WidgetOfflineStorage: Storage Key: ' + storageKey);

    var offlineData = null;
    var checkKey = checkStorageKey(storageKey);
    debug.log('WidgetOfflineStorage: getOfflineDataMethod: checkKey equals ', checkKey);
    if (osName == 'windows') {
        var workData = makeCORSRequest('POST', cacheGetURL, {
            cacheKey: storageKey
        });
        if (workData) {
            var jsonData = JSON.parse(workData);
            if (jsonData['cacheData']) {
                offlineData = jsonData['cacheData'];
                if (!checkKey) {
                    debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == false');
                    return LZString.decompressFromUTF16(offlineData);
                } else if (checkKey) {
                    debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == true');
                    return offlineData;
                } else {
                    return null;
                }
            }
        }
        return null;
    } else {
        try {
            offlineData = localStorage.getItem(storageKey);
            if (offlineData) {
                if (!checkKey) {
                    debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == false');
                    return LZString.decompressFromUTF16(offlineData);
                } else if (checkKey) {
                    debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == true');
                    return offlineData;
                } else {
                    return null;
                }
            }
        } catch (e) {
            debug.log('WidgetOfflineStorage: Local Storage getItem for Key: ' + storageKey + ' Failed: ' + e);
            return null;
        }
    }
    return null;
}


function storeOfflineData(storageKey, offlineData, keyPrefix) {
    var dataToStore;
    debug.log('WidgetOfflineStorage: Store Offline Data in localStorage...');
    debug.log('WidgetOfflineStorage: Storage Key: ' + storageKey);
    var tmpTest = typeof offlineData;
    debug.log('WidgetOfflineStorage: Type of Data to Store: ' + tmpTest);
    debug.log('WidgetOfflineStorage: Size of Offline Data Before Compression: ' + offlineData.length + ' bytes');
    var compressed = LZString.compressToUTF16(offlineData);
    debug.log('WidgetOfflineStorage: Compressed Size of Offline Data Before Storage: ' + compressed.length + ' bytes');
    var checkKey = checkStorageKey(storageKey);
    debug.log('WidgetOfflineStorage: storeOfflineDataMethod: checkKey equals ', checkKey);
    if (offlineData && !checkKey) {
        debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == false');
        dataToStore = compressed;
    } else {
        debug.log('WidgetOfflineStorage: Has Offline Data and checkKey == true');
        dataToStore = offlineData;
    }
    if (osName == 'windows') {
        if (!keyPrefix) {
            keyPrefix = 'EMPTYKEY';
        }
        offlineData = makeCORSRequest('POST', cacheSetURL, {
            cacheKey: storageKey,
            cacheData: dataToStore,
            cachePrefix: keyPrefix
        });
    } else {
        try {
            localStorage.setItem(storageKey, dataToStore);
            removeOtherOfflineStorageKeys(keyPrefix, storageKey);
        } catch (e) {
            debug.log('WidgetOfflineStorage: Local Storage setItem Method Failed: ' + e);
        }
    }
}

function logGet(item) {
    debug.log('WidgetOfflineStorage: Log Operation after Get from IndexedDB');
    debug.log(item);
}

function removeOtherOfflineStorageKeys(keyPrefix, keyToSave) {
    keyPrefix = keyPrefix || null;
    idbRemovalArray = [];
    debug.log('WidgetOfflineStorage: Entering Key Removal Routine with Prefix: ' + keyPrefix);
    if (keyPrefix) {
        debug.log('WidgetOfflineStorage: Removing Excess Keys from Local Storage with Prefix: ' + keyPrefix);
        var arr = [];
        for (var i = 0; i < localStorage.length; i++) {
            if (localStorage.key(i).substring(0, keyPrefix.length) == keyPrefix) {
                arr.push(localStorage.key(i));
            }
        }
        for (var z = 0; z < arr.length; z++) {
            if (arr[z] == keyToSave) {
                debug.log('WidgetOfflineStorage: Skipping Removal of Local Storage Currently Active Key: ' + arr[z]);
                continue;
            }
            if (arr[z] != keyToSave) {
                debug.log('WidgetOfflineStorage: Removing Excess Local Storage Key: ' + arr[z]);
                localStorage.removeItem(arr[z]);
            }
        }

    }
}

function checkStorageKey(keyToCheck) {
    var nonCompressionIndexes = ['Expiration', 'Rotation'];
    var arrayLength = nonCompressionIndexes.length;
    debug.log('WidgetOfflineStorage: Checking keys to see if data should be compressed for key: ', keyToCheck);
    for (var i = 0; i < arrayLength; i++) {
        debug.log('WidgetOfflineStorage: Checking if storage key: ' + keyToCheck + ' contains non-compression index: ', nonCompressionIndexes[i]);
        var checkedKey = keyToCheck.indexOf(nonCompressionIndexes[i]);
        debug.log('WidgetOfflineStorage: indexOf function returns: ', checkedKey);
        if (checkedKey > -1) {
            debug.log('WidgetOfflineStorage: Non-compression key is contained within, returning true...');
            return true;
        }
    }
    debug.log('WidgetOfflineStorage: Non-compression keys are not contained within the storage key, returning false...');
    return false;
}

function createCORSRequest(method, url) {
    var xhr = new XMLHttpRequest();
    var tS = new Date().getTime();
    var tSURL = url + '?tS=' + tS;
    if ("withCredentials" in xhr) {
        // Check if the XMLHttpRequest object has a "withCredentials" property.
        // "withCredentials" only exists on XMLHTTPRequest2 objects.
        xhr.open(method, tSURL, false);
    } else if (typeof XDomainRequest != "undefined") {
        // Otherwise, check if XDomainRequest.
        // XDomainRequest only exists in IE, and is IE's way of making CORS requests.
        xhr = new XDomainRequest();
        xhr.open(method, tSURL, false);
    } else {
        // Otherwise, CORS is not supported by the browser.
        xhr = null;

    }
    return xhr;
}

// Make the actual CORS request.
function makeCORSRequest(method, url, data) {
    var xhr = createCORSRequest(method, url);
    if (!xhr) {
        debug.log('WidgetOfflineStorage: CORS not supported!');
        return null;
    }

    if (method == 'POST') {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        debug.log('WidgetOfflineStorage: Data sent for CORS request: ', data);
        xhr.send(serializeStorageData(data));
    } else {
        xhr.send();
    }

    if (xhr.status === 200) {
        var data = xhr.responseText;
        if (data) {
            return data;
        } else {
            return null;
        }
        /*try {
            returnData = JSON.parse(data);
        } catch (e) {
            returnData = null;
        }*/
        //return returnData;
    }

}

function serializeStorageData(obj) {
    var str = [];
    for (var p in obj)
        if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        }
    return str.join("&");
}