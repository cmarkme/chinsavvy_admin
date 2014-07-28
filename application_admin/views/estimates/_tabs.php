<ul class="tabs">
	<li<?= $active === 'edit' ? ' class="active"' : '' ?>>
		<a href="estimates/estimate/edit/<?= $estimate_id ?>">
			Overview
		</a>
	</li>
	<li<?= $active === 'build' ? ' class="active"' : '' ?>>
		<a href="estimates/bom/build/<?= $estimate_id ?>">
			Build BOM
		</a>
	</li>
	<li<?= $active === 'diagram' ? ' class="active"' : '' ?>>
		<a href="estimates/bom/diagram/<?= $estimate_id ?>">
			BOM Diagram
		</a>
	</li>
	<li<?= $active === 'summary' ? ' class="active"' : '' ?>>
		<a href="estimates/bom/summary/<?= $estimate_id ?>">
			Summary
		</a>
	</li>
	<li<?= $active === 'fixed_cost' ? ' class="active"' : '' ?>>
		<a href="estimates/fixed_cost/browse/<?= $estimate_id ?>">
			Fixed Costs
		</a>
	</li>
</ul>