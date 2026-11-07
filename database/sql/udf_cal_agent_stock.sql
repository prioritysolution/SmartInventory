DROP FUNCTION IF EXISTS UDF_CAL_AGENT_STOCK;
DELIMITER $$
CREATE FUNCTION UDF_CAL_AGENT_STOCK(
    pAgent_Id INT,
    pProd_Id  INT,
    pDate     DATE
) RETURNS SMALLINT
    READS SQL DATA
BEGIN
    DECLARE vQty BIGINT DEFAULT 0;
    DECLARE vPending BIGINT DEFAULT 0;

    SELECT COALESCE(SUM(
        CASE i.Stock_Type
            WHEN 4 THEN  i.Quantity
            WHEN 6 THEN -i.Quantity
            WHEN 5 THEN -i.Quantity
            WHEN 8 THEN  i.Quantity
            ELSE 0
        END
    ), 0)
    INTO vQty
    FROM trns_stockinout AS i
    WHERE i.Stock_Type IN (4, 5, 6, 8)
      AND EXISTS (
          SELECT 1
          FROM trans_agent_stock AS s
          WHERE s.TrnsStock_Id = i.Stock_Id
            AND s.Agent_Id = pAgent_Id
            AND s.Prod_Id = pProd_Id
            AND s.Trans_Date < DATE_ADD(pDate, INTERVAL 1 DAY)
      );

    SELECT COALESCE(SUM(d.Quantity), 0)
    INTO vPending
    FROM trans_agent_indent m
    INNER JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
    WHERE m.Agent_Id = pAgent_Id
      AND IFNULL(m.Indent_Type_Id, 0) = 3
      AND IFNULL(m.Is_Issued, 0) = 0
      AND d.Prod_Id = pProd_Id
      AND m.Indent_Date <= pDate;

    RETURN GREATEST(0, vQty - vPending);
END$$
DELIMITER ;
