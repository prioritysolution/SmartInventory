-- New procedures only. Do not DROP or ALTER existing SPs/tables.
-- Run on the organisation schema (e.g. smartinventory_bccsl).

DELIMITER $$

CREATE PROCEDURE `USP_SEARCH_AGENT_ISSUE`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Indent_No AS Doc_No,
        DATE(IFNULL(m.Issued_DtTm, m.Indent_Date)) AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        d.Quantity,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name
    FROM trans_agent_indent m
    JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    WHERE m.Agent_Id = pAgent_Id
      AND m.Is_Issued = 1
      AND DATE(IFNULL(m.Issued_DtTm, m.Indent_Date)) BETWEEN pFrm_Date AND pTo_Date
    ORDER BY Doc_Date DESC, m.Indent_Id DESC, d.Indent_Sl;
END$$

CREATE PROCEDURE `USP_SEARCH_AGENT_SALE`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Invoice_No AS Doc_No,
        m.Invoice_Date AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        d.Item_Qty AS Quantity,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name
    FROM trans_trading m
    JOIN trans_trading_products d ON d.Trading_Id = m.Trading_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    WHERE m.Agent_Id = pAgent_Id
      AND m.Tranding_Type = 3
      AND m.Invoice_Date BETWEEN pFrm_Date AND pTo_Date
    ORDER BY m.Invoice_Date DESC, m.Trading_Id DESC, d.Sl;
END$$

CREATE PROCEDURE `USP_SEARCH_AGENT_OFFICE_RETURN`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        CAST(s.AgntStock_Id AS CHAR) AS Doc_No,
        DATE(s.Trans_Date) AS Doc_Date,
        p.Prod_Code,
        p.Prod_ShortNm,
        i.Quantity,
        UDF_GET_UNIT_NAME(i.Unit_Id) AS Unit_Name
    FROM trans_agent_stock s
    JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
    JOIN mst_product p ON p.Prod_Id = IFNULL(s.Prod_Id, i.Prod_Id)
    WHERE s.Agent_Id = pAgent_Id
      AND i.Stock_Type = 6
      AND DATE(s.Trans_Date) BETWEEN pFrm_Date AND pTo_Date
    ORDER BY s.Trans_Date DESC, s.AgntStock_Id DESC;
END$$

DELIMITER ;
