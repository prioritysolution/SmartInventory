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
         OR IFNULL(p.Address_Line1, '') LIKE CONCAT('%', vSearch, '%')
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
DROP PROCEDURE IF EXISTS USP_GET_PURCHASE_DTLS;
DELIMITER $$
CREATE PROCEDURE USP_GET_PURCHASE_DTLS(
    IN pPur_Id INT
)
BEGIN
    SELECT
        m.Trading_Id AS Pur_Id,
        m.Invoice_No,
        m.Ref_No,
        m.Invoice_Date,
        m.Party_Id,
        m.Invoice_Amt AS Tot_Amt,
        m.Disc_Percent,
        m.Disc_Amount,
        m.Freight_Amt,
        m.Taxable_Amt,
        m.GST_Amt,
        m.Round_Off,
        m.Net_Amt,
        v.Voucher_Id,
        v.Vou_Mode AS Trans_Mode,
        v.Instrument_No,
        IFNULL((
            SELECT d.GlHead_Id
            FROM trans_voucher_details d
            JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
            WHERE d.Voucher_Id = v.Voucher_Id
              AND g.Account_For = 'B'
            LIMIT 1
        ), 0) AS Bank_Ledg,
        (
            SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'Prod_Id', p.Prod_Id,
                'Cat_Id', i.Cate_Id,
                'Prod_Name', i.Prod_ShortNm,
                'hsn_code', p.Gst_Code,
                'qnty', p.Item_Qty,
                'Item_Rate', p.Item_Rate,
                'Unit_Id', p.Unit_Id,
                'Unit_Name', UDF_GET_UNIT_NAME(p.Unit_Id),
                'Item_Total', p.Item_Total,
                'MRP', p.MRP,
                'Sale_Margin', IFNULL(i.Sale_Margin, 0),
                'Disc_Prcnt', p.Disc_Prcnt,
                'Disc_Amt', p.Disc_Amt,
                'Taxable_Amt', p.Taxable_Amt,
                'SGST_Prcnt', p.SGST_Prcnt,
                'CGST_Prcnt', p.CGST_Prcnt,
                'SGST_Amt', p.SGST_Amt,
                'CGST_Amt', p.CGST_Amt,
                'Net_Amt', p.Net_Amt,
                'Pack_Date', s.Pack_Date,
                'Barcode', IFNULL(s.Barcode, '')
            ) ORDER BY p.Sl)
            FROM (
                SELECT tp.*,
                       ROW_NUMBER() OVER (
                           PARTITION BY tp.Prod_Id, tp.Item_Qty,
                                        ROUND(tp.Item_Rate, 2), ROUND(IFNULL(tp.MRP, 0), 2)
                           ORDER BY tp.Sl
                       ) AS line_no
                FROM trans_trading_products tp
                WHERE tp.Trading_Id = pPur_Id
            ) p
            JOIN mst_product i ON i.Prod_Id = p.Prod_Id
            LEFT JOIN (
                SELECT
                    st.Prod_Id,
                    st.Quantity,
                    ROUND(st.Rate, 2) AS Rate,
                    ROUND(IFNULL(st.MRP, 0), 2) AS MRP,
                    st.Pack_Date,
                    st.Barcode,
                    ROW_NUMBER() OVER (
                        PARTITION BY st.Prod_Id, st.Quantity,
                                     ROUND(st.Rate, 2), ROUND(IFNULL(st.MRP, 0), 2)
                        ORDER BY st.Stock_Id
                    ) AS line_no
                FROM trns_stockinout st
                WHERE st.Type_Id = pPur_Id
            ) s ON s.Prod_Id = p.Prod_Id
               AND s.Quantity = p.Item_Qty
               AND s.Rate = ROUND(p.Item_Rate, 2)
               AND s.MRP = ROUND(IFNULL(p.MRP, 0), 2)
               AND s.line_no = p.line_no
        ) AS Item_Details
    FROM trans_trading m
    LEFT JOIN trans_voucher_master v ON v.Voucher_Id = m.Voucher_Id
    WHERE m.Trading_Id = pPur_Id;
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
