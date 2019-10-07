<?php

//TODO: Get rid of globals.

$error = false;
$error_message = 'The following errors occurred while generating the new seed:';

$FlagSettings = array(
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

$Flags = array();

?>
