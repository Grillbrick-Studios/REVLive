-- currently not used
-- see srch.php, lines 157, 1782

delimiter ||
DROP FUNCTION IF EXISTS strip_tags||
CREATE FUNCTION strip_tags( fld mediumtext) RETURNS mediumtext
LANGUAGE SQL NOT DETERMINISTIC READS SQL DATA
BEGIN
SET fld = replace(fld, '<em>', '');
SET fld = replace(fld, '</em>', '');
SET fld = replace(fld, '<strong>', '');
SET fld = replace(fld, '</strong>', '');
SET fld = replace(fld, '&ldquo;', '');
SET fld = replace(fld, '&rdquo;', '');
SET fld = replace(fld, '&lsquo;', '');
SET fld = replace(fld, '&rsquo;', '');
SET fld = replace(fld, ',', '');
return fld;
END;
||
delimiter ;


