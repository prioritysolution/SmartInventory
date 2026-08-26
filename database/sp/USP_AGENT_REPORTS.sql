-- Run on the organisation schema (e.g. smartinventory_bccsl).
-- Agent Register reports:
--   USP_SEARCH_AGENT_INDENT  — indent register, from date / to date
--   USP_GET_AGENT_STOCK_REPORT — stock movements on one date only (not closing stock)
-- Also sets Register submenu routes for Indent and Stock.

UPDATE `mst_menus_agent`
   SET `Route` = 'agent.report.indent'
 WHERE `Menu_Id` = 6 AND `SubMenu_Id` = 1 AND `SubMenu_Name` = 'Indent';

UPDATE `mst_menus_agent`
   SET `Route` = 'agent.report.stock'
 WHERE `Menu_Id` = 6 AND `SubMenu_Id` = 5 AND `SubMenu_Name` = 'Stock';

DROP PROCEDURE IF EXISTS `USP_SEARCH_AGENT_INDENT`;
DROP PROCEDURE IF EXISTS `USP_GET_AGENT_STOCK_REPORT`;

DELIMITER $$

CREATE PROCEDURE `USP_SEARCH_AGENT_INDENT`(
    pAgent_Id   INT,
    pFrm_Date   DATE,
    pTo_Date    DATE
)
BEGIN
    SELECT
        m.Indent_Id,
        m.Indent_Date,
        m.Indent_No,
        m.Remarks,
        CAST(m.Is_Issued AS UNSIGNED) AS Is_Issued,
        m.Issued_DtTm,
        (
            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'Indent_Sl', d.Indent_Sl,
                'Prod_Id', d.Prod_Id,
                'Prod_ShortNm', p.Prod_ShortNm,
                'Prod_Code', p.Prod_Code,
                'Quantity', d.Quantity,
                'Unit_Id', d.Unit_Id,
                'Unit_Name', UDF_GET_UNIT_NAME(d.Unit_Id)
            ))
            FROM trans_agent_indent_details d
            JOIN mst_product p ON p.Prod_Id = d.Prod_Id
            WHERE d.Indent_Id = m.Indent_Id
        ) AS Item_Data
    FROM trans_agent_indent m
    WHERE m.Agent_Id = pAgent_Id
      AND m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
    ORDER BY m.Indent_Date DESC, m.Indent_Id DESC;
END$$

CREATE PROCEDURE `USP_GET_AGENT_STOCK_REPORT`(
    pAgent_Id   INT,
    pDate       DATE
)
BEGIN
    SELECT
        p.Prod_Id,
        p.Prod_Code,
        p.Prod_ShortNm,
        p.Unit_Id,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        (
            IFNULL((
                SELECT SUM(i.Quantity)
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND s.Prod_Id = p.Prod_Id
                  AND DATE(s.Trans_Date) = pDate
                  AND i.Stock_Type = 4
            ), 0)
          - IFNULL((
                SELECT SUM(i.Quantity)
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND s.Prod_Id = p.Prod_Id
                  AND DATE(s.Trans_Date) = pDate
                  AND i.Stock_Type = 6
            ), 0)
          - IFNULL((
                SELECT SUM(i.Quantity)
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND s.Prod_Id = p.Prod_Id
                  AND DATE(s.Trans_Date) = pDate
                  AND i.Stock_Type = 5
            ), 0)
          + IFNULL((
                SELECT SUM(i.Quantity)
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND s.Prod_Id = p.Prod_Id
                  AND DATE(s.Trans_Date) = pDate
                  AND i.Stock_Type = 8
            ), 0)
        ) AS Qty
    FROM mst_product p
    WHERE EXISTS (
        SELECT 1
        FROM trans_agent_stock s
        WHERE s.Agent_Id = pAgent_Id
          AND s.Prod_Id = p.Prod_Id
          AND DATE(s.Trans_Date) = pDate
    )
    ORDER BY p.Prod_ShortNm;
END$$

DELIMITER ;
