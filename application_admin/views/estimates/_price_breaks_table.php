<table class="price-breaks-table" border="1">
	<thead>
		<tr>
			<th>Qty</th>
			<th>Price</th>
		</tr>
	</thead>
	<tbody>
		<? if (isset($price_breaks)) : ?>
			<? foreach ($price_breaks as $qty => $price) : ?>
				<tr>
					<td><?= $qty ?></td>
					<td><?= currency_format($price) ?></td>
				</tr>
			<? endforeach ?>
		<? endif ?>
	</tbody>
</table>