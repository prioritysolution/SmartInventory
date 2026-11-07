-- Paged Agent Register (indent + stock) for scroll-wise fetch

DROP PROCEDURE IF EXISTS USP_RPT_AGENT_INDENT_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_RPT_AGENT_INDENT_PAGE(
    pFrm_Date   DATE,
    pTo_Date    DATE,
    pAgent_Id   INT,
    pLimit      INT,
    pOffset     INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 40;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IF(IFNULL(pLimit, 0) <= 0, 40, LEAST(pLimit, 100));
    SET vOffset = IFNULL(pOffset, 0);

    SELECT
        m.Indent_Id,
        m.Indent_No,
        m.Indent_Date,
        COALESCE(DATE(m.Issued_DtTm), m.Indent_Date) AS Issue_Date,
        IFNULL(m.Indent_Type_Id, 1) AS Indent_Type_Id,
        IFNULL(m.Remarks, '') AS Remarks,
        a.Agent_Id,
        a.Agent_Code,
        a.Agent_Name,
        p.Prod_Code,
        p.Prod_ShortNm,
        IFNULL(d.Quantity, 0) AS Req_Qty,
        IFNULL(d.Reject_Qty, 0) AS Reject_Qty,
        (IFNULL(d.Quantity, 0) - IFNULL(d.Reject_Qty, 0)) AS Issue_Qty,
        UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name,
        IFNULL((
            SELECT si.MRP
            FROM trans_agent_stock tas
            INNER JOIN trns_stockinout si ON si.Stock_Id = tas.TrnsStock_Id
            WHERE tas.Agent_Id = m.Agent_Id
              AND tas.Prod_Id = d.Prod_Id
              AND si.Stock_Type = 4
              AND DATE(tas.Trans_Date) = COALESCE(DATE(m.Issued_DtTm), m.Indent_Date)
            ORDER BY tas.AgntStock_Id DESC
            LIMIT 1
        ), 0) AS MRP,
        CASE WHEN m.Is_Issued = 1 THEN 'Issued' ELSE 'Pending' END AS Status_Name,
        COUNT(*) OVER() AS Total_Count
    FROM trans_agent_indent m
    JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    JOIN mst_product p ON p.Prod_Id = d.Prod_Id
    JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
    WHERE m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pAgent_Id = 0 OR m.Agent_Id = pAgent_Id)
    ORDER BY m.Indent_Date, m.Indent_Id, d.Indent_Sl
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_RPT_AGENT_INDENT_TOTALS;
DELIMITER $$
CREATE PROCEDURE USP_RPT_AGENT_INDENT_TOTALS(
    pFrm_Date DATE,
    pTo_Date  DATE,
    pAgent_Id INT
)
BEGIN
    SELECT
        COUNT(*) AS Total_Count,
        SUM(IFNULL(d.Quantity, 0)) AS Req_Qty,
        SUM(IFNULL(d.Reject_Qty, 0)) AS Reject_Qty,
        SUM(IFNULL(d.Quantity, 0) - IFNULL(d.Reject_Qty, 0)) AS Issue_Qty
    FROM trans_agent_indent m
    JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    WHERE m.Indent_Date BETWEEN pFrm_Date AND pTo_Date
      AND (pAgent_Id = 0 OR m.Agent_Id = pAgent_Id);
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_RPT_AGENT_STOCK_PAGE;
DELIMITER $$
CREATE PROCEDURE USP_RPT_AGENT_STOCK_PAGE(
    IN pDate     DATE,
    IN pAgent_Id INT,
    IN pLimit    INT,
    IN pOffset   INT
)
BEGIN
    DECLARE vLimit INT DEFAULT 40;
    DECLARE vOffset INT DEFAULT 0;
    SET vLimit = IF(IFNULL(pLimit, 0) <= 0, 40, LEAST(pLimit, 100));
    SET vOffset = IFNULL(pOffset, 0);

    SELECT
        a.Agent_Name,
        p.Prod_Code,
        p.Prod_ShortNm,
        u.Unit_Name,
        q.Qty,
        COUNT(*) OVER() AS Total_Count
    FROM (
        SELECT
            s.Agent_Id,
            s.Prod_Id,
            SUM(CASE i.Stock_Type
                WHEN 4 THEN  i.Quantity
                WHEN 6 THEN -i.Quantity
                WHEN 5 THEN -i.Quantity
                WHEN 8 THEN  i.Quantity
                ELSE 0
            END) AS Qty
        FROM (
            SELECT DISTINCT Agent_Id, Prod_Id, TrnsStock_Id
            FROM trans_agent_stock
            WHERE Trans_Date < DATE_ADD(pDate, INTERVAL 1 DAY)
              AND (pAgent_Id = 0 OR Agent_Id = pAgent_Id)
              AND TrnsStock_Id IS NOT NULL
        ) AS s
        INNER JOIN trns_stockinout AS i
            ON i.Stock_Id = s.TrnsStock_Id
        WHERE i.Stock_Type IN (4, 5, 6, 8)
        GROUP BY s.Agent_Id, s.Prod_Id
    ) AS q
    INNER JOIN mst_agent AS a
        ON a.Agent_Id = q.Agent_Id
    INNER JOIN mst_product AS p
        ON p.Prod_Id = q.Prod_Id
    LEFT JOIN mst_unit AS u
        ON u.Unit_Id = p.Unit_Id
    WHERE a.Status_Cd = 1
      AND q.Qty <> 0
    ORDER BY a.Agent_Name, p.Prod_ShortNm
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_RPT_AGENT_STOCK_TOTALS;
DELIMITER $$
CREATE PROCEDURE USP_RPT_AGENT_STOCK_TOTALS(
    IN pDate     DATE,
    IN pAgent_Id INT
)
BEGIN
    SELECT
        COUNT(*) AS Total_Count,
        SUM(q.Qty) AS Qty
    FROM (
        SELECT
            s.Agent_Id,
            s.Prod_Id,
            SUM(CASE i.Stock_Type
                WHEN 4 THEN  i.Quantity
                WHEN 6 THEN -i.Quantity
                WHEN 5 THEN -i.Quantity
                WHEN 8 THEN  i.Quantity
                ELSE 0
            END) AS Qty
        FROM (
            SELECT DISTINCT Agent_Id, Prod_Id, TrnsStock_Id
            FROM trans_agent_stock
            WHERE Trans_Date < DATE_ADD(pDate, INTERVAL 1 DAY)
              AND (pAgent_Id = 0 OR Agent_Id = pAgent_Id)
              AND TrnsStock_Id IS NOT NULL
        ) AS s
        INNER JOIN trns_stockinout AS i
            ON i.Stock_Id = s.TrnsStock_Id
        WHERE i.Stock_Type IN (4, 5, 6, 8)
        GROUP BY s.Agent_Id, s.Prod_Id
    ) AS q
    INNER JOIN mst_agent AS a ON a.Agent_Id = q.Agent_Id
    WHERE a.Status_Cd = 1
      AND q.Qty <> 0;
END$$
DELIMITER ;
