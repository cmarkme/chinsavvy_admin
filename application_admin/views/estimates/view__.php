<h1><?= $title ?></h1>

<textarea name="description" id="description" cols="30" rows="10"><?= $estimate->description ?></textarea>
<?php
function recursive($components)
{
	foreach ($components as $component)
	{
		echo '<div class="item ' . strtolower($type = substr($component->type, 9)) . '">';
		echo "\n\t<span class=\"title\">{$type} (&times;{$component->pivot->qty}) : {$component->name}</span>\n";
		if ($component->children->count())
		{
			echo "\t<div class=\"level\">";
				recursive($component->children);
			echo "\t</div>\n";
		}
		echo "</div>\n";
	}
}
?>

<div class="root">
	<span class="title"><?= $estimate->product->name ?></span>
	<div class="level">
		<?php
			recursive($estimate->product->children);
		?>
	</div>
</div>

<table>
	<thead>
		<th>Qty</th>
		<th>Name</th>
		<th>Code</th>
		<th>Unit Price</th>
		<th>Line Price</th>
	</thead>
	<tbody>
		<? $total = 0; ?>
		<? foreach ($estimate->product->getComponentList(100) as $component) : ?>
			<tr>
				<td><?= $component->qty ?></td>
				<td><?= $component->name ?></td>
				<td><?= $component->detail->code ?></td>
				<td>
					<? if($unit = $component->detail->getUnitPriceForQty($component->qty)) : ?>
						<?= $unit ?>
					<? else : ?>
						Under MOQ
					<? endif ?>
				</td>
				<td><?= $line = $component->detail->getLinePriceForQty($component->qty) ?></td>
				<? $total += $line ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th><?= $total ?></th>
	</tfoot>
</table>

<table>
	<thead>
		<tr>
			<th>Qty</th>
			<th>Price</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($estimate->getPrices() as $qty => $price) : ?>
			<tr>
				<td><?= $qty ?></td>
				<td><?= $price ?></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>


<? dd($estimate->getConnection()->getQueryLog()) ?>