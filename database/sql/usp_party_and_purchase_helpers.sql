-- Deploy on each organisation schema (e.g. smartinventory_bccsl)

DROP PROCEDURE IF EXISTS USP_SEARCH_PARTY_LIST;
DELIMITER $$
CREATE PROCEDURE USP_SEARCH_PARTY_LIST(
    IN pParty_Type TINYINT,
    IN pBranch_Id INT,
    IN pAgent_Id INT,
    IN pSearch VARCHAR(100),
    IN pLimit INT,
    IN pOffset INT,
    IN pStatus TINYINT
)
BEGIN
    DECLARE vLimit INT DEFAULT 30;
    DECLARE vOffset INT DEFAULT 0;
    DECLARE vSearch VARCHAR(120);

    SET vLimit = IFNULL(NULLIF(pLimit, 0), 30);
    SET vOffset = IFNULL(pOffset, 0);
    SET vSearch = TRIM(IFNULL(pSearch, ''));

    SELECT
        p.Party_Id,
        p.Party_Code,
        p.Party_Name,
        p.Contact_No,
        p.AltContact_No,
        p.EMail,
        p.Address_Line1,
        p.Address_Line2,
        p.City,
        p.State,
        p.Pan_No,
        p.GstIn,
        p.State_Cd,
        p.Credit_Limit,
        p.Opening_Bal,
        p.Cust_Agent_Id,
        p.Status_Cd,
        p.Village_Id,
        p.Ps_Id,
        p.Post_Id,
        p.Dist_Id,
        IFNULL(po.Pin_Code, '') AS Pin_Code,
        COUNT(*) OVER() AS Total_Count
    FROM mst_party p
    LEFT JOIN mst_post po ON po.Post_Id = p.Post_Id
    WHERE p.Party_Type = pParty_Type
      AND (IFNULL(pStatus, 0) = 0 OR IFNULL(p.Status_Cd, 1) = pStatus)
      AND (pBranch_Id = 0 OR IFNULL(p.Branch_Id, 0) = 0 OR p.Branch_Id = pBranch_Id)
      AND (pAgent_Id = 0 OR IFNULL(p.Cust_Agent_Id, 0) = pAgent_Id)
      AND (
            vSearch = ''
         OR p.Party_Name LIKE CONCAT('%', vSearch, '%')
         OR p.Party_Code LIKE CONCAT('%', vSearch, '%')
         OR IFNULL(p.Contact_No, '') LIKE CONCAT('%', vSearch, '%')
      )
    ORDER BY p.Party_Name, p.Party_Code
    LIMIT vLimit OFFSET vOffset;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_PARTY_BY_ID;
DELIMITER $$
CREATE PROCEDURE USP_GET_PARTY_BY_ID(
    IN pParty_Id INT
)
BEGIN
    SELECT
        p.Party_Id,
        p.Party_Code,
        p.Party_Name,
        p.Contact_No,
        p.AltContact_No,
        p.EMail,
        p.Address_Line1,
        p.Address_Line2,
        p.City,
        p.State,
        p.Pan_No,
        p.GstIn,
        p.State_Cd,
        p.Credit_Limit,
        p.Opening_Bal,
        p.Cust_Agent_Id,
        p.Status_Cd,
        p.Village_Id,
        p.Ps_Id,
        p.Post_Id,
        p.Dist_Id,
        IFNULL(po.Pin_Code, '') AS Pin_Code
    FROM mst_party p
    LEFT JOIN mst_post po ON po.Post_Id = p.Post_Id
    WHERE p.Party_Id = pParty_Id
    LIMIT 1;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_PURCHASE_LINE_ITEMS;
DELIMITER $$
CREATE PROCEDURE USP_GET_PURCHASE_LINE_ITEMS(
    IN pTrading_Id INT
)
BEGIN
    SELECT
        p.Prod_Id,
        i.Cate_Id AS Cat_Id,
        i.Prod_ShortNm AS Prod_Name,
        p.Gst_Code AS hsn_code,
        p.Item_Qty AS qnty,
        p.Item_Rate,
        p.Unit_Id,
        UDF_GET_UNIT_NAME(p.Unit_Id) AS Unit_Name,
        p.Item_Total,
        p.MRP,
        IFNULL(i.Sale_Margin, 0) AS Sale_Margin,
        p.Disc_Prcnt,
        p.Disc_Amt,
        p.Taxable_Amt,
        p.SGST_Prcnt,
        p.CGST_Prcnt,
        p.SGST_Amt,
        p.CGST_Amt,
        p.Net_Amt,
        s.Pack_Date
    FROM (
        SELECT tp.*,
               ROW_NUMBER() OVER (
                   PARTITION BY tp.Prod_Id, tp.Item_Qty, ROUND(tp.Item_Rate, 2), ROUND(IFNULL(tp.MRP, 0), 2)
                   ORDER BY tp.Sl
               ) AS line_no
        FROM trans_trading_products tp
        WHERE tp.Trading_Id = pTrading_Id
    ) p
    JOIN mst_product i ON i.Prod_Id = p.Prod_Id
    LEFT JOIN (
        SELECT
            st.Prod_Id,
            st.Quantity,
            ROUND(st.Rate, 2) AS Rate,
            ROUND(IFNULL(st.MRP, 0), 2) AS MRP,
            st.Pack_Date,
            ROW_NUMBER() OVER (
                PARTITION BY st.Prod_Id, st.Quantity, ROUND(st.Rate, 2), ROUND(IFNULL(st.MRP, 0), 2)
                ORDER BY st.Stock_Id
            ) AS line_no
        FROM trns_stockinout st
        WHERE st.Type_Id = pTrading_Id
    ) s ON s.Prod_Id = p.Prod_Id
       AND s.Quantity = p.Item_Qty
       AND s.Rate = ROUND(p.Item_Rate, 2)
       AND s.MRP = ROUND(IFNULL(p.MRP, 0), 2)
       AND s.line_no = p.line_no
    ORDER BY p.Sl;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS USP_GET_PURCHASE_RETURN_POOL;
DELIMITER $$
CREATE PROCEDURE USP_GET_PURCHASE_RETURN_POOL(
    IN pParty_Id INT,
    IN pInvoice_No VARCHAR(50),
    IN pRef_No VARCHAR(50)
)
BEGIN
    SELECT rp.Prod_Id,
           ROUND(rp.Item_Rate, 2) AS Item_Rate,
           SUM(rp.Item_Qty) AS Returned_Qty
    FROM trans_trading r
    INNER JOIN trans_trading_products rp ON rp.Trading_Id = r.Trading_Id
    WHERE r.Tranding_Type = 4
      AND IFNULL(r.Status_Cd, 1) = 1
      AND r.Party_Id = pParty_Id
      AND (
            IFNULL(TRIM(r.Ref_No), '') = ''
         OR r.Ref_No = pInvoice_No
         OR r.Ref_No = pRef_No
      )
    GROUP BY rp.Prod_Id, ROUND(rp.Item_Rate, 2);
END$$
DELIMITER ;
