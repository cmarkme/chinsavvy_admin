<h1>Sample Sizes</h1>
<p>Hint: Leave a 'Max Batch Qty' cell blank to remove all subsequent entries. If you make a mistake click 'Reset' to reload the table.</p>
<form method="post" action="/qc/sample_size/update">
	<table class="tbl" style="width:500px; table-layout:fixed;">
		<thead>
			<tr>
				<th>Min Batch Qty</th>
				<th>Max Batch Qty</th>
				<th>A</th>
				<th>B</th>
			</tr>
		</thead>
		<tbody>
			<? $min = 0 ?>
			<? foreach($sample_sizes as $key => $sample_size) : ?>
				<? $min++ ?>
				<tr>
					<td class="number" style="border-right: 0 !important;"><?= $min ?></td>
					<td><input type="text" name="qty[]" class="input-mini" value="<?= $min = $sample_size->max_batch_qty ?>"></td>
					<td><input type="text" name="a[]" class="input-mini" value="<?= $sample_size->A ?>">%</td>
					<td><input type="text" name="b[]" class="input-mini" value="<?= $sample_size->B ?>">%</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
	<input type="submit" value="Update">
	<a href="/qc/sample_size" class="button">Reset</a>
</form>