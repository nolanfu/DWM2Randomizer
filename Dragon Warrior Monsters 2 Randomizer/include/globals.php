<?php

//TODO: Get rid of globals.
// By default, require the user to upload a ROM themselves, and serve the patched result as a
// downloadable file. Also, choose default location and filenames for processing ROMs server-side.
$loadLocalRom = true;
$saveLocalRom = false;
$localRomDirectory = "F:\\ROMs\\Gameboy\\DWM2TA\\";
$localRomInputName = "DWM2TA.gbc";
$localRomOutputName = "DWM2TA_Random.gbc";

include_once("config/settings.php");

$romData;
$ValidMonsterIDs = array(); //This is the actual ID number of the monster
$ValidMonsterGrowthIndecies = array(); //This is the position of the monster in the "growths" list
$MonsterNames = array(); // Monster ID -> Name
$MonsterIDsByName = array(); // Monster Name -> ID
$SkillNames = array(); // Skill ID -> Name
$SkillIDsByName = array(); // Skill Name -> ID
$encounter_data_length = 26;
$first_bank_encounter_start = 0xD0075 + $encounter_data_length;
$first_bank_encounter_count = 591;
$second_bank_encounter_start = 0x288056 + $encounter_data_length;
$second_bank_encounter_count = 93;
$encounter_count = $first_bank_encounter_count + $second_bank_encounter_count;
$monster_data_length = 47;
$first_monster_byte = 0xD436A;
$monster_count = 323;
$item_strings_start = 0x224E3;
$item_strings_count = 91;
$item_behavior_start = 0x58CC2;
$item_behavior_count = 99;
$item_behavior_length = 13;

$magicValues = array(
	'HoodSquid Drop Encounter' => 26,

	'Healing Skills' => array(
		22,23,24,25,26, // Healing spells
		30,31,32, // Vivify/Revive/Farewell
		123,124, // LifeDance/Hustle
		133, // TatsuCall (Tatsu can cast HealMore)
		160,161, // LifeSong/LoveRain
	),
);
$error = false;
$error_message = 'The following errors occurred while generating the new seed:';


$FlagSettings = array(
	'Growth' => array(
		'default' => 'None',
		'label' => 'Monster Growth',
		'options' => array(
			array('value' => 'Redistribute', 'description' => 'Monster stat growth values will add to the same total, but will be randomly distributed.'),
			array('value' => 'Shuffle', 'description' => 'Monsters will keep the same six growth values, but they will be randomly shuffled.'),
			array('value' => 'None', 'description' => 'Do not randomize monster stats.'),
		),
	),

	'Resistance' => array(
		'default' => 'None',
		'label' => 'Monster Resistances',
		'options' => array(
			array('value' => 'Redistribute', 'description' => 'Monster stat resistance values will add to the same total, but will be randomly distributed.'),
			array('value' => 'Shuffle', 'description' => 'Monsters will keep the same 27 resistance values, but they will be randomly shuffled.'),
			array('value' => 'None', 'description' => 'Do not randomize monster resistances.'),
		),
	),

	'Skills' => array(
		'default' => 'None',
		'label' => 'Shuffle Monster Skills',
		'options' => array(
			array('value' => 'Random', 'description' => 'Monsters learn completely random skills. BeDragon is excluded.'),
			array('value' => 'Random With BeDragon', 'label' => 'Random With BeDragon <b>(!)</b>', 'description' => 'Monsters learn completely random skills, including BeDragon. Boo!'),
			array('value' => 'Random, No Healing', 'label' => 'Random, No Healing <b>(!)</b>', 'description' => 'Monsters learn completely random skills. BeDragon and all skills with healing capabilities are excluded.'),
			array('value' => 'None', 'description' => 'Do not randomize monster skills.'),
		),
	),

	'Encounters' => array(
		'default' => 'None',
		'label' => 'Shuffle Encounters',
		'options' => array(
			array('value' => 'Poorly', 'description' => 'Shuffle enemy types. Monsters\' stats add to the same total, proportional to their growth values. Later monsters are supposed to start with more skills, but no monsters start with advanced skills.'),
			array('value' => 'None', 'description' => 'Do not randomize encounters.'),
		),
	),

	'GeniusMode' => array(
		'default' => 'Off',
		'label' => 'Max Monster Intelligence',
		'options' => array(
			array('value' => 'On', 'description' => 'All wild monsters have 999 Int; all monsters have 31 int growth. (Overrides randomized base int/growth)'),
			array('value' => 'Off', 'description' => 'Do not max out monster intelligence.'),
		),
	),

	'EXPScaling' => array(
		'default' => '100',
		'label' => 'Experience Yields',
		'options' => array(
			array('value' => '0', 'label' => '0%', 'description' => 'Monsters will be worth 0% of their normal EXP yields (minimum 1 EXP)'),
			array('value' => '50', 'label' => '50%', 'description' => 'Monsters will be worth 50% of their normal EXP yields (minimum 1 EXP)'),
			array('value' => '100', 'label' => '100%', 'description' => 'Monsters will be worth 100% of their normal EXP yields'),
			array('value' => '200', 'label' => '200%', 'description' => 'Monsters will be worth 200% of their normal EXP yields'),
			array('value' => '500', 'label' => '500%', 'description' => 'Monsters will be worth 500% of their normal EXP yields'),
		),
	),

	'StatScaling' => array(
		'default' => '100',
		'label' => 'Wild Monster Stat Scaling',
		'options' => array(
			array('value' => '0', 'label' => '0%', 'description' => 'Wild monster stats will be altered based on this scalar'),
			array('value' => '50', 'label' => '50%', 'description' => 'Wild monster stats will be altered based on this scalar'),
			array('value' => '100', 'label' => '100%', 'description' => 'Wild monster stats will be altered based on this scalar'),
			array('value' => '200', 'label' => '200%', 'description' => 'Wild monster stats will be altered based on this scalar'),
			array('value' => '500', 'label' => '500%', 'description' => 'Wild monster stats will be altered based on this scalar'),
		),
	),

	'BossScaling' => array(
		'default' => '100',
		'label' => 'Boss Monster Stat Scaling',
		'options' => array(
			array('value' => '0', 'label' => '0%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '50', 'label' => '50%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '100', 'label' => '100%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '200', 'label' => '200%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '300', 'label' => '300%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '400', 'label' => '400%', 'description' => 'Boss stats will be altered based on this scalar'),
			array('value' => '500', 'label' => '500%', 'description' => 'Boss stats will be altered based on this scalar'),
		),
	),

	'YetiMode' => array(
		'default' => 'Off',
		'label' => 'Yeti Mode',
		'options' => array(
			array('value' => 'On', 'description' => 'All monsters are yetis!'),
			array('value' => 'Off', 'description' => 'Not all monsters are yetis.'),
		),
	),
);

$Flags = array();

function PopulateMetadata(){
	global $Flags;
	global $ValidMonsterIDs;
	global $ValidMonsterGrowthIndecies;
	global $MonsterNames;
	global $MonsterIDsByName;
	global $SkillNames;
	global $SkillIDsByName;
	global $magicValues;

	$monster_list_query = "SELECT * FROM dragonwarriormonsters2 order by id asc";
	execute($monster_list_query);
	while($monster = get()){
		$MonsterNames[$monster["id"]] = $monster["name"];
		$MonsterIDsByName[$monster["name"]] = $monster["id"];
	}

	$skill_list_query = "SELECT * FROM dragonwarriormonsters2_skills order by id asc";
	execute($skill_list_query);
	while($skill = get()){
		$SkillNames[$skill["id"]] = $skill["Name"];
		$SkillIdsByName[$skill["Name"]] = $skill["id"];
	}
	
	if($Flags["YetiMode"] == "On"){
		$ValidMonsterIDs[] = $MonsterIDsByName["Yeti"];
	}
	else
	{
		//This is the ID stored in the SRAM that determines which monster you have.
		//It's also used within the table of base-stats for each monster.
		//NOTE 0x1B is Butch and I don't think he should be used?
		for ($i = 0; $i <= 0x17E; $i++)
		{
			if (
				($i >= 0x01 && $i <= 0x1A) || //Slimes
				($i >= 0x24 && $i <= 0x42) || //Dragons
				($i >= 0x47 && $i <= 0x66) || //Beasts
				($i >= 0x6A && $i <= 0x84) || //Birds
				($i >= 0x8D && $i <= 0xA7) || //Plants
				($i >= 0xB0 && $i <= 0xC9) || //Bugs
				($i >= 0xD3 && $i <= 0xF0) || //Devils
				($i >= 0xF6 && $i <= 0x110) || //Zombies
				($i >= 0x119 && $i <= 0x138) || //Materials
				($i >= 0x13C && $i <= 0x15B) || //Waters
				($i >= 0x15F && $i <= 0x174) //Bosses
				)
			{
				$ValidMonsterIDs[] = $i;
			}
		}
	}
	for ($i = 0; $i <= 0x17E; $i++)
	{
		if (
			($i >= 0x01 && $i <= 0x1B) || //Slimes (0x1B is Butch)
			($i >= 0x24 && $i <= 0x42) || //Dragons
			($i >= 0x47 && $i <= 0x66) || //Beasts
			($i >= 0x6A && $i <= 0x84) || //Birds
			($i >= 0x8D && $i <= 0xA7) || //Plants
			($i >= 0xB0 && $i <= 0xC9) || //Bugs
			($i >= 0xD3 && $i <= 0xF0) || //Devils
			($i >= 0xF6 && $i <= 0x110) || //Zombies
			($i >= 0x119 && $i <= 0x138) || //Materials
			($i >= 0x13C && $i <= 0x15B) || //Waters
			($i >= 0x15F && $i <= 0x174) //Bosses
			)
		{
			$ValidMonsterGrowthIndecies[] = $i;
		}
	}
}


?>
