<?php

function main() {
	global $error;
	global $error_message;
	$rom = new Rom();
	$modder = modderFromArguments();
	
	if(array_key_exists("Submit",$_REQUEST)){
		if (!$rom->load()) {
			$error = true;
			$error_message = "<br>Empty file name(s) or unable to open files. Please verify the files exist.";
			return;
		}

		$rom->populateMetadata();
		
		//Some functions to dump the ROM in hexidecimal or text format
		//RomStructuredDataDump($rom);
		//RomDump($rom);
		//RomTextDump($rom);
		//die();
		if($rom->isLoaded()){
			$modder->apply($rom);
			Analytics($modder);
			$rom->save();
		}
	}
	return $modder;
}

function modderFromArguments() {
	global $FlagSettings;

	$flags = array();
	if(array_key_exists("StartingMonster",$_REQUEST)){
		$flags["StartingMonster"] = trim($_REQUEST["StartingMonster"]);
	}else{
		$flags["StartingMonster"] = 0;
	}
	foreach ($FlagSettings as $name => $settings) {
		if(array_key_exists("default", $settings)) {
			if(array_key_exists($name,$_REQUEST)) {
				$flags[$name] = trim($_REQUEST[$name]);
			}else{
				$flags[$name] = $settings["default"];
			}
		}
	}
	if(array_key_exists("Seed",$_REQUEST)){
		$flags["Seed"] = trim($_REQUEST["Seed"]);
	}else{
		$flags["Seed"] = 0;
	}
	return new RomModder($flags, $flags["Seed"]);
}

function Analytics($modder) {
	//Logging IP address, user agent, flags, seed, and current time.
	//Is your user agent any of my business?  Not really, but don't worry about it.
	execute("
		INSERT INTO dwm2r_activitymonitor
		(IPAddress,UserAgent,Flags,Seed,CreatedDTS)
		VALUES (
			'".$_SERVER['REMOTE_ADDR']."',
			'".$_SERVER['HTTP_USER_AGENT']."',
			'".trim($_REQUEST["Flags"])."',
			'".$modder->flags["Seed"]."',
			NOW()
		)
	");
}

function flagRadioButtons($name, $currentValue, $label, $options) {
?>
	<div class="row">
		<div class="col-sm"><?php echo $label ?></div>
<?php
		foreach ($options as $option) {
			$id = strtolower($name) . '_' . strtolower($option["value"]);
?>
			<div class="col-sm"><input type="radio" name="<?php echo $name ?>" value="<?php echo $option["value"] ?>" id="<?php echo $id ?>" <?php echo $currentValue == $option["value"] ? 'checked' : '' ?> /> <label for="<?php echo $id ?>" title="<?php echo $option["description"] ?>"><?php echo (array_key_exists("label", $option) ? $option["label"] : $option["value"]) ?></label></div>
<?php
		}
?>
	</div>
<?php
}

function drawForm($modder) {
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
		<div class="col-sm"><input type="text" name="Seed" value="<?php echo $modder->flags['Seed'] ?>" id="seed_input" size=10 /></div>
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
				<option value="<?php echo $monster["id"]; ?>" <?php echo $modder->flags['StartingMonster'] == $monster["id"] ? 'selected' : '' ?>><?php echo $monster["name"]; ?></option>
				<?php
					}
				?>
			</select>
		</div>
	</div>
<?php
	foreach ($FlagSettings as $name => $settings) {
		flagRadioButtons($name, $modder->flags[$name], $settings["label"], $settings["options"]);
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
