<h1><?= $title ?></h1>

<a href="estimates/process_cost/add" class="action-icon add"></a>
<br><br>
<p class="error">* Denotes a cost which is over <?= ESTIMATES_OVER_AGE_COST_THRESHOLD ?> days old.</p>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>ID</th>
			<th>Creation Date</th>
			<th>Revision Date</th>
			<th>Process</th>
			<th>Sub Process</th>
			<th>Machine Size</th>
			<th>Action</th>
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
		null,
		null,
		{bSortable: 'false'},
	];
	var dataTableUrl = 'estimates/process_cost/datatable';
</script>