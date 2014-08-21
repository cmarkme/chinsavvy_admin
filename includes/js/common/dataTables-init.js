var dataTable;

$(document).ready(function() {

	dataTable = $('.dataTable').dataTable({
		bProcessing: true,
		bServerSide: true,
		sAjaxSource: dataTableUrl,
		aoColumns: aoColumns,
		sServerMethod: 'POST'
	});

	$('h1').click(function() {
		dataTable.fnFilter( 'gas', 3 );
	});


});