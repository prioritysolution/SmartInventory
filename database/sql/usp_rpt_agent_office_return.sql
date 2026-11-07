DROP PROCEDURE IF EXISTS USP_RPT_AGENT_OFFICE_RETURN;
DELIMITER $$
CREATE PROCEDURE USP_RPT_AGENT_OFFICE_RETURN(
    IN pFrm_Date DATE,
    IN pTo_Date  DATE,
    IN pAgent_Id INT
)
BEGIN
    SELECT
        Doc_No,
        Return_Date,
        Accepted_Date,
        Agent_Code,
        Agent_Name,
        Prod_Code,
        Prod_ShortNm,
        Qty,
        Unit_Name,
        Remarks,
        Status_Name
    FROM (
        SELECT
            m.Indent_No AS Doc_No,
            m.Indent_Date AS Return_Date,
            COALESCE(DATE(m.Issued_DtTm), m.Indent_Date) AS Accepted_Date,
            a.Agent_Code,
            a.Agent_Name,
            p.Prod_Code,
            p.Prod_ShortNm,
            d.Quantity AS Qty,
            UDF_GET_UNIT_NAME(d.Unit_Id) AS Unit_Name,
            IFNULL(m.Remarks, '') AS Remarks,
            'Accepted' AS Status_Name
        FROM trans_agent_indent m
        JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
        JOIN mst_product p ON p.Prod_Id = d.Prod_Id
        LEFT JOIN mst_agent a ON a.Agent_Id = m.Agent_Id
        WHERE IFNULL(m.Indent_Type_Id, 0) = 3
          AND IFNULL(m.Is_Issued, 0) = 1
          AND COALESCE(DATE(m.Issued_DtTm), m.Indent_Date) BETWEEN pFrm_Date AND pTo_Date
          AND (IFNULL(pAgent_Id, 0) = 0 OR m.Agent_Id = pAgent_Id)

        UNION ALL

        SELECT
            CAST(s.AgntStock_Id AS CHAR) AS Doc_No,
            DATE(s.Trans_Date) AS Return_Date,
            DATE(s.Trans_Date) AS Accepted_Date,
            a.Agent_Code,
            a.Agent_Name,
            p.Prod_Code,
            p.Prod_ShortNm,
            i.Quantity AS Qty,
            UDF_GET_UNIT_NAME(i.Unit_Id) AS Unit_Name,
            IFNULL(s.Remarks, '') AS Remarks,
            'Accepted' AS Status_Name
        FROM trans_agent_stock s
        JOIN trns_stockinout i ON i.Stock_Id = s.TrnsStock_Id
        JOIN mst_product p ON p.Prod_Id = IFNULL(s.Prod_Id, i.Prod_Id)
        LEFT JOIN mst_agent a ON a.Agent_Id = s.Agent_Id
        WHERE i.Stock_Type = 6
          AND DATE(s.Trans_Date) BETWEEN pFrm_Date AND pTo_Date
          AND (IFNULL(pAgent_Id, 0) = 0 OR s.Agent_Id = pAgent_Id)
          AND NOT EXISTS (
                SELECT 1
                FROM trans_agent_indent m
                JOIN trans_agent_indent_details d ON d.Indent_Id = m.Indent_Id
                WHERE m.Agent_Id = s.Agent_Id
                  AND IFNULL(m.Indent_Type_Id, 0) = 3
                  AND IFNULL(m.Is_Issued, 0) = 1
                  AND m.Indent_Date = DATE(s.Trans_Date)
                  AND d.Prod_Id = IFNULL(s.Prod_Id, i.Prod_Id)
          )
    ) x
    ORDER BY Accepted_Date DESC, Agent_Name, Doc_No DESC, Prod_ShortNm;
END$$
DELIMITER ;
