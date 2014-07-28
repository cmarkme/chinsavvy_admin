<div class="message"><span class="error">You do not have the required permissions!</span></div>
You need <?=$required_capability?> to view this page.</br >
You current permissions are:
<ul>
<?php foreach ($this->session->userdata('user_caps') as $cap) : ?>
    <li><?=$cap?></li>
<?php endforeach; ?>
</ul>
