	</div>
	<div id="footer">
	<a href="<?=$loc;?>/">home</a> 
	<a href="<?=$loc;?>/upload/">upload</a>
	<a href="<?=$loc;?>/tagfield/">tags</a>
	<a href="<?=$loc;?>/stats/">statistics</a> 
	<a href="<?=$loc;?>/about/">about</a>
	<a href="<?=$loc;?>/help/">help</a> 
	<a href="<?=$loc;?>/track/">changes</a>
	<a href="http://track.easytospell.net/">bugs</a>
	[<a href="<?=$loc;?>/hide/"><?=($_SESSION['hide']) ? '' : 'not ';?>worksafe</a>]
	<br/>
	<span id="copy">&copy;<?=date('Y');?> <a href="http://www.easytospell.net">Richard Marshall</a> v<?=$version;?></span>
	</div>
	</div>
	<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try {
		var pageTracker = _gat._getTracker("UA-6458318-1");
		pageTracker._trackPageview();
		} catch(err) {}</script>
</body>
</html>
<?php
$db->close();
?>
