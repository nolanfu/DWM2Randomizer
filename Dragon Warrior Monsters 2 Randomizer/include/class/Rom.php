<?php

class Rom {
	public static $localRomDirectory = "F:\\ROMs\\Gameboy\\DWM2TA\\";
	public static $localRomInputName = "DWM2TA.gbc"; 
	public static $localRomOutputName = "DWM2TGA_Random.gbc";
	public static $loadLocalRom = false;
	public static $saveLocalRom = false;
	public $data = "";

	function load() {
		try
		{
			$filename = "";
			if (Rom::$loadLocalRom) {
				//This is code for processing a ROM that I already have on the server
				$filename = Rom::$localRomDirectory . Rom::$localRomInputName;
			} else {
				$filename = $_FILES['InputFile']['tmp_name'];
			}

			$file = fopen($filename, "rb");
			$this->data = fread($file,filesize($filename));
			fclose($file);
		}
		catch (Exception $e)
		{
			$error_message = "<br>Empty file name(s) or unable to open files. Please verify the files exist.";
			return false;
		}
		return true;
	}

	function isLoaded() {
		return (strlen($this->data) > 0);
	}

	function save() {
		// TODO: Move some of this out to a controller 
		global $initial_seed;
		
		if (Rom::$saveLocalRom) {
			$filename = Rom::$localRomDirectory . Rom::$localRomOutputName;
			$file = fopen($filename, "w");
			fwrite($file,$this->data);
			fclose($file);
		} else {
			header('Content-Disposition: attachment; filename="DWM2_Rando_'.$initial_seed.'.gbc"');
			header("Content-Size: ".strlen($this->data)*512);
			echo $this->data;
			die();
		}
	}

	function swap($firstAddress, $secondAddress) {
		$holdAddress = $this->data[$secondAddress];
		$this->data[$secondAddress] = $this->data[$firstAddress];
		$this->data[$firstAddress] = $holdAddress;
	}

	function getByte($offset) {
		return ord($this->data[$offset]);
	}

	function getWord($offset) {
		return ord($this->data[$offset]) + ord($this->data[$offset + 1])*256;
	}

	function setByte($offset, $value) {
		$this->data[$offset] = chr(floor($value % 256));
	}

	function setWord($offset, $value) {
		$this->data[$offset] = chr(floor($value % 256));
		$this->data[$offset + 1] = chr(floor(($value / 256) % 256));
	}

	function calcMonsterOffset($i, $offset) {
		global $first_monster_byte;
		global $monster_data_length;
		return $first_monster_byte + $i * $monster_data_length + $offset;
	}

	function getMonsterByte($i, $offset) {
		return $this->getByte($this->calcMonsterOffset($i, $offset));
	}

	function getMonsterWord($i, $offset) {
		return $this->getWord($this->calcMonsterOffset($i, $offset));
	}

	function setMonsterByte($i, $offset, $value) {
		return $this->setByte($this->calcMonsterOffset($i, $offset), $value);
	}

	function calcEncounterOffset($i, $offset) {
		global $encounter_data_length;
		global $first_bank_encounter_start;
		global $first_bank_encounter_count;
		global $second_bank_encounter_start;
		
		if ($i < $first_bank_encounter_count) {
			return ($first_bank_encounter_start + $i * $encounter_data_length + $offset);
		}
		return ($second_bank_encounter_start + ($i - $first_bank_encounter_count) * $encounter_data_length + $offset);
	}

	function getEncounterByte($i, $offset) {
		return $this->getByte($this->calcEncounterOffset($i, $offset));
	}

	function getEncounterWord($i, $offset) {
		return $this->getWord($this->calcEncounterOffset($i, $offset));
	}

	function setEncounterByte($i, $offset, $value) {
		return $this->setByte($this->calcEncounterOffset($i, $offset), $value);
	}

	function setEncounterWord($i, $offset, $value) {
		return $this->setWord($this->calcEncounterOffset($i, $offset), $value);
	}

	function swapEncounterBytes($i, $offset_a, $offset_b) {
		$this->swap($this->calcEncounterOffset($i, $offset_a), $this->calcEncounterOffset($i, $offset_b));
	}

	//This function translates text to binary code.  Text is uncompressed in DWM2, but it isn't mapped the same as ASCII.
	function writeText($address, $text) {
		$i = 0;
		for($j = 0; $j < strlen($text); $j++)
		{
			$c = $text[$j];
			$x = 0;
			if($c >= 'a' && $c <= 'z')
			{
				$x = ord($c) - ord('a') + 0x24;
			}else if($c >= 'A' && $c <= 'Z')
			{
				$x = ord($c) - ord('A') + 0x0A;
			}else if($c >= '0' && $c <= '9')
			{
				$x = ord($c) - '1';
				if($c == '0')
				{
					$x += 10;
				}
			}
			else
			{
				$x = 0x90;
			}
			$this->data[$address + $i] = chr($x);
			$i++;
		}
	}

	function isBossEncounter($i) {
		// Treat all encounters that have a 0% recruitment rate as bosses. This includes arena fights.
		if ($this->getEncounterByte($i, 8) == 7) { return 1; }

		// There are three boss encounters that apparently allow normal recruitment mechanics from the fight.
		switch($i){
			case 6: //Oasis CurseLamp
			case 399: //Pirate KingSquid
			case 426: //Sky MadCondor
				return 1;
		}
		return 0;
	}

	function isBossRecruit() {
		// Some of the bosses can be recruited through storyline means, but their recruitable stats
		// are stored in a different encounter than the actual boss fight.
		// TODO: Missing some?
		switch($i){
			case 386: //Oasis Beavern
			case 7: //Oasis CurseLamp
			case 26: //Pirate Hoodsquid
			case 427: //Sky MadCondor
			case 430: //Pirate KingSquid
				return 1;
		}
		return 0;
	}

	function isArenaEncounter($i) {
		// Kid class: 130-136
		// Class C: 137-145
		// Class B: 146-151?
		// Class A: 152-160
		// Class S: 161-167
		// KingLeo/Azurile/Divinegon: 168-170
		// Post-game arena randos?: 331-375
		if ($i >= 130 && $i <= 170) { return 1; }
		if ($i >= 331 && $i <= 375) { return 1; }
		return 0;
	}

	// Other encounter notes:
	// Randomly found mimics?: 117-124 (levels 1/5/10/15/20/25/30/35)
	// MedalKing egg rewards: 377-382 (MadCat, HornBeet, Skydragon, Octogon, Servant, Darck)
	// Wandering monster master randos?: 171-330

}

?>
