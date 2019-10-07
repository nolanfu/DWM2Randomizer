<?php

$initial_seed = 0;

//I'm a fan of memes.  Are you a fan of memes?  Here's a good meme: This is Dragon Warrior III's random number generator.
//Airkix please do not RNG manip my randomizer.
$seed = 0;
$counter = 0;
$discard = 0;

function Random($MultiRandom = true){ //Defaulting MultiRandom to true for this randomizer.
	global $counter;
	global $seed;
	global $discard;
	//If we're using the "Battle RNG" value, we throw away that many values before keeping one.
	$discarded = 0; //Throw away this many values
	if($MultiRandom == true){
		$discarded = $discard + 1;
	}
	for($j = 0; $j < $discarded+1; $j++){
		for ($i = 0; $i < 16; $i++){
			$seed = (($seed << 1) % 65536) ^ ((($seed >> 15) ^ 1) ? 0x1021 : 0);
			if($seed >= 65536) $seed = $seed % 65536;
		}
		$counter++;
		if($counter >= 256) $counter = $counter % 256;
	}
	return ($counter + $seed) % 256;
}

?>
