<?php

class RandomGenerator {
	public $initial_seed = 0;
	public $seed = 0;
	public $counter = 0;
	public $discard = 0;

	function __construct($init) {
		$this->initSeed($init);
	}

	function initSeed($init) {
		$this->initial_seed = $init;

		$tmp_seed = $initial_seed;
		$this->counter = $tmp_seed % 256;
		$tmp_seed = floor($tmp_seed / 256);
		$this->seed = $tmp_seed % 65536;
		$tmp_seed = floor($tmp_seed / 65536);
		$this->discard = $tmp_seed % 16;
	}

	//I'm a fan of memes.  Are you a fan of memes?  Here's a good meme: This is Dragon Warrior III's random number generator.
	//Airkix please do not RNG manip my randomizer.
	function random($MultiRandom = true){ //Defaulting MultiRandom to true for this randomizer.
		//If we're using the "Battle RNG" value, we throw away that many values before keeping one.
		$discarded = 0; //Throw away this many values
		if($MultiRandom == true){
			$discarded = $this->discard + 1;
		}
		for($j = 0; $j < $discarded+1; $j++){
			for ($i = 0; $i < 16; $i++){
				$this->seed = (($this->seed << 1) % 65536) ^ ((($this->seed >> 15) ^ 1) ? 0x1021 : 0);
				if($this->seed >= 65536) $this->seed = $this->seed % 65536;
			}
			$this->counter++;
			if($this->counter >= 256) $this->counter = $this->counter % 256;
		}
		return ($this->counter + $this->seed) % 256;
	}
}

?>
