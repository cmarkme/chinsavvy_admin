<div id="nav"><?=get_nav_title('Navigation')?>
    <ul id="<?=get_nav_id()?>">
      <li><a href="/" title="Home">Home</a></li>
      <li><a href="/logout" title="Logout">Logout</a></li>
    </ul>
    <?=get_dynamic_nav()?>
</div>
<div id="content">
<?=set_breadcrumb()?>
<?php if ($this->session->flashdata('message') || $this->session->userdata('message')) :
    $message = ($this->session->userdata('message')) ? $this->session->userdata('message') : $this->session->flashdata('message');
    $message_type = ($this->session->userdata('message_type')) ? $this->session->userdata('message_type') : $this->session->flashdata('message_type');
    clear_messages();
?>
    <div id="message" class="message" ><span class="<?=$message_type?>"><?=$message?></span></div>
<?php else : ?>
    <div id="message" class="message" style="display: none"><span></span></div>
<?php endif; ?>
