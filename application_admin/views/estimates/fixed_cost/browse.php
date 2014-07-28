<? $this->load->view('estimates/_tabs'); ?>
<h1><?= $title ?></h1>

<a href="estimates/fixed_cost/add/<?= $estimate_id ?>" class="action-icon add"></a>
<br><br>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Type</th>
			<th>Description</th>
			<th>Cost</th>
			<th>Created By</th>
			<th>Last Edited By</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<? $total = 0 ?>
		<? foreach($fixed_costs as $fixed_cost) : ?>
			<tr>
				<td><?= \Eloquent\FixedCost::$types[$fixed_cost->type] ?></td>
				<td><?= $fixed_cost->description ?></td>
				<td><?= $fixed_cost->cost_formatted ?></td>
				<td><?= $fixed_cost->creator->username ?></td>
				<td><?= $fixed_cost->revisor->username ?></td>
				<td><?= $fixed_cost->getActionColumn() ?></td>
				<? $total += $fixed_cost->cost; ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<th colspan="2">TOTAL</th>
			<th><?= currency_format($total) ?></th>
			<th colspan="3"></th>
		</tr>
	</tfoot>
</table>
