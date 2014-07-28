<? $this->load->view('estimates/_tabs') ?>

<?= $breadcrumb ?>

<h1><?= $title ?></h1>

<? if ($component->subassemblies->count()) : ?>
	<h3>Sub-Assemblies</h3>
	<table class="tbl tbl-fixed">
		<thead>
			<tr>
				<td colspan="3" style="border: none"></td>
				<th colspan="4" style="text-align: center">Components</th>
			</tr>
			<tr>
				<th>Qty</th>
				<th>Name</th>
				<th>Description</th>
				<th>Sub-Assemblies</th>
				<th>Parts</th>
				<th>Materials</th>
				<th>Processes</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($component->subassemblies as $subassembly) : ?>
				<tr>
					<td><a href="estimates/bom/update_qty/<?= $component->id ?>/<?= $subassembly->id ?>" class="change-qty"><?= $subassembly->pivot->qty ?></a></td>
					<td><?= $subassembly->name ?></td>
					<td><?= $subassembly->description ?></td>
					<td><?= $subassembly->subassemblies->count() ?></td>
					<td><?= $subassembly->parts->count() ?></td>
					<td><?= $subassembly->materials->count() ?></td>
					<td><?= $subassembly->processes->count() ?></td>
					<td>
						<a href="<?= $_SERVER['REQUEST_URI'] ?>/<?= $subassembly->id ?>" class="action-icon edit"></a>
						<a href="estimates/bom/detach/<?= $component->id ?>/<?= $subassembly->id ?>" class="action-icon delete"></a>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
<br>
<? endif ?>

<? if ($component->materials->count()) : ?>
	<h3>Materials</h3>
	<table class="tbl tbl-fixed">
		<thead>
			<tr>
				<th>Qty</th>
				<th>Unit</th>
				<th>Name</th>
				<th>Description</th>
				<th>Type</th>
				<th>Grade</th>
				<th>Form</th>
				<th>Source</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($component->materials as $material) : ?>
				<tr>
					<td><a href="estimates/bom/update_qty/<?= $component->id ?>/<?= $material->id ?>" class="change-qty"><?= $material->pivot->qty ?></a></td>
					<td><?= $material->cost->measurement_unit->name ?></td>
					<td><?= $material->name ?></td>
					<td><?= $material->description ?></td>
					<td><?= $material->cost->type->name ?></td>
					<td><?= $material->cost->grade->name ?></td>
					<td><?= $material->cost->form ?></td>
					<td><?= $material->cost->source ?></td>
					<td>
						<a href="<?= $_SERVER['REQUEST_URI'] ?>/<?= $material->id ?>" class="action-icon edit"></a>
						<a href="estimates/bom/detach/<?= $component->id ?>/<?= $material->id ?>" class="action-icon delete"></a>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
	<br>
<? endif ?>

<? if ($component->processes->count()) : ?>
	<h3>Processes</h3>
	<table class="tbl tbl-fixed">
		<thead>
			<tr>
				<th>Qty</th>
				<th>Name</th>
				<th>Description</th>
				<th>Type</th>
				<th>Sub-Type</th>
				<th>Machine Size</th>
				<th>Action</th>
				<th>Source</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($component->processes as $process) : ?>
				<tr>
					<td><a href="estimates/bom/update_qty/<?= $component->id ?>/<?= $process->id ?>" class="change-qty"><?= $process->pivot->qty ?></a></td>
					<td><?= $process->name ?></td>
					<td><?= $process->description ?></td>
					<td><?= $process->cost->type->name ?></td>
					<td><?= $process->cost->subtype->name ?></td>
					<td><?= $process->cost->machine_size ?></td>
					<td><?= $process->cost->action ?></td>
					<td><?= $process->cost->source ?></td>
					<td>
						<a href="<?= $_SERVER['REQUEST_URI'] ?>/<?= $process->id ?>" class="action-icon edit"></a>
						<a href="estimates/bom/detach/<?= $component->id ?>/<?= $process->id ?>" class="action-icon delete"></a>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<br>
<? endif ?>

<? if ($component->parts->count()) : ?>
	<h3>Parts</h3>
	<table class="tbl tbl-fixed">
		<thead>
			<tr>
				<th>Qty</th>
				<th>Name</th>
				<th>Description</th>
				<th>Source</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($component->parts as $part) : ?>
				<tr>
					<td><a href="estimates/bom/update_qty/<?= $component->id ?>/<?= $part->id ?>" class="change-qty"><?= $part->pivot->qty ?></a></td>
					<td><?= $part->name ?></td>
					<td><?= $part->description ?></td>
					<td><?= $part->cost->source ?></td>
					<td>
						<a href="<?= $_SERVER['REQUEST_URI'] ?>/<?= $part->id ?>" class="action-icon edit"></a>
						<a href="estimates/bom/detach/<?= $component->id ?>/<?= $part->id ?>" class="action-icon delete"></a>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>
	<br>
<? endif ?>

<ul class="inline">
	<li><a href="#add_subassembly" class="modal button">Add Sub-Assembly</a></li>
	<li><a href="#add_material" class="modal button">Add Material</a></li>
	<li><a href="#add_process" class="modal button">Add Process</a></li>
	<li><a href="#add_part" class="modal button">Add Part</a></li>
</ul>

<script>
	var parts = <?= $component->getUseableParts() ?>;
	var subassemblies = <?= $component->getUseableSubassemblies() ?>;
	var materials = <?= $component->getUseableMaterials() ?>;
	var processes = <?= $component->getUseableProcesses() ?>;
</script>

<div class="modal" id="add_subassembly">
	<a href="#" class="close"></a>
	<h2>Add Sub-Assembly</h2>
	<?php
	echo form_open('estimates/bom/add_subassembly/' . $component->id, array('id' => 'process_add_form', 'class' => 'ajax-form'));
		print_form_container_open();

			print_dropdown_element('id', 'Existing Sub-Assembly', array(), false,
				array('id' => 'subassembly_id'));

			print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true,
				array('class' => 'lockable')
				);
			print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 5), true,
				array('class' => 'lockable')
				);

			print_input_element('Qty', array('name' => 'qty', 'size' => 10, 'maxlength' => 10, 'value' => 1),
				true, array('value' => 1));

			print_submit_container_open();
    			echo form_submit('submit', 'Submit', 'id="submit_button"');
    		print_submit_container_close();

		print_form_container_close();

	echo form_close();
?>
</div>

<div class="modal" id="add_material">
	<a href="#" class="close"></a>
	<h2>Add Material</h2>
	<?php
		echo form_open('estimates/bom/add_material/' . $component->id, array('id' => 'material_add_form', 'class' => 'ajax-form'));
			print_form_container_open();

				print_dropdown_element('id', 'Existing Material', array(), false,
					array('id' => 'material_id'));

				print_dropdown_element('material_type_id', 'Material Type', Eloquent\MaterialType::getDropdown('name'), true,
					array('data-ajax-url' => 'estimates/material_grade/dropdown/', 'data-receiver' => 'material_grade_id',
						'class' => 'hideable-parent'));
				print_dropdown_element('material_grade_id', 'Material Grade', array('' => 'Please Select Above'), true,
					array('data-ajax-url' => 'estimates/material_cost/dropdown/', 'data-receiver' => 'material_cost_id',
						'id' => 'material_grade_id', 'class' => 'hideable-parent', 'disabled' => 'disabled'));
				print_dropdown_element('type_id', 'Material Cost', array('' => 'Please Select Above'), true,
					array('id' => 'material_cost_id', 'class' => 'hideable-parent', 'disabled' => 'disabled'));

				print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true,
					array('class' => 'lockable')
				);
				print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 5), true,
					array('class' => 'lockable')
				);

				print_input_element('Qty', array('name' => 'qty', 'size' => 10, 'maxlength' => 10), true, array('value' => 1));

				print_submit_container_open();
	    			echo form_submit('submit', 'Submit', 'id="submit_button"');
	    		print_submit_container_close();

			print_form_container_close();

		echo form_close();
	?>
</div>

<div class="modal" id="add_process">
	<a href="#" class="close"></a>
	<h2>Add Process</h2>
	<?php
		echo form_open('estimates/bom/add_process/' . $component->id, array('id' => 'process_add_form', 'class' => 'ajax-form'));
			print_form_container_open();

				print_dropdown_element('id', 'Existing Process', array(), false,
					array('id' => 'process_id'));

				print_dropdown_element('process_type_id', 'Process Type', Eloquent\ProcessType::getDropdown('name'), true,
					array('data-ajax-url' => 'estimates/process_subtype/dropdown/', 'data-receiver' => 'process_subtype_id'
						, 'class' => 'hideable-parent'));
				print_dropdown_element('process_subtype_id', 'Process Sub-Type', array('' => 'Please Select Above'), true,
					array('data-ajax-url' => 'estimates/process_cost/dropdown/', 'data-receiver' => 'process_cost_id',
						'id' => 'process_subtype_id', 'class' => 'hideable-parent', 'disabled' => 'disabled'));
				print_dropdown_element('type_id', 'Process Cost', array('' => 'Please Select Above'), true,
					array('id' => 'process_cost_id', 'class' => 'hideable-parent', 'disabled' => 'disabled'));

				print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true,
					array('class' => 'lockable')
				);
				print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 5), true,
					array('class' => 'lockable')
				);

				print_input_element('Qty', array('name' => 'qty', 'size' => 10, 'maxlength' => 10), true, array('value' => 1));


				print_submit_container_open();
	    			echo form_submit('submit', 'Submit', 'id="submit_button"');
	    		print_submit_container_close();

			print_form_container_close();

		echo form_close();
	?>
</div>

<div class="modal" id="add_part">
	<a href="#" class="close"></a>
	<h2>Add Part</h2>
	<?php
	echo form_open('estimates/bom/add_part/' . $component->id, array('id' => 'process_add_form', 'class' => 'ajax-form'));
		print_form_container_open();

			print_dropdown_element('id', 'Existing Part', array(), false,
				array('id' => 'part_id'));

			print_input_element('Part #', array('name' => 'code', 'size' => 30, 'maxlength' => 30), true,
				array('class' => 'lockable')
				);
			print_input_element('Name', array('name' => 'name', 'size' => 60, 'maxlength' => 60), true,
				array('class' => 'lockable')
				);
			print_textarea_element('Description', array('name' => 'description', 'cols' => 60, 'rows' => 5), true,
				array('class' => 'lockable')
				);
			print_input_element('Source', array('name' => 'source', 'size' => 60, 'maxlength' => 60), true,
				array('class' => 'lockable')
				);

			$this->load->view('estimates/_price_breaks_form');

			print_input_element('Qty', array('name' => 'qty', 'size' => 10, 'maxlength' => 10), true, array('value' => 1));


			print_submit_container_open();
    			echo form_submit('submit', 'Submit', 'id="submit_button"');
    		print_submit_container_close();

		print_form_container_close();

	echo form_close();
?>
</div>
