<?php

$stmts = array();

####################################################################
# 1. DATABASE ALTER                                                #
####################################################################

// For fulltext index on MyISAM
//$stmts['alter/product_search/add-fulltext'] = "ALTER TABLE `product_search` ADD FULLTEXT(`keywords`)";


####################################################################
# 2. DATABASE FUNCTIONS                                            #
####################################################################

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

$stmts['drop/function/strip_tags'] = "DROP FUNCTION IF EXISTS `strip_tags`";
$stmts['create/function/strip_tags']	= <<< ENDQ
CREATE FUNCTION strip_tags( Dirty varchar(4000) )
RETURNS varchar(10000)
DETERMINISTIC 
BEGIN
  DECLARE iStart, iEnd, iLength int;
    WHILE Locate( '<', Dirty ) > 0 And Locate( '>', Dirty, Locate( '<', Dirty )) > 0 DO
      BEGIN
        SET iStart = Locate( '<', Dirty ), iEnd = Locate( '>', Dirty, Locate('<', Dirty ));
        SET iLength = ( iEnd - iStart) + 1;
        IF iLength > 0 THEN
          BEGIN
            SET Dirty = Insert( Dirty, iStart, iLength, '');
          END;
        END IF;
      END;
    END WHILE;
    RETURN Dirty;
END;
ENDQ;

####################################################################
# 3. DATABASE PROCEDURES                                           #
####################################################################

$stmts['drop/procedure/rebuild_product_search'] = "DROP PROCEDURE IF EXISTS `rebuild_product_search`";
$stmts['create/procedure/rebuild_product_search']	= <<< ENDQ
CREATE PROCEDURE `rebuild_product_search` ()
BEGIN
	INSERT INTO product_search (product_id, lang, keywords, updated_at)
	SELECT
		p.product_id,
		p18.lang,
		strip_tags(TRIM(CONCAT_WS(' ',
					COALESCE(pb.title, ''),
					COALESCE(p18.title, p.title, ''),
					COALESCE(p18.invoice_title, p.invoice_title, ''),
					COALESCE(p18.description, p.description, ''),
					COALESCE(p18.characteristic, p.characteristic, ''),
					COALESCE(p18.keywords, p.keywords, '')))) as keywords,
		NOW() as updated_at
	from
		product p
			left outer join
		product_translation p18 ON p18.product_id = p.product_id
			left outer join
		product_brand pb ON p.brand_id = pb.brand_id
	order by p.product_id , p18.lang
	on duplicate key update
		  keywords = strip_tags(TRIM(CONCAT_WS(' ',
					COALESCE(pb.title, ''),
					COALESCE(p18.title, p.title, ''),
					COALESCE(p18.invoice_title, p.invoice_title, ''),
					COALESCE(p18.description, p.description, ''),
					COALESCE(p18.characteristic, p.characteristic, ''),
					COALESCE(p18.keywords, p.keywords, '')))),
		   updated_at = NOW();
END
ENDQ;
        

####################################################################
# 4. DATABASE TRIGGERS                                             #
####################################################################


####################################################################
# 5. DATABASE EVENTS                                               #
####################################################################



return array(
	'dbextra' => array(
		'statements' => $stmts
	)
	
);