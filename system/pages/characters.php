<?php
/**
 * Characters
 *
 * @package   MyAAC
 * @author    Gesior <jerzyskalski@wp.pl>
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @version   0.4.2
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');
$title = 'Characters';

require(SYSTEM . 'item.php');
$groups = new OTS_Groups_List();
function generate_search_form($autofocus = false)
{
	global $config, $twig;
	return $twig->render('characters.form.html.twig', array(
		'link' => getPageLink('characters'),
		'autofocus' => $autofocus
	));
}

function retrieve_former_name($name)
{
	global $oldName, $db;

	if(tableExist('player_namelocks') && fieldExist('name', 'player_namelocks')) {
		$newNameSql = $db->query('SELECT `name`, `new_name` FROM `player_namelocks` WHERE `name` = ' . $db->quote($name));
		if($newNameSql->rowCount() > 0) // namelocked
		{
			$newNameSql = $newNameSql->fetch();
			$oldName = ' (<small><b>Former name:</b> ' . $newNameSql['name'] . '</small>)';
			return $newNameSql['new_name'];
		}
	}

	return '';
}

$name = '';
if(isset($_REQUEST['name']))
	$name = stripslashes(ucwords(strtolower(trim($_REQUEST['name']))));

if(empty($name))
{
	$tmp_link = getPlayerLink($name);
	echo 'Here you can get detailed information about a certain player on ' . $config['lua']['serverName'] . '.<BR>';
	echo generate_search_form(true);
	return;
}

$name = str_replace('/', '', $name);

$oldName = '';

$player = new OTS_Player();
$player->find($name);
if(!$player->isLoaded())
{
	$tmp_zmienna = "";
	$tmp_name = retrieve_former_name($name);
	while(!empty($tmp_name))
	{
		$tmp_zmienna = $tmp_name;
		$tmp_name = retrieve_former_name($tmp_zmienna);
	}

	if(!empty($tmp_zmienna))
		$player->find($tmp_zmienna);
}

if($player->isLoaded() && !$player->isDeleted())
{
	$title = $player->getName() . ' - ' . $title;
	$account = $player->getAccount();
	$rows = 0;

	if($config['characters']['outfit'])
		$outfit = $config['outfit_images_url'] . '?id=' . $player->getLookType() . (fieldExist('lookaddons', 'players') ? '&addons=' . $player->getLookAddons() : '') . '&head=' . $player->getLookHead() . '&body=' . $player->getLookBody() . '&legs=' . $player->getLookLegs() . '&feet=' . $player->getLookFeet();
	
	$flag = '';
	if($config['account_country'])
		$flag = getFlagImage($account->getCustomField('country'));
	
	$player_sex = 'Unknown';
	if(isset($config['genders'][$player->getSex()]))
		$player_sex = strtolower($config['genders'][$player->getSex()]);
	
	$marriage = new OTS_Player();
	$marriage->load($player->getMarriage());
	
	$marital_status = 'single';
	if($marriage->isLoaded())
		$marital_status = 'married to ' . getPlayerLink($marriage->getName());
	
	$frags_enabled = tableExist('player_killers') && $config['characters']['frags'];
	$frags_count = 0;
	if($frags_enabled) {
		$query = $db->query(
			'SELECT COUNT(`player_id`) as `frags`' .
			'FROM `player_killers`' .
			'WHERE `player_id` = ' .$player->getId() . ' ' .
			'GROUP BY `player_id`' .
			'ORDER BY COUNT(`player_id`) DESC');
		
		if($query->rowCount() > 0)
		{
			$query = $query->fetch();
			$frags_count = $query['frags'];
		}
	}
	
	$town_field = 'town';
	if(fieldExist('town_id', 'houses'))
		$town_field = 'town_id';
	else if(fieldExist('townid', 'houses'))
		$town_field = 'townid';
	else if(!fieldExist('town', 'houses'))
		$town_field = false;
	
	if(fieldExist('name', 'houses')) {
		$house = $db->query('SELECT `id`, `paid`, `name`' . ($town_field != false ? ', `' . $town_field . '` as `town`' : '') . ' FROM `houses` WHERE `owner` = '.$player->getId())->fetch();
		if(isset($house['id']))
		{
			$add = '';
			if($house['paid'] > 0)
				$add = ' is paid until '.date("M d Y", $house['paid']);
		}
	}
	
	$rank_of_player = $player->getRank();
	if($rank_of_player->isLoaded()) {
		$guild = $rank_of_player->getGuild();
		if($guild->isLoaded()) {
			$guild_name = $guild->getName();
		}
	}
	
	$comment = $player->getComment();
	
	if($config['characters']['skills'])
	{
		if(fieldExist('skill_fist', 'players')) {// tfs 1.0+
			$skills_db = $db->query('SELECT `skill_fist`, `skill_club`, `skill_sword`, `skill_axe`, `skill_dist`, `skill_shielding`, `skill_fishing` FROM `players` WHERE `id` = ' . $player->getId())->fetch();
			
			$skill_ids = array(
				POT::SKILL_FIST => 'skill_fist',
				POT::SKILL_CLUB => 'skill_club',
				POT::SKILL_SWORD => 'skill_sword',
				POT::SKILL_AXE => 'skill_axe',
				POT::SKILL_DIST => 'skill_dist',
				POT::SKILL_SHIELD => 'skill_shielding',
				POT::SKILL_FISH => 'skill_fishing',
			);
			
			$skills = array();
			foreach($skill_ids as $skillid => $field_name) {
				$skills[] = array('skillid' => $skillid, 'value' => $skills_db[$field_name]);
			}
		}
		else {
			$skills_db = $db->query('SELECT `skillid`, `value` FROM `player_skills` WHERE `player_id` = ' . $player->getId() . ' LIMIT 7');
			$skills = $skills_db->fetchAll();
		}
		
		foreach($skills as &$skill) {
			$skill['name'] = getSkillName($skill['skillid']);
		}
	}
	
	$quests_enabled = $config['characters']['quests'] && !empty($config['quests']);
	if($quests_enabled)
	{
		$quests = $config['quests'];
		$sql_query_in = '';
		$i = 0;
		foreach($quests as $quest_name => $quest_storage)
		{
			if($i != 0)
				$sql_query_in .= ', ';
			
			$sql_query_in .= $quest_storage;
			$i++;
		}
		
		$storage_sql = $db->query('SELECT `key`, `value` FROM `player_storage` WHERE `player_id` = '.$player->getId().' AND `key` IN (' . $sql_query_in . ')');
		$player_storage = array();
		foreach($storage_sql as $storage)
			$player_storage[$storage['key']] = $storage['value'];
		
		foreach($quests as &$storage) {
			$storage = isset($player_storage[$storage]) && $player_storage[$storage] > 0;
		}
	}
	
	if($config['characters']['equipment'])
	{
		global $db;
		$eq_sql = $db->query('SELECT `pid`, `itemtype` FROM player_items WHERE player_id = '.$player->getId().' AND (`pid` >= 1 and `pid` <= 10)');
		$equipment = array();
		foreach($eq_sql as $eq)
			$equipment[$eq['pid']] = $eq['itemtype'];
		
		$empty_slots = array("", "no_helmet", "no_necklace", "no_backpack", "no_armor", "no_handleft", "no_handright", "no_legs", "no_boots", "no_ring", "no_ammo");
		for($i = 0; $i <= 10; $i++)
		{
			if(!isset($equipment[$i]) || $equipment[$i] == 0)
				$equipment[$i] = $empty_slots[$i];
		}
		
		for($i = 1; $i < 11; $i++)
		{
			if(check_number($equipment[$i]))
				$equipment[$i] = getItemImage($equipment[$i]);
			else
				$equipment[$i] = '<img src="images/items/' . $equipment[$i] . '.gif" width="32" height="32" border="0" alt=" ' . $equipment[$i] . '" />';
		}
		
		$skulls = array(
			1 => 'yellow_skull',
			2 => 'green_skull',
			3 => 'white_skull',
			4 => 'red_skull',
			5 => 'black_skull'
		);
	}

	$dead_add_content = '';
	if(tableExist('killers')) {
		$player_deaths = $db->query('SELECT `id`, `date`, `level` FROM `player_deaths` WHERE `player_id` = '.$player->getId().' ORDER BY `date` DESC LIMIT 0,10;');
		if(count($player_deaths))
		{
			$dead_add_content = '<br/><table border=0 cellspacing=1 cellpadding=4 width=100%><tr bgcolor='.$config['vdarkborder'].'><td colspan=2 class="white"><b>Character Deaths</b></td></tr>';
			
			$number_of_rows = 0;
			foreach($player_deaths as $death)
			{
				$dead_add_content .= '<tr bgcolor="'.getStyle($number_of_rows++).'">
					<td width="20%" align="center">'.date("j M Y, H:i", $death['date']).'</td>
					<td> ';
				$killers = $db->query("SELECT environment_killers.name AS monster_name, players.name AS player_name, players.deleted AS player_exists FROM killers LEFT JOIN environment_killers ON killers.id = environment_killers.kill_id
LEFT JOIN player_killers ON killers.id = player_killers.kill_id LEFT JOIN players ON players.id = player_killers.player_id
WHERE killers.death_id = '".$death['id']."' ORDER BY killers.final_hit DESC, killers.id ASC")->fetchAll();
				
				$i = 0;
				$count = count($killers);
				foreach($killers as $killer)
				{
					$i++;
					if($killer['player_name'] != "")
					{
						if($i == 1)
							$dead_add_content .= "Killed at level <b>".$death['level']."</b>";
						else if($i == $count)
							$dead_add_content .= " and";
						else
							$dead_add_content .= ",";
						
						$dead_add_content .= " by ";
						if($killer['monster_name'] != "")
							$dead_add_content .= $killer['monster_name']." summoned by ";
						
						if($killer['player_exists'] == 0)
							$dead_add_content .= getPlayerLink($killer['player_name']);
						else
							$dead_add_content .= $killer['player_name'];
					}
					else
					{
						if($i == 1)
							$dead_add_content .= "Died at level <b>".$death['level']."</b>";
						else if($i == $count)
							$dead_add_content .= " and";
						else
							$dead_add_content .= ",";
						
						$dead_add_content .= " by ".$killer['monster_name'];
					}
				}
				
				$dead_add_content .= ".</td></tr>";
			}
			
			if($number_of_rows > 0)
				$dead_add_content += '</table>';
			else
				$dead_add_content = '';
		}
	}
	else {
		$mostdamage = '';
		if(fieldExist('mostdamage_by', 'player_deaths'))
			$mostdamage = ', `mostdamage_by`, `mostdamage_is_player`, `unjustified`, `mostdamage_unjustified`';
		$deaths_db = $db->query('SELECT
				`player_id`, `time`, `level`, `killed_by`, `is_player`' . $mostdamage . '
				FROM `player_deaths`
				WHERE `player_id` = ' . $player->getId() . ' ORDER BY `time` DESC LIMIT 10;');
		
		if(count($deaths_db))
		{
			$dead_add_content = '<br/><table border=0 cellspacing=1 cellpadding=4 width=100%><tr bgcolor="'.$config['vdarkborder'].'"><td colspan="2" class="white"><b>Character Deaths</b></td></tr>';
			
			$number_of_rows = 0;
			foreach($deaths_db as $death)
			{
				$dead_add_content .= '<tr bgcolor="'.getStyle($number_of_rows++).'">
					<td width="20%" align="center">'.date("j M Y, H:i", $death['time']).'</td>
					<td> ';
				
				$lasthit = ($death['is_player']) ? getPlayerLink($death['killed_by']) : $death['killed_by'];
				$dead_add_content .=  'Killed at level ' . $death['level'] . ' by ' . $lasthit;
				if($death['unjustified']) {
					$dead_add_content .=  " <font color='red' style='font-style: italic;'>(unjustified)</font>";
				}
				
				$mostdmg = ($death['mostdamage_by'] !== $death['killed_by']) ? true : false;
				if($mostdmg)
				{
					$mostdmg = ($death['mostdamage_is_player']) ? getPlayerLink($death['mostdamage_by']) : $death['mostdamage_by'];
					$dead_add_content .=  ' and by ' . $mostdmg;
					
					if ($death['mostdamage_unjustified']) {
						$dead_add_content .=  " <font color='red' style='font-style: italic;'>(unjustified)</font>";
					}
				}
				else {
					$dead_add_content .=  " <b>(soloed)</b>";
				}
				
				$dead_add_content .= ".</td></tr>";
			}
			
			if($number_of_rows > 0)
				$dead_add_content += '</table>';
			else
				$dead_add_content = '';
		}
	}
	
	$frag_add_content = '';
	if($config['characters']['frags'])
	{
		//frags list by Xampy
		$i = 0;
		$frags_limit = 10; // frags limit to show? // default: 10
		$player_frags = $db->query('SELECT `player_deaths`.*, `players`.`name`, `killers`.`unjustified` FROM `player_deaths` LEFT JOIN `killers` ON `killers`.`death_id` = `player_deaths`.`id` LEFT JOIN `player_killers` ON `player_killers`.`kill_id` = `killers`.`id` LEFT JOIN `players` ON `players`.`id` = `player_deaths`.`player_id` WHERE `player_killers`.`player_id` = '.$player->getId().' ORDER BY `date` DESC LIMIT 0,'.$frags_limit.';');
		if(count($player_frags))
		{
			$frags = 0;
			$frag_add_content .= '<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=4 WIDTH=100%><br><TR BGCOLOR='.$config['vdarkborder'].'><TD COLSPAN=2 CLASS=white><B>Victims</B></TD></TR>';
			foreach($player_frags as $frag)
			{
				$frags++;
				$frag_add_content .= '<tr bgcolor="' . getStyle($frags) . '">
					<td width="20%" align="center">' . date("j M Y, H:i", $frag['date']) . '</td>
					<td>Fragged <a href="' . getPlayerLink($frag[name], false) . '">' . $frag[name] . '</a> at level ' . $frag[level];
				
				$frag_add_content .= ". (".(($frag['unjustified'] == 0) ? "<font size=\"1\" color=\"green\">Justified</font>" : "<font size=\"1\" color=\"red\">Unjustified</font>").")</td></tr>";
			}
			
			if($frags > 0)
				$frag_add_content += '</table>';
			else
				$frag_add_content = '';
		}
	}
	
	// signature
	if($config['signature_enabled']) {
		$signature_url = BASE_URL . 'tools/signature/?name=' . urlencode($player->getName());
		if($config['friendly_urls'])
			$signature_url = BASE_URL . urlencode($player->getName()) . '.png';
	}
	
	$hidden = $player->getCustomField('hidden');
	if($hidden != 1) {
		// check if account has been banned
		$bannedUntil = '';
		$banned = array();
		if (tableExist('account_bans'))
			$banned = $db->query('SELECT `expires_at` as `expires` FROM `account_bans` WHERE `account_id` = ' . $account->getId() . ' and `expires_at` > ' . time());
		else if (tableExist('bans')) {
			if (fieldExist('expires', 'bans'))
				$banned = $db->query('SELECT `expires` FROM `bans` WHERE (`value` = ' . $account->getId() . ' or `value` = ' . $player->getId() . ') and `active` = 1 and `type` != 2 and `type` != 4 and `expires` > ' . time());
			else
				$banned = $db->query('SELECT `time` as `time` FROM `bans` WHERE (`account` = ' . $account->getId() . ' or `player` = ' . $player->getId() . ') and `type` != 2 and `type` != 4 and `time` > ' . time());
		}
		foreach ($banned as $ban) {
			$bannedUntil = ' <font color="red">[Banished ' . ($ban['expires'] == "-1" ? 'forever' : 'until ' . date("d F Y, h:s", $ban['expires'])) . ']</font>';
		}
		
		$account_players = $account->getPlayersList();
		$account_players->orderBy('name');
	}
	
	echo $twig->render('characters.html.twig', array(
		'outfit' => isset($outfit) ? $outfit : null,
		'player' => $player,
		'account' => $account,
		'flag' => $flag,
		'oldName' => $oldName,
		'sex' => $player_sex,
		'marriage_enabled' => $config['characters']['marriage_info'] && fieldExist('marriage', 'players'),
		'marital_status' => $marital_status,
		'vocation' => $config['vocations'][$player->getVocation()],
		'frags_enabled' => $frags_enabled,
		'frags_count' => $frags_count,
		'town' => isset($config['towns'][$player->getTownId()]) ? $config['towns'][$player->getTownId()] : null,
		'house' => array(
			'found' => isset($house['id']),
			'add' => isset($house['id']) ? $add : null,
			'name' => isset($house['id']) ? (isset($house['name']) ? $house['name'] : $house['id']) : null,
			'town' => isset($house['town']) ? ' (' . $config['towns'][$house['town']] . ')' : ''
		),
		'guild' => array(
			'rank' => isset($guild_name) ? $rank_of_player->getName() : null,
			'link' => isset($guild_name) ? getGuildLink($guild_name) : null
		),
		'comment' => !empty($comment) ? wordwrap(nl2br($comment), 60, "<br/>", true) : null,
		'skills' => isset($skills) ? $skills : null,
		'quests_enabled' => $quests_enabled,
		'quests' => isset($quests) ? $quests : null,
		'equipment' => isset($equipment) ? $equipment : null,
		'skull' => $player->getSkullTime() > 0 && ($player->getSkull() == 4 || $player->getSkull() == 5) ? $skulls[$player->getSkull()] : null,
		'deaths' => $dead_add_content,
		'frags' => $frag_add_content,
		'signature_url' => isset($signature_url) ? $signature_url : null,
		'player_link' => getPlayerLink($player->getName(), false),
		'hidden' => $hidden,
		'bannedUntil' => isset($bannedUntil) ? $bannedUntil : null,
		'characters_link' => internalLayoutLink('characters'),
		'account_players' => isset($account_players) ? $account_players : null,
		'search_form' => generate_search_form()
	));
}
else
{
	$search_errors[] = 'Character <b>' . $name . '</b> does not exist or has been deleted.';
	echo $twig->render('error_box.html.twig', array('errors' => $search_errors));
	$search_errors = array();

	$promotion = '';
	if(fieldExist('promotion', 'players'))
		$promotion = ', `promotion`';

	$deleted = 'deleted';
	if(fieldExist('deletion', 'players'))
		$deleted = 'deletion';

	$query = $db->query('SELECT `name`, `level`, `vocation`' . $promotion . ' FROM `players` WHERE `name` LIKE  ' . $db->quote('%' . $name . '%') . ' AND ' . $deleted . ' != 1;');
	if($query->rowCount() > 0)
	{
		echo 'Did you mean:<ul>';
		foreach($query as $player) {
			if(isset($player['promotion'])) {
				if((int)$player['promotion'] > 0)
					$player['vocation'] += ($player['promotion'] * $config['vocations_amount']);
			}
			echo '<li>' . getPlayerLink($player['name']) . ' (<small><strong>level ' . $player['level'] . ', ' . $config['vocations'][$player['vocation']] . '</strong></small>)</li>';
		}
		echo '</ul>';
	}

	echo generate_search_form(true);
}

if(!empty($search_errors))
	echo $twig->render('error_box.html.twig', array('errors' => $search_errors));