-- Purchase stock helpers — deploy on each organisation schema

DROP PROCEDURE IF EXISTS USP_GET_OFFICE_QTY_PARTY_PRODUCT;
DELIMITER $$
CREATE PROCEDURE USP_GET_OFFICE_QTY_PARTY_PRODUCT(
    IN pParty_Id INT,
    IN pProd_Id INT,
    IN pDate DATE,
    IN pExclude_Id INT
)
BEGIN
    SELECT IFNULL(SUM(CASE WHEN q.Qty > 0 THEN q.Qty ELSE 0 END), 0) AS Office_Qty
    FROM (
        SELECT IFNULL(SUM(
            CASE
                WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                WHEN s.Stock_Type = 8 AND ag.TrnsStock_Id IS NULL THEN s.Quantity
                WHEN s.Stock_Type = 7 AND pExclude_Id > 0 AND IFNULL(s.Type_Id, 0) = pExclude_Id THEN 0
                WHEN s.Stock_Type IN (3, 4, 7, 9) THEN -s.Quantity
                ELSE 0
            END
        ), 0) AS Qty
        FROM trns_stockinout s
        LEFT JOIN (
            SELECT DISTINCT TrnsStock_Id
            FROM trans_agent_stock
            WHERE TrnsStock_Id IS NOT NULL
        ) ag ON ag.TrnsStock_Id = s.Stock_Id
        WHERE s.InOut_Date <= pDate
          AND s.Barcode IN (
              SELECT DISTINCT p.Barcode
              FROM trns_stockinout p
              INNER JOIN trans_trading t
                  ON t.Trading_Id = p.Type_Id
                 AND t.Tranding_Type = 2
              WHERE p.Stock_Type = 2
                AND p.Prod_Id = pProd_Id
                AND t.Party_Id = pParty_Id
                AND IFNULL(p.Barcode, 0) <> 0
                AND p.InOut_Date <= pDate
          )
        GROUP BY s.Barcode
    ) q;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_OFFICE_QTY_BY_BARCODES;
DELIMITER $$
CREATE PROCEDURE USP_GET_OFFICE_QTY_BY_BARCODES(
    IN pBarcodes TEXT,
    IN pDate DATE,
    IN pExclude_Id INT
)
BEGIN
    SELECT s.Barcode,
           IFNULL(SUM(
               CASE
                   WHEN s.Stock_Type IN (1, 2, 6) THEN s.Quantity
                   WHEN s.Stock_Type = 8 AND ag.TrnsStock_Id IS NULL THEN s.Quantity
                   WHEN s.Stock_Type = 7 AND pExclude_Id > 0 AND IFNULL(s.Type_Id, 0) = pExclude_Id THEN 0
                   WHEN s.Stock_Type IN (3, 4, 7, 9) THEN -s.Quantity
                   ELSE 0
               END
           ), 0) AS Qty
    FROM trns_stockinout s
    LEFT JOIN (
        SELECT DISTINCT TrnsStock_Id
        FROM trans_agent_stock
        WHERE TrnsStock_Id IS NOT NULL
    ) ag ON ag.TrnsStock_Id = s.Stock_Id
    WHERE s.InOut_Date <= pDate
      AND FIND_IN_SET(s.Barcode, pBarcodes)
    GROUP BY s.Barcode;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_PURCHASE_BARCODES_FOR_RETURN;
DELIMITER $$
CREATE PROCEDURE USP_GET_PURCHASE_BARCODES_FOR_RETURN(
    IN pParty_Id INT,
    IN pDate DATE,
    IN pProd_Id INT,
    IN pBarcode VARCHAR(50),
    IN pExclude_Id INT
)
BEGIN
    SELECT
        s.Barcode,
        CAST(IFNULL(MAX(s.MRP), 0) AS DECIMAL(10,2)) AS MRP,
        p.Prod_Id,
        IFNULL(p.Prod_Code, '') AS Prod_Code,
        p.Prod_ShortNm,
        p.Unit_Id,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        p.Gst_Id,
        IFNULL(c.Is_Fmcg, 0) + 0 AS Is_Fmcg,
        UDF_GET_ITEM_GST_DETAILS(p.Gst_Id) AS GST_Data,
        SUM(s.Quantity) AS Purchased_Qty,
        IFNULL(MAX(ret.Returned_Qty), 0) AS Returned_Qty,
        SUM(s.Quantity) - IFNULL(MAX(ret.Returned_Qty), 0) AS Remaining_Qty
    FROM trns_stockinout s
    INNER JOIN trans_trading t
        ON t.Trading_Id = s.Type_Id
       AND t.Tranding_Type = 2
    INNER JOIN mst_product p ON p.Prod_Id = s.Prod_Id
    LEFT JOIN mst_prod_category c ON c.Prd_CateId = p.Cate_Id
    LEFT JOIN (
        SELECT Barcode, SUM(Quantity) AS Returned_Qty
        FROM trns_stockinout
        WHERE Stock_Type = 7
          AND IFNULL(Barcode, 0) <> 0
          AND InOut_Date <= pDate
          AND (pExclude_Id = 0 OR IFNULL(Type_Id, 0) <> pExclude_Id)
        GROUP BY Barcode
    ) ret ON ret.Barcode = s.Barcode
    WHERE s.Stock_Type = 2
      AND IFNULL(s.Barcode, 0) <> 0
      AND t.Party_Id = pParty_Id
      AND s.InOut_Date <= pDate
      AND (pProd_Id = 0 OR s.Prod_Id = pProd_Id)
      AND (IFNULL(pBarcode, '') = '' OR s.Barcode = pBarcode)
    GROUP BY s.Barcode, p.Prod_Id, p.Prod_Code, p.Prod_ShortNm, p.Unit_Id, p.Gst_Id, c.Is_Fmcg
    HAVING SUM(s.Quantity) - IFNULL(MAX(ret.Returned_Qty), 0) > 0
    ORDER BY MAX(s.Stock_Id) DESC;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_BARCODE_MAX_MRP;
DELIMITER $$
CREATE PROCEDURE USP_GET_BARCODE_MAX_MRP(
    IN pBarcode VARCHAR(50)
)
BEGIN
    SELECT IFNULL(MAX(MRP), 0) AS MRP
    FROM trns_stockinout
    WHERE Barcode = pBarcode
      AND Stock_Type IN (1, 2);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_ASSIGN_PURCHASE_RETURN_BARCODE;
DELIMITER $$
CREATE PROCEDURE USP_ASSIGN_PURCHASE_RETURN_BARCODE(
    IN pBarcode VARCHAR(50),
    IN pMrp DECIMAL(12,2),
    IN pProd_Id INT,
    IN pDate DATE
)
BEGIN
    UPDATE trns_stockinout
    SET Barcode = pBarcode,
        MRP = CASE WHEN IFNULL(MRP, 0) = 0 THEN pMrp ELSE MRP END
    WHERE Stock_Type = 7
      AND Prod_Id = pProd_Id
      AND IFNULL(Barcode, 0) = 0
      AND InOut_Date = pDate
    ORDER BY Stock_Id ASC
    LIMIT 1;
END$$
DELIMITER ;
