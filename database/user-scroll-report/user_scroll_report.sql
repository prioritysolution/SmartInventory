DROP PROCEDURE IF EXISTS USP_GET_DEFAULT_SUNDRY_LEDGER_IDS;
DROP PROCEDURE IF EXISTS USP_RPT_USER_SCROLL_COUNTER_OPENING;
DROP PROCEDURE IF EXISTS USP_RPT_USER_SCROLL_CASH_MOVE;
DROP PROCEDURE IF EXISTS USP_RPT_USER_SCROLL_VOUCHERS;
DROP PROCEDURE IF EXISTS USP_RPT_USER_SCROLL_TRADING_PRODUCTS;
DROP PROCEDURE IF EXISTS USP_RPT_USER_SCROLL;
DELIMITER $$
CREATE PROCEDURE USP_RPT_USER_SCROLL(
    IN pYear_Id INT,
    IN pAsOnDate DATE,
    IN pUser_Id INT
)
BEGIN
    SELECT
        m.Voucher_Id,
        IFNULL(m.Vou_No, '') AS Vou_No,
        IFNULL(m.Particulars, '') AS Particulars,
        IFNULL(m.Ref_Vou_No, '') AS Ref_Vou_No,
        IFNULL(m.Vou_Type, 0) AS Vou_Type,
        IFNULL(m.Vou_Mode, 0) AS Vou_Mode,
        IFNULL(t.Trading_Id, 0) AS Trading_Id,
        IFNULL(t.Tranding_Type, 0) AS Tranding_Type,
        IFNULL(t.Invoice_No, '') AS Invoice_No,
        IFNULL(t.Net_Amt, 0) AS Net_Amt,
        CASE
            WHEN t.Trading_Id IS NOT NULL AND IFNULL(t.Party_Id, 0) = 0 THEN 'Cash'
            ELSE IFNULL(p.Party_Name, '')
        END AS Party_Name,
        IFNULL(a.Agent_Name, '') AS Agent_Name,
        CASE
            WHEN m.Vou_Mode = 1 AND t.Trading_Id IS NOT NULL AND t.Tranding_Type IN (3, 4)
                THEN IFNULL(t.Net_Amt, 0)
            WHEN m.Vou_Mode = 1 AND (t.Trading_Id IS NULL OR t.Tranding_Type NOT IN (2, 3, 4, 5))
                THEN IFNULL(v.Cash_Debit, 0)
            ELSE 0
        END AS Receipt_Cash,
        CASE
            WHEN m.Vou_Mode = 2 AND t.Trading_Id IS NOT NULL AND t.Tranding_Type IN (3, 4)
                THEN IFNULL(t.Net_Amt, 0)
            WHEN m.Vou_Mode = 2 AND (t.Trading_Id IS NULL OR t.Tranding_Type NOT IN (2, 3, 4, 5))
                THEN IFNULL(v.Bank_Debit, 0)
            ELSE 0
        END AS Receipt_Bank,
        CASE
            WHEN m.Vou_Mode = 1 AND t.Trading_Id IS NOT NULL AND t.Tranding_Type IN (2, 5)
                THEN IFNULL(t.Net_Amt, 0)
            WHEN m.Vou_Mode = 1 AND (t.Trading_Id IS NULL OR t.Tranding_Type NOT IN (2, 3, 4, 5))
                THEN IFNULL(v.Cash_Credit, 0)
            ELSE 0
        END AS Payment_Cash,
        CASE
            WHEN m.Vou_Mode = 2 AND t.Trading_Id IS NOT NULL AND t.Tranding_Type IN (2, 5)
                THEN IFNULL(t.Net_Amt, 0)
            WHEN m.Vou_Mode = 2 AND (t.Trading_Id IS NULL OR t.Tranding_Type NOT IN (2, 3, 4, 5))
                THEN IFNULL(v.Bank_Credit, 0)
            ELSE 0
        END AS Payment_Bank,
        CASE
            WHEN m.Vou_Mode = 3 THEN COALESCE(
                NULLIF(t.Net_Amt, 0),
                NULLIF(pt.Trading_Amount, 0),
                NULLIF(pv.Voucher_Amount, 0),
                IFNULL(v.Voucher_Amount, 0)
            )
            ELSE 0
        END AS Credit,
        pdt.Trading_Id AS Item_Trading_Id,
        IFNULL(pr.Prod_ShortNm, '') AS Item_Name,
        IFNULL(pdt.Gst_Code, '') AS HSN_No,
        IFNULL(UDF_GET_UNIT_NAME(pdt.Unit_Id), '') AS Unit_Name,
        IFNULL(pdt.Item_Qty, 0) AS Item_Qty,
        IFNULL(pdt.Item_Rate, 0) AS Item_Rate,
        IFNULL(pdt.MRP, 0) AS MRP,
        IFNULL(pdt.Disc_Prcnt, 0) AS Disc_Prcnt,
        IFNULL(pdt.Taxable_Amt, 0) AS Taxable_Amt,
        (IFNULL(pdt.SGST_Prcnt, 0) + IFNULL(pdt.CGST_Prcnt, 0)) AS GST_Prcnt,
        (IFNULL(pdt.SGST_Amt, 0) + IFNULL(pdt.CGST_Amt, 0)) AS GST_Amt,
        IFNULL(pdt.Net_Amt, 0) AS Item_Net_Amt,
        IFNULL((
            SELECT g2.Account_Desc
            FROM trans_voucher_details d2
            INNER JOIN mst_acct_glhead g2 ON g2.Account_Id = d2.GlHead_Id
            WHERE d2.Voucher_Id = m.Voucher_Id
              AND IFNULL(g2.Account_For, 'O') NOT IN ('C', 'B')
            ORDER BY d2.Vou_Amount DESC, d2.VouDtls_Id
            LIMIT 1
        ), '') AS Ledger_Name
    FROM trans_voucher_master m
    LEFT JOIN trans_trading t ON t.Voucher_Id = m.Voucher_Id
    LEFT JOIN (
        SELECT pt.Trading_Id, MAX(pt.Party_Id) AS Party_Id, MAX(pt.Amount) AS Trading_Amount
        FROM trn_party_trans pt
        INNER JOIN trans_trading tx ON tx.Trading_Id = pt.Trading_Id
        INNER JOIN trans_voucher_master pm ON pm.Voucher_Id = tx.Voucher_Id
        WHERE IFNULL(pt.Trading_Id, 0) > 0
          AND pm.Year_Id = pYear_Id
          AND IFNULL(pm.Status, 2) = 2
          AND pm.Vou_Date = pAsOnDate
          AND (pUser_Id = 0 OR pm.Created_By = pUser_Id)
        GROUP BY pt.Trading_Id
    ) pt ON pt.Trading_Id = t.Trading_Id
    LEFT JOIN (
        SELECT pt.Txn_Id, MAX(pt.Party_Id) AS Party_Id, MAX(pt.Amount) AS Voucher_Amount
        FROM trn_party_trans pt
        INNER JOIN trans_voucher_master pm ON pm.Voucher_Id = pt.Txn_Id
        WHERE IFNULL(pt.Txn_Id, 0) > 0
          AND pm.Year_Id = pYear_Id
          AND IFNULL(pm.Status, 2) = 2
          AND pm.Vou_Date = pAsOnDate
          AND (pUser_Id = 0 OR pm.Created_By = pUser_Id)
        GROUP BY pt.Txn_Id
    ) pv ON pv.Txn_Id = m.Voucher_Id
    LEFT JOIN mst_party p
        ON p.Party_Id = COALESCE(NULLIF(t.Party_Id, 0), NULLIF(pt.Party_Id, 0), pv.Party_Id)
    LEFT JOIN mst_agent a ON a.Agent_Id = t.Agent_Id
    LEFT JOIN (
        SELECT
            d.Voucher_Id,
            SUM(CASE WHEN g.Account_For = 'C' AND d.Trans_Type = 'D' THEN d.Vou_Amount ELSE 0 END) AS Cash_Debit,
            SUM(CASE WHEN g.Account_For = 'C' AND d.Trans_Type = 'C' THEN d.Vou_Amount ELSE 0 END) AS Cash_Credit,
            SUM(CASE WHEN g.Account_For = 'B' AND d.Trans_Type = 'D' THEN d.Vou_Amount ELSE 0 END) AS Bank_Debit,
            SUM(CASE WHEN g.Account_For = 'B' AND d.Trans_Type = 'C' THEN d.Vou_Amount ELSE 0 END) AS Bank_Credit,
            MAX(d.Vou_Amount) AS Voucher_Amount
        FROM trans_voucher_details d
        INNER JOIN mst_acct_glhead g ON g.Account_Id = d.GlHead_Id
        INNER JOIN trans_voucher_master vm ON vm.Voucher_Id = d.Voucher_Id
        WHERE vm.Year_Id = pYear_Id
          AND IFNULL(vm.Status, 2) = 2
          AND vm.Vou_Date = pAsOnDate
          AND (pUser_Id = 0 OR vm.Created_By = pUser_Id)
        GROUP BY d.Voucher_Id
    ) v ON v.Voucher_Id = m.Voucher_Id
    LEFT JOIN trans_trading_products pdt ON pdt.Trading_Id = t.Trading_Id
    LEFT JOIN mst_product pr ON pr.Prod_Id = pdt.Prod_Id
    WHERE m.Year_Id = pYear_Id
      AND IFNULL(m.Status, 2) = 2
      AND m.Vou_Date = pAsOnDate
      AND (pUser_Id = 0 OR m.Created_By = pUser_Id)
    ORDER BY m.Voucher_Id, pdt.Sl;
END$$
DELIMITER ;
