<?php

function loadRom()
{
	global $romData;
	global $localRomDirectory;
	global $localRomInputName;
	global $loadLocalRom;
	try
	{
		$filename = "";
		if ($loadLocalRom) {
			//This is code for processing a ROM that I already have on the server
			$filename = $localRomDirectory.$localRomInputName;
		} else {
			$filename = $_FILES['InputFile']['tmp_name'];
		}

		$file = fopen($filename, "rb");
		$romData = fread($file,filesize($filename));
		
		//When $romData is a standard PHP array, this 4MB file takes up over 128M of RAM.
		//When $romData is an SplFixedArray, this 4MB file takes up 67MB of RAM.
		//for($i = 0; $i < strlen($_romData); $i++){
		//	$romData[$i] = ord($_romData[$i]);
		//}
		
		fclose($file);
	}
	catch (Exception $e)
	{
		$error_message = "<br>Empty file name(s) or unable to open files.  Please verify the files exist.";
		return false;
	}
	return true;
}

function saveRom()
{
	global $romData;
	global $initial_seed;
	global $localRomDirectory;
	global $localRomOutputName;
	global $saveLocalRom;
	
	if ($saveLocalRom) {
		//This is code for saving a rom to the server instead of spitting it back out to the user
		$filename = $localRomDirectory.$localRomOutputName;
		$file = fopen($filename, "w");
		fwrite($file,$romData);
		fclose($file);
	} else {
		header('Content-Disposition: attachment; filename="DWM2_Rando_'.$initial_seed.'.gbc"');
		header("Content-Size: ".strlen($romData)*512);
		echo $romData;
		die();
	}
}

function swap($firstAddress, $secondAddress) {
	//This function just switches one value in the rom data with another.  Useful when shuffling data.
	global $romData;
	$holdAddress = $romData[$secondAddress];
	$romData[$secondAddress] = $romData[$firstAddress];
	$romData[$firstAddress] = $holdAddress;
}

function getByte($offset) {
	global $romData;
	return ord($romData[$offset]);
}

function getWord($offset) {
	global $romData;
	return ord($romData[$offset]) + ord($romData[$offset + 1])*256;
}

function setByte($offset, $value) {
	global $romData;
	$romData[$offset] = chr(floor($value % 256));
}

function setWord($offset, $value) {
	global $romData;
	$romData[$offset] = chr(floor($value % 256));
	$romData[$offset + 1] = chr(floor(($value / 256) % 256));
}

function calcMonsterOffset($i, $offset) {
	global $first_monster_byte;
	global $monster_data_length;
	return $first_monster_byte + $i * $monster_data_length + $offset;
}

function getMonsterByte($i, $offset)
{
	return getByte(calcMonsterOffset($i, $offset));
}

function getMonsterWord($i, $offset)
{
	return getWord(calcMonsterOffset($i, $offset));
}

function setMonsterByte($i, $offset, $value)
{
	return setByte(calcMonsterOffset($i, $offset), $value);
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

function getEncounterByte($i, $offset)
{
	return getByte(calcEncounterOffset($i, $offset));
}

function getEncounterWord($i, $offset)
{
	return getWord(calcEncounterOffset($i, $offset));
}

function setEncounterByte($i, $offset, $value)
{
	return setByte(calcEncounterOffset($i, $offset), $value);
}

function setEncounterWord($i, $offset, $value)
{
	return setWord(calcEncounterOffset($i, $offset), $value);
}

function swapEncounterBytes($i, $offset_a, $offset_b)
{
	swap(calcEncounterOffset($i, $offset_a), calcEncounterOffset($i, $offset_b));
}


function WriteText($address, $text)
{
	//This function translates text to binary code.  Text is uncompressed in DWM2, but it isn't mapped the same as ASCII.
	global $romData;
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
		$romData[$address + $i] = chr($x);
		$i++;
	}
}


?>
