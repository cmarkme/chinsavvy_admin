function doCalculation(county) {
    $('ResultsContainer').html("");

    var length;
    var width;
    var height;
    var weight;
    var units;

    unitindex = document.calculator.units.selectedIndex;
    units = document.calculator.units.options[unitindex].value;

    length = document.calculator.length.value;
    width = document.calculator.width.value;
    height = document.calculator.height.value;
    weight = document.calculator.weight.value;


    product = document.calculator.product.value;
    reference = document.calculator.reference.value;
    notes = document.calculator.notes.value;

    var params = {length: length, width: width, height: height, weight: weight, product: product, reference: reference, notes: notes, units: units}
    $.post('/calculator/air_docalc', params, function(data) {
        $('#ResultsContainer').html(data);
    });
}


function ResetText() {
	unitindex = document.calculator.units.selectedIndex;

	units = document.calculator.units.options[unitindex].value;

	if (units == "imperial") {
		$("#lengthtext").html("Length (inches)");
		$("#widthtext").html("Width (inches)");
		$("#heighttext").html("Height (inches)");
		$("#weighttext").html("Weight (<strong>Kilograms</strong>)");
	} else {
		$("#lengthtext").html("Carton Length (CM)");
		$("#widthtext").html("Carton Width (CM)");
		$("#heighttext").html("HeightWidth (CM)");
		$("#weighttext").html("Weight (Kilograms)");
	}
}
