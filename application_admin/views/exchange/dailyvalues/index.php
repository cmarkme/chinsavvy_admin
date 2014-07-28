<?php echo $section_title ?>
<div id="<?=$category?>_values">
    <form id="filter_form" action="exchange/dailyvalues/<?=$category_name?>" method="post">
        <div>
            <table summary="table" class="tbl">
                <tr>
                    <td>
                        <select name="filter_markets" onchange="document.getElementById('filter_form').submit();">
                            <option <?php echo $markets_selected['all']; ?> value="all" >All markets</option>
                             <?php foreach ($markets_array as $market) {
                                if (empty($markets_selected[$market->id])) {
                                    $markets_selected[$market->id] = '';
                                }

                                echo '<option ' . $markets_selected[$market->id] . ' value="'.$market->id.'">' .$market->name.'</option>' . "\n";
                                }
                              ?>
                        </select>
                        <select name="filter_commodities" onchange="document.getElementById('filter_form').submit();">
                            <option <?php echo $commodities_selected['all']; ?> value="all" >All <?php echo $category_name ;?></option>
                              <?php
                                foreach ($commodities_array as $commodity) {
                                    if (empty($commodities_selected[$commodity->id])) {
                                        $commodities_selected[$commodity->id] = '';
                                    }

                                    echo '<option ' . $commodities_selected[$commodity->id] . ' value="'.$commodity->id.'">' .$commodity->name.'</option>'. "\n";
                                }
                              ?>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </form>
<?php
foreach ($dailyvalues_array as $market_id => $commodities) {
    $market_name = $market_names[$market_id];
    echo get_title(array('title' => $market_name, 'help' => "Data on $category_name prices for $market_name", 'expand' => "market$market_id"));
    echo '<div id="market'.$market_id.'">';
    foreach ($commodities as $commodity_id => $dailyvalues) {
        echo get_dailyvalue_table($market_id, $commodity_id, $dailyvalues_array, $category);
    }

    $market_html = '<div id="graph'.$market_id.'" style="width:600px;height:400px;"></div>';
    $data = $graph_data[$market_id];

    if (!empty($data)) {
        $label = "Daily ".$category_name." commodity prices in US$";
        $graph_options = array(
            'lines' => array('show' => true, 'fill' => false),
            'points' => array('show' => false, 'fill' => true),
            'grid' => array('hoverable' => true, 'clickable' => false),
            'xaxis' => array('mode' => 'time', 'timeformat' => '%m/%y', 'minTickSize' => array(1, 'month'), 'tickSize' => array(2, 'month')),
            'yaxis' => array('tickFormatter' => 'currency_formatter'),
            'legend' => array('backgroundOpacity' => 0.3, 'margin' => 20)
            );
        $this->flotgraph->initialise('graph'.$market_id, $data, $graph_options);
        $market_html .= $this->flotgraph->get_JS();
    }

    $market_html .= '</div>' . "\n";
    echo $market_html;
}
?>
</div>
