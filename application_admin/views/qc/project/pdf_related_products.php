<tr>
    <th<?=$style?>><strong><?=$lang_strings['relatedproducts'][$lang]?></strong></th>
    <td colSpan="3">

    <?php if (!empty($details['related'])) {
        $is_first = true;
        foreach ($details['related'] as $related_id => $related_name) {
            if (!$is_first) {
                echo '<br />';
            }
            echo $related_name;
            $is_first = false;
        }
    }
    ?>

    </td>
</tr>
