-- Number series: match branch rows even when the caller passes NULL branch.

DROP FUNCTION IF EXISTS UDF_GEN_SYS_NO;
DELIMITER $$
CREATE FUNCTION UDF_GEN_SYS_NO(
    pElement_Type VARCHAR(10),
    pFin_Id INT,
    pBranch_Id INT
) RETURNS VARCHAR(100) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    DETERMINISTIC
BEGIN
    DECLARE pCurrent_Val INT;
    DECLARE pYear VARCHAR(50);
    DECLARE pFinal VARCHAR(100);
    DECLARE pStore_Id INT;

    SET pYear = (
        SELECT CONCAT(DATE_FORMAT(Year_Start, '%y'), ' - ', DATE_FORMAT(Year_End, '%y'))
        FROM mst_accountingyear
        WHERE Year_Id = pFin_Id
    );

    SELECT Id, (Cur_Val + 1)
      INTO pStore_Id, pCurrent_Val
    FROM stores_id
    WHERE Element_Name = pElement_Type
      AND Year_Id = pFin_Id
      AND (
            pBranch_Id IS NULL
            OR Branch_Id IS NULL
            OR Branch_Id = pBranch_Id
      )
    ORDER BY (Branch_Id <=> pBranch_Id) DESC, Id
    LIMIT 1;

    IF pStore_Id IS NULL OR pCurrent_Val IS NULL OR IFNULL(pYear, '') = '' THEN
        RETURN NULL;
    END IF;

    SET sql_safe_updates = 0;
    UPDATE stores_id SET Cur_Val = pCurrent_Val WHERE Id = pStore_Id;

    SET pFinal = CONCAT(pElement_Type, pYear, '/', pCurrent_Val);
    RETURN pFinal;
END$$
DELIMITER ;
