<tr class="hideable">
    <th class="formlabel">Price Breaks</th>
    <td class="formelement required ">
		<table class="price-breaks-form" border="1">
			<thead>
				<tr>
					<th>Qty</th>
					<th>Price</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<? if (isset($price_breaks)) : ?>
					<? foreach ($price_breaks as $qty => $price) : ?>
						<tr>
							<td><input type="text" name="price_breaks[qty][]" class="qty" value="<?= $qty ?>" size="10"></td>
							<td>&yen;<input type="text" name="price_breaks[price][]" class="price" value="<?= $price ?>" size="10"></td>
							<td><button type="button" class="remove">Remove</button></td>
						</tr>
					<? endforeach ?>
				<? endif ?>
				<tr>
					<td><input type="text" name="price_breaks[qty][]" class="qty" size="10"></td>
					<td>&yen;<input type="text" name="price_breaks[price][]" class="price" size="10"></td>
					<td><button type="button" class="add button-small">Add</button></td>
				</tr>
			</tbody>
		</table>
    </td>
</tr>