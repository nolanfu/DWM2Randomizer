<?php

class UIController {
	public $error = false;
	public $error_message = 'The following errors occurred while generating the new seed:';
	public $delivered_file = false;

	public $flagSettings = array(
		'Growth' => array(
			'default' => 'None',
			'label' => 'Monster Growth',
			'options' => array(
				array('value' => 'Redistribute', 'description' => 'Monster stat growth values will add to the same total, but will be randomly distributed.'),
				array('value' => 'Shuffle', 'description' => 'Monsters will keep the same six growth values, but they will be randomly shuffled.'),
				array('value' => 'None', 'description' => 'Do not randomize monster stats.'),
			),
		),

		'Resistance' => array(
			'default' => 'None',
			'label' => 'Monster Resistances',
			'options' => array(
				array('value' => 'Redistribute', 'description' => 'Monster stat resistance values will add to the same total, but will be randomly distributed.'),
				array('value' => 'Shuffle', 'description' => 'Monsters will keep the same 27 resistance values, but they will be randomly shuffled.'),
				array('value' => 'None', 'description' => 'Do not randomize monster resistances.'),
			),
		),

		'Skills' => array(
			'default' => 'None',
			'label' => 'Shuffle Monster Skills',
			'options' => array(
				array('value' => 'Random', 'description' => 'Monsters learn completely random skills. BeDragon is excluded.'),
				array('value' => 'Random With BeDragon', 'label' => 'Random With BeDragon <b>(!)</b>', 'description' => 'Monsters learn completely random skills, including BeDragon. Boo!'),
				array('value' => 'Random, No Healing', 'label' => 'Random, No Healing <b>(!)</b>', 'description' => 'Monsters learn completely random skills. BeDragon and all skills with healing capabilities are excluded.'),
				array('value' => 'None', 'description' => 'Do not randomize monster skills.'),
			),
		),

		'Encounters' => array(
			'default' => 'None',
			'label' => 'Shuffle Encounters',
			'options' => array(
				array('value' => 'Poorly', 'description' => 'Shuffle enemy types. Monsters\' stats add to the same total, proportional to their growth values. Later monsters are supposed to start with more skills, but no monsters start with advanced skills.'),
				array('value' => 'None', 'description' => 'Do not randomize encounters.'),
			),
		),

		'GeniusMode' => array(
			'default' => 'Off',
			'label' => 'Max Monster Intelligence',
			'options' => array(
				array('value' => 'On', 'description' => 'All wild monsters have 999 Int; all monsters have 31 int growth. (Overrides randomized base int/growth)'),
				array('value' => 'Off', 'description' => 'Do not max out monster intelligence.'),
			),
		),

		'EXPScaling' => array(
			'default' => '100',
			'label' => 'Experience Yields',
			'options' => array(
				array('value' => '0', 'label' => '0%', 'description' => 'Monsters will be worth 0% of their normal EXP yields (minimum 1 EXP)'),
				array('value' => '50', 'label' => '50%', 'description' => 'Monsters will be worth 50% of their normal EXP yields (minimum 1 EXP)'),
				array('value' => '100', 'label' => '100%', 'description' => 'Monsters will be worth 100% of their normal EXP yields'),
				array('value' => '200', 'label' => '200%', 'description' => 'Monsters will be worth 200% of their normal EXP yields'),
				array('value' => '500', 'label' => '500%', 'description' => 'Monsters will be worth 500% of their normal EXP yields'),
			),
		),

		'StatScaling' => array(
			'default' => '100',
			'label' => 'Wild Monster Stat Scaling',
			'options' => array(
				array('value' => '0', 'label' => '0%', 'description' => 'Wild monster stats will be altered based on this scalar'),
				array('value' => '50', 'label' => '50%', 'description' => 'Wild monster stats will be altered based on this scalar'),
				array('value' => '100', 'label' => '100%', 'description' => 'Wild monster stats will be altered based on this scalar'),
				array('value' => '200', 'label' => '200%', 'description' => 'Wild monster stats will be altered based on this scalar'),
				array('value' => '500', 'label' => '500%', 'description' => 'Wild monster stats will be altered based on this scalar'),
			),
		),

		'BossScaling' => array(
			'default' => '100',
			'label' => 'Boss Monster Stat Scaling',
			'options' => array(
				array('value' => '0', 'label' => '0%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '50', 'label' => '50%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '100', 'label' => '100%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '200', 'label' => '200%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '300', 'label' => '300%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '400', 'label' => '400%', 'description' => 'Boss stats will be altered based on this scalar'),
				array('value' => '500', 'label' => '500%', 'description' => 'Boss stats will be altered based on this scalar'),
			),
		),

		'YetiMode' => array(
			'default' => 'Off',
			'label' => 'Yeti Mode',
			'options' => array(
				array('value' => 'On', 'description' => 'All monsters are yetis!'),
				array('value' => 'Off', 'description' => 'Not all monsters are yetis.'),
			),
		),
	);

	public $modder = null;

	function modderFromArguments($args) {
		$flags = array();
		if(array_key_exists("StartingMonster",$args)){
			$flags["StartingMonster"] = trim($args["StartingMonster"]);
		}else{
			$flags["StartingMonster"] = 0;
		}
		foreach ($this->flagSettings as $name => $settings) {
			if(array_key_exists("default", $settings)) {
				if(array_key_exists($name,$args)) {
					$flags[$name] = trim($args[$name]);
				}else{
					$flags[$name] = $settings["default"];
				}
			}
		}
		if(array_key_exists("Seed",$args)){
			$flags["Seed"] = trim($args["Seed"]);
		}else{
			$flags["Seed"] = 0;
		}
		return new RomModder($flags, $flags["Seed"]);
	}

	function handleRequest() {
		$rom = new Rom();
		$this->modder = $this->modderFromArguments($_REQUEST);
		
		if(array_key_exists("Submit",$_REQUEST)){
			if (!$rom->load()) {
				$this->error = true;
				$this->error_message .= "<br>Empty file name(s) or unable to open files. Please verify the files exist.";
				return;
			}

			//Some functions to dump the ROM in hexidecimal or text format
			//RomStructuredDataDump($rom);
			//RomDump($rom);
			//die();
			if($rom->isLoaded()){
				$this->modder->apply($rom);
				$this->recordAnalytics();
				if (Rom::$saveLocalRom) {
					$rom->localSave();
				} else {
					$this->outputRom($rom, $this->modder->flags["Seed"]);
				}
			}
		}
	}

	function outputRom($rom, $initSeed) {
		header('Content-Disposition: attachment; filename="DWM2_Rando_'.$initSeed.'.gbc"');
		header("Content-Size: ".strlen($rom->data)*512);
		echo $rom->data;
		$this->delivered_file = true;
	}

	function recordAnalytics() {
		//Logging IP address, user agent, flags, seed, and current time.
		//Is your user agent any of my business?  Not really, but don't worry about it.
		executeQuery("
			INSERT INTO dwm2r_activitymonitor
			(IPAddress,UserAgent,Flags,Seed,CreatedDTS)
			VALUES (
				'".$_SERVER['REMOTE_ADDR']."',
				'".$_SERVER['HTTP_USER_AGENT']."',
				'".trim($_REQUEST["Flags"])."',
				'".$this->modder->flags["Seed"]."',
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

	function draw() {
		if ($this->delivered_file) return;
?>
		<html>
			<head>
				<?php $this->drawHeader(); ?>
			</head>
			<body>
				<?php $this->drawBody(); ?>
			</body>
		</html>
<?php
	}

	function drawHeader() {
?>
		<script type="text/javascript" src="/Library/jquery-3.0.0.js"></script>
		<script type="text/javascript" src="/Library/bootstrap-4.0.0/js/bootstrap.js"></script>
		<script type="text/javascript" src="dwm2rando.js"></script>
		<link rel="stylesheet" href="/Library/bootstrap-4.0.0/css/bootstrap.css" />
		<link rel="Stylesheet" href="dwm2rando.css" />
<?php
	}

	function drawBody() {
		$this->drawErrorMessage();
		$this->drawForm();
	}

	function drawErrorMessage() {
		if ($this->error) {
?>
			<div class="error_message"><?php echo $this->error_message; ?></div>
<?php
		}
	}

	function drawForm() {
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
			<div class="col-sm"><input type="text" name="Seed" value="<?php echo $this->modder->flags['Seed'] ?>" id="seed_input" size=10 /></div>
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
						executeQuery($monster_list_query);
						while($monster = get()){
					?>
					<option value="<?php echo $monster["id"]; ?>" <?php echo $this->modder->flags['StartingMonster'] == $monster["id"] ? 'selected' : '' ?>><?php echo $monster["name"]; ?></option>
					<?php
						}
					?>
				</select>
			</div>
		</div>
<?php
		foreach ($this->flagSettings as $name => $settings) {
			$this->flagRadioButtons($name, $this->modder->flags[$name], $settings["label"], $settings["options"]);
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
}

?>
