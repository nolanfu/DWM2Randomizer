<?php

class Rom {
	public static $localRomDirectory = "F:\\ROMs\\Gameboy\\DWM2TA\\";
	public static $localRomInputName = "DWM2TA.gbc";
	public static $localRomOutputName = "DWM2TGA_Random.gbc";
	public static $loadLocalRom = false;
	public static $saveLocalRom = false;

	public static $magicValues = array(
		'HoodSquid Drop Encounter' => 26,

		'Healing Skills' => array(
			22,23,24,25,26, // Healing spells
			30,31,32, // Vivify/Revive/Farewell
			123,124, // LifeDance/Hustle
			133, // TatsuCall (Tatsu can cast HealMore)
			160,161, // LifeSong/LoveRain
		),
	);

	public $data = "";
	public $monsterNames = array(); // Monster ID -> Name
	public $monsterIDsByName = array(); // Monster Name -> ID
	public $skillNames = array(); // Skill ID -> Name
	public $skillIDsByName = array(); // Skill Name -> ID
	public $allowedMonsterIDs = array();
	public $monsterGrowthStatsIndex = array(); //This is the position of the monster in the "growths" list

	function __construct() {
		$this->configureMap();
		$this->populateMetadata();
	}

	function configureMap() {
		$this->map = array();
		$this->map["encounters"] = array();
		$this->map["encounters"]["data_length"] = 26;
		$this->map["encounters"]["first_bank_start"] = 0xD0075 + $this->map["encounters"]["data_length"];
		$this->map["encounters"]["first_bank_count"] = 591;
		$this->map["encounters"]["second_bank_start"] = 0x288056 + $this->map["encounters"]["data_length"];
		$this->map["encounters"]["second_bank_count"] = 93;
		$this->map["encounters"]["count"] = $this->map["encounters"]["first_bank_count"] + $this->map["encounters"]["second_bank_count"];

		$this->map["monsters"] = array(
			"data_length" => 47,
			"start" => 0xD436A,
			"count" => 323,
		);

		$this->map["item_strings"] = array(
			"start" => 0x224E3,
			"count" => 91,
		);

		$this->map["item_behavior"] = array(
			"start" => 0x58CC2,
			"count" => 99,
			"data_length" => 13,
		);
	}

	function load() {
		try
		{
			$filename = "";
			if (Rom::$loadLocalRom) {
				$filename = Rom::$localRomDirectory . Rom::$localRomInputName;
			} else {
				$filename = $_FILES['InputFile']['tmp_name'];
			}

			$file = fopen($filename, "rb");
			$this->data = fread($file,filesize($filename));
			fclose($file);
		} catch (Exception $e) {
			return false;
		}
		return true;
	}

	function isLoaded() {
		return (strlen($this->data) > 0);
	}

	function localSave() {
		$filename = Rom::$localRomDirectory . Rom::$localRomOutputName;
		$file = fopen($filename, "w");
		fwrite($file,$this->data);
		fclose($file);
	}

	function populateMetadata(){
		$monster_list_query = "SELECT * FROM dragonwarriormonsters2 order by id asc";
		executeQuery($monster_list_query);
		$this->monsterNames = array();
		$this->monsterIDsByName = array();
		while($monster = get()){
			$this->monsterNames[$monster["id"]] = $monster["name"];
			$this->monsterIDsByName[$monster["name"]] = $monster["id"];
		}

		$skill_list_query = "SELECT * FROM dragonwarriormonsters2_skills order by id asc";
		executeQuery($skill_list_query);
		while($skill = get()){
			$this->skillNames[$skill["id"]] = $skill["Name"];
			$this->skillIDsByName[$skill["Name"]] = $skill["id"];
		}
		
		//This is the ID stored in the SRAM that determines which monster you have.
		//It's also used within the table of base-stats for each monster.
		//NOTE 0x1B is Butch and I don't think he should be used?
		for ($i = 0; $i <= 0x17E; $i++) {
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
				$this->monsterGrowthStatsIndex[] = $i;
				if ($i != 0x1B) {
					$this->allowedMonsterIDs[] = $i;
				}
			}
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

	function calcStructuredOffset($key, $i, $offset) {
		return $this->map[$key]["start"] + $i * $this->map[$key]["data_length"] + $offset;
	}

	function calcMonsterOffset($i, $offset) {
		return $this->calcStructuredOffset("monsters", $i, $offset);
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
		if ($i < $this->map["encounters"]["first_bank_count"]) {
			return ($this->map["encounters"]["first_bank_start"] + $i * $this->map["encounters"]["data_length"] + $offset);
		}
		return ($this->map["encounters"]["second_bank_start"] + ($i - $this->map["encounters"]["first_bank_count"]) * $this->map["encounters"]["data_length"] + $offset);
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

	function isBossRecruit($i) {
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
