function doCalculation(county) {
    $('ResultsContainer').html("");

    var length;
    var width;
    var depth;
    var weight;
    var units;
	var item_count;

    unitindex = document.calculator.units.selectedIndex;
    units = document.calculator.units.options[unitindex].value;

    length = document.calculator.length.value;
    width = document.calculator.width.value;
    depth = document.calculator.depth.value;
    weight = document.calculator.weight.value;
    item_count = document.calculator.item_count.value;

    product = document.calculator.product.value;
    reference = document.calculator.reference.value;
    notes = document.calculator.notes.value;

    var params = {length: length,
                  width: width,
                  depth: depth,
                  weight: weight,
                  item_count: item_count,
                  product: product,
                  reference: reference,
                  notes: notes,
                  units: units}

    $.post('/calculator/shipping_docalc', params, function(data) {
        $('#ResultsContainer').html(data);
    });
}


function ResetText() {
	unitindex = document.calculator.units.selectedIndex;

	units = document.calculator.units.options[unitindex].value;

	if (units == "imperial") {
		$("#lengthtext").html("Carton Length (inches)");
		$("#widthtext").html("Carton Width (inches)");
		$("#depthtext").html("Carton Depth (inches)");
		$("#weighttext").html("Carton Weight (Lbs)");
	} else {
		$("#lengthtext").html("Carton Length (cm)");
		$("#widthtext").html("Carton Width (cm)");
		$("#depthtext").html("Carton Depth (cm)");
		$("#weighttext").html("Weight (Kilograms)");
	}
}
