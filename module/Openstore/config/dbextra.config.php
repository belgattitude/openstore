<?php

$stmts = array();
$stmts['drop/function/slugify']		= "DROP FUNCTION IF EXISTS `slugify`";
$stmts['create/function/slugify']	= <<< ENDQ
CREATE FUNCTION `slugify` (dirty_string varchar(255))
RETURNS varchar(255) CHARSET latin1
DETERMINISTIC
BEGIN
	DECLARE x, y , z Int;
	Declare temp_string, new_string VarChar(255);
	Declare is_allowed Bool;
	Declare c, check_char VarChar(1);

	set temp_string = LOWER(dirty_string);

	Set temp_string = replace(temp_string, '&', ' and ');

	Select temp_string Regexp('[^a-z0-9\-]+') into x;
	If x = 1 then
		set z = 1;
		While z <= Char_length(temp_string) Do
			Set c = Substring(temp_string, z, 1);
			Set is_allowed = False;
			If !((ascii(c) = 45) or (ascii(c) >= 48 and ascii(c) <= 57) or (ascii(c) >= 97 and ascii(c) <= 122)) Then
				Set temp_string = Replace(temp_string, c, '-');
			End If;
			set z = z + 1;
		End While;
	End If;

	Select temp_string Regexp("^-|-$|'") into x;
	If x = 1 Then
		Set temp_string = Replace(temp_string, "'", '');
		Set z = Char_length(temp_string);
		Set y = Char_length(temp_string);
		Dash_check: While z > 1 Do
			If Strcmp(SubString(temp_string, -1, 1), '-') = 0 Then
				Set temp_string = Substring(temp_string,1, y-1);
				Set y = y - 1;
			Else
				Leave Dash_check;
			End If;
			Set z = z - 1;
		End While;
	End If;

	Repeat
		Select temp_string Regexp("--") into x;
		If x = 1 Then
			Set temp_string = Replace(temp_string, "--", "-");
		End If;
	Until x <> 1 End Repeat;

	If LOCATE('-', temp_string) = 1 Then
		Set temp_string = SUBSTRING(temp_string, 2);
	End If;

	Return temp_string;
END		
ENDQ;
	

return array(
	'dbextra' => array(
		'statements' => $stmts
	)
	
);