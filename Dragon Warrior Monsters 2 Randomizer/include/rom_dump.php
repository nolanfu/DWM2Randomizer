<?php

function RomDump(){
	/* Code to create a hexidecimal rom dump of the input rom. */
	global $romData;
	global $localRomDirectory;

	$outfilename = $localRomDirectory.'romdump100_w_text.txt';
	$outfile = fopen($outfilename, "w");
	
	$line = '0x000000  ';
	$hex = "";
	$str = "";
	for($i = 0; $i < strlen($romData); $i++){
		$byte = getByte($i);
		$hex .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		if ($byte >= 10 && $byte < 36) {
			$str .= chr(ord('A') + ($byte - 10));
		} elseif ($byte >= 36 && $byte < 62) {
			$str .= chr(ord('a') + ($byte - 36));
		} elseif ($byte == 0x90) {
			$str .= ' ';
		} elseif ($byte < 10) {
			$str .= chr(ord('0') + $byte);
		} else {
			$str .= '.';
		}
		if($i % 64 == 63){
			fwrite($outfile,$line . $str . '   ' . $hex."\n");
			$line = '0x' . str_pad(dechex($i + 1), 6, '0', STR_PAD_LEFT) . '  ';
			$hex = '';
			$str = '';
		}
	}
	fclose($outfile);
}

function RomTextDump(){
	/* Code to create a text dump of the input rom. */
	global $romData;
	global $localRomDirectory;
	
	$files_to_create = 1;
	
	for($j = 0; $j < $files_to_create; $j++){
		$outfilename = $localRomDirectory.'romtextdump'.$j.'.txt';
		$outfile = fopen($outfilename, "w");
		
		$str = '';
		for($i = strlen($romData)/$files_to_create*$j; $i < strlen($romData)/$files_to_create*($j+1); $i++){
			if((ord($romData[$i]) >= 0x24) && (ord($romData[$i]) < (0x24 + 26))){
				$str .= chr(ord($romData[$i]) - 0x24 + ord('a'));
			}
			elseif((ord($romData[$i]) >= 0x0A) && (ord($romData[$i]) < (0x0A + 26))){
				$str .= chr(ord($romData[$i]) - 0x0A + ord('A'));
			}
			elseif((ord($romData[$i]) >= 0) && (ord($romData[$i]) < 0x10)){
				$str .= chr(ord($romData[$i]) + ord('0'));
			}
			else{
				$str .= ' ';
			}
			if($i % 100 == 99){
				$str .= "\n";
			}
		}
		fwrite($outfile,$str);
		fclose($outfile);
	}
}

function RomStructuredDataDump() {
	RomEncounterDump();
	RomMonsterDump();
	RomItemStringsDump();
	RomItemBehaviorDump();
	RomBank33Dump();
}

function RomRawEncounterDump() {
	global $romData;
	global $encounter_data_length;
	global $encounter_count;
	global $localRomDirectory;

	$outfilename = $localRomDirectory.'rom_raw_encounter_dump.txt';
	$outfile = fopen($outfilename, "w");
	for ($i = 0; $i < $encounter_count; $i++) {
		$str = "";
		for ($j = 0; $j < $encounter_data_length; $j++) {
			$byte = getEncounterByte($i, $j);
			$str .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomEncounterDump() {
	// Encounter data guide
	// Offset:   Size:    Description:
	// 0         2        Monster ID
	// 2-5       4*1      Monster Skills
	// 6         2        Monster EXP
	// 8         1        Join Rate (Values from 0-7. 0 = max join chance, 7 = never)
	// 9         1        Monster level
	// 10        2        Monster HP
	// 12        2        Monster MP
	// 14        2        Monster ATK
	// 16        2        Monster DEF
	// 18        2        Monster AGL
	// 20        2        Monster INT
	// 22-25     4x1      Personality Traits / Motivation
	global $romData;
	global $encounter_data_length;
	global $encounter_count;
	global $localRomDirectory;
	global $MonsterNames;

	$outfilename = $localRomDirectory.'rom_encounter_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "Row     mID Name       Boss |--Skills-|   EXP Jn LV    HP    MP   ATK   DEF   AGL   INT Personality ";
	fwrite($outfile, $str."\n");
	$str = "Offset:   0                  2  3  4  5   6-7  8  9 10-11 12-13 14-15 16-17 18-19 20-21 22 23 24 25 ";
	fwrite($outfile, $str."\n");
	$str = "--------------------------------------------------------------------------------------------------- ";
	fwrite($outfile, $str."\n");
	for ($i = 0; $i < $encounter_count; $i++) {
		$str = "";
		$str .= str_pad($i, 3, ' ', STR_PAD_LEFT) . '     ';
		$monster_id = getEncounterWord($i, 0);
		$str .= str_pad($monster_id, 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad($MonsterNames[$monster_id], 10, ' ') . ' ';
		$str .= (isArenaEncounter($i) ? 'Arna' : (isBossEncounter($i) ? 'Boss' : (isBossRecruit($i) ? 'Recr' : '    '))) . ' ';
		for ($j = 2; $j <= 5; $j++) {
			$str .= str_pad(dechex(getEncounterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' ';
		}
		$str .= str_pad(getEncounterWord($i, 6), 5, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getEncounterByte($i, 8)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad(getEncounterByte($i, 9), 2, ' ', STR_PAD_LEFT) . ' ';
		for ($j = 10; $j <= 20; $j += 2) {
			$str .= str_pad(getEncounterWord($i, $j), 5, ' ', STR_PAD_LEFT) . ' ';
		}
		for ($j = 22; $j <= 25; $j++) {
			$str .= str_pad(dechex(getEncounterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile, $str."\n");
	}
	fclose($outfile);
}

function RomRawMonsterDump() {
	global $romData;
	global $monster_data_length;
	global $monster_count;
	global $localRomDirectory;

	$outfilename = $localRomDirectory.'rom_monster_dump.txt';
	$outfile = fopen($outfilename, "w");
	for ($i = 0; $i < $monster_count; $i++) {
		$str = "";
		for ($j = 0; $j < $monster_data_length; $j++) {
			$byte = getMonsterByte($i, $j);
			$str .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomMonsterDump() {
	// Monster data guide
	// Offset:   Size:    Description:
	// 0         1        Monster family (00-0a)
	// 1         1        Sex distribution (Possible values are 0-3, most are 2.)
	// 2         1        Flying flag (e.g. LegSweep miss)
	// 3         1        Metal / coward flag (Metaly, Metabble, and MetalKing have 1, the rest are 0.)
	// 4         1        Join rate in random key worlds (Possible values are 1-7. 7 = never)
	// 5         1        ??? (Possible values are 0, 1, 2, and 3. Almost always matches offset 2, except for monster family 0x09 and a couple other exceptions: GigaDraco: 1 here, but 0 in offset 2. PomPomBom: 0 here, but 1 in offset 2. Water family is almost always 2, except for Starfish, which is 3. Geyser / water attack related?)
	// 6         1        Max unbred level
	// 7         1        Exp required for next level growth
	// 8-10      3*1      Learnable Skills
	// 11        1        ??? (observed values are 0, 1, 2, 3, and 5. Most are 0. Choices for rando world guardians?)
	// 12-17     6*1      HP/MP/ATK/DEF/AGL/INT Growth
	// 18-44     27*1     Resistances (0 = none, 1 = slight, 2 = some, 3 = immune)
	// 45-46     2        Base EXP value on kill for random key worlds
	global $romData;
	global $monster_data_length;
	global $monster_count;
	global $localRomDirectory;
	global $ValidMonsterGrowthIndecies;
	global $MonsterNames;

	$outfilename = $localRomDirectory.'rom_monster_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "                                                   Gro  Skills      |----Growth-----| |-------Resistances-------|        ";
	fwrite($outfile, $str."\n");
	$str = "Row     mID Name       Group  ? Fly Run  ?  ? mLVL EXP |------|  ?  HP MP AT DF AG IN |-------------------------| KillXP ";
	fwrite($outfile, $str."\n");
	$str = "Offset:                    0  1   2   3  4  5    6   7  8  9 10 11  12 13 14 15 16 17 18-44                        45-46 ";
	fwrite($outfile, $str."\n");
	$str = "------------------------------------------------------------------------------------------------------------------------ ";
	fwrite($outfile, $str."\n");
	for ($i = 0; $i < $monster_count; $i++) {
		$str = "";
		$str .= str_pad($i, 7, ' ', STR_PAD_LEFT) . ' ';
		$monster_id = $ValidMonsterGrowthIndecies[$i];
		$str .= str_pad($monster_id, 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad($MonsterNames[$monster_id], 10, ' ') . ' ';
		$str .= str_pad(getMonsterByte($i, 0), 5, ' ', STR_PAD_LEFT) . ' '; // Group
		$str .= str_pad(dechex(getMonsterByte($i, 1)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= (getMonsterByte($i, 2) == 1 ? "Yes" : "  .") . ' '; // Fly?
		$str .= (getMonsterByte($i, 3) == 1 ? "Yes" : "  .") . ' '; // Run?
		$str .= str_pad(dechex(getMonsterByte($i, 4)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getMonsterByte($i, 5)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad(getMonsterByte($i, 6), 4, ' ', STR_PAD_LEFT) . ' '; // Max LVL
		$str .= str_pad(getMonsterByte($i, 7), 3, ' ', STR_PAD_LEFT) . ' '; // Next level EXP growth
		for ($j = 8; $j <= 10; $j++) {
			$str .= str_pad(dechex(getMonsterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' '; // Skills
		}
		$str .= str_pad(dechex(getMonsterByte($i, 11)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= ' ';
		for ($j = 12; $j <= 17; $j++) {
			$str .= str_pad(getMonsterByte($i, $j), 2, ' ', STR_PAD_LEFT) . ' '; // Stat growth
		}
		for ($j = 18; $j <= 44; $j++) {
			$res = getMonsterByte($i, $j);
			if ($res == 0) {
				$str .= ".";
			} elseif ($res == 1) {
				$str .= "o";
			} elseif ($res == 2) {
				$str .= "O";
			} else {
				$str .= "X";
			}
		}
		$str .= ' ';
		$str .= str_pad(getMonsterWord($i, 45), 6, ' ', STR_PAD_LEFT) . ' '; // Base EXP gain for kill?

		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomItemStringsDump() {
	global $romData;
	global $item_strings_start;
	global $item_strings_count;
	global $localRomDirectory;
	
	$outfilename = $localRomDirectory.'rom_strings_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "";
	$hex = "";
	$counter = 0;
	for ($i = 0; $i < 1000; $i++) {
		$byte = ord($romData[$item_strings_start + $i]);
		$hex .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		if ($byte == 0xf0) {
			fwrite($outfile,str_pad($str, 18, ' ') . $hex . "\n");
			$str = "";
			$hex = "";
			$counter += 1;
			if ($counter >= $item_strings_count) {
				break;
			}
		} elseif ($byte >= 10 && $byte < 36) {
			$str .= chr(ord('A') + ($byte - 10));
		} elseif ($byte >= 36 && $byte < 62) {
			$str .= chr(ord('a') + ($byte - 36));
		} elseif ($byte < 10) {
			$str .= chr(ord('0') + $byte);
		} else {
			$str .= '.';
		}
	}
	fclose($outfile);
}

function RomItemBehaviorDump() {
	global $item_behavior_start;
	global $item_behavior_count;
	global $item_behavior_length;
	global $localRomDirectory;
	
	$outfilename = $localRomDirectory.'rom_items_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "Row     Type Price  ? Use  ? Target Icon  ? F1 F2 V1 V2 ";
	fwrite($outfile,$str . "\n");
	$str = "Offset:    0   1-2  3   4  5      6    7  8  9 10 11 12 ";
	fwrite($outfile,$str . "\n");
	$str = "------------------------------------------------------- ";
	fwrite($outfile,$str . "\n");
	
	for ($i = 0; $i < $item_behavior_count; $i++) {
		$str = "";
		$str .= str_pad($i, 6, ' ', STR_PAD_LEFT) . '  ';
		$str .= str_pad(getByte($item_behavior_start + $i * $item_behavior_length + 0), 4, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(getWord($item_behavior_start + $i * $item_behavior_length + 1), 5, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + 3)), 2, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + 4)), 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + 5)), 2, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + 6)), 6, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + 7)), 4, ' ', STR_PAD_LEFT) . ' ';
		for ($j = 8; $j <= 12; $j++) {
			$str .= str_pad(dechex(getByte($item_behavior_start + $i * $item_behavior_length + $j)), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str . "\n");
	}
	fclose($outfile);
}

function RomDumpStrings($outfile, $start, $end) {
	$str = '';
	for ($i = $start; $i <= $end; $i++) {
		$byte = getByte($i);
		if ($byte == 0xf0) {
			fwrite($outfile, $str . "\n");
			$str = '';
		} elseif ($byte >= 10 && $byte < 36) {
			$str .= chr(ord('A') + ($byte - 10));
		} elseif ($byte >= 36 && $byte < 62) {
			$str .= chr(ord('a') + ($byte - 36));
		} elseif ($byte == 0x90) {
			$str .= ' ';
		} elseif ($byte < 10) {
			$str .= chr(ord('0') + $byte);
		} else {
			$str .= '.';
		}
	}
}

function RomBank33Dump() {
	global $localRomDirectory;
	$outfilename = $localRomDirectory.'rom_bank_33_dump.txt';
	$outfile = fopen($outfilename, "w");
	fwrite($outfile, "Bank 0x33 (0x0CC000 - 0x0CFFFF): Dialog box / window structure, arena text\n\n");
	fwrite($outfile, "Dialog box / window structures\n");
	$counter = 0;
	$i = 0xCC0C5;
	while ($i <= 0xCD1C5) {
		$str = "Offset " . $counter . ":\n";
		$flag1 = getByte($i); $str .= str_pad(dechex($flag1), 2, '0', STR_PAD_LEFT) . ' '; $i += 1;
		$flag2 = getByte($i); $str .= str_pad(dechex($flag2), 2, '0', STR_PAD_LEFT) . ' '; $i += 1;
		$width = getByte($i); $str .= str_pad($width, 2, ' ', STR_PAD_LEFT) . ' '; $i += 1;
		$height = getByte($i); $str .= str_pad($height, 2, ' ', STR_PAD_LEFT) . ' '; $i += 1;
		fwrite($outfile, $str."\n");
		for ($j = 0; $j < $height; $j++) {
			$str = "";
			for ($k = 0; $k < $width; $k++) {
				$str .= str_pad(dechex(getByte($i + $j * $width + $k)), 2, '0', STR_PAD_LEFT) . ' ';
			}
			fwrite($outfile, $str."\n");
		}
		$i += $height * $width;
		$counter += 1;
		fwrite($outfile, "\n");
	}
	
	fwrite($outfile, "\n\nArena Attendant strings:\n");
	RomDumpStrings($outfile, 0xCE515, 0xCF036);
	fwrite($outfile, "\n\nPvP combat / trade strings:\n");
	RomDumpStrings($outfile, 0xCF074, 0xCF1EF);
	fclose($outfile);
}

function LocateBosses(){
	//I haven't been citing my sources for all of the information I'm pulling from Gamefaqs since it's in the ROM data anyway...
	//...but this data doesn't seem to be entirely accurate thecowLUL
	//boss stats source: https://gamefaqs.gamespot.com/gbc/525414-dragon-warrior-monsters-2-cobis-journey/faqs/14383
	
	global $romData;
	global $encounter_count;

	//Really shoulda put this in the database...
	$boss_stats = array(
		array("Oasis Beavern",5,98,16,20,8,36,120),
		array("Oasis CurseLamp",5,220,8,27,10,44,65),
		array("K-1 Babble",6,36,9,14,13,32,123),
		array("K-1 PearlGel",5,15,12,17,35,51,96),
		array("K-2 SpikyBoy",6,19,42,17,31,34,96),
		array("K-2 Pixy",7,29,24,20,14,54,79),
		array("K-2 Dracky",7,40,22,14,12,70,96),
		array("K-3 MadRaven",9,41,55,16,30,84,163),
		array("K-3 Kitehawk",9,46,35,26,21,42,146),
		array("K-3 MadRaven",9,41,55,16,30,84,163),
		array("Pirate Hoodsquid",12,350,100,78,39,85,180),
		array("Pirate Boneslave",13,72,58,58,45,49,99),
		array("Pirate CaptDead",13,500,105,66,48,50,144),
		array("Pirate KingSquid",38,2500,140,227,147,189,73),
		array("Ice Bombcrag",18,650,10,80,80,20,140),
		array("Ice AgDevil",18,550,50,90,59,109,216),
		array("Ice Puppetor",22,400,82,85,60,48,120),
		array("Ice Goathorn",27,850,89,95,65,92,165),
		array("Ice ArcDemon",27,210,89,105,71,84,211),
		array("Ice Goathorn 2",27,330,89,95,65,92,223),
		array("Sky MadCondor",30,600,119,131,90,110,159),
		array("Sky Skeletor",37,226,108,160,148,140,140),
		array("Sky Niterich",36,1500,106,177,149,110,510),
		array("Sky Metabble",37,20,368,95,999,670,522),
		array("Sky EvilArmor",38,450,140,185,150,138,280),
		array("Sky Mudou",38,3000,413,225,160,156,150),
		array("Limbo GigaDraco",42,1000,245,277,180,120,100),
		array("Limbo Centasaur",40,900,83,220,150,250,250),
		array("Limbo Garudian",41,800,88,206,160,251,320),
		array("Limbo Darck",44,4000,235,350,220,160,210),
		array("Butch Butch",0,755,322,547,238,644,500),
		array("Butch Pumpoise",40,800,388,238,255,330,500),
		array("Butch Drygon",40,480,210,432,677,270,500),
		array("Kameha50 MimeSlime",43,370,690,258,320,350,520),
		array("Kameha50 Tonguella",45,400,320,338,310,284,430),
		array("Kameha50 Golem",48,520,315,410,301,189,540),
		array("Kameha150 MetalKing",50,200,950,310,780,840,700),
		array("Kameha150 KingLeo",55,1200,490,370,480,460,680),
		array("Kameha150 GoldGolem",50,900,590,430,600,370,700),
		array("Terry GreatDrak",52,840,330,375,400,299,300),
		array("Terry Watabou",40,610,467,320,380,178,150),
		array("Terry Durran",53,1000,470,420,430,326,450),
		array("Elf Arrowdog",40,500,210,237,288,249,501),
		array("Elf AgDevil",47,1500,580,346,310,267,700),
		array("Power WindBeast",40,900,127,236,168,489,0),
		array("Power MadGoose",40,580,387,201,203,348,0),
		array("Power WhaleMage",42,750,590,199,228,247,0),
		array("Power SeaHorse",37,480,180,191,253,222,0),
		array("Power Octoreach",39,700,140,238,174,247,0),
		array("Power IceMan",45,1200,500,345,279,210,0),
		array("Power Shadow",40,600,300,235,240,170,0),
		array("Power BigEye",37,750,280,358,242,198,0),
		array("Power Balzak",45,2000,398,375,257,220,0),
		array("Power RotRaven",35,680,340,160,249,340,0),
		array("Power Jamirus",48,1400,750,247,349,410,0),
		array("Power SkyDragon",46,990,700,349,279,243,0),
		array("Power Gremlin",40,600,450,201,232,218,0),
		array("Traveler Pixy",52,680,400,357,374,443,700),
		array("Traveler Copycat",55,750,300,310,520,380,999),
		array("Traveler StoneMan",52,1300,360,458,268,247,700),
		array("Traveler WhipBird",53,950,830,334,374,438,700),
		array("Traveler MetalKing",48,600,200,410,859,320,160),
		array("Traveler RainHawk",99,3500,520,710,540,250,300),
		array("Traveler Coatol",46,1500,210,600,600,300,255),
		array("Baffle Crestpent",42,780,700,279,300,299,480),
		array("Baffle SpotKing",45,1300,550,378,214,308,800),
		array("Baffle Gulpple",45,840,590,289,299,387,0),
		array("Baffle FairyDrak",40,700,340,312,298,273,0),
		array("Baffle DuckKite",42,900,470,296,330,400,0),
		array("Baffle FunkyBird",42,890,500,301,267,430,0),
		array("Baffle FunkyBird",42,890,500,301,267,430,0),
		array("Baffle Slurperon",39,790,400,277,259,302,0),
		array("Baffle DanceVegi",37,680,430,259,249,240,0),
		array("Baffle MadPlant",39,880,380,243,350,289,510),
		array("Baffle Orc",43,900,350,346,289,190,770,6000),
		array("Baffle PutrePup",41,700,280,330,328,232,450),
		array("Baffle Devipine",43,840,400,300,358,222,660),
		array("Baffle Anemon",44,900,370,248,388,289,580),
		array("Baffle HerbMan",46,1300,700,341,273,370,900),
		array("Soul KingSlime",50,4000,280,329,289,334,800),
		array("Soul Coatol",48,640,800,289,410,329,800),
		array("Soul FangSlime",44,700,378,333,278,397,590),
		array("Soul Grizzly",45,870,360,372,245,379,288),
		array("Soul BeastNite",45,1200,420,358,343,201,600),
		array("Soul Slimeborg",44,650,790,217,327,418,740),
		array("Soul Unicorn",45,720,680,316,322,319,800),
		array("Soul SuperTen",44,600,380,312,298,417,600),
		array("Soul Slime",47,600,500,387,299,387,700),
		array("Soul RockSlime",40,680,230,265,362,265,450),
		array("Soul Metabble",40,289,700,146,798,688,770),
		array("Soul Gorago",55,990,780,432,378,688,800)
	);
	
	foreach($boss_stats as $boss){
		$foundit = 0;
		for ($i = 0; $i < $encounter_count; $i++){
			$lv  = getEncounterByte($i, 9);
			$hp  = getEncounterWord($i, 10);
			$mp  = getEncounterWord($i, 12);
			$atk = getEncounterWord($i, 14);
			$def = getEncounterWord($i, 16);
			$agl = getEncounterWord($i, 18);
			$int = getEncounterWord($i, 20);
			
			$pts = 0;
			if($boss[1] == $lv ) $pts++;
			if($boss[2] == $hp ) $pts++;
			if($boss[3] == $mp ) $pts++;
			if($boss[4] == $atk) $pts++;
			if($boss[5] == $def) $pts++;
			if($boss[6] == $agl) $pts++;
			if($boss[7] == $int) $pts++;
			
			if($pts > 2){
				$foundit = 1;
				echo $boss[0].' located: '.$i.' ('.$pts.')<br>';
			}
		}
		if($foundit == 0){
			echo $boss[0].' NOT located!<br>';
		}
	}
}

?>
