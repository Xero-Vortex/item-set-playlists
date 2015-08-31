<?php
// this file should define the API keys we need
require_once('inc/config.php');
require_once('inc/general_helper.php');

// set up some variables to control various aspects of the Echonest and Spotify code
$spotifyPlayerWidth = 500;
$spotifyPlayerHeight = 580;

$numberInPlaylist = 25;

function getEchonestAPIKey()
{
	if(defined('ECHOAPIKEY'))
	{
		return ECHOAPIKEY;
	}
	else
	{
		return null;
	}
}


//http://developer.echonest.com/api/v4/playlist/static?api_key=[key]&artist=weezer&format=json&results=2&type=artist&bucket=id:spotify-US&bucket=tracks&limit=true
function getStaticPlaylistJSON($queryParameters)
{
	// we want to use the global variables
	global $numberInPlaylist;
	
	if($queryParameters != '')
	{
		$url = 'http://developer.echonest.com/api/v4/playlist/static?api_key=' . getEchonestAPIKey() . '&format=json&bucket=id:spotify-US&bucket=tracks&limit=true&results=' . $numberInPlaylist . '&type=artist-description' . getStandardQueryParams() . $queryParameters;
		
		//error_log('In getStaticPlaylistJSON. url: ' . print_r($url, true));
		
		return getJSONObject($url);
	}
	
	// need the queryParameters, returning null
	return null;
}


function getStandardQueryParams()
{
	// here we're going to 'blacklist' some generes that look to come up inappropriatly
	$ret = '&description=-jazz&description=-blues&description=-comedy&description=-religious';
	
	// set the distribution to wandering, attempting to not have as many songs from duplicate artists
	$ret .= '&distribution=wandering';
	
	// this seems to help with reducing the occurence of getting more than 1 song by the same artist
	$ret .= '&limited_interactivity=stylea';
	
	// restricting the earliest year helps keep out some older music that doesn't match what we're looking for
	$ret .= '&artist_start_year_after=1990';
	
	return $ret;
}


function getSpotifyTrackIDs($echonestJSON)
{
	if($echonestJSON !== '')
	{
		$trackIDs = '';
	
		// grab the song data list
		$songs = $echonestJSON['response']['songs'];
		
		// loop through the songs and grab the Spotify track IDs
		for($i = 0; $i < count($songs); $i++)
		{
			//print_r($songs[$i]['tracks'][0]);
			
			// the Spotify track ID is in the 'foreign_id' field and of the format 'spotify-US:track:5zd9TgduWbfFXwgnm3K3Rz'
			// we just want the track ID at the end of the string, so we grab the last 22 characters of the string
			$trackID = substr($songs[$i]['tracks'][0]['foreign_id'], -22);
		
			if($trackIDs !== '' && $trackID !== '')
			{
				$trackIDs .= ',';
			}
			
			$trackIDs .= $trackID;
		}
		
		//print_r($songs);
		
		return $trackIDs;
	}
	
}


// this is the function that should be used to take in the query parameters (mood, style, etc) 
// and return a comma-separated list Spotify track IDs
function getSpotifyTrackList($queryParameters)
{
	if($queryParameters !== null)
	{
		$playlistJSON = getStaticPlaylistJSON($queryParameters);
	
		//error_log('In getSpotifyTrackList. playlistJSON: ' . print_r($playlistJSON, true));
		
		if(hitRateLimit($playlistJSON))
		{
			// we hit the rate limit
			return 'ERROR|RATELIMIT';
		}
		
		return getSpotifyTrackIDs($playlistJSON);
	}
	else
	{
		return null;
	}
}


function hitRateLimit($playlistJSON)
{
	// example rate limit error JSON:
	// {"response": {"status": {"version": "4.2", "code": 3, "message": "3|You are limited to 20 accesses every minute. You might be eligible for a rate limit increase, go to http://developer.echonest.com/account/upgrade"}}}
	
	// if we have the 'code' key and it has a value of 3, we hit the rate limit
	if(array_key_exists('response', $playlistJSON) 
	&& array_key_exists('status', $playlistJSON['response']) 
	&& array_key_exists('code', $playlistJSON['response']['status'])
	&& ($playlistJSON['response']['status']['code'] == 3 || $playlistJSON['response']['status']['code'] == "3"))
	{
		return true;
	}
	else
	{
		return false;
	}
}

?>