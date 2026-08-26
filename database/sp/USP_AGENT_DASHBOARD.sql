-- New procedures only. Do not DROP or ALTER existing SPs/tables.
-- Run on the organisation schema (e.g. smartinventory_bccsl).
--
--   USP_GET_AGENT_DASHBOARD_STATS     — KPI tiles
--   USP_GET_AGENT_MONTHLY_SALE_RETURN — sales vs customer-return chart
--   USP_GET_AGENT_LOW_STOCK           — low stock table

DROP PROCEDURE IF EXISTS `USP_GET_AGENT_DASHBOARD_STATS`;
DROP PROCEDURE IF EXISTS `USP_GET_AGENT_MONTHLY_SALE_RETURN`;
DROP PROCEDURE IF EXISTS `USP_GET_AGENT_LOW_STOCK`;

DELIMITER $$

CREATE PROCEDURE `USP_GET_AGENT_DASHBOARD_STATS`(
    pAgent_Id   INT,
    pYear_Id    TINYINT,
    pToday      DATE
)
BEGIN
    DECLARE pYearStart  DATE;
    DECLARE pYearEnd    DATE;
    DECLARE pMonthStart DATE;

    IF pToday IS NULL THEN
        SET pToday = CURDATE();
    END IF;

    SET pMonthStart = DATE_FORMAT(pToday, '%Y-%m-01');

    SELECT Year_Start, Year_End
      INTO pYearStart, pYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id
     LIMIT 1;

    SELECT
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 3
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS agent_sales_month,
        IFNULL((
            SELECT SUM(d.Quantity * IFNULL((
                SELECT s.MRP
                FROM trns_stockinout s
                WHERE s.Prod_Id = d.Prod_Id
                  AND IFNULL(s.MRP, 0) > 0
                ORDER BY s.InOut_Date DESC, s.Stock_Id DESC
                LIMIT 1
            ), 0))
            FROM trans_agent_indent m
            JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
            WHERE m.Agent_Id = pAgent_Id
              AND m.Indent_Date BETWEEN pMonthStart AND pToday
              AND m.Indent_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS requisitions_month,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 5
              AND t.Status_Cd = 1
              AND t.Invoice_Date BETWEEN pMonthStart AND pToday
              AND t.Invoice_Date BETWEEN pYearStart AND pYearEnd
        ), 0) AS customer_returns_month,
        IFNULL((
            SELECT SUM(x.Qty)
            FROM (
                SELECT
                    SUM(
                        CASE i.Stock_Type
                            WHEN 4 THEN i.Quantity
                            WHEN 6 THEN -i.Quantity
                            WHEN 5 THEN -i.Quantity
                            WHEN 8 THEN i.Quantity
                            ELSE 0
                        END
                    ) AS Qty
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND DATE(s.Trans_Date) <= pToday
                  AND s.Prod_Id IS NOT NULL
                GROUP BY s.Prod_Id
            ) x
            WHERE x.Qty > 0
        ), 0) AS stock_with_agent,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 3
              AND t.Status_Cd = 1
              AND t.Invoice_Date = pToday
        ), 0) AS today_sales,
        IFNULL((
            SELECT SUM(t.Net_Amt)
            FROM trans_trading t
            WHERE t.Agent_Id = pAgent_Id
              AND t.Tranding_Type = 5
              AND t.Status_Cd = 1
              AND t.Invoice_Date = pToday
        ), 0) AS today_return,
        IFNULL((
            SELECT COUNT(*)
            FROM trans_agent_indent m
            WHERE m.Agent_Id = pAgent_Id
              AND IFNULL(m.Is_Issued, 0) = 0
        ), 0) AS pending_indents,
        IFNULL((
            SELECT COUNT(*)
            FROM (
                SELECT
                    p.Prod_Id,
                    p.ReOrder_Qty,
                    SUM(
                        CASE i.Stock_Type
                            WHEN 4 THEN i.Quantity
                            WHEN 6 THEN -i.Quantity
                            WHEN 5 THEN -i.Quantity
                            WHEN 8 THEN i.Quantity
                            ELSE 0
                        END
                    ) AS Qty
                FROM trans_agent_stock s
                JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
                JOIN mst_product p ON p.Prod_Id = s.Prod_Id
                WHERE s.Agent_Id = pAgent_Id
                  AND DATE(s.Trans_Date) <= pToday
                  AND s.Prod_Id IS NOT NULL
                  AND p.Is_Active = 1
                GROUP BY p.Prod_Id, p.ReOrder_Qty
            ) z
            WHERE z.Qty > 0
              AND (
                    (IFNULL(z.ReOrder_Qty, 0) > 0 AND z.Qty < z.ReOrder_Qty)
                 OR (IFNULL(z.ReOrder_Qty, 0) = 0 AND z.Qty <= 10)
              )
        ), 0) AS low_stock_count;
END$$

CREATE PROCEDURE `USP_GET_AGENT_MONTHLY_SALE_RETURN`(
    pAgent_Id   INT,
    pYear_Id    TINYINT
)
BEGIN
    DECLARE vYearStart DATE;
    DECLARE vYearEnd   DATE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
        SET vYearStart = NULL;
        SET vYearEnd = NULL;
    END;

    SELECT Year_Start, Year_End
      INTO vYearStart, vYearEnd
      FROM mst_accountingyear
     WHERE Year_Id = pYear_Id
     LIMIT 1;

    IF vYearStart IS NULL OR vYearEnd IS NULL THEN
        SELECT
            CAST(NULL AS CHAR(20)) AS Month_Label,
            CAST(NULL AS CHAR(7))  AS Month_Key,
            CAST(0 AS DECIMAL(12,2)) AS Sales_Amt,
            CAST(0 AS DECIMAL(12,2)) AS Return_Amt
        WHERE 1 = 0;
    ELSE
        WITH RECURSIVE months AS (
            SELECT DATE_FORMAT(vYearStart, '%Y-%m-01') AS Month_Start
            UNION ALL
            SELECT DATE_ADD(Month_Start, INTERVAL 1 MONTH)
              FROM months
             WHERE DATE_ADD(Month_Start, INTERVAL 1 MONTH) <= vYearEnd
        )
        SELECT
            DATE_FORMAT(m.Month_Start, '%b %Y') AS Month_Label,
            DATE_FORMAT(m.Month_Start, '%Y-%m') AS Month_Key,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                FROM trans_trading t
                WHERE t.Agent_Id = pAgent_Id
                  AND t.Tranding_Type = 3
                  AND t.Status_Cd = 1
                  AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                  AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Sales_Amt,
            IFNULL((
                SELECT SUM(t.Net_Amt)
                FROM trans_trading t
                WHERE t.Agent_Id = pAgent_Id
                  AND t.Tranding_Type = 5
                  AND t.Status_Cd = 1
                  AND t.Invoice_Date >= GREATEST(m.Month_Start, vYearStart)
                  AND t.Invoice_Date <= LEAST(LAST_DAY(m.Month_Start), vYearEnd)
            ), 0) AS Return_Amt
        FROM months m
        ORDER BY m.Month_Start;
    END IF;
END$$

CREATE PROCEDURE `USP_GET_AGENT_LOW_STOCK`(
    pAgent_Id   INT,
    pToday      DATE
)
BEGIN
    IF pToday IS NULL THEN
        SET pToday = CURDATE();
    END IF;

    SELECT
        x.Prod_Id,
        x.Prod_Code,
        x.Prod_Name,
        x.On_Hand,
        x.ReOrder_Qty,
        CASE
            WHEN x.On_Hand <= 5 THEN 'Critical'
            WHEN IFNULL(x.ReOrder_Qty, 0) > 0 AND x.On_Hand <= (x.ReOrder_Qty * 0.5) THEN 'Critical'
            ELSE 'Low'
        END AS Alert_Status
    FROM (
        SELECT
            p.Prod_Id,
            p.Prod_Code,
            p.Prod_ShortNm AS Prod_Name,
            SUM(
                CASE i.Stock_Type
                    WHEN 4 THEN i.Quantity
                    WHEN 6 THEN -i.Quantity
                    WHEN 5 THEN -i.Quantity
                    WHEN 8 THEN i.Quantity
                    ELSE 0
                END
            ) AS On_Hand,
            IFNULL(p.ReOrder_Qty, 0) AS ReOrder_Qty
        FROM trans_agent_stock s
        JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
        JOIN mst_product p ON p.Prod_Id = s.Prod_Id
        WHERE s.Agent_Id = pAgent_Id
          AND DATE(s.Trans_Date) <= pToday
          AND s.Prod_Id IS NOT NULL
          AND p.Is_Active = 1
        GROUP BY p.Prod_Id, p.Prod_Code, p.Prod_ShortNm, p.ReOrder_Qty
    ) x
    WHERE x.On_Hand > 0
      AND (
            (IFNULL(x.ReOrder_Qty, 0) > 0 AND x.On_Hand < x.ReOrder_Qty)
         OR (IFNULL(x.ReOrder_Qty, 0) = 0 AND x.On_Hand <= 10)
      )
    ORDER BY x.On_Hand ASC, x.Prod_Name
    LIMIT 10;
END$$

DELIMITER ;
