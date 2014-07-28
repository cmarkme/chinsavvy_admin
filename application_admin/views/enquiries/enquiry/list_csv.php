<?php foreach ($table_data['headings'] as $heading) : ?>"<?=$heading?>",<?php endforeach; ?>

<?php foreach ($table_data['rows'] as $row) : ?><?php foreach ($row as $index => $value) : ?>"<?=stripslashes($value)?>",<?php endforeach; ?>

<?php endforeach; ?>
