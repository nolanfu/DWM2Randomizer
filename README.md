# DWM2Randomizer
Randomizer for Dragon Warrior Monsters II
3/27/2018

This respository for is the source code for the DWM2 Randomizer hosted at https://cowness.net/DWM2R/


Current Features:
 - Randomized monster loadouts, including stat growth, skill resistances, and skills learned
 - Randomized encounters: Which monsters appear in the field are randomized, with base stats altered to fit their randomized growths
 - Selectable Starting Monster
 - Genius Mode: All monsters have high intelligence, so that it's easier to learn your skills
 - Yeti Mode: All* monsters are yetis!!!
 - QOL patches, such as opening the Starry Shrine's back door early to allow breeding during Oasis/Pirate.

* - A few monsters in Yeti Mode are not actually yetis.  This includes monsters needed to complete the game (Hoodsquid, Madgopher, Army Ant) and monsters that NPCs offer to breed with you.


Known notable issues:
 - The Starry Shrine's back door opens earlier than intended; do not talk to the Monster Master before acquiring Slash.
 - A softlock can occur if you walk out the front door of the Starry Shrine before the Pirate key world is completed.
 - Most enemies seem to focus the back monster in your party with all single-target attacks.  The cause of this is not known.
 - Sky World enemies and most bosses hit harder than intended


Short-Term Planned Fixes/Features:
 - Need to make sure boss monsters' HP doesn't get turned into ATK...
 - Pokemon Trainer Mode: Choose from three random starting monsters (Won't affect the rest of the seed)
 - Random encounters aren't learning "evolved" spells/skills, and don't even learn those until around Ice.


Long-Term Planned Features:
 - Randomized Fixed Items & Shops/Prices (And random items?  Or at least buff Oasis' items?)
 - Want to "remove" the WLD stat (set it to zero for all wild monsters)
 - Better base-stat calculation
 - More/better options for growth 
 - More silly text changes
 - All outstanding comments flagged "TODO" in index.php
