<?=$main_title?>
<div id="project">
    <div id="projectmessage"></div>
    <?=$details_title?>
    <div id="details">
        <div id="detailsmessage"></div>
        <table class="tbl" id="detailstable"></table>
    </div>
<?php if (has_capability('qc:viewproductspecs')) : ?>
    <?=$product_title?>
    <div id="productspecs" style="display: none;">
        <div id="productspecsmessage"></div>
        <table class="tbl" id="dimensionstable"><tr><td></td></tr></table>
        <table class="tbl" id="productfilestable"><tr><td></td></tr></table>
        <table class="tbl" id="productspecstable"><tr><td></td></tr></table>
    </div>
<?php endif; ?>
<?php if (has_capability('qc:viewqcspecs')) : ?>
    <?=$qc_title?>
    <div class="tbl" id="qcspecs" style="display: none;">
        <div id="qcspecsmessage"></div>
        <table class="tbl" id="qcfilestable"><tr><td></td></tr></table>
        <table id="qcspecstable" class="tbl"><tr><td></td></tr></table>
    </div>
<?php endif; ?>
<?php if (has_capability('qc:doanything')) : ?>
    <?=$inspector_title?>
    <div id="inspectors" style="display: none;">
        <table id="inspectorstable" class="tbl"></table>
    </div>
<?php endif; ?>
</div><!--div#project-->
<script type="text/javascript">
/*<![CDATA[ */
var projectid = <?php echo ($project_id) ? $project_id: '0'; ?>;
var specphotocounts = [];
var procedures = <?php echo json_encode($procedures) ?>;
var qcinspectors = <?php echo json_encode($inspector_types['qc_inspectors']) ?>;
var qcmanagers = <?php echo json_encode($inspector_types['qc_managers']) ?>;
//]]>
</script>
