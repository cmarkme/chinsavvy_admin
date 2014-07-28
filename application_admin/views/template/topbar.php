<p><a id="top"></a></p>
<div id="site-info">
    <div class="left">
        <span id="username">
        <?=img(array('src' => 'images/admin/icons/user_16.gif', 'class' => 'icon nofloat'))?>
        <b>User</b>:<?=$this->session->userdata('username')?> |
        </span>
        <span id="server_time">
        <?=img(array('src' => 'images/admin/icons/history_16.gif', 'class' => 'icon nofloat'))?>
        <b>Server time</b>: <?php echo date('F j, Y, g:i a'); ?> |
        </span>
        <span id="roles">
        <?=img(array('src' => 'images/admin/icons/key.png', 'class' => 'icon nofloat'))?>
        <b>Roles</b>: <?php foreach ($this->session->userdata('roles') as $key => $role) {
          echo $role->name;
          echo ($key+1 < count($this->session->userdata('roles'))) ? ', ' : '';
        } ?>
        </span>
    </div>
    <div class="right">
        <a href="/users/user/edit/<?=$this->session->userdata('user_id')?>" title="Edit Details">
        <?=img(array('src' => 'images/admin/icons/contacts_16.gif', 'class' => 'icon nofloat'))?>
        Edit Details</a>
    </div>
</div><!-- div info -->
<div id="site-description">
    <div class="text"><?=$this->config->item('site_name')?></div>
</div><!-- div description -->
<div id="wrapper">
