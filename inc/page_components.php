<?php

function outputHeadCssAndJs()
{
	?>
	<link type="text/css" rel="stylesheet" href="css/bootstrap/bootstrap.css">
	<link type="text/css" rel="stylesheet" href="css/bootstrap/bootstrap-theme.css">
	<script language="javascript" type="text/javascript" src="js/jquery-1.11.3.min.js"></script>
	<script language="javascript" type="text/javascript" src="js/Vague.js"></script>
	<link type="text/css" rel="stylesheet" href="css/itemset_style.css">
	<?php
}


function outputHeader()
{
	?>
	<h1 id="main-title">Item Set Playlists</h1>
	<h3 id="main-subtitle">Create musical playlists that compliment the play style of your League of Legends custom item sets</h3>
	<br />
	<?php
}


function outputFooter()
{
	?>
	<div id="site-footer">
		<div class="footer-text">Item Set Playlists was created for the Riot Games API Challenge 2.0 that ran from August 10, 2015 through August 31, 2015. It uses the Riot Games League of Legends API, the Echo Nest API, and the Spotify Play Button.<br /><br />
		Item Set Playlists isn't endorsed by Riot Games and doesn't reflect the views or opinions of Riot Games or anyone officially involved in producing or managing League of Legends. League of Legends and Riot Games are trademarks or registered trademarks of Riot Games, Inc. League of Legends © Riot Games, Inc.</div>
	</div>
	<script language="javascript" type="text/javascript" src="js/itemset-playlist.js"></script>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-61968631-2', 'auto');
	  ga('send', 'pageview');
	</script>
	<?php
}

?>