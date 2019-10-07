<?php

function ShuffleMonsterGrowth($rom)
{
	global $Flags;
	global $monster_count;

	for ($i = 0; $i < $monster_count; $i++)
	{
		if ($Flags["Growth"] == "Redistribute")
		{
			//Let's randomize the monster's growth stats, but have them add up to the same value.
			$total_stats = 0;
			for ($j = 0; $j < 6; $j++)
			{
				//We're going to set a minimum growth value at 1 because growth of 0 SUCKS
				$total_stats += $rom->getMonsterByte($i, 12 + $j) - 1;
				$rom->setMonsterByte($i, 12 + $j, 1);
			}

			//Start by assigning 30 points: 20 to one stat and 10 to another (Or the same?)
			$slot1 = Random() % 6; //Named slot1 because C# is throwing a fit if I re-use the same var name in the loop below...
			$rom->setMonsterByte($i, 12 + $slot1, $rom->getMonsterByte($i, 12 + $slot1) + 20);
			$slot1 = Random() % 6;
			$rom->setMonsterByte($i, 12 + $slot1, $rom->getMonsterByte($i, 12 + $slot1) + 10);
			$total_stats -= 30;

			if($total_stats > 31*6) $total_stats = 31*6;
			while($total_stats > 0)
			{
				$slot = Random() % 6;
				$safety = 0;
				//Do not let the stat go over 31
				//2018 08 30 - ealm - Instead of rerolling, let's just use the next stat.  I guess this encourages high stats to be adjacent though?
				while($rom->getMonsterByte($i, 12 + $slot) >= 31){
					$slot = ($slot + 1) % 6;
					$safety++;
					if($safety >= 6) break;
				}
				if($safety >= 6) break;
				if ($rom->getMonsterByte($i, 12 + $slot) < 31)
				{
					$rom->setMonsterByte($i, 12 + $slot, $rom->getMonsterByte($i, 12 + $slot) + 1);
					$total_stats--;
				}
			}
		} elseif ($Flags["Growth"] == "Shuffle") {
			$options = [];
			for ($j = 0; $j < 6; $j++)
			{
				$options[] = $rom->getMonsterByte($i, 12 + $j);
			}
			for ($j = 0; $j < 6; $j++)
			{
				$chosen_offset = Random() % count($options);
				$chosen = array_splice($options, $chosen_offset, 1);
				$rom->setMonsterByte($i, 12 + $j, $chosen[0]);
			}
		}
		
		//If we're in Genius Mode, all monsters get 31 int growth
		if($Flags["GeniusMode"] == "Yes"){
			$rom->setMonsterByte($i, 12 + 5, 31);
		}

	}

	return true;
}



function ShuffleMonsterResistances($rom)
{
	global $Flags;
	
	global $monster_count;
	
	for ($i = 0; $i < $monster_count; $i++)
	{
		if ($Flags["Resistance"] == "Redistribute")
		{
			//Repeat for resistances.  There are 27 of these...
			$total_resistances = 0;
			for ($j = 0; $j < 27; $j++)
			{
				$total_resistances += $rom->getMonsterByte($i, 18 + $j);
				$rom->setMonsterByte($i, 18 + $j, 0);
			}
			if($total_resistances > 27*3) $total_resistances = 27*3;
			while ($total_resistances > 0)
			{
				$slot = Random() % 27;
				//2019 03 11 - ealm - Initialize this variable idiot
				$safety = 0;
				//2018 08 30 - ealm - Instead of rerolling, let's just use the next stat.  I guess this encourages high stats to be adjacent though?
				while($rom->getMonsterByte($i, 18 + $slot) >= 3){
					$slot = ($slot + 1) % 27;
					$safety++;
					if($safety >= 27) break;
				}
				if($safety >= 27) break;
				//Do not let the stat go over 3
				if ($rom->getMonsterByte($i, 18 + $slot) < 3)
				{
					$rom->setMonsterByte($i, 18 + $slot, $rom->getMonsterByte($i, 18 + $slot) + 1);
					$total_resistances--;
				}
			}
		} elseif ($Flags["Resistance"] == "Shuffle") {
			$options = [];
			for ($j = 0; $j < 27; $j++)
			{
				$options[] = $rom->getMonsterByte($i, 18 + $j);
			}
			for ($j = 0; $j < 27; $j++)
			{
				$chosen_offset = Random() % count($options);
				$chosen = array_splice($options, $chosen_offset, 1);
				$rom->setMonsterByte($i, 18 + $j, $chosen[0]);
			}
		}
	}

	return true;
}


function ShuffleMonsterSkills($rom)
{
	global $Flags;
	global $monster_count;
	global $magicValues;
	global $SkillIDsByName;

	$tier_one_skills = array( 1, 4, 7, 10, 13, 16, 19, 21, 22, 25, 27, 30, 32, 33, 34, 35, 36, 37, 39, 41, 43, 45, 46, 47, 49, 51, 52, 53, 54, 56, 57, 58, 60, 61, 62, 63, 64, 68, 72, 74, 75, 76, 78, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 137, 138, 139, 141, 143, 144, 145, 146, 147, 148, 149, 150, 151, 153, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169 );

	// Remove BeDragon unless we've specifically asked to keep it.
	if ($Flags["Skills"] != "Random With BeDragon") {
		$index = array_search($SkillIDsByName["BeDragon"], $tier_one_skills);
		if ($index !== false) {
			unset($tier_one_skills[$index]);
		}
	}

	if ($Flags["Skills"] == "Random, No Healing") {
		foreach ($magicValues["Healing Skills"] as $skillId) {
			$index = array_search($skillId, $tier_one_skills);
			if ($index !== false) {
				unset($tier_one_skills[$index]);
			}
		}
	}
  
	for ($i = 0; $i < $monster_count; $i++)
	{
		if ($Flags["Skills"] != "None")
		{
			//Randomize skills!  Pick three of these.
			$skill1 = Random() % count($tier_one_skills);
			$skill2 = Random() % count($tier_one_skills);
			while ($skill2 == $skill1)
			{
				$skill2 = Random() % count($tier_one_skills);
			}
			$skill3 = Random() % count($tier_one_skills);
			while ($skill3 == $skill1 || $skill3 == $skill2)
			{
				$skill3 = Random() % count($tier_one_skills);
			}
			$rom->setMonsterByte($i, 8, $tier_one_skills[$skill1]);
			$rom->setMonsterByte($i, 9, $tier_one_skills[$skill2]);
			$rom->setMonsterByte($i, 10, $tier_one_skills[$skill3]);
		}
	}

	return true;
}

function ShuffleEncounters($rom)
{
	global $Flags;
	
	global $ValidMonsterIDs;
	global $ValidMonsterGrowthIndecies;
	global $MonsterIDsByName;
	global $encounter_count;
	global $magicValues;
	
	//Code patch: Reduce level of SpikyBoys in Oasis to 1 so that they level faster
	$rom->data[0xD00CC] = chr(0x01);

	for ($i = 0; $i < $encounter_count; $i++)
	{
		//Which monster is this?
		if($i == 0 && $Flags["StartingMonster"] != 0){
			//Allow the starting monster to be selectable
			$monsterid = $Flags["StartingMonster"];
		}
		elseif($i == $magicValues["HoodSquid Drop Encounter"]){
			//Special case: The hoodsquid needs to be a water-type. (0x13C - 0x15B, 32 monsters)
			//TODO: Yeti Mode won't put a yeti here ):
			$monsterid = Random() % 32 + 0x13C;
		}else{
			$monsterid = $ValidMonsterIDs[Random() % count($ValidMonsterIDs)];
		}
		$MonsterGrowthIndex = array_search($monsterid,$ValidMonsterGrowthIndecies);
		
		//Should probably choose monster independently of the rest of this.
		if($Flags["Encounters"] == "Poorly") //Previously "Based On Growth"
		{
			
			//Need to ensure Army Ant/Madgopher are obtainable before Ice.
			if($rom->getEncounterWord($i, 0) != $MonsterIDsByName["MadGopher"] && $rom->getEncounterWord($i, 0) != $MonsterIDsByName["ArmyAnt"]){
				$rom->setEncounterWord($i, 0, $monsterid);
			}
			
			$is_boss_I_think = $rom->isBossEncounter($i);
			
			//Add up the monster's GROWTH values
			$total_growth_stats = 0;
			for ($j = 0; $j < 6; $j++)
			{
				if($j == 0 && $is_boss_I_think) continue;
				$total_growth_stats += $rom->getMonsterByte($MonsterGrowthIndex, 12 + $j);
			}
			
			//Add up the monster's BASE STATS
			$total_stats = 0;
			for ($j = 0; $j < 6; $j++)
			{
				if($j == 0 && $is_boss_I_think) continue;
				$total_stats += $rom->getEncounterWord($i, 10 + $j * 2);
			}
			//Double the base stats for our starting monster, because Slash is a little weak at level one.
			if($i == 0) $total_stats *= 2;
			
			//Take the percentage of the GROWTH allocated to each stat and multiply by the total BASE
			for ($j = 0; $j < 6; $j++)
			{
				//TODO: Test stat scaling Kappa
				if($j == 0 && $is_boss_I_think){
					//2018 08 30 - ealm - TODO: Why is this continue here?
					//continue;
					//We're handling boss HP differently.  Basically, we're not changing it.  However...
					//2018 06 25 - ealm - Adding base stat scaling flags
					$new_stat = $rom->getEncounterWord($i, 10 + $j * 2);
					
					//I actually don't want HP to scale the same as other stats.  Let's "soften" the effect of the scalar if it's over 100%.
					$scalar = $Flags["BossScaling"]/100;
					//Turns 500/400/300/200 into 300/250/200/150.
					if($scalar > 1) $scalar = ($scalar - 1) / 2 + 1;
										
					$rom->setEncounterWord($i, 10 + $j * 2, $new_stat);
				}else{
					//2018 06 25 - ealm - Renaming this variable for clarity.  This is the growth value for this stat.
					$stat_growth = $rom->getMonsterByte($MonsterGrowthIndex, 12 + $j);
					//Divide by total growth stats to get this stat's "share" ratio, multiply by total base stats
					$new_stat = floor($stat_growth * $total_stats / $total_growth_stats);
					
					//2018 06 25 - ealm - Adding base stat scaling flags
					$scalar = 1;
					if($is_boss_I_think) $scalar = $Flags["BossScaling"]/100;
					else $scalar = $Flags["StatScaling"]/100;
					$new_stat *= $scalar;
					
					
					
					//Everyone gets minimum 10 HP and 5 str, agi, def, int.  (MP can be zero)
					//If this is our starting monster (Slash), double the baseline for all of those.
					if($j == 0){
						if($new_stat < 10 * (($i == 0) ? 2 : 1)) $new_stat = 10 * (($i == 0) ? 2 : 1);
					}elseif(($j == 2) or ($j == 3) or ($j == 4) or ($j == 5)){
						if($new_stat < 5 * (($i == 0) ? 2 : 1)) $new_stat = 5 * (($i == 0) ? 2 : 1);
					}
					//2018 06 25 - ealm - Why wasn't this already in here?  Please cap stats (except boss HP, handled above) at 999...
					if($new_stat > 999) $new_stat = 999;
					
					$rom->setEncounterWord($i, 10 + $j * 2, $new_stat);
				}
			}
		}
		//Ramp up early EXP gains.
		$exp = $rom->getEncounterWord($i, 6);
		if($exp < 20) {
			$rom->setEncounterWord($i, 6, $exp*2.5);
		} elseif($exp < 40) {
			$rom->setEncounterWord($i, 6, $exp*2);
		} elseif($exp < 100) {
			$rom->setEncounterWord($i, 6, $exp*1.5);
		}
		$global_exp_scalar = $Flags["EXPScaling"]/100;
		$exp = $rom->getEncounterWord($i, 6) * $global_exp_scalar;
		// EXP is 2 bytes. Don't overflow and end up with a small EXP amount.
		if ($exp > 0xFFFF) {
			$exp = 0xFFFF;
		}
		$rom->setEncounterWord($i, 6, $exp);
		
		//If we're in Genius Mode, all wild monsters get 999 int
		if($Flags["GeniusMode"] == "On"){
			$rom->setEncounterWord($i, 10 + 5*2, 999);
		}

		
		//Now, let's teach random encounters their skills.  We'll give them the three they're supposed to learn, plus a bonus skill, and let it level up appropriately.
		$lv  = $rom->getEncounterByte($i, 9);
		$hp  = $rom->getEncounterWord($i, 10);
		$mp  = $rom->getEncounterWord($i, 12);
		$atk = $rom->getEncounterWord($i, 14);
		$def = $rom->getEncounterWord($i, 16);
		$agl = $rom->getEncounterWord($i, 18);
		$int = $rom->getEncounterWord($i, 20);
		
		for($j = 0; $j < 4; $j++){
			//Loop through all three skills the monster should learn, plus a BONUS SKILL
			if($j <> 3){
				$skill = $rom->getMonsterByte($MonsterGrowthIndex, 8 + $j);
			}else{
				$skill = Random() % 169 + 1;
				//Re-roll until this isn't the same skill as the three it innately learns.
				while($skill == $rom->getMonsterByte($MonsterGrowthIndex, 8) ||
					  $skill == $rom->getMonsterByte($MonsterGrowthIndex, 9) ||
					  $skill == $rom->getMonsterByte($MonsterGrowthIndex, 10))
					  {
					$skill = Random() % 169 + 1;
				}
			}
			
			//This variable is the "return value" from our loop.
			$return_skill = 0xFF; //0xFF is "no skill".
			while(true){
				//"Skill" is the ID of the skill we're trying to learn.
				$skill_qry = "select * from dragonwarriormonsters2_skills where id = ".$skill." and lv <= ".$lv." and hp <= ".$hp." and mp <= ".$mp." and atk <= ".$atk." and def <= ".$def." and agl <= ".$agl." and `int` <= ".$int;
				execute($skill_qry);
				$result = get();
				if(count($result) !== 0){
					//If we can learn this skill, plug it into return_skill
					$return_skill = $result["id"];
					$skill = $result["SUCCESSOR"];
					//echo 'Evolve into '.$result['Name'].': '.$lv.', '.$hp.', '.$mp.', '.$atk.', '.$def.', '.$agl.', '.$int.'<br>';
					if($skill == 0){
						//Break if there is no next skill to look up.
						break;
					}
				}else{
					//echo 'No evolution: '.$lv.', '.$hp.', '.$mp.', '.$atk.', '.$def.', '.$agl.', '.$int.'<br>';
					//Break if we don't qualify for the current skill.
					break;
				}
			}
			$rom->setEncounterByte($i, 2 + $j, $return_skill);
		}
		//Hoodsquid should always know LureDance as its fourth move
		if($i == $magicValues["HoodSquid Drop Encounter"]){
			$rom->setEncounterByte($i, 4, $SkillIDsByName["LureDance"]);
		}
		
		//Swap empty moves to the back.  Just gonna "brute force" a bubble sort; could be more efficient but it's nine swaps max so whatever.
		for($swapCounter = 0; $swapCounter < 3; $swapCounter++) {
			for($j = 2; $j <= 4; $j++){
				if($rom->getEncounterByte($i, $j) == 0xFF){
					$rom->swapEncounterBytes($i, $j, $j + 1);
				}
			}
		}
		//END ENCOUNTER SKILLS
	}

	// Old to-do list:
	//TODO: If it needs it, make starting monster have a minimum of 10 on each stat (15-20 for HP/MP?)
	//TODO: Pick from three monsters instead of just selecting one
	//TODO: Distribute stats on monsters by deleveling them and then leveling them back up
	//TODO: Does everyone actually have 31 int growth or did I forget that?
	//TODO: All enemy monsters seem to target back monster?
	//TODO: Why did Putrepup in Ice world hit so hard?
	//TODO: Sky world enemies hurt!!!
	//TODO: Mudo's HP was randomized and it hurt WutFace
	//TODO: Make WLD zero for all random encounters
	//TODO: Why is this where the TODOs are?
	//TODO: Maybe make it easier for random encounters to learn skills, so that early monsters know things? (Only use level requirement?)
	
	return true;
}

function CodePatches($rom){
	//Wherever "clear water" is mentioned, write "tonic" over the word "water"
	//TODO: Find more of these; Bizhawk's text search is glitchy.  Do a text dump?
	$rom->writeText(0x266624, "tonic");
	$rom->writeText(0x0A107B, "tonic");
	
	//TODO: This code to randomize your team name in the arena is glitchy...  If I write too many (or too few?) characters over existing text, the text box can glitch out.  Might need to figure out what the extra characters are between messages...
	//This is an array of every instance of "Master ____ and the (Team Name)!", which changes with every rank.
	//These aren't hex values because my text dump is broken into 100-character lines.  Don't judge me.
	/*
	$Masters = [[155640,30],[155676,29],[155711,28],[155745,30],[155781,27],[155885,32],[156338,30],[156441,30],[156549,29],[156656,31],[156766,28],[156866,31],[156976,32]];
	$Titles = ["Master","Captain","Commander","Mistress","Lord","Lady","Sir","King","Queen","Princess","Prince","Duke","Duchess","Speedrunner","WR Holder","Lieutenant","Corporal","Officer","Admiral","Pirate Lord"];
	$Teams = ["Team","Sweaty Yetis","Alefgard Alliance","Dream Team","Good Monsters","Slime Time","Fun Friends","Berserkers","Random Monsters","Buttermilk Ranch"];
	for($i = 0; $i < count($Masters); $i++){
		//We've got 31 characters to work with, including 0xf900 which'll be the player's name
		//"(Title) ____ and the (Team)" = 12 characters + title/team names (Player name is two characters)
		//(Note to self:  I gotta make sure the longest title and the shortest team name don't exceed 20 characters)
		$title = Random() % count($Titles);
		$team = Random() % count($Teams);
		//Roll team names until we get one that's short enough.
		while(strlen($Titles[$title]) + strlen($Teams[$team]) + 12 > $Masters[$i][1]){
			$team = Random() % count($Teams);
		}
		$_team_with_padding = $Teams[$team];
		while(strlen($Titles[$title]) + strlen($_team_with_padding) + 12 < $Masters[$i][1]){
			$_team_with_padding .= ' ';
		}
		
		$rom->writeText($Masters[$i][0], $Titles[$title].' ');
		$rom->data[$Masters[$i][0]+strlen($Titles[$title])+1] = chr(0xF9);
		$rom->data[$Masters[$i][0]+strlen($Titles[$title])+2] = chr(0x00);
		$rom->writeText($Masters[$i][0]+strlen($Titles[$title])+3, ' and the '.$_team_with_padding);
	}
	*/

	//Patch to open up the monster breeder's back door early (As soon as you get Slash)
	//0x3FAD-0x3FFF are no-ops, so we'll write our new code in that range.
	
	//First, move these three addresses that we're about to replace with the JP command into the new code block
	$rom->data[0x3FAD] = $rom->data[0x186C];
	$rom->data[0x3FAE] = $rom->data[0x186D];
	$rom->data[0x3FAF] = $rom->data[0x186E];
	//$rom->data[0x3FB0] = $rom->data[0x186F];
	
	$rom->data[0x186C] = chr(0x00); 	//	nop
	$rom->data[0x186D] = chr(0xC3); 	//	JP 0x3FAD	Jump to 0x3FAD
	$rom->data[0x186E] = chr(0xAD);	//
	$rom->data[0x186F] = chr(0x3F);	//
	
	//Now, add my own code...
	$rom->data[0x3FB1] = chr(0x3E);	//LD a,FCh		Load 0xFC into register a
	$rom->data[0x3FB2] = chr(0xFC);	//
	$rom->data[0x3FB3] = chr(0x5F);	//LD e,a		Load register A into register E
	
	//$rom->data[0x3FB4] = chr(0xCB);	//SLA C			(Turn C from 4 to 8 with a left shift, two-byte command)
	//$rom->data[0x3FB5] = chr(0x21);	//
	$rom->data[0x3FB4] = chr(0x0E);	//SLA C			(Load the number 8 into register c)
	$rom->data[0x3FB5] = chr(0x08);	//
	$rom->data[0x3FB6] = chr(0x1A);	//ld a,(de)
	$rom->data[0x3FB7] = chr(0xB1);	//or c
	$rom->data[0x3FB8] = chr(0x12);	//ld (de),a
	
	$rom->data[0x3FB9] = chr(0xC9);	//	RET 		End subroutine (Instruction previously at 0x186F)
	//TODO: Is it possible to get rid of all of the people in front of the monster breeder?
}

?>
