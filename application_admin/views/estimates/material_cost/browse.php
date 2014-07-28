<h1><?= $title ?></h1>

<a href="estimates/material_cost/add" class="action-icon add"></a>
<br><br>
<p class="error">* Denotes a cost which is over <?= ESTIMATES_OVER_AGE_COST_THRESHOLD ?> days old.</p>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>ID</th>
			<th>Creation Date</th>
			<th>Revision Date</th>
			<th>Type</th>
			<th>Grade</th>
			<th>Form</th>
			<th>Units</th>
			<th>Min Qty</th>
			<th>Source</th>
			<th>Created By</th>
			<th>Last Edited By</th>
			<th></th>
		</tr>
	</thead>
	<tbody></tbody>
</table>
<script>
	var aoColumns = [
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		{bSortable: 'false'},
		null,
		null,
		{bSortable: 'false'},
	];
	var dataTableUrl = 'estimates/material_cost/datatable';
</script>