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
$controller->draw();
