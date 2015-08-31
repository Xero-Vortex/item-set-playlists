<?php
require_once('inc/page_components.php');
?>
<!DOCTYPE html>
<html>
<head>
	<title>League of Legends Item Set Playlists</title>
	<?php outputHeadCssAndJs(); ?>
</head>
<body>
	<?php outputHeader(); ?>
	<div id="main-content">
		<div id="main-content-wrapper" class="content-container">
			<div id="itemset-wrapper">
				<div id="help-wrapper">
					<div>To get your own custom playlist:</div>
					<ol>
						<li>Find your League of Legends item sets:
							<ul>
								<li>Windows: <span class="text-highlight">[League of Legends directory]\Config\Champions\[champion name]\Recommended\</span> for champion-specific item sets or <span class="text-highlight">[League of Legends directory]\Config\Global\Recommended\</span> for general item sets</li>
									<ul><li>Your League of Legends directory will be wherever you installed it. This is often <span class="text-highlight">C:\Riot Games\League of Legends</span></li></ul>
								<li>Mac/OSX: Open Finder > Applications > right-click League of Legends > go to <span class="text-highlight">/Contents/LoL/Config/[champion name]/Recommended/</span> for champion-specific item sets or <span class="text-highlight">/Contents/LoL/Config/Global/Recommended/</span> for general item sets</li>
							</ul>
						</li>
						<li>Drag and drop the item set file into the box below
							<ul>
								<li>Alternatively you can open the item set file in a text editor (like Notepad) and copy the entire file's text and paste it into the box below</li>
							</ul>
						</li>
					</ol>
				</div>
				<div id="toggle-help-link"><a id="toggle-help" href="#">Hide Help</a></div>
				<div id="error-msg-wrapper">
					<div id="error-msg" class="bg-danger"></div>
					<br />
				</div>
				<div id="itemset-form-wrapper">
					<div id="itemset-drop-zone" class="drop-zone">Drop item set file here</div>
					<div id="itemset-box-description">Or paste item set text here:</div>
					<textarea id="txtItemSetJSON"></textarea><br />
					<a href="#" id="load-sample-itemset">Click here to load a sample item set</a>
					<br />
					<div id="itemset-name"></div>
					<div id="preloader"><img src="img/preloader.gif" title="Loading..." /></div>
					<button class="btn btn-default core-button" id="btnGetPlaylist" type="button">Get Playlist</button>
					<div id="spotify-note">A free or premium Spotify account is required. <br /><span id="content-warning">NOTE: because playlists are generated procedurally, some tracks in the playlists might (and probably will) contain inappropriate content.</span></div>
				</div>
			</div>
			<div id="toggle-settings-link"><a id="toggle-settings" href="#">Hide Settings</a></div>
		</div>
		<div id="playlist-wrapper" class="content-container">
			<div id="playlistInfo"></div>
			<div id="spotifyPlaylistWrapper"></div>
		</div>
	</div>
	<?php outputFooter(); ?>
	<div id="bg-image"></div>
</body>
</html>
<?php

?>