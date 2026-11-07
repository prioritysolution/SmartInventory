-- Deploy on each organisation schema (e.g. smartinventory_bccsl)
-- Generates barcodes for all trns_stockinout entries that have no barcode.
-- Rule: same Prod_Id + same MRP → same barcode (reuse existing)
--       same Prod_Id + different MRP → different barcode (new via UDF_GEN_BARCODE)

DROP PROCEDURE IF EXISTS USP_GENERATE_BARCODE_BULK;
DELIMITER $$
CREATE PROCEDURE USP_GENERATE_BARCODE_BULK(
    IN pOrg_Id      INT,
    IN pBranch_Code VARCHAR(6)
)
BEGIN
    DECLARE done        INT DEFAULT 0;
    DECLARE pStock_Id   BIGINT;
    DECLARE pStock_Date DATE;
    DECLARE pMRP        DECIMAL(10,2);
    DECLARE pProd_Id    INT;
    DECLARE pBarcode    VARCHAR(30);
    DECLARE pAffected   INT DEFAULT 0;

    -- All inward/outward entries with no barcode (opening, purchase, counter sale, agent indent)
    DECLARE cur CURSOR FOR
        SELECT Stock_Id, InOut_Date, IFNULL(MRP, 0), Prod_Id
        FROM trns_stockinout
        WHERE IFNULL(Barcode, 0) = 0
          AND Stock_Type IN (1, 2, 3, 4)
        ORDER BY Stock_Id ASC;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO pStock_Id, pStock_Date, pMRP, pProd_Id;
        IF done THEN LEAVE read_loop; END IF;

        -- Reuse existing barcode if same product + same MRP already has one
        SET pBarcode = (
            SELECT Barcode
            FROM trns_stockinout
            WHERE Prod_Id = pProd_Id
              AND ROUND(IFNULL(MRP, 0), 2) = ROUND(pMRP, 2)
              AND IFNULL(Barcode, 0) <> 0
              AND Stock_Type IN (1, 2)
            ORDER BY Stock_Id DESC
            LIMIT 1
        );

        -- No existing barcode for this product+MRP → generate a new unique one
        IF IFNULL(pBarcode, 0) = 0 THEN
            SET pBarcode = UDF_GEN_BARCODE(pOrg_Id, pBranch_Code, pStock_Date, pStock_Id);
        END IF;

        UPDATE trns_stockinout
        SET Barcode = pBarcode
        WHERE Stock_Id = pStock_Id;

        SET pAffected = pAffected + ROW_COUNT();

    END LOOP;
    CLOSE cur;

    SELECT pAffected AS Affected, 'Barcodes generated successfully' AS Message;
END$$
DELIMITER ;
