-- Agent code: next stores_id.Cur_Val as a plain number (129), not a 6-digit 100129.

DROP FUNCTION IF EXISTS UDF_GEN_AGENT_NO;
DELIMITER $$
CREATE FUNCTION UDF_GEN_AGENT_NO(
    pElement_Type VARCHAR(100)
) RETURNS VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    DETERMINISTIC
BEGIN
    DECLARE pCurr_Val INT;

    SET sql_safe_updates = 0;

    SELECT Cur_Val + 1
      INTO pCurr_Val
    FROM stores_id
    WHERE Element_Name = pElement_Type
    LIMIT 1;

    IF pCurr_Val IS NULL THEN
        RETURN NULL;
    END IF;

    UPDATE stores_id
       SET Cur_Val = pCurr_Val
     WHERE Element_Name = pElement_Type;

    RETURN CAST(pCurr_Val AS CHAR);
END$$
DELIMITER ;
