var calcYear = 2019;
var calcMake = '';
var calcModel = '';

$(document).ready(function () {
    $('#calc-select-year').on('change', function () {
        disableAddVehicle();
        $('#calc-add-vehicle').html('<i class="fas fa-spinner fa-spin"></i>');
        calcYear = $('#calc-select-year').val();
        calcMake = '';
        calcModel = '';
        var $el = $('#calc-select-make');
        $el.val('');
        $el.prop('disabled', true);
        var $el = $('#calc-select-model');
        $el.val('');
        $el.prop('disabled', true);
        ajaxRequest();
    });

    $('#calc-select-make').on('change', function () {
        disableAddVehicle();
        $('#calc-add-vehicle').html('<i class="fas fa-spinner fa-spin"></i>');
        calcMake = $('#calc-select-make').val();
        calcModel = '';
        var $el = $('#calc-select-model');
        $el.val('');
        $el.prop('disabled', true);
        ajaxRequest();
    });

    $('#calc-select-model').on('change', function () {
        disableAddVehicle();
        $('#calc-add-vehicle').html('<i class="fas fa-spinner fa-spin"></i>');
        calcModel = $('#calc-select-model').val();
        disableAddVehicle();
        ajaxRequest();
    });

    $('#calc-add-vehicle').on('click', function (e) {
        var vehicleData = JSON.parse($('#calc-add-vehicle').attr('data-vehicle'));
        var vehicleID = $('#calc-select-model').val();
        var vehicleItem = $('#template-vehicle-list-item').html();
        vehicleItem = vehicleItem.replace(/\{\{vehicleID\}\}/g, vehicleID);
        vehicleItem = vehicleItem.replace(/\{\{vehicleImage\}\}/g, vehicleData.modelImg);
        vehicleItem = vehicleItem.replace(/\{\{vehicleDescription\}\}/g, vehicleData.modelName);
        vehicleItem = vehicleItem.replace(/\{\{vehiclePrice\}\}/g, vehicleData.modelPrice);
        vehicleItem = vehicleItem.replace(/\{\{vehicleMPG\}\}/g, vehicleData.modelMPG);
        vehicleItem = vehicleItem.replace(/\{\{vehicleFuelType\}\}/g, vehicleData.modelFuelType);
        vehicleItem = vehicleItem.replace(/\{\{modelName\}\}/g, vehicleData.modelNameClean);
        vehicleItem = vehicleItem.replace(/\{\{cityMPG\}\}/g, vehicleData.modelCityMPG);
        vehicleItem = vehicleItem.replace(/\{\{highwayMPG\}\}/g, vehicleData.modelHighwayMPG);
        $('#vehicle-compare-list-group').append(vehicleItem);
        disableAddVehicle();
        checkCalculateReport();
        initializeVehiclePriceKeyboard();
    });

    $('body').on('click', '.remove-comparison-vehicle', function () {
        var removalID = $(this).attr('data-removeitem');
        $('#' + removalID).remove();
    });

    $('#calc-begin-graphing').on('click', function () {
        disableBeginGraphing();
        $('#calc-begin-graphing').html('<i class="fas fa-spinner fa-spin"></i>');
        calculateReport();
    });

    $('#calc-clear-comparison').on('click', function () {
        var listModels = $('#vehicle-compare-list-group li');
        listModels.each(function (idx, li) {
            if ($(li).attr('id') != '40769_list_item') {
                $(li).remove();
            }
        });
        checkCalculateReport();
    });

    $('#calc-reset-vehicle-usage').on('click', function () {
        $('#usage-driving-distance').val('34');
        $('#usage-days-per-week').val('5');
        $('#usage-weeks-per-year').val('49');
        $('#usage-percent-highway').val('45');
        $('#usage-annual-mileage-other').val('3596');
        $('#usage-percent-highway-other').val('45');
        $('#calc-resident-state').val('TN');
        checkCalculateReport();
    });

    $('input[name=chart-options-usage]').on('change', function () {
        $('.chart-options-usage').hide();
        $('input[name=chart-options-usage]').parent().removeClass('btn-primary active');
        $(this).parent().addClass('btn-primary active');
        $('#' + $(this).val()).show();
    });

    $('input[name=chart-options-costs]').on('change', function () {
        $('.chart-options-costs').hide();
        $('input[name=chart-options-costs]').parent().removeClass('btn-primary active');
        $(this).parent().addClass('btn-primary active');
        $('#' + $(this).val()).show();
    });

    ajaxRequest();
    calcTotalDistance();
    disableButtonBar();
    checkCalculateReport();
    initializeVehiclePriceKeyboard();

    $('.keyboard-validate-miles').keyboard({
        layout: 'custom',
        customLayout: {
            'normal': ['{clear} {empty} {b}', '7 8 9', '4 5 6', '1 2 3', '0 {a} {c}']
        },
        restrictInput: true, // Prevent keys not in the displayed keyboard from being typed in
        preventPaste: true, // prevent ctrl-v and right click
        autoAccept: true,
        css: {
            popup: 'numeric-only-keyboard'
        },
        validate: function (keyboard, value, isClosing) {
            if (value >= 0 && value < 100000 && value != '') {
                return true;
            }
            return false;
        }
    }).addTyping();

    $('.keyboard-validate-percent').keyboard({
        layout: 'custom',
        customLayout: {
            'normal': ['{clear} {empty} {b}', '7 8 9', '4 5 6', '1 2 3', '0 {a} {c}']
        },
        restrictInput: true, // Prevent keys not in the displayed keyboard from being typed in
        preventPaste: true, // prevent ctrl-v and right click
        autoAccept: true,
        css: {
            popup: 'numeric-only-keyboard'
        },
        validate: function (keyboard, value, isClosing) {
            if (value >= 0 && value < 101 && value != '') {
                return true;
            }
            return false;
        }
    }).addTyping();
});

function initializeVehiclePriceKeyboard() {
    $('.msrp_input').keyboard({
        layout: 'custom',
        customLayout: {
            'normal': ['{clear} {empty} {b}', '7 8 9', '4 5 6', '1 2 3', '0 {a} {c}']
        },
        restrictInput: true, // Prevent keys not in the displayed keyboard from being typed in
        preventPaste: true, // prevent ctrl-v and right click
        autoAccept: true,
        css: {
            popup: 'numeric-only-keyboard'
        },
        validate: function (keyboard, value, isClosing) {
            if (value >= 0 && value < 300000 && value != '') {
                return true;
            }
            return false;
        }
    }).addTyping();
}

function ajaxRequest() {
    $.ajax({
        url: 'https://feeds.abnetwork.com/cache/api/energy/test/request',
        data: {
            year: calcYear,
            make: calcMake,
            model: calcModel
        },
        type: 'POST',
        success: function (result, status, xhr) {
            $('#calc-add-vehicle').html('Add Vehicle');
            if (result.makes) {
                var $el = $('#calc-select-make');
                $el.empty();
                $el.append($("<option></option>")
                    .attr('value', '').text('Make'));
                $.each(result.makes, function (key, value) {
                    $el.append($("<option></option>")
                        .attr('value', value.value).text(value.label));
                });
                $el.prop('disabled', false);
            }
            if (result.models) {
                var $el = $('#calc-select-model');
                $el.empty();
                $el.append($("<option></option>")
                    .attr('value', '').text('Model'));
                $.each(result.models, function (key, value) {
                    $el.append($("<option></option>")
                        .attr('value', value.value).text(value.label));
                });
                $el.prop('disabled', false);
            }
            if (result.add) {
                $('#calc-add-vehicle').attr('data-vehicle', JSON.stringify(result.add));
                var listModels = $('#vehicle-compare-list-group li');
                if (listModels.length > 4) {
                    disableAddVehicle();
                } else {
                    enableAddVehicle();
                }
            }
            console.log(result);
        }
    });
}

function enableAddVehicle() {
    var $el = $('#calc-add-vehicle');
    $el.prop('disabled', false);
    $el.removeClass('btn-disabled');
    $el.addClass('btn-success');
}

function disableAddVehicle() {
    var $el = $('#calc-add-vehicle');
    $el.prop('disabled', true);
    $el.removeClass('btn-success');
    $el.addClass('btn-disabled');
}

function enableBeginGraphing() {
    var $el = $('#calc-begin-graphing');
    $el.prop('disabled', false);
    $el.removeClass('btn-disabled');
    $el.addClass('btn-success');
    $el.html('<i class="fas fa-calculator"></i> Calculate Report');
}

function disableBeginGraphing() {
    var $el = $('#calc-begin-graphing');
    $el.prop('disabled', true);
    $el.removeClass('btn-success');
    $el.addClass('btn-disabled');
}

function calcTotalDistance() {
    var drivingDistance = $('#usage-driving-distance').val();
    var daysPerWeek = $('#usage-days-per-week').val();
    var weeksPerYear = $('#usage-weeks-per-year').val();
    var percentHighway = $('#usage-percent-highway').val();
    var annualMileageOther = $('#usage-annual-mileage-other').val();
    var percentHighwayOther = $('#usage-percent-highway-other').val();

    var normalMileage = drivingDistance * daysPerWeek * weeksPerYear;
    var cityMileage = normalMileage * ((100 - percentHighway) / 100);
    var highwayMileage = normalMileage * (percentHighway / 100);

    var additionalCity = annualMileageOther * ((100 - percentHighwayOther) / 100);
    var additionalHighway = annualMileageOther * (percentHighwayOther / 100);

    var totalMileage = parseInt(normalMileage, 10) + parseInt(annualMileageOther, 10);
    var totalCity = parseInt(cityMileage + additionalCity, 10);
    var totalHighway = parseInt(highwayMileage + additionalHighway, 10);

    $('#calc-totals-annual').text(numberWithCommas(totalMileage));
    $("#calc-totals-annual").fadeOut(100).fadeIn(100);
    $('#calc-totals-city').text(numberWithCommas(totalCity));
    $("#calc-totals-city").fadeOut(100).fadeIn(100);
    $('#calc-totals-highway').text(numberWithCommas(totalHighway));
    $("#calc-totals-highway").fadeOut(100).fadeIn(100);
}

function calculateReport() {
    var drivingDistance = $('#usage-driving-distance').val();
    var daysPerWeek = $('#usage-days-per-week').val();
    var weeksPerYear = $('#usage-weeks-per-year').val();
    var percentHighway = $('#usage-percent-highway').val();
    var annualMileageOther = $('#usage-annual-mileage-other').val();
    var percentHighwayOther = $('#usage-percent-highway-other').val();

    var normalMileage = drivingDistance * daysPerWeek * weeksPerYear;
    var cityMileage = normalMileage * ((100 - percentHighway) / 100);
    var highwayMileage = normalMileage * (percentHighway / 100);

    var additionalCity = annualMileageOther * ((100 - percentHighwayOther) / 100);
    var additionalHighway = annualMileageOther * (percentHighwayOther / 100);

    var totalMileage = parseInt(normalMileage, 10) + parseInt(annualMileageOther, 10);
    var totalCity = parseInt(cityMileage + additionalCity, 10);
    var totalHighway = parseInt(highwayMileage + additionalHighway, 10);

    var residentState = $('#calc-resident-state').val();

    var listModels = $('#vehicle-compare-list-group li');
    var vehicles = [];
    listModels.each(function (idx, li) {
        var vehicle = {
            name: $(li).attr('data-model'),
            price: parseInt($(li).find('.msrp_input').val().replace(/\,/g, ''), 10),
            citympg: parseInt($(li).attr('data-citympg'), 10),
            highwaympg: parseInt($(li).attr('data-highwaympg'), 10),
            fueltype: $(li).attr('data-fueltype').toLowerCase(),
            citymiles: totalCity,
            highwaymiles: totalHighway,
            totalmiles: totalMileage
        }
        if (vehicle.fueltype == 'gasoline') {
            vehicle.citygallons = Math.floor(totalCity / vehicle.citympg);
        } else {
            vehicle.citygallons = 0;
        }
        if (vehicle.fueltype == 'gasoline' || vehicle.fueltype == 'hybrid') {
            vehicle.highwaygallons = Math.floor(totalHighway / vehicle.highwaympg);
        } else {
            vehicle.highwaygallons = 0;
        }
        // Carbon emissions 8.78 kgs per gallon
        vehicle.totalgallons = vehicle.citygallons + vehicle.highwaygallons;
        vehicle.fuelemissions = Math.floor(vehicle.totalgallons * 8.78);
        vehicle.fuelcost = vehicle.totalgallons * stateMapData[residentState].fuelPrice;
        // 0.4483 kgs per kWh
        if (vehicle.fueltype == 'electric') {
            vehicle.cityelectric = Math.floor(totalCity / 100) * vehicle.citympg;
        } else {
            vehicle.cityelectric = 0;
        }
        if (vehicle.fueltype == 'electric' || vehicle.fueltype == 'hybrid') {
            vehicle.highwayelectric = Math.floor(totalHighway / 100) * vehicle.highwaympg;
        } else {
            vehicle.highwayelectric = 0;
        }
        vehicle.totalelectric = vehicle.cityelectric + vehicle.highwayelectric;
        vehicle.electricemissions = Math.floor(vehicle.totalelectric * .4483);
        vehicle.electriccost = (vehicle.totalelectric * stateMapData[residentState].electricPrice) / 100;

        if (vehicle.fueltype == 'gasoline') {
            vehicle.maintenance = (totalMileage * .0538) + 1616;
        }
        if (vehicle.fueltype == 'hybrid') {
            var tempCityMaint = totalCity * .041;
            var tempHighMaint = totalHighway * .0538;
            vehicle.maintenance = tempCityMaint + tempHighMaint + 1616;
        }
        if (vehicle.fueltype == 'electric') {
            vehicle.maintenance = (totalMileage * .041) + 1616;
        }
        vehicle.loanpayments = loanPerYearCost(vehicle.price)
        vehicles.push(vehicle);
    });
    console.log(vehicles);
    console.log(stateMapData[residentState]);
    $('#empty-chart-calculator').hide();
    $('#has-chart-calculator').show();
    $('.build-chart').show();
    chartVehicleAnnualFuelUsage(vehicles);
    chartVehicleAnnualElectricityUsage(vehicles);
    chartVehicleFuelElectricCost(vehicles);
    chartVehicleAnnualOperatingCost(vehicles);
    chartVehicleAnnualPerMileCost(vehicles);
    chartVehicleAnnualCarbonEmissions(vehicles);
    chartVehicleTotalCostOfOwnership(vehicles);
    chartStateEnergyBreakdown(residentState);
    enableButtonBar();
    resetButtonBar();
    setTimeout(function () {
        enableBeginGraphing();
    }, 3000);
}

function checkCalculateReport() {
    var listModels = $('#vehicle-compare-list-group li');
    if (listModels.length > 1) {
        enableBeginGraphing();
    } else {
        disableBeginGraphing();
    }
}

function resetButtonBar() {
    $('input:radio[name="chart-options-usage"]').filter('[value="chart-annual-fuel-usage"]').attr('checked', true).trigger('change');
    $('input:radio[name="chart-options-costs"]').filter('[value="chart-annual-fuel-electric-cost"]').attr('checked', true).trigger('change');
}

function enableButtonBar() {
    $('input:radio[name="chart-options-usage"]').prop('disabled', false).parent().removeClass('btn-disabled').show();
    $('input:radio[name="chart-options-costs"]').prop('disabled', false).parent().removeClass('btn-disabled').show();
}

function disableButtonBar() {
    $('input:radio[name="chart-options-usage"]').prop('disabled', true).parent().removeClass('btn-primary active').addClass('btn-disabled').hide();
    $('input:radio[name="chart-options-costs"]').prop('disabled', true).parent().removeClass('btn-primary active').addClass('btn-disabled').hide();
}

function loanPerYearCost(principle) {
    var downPayment = principle * .1;
    var loanTerm = 60;
    var interestPercent = 6 / 1200;
    var monthlyPayment = (principle - downPayment) * interestPercent / (1 - (Math.pow(1 / (1 + interestPercent), loanTerm)));
    var yearPayment = [];
    for (var i = 0; i < 5; i++) {
        yearPayment[i] = monthlyPayment * 12;
    }
    return yearPayment;
}

// payment = principle * monthly interest/(1 - (1/(1+MonthlyInterest)*Months))

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function chartVehicleAnnualFuelUsage(vehicles) {
    $('#chart-annual-fuel-usage').empty();
    var container = document.getElementById('chart-annual-fuel-usage');
    var categoriesData = [];
    var fuelData = [];
    var maxFuel = 0;
    for (var idx in vehicles) {
        if (vehicles[idx].totalgallons > maxFuel) {
            maxFuel = vehicles[idx].totalgallons;
        }
        categoriesData.push(vehicles[idx].name);
        fuelData.push(vehicles[idx].totalgallons);
    }
    maxFuel = maxFuel * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
            name: 'Fuel',
            data: fuelData
        }]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Annual Fuel Usage',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'Gallons',
            max: maxFuel
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: 'gallons'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleAnnualElectricityUsage(vehicles) {
    $('#chart-annual-electricity-usage').empty();
    var container = document.getElementById('chart-annual-electricity-usage');
    var categoriesData = [];
    var electricityData = [];
    var maxElectric = 0;
    for (var idx in vehicles) {
        if (vehicles[idx].totalelectric > maxElectric) {
            maxElectric = vehicles[idx].totalelectric;
        }
        categoriesData.push(vehicles[idx].name);
        electricityData.push(vehicles[idx].totalelectric);
    }
    maxElectric = maxElectric * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
            name: 'Electricity',
            data: electricityData
        }]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Annual Electricity Usage',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'kWh',
            max: maxElectric
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: 'kWh'
        }
    };
    var theme = {
        series: {
            colors: [
                '#ffb840', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    tui.chart.registerTheme('electricTheme', theme);
    options.theme = 'electricTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleFuelElectricCost(vehicles) {
    $('#chart-annual-fuel-electric-cost').empty();
    var container = document.getElementById('chart-annual-fuel-electric-cost');
    var categoriesData = [];
    var fuelCostData = [];
    var electricCostData = [];
    var maxCost = 0;
    for (var idx in vehicles) {
        categoriesData.push(vehicles[idx].name);
        if (vehicles[idx].fuelcost > maxCost) {
            maxCost = vehicles[idx].fuelcost;
        }
        fuelCostData.push(vehicles[idx].fuelcost.toFixed(2));
        if (vehicles[idx].electriccost > maxCost) {
            maxCost = vehicles[idx].electriccost;
        }
        electricCostData.push(vehicles[idx].electriccost.toFixed(2));
    }
    console.log(categoriesData);
    console.log(fuelCostData);
    console.log(electricCostData);
    maxCost = maxCost * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
                name: 'Fuel',
                data: fuelCostData
            },
            {
                name: 'Electricity',
                data: electricCostData
            }
        ]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Annual Fuel/Electric Costs',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'Dollars',
            min: 0,
            max: maxCost,
            suffix: '$'
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: '$'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleAnnualOperatingCost(vehicles) {
    $('#chart-annual-operating-cost').empty();
    var container = document.getElementById('chart-annual-operating-cost');
    var categoriesData = [];
    var operatingData = [];
    var maxOperating = 0;
    for (var idx in vehicles) {
        var totalCost = vehicles[idx].fuelcost + vehicles[idx].electriccost + vehicles[idx].maintenance;
        if (totalCost > maxOperating) {
            maxOperating = totalCost;
        }
        categoriesData.push(vehicles[idx].name);
        operatingData.push(totalCost.toFixed(2));
    }
    maxOperating = maxOperating * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
            name: 'Operating Costs',
            data: operatingData
        }]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Annual Operating Costs',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'Dollars',
            max: maxOperating
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: '$'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleAnnualPerMileCost(vehicles) {
    $('#chart-cost-per-mile').empty();
    var container = document.getElementById('chart-cost-per-mile');
    var categoriesData = [];
    var perMileData = [];
    var maxPerMile = 0;
    for (var idx in vehicles) {
        var totalCost = vehicles[idx].fuelcost + vehicles[idx].electriccost + vehicles[idx].maintenance;
        var perMileCost = Math.round((totalCost / vehicles[idx].totalmiles) * 100) / 100
        if (perMileCost > maxPerMile) {
            maxPerMile = perMileCost;
        }
        categoriesData.push(vehicles[idx].name);
        perMileData.push(perMileCost.toFixed(2));
    }
    maxPerMile = maxPerMile * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
            name: 'Cost Per Mile',
            data: perMileData
        }]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Cost Per Mile',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'Dollars',
            max: maxPerMile
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: '$'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleAnnualCarbonEmissions(vehicles) {
    $('#chart-annual-carbon-emissions').empty();
    var container = document.getElementById('chart-annual-carbon-emissions');
    var categoriesData = [];
    var emissionsData = [];
    var maxEmissions = 0;
    for (var idx in vehicles) {
        var totalEmissions = vehicles[idx].fuelemissions + vehicles[idx].electricemissions;
        if (totalEmissions > maxEmissions) {
            maxEmissions = totalEmissions;
        }
        categoriesData.push(vehicles[idx].name);
        emissionsData.push(totalEmissions);
    }
    maxEmissions = maxEmissions * 1.25;
    var data = {
        categories: categoriesData,
        series: [{
            name: 'Carbon Emissions',
            data: emissionsData
        }]
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Annual Carbon Emissions',
            'format': '1,000'
        },
        yAxis: {
            title: 'Vehicle'
        },
        xAxis: {
            title: 'kg',
            max: maxEmissions
        },
        series: {
            showLabel: true
        },
        tooltip: {
            suffix: 'kg'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.barChart(container, data, options);
}

function chartVehicleTotalCostOfOwnership(vehicles) {
    $('#chart-total-cost-of-ownership').empty();
    var container = document.getElementById('chart-total-cost-of-ownership');
    var tcoData = [];
    var maxTCO = 0;
    for (var idx in vehicles) {
        var tempVehicle = {};
        tempVehicle['name'] = vehicles[idx].name;
        tempVehicle['data'] = [];
        var additiveCost = 0;
        for (var z = 0; z < 15; z++) {
            var loanCost = 0;
            if (z < 5) {
                loanCost = vehicles[idx].loanpayments[z];
            }
            var tempCost = additiveCost + loanCost + vehicles[idx].fuelcost + vehicles[idx].electriccost + vehicles[idx].maintenance;
            var tcoYear = Math.round(tempCost * 100) / 100;
            if (tcoYear > maxTCO) {
                maxTCO = tcoYear;
            }
            tempVehicle['data'].push(tcoYear.toFixed(2));
            additiveCost = tcoYear;
        }
        console.log(tempVehicle);
        tcoData.push(tempVehicle);
    }
    console.log(tcoData);
    maxTCO = maxTCO * 1.25;
    var data = {
        categories: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15'],
        series: tcoData
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 440,
            height: 320,
            title: 'Total Cost of Ownership by Vehicle'
        },
        yAxis: {
            title: 'TCO',
        },
        xAxis: {
            title: 'Year',
            pointOnColumn: false,
            tickInterval: 'auto'
        },
        series: {
            showDot: false,
            zoomable: true
        },
        tooltip: {
            suffix: '$'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };
    // For apply theme
    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';
    tui.chart.lineChart(container, data, options);
}

function chartStateEnergyBreakdown(residentState) {
    var electricSources = stateMapData[residentState].electricSources;
    var seriesData = [];
    for (var idx in electricSources) {
        var title = idx;
        if (idx == 'gas') {
            title = 'Natural Gas';
        }
        seriesData.push({
            name: toTitleCase(title),
            data: electricSources[idx]
        });
    };
    $('#chart-state-electricity-generation').empty();
    var container = document.getElementById('chart-state-electricity-generation');
    var data = {
        categories: ['Electricity'],
        series: seriesData
    };
    var options = {
        usageStatistics: false,
        chartExportMenu: {
            visible: false
        },
        chart: {
            width: 420,
            height: 320,
            title: stateMapData[residentState].stateName + ' - Sources of Electricity'
        },
        legend: {
            visible: true
        },
        tooltip: {
            suffix: '%'
        }
    };
    var theme = {
        series: {
            colors: [
                '#83b14e', '#458a3f', '#295ba0', '#2a4175', '#289399',
                '#289399', '#617178', '#8a9a9a', '#516f7d', '#dddddd'
            ]
        }
    };

    // For apply theme

    // tui.chart.registerTheme('myTheme', theme);
    // options.theme = 'myTheme';

    tui.chart.pieChart(container, data, options);
}