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

    SELECT Trading_Id AS Pur_Id, Invoice_No, Ref_No, Invoice_Date, Party_Id,
           CASE
               WHEN IFNULL(Party_Id, 0) = 0 THEN 'Cash'
               ELSE UDF_GET_PARTY_NAME(Party_Id)
           END AS Party_Name,
           Net_Amt,
           COUNT(*) OVER() AS Total_Count
    FROM trans_trading
    WHERE Tranding_Type = 2
      AND (pParty_Id = 0 OR Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR Invoice_No LIKE pBillLike OR Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(Status_Cd, 1) = 1
    ORDER BY Invoice_Date DESC, Trading_Id DESC
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
      AND (pParty_Id = 0 OR Party_Id = pParty_Id)
      AND (IFNULL(pBillLike, '') = '' OR Invoice_No LIKE pBillLike OR Ref_No LIKE pBillLike)
      AND ((pFrm_Date IS NULL AND pTo_Date IS NULL) OR Invoice_Date BETWEEN pFrm_Date AND pTo_Date)
      AND IFNULL(Status_Cd, 1) = 1
    ORDER BY Invoice_Date DESC, Trading_Id DESC
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;
