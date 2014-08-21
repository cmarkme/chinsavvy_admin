$(document).ready(function(){
	$(".caption").each(function () {
		var $this = $(this);
		
		var title = $this.attr("alt");
		if(typeof title !== 'undefined' && title !== false)
		{
			var w = $this.css("width");
			var h = parseInt($this.css("height"), 10);
			var f = $this.css("float");
			var m_t = parseInt($this.css("margin-top"), 10);
			var m_r = parseInt($this.css("margin-right"), 10);
			var m_b = parseInt($this.css("margin-bottom"), 10);
			var m_l = parseInt($this.css("margin-left"), 10); 
			
			var h_adjust = 0;

			if(f == "right")
			{
				h_adjust += m_l;
			}
			
			$this.wrap('<div style="position: relative; float: ' + f + ';" />');
			$this.after('<div class="captionContainer" style="width: ' + w + '; top: ' + (h + m_t - 24) + 'px; left: ' + h_adjust + 'px;"><p style="margin: 0px; text-align: center;">'+ title +'</p></div>');
		}
	});
});