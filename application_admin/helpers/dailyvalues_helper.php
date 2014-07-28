<?php
function get_dailyvalue_table($market_id, $commodity_id, $daily_values, $commodity_category = EXCHANGE_COMMODITY_CATEGORY_METALS) {
    $ci = get_instance();
    $ci->load->helper('date');

    $html = '';

    $values_count = count($daily_values);
    $marginbottom = 20;

    if ($values_count < 10) {
        $marginbottom = 10 * (10 - $values_count);
    }

    $first_value = reset($daily_values[$market_id][$commodity_id]);
    $commodity_name = $first_value['commodity'];
    $currency_name = get_lang_for_constant_value('CURRENCY_', $first_value['currency_id']);

    $daily_table = '<table class="tbl" style="width: 30%; margin-bottom: '.$marginbottom.'px;">' . "\n";
    $daily_table .= "<caption>$commodity_name daily prices</caption>" . "\n";
    $daily_table .= "<tr><th>Date</th>\n";

    if ($first_value['currency_id'] != CURRENCY_USD) {
        $daily_table .= "<th>Local Value ($currency_name)</th><th>Conversion rate</th>\n";
    }
    $daily_table .= "<th>Value (US$)</th><th>Delete</th></tr>\n";

    $counter = 0;
    $stats = array();
    $stats_prev = array();

    $stats['period_week'] = array();
    $stats['period_month'] = array();
    $stats['period_quarter'] = array();
    $stats['period_year'] = array();

    $stats['period_week_original'] = array();
    $stats['period_month_original'] = array();
    $stats['period_quarter_original'] = array();
    $stats['period_year_original'] = array();

    $stats['period_week_conversion'] = array();
    $stats['period_month_conversion'] = array();
    $stats['period_quarter_conversion'] = array();
    $stats['period_year_conversion'] = array();

    $stats_prev['period_week'] = array();
    $stats_prev['period_month'] = array();
    $stats_prev['period_quarter'] = array();
    $stats_prev['period_year'] = array();

    uasort($daily_values[$market_id][$commodity_id], 'sort_dailyvalues_by_timestamp');

    foreach ($daily_values[$market_id][$commodity_id] as $daily_value_id => $daily_value) {
        $display_value = $daily_value['value'] / $daily_value['conversion_rate'];
        $formatted_date = mdate('%d/%m/%Y', $daily_value['timestamp']);

        $formatted_value = number_format(round($display_value));
        $original_value = number_format(round($daily_value['value']));

        $currency_rate = $daily_value['conversion_rate'];

        $conversion_rate = 1 / $currency_rate;
        $conversion_rate_formatted = round(1 / $conversion_rate, 5);

        if ($counter < 30) {
            $daily_table .= "<tr><td>$formatted_date</td>";
            if ($first_value['currency_id'] != CURRENCY_USD) {
                $daily_table .= "
                    <td style=\"text-align: left;\">$original_value</td>\n
                    <td style=\"text-align: left;\">$conversion_rate_formatted</td>\n";
            }
            $daily_table .= "
                <td style=\"text-align: left;\">$formatted_value</td>
                <td>";
            $daily_table .= anchor("exchange/daily_values/delete/$daily_value_id/$commodity_category", img(array('src' => 'images/admin/icons/delete_16.gif', 'class' => 'icon')));
            $daily_table .= "</td>
                </tr>\n";
        }

        // Add value to different time periods arrays
        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 7)) {
            $stats['period_week'][] = $display_value;
            $stats['period_week_conversion'][] = $conversion_rate;
            $stats['period_week_original'][] = $daily_value['value'];
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 30)) {
            $stats['period_month'][] = $display_value;
            $stats['period_month_conversion'][] = $conversion_rate;
            $stats['period_month_original'][] = $daily_value['value'];
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 7 * 13)) {
            $stats['period_quarter'][] = $display_value;
            $stats['period_quarter_conversion'][] = $conversion_rate;
            $stats['period_quarter_original'][] = $daily_value['value'];
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 365)) {
            $stats['period_year'][] = $display_value;
            $stats['period_year_conversion'][] = $conversion_rate;
            $stats['period_year_original'][] = $daily_value['value'];
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 7 * 2)) {
            $stats_prev['period_week'][] = $display_value;
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 30 * 2)) {
            $stats_prev['period_month'][] = $display_value;
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 7 * 13 * 2)) {
            $stats_prev['period_quarter'][] = $display_value;
        }

        if ($daily_value['timestamp'] > time() - (60 * 60 * 24 * 365 * 2)) {
            $stats_prev['period_year'][] = $display_value;
        }

        $counter ++;

    }

    foreach ($stats_prev as $key => $stat_array) {
        if (count($stat_array) > 0) {
            $stats_prev[$key .'_mean'] = array_sum($stat_array) / count($stat_array);
        } else {
            $stats_prev[$key .'_mean'] = 'No data';
        }
    }

    foreach ($stats as $key => $stat_array) {
        if (count($stat_array) == 0) {
            $stats[$key .'_mean'] = 'No data';
            $stats[$key .'_arrow'] = '--';
        } elseif (!preg_match('/(original|conversion)/', $key, $matches)) {
            $stats[$key .'_mean'] = array_sum($stat_array) / count($stat_array);
            $stats[$key .'_diff'] = $stats[$key .'_mean'] - $stats_prev[$key .'_mean'];

            $formatted_diff = (round($stats[$key .'_diff'], 3));
            $stats[$key .'_mean'] = number_format(round($stats[$key .'_mean']));

            if ($stats[$key .'_diff'] > 0) {
                $stats[$key .'_arrow'] = '<img src="images/admin/icons/arrow-up_16.gif" title="'
                    .$formatted_diff.'" alt="'.$formatted_diff.'" />';
            } elseif ($stats[$key .'_diff'] < 0) {
                $stats[$key .'_arrow'] = '<img src="images/admin/icons/arrow-down-red_16.gif" title="'
                    .$formatted_diff.'" alt="'.$formatted_diff.'" />';
            } else {
                $stats[$key .'_arrow'] = '--';
            }
        } else {
            $stats[$key .'_mean'] = array_sum($stat_array) / count($stat_array);

            if (strstr($key, 'original')) {
                $stats[$key .'_mean'] = number_format(round($stats[$key .'_mean']));
            } else {
                $stats[$key .'_mean'] = round(1/ $stats[$key .'_mean'], 5);
            }
        }
    }

    $daily_table .= '</table>' . "\n";

    $html = '<table class="tbl" style="width: 32%; float: left; margin-right: 20px">' . "\n";
    $html .= "<caption>$commodity_name averages</caption>" . "\n";
    $html .= '<tr><th style="width:90px">Period</th>';

    if ($first_value['currency_id'] != CURRENCY_USD) {
        $html .= "<th>Average price ($currency_name)</th><th>Average conversion rate</th>\n";
    }

    $html .= "<th>Average price (US$)</th><th>Progression</th></tr>\n";

    // Last week
    $html .= "<tr><td>Last 7 days</td>";
    if ($first_value['currency_id'] != CURRENCY_USD) {
        $html .= "<td>{$stats['period_week_original_mean']}</td><td>{$stats['period_week_conversion_mean']}</td>\n";
    }
    $html .= "<td>{$stats['period_week_mean']}</td><td>{$stats['period_week_arrow']}</td></tr>" . "\n";

    // Last month
    $html .= "<tr><td>Last month</td>";
    if ($first_value['currency_id'] != CURRENCY_USD) {
        $html .= "<td>{$stats['period_month_original_mean']}</td><td>{$stats['period_month_conversion_mean']}</td>\n";
    }
    $html .= "<td>{$stats['period_month_mean']}</td><td>{$stats['period_month_arrow']}</td></tr>" . "\n";

    // Last quarter
    $html .= "<tr><td>Last 3 months</td>";
    if ($first_value['currency_id'] != CURRENCY_USD) {
        $html .= "<td>{$stats['period_quarter_original_mean']}</td><td>{$stats['period_quarter_conversion_mean']}</td>\n";
    }
    $html .= "<td>{$stats['period_quarter_mean']}</td><td>{$stats['period_quarter_arrow']}</td></tr>" . "\n";

    // Last year
    $html .= "<tr><td>Last year</td>";
    if ($first_value['currency_id'] != CURRENCY_USD) {
        $html .= "<td>{$stats['period_year_original_mean']}</td><td>{$stats['period_year_conversion_mean']}</td>\n";
    }
    $html .= "<td>{$stats['period_year_mean']}</td><td>{$stats['period_year_arrow']}</td></tr>" . "\n";
    $html .= "</table>\n";

    $html .= $daily_table;

    return $html;
}

/**
 * Callback function used to sort daily_value arrays by their timestamp value, in reverse order
 */
function sort_dailyvalues_by_timestamp($val1, $val2) {
    if ($val1['timestamp'] == $val2['timestamp']) {
        return 0;
    }
    return ($val1['timestamp'] > $val2['timestamp']) ? -1 : 1;
}
