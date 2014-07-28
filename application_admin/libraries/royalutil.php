<?php
class Royalutil 
{
	function Royalutil()
	{
	}
	
	function convertCompType($t)
	{
		switch($t)
		{
			case "multiple_choice":
				return "Multiple Choice";
			break;
			case "free_format":
				return "Free Format";
			break;
		}
	}
	
	function convertOfferArea($a)
	{
		switch($a)
		{
			case "rooms":
				return "Rooms";
			break;
			case "dining":
				return "Dining";
			break;
			case "conference":
				return "Conference";
			break;
		}
	}
	
	function convertOfferType($t)
	{
		switch($t)
		{
			case "discounted_offers":
				return "Discounts";
			break;
			case "christmas":
				return "Christmas";
			break;
			case "food_drink":
				return "Food &amp; Drink";
			break;
			case "special_occasions":
				return "Special Occasions";
			break;
			case "outdoor":
				return "Outdoor Activities / Sports";
			break;
			case "special_interest":
				return "Special Interest";
			break;
		}
	}
	
	function timespanFormat($startDateStamp, $endDateStamp)
	{
		$startMonth = date("d/m/Y", $startDateStamp);
		$endMonth = date("d/m/Y", $endDateStamp);
		
		//echo "<pre>|".$startMonth."|".$endMonth."|</pre>";
		if(strcasecmp($startMonth , $endMonth) ==0)
		{
			return "On:&nbsp;". date("d/m/Y", $startDateStamp);
		}
		else {
			return "From:&nbsp;". date("d/m/Y", $startDateStamp) ."&nbsp;To:&nbsp;". date("d/m/Y", $endDateStamp);
		}
	}
	
	//used in the template to load the initial news story
	function createNewsTicker()
	{
		$ci =& get_instance();
		$ci->load->model("news_model");
		$news = $ci->news_model->get_recent(10);
		if(!isset($_SESSION['news_ticker_number']) || $_SESSION['news_ticker_number'] >= count($news)) $_SESSION['news_ticker_number'] = 0;
		
		echo $this->createTickerItem($news[$_SESSION['news_ticker_number']]);
		$_SESSION['news_ticker_number']++;
	}
	
	 function createTickerItem($news_item)
	{
		$ci = get_instance();
		$ci->load->helper('text');
		$h = '<div class="news_ticker_title"><a style="color:#FFFFFF;" href="/news/view/'.$news_item->ID.'.htm">'.character_limiter($news_item->Title, 34)."</a></div>";
		$h .= '<div>'.character_limiter($news_item->ShortBody, 56).'</div>';
		return $h;
	}
	
	//called by the template to load the initial offer.
	function createOffersTicker()
	{
		$ci =& get_instance();
		$rand = mt_rand(0, 1);
		if($rand == 0) 
		{ //offer
			$ci->load->model("offers_model");
			$offers = $ci->offers_model->get_current_pending();
			if(!isset($_SESSION['offer_ticker_number']) || $_SESSION['offer_ticker_number'] >= count($offers)) $_SESSION['offer_ticker_number'] = 0;
			
			echo $ci->royalutil->createOfferItem($offers[$_SESSION['offer_ticker_number']]);
			$_SESSION['offer_ticker_number']++;
		}
		
		else 
		{ //event
			$ci->load->model("events_model");
			$offers = $ci->events_model->get_current_pending();
			if(!isset($_SESSION['event_ticker_number']) || $_SESSION['event_ticker_number'] >= count($offers)) $_SESSION['event_ticker_number'] = 0;
			echo $ci->royalutil->createEventItem($offers[$_SESSION['event_ticker_number']]);
			$_SESSION['event_ticker_number']++;
		}
	}
	
	function createOfferItem($o)
	{
		$ci =& get_instance();
		$ci->load->helper('text');
		$h = '<div class="offer_ticker_title"><a style="color:#FFFFFF;" href="/special_offers/view/'.$o->ID.'.htm">'.character_limiter($o->Name, 34)."</a></div>";
		$h .= '<div>'.character_limiter($o->ShortDesc, 56).'</div>';
		return $h;
	}
	
	function createEventItem($o)
	{
		$ci =& get_instance();
		$ci->load->helper('text');
		$h = '<div class="offer_ticker_title"><a style="color:#FFFFFF;" href="/special_events/view/'.$o->ID.'.htm">'.character_limiter($o->Name, 34)."</a></div>";
		$h .= '<div>'.character_limiter($o->ShortDesc, 56).'</div>';
		return $h;
	}
	
	
	//called by the template to load the initial comp.
	function createCompetitionTicker()
	{
		$ci =& get_instance();
		$ci->load->model("competitions_model");
		$comp = $ci->competitions_model->get_current();
		if($comp === null)
		{
			$fake->Title = "No Current Competition";
			$fake->ShortDesc = "There is no competition currently open";
			echo $this->createCompItem($fake);
		}
		else {
			echo $this->createCompItem($comp);
		}
	}
	
	function createCompItem($c)
	{
		$ci =& get_instance();
		$ci->load->helper('text');
		$h = '<div class="comp_ticker_title">';
			if(isset($c->ID)) $h.= '<a style="color:#FFFFFF;" href="/competition/view.htm">';
				$h .= character_limiter($c->Title, 34);
			if(isset($c->ID)) $h .="</a>";
		$h .="</div>";
		$h .= '<div>'.character_limiter($c->ShortDesc, 56).'</div>';
		return $h;
	}
	
	public function galleryCount() 
	{
		$ci =& get_instance();
		$ci->load->model("gallery_m");
		return count($ci->gallery_m->activeList());
	}
	
	public function firstActiveGallery()
	{
		$ci =& get_instance();
		$ci->load->model("gallery_m");
		$gList = $ci->gallery_m->activeList();
		return $gList[0];
	}
}
?>