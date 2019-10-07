<?php

function main()
{
	global $romData;
	
	parseArguments();
	$rom = new Rom();
	
	if(array_key_exists("Submit",$_REQUEST)){
		if (!$rom->load())
			return;

		PopulateMetadata();
		
		//Some functions to dump the ROM in hexidecimal or text format
		//RomStructuredDataDump($rom);
		//RomDump($rom);
		//RomTextDump($rom);
		//die();
		if($rom->isLoaded()){
			hackRom($rom);
			$rom->save();
		}
	}
}

function parseArguments() {
	global $Flags;
	global $FlagSettings;
	global $initial_seed;

	if(array_key_exists("StartingMonster",$_REQUEST)){
		$Flags["StartingMonster"] = trim($_REQUEST["StartingMonster"]);
	}else{
		$Flags["StartingMonster"] = 0;
	}
	foreach ($FlagSettings as $name => $settings) {
		if(array_key_exists("default", $settings)) {
			if(array_key_exists($name,$_REQUEST)) {
				$Flags[$name] = trim($_REQUEST[$name]);
			}else{
				$Flags[$name] = $settings["default"];
			}
		}
	}
	if(array_key_exists("Seed",$_REQUEST)){
		$Flags["Seed"] = trim($_REQUEST["Seed"]);
	}else{
		$Flags["Seed"] = 0;
	}
	$initial_seed = $Flags["Seed"];
}

function hackRom($rom)
{
	//This function checks the selected flags and determines which randomization subroutines to run
	
	//First, let's seed the random number generator.  We're using DW3's generator, which uses three variables.
	global $counter;
	global $seed;
	global $discard;
	global $initial_seed;
	global $flags;
	$tmp_seed = $initial_seed;
	
	$counter = $tmp_seed % 256;
	$tmp_seed = floor($tmp_seed / 256);
	$seed = $tmp_seed % 65536;
	$tmp_seed = floor($tmp_seed / 65536);
	$discard = $tmp_seed % 16;
	
	for($j = 0; $j < $flags["StartingMonster"]; $j++){
		//Burn random numbers to make monster choice affect the randomization.
		//TODO: Should all flags do this?
		Random();
	}
	
	ShuffleMonsterGrowth($rom);
	ShuffleMonsterResistances($rom);
	ShuffleMonsterSkills($rom);
	ShuffleEncounters($rom);
	CodePatches($rom);
	Analytics();
	
	return true;
}


function Analytics()
{
	//This function logs intrusive analytical data so I can see how often this randomizer gets played
	global $initial_seed;
	global $flags;
	
	//Logging IP address, user agent, flags, seed, and current time.
	//Is your user agent any of my business?  Not really, but don't worry about it.
	execute("
		INSERT INTO dwm2r_activitymonitor
		(IPAddress,UserAgent,Flags,Seed,CreatedDTS)
		VALUES (
			'".$_SERVER['REMOTE_ADDR']."',
			'".$_SERVER['HTTP_USER_AGENT']."',
			'".trim($_REQUEST["Flags"])."',
			'".$initial_seed."',
			NOW()
		)
	");
}

function flagRadioButtons($name, $label, $options) {
	global $Flags;
?>
	<div class="row">
		<div class="col-sm"><?php echo $label ?></div>
<?php
		foreach ($options as $option) {
			$id = strtolower($name) . '_' . strtolower($option["value"]);
?>
			<div class="col-sm"><input type="radio" name="<?php echo $name ?>" value="<?php echo $option["value"] ?>" id="<?php echo $id ?>" <?php echo $Flags[$name] == $option["value"] ? 'checked' : '' ?> /> <label for="<?php echo $id ?>" title="<?php echo $option["description"] ?>"><?php echo $option["label"] ? $option["label"] : $option["value"] ?></label></div>
<?php
		}
?>
	</div>
<?php
}

function drawForm() {
	global $Flags;
	global $FlagSettings;
?>
<form action="index.php" method="POST" enctype="multipart/form-data">
<div class="container-fluid" style="max-width:800px;margin:0px auto;">
	<div class="row">
		<div class="col-sm" style="text-align:center"><h1>Dragon Warrior Monsters 2 Randomizer!</h1></div>
	</div>
	<div class="row">
		<div class="col-sm"><label for="flags_input">Flags:</label></div>
		<div class="col-sm"><input type="text" name="Flags" value="" id="flags_input" size=15 /></div>
		<div class="col-sm"><label for="seed_input">Seed:</label></div>
		<div class="col-sm"><input type="text" name="Seed" value="<?php echo $Flags['Seed'] ?>" id="seed_input" size=10 /></div>
		<div class="col-sm"><input type="button" name="NewSeedBtn" value="New Seed" id="NewSeedBtn" /></div>
	</div>
	<div class="row">
		<div class="col-sm"><label for="input_file">Input File:</label></div>
		<div class="col-sm"><input type="file" name="InputFile" value="" id="input_file" size=50 /></div>
	</div>
	<div class="row">
		<div class="col-sm">Starting Monster</div>
		<div class="col-sm">
			<select name="StartingMonster">
				<option value="0">Random</option>
				<?php
					$monster_list_query = "SELECT * FROM dragonwarriormonsters2 order by id asc";
					execute($monster_list_query);
					while($monster = get()){
				?>
				<option value="<?php echo $monster["id"]; ?>" <?php echo $Flags['StartingMonster'] == $monster["id"] ? 'selected' : '' ?>><?php echo $monster["name"]; ?></option>
				<?php
					}
				?>
			</select>
		</div>
	</div>
<?php
	foreach ($FlagSettings as $name => $settings) {
		flagRadioButtons($name, $settings["label"], $settings["options"]);
	}
?>
	<div class="row">
		<div class="col-sm" style="text-align:center"><input type="Submit" name="Submit" value="Randomize!"></div>
	</div>
</div>
</form>
<div class="container-fluid" style="max-width:800px;margin:0px auto;">
	<div class="row">
		This randomizer will work with both Cobi's Journey or Tara's Adventure, but the resulting rom will vary slightly based on which one you use.  Note that this randomizer is still in beta, and consequently has a few known bugs and isn't quite feature-complete.
	</div>
	<div class="row">
		For more details on how this randomizer works, planned features and changelog, and the source code/data, checkout the ReadMe on GitHub: <a href="https://github.com/TheCowness/DWM2Randomizer">https://github.com/TheCowness/DWM2Randomizer</a>
	</div>
</div>

<?php
}

?>
