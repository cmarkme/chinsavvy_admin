<? $this->load->view('estimates/_tabs') ?>

<h1><?= $title ?></h1>
<?php
function recursive($components)
{
	if ( ! $components instanceof Illuminate\Database\Eloquent\Collection)
	{
		$components = new Illuminate\Database\Eloquent\Collection(array($components));
	}
	foreach ($components as $component)
	{
		echo '<li class="component' . ($component->type === 'Eloquent\Assembly' ? ' droppable' : '') . '" data-component_id="' . $component->id . '"';
		if (isset($component->pivot))
		{
			echo ' data-parent_id="' . $component->pivot->assembly_id . '"';
		}
		echo '>';
		echo '<div class="box '. strtolower($type = substr($component->type, 9)) . '">';

		echo "<strong>{$type}";
		if (isset($component->detail->code))
		{
			echo " #{$component->detail->code}";
		}
		echo "</strong><br>\n";
		echo (isset($component->pivot->qty) ? '<span class="qty">' . $component->pivot->qty . '</span>' : 1) . '# <span class="name">' . $component->name . "</span></br>\n";
		echo "<div class=\"description\">\n\t{$component->description}\n</div>\n";
		echo "</div>\n";
		if ($component instanceof Eloquent\Assembly and $component->children->count())
		{
			echo '<ul>';
				recursive($component->children);
			echo '</ul>';
		}
		echo '</li>';
	}
}
?>

<ul class="bom">
	<?php recursive($estimate->product); ?>
</ul>
