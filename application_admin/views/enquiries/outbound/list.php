<?php echo $report_title ?>
<div id="page">
    <?php echo $this->filter->get_filter_table('enquiries_outbound_quotations'); ?>
    <div id="outboundtable">
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
<script type="text/javascript" src="/includes/js/application/enquiries/outbound.js">
/*<![CDATA[ */
//]]>
</script>

