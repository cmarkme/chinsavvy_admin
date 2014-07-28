     </div><!-- div content -->
      <div id="footer">
        <div class="left">
        </div>
        <div class="right">
        </div>
      </div><!-- div footer -->
  </div><!-- div wrapper -->

    <?php if (!empty($jstoloadinfooter)) {
        foreach ($jstoloadinfooter as $jsfile): ?>
            <script type="text/javascript" src="/includes/js/<?=$jsfile?>.js"> /* <![CDATA[ */ /* ]]> */ </script>
        <?php endforeach;
    }
    if (!empty($jstoloadforie)) {
        foreach ($jstoloadforie as $jsfile): ?>
            <?php if ($this->config->item('site_type') == 'production') {
                $jsfile .= '.min.js';
            } else {
                $jsfile .= '.js';
            }
            ?>
            <!--[if IE]>
            <script type="text/javascript" src="/includes/js/<?=$jsfile?>"> /* <![CDATA[ */ /* ]]> */ </script>
            <![endif]-->
        <?php endforeach;
    }
    ?>
<div id="mask"></div>
</body>
</html>
