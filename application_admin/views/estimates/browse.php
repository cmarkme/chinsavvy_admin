<h1><?= $title ?></h1>

<a href="estimates/estimate/add" class="action-icon add"></a>
<br>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Enquiry Date</th>
			<th>Enquiry Ref</th>
			<th>Product Title</th>
			<th>Description</th>
			<th>Latest Version</th>
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
		{ bSortable: false }
	];
	var dataTableUrl = 'estimates/estimate/datatable';
</script>