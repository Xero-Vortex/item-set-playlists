<?php
// this file should define the API keys we need
require_once('inc/config.php');
require_once('inc/general_helper.php');


// define some constants
// regions
define('REGION_NA', 'na');
define('REGION_BR', 'br');
define('REGION_EUNE', 'eune');
define('REGION_EUW', 'euw');
define('REGION_KR', 'kr');
define('REGION_LAN', 'lan');
define('REGION_LAS', 'las');
define('REGION_OCE', 'oce');
define('REGION_TR', 'tr');
define('REGION_RU', 'ru');

// stat modifier names from the static item data from Riot
define('STAT_AD', 'FlatPhysicalDamageMod');
define('STAT_ATKSPD', 'PercentAttackSpeedMod');
define('STAT_CRIT', 'FlatCritChanceMod');
define('STAT_LIFESTEAL', 'PercentLifeStealMod');
define('STAT_AP', 'FlatMagicDamageMod');
define('STAT_ARMOR', 'FlatArmorMod');
define('STAT_MR', 'FlatSpellBlockMod');
define('STAT_HP', 'FlatHPPoolMod');
define('STAT_MP', 'FlatMPPoolMod');
define('STAT_GOLDGEN', 'GoldBase');
define('STAT_AURAS', '<aura>');
define('STAT_FLATMOVESPD', 'FlatMovementSpeedMod');
define('STAT_PERCENTMOVESPD', 'PercentMovementSpeedMod');


// play styles that we'll parse based on the item set given
define('STYLE_ADC', 'adc');
define('STYLE_ASSASSIN_AD', 'assassin_ad');
define('STYLE_ASSASSIN_AP', 'assassin_ap');
define('STYLE_TANK', 'tank');
define('STYLE_SUPPORT', 'support');
define('STYLE_FIGHTER', 'fighter');
define('STYLE_TROLL', 'troll');


// item set traits, which include css classes
define('TRAIT_TROLLFEW', '<span class=\"trait-troll-few\">thinking you are so good that you only need very few items</span>');
define('TRAIT_TROLLMANY', '<span class=\"trait-troll-many\">not knowing which items to use and adding a bunch to the item set (just in case)</span>');
define('TRAIT_GOLDGEN', '<span class=\"trait-gold-gen\">gold generation</span>');
define('TRAIT_AURAS', '<span class=\"trait-auras\">auras</span>');

define('TRAIT_ARMORSOME', '<span class=\"trait-armor-some\">some armor</span>');
define('TRAIT_ARMORHIGH', '<span class=\"trait-armor-high\">high armor</span>');
define('TRAIT_ARMORTONS', '<span class=\"trait-armor-tons\">tons of armor</span>');

define('TRAIT_MRSOME', '<span class=\"trait-mr-some\">some magic resist</span>');
define('TRAIT_MRHIGH', '<span class=\"trait-mr-high\">high magic resist</span>');
define('TRAIT_MRTONS', '<span class=\"trait-mr-tons\">tons of magic resist</span>');

define('TRAIT_HPHIGH', '<span class=\"trait-hp-high\">high health</span>');
define('TRAIT_HPTONS', '<span class=\"trait-hp-tons\">tons of health</span>');

define('TRAIT_ADHIGH', '<span class=\"trait-ad-high\">high AD</span>');
define('TRAIT_ADTONS', '<span class=\"trait-ad-tons\">tons of AD</span>');

define('TRAIT_APHIGH', '<span class=\"trait-ap-high\">high AP</span>');
define('TRAIT_APTONS', '<span class=\"trait-ap-tons\">tons of AP</span>');

define('TRAIT_CRITHIGH', '<span class=\"trait-crit-high\">high critical strike chance</span>');

define('TRAIT_ASHIGH', '<span class=\"trait-as-high\">high attack speed</span>');
define('TRAIT_ASTONS', '<span class=\"trait-as-tons\">tons of attack speed</span>');

define('TRAIT_LSHIGH', '<span class=\"trait-ls-high\">high life steal</span>');
define('TRAIT_LSTONS', '<span class=\"trait-ls-tons\">tons of life steal</span>');

define('TRAIT_GENERIC', '<span class=\"trait-generic\">being a generic fighter</span>');

define('TRAIT_MSHIGH', '<span class=\"trait-ms-high\">high movement speed</span>');
define('TRAIT_MSTONS', '<span class=\"trait-ms-tons\">tons of movement speed</span>');

// global variable to store the static item data
$staticItemData = array();

function getLeagueAPIKey()
{
	if(defined('LEAGUEAPIKEY'))
	{
		return LEAGUEAPIKEY;
	}
	else
	{
		return null;
	}
}

function getRegions()
{
	// returning an array with all the regions, so we can check that we got a valid region input
	$rgn[REGION_NA] = 1;
	$rgn[REGION_BR] = 1;
	$rgn[REGION_EUNE] = 1;
	$rgn[REGION_EUW] = 1;
	$rgn[REGION_KR] = 1;
	$rgn[REGION_LAN] = 1;
	$rgn[REGION_LAS] = 1;
	$rgn[REGION_OCE] = 1;
	$rgn[REGION_TR] = 1;
	$rgn[REGION_RU] = 1;
	
	return $rgn;
}

function isValidRegion($region = '')
{
	// check if the given region is in the list of valid regions
	return array_key_exists($region, getRegions());
}


/*
 returns an associative array with the array IDs being the item IDs, and an array of the item info being the value
*/
// https://global.api.pvp.net/api/lol/static-data/na/v1.2/item?itemListData=stats&api_key=
function getStaticItemInfo($region = '')
{
	if($region != '' && isValidRegion($region))
	{
		// we want to get stat, tag, and image data for all items
		$url = 'https://global.api.pvp.net/api/lol/static-data/' . $region . '/v1.2/item?itemListData=image,stats,tags&api_key=' . getLeagueAPIKey();
		
		//error_log('In getPlayerInfo. name: ' . $name . ', cleanName: ' . $cleanName . ', region: ' . $region . ', url: ' .$url);
		
		$rawItemData = getJSONObject($url);
		
		return $rawItemData['data'];
	}
	
	// need a name and region but didn't get at least one, returning null
	return null;
}


function getItemSetTotalStats($itemSetArray = '', $region = '')
{
	if($itemSetArray != '' && $region != '' && isValidRegion($region))
	{
		// check if the global variable is already filled
		global $staticItemData;
		
		// if we don't have the static item data yet, get it
		if(empty($staticItemData))
		{
			$staticItemData = getStaticItemInfo($region);
		}
		
		// alright, we have the static item data, let's add up all the stats in the item set
		// we still check to see if the variable is empty because I hit an issue once where it seems like
		// the Riot API didn't return the 'data' element when called in getStaticItemInfo
		if(!empty($staticItemData))
		{
			$stats = array();
			
			// keep track of how many items are in the item setcookie
			$stats['totalItemCount'] = 0;
			
			// first we loop through each blocks in the item set
			for($b = 0; $b < count($itemSetArray['blocks']); $b++)
			{
				// next we loop through each item in the block
				for($i = 0; $i < count($itemSetArray['blocks'][$b]['items']); $i++)
				{
					$itemID = $itemSetArray['blocks'][$b]['items'][$i]['id'];
					$itemCount = $itemSetArray['blocks'][$b]['items'][$i]['count'];
					
					// keep counting the items
					$stats['totalItemCount'] = $stats['totalItemCount'] + $itemCount;
					
					// loop through each stat for the current item
					foreach($staticItemData[$itemID]['stats'] as $key => $value)
					{
						// add the stats to the existing stat total, taking into account the count of the item
						if(!isset($stats[$key]))
						{
							$stats[$key] = $value * $itemCount;
						}
						else
						{
							$stats[$key] = $stats[$key] + ($value * $itemCount);
						}
					}
					
					// now we'll check for special case 'stats' that we are interested in
					// gold generation: count the number of gold gen items (not amount of gold per time)
					if(array_key_exists('group', $staticItemData[$itemID]) 
						&& $staticItemData[$itemID]['group'] == STAT_GOLDGEN)
					{
						if(!isset($stats[STAT_GOLDGEN]))
						{
							$stats[STAT_GOLDGEN] = $itemCount;
						}
						else
						{
							$stats[STAT_GOLDGEN] = $stats[STAT_GOLDGEN] + $itemCount;
						}
					}
					
					// aura: count the number of aura items (determined by having the <aura></aura> tag in the description field)
					if(array_key_exists('description', $staticItemData[$itemID]) 
						&& strpos($staticItemData[$itemID]['description'], STAT_AURAS) !== false)
					{
						if(!isset($stats[STAT_AURAS]))
						{
							$stats[STAT_AURAS] = $itemCount;
						}
						else
						{
							$stats[STAT_AURAS] = $stats[STAT_AURAS] + $itemCount;
						}
					}
				}
			}
		}
		
		//error_log('In getItemSetTotalStats. stats: ' . print_r($stats, true));
		return $stats;
	}
	
	// we need all those variables, so if we don't have one return null
	return null;
}


function getItemSetInfoFromItemSetStats($itemStats)
{
	// this is where we make a guess of what play style the player's item set is for
	// these values for the stats are somewhat arbitrary, based on looking at some of my own item sets
	
	/*
	Troll: too few or too many items
	Support: gold generation, aura, armor/MR
	ADC: AD, atk spd, crit, not too much defense
	Tank: high armor, MR, HP, not much AD or AP
	Fighter: some AD/AP and some armor/MR
	Assassin: high AD or high AP
	*/

	// load the stats we are looking at into easy variables
	$AD = getStat($itemStats, STAT_AD);
	$crit = getStat($itemStats, STAT_CRIT);
	$atkSpd = getStat($itemStats, STAT_ATKSPD);
	$lifesteal = getStat($itemStats, STAT_LIFESTEAL);
	$AP = getStat($itemStats, STAT_AP);
	$armor = getStat($itemStats, STAT_ARMOR);
	$MR = getStat($itemStats, STAT_MR);
	$HP = getStat($itemStats, STAT_HP);
	$MP = getStat($itemStats, STAT_MP);
	$goldGen = getStat($itemStats, STAT_GOLDGEN);
	$auras = getStat($itemStats, STAT_AURAS);
	$flatMoveSpd = getStat($itemStats, STAT_FLATMOVESPD);
	$percentMoveSpd = getStat($itemStats, STAT_PERCENTMOVESPD);
	$itemCount = getStat($itemStats, 'totalItemCount');
	
	// create an object that we're going to return
	$ret = array();
	$ret['play-style'] = null;
	$ret['traits'] = array();
	$ret['otherMods'] = array();
	
	
	// check for the item set having too few or too many items (aka the Troll item set)
	if(
		$itemCount <= 3 || $itemCount >= 30
	)
	{
		$ret['play-style'] = STYLE_TROLL;
		
		if($itemCount <= 3) { $ret['traits'][] = TRAIT_TROLLFEW; }
		if($itemCount >= 30) { $ret['traits'][] = TRAIT_TROLLMANY; }
	}
	// start with Support so the gold gen items and auras don't get overshadowed by other criteria (like high AP)
	// Support: gold generation, aura (CDR, mana?)
	else if(
		($goldGen + $auras >= 4)
		|| ($goldGen + $auras >= 2 && $armor + $MR >= 200)
	)
	{
		$ret['play-style'] = STYLE_SUPPORT;
		
		if($goldGen >= 1) { $ret['traits'][] = TRAIT_GOLDGEN; }
		if($auras >= 1) { $ret['traits'][] = TRAIT_AURAS; }
		if($armor >= 1) { $ret['traits'][] = TRAIT_ARMORSOME; }
		if($MR >= 1) { $ret['traits'][] = TRAIT_MRSOME; }
	}
	// ADC: AD, attack speed, critical hit chance, not too much defense
	else if(
		(
			($AD >= 200 && $crit >= .4 && $atkSpd >= .5)
			|| ($AD >= 150 && $crit >= .4 && $atkSpd >= .75)
		)
		&&
		(
			!($armor + $MR >= 200)
		)
	)
	{
		$ret['play-style'] = STYLE_ADC;
		
		if($AD >= 200) { $ret['traits'][] = TRAIT_ADTONS; }
		else if($AD >= 150) { $ret['traits'][] = TRAIT_ADHIGH; }
		$ret['traits'][] = TRAIT_CRITHIGH;
		if($atkSpd >= .75) { $ret['traits'][] = TRAIT_ASTONS; }
		else if($atkSpd >= .75) { $ret['traits'][] = TRAIT_ASHIGH; }
	}
	// Tank: high armor, MR, HP, not much AD or AP
	else if(
		(
			($armor + $MR >= 400 && $HP >= 400)
			|| ($armor + $MR >= 200 && $HP >= 1000)
		)
		&&
		!($AD + $AP > 150)
	)
	{
		$ret['play-style'] = STYLE_TANK;
		
		if($armor >= 200) { $ret['traits'][] = TRAIT_ARMORTONS; }
		else if($armor >= 100) { $ret['traits'][] = TRAIT_ARMORHIGH; }
		if($MR >= 200) { $ret['traits'][] = TRAIT_MRTONS; }
		else if($MR >= 100) { $ret['traits'][] = TRAIT_MRHIGH; }
		if($HP >= 1000) { $ret['traits'][] = TRAIT_HPTONS; }
		else if($HP >= 400) { $ret['traits'][] = TRAIT_HPHIGH; }
	}
	// Fighter: some AD/AP and some Armor/MR
	else if (
		($AD + $AP >= 150 && $armor + $MR >= 150)
		|| ($AD >= 100 && $lifesteal >= .25 && $armor + $MR >= 150)
		
	)
	{
		$ret['play-style'] = STYLE_FIGHTER;
		
		if($AD >= 200) { $ret['traits'][] = TRAIT_ADTONS; }
		else if($AD >= 150) { $ret['traits'][] = TRAIT_ADHIGH; }
		if($AP >= 200) { $ret['traits'][] = TRAIT_APTONS; }
		else if($AP >= 150) { $ret['traits'][] = TRAIT_APHIGH; }
		if($lifesteal >= .5) { $ret['traits'][] = TRAIT_LSTONS; }
		else if($lifesteal >= .25) { $ret['traits'][] = TRAIT_LSHIGH; }
		if($armor >= 200) { $ret['traits'][] = TRAIT_ARMORTONS; }
		else if($armor >= 100) { $ret['traits'][] = TRAIT_ARMORHIGH; }
		if($MR >= 200) { $ret['traits'][] = TRAIT_MRTONS; }
		else if($MR >= 100) { $ret['traits'][] = TRAIT_MRHIGH; }
		if($HP >= 1000) { $ret['traits'][] = TRAIT_HPTONS; }
		else if($HP >= 400) { $ret['traits'][] = TRAIT_HPHIGH; }
	}
	// Assassin - AP: high AP
	else if($AP >= 400)
	{
		$ret['play-style'] = STYLE_ASSASSIN_AP;
		
		if($AP >= 400) { $ret['traits'][] = TRAIT_APTONS; }
	}
	// Assassin - AD: high AD
	else if($AD >= 400)
	{
		$ret['play-style'] = STYLE_ASSASSIN_AD;
		
		if($AD >= 400) { $ret['traits'][] = TRAIT_ADTONS; }
	}
	// otherwise we'll just return the 'fighter' style, though we shouldn't really ever get here
	else
	{
		$ret['play-style'] = STYLE_FIGHTER;
		
		if($AD >= 200) { $ret['traits'][] = TRAIT_ADTONS; }
		else if($AD >= 150) { $ret['traits'][] = TRAIT_ADHIGH; }
		if($AP >= 200) { $ret['traits'][] = TRAIT_APTONS; }
		else if($AP >= 150) { $ret['traits'][] = TRAIT_APHIGH; }
		if($lifesteal >= .5) { $ret['traits'][] = TRAIT_LSTONS; }
		else if($lifesteal >= .25) { $ret['traits'][] = TRAIT_LSHIGH; }
		if($armor >= 200) { $ret['traits'][] = TRAIT_ARMORTONS; }
		else if($armor >= 100) { $ret['traits'][] = TRAIT_ARMORHIGH; }
		if($MR >= 200) { $ret['traits'][] = TRAIT_MRTONS; }
		else if($MR >= 100) { $ret['traits'][] = TRAIT_MRHIGH; }
		if($HP >= 1000) { $ret['traits'][] = TRAIT_HPTONS; }
		else if($HP >= 400) { $ret['traits'][] = TRAIT_HPHIGH; }
	}
	
	// if we don't have any traits at this point, we'll give it something generic
	if(count($ret['traits']) == 0)
	{
		$ret['traits'][] = TRAIT_GENERIC;
	}
	
	// check for other stats that we'll use to add modifiers to the playlist query, regardless of the mood
	// move speed
	$moveSpdVal = '';
	
	if($flatMoveSpd >= 200 || ($percentMoveSpd > .1 && $flatMoveSpd >= 100))
	{
		$moveSpdVal = 'tons';
		$ret['traits'][] = TRAIT_MSTONS;
	}
	else if($flatMoveSpd >= 100 || ($percentMoveSpd > .1 && $flatMoveSpd >= 50))
	{
		$moveSpdVal = 'high';
		$ret['traits'][] = TRAIT_MSHIGH;
	}
	
	if($moveSpdVal !== '')
	{
		$ret['otherMods']['moveSpd'] = $moveSpdVal;
	}
	
	return $ret;
}


function getEchoNestAPIQueryParamters($itemSetInfo = '')
{
	if($itemSetInfo !== '')
	{
		$queryParameters = '';
	
		switch($itemSetInfo['play-style'])
		{
			case STYLE_ADC:
				$queryParameters = '&mood=aggressive';
				break;
			case STYLE_ASSASSIN_AP:
				$queryParameters = '&mood=dramatic&mood=intense';
				break;
			case STYLE_ASSASSIN_AD:
				$queryParameters = '&mood=mystical&mood=intense';
				break;
			case STYLE_TANK:
				$queryParameters = '&mood=epic';
				break;
			case STYLE_SUPPORT:
				$queryParameters = '&mood=mellow';
				break;
			case STYLE_FIGHTER:
				$queryParameters = '&mood=rowdy';
				break;
			case STYLE_TROLL:
				// we'll pick a random mood from some troll-appropriate moods				
				$trollMoods = ['trippy', 'whimsical', 'funky', 'hypnotic'];
				
				$mood = $trollMoods[rand(0, count($trollMoods) - 1)];
				
				$queryParameters = '&mood=' . $mood;
				break;
			default:
				return null;
		}
		
		// check for other stats that we'll use to add modifiers to the playlist query, regardless of the mood
		if($queryParameters != '' && array_key_exists('moveSpd', $itemSetInfo['otherMods']))
		{
			$moveSpd = $itemSetInfo['otherMods']['moveSpd'];
			
			if($moveSpd == 'tons')
			{
				$queryParameters .= '&min_tempo=170';
			}
			else if($moveSpd == 'high')
			{
				$queryParameters .= '&min_tempo=150';
			}
		}
		
		return $queryParameters;
	}
	
	return null;
}


function getStat($itemStats, $statName)
{
	if(array_key_exists($statName, $itemStats))
	{
		return $itemStats[$statName];
	}
	else
	{
		return 0;
	}
}


function getItemsFromItemSet($itemSetArray = '', $region = '')
{
	if($itemSetArray != '' && $region != '' && isValidRegion($region))
	{
		// check if the global variable is already filled
		global $staticItemData;
		
		// if we don't have the static item data yet, get it
		if(empty($staticItemData))
		{
			$staticItemData = getStaticItemInfo($region);
		}
		
		// alright, we have the static item data, let's add up all the stats in the item set
		// we still check to see if the variable is empty because I hit an issue once where it seems like
		// the Riot API didn't return the 'data' element when called in getStaticItemInfo
		if(!empty($staticItemData))
		{
			$items = array();
			
			// first we loop through each blocks in the item set
			for($b = 0; $b < count($itemSetArray['blocks']); $b++)
			{
				// next we loop through each item in the block
				for($i = 0; $i < count($itemSetArray['blocks'][$b]['items']); $i++)
				{
					$itemID = $itemSetArray['blocks'][$b]['items'][$i]['id'];
					$itemCount = $itemSetArray['blocks'][$b]['items'][$i]['count'];
					
					$items[] = $staticItemData[$itemID];
				}
			}
		}
		
		return $items;
	}
	
	// we need all those variables, so if we don't have one return null
	return null;
}


function getFullItemImageURL($itemID)
{
	if($itemID !== '')
	{
		// check if the global variable is already filled
		global $staticItemData;
		
		// if we don't have the static item data yet, get it
		if(empty($staticItemData))
		{
			$staticItemData = getStaticItemInfo($region);
		}
	
		//error_log('In getFullItemImageURL. $itemID: ' . print_r($itemID, true) . ', $staticItemData[$itemID]: ' . print_r($staticItemData[$itemID], true));
	
		// grab the image file name from the static item data
		$imageName = $staticItemData[$itemID]['image']['full'];
	
		return 'http://ddragon.leagueoflegends.com/cdn/' . getLeagueContentVersion() . '/img/item/' . $imageName;
	}
	return null;
}


function getLeagueContentVersion()
{
	// this is for the version of the game that we want to grab content for
	// yep, it's currently hardcoded
	return '5.16.1';
}


function getItemSetItemListHTML($items = '')
{
	if($items != '')
	{
		$itemHTML = '<ul>';
	
		// loop through each stat for the current item
		foreach($items as $item)
		{
			$itemHTML .= '<li>';
			
			$itemHTML .= $item['name'];
			
			if(count($item['stats'] > 0))
			{
				$itemHTML .= '<ul>';
				
				foreach($item['stats'] as $key => $value)
				{
					$itemHTML .= '<li>' . $key . ': ' . $value . '</li>';
				}
				
				$itemHTML .= '</ul>';
			}
			
			$itemHTML .= '</li>';
		}
		
		$itemHTML .= '</ul>';
		
		return $itemHTML;
	}
	
	// we need all those variables, so if we don't have one return null
	return null;
}


?>