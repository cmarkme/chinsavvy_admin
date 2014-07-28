<? $this->load->view('estimates/_tabs') ?>

<h1><?= $title ?></h1>

<label for="qty">Qty:</label>
<input type="text" id="qty" value="<?= $qty ?>">
<input type="submit" value="Calculate"
	onclick="window.location.href='estimates/bom/summary/<?= $estimate->id ?>/' + document.getElementById('qty').value;">

<h3>Materials</h3>
<table class="tbl">
	<thead>
		<th>Qty</th>
		<th>Unit</th>
		<th>Name</th>
		<th>Type</th>
		<th>Grade</th>
		<th>Form</th>
		<th>Unit Price</th>
		<th>Total Price</th>
		<th>Amortized Price</th>
	</thead>
	<tbody>
		<? $total['material'] = $amortizedTotal['material'] = 0; ?>
		<? foreach ($components['Eloquent\Material'] as $component) : ?>
			<tr>
				<td>
					<?= $component->qty ?>
					<? if ($component->cost->isBelowMoq($component->qty)) : ?>
						(<span class="tooltip error">
							<?= $component->cost->moq ?>
							<span class="custom info">
								Below Minimum Order Qty of <?= $component->cost->moq ?>
							</span>
						</span>)
					<? endif ?>
				</td>
				<td><?= $component->cost->measurement_unit->name ?></td>
				<td><?= $component->name ?></td>
				<td><?= $component->cost->type->name ?></td>
				<td><?= $component->cost->grade->name ?></td>
				<td><?= $component->cost->form ?></td>
				<td>
					<span class="tooltip">
						<?= $cost = currency_format($component->saved_price->getUnitPriceForQty($component->qty)) ?>
						<span class="custom info">
							<?= $cost ?> is based on the cost price taken from the Material Cost database on <?= $component->creation_date ?>.
							<? $this->load->view('estimates/_price_breaks_table', array('price_breaks' => $component->saved_price->price_breaks)) ?>
						</span>
					</span>
					<? if ( $component->hasPriceChanged($qty) ) : ?>
						(<span class="tooltip error">
							<?= $cost = currency_format($component->cost->getUnitPriceForQty($component->qty)) ?>
							<span class="custom info">
								<?= $cost ?> is based on the Material Cost last updated on <?= $component->cost->revision_date ?>.
								<? $this->load->view('estimates/_price_breaks_table', array('price_breaks' => $component->cost->price_breaks)) ?>
							</span>
						</span>)
					<? endif ?>
				</td>
				<td>
					<?= currency_format( $line = $component->saved_price->getLinePriceForQty($component->qty) ) ?>
					<? if ( $component->hasPriceChanged($qty) ) : ?>
						(<span class="error"><?= currency_format($component->cost->getLinePriceForQty($component->qty)) ?></span>)
					<? endif ?>
				</td>
				<td><?= currency_format($product = $line / $qty) ?></td>
				<? $total['material'] += $line; $amortizedTotal['material'] += $product; ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th><?= currency_format($total['material']) ?></th>
		<th><?= currency_format($amortizedTotal['material']) ?></th>
	</tfoot>
</table>

<br>

<h3>Processes</h3>
<table class="tbl">
	<thead>
		<th>Qty</th>
		<th>Unit / Action</th>
		<th>Name</th>
		<th>Type</th>
		<th>Sub-Type</th>
		<th>Machine Size</th>
		<th>Unit Price</th>
		<th>Total Price</th>
		<th>Amortized Price</th>
	</thead>
	<tbody>
		<? $total['process'] = $amortizedTotal['process'] = 0; ?>
		<? foreach ($components['Eloquent\Process'] as $component) : ?>
			<tr>
				<td>
					<?= $component->qty ?>
					<? if ($component->cost->isBelowMoq($component->qty)) : ?>
						(<span class="tooltip error">
							<?= $component->cost->moq ?>
							<span class="custom info">
								Below Minimum Order Qty of <?= $component->cost->moq ?>
							</span>
						</span>)
					<? endif ?>
				</td>
				<td><?= $component->cost->action ?></td>
				<td><?= $component->name ?></td>
				<td><?= $component->cost->type->name ?></td>
				<td><?= $component->cost->subtype->name ?></td>
				<td><?= $component->cost->machine_size ?></td>
				<td>
					<span class="tooltip">
						<?= $cost = currency_format($component->saved_price->getUnitPriceForQty($component->qty)) ?>
						<span class="custom info">
							<?= $cost ?> is based on the cost price taken from the Process Cost database on <?= $component->creation_date ?>.
							<? $this->load->view('estimates/_price_breaks_table', array('price_breaks' => $component->saved_price->price_breaks)) ?>
						</span>
					</span>
					<? if ( $component->hasPriceChanged($qty) ) : ?>
						(<span class="tooltip error">
							<?= $cost = currency_format($component->cost->getUnitPriceForQty($component->qty)) ?>
							<span class="custom info">
								<?= $cost ?> is based on the Process Cost last updated on <?= $component->cost->revision_date ?>.
								<? $this->load->view('estimates/_price_breaks_table', array('price_breaks' => $component->cost->price_breaks)) ?>
							</span>
						</span>)
					<? endif ?>
				</td>
				<td>
					<?= currency_format( $line = $component->saved_price->getLinePriceForQty($component->qty) ) ?>
					<? if ( $component->hasPriceChanged($qty) ) : ?>
						(<span class="error"><?= currency_format($component->cost->getLinePriceForQty($component->qty)) ?></span>)
					<? endif ?>
				</td>
				<td><?= currency_format($product = $line / $qty) ?></td>
				<? $total['process'] += $line;
					$amortizedTotal['process'] += $product;
				?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th><?= currency_format($total['process']) ?></th>
		<th><?= currency_format($amortizedTotal['process']) ?></th>
	</tfoot>
</table>

<br>

<h3>Parts</h3>
<table class="tbl">
	<thead>
		<th>Qty</th>
		<th>Name</th>
		<th>Part #</th>
		<th>Unit Price</th>
		<th>Total Price</th>
		<th>Amortized Price</th>
	</thead>
	<tbody>
		<? $total['part'] = $amortizedTotal['part'] = 0; ?>
		<? foreach ($components['Eloquent\Part'] as $component) : ?>
			<tr>
				<td><?= $component->qty ?></td>
				<td><?= $component->name ?></td>
				<td><?= $component->cost->code ?></td>
				<? if($unit = $component->cost->getUnitPriceForQty($component->qty)) : ?>
					<td>
						<?= $unit ?>
					</td>
				<? else : ?>
					<td style="color: red">
						Below MOQ of <?= $component->cost->moq ?>
					</td>
				<? endif ?>
				<td><?= currency_format( $line = $component->cost->getLinePriceForQty($component->qty) ) ?></td>
				<td><?= currency_format( $product = $line / $qty ) ?></td>
				<? $total['part'] += $line; $amortizedTotal['part'] += $product; ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th><?= currency_format($total['part']) ?></th>
		<th><?= currency_format($amortizedTotal['part']) ?></th>
	</tfoot>
</table>

<br>

<h3>Fixed Costs</h3>
<table class="dataTable tbl">
	<thead>
		<tr>
			<th>Type</th>
			<th>Description</th>
			<th>Unit Price</th>
			<th>Amortized Price</th>
		</tr>
	</thead>
	<tbody>
		<? $total['fixed_cost'] = $amortizedTotal['fixed_cost'] = 0; ?>
		<? foreach($estimate->fixed_costs as $fixed_cost) : ?>
			<tr>
				<td><?= \Eloquent\FixedCost::$types[$fixed_cost->type] ?></td>
				<td><?= $fixed_cost->description ?></td>
				<td><?= currency_format( $fixed_cost->cost ) ?></td>
				<td><?= currency_format( $fixed_cost->cost / $qty ) ?></td>
				<? $total['fixed_cost'] += $fixed_cost->cost; ?>
				<? $amortizedTotal['fixed_cost'] += $fixed_cost->cost / $qty; ?>
			</tr>
		<? endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<th></th>
			<th></th>
			<th><?= currency_format($total['fixed_cost']) ?></th>
			<th><?= currency_format($amortizedTotal['fixed_cost']) ?></th>
		</tr>
	</tfoot>
</table>

<br>
<h3>Totals</h3>
<? if (empty($fixed_costs_grouped[array_search('QA', Eloquent\FixedCost::$types)])) : ?>
	<h3 class="error">Warning: You have not included QA in this estimate!</h3>
<? endif ?>
<div style="width: 600px">
	<table class="tbl tbl-fixed">
		<thead>
			<tr>
				<th colspan="2"></th>
				<th>Total for <?= $qty ?> Unit<?= $qty == 1 ? '' : 's' ?></th>
				<th>Amortized Price</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th rowspan="4">Components</th>
				<th>Components: Materials</th>
				<td><?= currency_format($total['material']) ?></td>
				<td><?= currency_format($amortizedTotal['material']) ?></td>
			</tr>
			<tr>
				<th>Processes</th>
				<td><?= currency_format($total['process']) ?></td>
				<td><?= currency_format($amortizedTotal['process']) ?></td>
			</tr>
			<tr>
				<th>Parts</th>
				<td><?= currency_format($total['part']) ?></td>
				<td><?= currency_format($amortizedTotal['part']) ?></td>
			</tr>
			<tr>
				<th>SUBTOTAL</th>
				<td><strong><?= currency_format($total['material'] + $total['process'] + $total['part']) ?></strong></td>
				<td><strong><?= currency_format($amortizedTotal['material'] + $amortizedTotal['process'] + $amortizedTotal['part']) ?></strong></td>
			</tr>
			<tr>
				<th colspan="4">&nbsp;</th>
			</tr>
			<? $first = true; ?>
			<? foreach(Eloquent\FixedCost::$types as $key => $label) : ?>
				<tr>
					<? if($first) : $first = false ?>
						<th rowspan="<?= count(Eloquent\FixedCost::$types) + 1 ?>">Fixed Costs</th>
					<? endif ?>
					<th><?= $label ?></th>
					<td><?= currency_format($cost = @$fixed_costs_grouped[$key]) ?></td>
					<td><?= currency_format($cost / $qty) ?></td>
				</tr>
			<? endforeach ?>
			<tr>
				<th>SUBTOTAL</th>
				<td><strong><?= currency_format($total['fixed_cost']) ?></strong></td>
				<td><strong><?= currency_format($amortizedTotal['fixed_cost']) ?></strong></td>
			</tr>
			<tr>
				<th colspan="4">&nbsp;</th>
			</tr>
			<tr>
				<th colspan="2">TOTAL</th>
				<td><strong><?= currency_format( array_sum($total) ) ?></strong></td>
				<td><strong><?= currency_format( array_sum($amortizedTotal) ) ?></strong></td>
			</tr>
		</tbody>
	</table>
</div>

<br>

<h3>Price Breaks</h3>
<div style="width: 200px">
	<table class="tbl">
		<thead>
			<tr>
				<th>Qty</th>
				<th>Price</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($estimate->getPrices() as $qty => $price) : ?>
				<tr>
					<td><a href="estimates/bom/summary/<?= $estimate->id ?>/<?= $qty ?>"><?= $qty ?></a></td>
					<td><?= currency_format($price) ?></td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
</div>