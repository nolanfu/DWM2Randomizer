
$(document).ready(function(){
	//Set a random seed, if we didn't just generate one.
	if($("#seed_input").val() == '0'){
		NewSeed();
	}
	$("#NewSeedBtn").click(function(){NewSeed();});
	
	//When any flag-field is changed, update the flag string
	$("input[type=radio]").change(function(){
		RefreshFlagString();
	});
	$("select").change(function(){
		RefreshFlagString();
	});
	//When the flag string is changed, update all fields
	$("#flags_input").change(function(){
		SetFlagsFromString();
	});
	//Initialize the flag string
	RefreshFlagString();
});
function NewSeed(){
	$("#seed_input").val(Math.floor(Math.random()*268435455));
}


//Number of 6-bit values required to represent an HTML "select" value.
select_max_size = 2;
function RefreshFlagString(){
	//This function will serialize the important form flags into a text string so that they can be shared for races.
	//Because JS integers are basically 32-bit, we'll have to utilize an array.
	//Let's have each array value hold a six-bit integer (0-63), which will ultimately be represented in text by a single character
	//I realized later that I basically reinvented base-64 encoding here...
	flags = [];
	flag_ctr = 0;
	flag_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
	//Start with all radio buttons.  Six radio buttons can be stored in one 6-bit array value
	$("input[type=radio]").each(function(){
		if(flag_ctr % 6 == 0){
			//Initialize the next array index
			flags.push(0);
		}
		if($(this).prop("checked")){
			flags[Math.floor(flag_ctr / 6)] += 2**(flag_ctr % 6);
		}else{
			flags[Math.floor(flag_ctr / 6)] += 0;
		}
		flag_ctr++;
	});
	//Now do selects.  Assume all select values are integers less than (64 ^ select_max_size)
	$("select").each(function(){
		for(i = 0; i < select_max_size; i++){
			flags.push(Math.floor($(this).val() / (64 ** i)) % 64);
		}
	});
	
	//Convert our array into a "base 64" string and output to the Flags field.
	$("#flags_input").val('');
	for(i = 0; i < flags.length; i++){
		$("#flags_input").val($("#flags_input").val()+flag_chars.charAt(flags[i]));
	}
}
function SetFlagsFromString(){
	//This will be the opposite of RefreshFlagString - turn on flags based on the Flags string.
	//TODO: Need a check to make sure the flags string is long enough.  Calculate length based on number of form elements?
	flag_string = $("#flags_input").val();
	flags = [];
	flag_ctr = 0;
	flag_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
	//Convert from alphanumeric to the array we used above
	for(i = 0; i < flag_string.length; i++){
		flags.push(flag_chars.indexOf(flag_string.charAt(i)));
	}
	//Start with all radio buttons
	$("input[type=radio]").each(function(){
		if((flags[Math.floor(flag_ctr / 6)] & 2**(flag_ctr%6)) != 0){
			$(this).prop("checked","checked");
		}else{
			$(this).removeProp("checked");
		}
		flag_ctr++;
	});
	//Continue with Selects.
	$("select").each(function(){
		_newval = 0;
		for(i = 0; i < select_max_size; i++){
			_newval += flags[flag_ctr / 6] * 64 ** i;
			flag_ctr += 6;
		}
		$(this).val(_newval);
	});
}

