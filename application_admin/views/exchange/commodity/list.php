<?php echo $report_title ?>
<div id="page">
    <?php echo $this->filter->get_filter_table('exchange_commodities'); ?>
    <div id="commoditytable">
        <table id="ajaxtable" class="tbl">
            <thead>
                <tr>
        <?php foreach ($table_headings as $field => $label): ?>
                    <th class="<?=$field?>"><?=$label?></th>

        <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript" src="/includes/js/application/exchange/commodity.js">
/*<![CDATA[ */
//]]>
</script>

