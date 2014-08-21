<div class="col-menu-content">
	<div class="navcontainer">
		<div class="menu-header">&nbsp;</div>
		<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/house.png" width="16" height="16" alt="Home" /></div><a href="/admin/">Home</a></div>
		<?php
		if(isset($_SESSION['user_id'])) { ?>
			<?php
			if($this->user->Admin) { ?>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/user.png" width="16" height="16" alt="users" /></div><a href="/admin/manage_users.php">Users</a></div>
	<?php	}
			?>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/page.png" width="16" height="16" alt="Events" /></div><a href="/admin/manage_pages.php">Pages</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/newspaper.png" width="16" height="16" alt="News" /></div><a href="/admin/news/index.php">News</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/help.png" width="16" height="16" alt="FAQs" /></div><a href="/admin/faqs/index.php">FAQs</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/brick_add.png" width="16" height="16" alt="Products" /></div><a href="/admin/products/index.php">Products</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/images.png" width="16" height="16" alt="Exhibitions" /></div><a href="/admin/exhibitions/index.php">Exhibitions</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/page.png" width="16" height="16" alt="Blocks" /></div><a href="/admin/blocks/index.php">Blocks</a></div>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/lock_open.png" width="16" height="16" alt="Logout" /></div><strong><a href="/admin/home/logout.php">Log-out</a></strong></div>
	<?php }
		else {?>
			<div class="menu-item"><div style="float:left; width:24px;"><img src="/images/icons/lock.png" width="16" height="16" alt="Login" /></div><a href="/admin/home/login.php">Login</a></div>
	<?php }
		?>
		
	</div>
</div>