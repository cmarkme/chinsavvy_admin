
<?php
if(empty($offers) && empty($events)){

} else {
?>
<div class="offerScrollContainer">
    <img src="/images/offers.png" width="175" height="16" alt="Special Events and Offers at The Royal Hotel Cardiff" title="" class="offerScrollTitle" />
    <ul class="offerScrollContent">
    	<?php
		$firstOutput = false;
		foreach($offers as $offer)
		{ ?>
		<li style="<?=$firstOutput ? 'top:250px;' : 'top:0px;' ?>">
            <a href="/<?=$sectionName?>/offer/<?=$offer->ID?>.htm"><strong><?=htmlspecialchars($offer->Name)?></strong><br />
			<?=htmlspecialchars($offer->ShortDesc)?></a>
			<div><?=date("d M Y", $offer->StartDateStamp)?> to <?=date("d M Y", $offer->EndDateStamp)?></div>
      	</li>
		<?php
			$firstOutput = true;
        }
		foreach($events as $evnt) { ?>
		<li style="<?=$firstOutput ? 'top:250px;' : 'top:0px;' ?>">
			<a href="/events/view/<?=$evnt->ID?>.htm"><strong><?=htmlspecialchars($evnt->Name)?></strong><br />
			<?=htmlspecialchars($evnt->ShortDesc)?></a>
			<div><?=date("d M Y", $evnt->StartDateStamp)?> to <?=date("d M Y", $evnt->EndDateStamp)?></div>
		</li>
			<?php
			$firstOutput = true;
		}
        ?>                    
    </ul>
</div>
<?php
}