-- Paged search helpers for Find Sale / search modals (lazy scroll)

DROP PROCEDURE IF EXISTS USP_SEARCH_SALE_FOR_RETURN_BILL;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_SALE_FOR_RETURN_BILL(
    IN pSale_Type TINYINT,
    IN pReturn_Type TINYINT,
    IN pBillLike VARCHAR(242),
    IN pBranch_Id INT,
    IN pFromDate DATE,
    IN pToDate DATE,
    IN pParty_Id INT,
    IN pUseDateRange TINYINT,
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SELECT
        m.Trading_Id AS Sale_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        CASE
            WHEN IFNULL(m.Party_Id, 0) = 0 THEN 'Cash'
            ELSE UDF_GET_PARTY_NAME(m.Party_Id)
        END AS Party_Name,
        m.Net_Amt,
        COUNT(*) OVER() AS Total_Count
    FROM trans_trading m
    WHERE m.Tranding_Type = pSale_Type
      AND m.Status_Cd = 1
      AND IFNULL(m.Agent_Id, 0) = 0
      AND (IFNULL(pBillLike, '') = '' OR m.Invoice_No LIKE pBillLike OR m.Ref_No LIKE pBillLike)
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND (pUseDateRange = 0 OR m.Invoice_Date BETWEEN pFromDate AND pToDate)
      AND (pParty_Id = 0 OR m.Party_Id = pParty_Id)
      AND EXISTS (
            SELECT 1
            FROM trans_trading_products p
            WHERE p.Trading_Id = m.Trading_Id
              AND (
                    IFNULL(p.Item_Qty, 0)
                    - IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturn_Type
                          AND r.Status_Cd = 1
                          AND IFNULL(r.Agent_Id, 0) = 0
                          AND IFNULL(r.Party_Id, 0) = IFNULL(m.Party_Id, 0)
                          AND rp.Prod_Id = p.Prod_Id
                          AND (r.Ref_No = m.Invoice_No OR r.Ref_No = m.Ref_No)
                    ), 0)
                  ) > 0
          )
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_SEARCH_SALE_FOR_RETURN;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_SALE_FOR_RETURN(
    IN pFrm_Date DATE,
    IN pTo_Date DATE,
    IN pParty_Id INT,
    IN pBranch_Id INT,
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE pSaleType   TINYINT;
    DECLARE pReturnType TINYINT;
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;

    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SET pSaleType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Entry' LIMIT 1
    );
    SET pReturnType = (
        SELECT Value_Id FROM smartinventory_core.mst_options
         WHERE Option_Id = 6 AND Value_Name = 'Sales Return Entry' LIMIT 1
    );
    SET pSaleType   = IFNULL(pSaleType, 3);
    SET pReturnType = IFNULL(pReturnType, 5);

    SELECT
        m.Trading_Id AS Sale_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        CASE
            WHEN IFNULL(m.Party_Id, 0) = 0 THEN 'Cash'
            ELSE UDF_GET_PARTY_NAME(m.Party_Id)
        END AS Party_Name,
        m.Net_Amt,
        COUNT(*) OVER() AS Total_Count
    FROM trans_trading m
    WHERE m.Tranding_Type = pSaleType
      AND m.Status_Cd = 1
      AND IFNULL(m.Agent_Id, 0) = 0
      AND (pParty_Id = 0 OR m.Party_Id = pParty_Id)
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pBranch_Id = 0 OR m.Branch_Id = pBranch_Id)
      AND EXISTS (
            SELECT 1
            FROM trans_trading_products p
            WHERE p.Trading_Id = m.Trading_Id
              AND (
                    IFNULL(p.Item_Qty, 0)
                    - IFNULL((
                        SELECT SUM(rp.Item_Qty)
                        FROM trans_trading r
                        INNER JOIN trans_trading_products rp
                            ON rp.Trading_Id = r.Trading_Id
                        WHERE r.Tranding_Type = pReturnType
                          AND r.Status_Cd = 1
                          AND IFNULL(r.Agent_Id, 0) = 0
                          AND IFNULL(r.Party_Id, 0) = IFNULL(m.Party_Id, 0)
                          AND rp.Prod_Id = p.Prod_Id
                          AND (r.Ref_No = m.Invoice_No OR r.Ref_No = m.Ref_No)
                    ), 0)
                  ) > 0
      )
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_SEARCH_SALE_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_SALE_PAGE(
    IN pFrm_Date DATE,
    IN pTo_Date DATE,
    IN pParty_Id INT,
    IN pBillLike VARCHAR(242),
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SELECT Trading_Id AS Sale_Id, Invoice_No, Ref_No, Invoice_Date, Party_Id,
           CASE
               WHEN IFNULL(Party_Id, 0) = 0 THEN 'Cash'
               ELSE UDF_GET_PARTY_NAME(Party_Id)
           END AS Party_Name,
           Net_Amt,
           COUNT(*) OVER() AS Total_Count
    FROM trans_trading
    WHERE Tranding_Type = 3
      AND (pParty_Id = 0 OR Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR Invoice_No LIKE pBillLike OR Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(Status_Cd, 1) = 1
    ORDER BY Invoice_Date DESC, Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_SEARCH_SALE_RETURN_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_SALE_RETURN_PAGE(
    IN pFrm_Date DATE,
    IN pTo_Date DATE,
    IN pParty_Id INT,
    IN pBillLike VARCHAR(242),
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SELECT Trading_Id AS Sale_Id, Invoice_No, Ref_No, Invoice_Date, Party_Id,
           CASE
               WHEN IFNULL(Party_Id, 0) = 0 THEN 'Cash'
               ELSE UDF_GET_PARTY_NAME(Party_Id)
           END AS Party_Name,
           Net_Amt,
           COUNT(*) OVER() AS Total_Count
    FROM trans_trading
    WHERE Tranding_Type = 5
      AND (pParty_Id = 0 OR Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR Invoice_No LIKE pBillLike OR Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(Status_Cd, 1) = 1
    ORDER BY Invoice_Date DESC, Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP FUNCTION IF EXISTS UDF_CAL_AVAIL_STOCK;
DELIMITER $$
CREATE FUNCTION UDF_CAL_AVAIL_STOCK(
    pProd_Id INT,
    pDate DATE
)
RETURNS BIGINT
READS SQL DATA
BEGIN
    DECLARE vQty BIGINT DEFAULT 0;

    SELECT COALESCE(
        SUM(
            CASE Stock_Type
                WHEN 1 THEN Quantity
                WHEN 2 THEN Quantity
                WHEN 3 THEN -Quantity
                WHEN 4 THEN -Quantity
                WHEN 6 THEN Quantity
                WHEN 7 THEN -Quantity
                WHEN 8 THEN Quantity
                WHEN 9 THEN -Quantity
                ELSE 0
            END
        ), 0
    )
    INTO vQty
    FROM trns_stockinout
    WHERE Prod_Id = pProd_Id
      AND InOut_Date < DATE_ADD(pDate, INTERVAL 1 DAY)
      AND Stock_Type IN (1, 2, 3, 4, 6, 7, 8, 9);

    RETURN vQty;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_SEARCH_PURCHASE_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_PURCHASE_PAGE(
    IN pFrm_Date DATE,
    IN pTo_Date DATE,
    IN pParty_Id INT,
    IN pBillLike VARCHAR(242),
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SELECT
           m.Trading_Id AS Pur_Id,
           m.Invoice_No,
           m.Ref_No,
           m.Invoice_Date,
           m.Party_Id,
           CASE
               WHEN IFNULL(m.Party_Id, 0) = 0 THEN 'Cash'
               ELSE UDF_GET_PARTY_NAME(m.Party_Id)
           END AS Party_Name,
           m.Net_Amt,
           COALESCE((
               SELECT JSON_ARRAYAGG(JSON_OBJECT(
                   'Prod_Id', p.Prod_Id,
                   'Cat_Id', i.Cate_Id,
                   'Prod_Code', IFNULL(i.Prod_Code, ''),
                   'Prod_Name', i.Prod_ShortNm,
                   'Gst_Id', i.Gst_Id,
                   'hsn_code', p.Gst_Code,
                   'qnty', p.Item_Qty,
                   'Item_Rate', p.Item_Rate,
                   'Unit_Id', p.Unit_Id,
                   'Unit_Name', UDF_GET_UNIT_NAME(p.Unit_Id),
                   'Item_Total', p.Item_Total,
                   'MRP', p.MRP,
                   'Sale_Margin', IFNULL(i.Sale_Margin, 0),
                   'GST_Data', UDF_GET_ITEM_GST_DETAILS(i.Gst_Id),
                   'Is_Fmcg', IFNULL(c.Is_Fmcg, 0) + 0,
                   'Disc_Prcnt', p.Disc_Prcnt,
                   'Disc_Amt', p.Disc_Amt,
                   'Taxable_Amt', p.Taxable_Amt,
                   'SGST_Prcnt', p.SGST_Prcnt,
                   'CGST_Prcnt', p.CGST_Prcnt,
                   'SGST_Amt', p.SGST_Amt,
                   'CGST_Amt', p.CGST_Amt,
                   'Net_Amt', p.Net_Amt,
                   'Pack_Date', s.Pack_Date,
                   'Barcode', s.Barcode,
                   'Available_Qty', GREATEST(UDF_CAL_AVAIL_STOCK(p.Prod_Id, CURDATE()), 0)
               ) ORDER BY p.Sl)
               FROM (
                   SELECT tp.*,
                          ROW_NUMBER() OVER (
                              PARTITION BY tp.Trading_Id, tp.Prod_Id, tp.Item_Qty,
                                           ROUND(tp.Item_Rate, 2), ROUND(IFNULL(tp.MRP, 0), 2)
                              ORDER BY tp.Sl
                          ) AS line_no
                   FROM trans_trading_products tp
                   WHERE tp.Trading_Id = m.Trading_Id
               ) p
               JOIN mst_product i ON i.Prod_Id = p.Prod_Id
               LEFT JOIN mst_prod_category c ON c.Prd_CateId = i.Cate_Id
               LEFT JOIN (
                   SELECT
                       st.Type_Id,
                       st.Prod_Id,
                       st.Quantity,
                       ROUND(st.Rate, 2) AS Rate,
                       ROUND(IFNULL(st.MRP, 0), 2) AS MRP,
                       st.Pack_Date,
                       st.Barcode,
                       ROW_NUMBER() OVER (
                           PARTITION BY st.Type_Id, st.Prod_Id, st.Quantity,
                                        ROUND(st.Rate, 2), ROUND(IFNULL(st.MRP, 0), 2)
                           ORDER BY st.Stock_Id
                       ) AS line_no
                   FROM trns_stockinout st
                   WHERE st.Stock_Type = 2
               ) s ON s.Type_Id = m.Trading_Id
                  AND s.Prod_Id = p.Prod_Id
                  AND s.Quantity = p.Item_Qty
                  AND s.Rate = ROUND(p.Item_Rate, 2)
                  AND s.MRP = ROUND(IFNULL(p.MRP, 0), 2)
                  AND s.line_no = p.line_no
           ), JSON_ARRAY()) AS Item_Details,
           COUNT(*) OVER() AS Total_Count
    FROM trans_trading m
    WHERE m.Tranding_Type = 2
      AND (pParty_Id = 0 OR m.Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR m.Invoice_No LIKE pBillLike OR m.Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(m.Status_Cd, 1) = 1
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_SEARCH_PURCHASE_RETURN_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_PURCHASE_RETURN_PAGE(
    IN pFrm_Date DATE,
    IN pTo_Date DATE,
    IN pParty_Id INT,
    IN pBillLike VARCHAR(242),
    IN pLimit INT,
    IN pOffset INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);

    SELECT Trading_Id AS Pur_Id, Invoice_No, Ref_No, Invoice_Date, Party_Id,
           CASE
               WHEN IFNULL(Party_Id, 0) = 0 THEN 'Cash'
               ELSE UDF_GET_PARTY_NAME(Party_Id)
           END AS Party_Name,
           Net_Amt,
           COUNT(*) OVER() AS Total_Count
    FROM trans_trading
    WHERE Tranding_Type = 4
      AND (IFNULL(pParty_Id, 0) = 0 OR Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR Invoice_No LIKE pBillLike OR Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(Status_Cd, 1) = 1
    ORDER BY Invoice_Date DESC, Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;
