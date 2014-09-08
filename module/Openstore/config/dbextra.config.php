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

$stmts['drop/function/slugify']        = "DROP FUNCTION IF EXISTS `slugify`";
$stmts['create/function/slugify']    = <<< ENDQ
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
$stmts['create/function/strip_tags']    = <<< ENDQ
CREATE FUNCTION strip_tags( Dirty varchar(3000) )
RETURNS varchar(3000)
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


$stmts['drop/function/delete_double_spaces'] = "DROP FUNCTION IF EXISTS `delete_double_spaces`";
$stmts['create/function/delete_double_spaces']    = <<< ENDQ
CREATE FUNCTION delete_double_spaces ( title VARCHAR(3000) )
RETURNS VARCHAR(3000) DETERMINISTIC
BEGIN
    DECLARE result VARCHAR(3000);
    SET result = REPLACE( title, '  ', ' ' );
    WHILE (result <> title) DO 
        SET title = result;
        SET result = REPLACE( title, '  ', ' ' );
    END WHILE;
    RETURN result;
END;
ENDQ;



####################################################################
# 3. DATABASE PROCEDURES                                           #
####################################################################

$stmts['drop/procedure/rebuild_product_search'] = "DROP PROCEDURE IF EXISTS `rebuild_product_search`";
$stmts['create/procedure/rebuild_product_search']    = <<< ENDQ
CREATE PROCEDURE `rebuild_product_search` ()
BEGIN
        SET @updated_at = NOW();
        SET @default_lang := (SELECT lang FROM `language` where flag_default = 1);
        IF (@default_lang is null) THEN 
             SET @default_lang = 'en'; 
        END IF;
        INSERT INTO product_search (product_id, lang, keywords, updated_at)
        SELECT 
            p.product_id,
            if (p18.lang is null, @default_lang, p18.lang) as lang,
            UPPER(
                delete_double_spaces(
                    strip_tags(
                            TRIM(
                                    CONCAT_WS(' ',
                                            COALESCE(p.reference, ''),
                                            COALESCE(pb.title, ''),
                                            COALESCE(p18.title, p.title, ''),
                                            COALESCE(p18.invoice_title, p.invoice_title, ''),
                                            IF(p2.product_id is not null,
                                                COALESCE(p18_2.description, p2.description, ''),
                                                COALESCE(p18.description, p.description, '')
                                            ),
                                            COALESCE(p18.characteristic, p.characteristic, ''),
                                            COALESCE(p18.keywords, p.keywords, ''),
                                            COALESCE(pc18.breadcrumb, pc.breadcrumb, ''),
                                            COALESCE(pg18.title, pg.title, '')
                                    )
                            )
                    )
                )
            )
            as keywords,
            @updated_at as updated_at
        from
            product p
                left outer join
            product_translation p18 ON p18.product_id = p.product_id
                left outer join
            product_brand pb ON p.brand_id = pb.brand_id
                left outer join
            product p2 ON p2.product_id = p.parent_id
                left outer join
            product_translation p18_2 ON p18_2.product_id = p2.product_id
                and p18_2.lang = p18.lang
                left outer join
            product_group pg ON p.group_id = pg.group_id
                left outer join
            product_group_translation pg18 ON pg18.group_id = pg.group_id
                and pg18.lang = p18.lang
                left outer join
            product_category pc on pc.category_id = p.category_id
                left outer join
            product_category_translation pc18 on pc18.category_id = p.category_id
                and pc18.lang = p18.lang
        where
            1=1
            and p.flag_active = 1
        order by if (p18.lang is null, @default_lang, p18.lang), p.product_id 
    on duplicate key update
            keywords = UPPER(
                delete_double_spaces(
                    strip_tags(
                            TRIM(
                                    CONCAT_WS(' ',
                                            COALESCE(p.reference, ''),
                                            COALESCE(pb.title, ''),
                                            COALESCE(p18.title, p.title, ''),
                                            COALESCE(p18.invoice_title, p.invoice_title, ''),
                                            IF(p2.product_id is not null,
                                                COALESCE(p18_2.description, p2.description, ''),
                                                COALESCE(p18.description, p.description, '')
                                            ),
                                            COALESCE(p18.characteristic, p.characteristic, ''),
                                            COALESCE(p18.keywords, p.keywords, ''),
                                            COALESCE(pc18.breadcrumb, pc.breadcrumb, ''),
                                            COALESCE(pg18.title, pg.title, '')
                                    )
                            )
                    )
                )
            )
            ,
            updated_at = @updated_at;
        
        -- REMOVE OLDER DATA
        DELETE FROM product_search where updated_at < @updated_at and updated_at is not null;
END
ENDQ;


$stmts['drop/procedure/rebuild_category_breadcrumbs'] = "DROP PROCEDURE IF EXISTS `rebuild_category_breadcrumbs`";
$stmts['create/procedure/rebuild_category_breadcrumbs']    = <<< ENDQ
CREATE PROCEDURE `rebuild_category_breadcrumbs` ()
BEGIN
    -- 1. Category     
    UPDATE product_category
            INNER JOIN
        (
                    SELECT
                            pc1.category_id,
                                    GROUP_CONCAT(
                                            pc2.title
                                            ORDER BY pc1.lvl , pc2.lvl
                                            --     could be utf8 - &rarr; →
                                            SEPARATOR ' | '
                            ) AS `breadcrumb`
                    FROM
                            `product_category` AS `pc1`
                    LEFT JOIN `product_category` AS `pc2` ON pc1.lft BETWEEN pc2.lft AND pc2.rgt
                    WHERE
                            pc2.lvl > 0
                    GROUP BY 1 
                    ORDER BY pc1.category_id
            ) AS tmp 
            ON tmp.category_id = product_category.category_id
    SET product_category.breadcrumb = tmp.breadcrumb;        
        
    -- 2. Category translations    
    UPDATE product_category_translation
            INNER JOIN
        (
                    SELECT
                            pc1.category_id,
                            pc18.lang,
                                    GROUP_CONCAT(
                                            IF(pc18.title is null, pc2.title, pc18.title)
                                            ORDER BY pc1.lvl , pc2.lvl
                                            --     could be utf8 - &rarr; →
                                            SEPARATOR ' | '
                            ) AS `breadcrumb`
                    FROM
                            `product_category` AS `pc1`
                    LEFT JOIN `product_category` AS `pc2` ON pc1.lft BETWEEN pc2.lft AND pc2.rgt
                    LEFT JOIN `product_category_translation` AS `pc18` ON pc18.category_id = pc2.category_id
                    WHERE
                            pc2.lvl > 0
                    GROUP BY 1 , 2
                    ORDER BY pc1.category_id
            ) AS tmp 
            ON tmp.category_id = product_category_translation.category_id
            AND tmp.lang = product_category_translation.lang
    SET product_category_translation.breadcrumb = tmp.breadcrumb;    
END;
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