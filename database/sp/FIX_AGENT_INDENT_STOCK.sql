-- Run on the organisation schema (e.g. smartinventory_bccsl).
-- Why AI26 - 27/14 did not show in stock:
--   trns_stockinout has the issue (Prod_Id=98, Qty=100) but trans_agent_stock.Prod_Id is NULL.
--   USP_ADD_EDIT_AGENT_INDENT inserts VALUES (..., prod_id) and prod_id is not a procedure
--   variable, so it saves NULL. UDF_CAL_AGENT_STOCK then cannot match the item.
--
-- 1) Backfill missing Prod_Id on existing agent stock rows.
-- 2) Replace USP_ADD_EDIT_AGENT_INDENT so new issues save Prod_Id from tempitem.

UPDATE trans_agent_stock s
JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
SET s.Prod_Id = i.Prod_Id
WHERE s.Prod_Id IS NULL
  AND i.Prod_Id IS NOT NULL;

DROP PROCEDURE IF EXISTS `USP_ADD_EDIT_AGENT_INDENT`;

DELIMITER $$

CREATE PROCEDURE `USP_ADD_EDIT_AGENT_INDENT`(
    pAgent_Id           INT,
    pDate               DATE,
    pBranch_Id          INT,
    pFin_Id             INT,
    pType               INT,
    pMode               SMALLINT
)
BEGIN

    DECLARE pData_Count     INT;
    DECLARE pTemp_Id        INT;
    DECLARE pLoop_Count     INT;
    DECLARE pError_No       INT;
    DECLARE pStock_Id       BIGINT;
    DECLARE pIndent_No      VARCHAR(25);
    DECLARE pInsert_Id      INT;
    DECLARE pError_Message  VARCHAR(100);

    SET pData_Count = (SELECT COUNT(*) FROM tempitem);

    IF (pData_Count = 0) THEN
        SET pError_No = -1;
        SET pError_Message = 'No Product ';
    ELSE
        SET pError_No = 0;
    END IF;

    IF (pError_No = 0) THEN
        IF (pMode = 1) THEN
            IF (pType = 1) THEN
                SET pLoop_Count = 0;
                WHILE (pLoop_Count < pData_Count) DO
                    SET pTemp_Id = (SELECT id FROM tempitem ORDER BY id LIMIT pLoop_Count, 1);
                    INSERT INTO trns_stockinout (Branch_Id, Stock_Type, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Stock_At)
                    SELECT pBranch_Id, 4, 'I', pDate, prod_id, unit_id, qnty, 3
                    FROM tempitem
                    WHERE id = pTemp_Id;
                    SET pStock_Id = LAST_INSERT_ID();
                    INSERT INTO trans_agent_stock (TrnsStock_Id, Agent_Id, Trans_Date, Stock_Status, Prod_Id)
                    SELECT pStock_Id, pAgent_Id, pDate, 3, prod_id
                    FROM tempitem
                    WHERE id = pTemp_Id;
                    SET pLoop_Count = pLoop_Count + 1;
                END WHILE;
            END IF;
            UPDATE trans_agent_indent
               SET Is_Issued = 1,
                   Issued_DtTm = CURRENT_TIMESTAMP()
             WHERE Indent_Id IN (SELECT indent_id FROM tempitem);
            SET pError_Message = 'Agent Indent Successfully Saved !!';
        END IF;

        IF (pType = 2) THEN
            SET pIndent_No = (UDF_GEN_SYS_NO('AI', pFin_Id, pBranch_Id));
            IF (pIndent_No IS NULL) THEN
                SET pError_No = -2;
                SET pError_Message = 'Indent Number Is Not Genereated !!';
            ELSE
                SET pError_No = 0;
            END IF;

            IF (pError_No = 0) THEN
                INSERT INTO trans_agent_indent (Indent_Date, Indent_No, Agent_Id, Remarks, Entry_DtTm)
                VALUES (pDate, pIndent_No, pAgent_Id, 'From Office', CURRENT_TIMESTAMP());
                SET pInsert_Id = LAST_INSERT_ID();
                INSERT INTO trans_agent_indent_details (Indent_Id, Prod_Id, Quantity, Unit_Id)
                SELECT pInsert_Id, item_id, qnty, unit_id FROM tempitem;

                SET pLoop_Count = 0;
                WHILE (pLoop_Count < pData_Count) DO
                    SET pTemp_Id = (SELECT id FROM tempitem ORDER BY id LIMIT pLoop_Count, 1);
                    INSERT INTO trns_stockinout (Branch_Id, Stock_Type, Type_Nature, InOut_Date, Prod_Id, Unit_Id, Quantity, Stock_At)
                    SELECT pBranch_Id, 4, 'I', pDate, prod_id, unit_id, qnty, 3
                    FROM tempitem
                    WHERE id = pTemp_Id;
                    SET pStock_Id = LAST_INSERT_ID();
                    INSERT INTO trans_agent_stock (TrnsStock_Id, Agent_Id, Trans_Date, Stock_Status, Prod_Id)
                    SELECT pStock_Id, pAgent_Id, pDate, 3, prod_id
                    FROM tempitem
                    WHERE id = pTemp_Id;
                    SET pLoop_Count = pLoop_Count + 1;
                END WHILE;

                UPDATE trans_agent_indent
                   SET Is_Issued = 1,
                       Issued_DtTm = CURRENT_TIMESTAMP()
                 WHERE Indent_Id = pInsert_Id;
                SET pError_Message = CONCAT('Agent Indent Successful, No Is ', pIndent_No);
            END IF;
        END IF;
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
END$$

DELIMITER ;
