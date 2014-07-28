<?php echo "<?xml version='1.0' encoding='UTF-8'?>"?>
<enquiries>
<?php foreach ($table_data['rows'] as $row) : ?>
    <enquiry>
<?php for ($i = 0; $i < count($row); $i++) :
    if ($i == 0) reset($table_data['headings']); else next($table_data['headings']);
    $heading = key($table_data['headings']);
    ?>
        <<?=$heading?>><?=htmlentities(str_replace('<br />', ', ', stripslashes($row[$i])))?></<?=$heading?>>
<?php endfor; ?>
    </enquiry>
<?php endforeach; ?>
</enquiries>
