$(document).ready(function() {
	// set up the click handlers
	$("#btnGetPlaylist").click(function() {
		$('#error-msg-wrapper').hide();
		loadItemSetPlaylist();
	});
	
	// add the handler for when the textarea text changes
	$("#txtItemSetJSON").keyup(function() {
		displayItemSetTitle();
	});
	
	// click handlers for the show/hide links
	$("#toggle-settings").click(function() {
		toggleSettingsVisibility();
	});
	
	$("#toggle-help").click(function() {
		toggleHelpVisibility();
	});
	
	// clock handler for the link that loads a sample itemset link
	$("#load-sample-itemset").click(function() {
		// load the sample item set into the textarea
		loadSampleItemSet();
	});
	
	// check to see if the browser has support for the various File APIs
	if(window.File && window.FileReader && window.FileList && window.Blob)
	{
		// we do have support, show the drop zone
		$("#itemset-drop-zone").show();
	
		// add handlers for the file drop area for item set files
		$("#itemset-drop-zone").on('dragover', function(event) {
			event.preventDefault();
			event.stopPropagation();
			event.originalEvent.dataTransfer.dropEffect = 'copy'; // explicitly show this is a copy
		});
		
		$("#itemset-drop-zone").on('dragenter', function(event) {
			event.preventDefault();
			event.stopPropagation();
			$("#itemset-drop-zone").addClass("drop-zone-hover");
		});
		
		$("#itemset-drop-zone").on('dragleave', function(event) {
			event.preventDefault();
			event.stopPropagation();
			$("#itemset-drop-zone").removeClass("drop-zone-hover");
		});
		
		$("#itemset-drop-zone").on('drop', function(event) {
			if(event.originalEvent.dataTransfer)
			{
				if(event.originalEvent.dataTransfer.files.length)
				{
					event.preventDefault();
					event.stopPropagation();
					readItemSetFromFile(event.originalEvent.dataTransfer.files);
				}
			}
		});
	}
	
	// if we have anything in the textarea, load the item set title if appropriate
	displayItemSetTitle();
});

function loadSampleItemSet()
{
	// load the sample item set into the textarea
		$("#txtItemSetJSON").val('{"isGlobalForMaps":true,"type":"custom","isGlobalForChampions":false,"mode":"any","associatedChampions":[],"map":"any","title":"Jinx, durr","associatedMaps":[],"sortrank":4999,"blocks":[{"items":[{"count":1,"id":"1055"},{"count":1,"id":"3340"},{"count":1,"id":"2003"},{"count":1,"id":"2004"},{"count":1,"id":"2044"},{"count":1,"id":"2043"}],"type":"starting"},{"items":[{"count":1,"id":"1038"},{"count":2,"id":"2003"},{"count":1,"id":"2004"},{"count":1,"id":"1037"},{"count":2,"id":"2003"},{"count":1,"id":"2004"}],"type":"First Back"},{"items":[{"count":1,"id":"3031"},{"count":1,"id":"3006"},{"count":1,"id":"3087"}],"type":"Core Items"},{"items":[{"count":1,"id":"3072"},{"count":1,"id":"3046"},{"count":1,"id":"3035"},{"count":1,"id":"3139"},{"count":1,"id":"3102"},{"count":1,"id":"3026"}],"type":"Other/Defensive"}],"champion":"Jinx","priority":false}');
		
		// then load the item set title
		displayItemSetTitle();
}

function loadSpotifyPlaylist(songIDs, playlistTitle)
{
	var playlistWrapper = $("#playlist-wrapper");
	var spotifyPlaylist = $("#spotifyPlaylistWrapper");
	
	spotifyPlaylist.html('<iframe src="https://embed.spotify.com/?uri=spotify:trackset:' + playlistTitle + ':' + songIDs + '" width="500" height="580" frameborder="0" allowtransparency="true"></iframe>');
	
	playlistWrapper.show();
}

function displayPlaylistInfo(responseJSON)
{
	var res = responseJSON;

	// if we got a Success, display the info
	if(res.hasOwnProperty('status') && res.status == 'Success')
	{
		var info = '';
		
		// get the css class we want to add the to title, based on the returned play style
		var itemSetTitle = '';		
		
		if(res.hasOwnProperty('play_style'))
		{
			var titleClass = '';
			
			switch(res.play_style)
			{
				case 'adc':
					titleClass = 'title-adc';
					break;
				case 'assassin_ad':
					titleClass = 'title-ada';
					break;
				case 'assassin_ap':
					titleClass = 'title-apa';
					break;
				case 'tank':
					titleClass = 'title-tank';
					break;
				case 'support':
					titleClass = 'title-support';
					break;
				case 'fighter':
					titleClass = 'title-fighter';
					break;
				case 'troll':
					titleClass = 'title-troll';
					break;
			}
			
			itemSetTitle = '<span class="' + titleClass + '">' + getItemSetTitle() + '</span>';
		}
		else
		{
			itemSetTitle = getItemSetTitle();
		}
		
		info += '<span id="itemset-title-info">' + itemSetTitle + ' Playlist</span><br />';
		
		if(res.hasOwnProperty('traits'))
		{
			info += '<span id="traits-info">Based on: ' + res.traits + '</span><br />';
		}
		
		// now put it on the screen
		$('#playlistInfo').html(info);
	}
}

function loadItemSetPlaylist()
{
	var itemSetString = $("#txtItemSetJSON").val();
	
	// check that the json string is a valid JSON object
	if(isStringJSON(itemSetString))
	{
		displayPreloaderImage();
	
		var itemSetJSON = JSON.parse(itemSetString);
		
		$.post("get_itemset_playlist.php"
			, { itemset: itemSetJSON }
			, function(data) {
				// check that the data we got back is valid JSON data (in case we got some sort of PHP error response)
				if(!isStringJSON(data))
				{
					displayError('Error: There was an error while generating your playlist');
				}
				else
				{
					//alert("Got data: " + data);
					var responseJSON = JSON.parse(data);
					
					// check the response for Success and that the trackIDs field exists
					if(responseJSON.hasOwnProperty('response'))
					{
						var res = responseJSON.response;

						// if we got a Success and trackIDs, load the playlist
						if(res.hasOwnProperty('status') && res.status == 'Success' && res.hasOwnProperty('trackIDs'))
						{
							toggleSettingsVisibility();
							displayItemBG(res);
							displayPlaylistInfo(res);
							hidePreloaderImage();
							loadSpotifyPlaylist(res.trackIDs, getItemSetTitle() + " Playlist");
						}
						// if there was an error, display the error message
						else if(res.hasOwnProperty('status') && res.status == 'Failure')
						{
							var errorMsg = 'Error: Error reading item set';
						
							if(res.hasOwnProperty('message'))
							{
								errorMsg = 'Error: ' + res.message;
							}
							
							displayError(errorMsg);
						}
					}
					// didn't get a response, display a generic error
					else
					{
						displayError('Error: Did not get a response');
					}
				}
			}
		);
	}
	// else it doesn't look like a valid JSON string, show an error message
	else
	{
		displayError('Error: Please load a valid item set');
	}
}

function readItemSetFromFile(files)
{
	// we only want to accept 1 file at a time
	if(files.length != 1)
	{
		displayError('Error: Please drag in only 1 item set file');
		$("#itemset-drop-zone").removeClass("drop-zone-hover");
	}
	else
	{
		// we got 1 file, read it as text
		file = files[0];
		var reader = new FileReader();
		
		// set the callback for when the file is loaded
		reader.onload = function(event) {
			// put the results of the file in the textarea, then change the text in the drop zone
			$("#txtItemSetJSON").val(event.target.result);
			$("#itemset-drop-zone").html("Got it! Click Get Playlist!");
			// and display the item set title
			displayItemSetTitle();
		};
		
		// start reading the file
		reader.readAsText(file);
	}
}

function getItemSetTitle()
{
	var itemSetString = $("#txtItemSetJSON").val();
	
	// check that the json string is a valid JSON object
	if(isStringJSON(itemSetString))
	{
		var itemSetJSON = JSON.parse(itemSetString);
		
		if(itemSetJSON.hasOwnProperty('title'))
		{
			return itemSetJSON.title;
		}
		return null;
	}
	return null;
}

function toggleSettingsVisibility()
{
	var settings = $("#itemset-wrapper");
	var settingsToggle = $("#toggle-settings");
	
	// first we'll collapse the help area if it isn't
	if($("#help-wrapper").is(":visible"))
	{
		toggleHelpVisibility();
	}
	
	// change the link text depending on if the settings are now visible or not
	if(settings.is(":visible"))
	{
		settingsToggle.html("Show Settings");
	}
	else
	{
		settingsToggle.html("Hide Settings");
	}
	
	settings.slideToggle();
}

function toggleHelpVisibility()
{
	var help = $("#help-wrapper");
	var helpToggle = $("#toggle-help");
	
	// change the link text depending on if the settings are now visible or not
	if(help.is(":visible"))
	{
		helpToggle.html("Show Help");
	}
	else
	{
		helpToggle.html("Hide Help");
	}
	
	help.slideToggle();
}

function displayItemSetTitle()
{
	var title = getItemSetTitle();
		
	if(title !== null)
	{
		$("#itemset-name").html('Item set: <span id="itemset-title">' + title + '</span>');
	}
}

function displayItemBG(res)
{
	// if we got a Success and a display image, display the image
	if(res.hasOwnProperty('status') && res.status == 'Success' && res.hasOwnProperty('display_image'))
	{
		$('#bg-image').css("background-image", "url(" + res.display_image + ")");

		// blur the background image, sweet!
		var vague = $('#bg-image').Vague({
			intensity:      60,      // Blur Intensity
			forceSVGUrl:    false  // Force absolute path to the SVG filter,
		});
		vague.blur();
		
		$("#bg-image").fadeIn(1000, "linear", function() {
			// animation complete
		});
	}
}

function displayPreloaderImage()
{
	$("#preloader").show();
}

function hidePreloaderImage()
{
	$("#preloader").hide();
}

function displayError(errorMsg)
{
	hidePreloaderImage();

	// set the message text and display it
	$('#error-msg').html(errorMsg);
	$('#error-msg-wrapper').show();
}

function isStringJSON(str) 
{
    try
	{
        JSON.parse(str);
    } catch (e)
	{
        return false;
    }
    return true;
}