<?php
if ($this->session->userdata('user_id')==3868)
{?>
<div style="height: 100px">
</div>
<div>
<div style="float: left;margin-left:200px;padding: 10px 0px 10px 10px;">
<h1><a href="#">Back-Up Database(s)</a></h1>
</div>
<div style="float: left;">
<a href="#"><img alt="" src="<?php echo base_url();?>images/DB_backup.jpg"/></a>
</div>
<div style="clear:both">
<?php
foreach ($db_list as $key => $value)
{
	echo $value."<br/>";
}
?>
</div>
<div>

</div>

</div>

<?php 
}
?>