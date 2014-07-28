<?php

function print_recursive_list($list, $name_field='name', $children_field='children', $label_field=null, $toggle_url=null, $toggle_id_field='id') {
    foreach ($list as $item) {
        $href = '';

        if (!is_null($toggle_url) && !empty($item->$toggle_id_field)) {
            $href = $toggle_url . $item->$toggle_id_field;
        }

        echo "<li>";
        $label = (is_null($label_field)) ? 'Select this item' : $item->$label_field;

        if (!empty($href)) {
            echo "<a href=\"$href\" title=\"$label\">";
        }
        echo "<span>{$item->$name_field}</span>\n";
        if (!empty($href)) {
            echo "</a>\n";
        }

        if (!empty($item->$children_field)) {
            echo "\n<ul>";
            print_recursive_list($item->$children_field, $name_field, $children_field, $label_field, $toggle_url, $toggle_id_field);
            echo "</ul>\n";
        }
        echo "</li>\n";
    }
}
