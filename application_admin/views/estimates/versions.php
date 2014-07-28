<h1><?= $title ?></h1>

<a href="estimates/estimate/add/<?= $enquiry_id ?>" class="action-icon add"></a>
<br><br>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Date</th>
			<th>Version</th>
			<th>Name</th>
			<th>Description</th>
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
		{ bSortable: false }
	];
	var dataTableUrl = 'estimates/estimate/datatable/<?= $enquiry_id ?>';
</script>