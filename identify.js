function showResults() {
    // Get major options results
    var engineOverhaulResult = 'Engine Overhaul: ' + document.getElementById('engineOverhaul').value;
    var engineLowPowerResult = 'Engine Low Power: ' + document.getElementById('engineLowPower').value;
    var electricalProblemResult = 'Electrical Problem: ' + document.getElementById('electricalProblem').value;

    // Get maintenance options results
    var batteryResult = 'Battery: ' + document.getElementById('battery').value;
    var lightsResult = 'Lights: ' + document.getElementById('lights').value;
    // Add other maintenance options results here

    // Display results in modal
    document.getElementById('engineOverhaulResult').innerText = engineOverhaulResult;
    document.getElementById('engineLowPowerResult').innerText = engineLowPowerResult;
    document.getElementById('electricalProblemResult').innerText = electricalProblemResult;
    document.getElementById('maintenanceResult').innerText = batteryResult + '\n' + lightsResult;
    // Add other maintenance options results here

    // Show the modal
    $('#resultsModal').modal('show');
}