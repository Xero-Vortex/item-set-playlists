# Item Set Playlists

Check out the live site and get your own playlist: [Item Set Playlists](http://star-xvcode.rhcloud.com/item_set_playlists/)

Item Set Playlists is an application that was created for the Riot Games API Challenge 2.0, which ran from 8/10-8/31. It that takes League of Legends custom item set data that the user uploads and creates a musical playlist that compliments the calculated play style of the item set.

The idea of the application is that users will upload an item set that they are planning to use in game and then listen to the playlist that is created while playing the match. The playlists created are meant to compliment the playstyle of the given item set; for example, the aggression of an ADC, or the feeling of being on an island while playing a bruiser up top. I might be kidding about that second one ;)

# Technical Info
This application is fairly simple technically. It uses PHP on the server and javascript, html, and css on the client side. There is no need for database storage as everything is calculated when an item set is uploaded.

## Languages, Libraries, and Server
* PHP 5.4
* jQuery 1.11.3
* [Vague.js](https://github.com/GianlucaGuarini/vague.js/) (for blurring effect)
* Bootstrap v3.3.4
* html/javascript/css
* Application hosting using a free [OpenShift](https://www.openshift.com/) account

## Requirements
### Riot Games API Key
The Riot Games API key is used to get item data (stats, images, and so on). To get a Riot Games API key, follow the steps here: https://developer.riotgames.com/docs/getting-started

### Echo Nest API Key
The Echo Nest API key is used to get the song information that is used to create the Spotify playlist. To get an Echo Nest API key, create an account here: https://developer.echonest.com/account/register

### Spotify Account
A free or premium Spotify account is required to listen to the playlist of songs that is generated.

## Installation
To install this application, upload the code to a PHP server and enter your Riot Games API key and Echo Nest API key in the /inc/config.php file.

## Application Structure
The application is a one-page site that uses AJAX to send item set data and get the song information back, then creates a Spotify Play Button widget. The most notable files are:
* `index.php` - The entry point for the application. This is the 'one page' of the site.
* `get_itemset_playlist.php` - This is the main backbone of the application. It is a web service that expects to be POSTed a JSON object that is a valid League of Legends custom item set. If a valid item set was POSTed, the service returns a JSON object that contains track IDs to be used with the Spotify Play Button widget, play style info, and traits detected in the item set (tons of AD, high armor, and so on).
* `inc/config.php` - This file defines PHP constants for the Riot Games API key and Echo Nest API key. You **must** add your own API keys to this file before the application will work.
* `inc/league_helper.php` - This file handles all the League-related logic that is performed server-side. This includes getting data from the Riot Games API, calculating a play style from a given item set, assigning musical moods to play styles, and so on. If it's related to League data specifically, it's probably in this file.
* `inc/echonest_helper.php` - Like the League helper, this file covers all Echo Nest needs. This includes building the query to the Echo Nest API and pulling out the Spotify track IDs from the response. The parameters for the type of music that is return can be tweaked here.
* `inc/general_helper.php` - Similar to the other helpers, but for general functionality. This mostly contains functions to get a JSON object from a given URL that returns JSON data.
* `inc/page_components.php` - Contains some common html code for the site, such as the header, footer, and javascript and css include statements. While this isn't too useful right now since the application is a one-page site, if it grows these common sections won't need to be re-written.
* `js/itemset-playlist.js` - This file contains all the client-side javascript code. It handles the AJAX request to the web service, as well as all the buttons and link handlers.

## Application Flow Outline
The following is an overview of how the application executes when given the correct data:

1. User drags and drops a custom item set JSON file into the designated drop area
2. Client-side javascript reads the file and puts its contents into the textarea
3. The user clicks the 'Get Playlist' button
4. Client-side javascript validates the item set in the textarea and converts the text to a JSON object
5. Client-side javascript sends an AJAX request with the item set JSON object to `get_itemset_playlist.php`
6. On the server, stats are totalled for all the items in the item set
7. Based on the stats, a play style and item set traits (tons of AD, high armor, and so on) are calculated
8. From the play style and traits, a query to the Echo Nest API is formed and sent, based on the calculated 'mood' of the given item set
9. Echo Nest returns a JSON response that contains the Spotify track IDs for each song in the playlist
10. The track IDs are put into a comma-separated list and added to the `get_itemset_playlist.php` response
11. `get_itemset_playlist.php` returns a JSON string that contains the calculated play style and traits of the item set and the track list
12. The client-side javascript validates the returned JSON string and converts it to a JSON object
13. The client-side javascript displays the item set name and traits, and a randomly selected item from the item set as a blurred background image
14. The client-side javascript then loads the Spotify Play Button (which doesn't require an API key!) with the given track IDs, in an iframe
15. Now the user has an awesome custom playlist based on their item set!

## Thoughts on Echo Nest
I love music, so when I learned about the Echo Nest API, I was quite interested in it. With the Echo Nest API you can query all sorts of metadata on songs, artists, and more. When I first found out about it I didn't have any specific application in which I wanted to use, but I kept it in mind. When I found out about the Riot Games API Challenge 2.0, it seemed like a great time to experiment with the Echo Nest API, and thus Item Set Playlists was born!

Exploring how query parameters affected the songs that Echo Nest returned was quite interesting. The most essential query parameter to this application is the 'mood'. The idea is that I can specify a mood for a certain play style, which is better than picking a specific genre since not everyone enjoys the same genres of music. This would also hopefully give a good mix of music types, so if the player doesn't like a specific song they can skip it and continue listening to the other songs in the playlist.

I found that specifying just mood didn't work too well for what I wanted. I'm guessing that the tagging of moods is an automated process (though I haven't researched this), so not all tracks returned for moods actually match the mood. For example, I had the most trouble with the 'aggressive' mood, which is used for the ADC play style. I found my 'aggressive' playlists containing tracks from comedians, artists I wouldn't consider 'aggressive' like Ike & Tina Turner, and blues and jazz tracks. I spent a bit of time researching the Echo Nest API (there's a lot to it!) to try to find ways to prevent these types of tracks being returned. One query parameter, called 'description', seems to be a generic description of the song. I used this query parameter to tell Echo Nest that I don't want tracks with a description of 'comedy', 'blues', or 'jazz'. This worked in getting rid of the comedy, blue, and jazz tracks, but I was still getting Ike & Tina Turner. Not really sure what description I'd use to blacklist Ike & Tina Turner, I decided to limit the returned tracks to those in which the artist had a start year after 1990. This might be a bit limiting and not really what I was aiming to limit originally, but it did also get rid of the Ike & Tina Turner tracks.

I'm pretty happy with the tracks that are returned now. There's even a special play style that returns some really odd tracks. I'll leave that for you to find ;)