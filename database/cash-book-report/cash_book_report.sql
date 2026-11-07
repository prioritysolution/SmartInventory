DROP PROCEDURE IF EXISTS USP_RPT_CASH_BOOK_LINES;
DROP PROCEDURE IF EXISTS USP_RPT_CASH_BOOK;
DROP FUNCTION IF EXISTS UDF_CAL_CASH_BOOK_BALANCE;

DELIMITER $$
CREATE FUNCTION UDF_CAL_CASH_BOOK_BALANCE(
    pYear_Id INT,
    pAsOnDate DATE
)
RETURNS DECIMAL(18, 2)
READS SQL DATA
BEGIN
    DECLARE vCash_Id INT DEFAULT 0;

    SELECT IFNULL(Cash_Ledg, 0)
      INTO vCash_Id
      FROM mst_default_ledger
     LIMIT 1;

    IF vCash_Id = 0 THEN
        RETURN 0;
    END IF;

    RETURN IFNULL((
        SELECT SUM(
            CASE
                WHEN d.Trans_Type = 'D' THEN d.Vou_Amount
                WHEN d.Trans_Type = 'C' THEN -d.Vou_Amount
                ELSE 0
            END
        )
        FROM trans_voucher_details d
        INNER JOIN trans_voucher_master m ON m.Voucher_Id = d.Voucher_Id
        WHERE d.GlHead_Id = vCash_Id
          AND d.Trans_Type IN ('D', 'C')
          AND m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Mode = 1
          AND m.Vou_Date <= pAsOnDate
    ), 0);
END$$

CREATE PROCEDURE USP_RPT_CASH_BOOK(
    IN pYear_Id INT,
    IN pFromDate DATE,
    IN pToDate DATE
)
BEGIN
    DECLARE vCash_Id INT DEFAULT 0;

    SELECT IFNULL(Cash_Ledg, 0)
      INTO vCash_Id
      FROM mst_default_ledger
     LIMIT 1;

    SELECT
        1 AS Is_Summary,
        UDF_CAL_CASH_BOOK_BALANCE(pYear_Id, DATE_SUB(pFromDate, INTERVAL 1 DAY)) AS Opening,
        NULL AS Vou_Date,
        '' AS Side,
        '' AS Gl_Head,
        '' AS Particulars,
        0.00 AS Amount,
        0 AS Voucher_Id

    UNION ALL

    SELECT
        0 AS Is_Summary,
        0.00 AS Opening,
        detail.Vou_Date,
        detail.Side,
        detail.Gl_Head,
        detail.Particulars,
        detail.Amount,
        detail.Voucher_Id
    FROM (
        SELECT
            m.Vou_Date,
            'R' AS Side,
            IFNULL(g.Account_Desc, '') AS Gl_Head,
            COALESCE(NULLIF(TRIM(m.Particulars), ''), NULLIF(TRIM(m.Ref_Vou_No), ''), '') AS Particulars,
            SUM(counterpart.Vou_Amount) AS Amount,
            m.Voucher_Id
        FROM trans_voucher_master m
        INNER JOIN (
            SELECT Voucher_Id, SUM(Vou_Amount) AS Vou_Amount
            FROM trans_voucher_details
            WHERE GlHead_Id = vCash_Id
              AND Trans_Type = 'D'
            GROUP BY Voucher_Id
        ) cash ON cash.Voucher_Id = m.Voucher_Id
        INNER JOIN (
            SELECT Voucher_Id, GlHead_Id, SUM(Vou_Amount) AS Vou_Amount
            FROM trans_voucher_details
            WHERE Trans_Type = 'C'
              AND GlHead_Id <> vCash_Id
            GROUP BY Voucher_Id, GlHead_Id
        ) counterpart ON counterpart.Voucher_Id = m.Voucher_Id
        LEFT JOIN mst_acct_glhead g ON g.Account_Id = counterpart.GlHead_Id
        WHERE m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Mode = 1
          AND m.Vou_Date BETWEEN pFromDate AND pToDate
        GROUP BY m.Voucher_Id, m.Vou_Date, g.Account_Id, g.Account_Desc, m.Particulars, m.Ref_Vou_No

        UNION ALL

        SELECT
            m.Vou_Date,
            'P' AS Side,
            IFNULL(g.Account_Desc, '') AS Gl_Head,
            COALESCE(NULLIF(TRIM(m.Particulars), ''), NULLIF(TRIM(m.Ref_Vou_No), ''), '') AS Particulars,
            SUM(counterpart.Vou_Amount) AS Amount,
            m.Voucher_Id
        FROM trans_voucher_master m
        INNER JOIN (
            SELECT Voucher_Id, SUM(Vou_Amount) AS Vou_Amount
            FROM trans_voucher_details
            WHERE GlHead_Id = vCash_Id
              AND Trans_Type = 'C'
            GROUP BY Voucher_Id
        ) cash ON cash.Voucher_Id = m.Voucher_Id
        INNER JOIN (
            SELECT Voucher_Id, GlHead_Id, SUM(Vou_Amount) AS Vou_Amount
            FROM trans_voucher_details
            WHERE Trans_Type = 'D'
              AND GlHead_Id <> vCash_Id
            GROUP BY Voucher_Id, GlHead_Id
        ) counterpart ON counterpart.Voucher_Id = m.Voucher_Id
        LEFT JOIN mst_acct_glhead g ON g.Account_Id = counterpart.GlHead_Id
        WHERE m.Year_Id = pYear_Id
          AND IFNULL(m.Status, 2) = 2
          AND m.Vou_Mode = 1
          AND m.Vou_Date BETWEEN pFromDate AND pToDate
        GROUP BY m.Voucher_Id, m.Vou_Date, g.Account_Id, g.Account_Desc, m.Particulars, m.Ref_Vou_No
    ) detail
    ORDER BY Is_Summary DESC, Vou_Date, Voucher_Id, Gl_Head;
END$$
DELIMITER ;
