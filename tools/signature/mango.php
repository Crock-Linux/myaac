<?php
	defined('MYAAC') or die('Direct access not allowed!');

	/**
	ALTER TABLE `players` ADD `madphp_signature` TINYINT( 4 ) NOT NULL DEFAULT '1' COMMENT 'Absolute Mango � MadPHP.org', ADD `madphp_signature_bg` VARCHAR( 50 ) NOT NULL COMMENT 'Absolute Mango � MadPHP.org' AFTER `madphp_signature`, ADD `madphp_signature_eqs` TINYINT( 4 ) NOT NULL DEFAULT '0' COMMENT 'Absolute Mango � MadPHP.org' AFTER `madphp_signature_bg`, ADD `madphp_signature_bars` TINYINT( 4 ) NOT NULL DEFAULT '1' COMMENT 'Absolute Mango � MadPHP.org' AFTER `madphp_signature_eqs`, ADD `madphp_signature_cache` INT( 11 ) NOT NULL COMMENT 'Absolute Mango � MadPHP.org' AFTER `madphp_signature_bars`;
	**/

	/** Load the MadGD class **/
	require( 'gd.class.php' );
	/** Default values **/
	list( $i, $eachRow, $percent ) = array( .5, 14, array( 'size' => 7 ) );
	/** Get experience points for a certain level **/
	function getExpToLevel( $level )
	{
		return ( 50 / 3 ) * pow( $level, 3 ) - ( 100 * pow( $level, 2 ) ) + ( ( 850 / 3 ) * $level ) - 200;
	}
	/** Definitions **/
	define( 'SIGNATURES', 'signatures/' );
	define( 'SIGNATURES_BACKGROUNDS', 'images/backgrounds/' );
	define( 'SIGNATURES_CACHE', BASE . 'tmp/signatures/' );
	define( 'SIGNATURES_DATA', SYSTEM . 'data/' );
	define( 'SIGNATURES_FONTS', 'fonts/' );
	define( 'SIGNATURES_IMAGES', 'images/' );
	define( 'SIGNATURES_ITEMS', BASE . 'images/items/' );

	/** Sprite settings **/
	$spr_path = SIGNATURES_DATA.'Tibia.spr';
	$dat_path = SIGNATURES_DATA.'Tibia.dat';
	$otb_path = SIGNATURES_DATA.'items.otb';

	$name = stripslashes( isset( $_GET['name'] ) ? $_GET['name'] : '-' );
	$player = $ots->createObject( 'Player' );
	$player->find( $name );

	if ( function_exists( 'imagecreatefrompng' ) )
	{
		if ( $player->isLoaded( ) )
		{
			$enabled = $player->getCustomField( 'madphp_signature' );
			$bars = $player->getCustomField( 'madphp_signature_bars' );
			$equipments = $player->getCustomField( 'madphp_signature_eqs' );
			$background = ( $player->getCustomField( 'madphp_signature_bg' ) == '' ? 'default.png' : $player->getCustomField( 'madphp_signature_bg' ) );
			$file = SIGNATURES_CACHE.$player->getId().'.png';
			if ( $enabled == 1 )
			{
				if ( file_exists( $file ) and ( time( ) < ( filemtime($file) + ( 60 * $cacheMinutes ) ) ) )
				{
					header( 'Content-type: image/png' );
					readfile( SIGNATURES_CACHE.$player->getId().'.png' );
				}
				else
				{
					if ( file_exists( SIGNATURES_BACKGROUNDS.$background ) )
					{
						$MadGD = new MadGD( SIGNATURES_BACKGROUNDS.$background );
						$MadGD->testMode = false;

						$MadGD->setDefaultStyle( SIGNATURES_FONTS.'arial.ttf', SIGNATURES_FONTS.'arialbd.ttf', 8 );
						$MadGD->setEquipmentBackground( SIGNATURES_IMAGES.'equipments.png' );

						/** NAME **/
						$MadGD->addText( 'Name:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $player->getName(), ( $player->isOnline() ? array( 'color' => '5df82d' ) : array( ) ) )->setPosition( ); $i++;
						/** SEX **/
						$MadGD->addText( 'Sex:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $player->getSex() == 1 ? 'male' : 'female' )->setPosition( ); $i++;
						/** PROFESSION **/
						$MadGD->addText( 'Profession:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $vocation_name[$player->getWorldId()][$player->getPromotion()][$player->getVocation()] )->setPosition( ); $i++;
						/** LEVEL **/
						$MadGD->addText( 'Level:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $player->getLevel() )->setPosition( ); $i++;
						/** WORLD **/
						$MadGD->addText( 'World:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $config['worlds'][$player->getWorldId()] )->setPosition( ); $i++;

						/** RESIDENCE **/
						$MadGD->addText( 'Residence:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( $towns_list[$player->getWorldId()][$player->getTownId()] )->setPosition( ); $i++;

						/** HOUSE **/
						$house = $db->query( 'SELECT `houses`.`name`, `houses`.`town` FROM `houses` WHERE `houses`.`world_id` = '.$player->getWorldId().' AND `houses`.`owner` = '.$player->getId().';' )->fetchAll();
						if ( count( $house ) != 0 )
						{
							$MadGD->addText( 'House:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
							$MadGD->addText( $house[0]['name'].' ('.$towns_list[$player->getWorldId()][$house[0]['town']].')' )->setPosition( ); $i++;
						}
						/** GUILD **/
						if ( $player->getRank() != null )
						{
							$MadGD->addText( 'Guild membership:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
							$MadGD->addText( $player->getRank()->getName().' of the '.$player->getRank()->getGuild()->getName() )->setPosition( ); $i++;
						}
						/** LAST LOGIN **/
						$MadGD->addText( 'Last login:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( ( $player->getLastLogin() == 0 ? 'Never logged in' : date( 'M d Y, H:i:s T', $player->getLastLogin() ) ) )->setPosition( ); $i++;
						/** ACCOUNT STATUS **/
						$MadGD->addText( 'Account Status:', $MadGD->textBold )->setPosition( 10, $i * $eachRow );
						$MadGD->addText( ( $player->getAccount()->getPremDays() > 0 ? 'Premium Account' : 'Free Account' ) )->setPosition( ); $i++;

						if ( $bars == 0 )
						{
							$MadGD->addIcon( SIGNATURES_IMAGES.'bg.png' )->setPosition( 200, 45 );
							$MadGD->addIcon( SIGNATURES_IMAGES.'bg.png' )->setPosition( 200, 54 );
							$MadGD->addIcon( SIGNATURES_IMAGES.'bg.png' )->setPosition( 200, 63 );

							/** HEALTH BAR **/
							$MadGD->addText( 'HP:', $percent )->setPosition( 182, 40 );
							if ( ( $player->getHealth() > $player->getHealthMax() ) or (  $player->getHealth() > 0 and $player->getHealthMax() > 0 ) )
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'health.png', $player->getHealth() / $player->getHealthMax() * 100 )->setPosition( 201, 46 );
								$MadGD->addText( floor( $player->getHealth() / $player->getHealthMax() * 100 ).'%', $percent )->setPosition( 305, 40 );
							}
							else
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'health.png', 100 )->setPosition( 201, 46 );
								$MadGD->addText( '100%', $percent )->setPosition( 305, 40 );
							}
							/** MANA BAR **/
							$MadGD->addText( 'MP:', $percent )->setPosition( 180, 50 );
							if ( ( $player->getMana() > $player->getManaMax() ) or ( $player->getMana() > 0 and $player->getManaMax() > 0 ) )
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'mana.png', $player->getMana() / $player->getManaMax() * 100 )->setPosition( 201, 55 );
								$MadGD->addText( floor( $player->getMana() / $player->getManaMax() * 100 ).'%', $percent )->setPosition( 305, 50 );
							}
							else
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'mana.png', 100 )->setPosition( 201, 55 );
								$MadGD->addText( '100%', $percent )->setPosition( 305, 50 );
							}
							/** EXPERIENCE BAR **/
							$MadGD->addText( 'EXP:', $percent )->setPosition( 176, 60 );
							if ( $player->getExperience() > 0 and ( $player->getExperience() / getExpToLevel( $player->getLevel() + 1 ) * 100 ) <= 100 )
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'exp.png', $player->getExperience() / getExpToLevel( $player->getLevel() + 1 ) * 100 )->setPosition( 201, 64 );
								$MadGD->addText( floor( $player->getExperience() / getExpToLevel( $player->getLevel() + 1 ) * 100 ).'%', $percent )->setPosition( 305, 60 );
							}
							else
							{
								$MadGD->addIcon( SIGNATURES_IMAGES.'exp.png', 100 )->setPosition( 201, 64 );
								$MadGD->addText( '100%', $percent )->setPosition( 305, 60 );
							}
						}

						if ( $equipments == 0 )
						{
							$slots = array(
								2 => array( $MadGD->equipment['x']['amulet'],      $MadGD->equipment['y']['amulet'] ),
								1 => array( $MadGD->equipment['x']['helmet'],      $MadGD->equipment['y']['helmet'] ),
								3 => array( $MadGD->equipment['x']['backpack'],    $MadGD->equipment['y']['backpack'] ),
								6 => array( $MadGD->equipment['x']['lefthand'],    $MadGD->equipment['y']['lefthand'] ),
								4 => array( $MadGD->equipment['x']['armor'],       $MadGD->equipment['y']['armor'] ),
								5 => array( $MadGD->equipment['x']['righthand'],   $MadGD->equipment['y']['righthand'] ),
								9 => array( $MadGD->equipment['x']['ring'],        $MadGD->equipment['y']['ring'] ),
								7 => array( $MadGD->equipment['x']['legs'],        $MadGD->equipment['y']['legs'] ),
								10 => array( $MadGD->equipment['x']['ammunition'], $MadGD->equipment['y']['ammunition'] ),
								8 => array( $MadGD->equipment['x']['boots'],       $MadGD->equipment['y']['boots'] )
							);
							foreach ( $slots as $pid => $position )
							{
								$item = $db->query( 'SELECT `itemtype`, `attributes` FROM `player_items` WHERE `player_items`.`player_id` = '.$player->getId().' AND `player_items`.`pid` = '.$pid.';' )->fetch();
								if ( $item['itemtype'] != null )
								{
									$count = unpack( 'C*', $item['attributes'] );
									if ( isset( $count[2] ) )
									{
										$count = $count[2];
									}
									else
									{
										$count = 1;
									}

									$imagePath = BASE . 'images/items/'.( $count > 1 ? $item['itemtype'].'/'.$count : $item['itemtype'] ).'.gif';
									if ( !file_exists( $imagePath ) )
									{
										require(SYSTEM . 'item.php');
										generateItem($item['itemtype'], $count);
									}
									if ( file_exists( $imagePath ) )
									{
										$MadGD->addIcon( $imagePath )->setPosition( $position[0], $position[1] );
									}
									else
									{
										$MadGD->addIcon( SIGNATURES_IMAGES.'noitem.png' )->setPosition( $position[0], $position[1] );
									}
								}
							}
						}

						$MadGD->display( SIGNATURES_CACHE.$player->getId().'.png' );

						header( 'Content-type: image/png' );
						readfile( SIGNATURES_CACHE.$player->getId().'.png' );
					}
					else
					{
						header( 'Content-type: image/png' );
						readfile( SIGNATURES_IMAGES.'nobackground.png' );
					}
				}
			}
		}
		else
		{
			header( 'Content-type: image/png' );
			readfile( SIGNATURES_IMAGES.'nocharacter.png' );
		}
	}
	else
	{
		header( 'Content-type: image/png' );
		readfile( SIGNATURES_IMAGES.'nogd.png' );
	}