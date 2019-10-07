<?php

// Site with useful information: https://dragon-warrior-monsters-2-modding.fandom.com/wiki/Dragon_Warrior_Monsters_2_Modding_HomePage

require_once("include/db.php");
require_once("include/globals.php");
require_once("include/random.php");
require_once("include/rom.php");
require_once("include/rom_dump.php");
require_once("include/rom_hacks.php");
require_once("include/ui.php");

main();

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

<?php drawForm(); ?>

</body>
</html>
