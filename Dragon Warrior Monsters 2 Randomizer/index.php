<?php

// Site with useful information: https://dragon-warrior-monsters-2-modding.fandom.com/wiki/Dragon_Warrior_Monsters_2_Modding_HomePage

require_once("include/db.php");
require_once("include/class/Rom.php");
require_once("include/class/RandomGenerator.php");
require_once("include/class/RomModder.php");
require_once("include/class/UIController.php");
require_once("include/rom_dump.php");

include_once("config/settings.php");

$controller = new UIController();
$controller->handleRequest();

?>
<html>
<head>
	<script type="text/javascript" src="/Library/jquery-3.0.0.js"></script>
	<script type="text/javascript" src="/Library/bootstrap-4.0.0/js/bootstrap.js"></script>
	<script type="text/javascript" src="dwm2rando.js"></script>
	<link rel="stylesheet" href="/Library/bootstrap-4.0.0/css/bootstrap.css" />
	<link rel="Stylesheet" href="dwm2rando.css" />
</head>
<body>

<?php $controller->drawPage(); ?>

</body>
</html>
