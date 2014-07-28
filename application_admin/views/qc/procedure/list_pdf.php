<table cellspacing="0" cellpadding="10" border="0">
    <thead>
        <tr>
        <?php for ($i = 0; $i < count($table_data['headings']); $i++) : ?>
            <th <?=$style?> width: <?=$widths[$i]?>px;"><strong><?=($i == 0) ? reset($table_data['headings']) : next($table_data['headings'])?></strong></th>
        <?php endfor; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($table_data['rows'] as $i => $row) : ?>
            <tr style="background-color: <?php echo ($i % 2) ? '#eee' : '#fff'?>;">
            <?php foreach ($row as $index => $value) : ?>
                <td border="0" style="width:<?=$widths[$index]?>px;"><?=stripslashes($value)?></td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
