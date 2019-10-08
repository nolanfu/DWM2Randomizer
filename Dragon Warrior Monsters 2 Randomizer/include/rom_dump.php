<?php

function RomDump($rom){
	/* Code to create a hexidecimal rom dump of the input rom. */

	$outfilename = Rom::$localRomDirectory.'romdump100_w_text.txt';
	$outfile = fopen($outfilename, "w");
	
	$line = '0x000000  ';
	$hex = "";
	$str = "";
	for($i = 0; $i < strlen($rom->data); $i++){
		$byte = $rom->getByte($i);
		$hex .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= byteToAscii($byte);
		if($i % 64 == 63){
			fwrite($outfile,$line . $str . '   ' . $hex."\n");
			$line = '0x' . str_pad(dechex($i + 1), 6, '0', STR_PAD_LEFT) . '  ';
			$hex = '';
			$str = '';
		}
	}
	fclose($outfile);
}

function RomStructuredDataDump($rom) {
	RomEncounterDump($rom);
	RomMonsterDump($rom);
	RomItemStringsDump($rom);
	RomItemBehaviorDump($rom);
	RomBank33Dump($rom);
}

function RomRawEncounterDump($rom) {
	$outfilename = Rom::$localRomDirectory.'rom_raw_encounter_dump.txt';
	$outfile = fopen($outfilename, "w");
	for ($i = 0; $i < $rom->map["encounters"]["count"]; $i++) {
		$str = "";
		for ($j = 0; $j < $rom->map["encounters"]["data_length"]; $j++) {
			$byte = $rom->getEncounterByte($i, $j);
			$str .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomEncounterDump($rom) {
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

	$outfilename = Rom::$localRomDirectory.'rom_encounter_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "Row     mID Name       Boss |--Skills-|   EXP Jn LV    HP    MP   ATK   DEF   AGL   INT Personality ";
	fwrite($outfile, $str."\n");
	$str = "Offset:   0                  2  3  4  5   6-7  8  9 10-11 12-13 14-15 16-17 18-19 20-21 22 23 24 25 ";
	fwrite($outfile, $str."\n");
	$str = "--------------------------------------------------------------------------------------------------- ";
	fwrite($outfile, $str."\n");
	for ($i = 0; $i < $rom->map["encounters"]["count"]; $i++) {
		$str = "";
		$str .= str_pad($i, 3, ' ', STR_PAD_LEFT) . '     ';
		$monster_id = $rom->getEncounterWord($i, 0);
		$str .= str_pad($monster_id, 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad((array_key_exists($monster_id, $rom->monsterNames) ? $rom->monsterNames[$monster_id] : ""), 10, ' ') . ' ';
		$str .= ($rom->isArenaEncounter($i) ? 'Arna' : ($rom->isBossEncounter($i) ? 'Boss' : ($rom->isBossRecruit($i) ? 'Recr' : '    '))) . ' ';
		for ($j = 2; $j <= 5; $j++) {
			$str .= str_pad(dechex($rom->getEncounterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' ';
		}
		$str .= str_pad($rom->getEncounterWord($i, 6), 5, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getEncounterByte($i, 8)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad($rom->getEncounterByte($i, 9), 2, ' ', STR_PAD_LEFT) . ' ';
		for ($j = 10; $j <= 20; $j += 2) {
			$str .= str_pad($rom->getEncounterWord($i, $j), 5, ' ', STR_PAD_LEFT) . ' ';
		}
		for ($j = 22; $j <= 25; $j++) {
			$str .= str_pad(dechex($rom->getEncounterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile, $str."\n");
	}
	fclose($outfile);
}

function RomRawMonsterDump($rom) {
	$outfilename = Rom::$localRomDirectory.'rom_monster_dump.txt';
	$outfile = fopen($outfilename, "w");
	for ($i = 0; $i < $rom->map["monsters"]["count"]; $i++) {
		$str = "";
		for ($j = 0; $j < $rom->map["monsters"]["data_length"]; $j++) {
			$byte = $rom->getMonsterByte($i, $j);
			$str .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomMonsterDump($rom) {
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

	$outfilename = Rom::$localRomDirectory.'rom_monster_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "                                                   Gro  Skills      |----Growth-----| |-------Resistances-------|        ";
	fwrite($outfile, $str."\n");
	$str = "Row     mID Name       Group  ? Fly Run  ?  ? mLVL EXP |------|  ?  HP MP AT DF AG IN |-------------------------| KillXP ";
	fwrite($outfile, $str."\n");
	$str = "Offset:                    0  1   2   3  4  5    6   7  8  9 10 11  12 13 14 15 16 17 18-44                        45-46 ";
	fwrite($outfile, $str."\n");
	$str = "------------------------------------------------------------------------------------------------------------------------ ";
	fwrite($outfile, $str."\n");
	for ($i = 0; $i < $rom->map["monsters"]["count"]; $i++) {
		$str = "";
		$str .= str_pad($i, 7, ' ', STR_PAD_LEFT) . ' ';
		$monster_id = (array_key_exists($i, $rom->monsterGrowthStatsIndex) ? $rom->monsterGrowthStatsIndex[$i] : "");
		$str .= str_pad($monster_id, 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad((array_key_exists($monster_id, $rom->monsterNames) ? $rom->monsterNames[$monster_id] : ""), 10, ' ') . ' ';
		$str .= str_pad($rom->getMonsterByte($i, 0), 5, ' ', STR_PAD_LEFT) . ' '; // Group
		$str .= str_pad(dechex($rom->getMonsterByte($i, 1)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= ($rom->getMonsterByte($i, 2) == 1 ? "Yes" : "  .") . ' '; // Fly?
		$str .= ($rom->getMonsterByte($i, 3) == 1 ? "Yes" : "  .") . ' '; // Run?
		$str .= str_pad(dechex($rom->getMonsterByte($i, 4)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getMonsterByte($i, 5)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= str_pad($rom->getMonsterByte($i, 6), 4, ' ', STR_PAD_LEFT) . ' '; // Max LVL
		$str .= str_pad($rom->getMonsterByte($i, 7), 3, ' ', STR_PAD_LEFT) . ' '; // Next level EXP growth
		for ($j = 8; $j <= 10; $j++) {
			$str .= str_pad(dechex($rom->getMonsterByte($i, $j)), 2, '0', STR_PAD_LEFT) . ' '; // Skills
		}
		$str .= str_pad(dechex($rom->getMonsterByte($i, 11)), 2, '0', STR_PAD_LEFT) . ' ';
		$str .= ' ';
		for ($j = 12; $j <= 17; $j++) {
			$str .= str_pad($rom->getMonsterByte($i, $j), 2, ' ', STR_PAD_LEFT) . ' '; // Stat growth
		}
		for ($j = 18; $j <= 44; $j++) {
			$res = $rom->getMonsterByte($i, $j);
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
		$str .= str_pad($rom->getMonsterWord($i, 45), 6, ' ', STR_PAD_LEFT) . ' '; // Base EXP gain for kill

		fwrite($outfile,$str."\n");
	}
	fclose($outfile);
}

function RomItemStringsDump($rom) {
	$outfilename = Rom::$localRomDirectory.'rom_strings_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "";
	$hex = "";
	$counter = 0;
	for ($i = 0; $i < 1000; $i++) {
		$byte = ord($rom->data[$rom->map["item_strings"]["start"] + $i]);
		$hex .= str_pad(dechex($byte), 2, '0', STR_PAD_LEFT) . ' ';
		if ($byte == 0xf0) {
			fwrite($outfile,str_pad($str, 18, ' ') . $hex . "\n");
			$str = "";
			$hex = "";
			$counter += 1;
			if ($counter >= $rom->map["item_strings"]["count"]) {
				break;
			}
		} else {
			$str .= byteToAscii($byte);
		}
	}
	fclose($outfile);
}

function RomItemBehaviorDump($rom) {
	$outfilename = Rom::$localRomDirectory.'rom_items_dump.txt';
	$outfile = fopen($outfilename, "w");
	$str = "Row     Type Price  ? Use  ? Target Icon  ? F1 F2 V1 V2 ";
	fwrite($outfile,$str . "\n");
	$str = "Offset:    0   1-2  3   4  5      6    7  8  9 10 11 12 ";
	fwrite($outfile,$str . "\n");
	$str = "------------------------------------------------------- ";
	fwrite($outfile,$str . "\n");
	
	for ($i = 0; $i < $rom->map["item_behavior"]["count"]; $i++) {
		$str = "";
		$str .= str_pad($i, 6, ' ', STR_PAD_LEFT) . '  ';
		$str .= str_pad($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 0)), 4, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad($rom->getWord($rom->calcStructuredOffset("item_behavior", $i, 1)), 5, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 3))), 2, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 4))), 3, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 5))), 2, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 6))), 6, ' ', STR_PAD_LEFT) . ' ';
		$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, 7))), 4, ' ', STR_PAD_LEFT) . ' ';
		for ($j = 8; $j <= 12; $j++) {
			$str .= str_pad(dechex($rom->getByte($rom->calcStructuredOffset("item_behavior", $i, $j))), 2, '0', STR_PAD_LEFT) . ' ';
		}
		fwrite($outfile,$str . "\n");
	}
	fclose($outfile);
}

function romDumpStrings($rom, $outfile, $start, $end) {
	$str = '';
	for ($i = $start; $i <= $end; $i++) {
		$byte = $rom-> getByte($i);
		if ($byte == 0xf0) {
			fwrite($outfile, $str . "\n");
			$str = '';
		} else {
			$str .= byteToAscii($byte);
		}
	}
}

function byteToAscii($byte) {
	if ($byte >= 10 && $byte < 36) {
		return chr(ord('A') + ($byte - 10));
	} elseif ($byte >= 36 && $byte < 62) {
		return chr(ord('a') + ($byte - 36));
	} elseif ($byte == 0x90) {
		return ' ';
	} elseif ($byte < 10) {
		return chr(ord('0') + $byte);
	} else {
		return '.';
	}
}

function RomBank33Dump($rom) {
	$outfilename = Rom::$localRomDirectory.'rom_bank_33_dump.txt';
	$outfile = fopen($outfilename, "w");
	fwrite($outfile, "Bank 0x33 (0x0CC000 - 0x0CFFFF): Dialog box / window structure, arena text\n\n");
	fwrite($outfile, "Dialog box / window structures\n");
	$counter = 0;
	$i = 0xCC0C5;
	while ($i <= 0xCD1C5) {
		$str = "Offset " . $counter . ":\n";
		$flag1 = $rom->getByte($i); $str .= str_pad(dechex($flag1), 2, '0', STR_PAD_LEFT) . ' '; $i += 1;
		$flag2 = $rom->getByte($i); $str .= str_pad(dechex($flag2), 2, '0', STR_PAD_LEFT) . ' '; $i += 1;
		$width = $rom->getByte($i); $str .= str_pad($width, 2, ' ', STR_PAD_LEFT) . ' '; $i += 1;
		$height = $rom->getByte($i); $str .= str_pad($height, 2, ' ', STR_PAD_LEFT) . ' '; $i += 1;
		fwrite($outfile, $str."\n");
		for ($j = 0; $j < $height; $j++) {
			$str = "";
			for ($k = 0; $k < $width; $k++) {
				$str .= str_pad(dechex($rom->getByte($i + $j * $width + $k)), 2, '0', STR_PAD_LEFT) . ' ';
			}
			fwrite($outfile, $str."\n");
		}
		$i += $height * $width;
		$counter += 1;
		fwrite($outfile, "\n");
	}
	
	fwrite($outfile, "\n\nArena Attendant strings:\n");
	romDumpStrings($rom, $outfile, 0xCE515, 0xCF036);
	fwrite($outfile, "\n\nPvP combat / trade strings:\n");
	romDumpStrings($rom, $outfile, 0xCF074, 0xCF1EF);
	fclose($outfile);
}

?>
