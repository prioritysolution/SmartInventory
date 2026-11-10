-- Deploy on each organisation schema.
-- temppurchase must contain item_id, qnty, and an optional Barcode column.
-- UDF_CAL_AVAIL_STOCK must be deployed before this procedure.

DROP PROCEDURE IF EXISTS USP_ADD_EDIT_PURCHASE_RETURN;
DELIMITER $$
CREATE PROCEDURE USP_ADD_EDIT_PURCHASE_RETURN(
    IN pPurchase_Id BIGINT,
    IN pVouch_Id BIGINT,
    IN pBranch_Id SMALLINT,
    IN pPur_No VARCHAR(25),
    IN pPur_Date DATE,
    IN pParty_Id INT,
    IN pTot_Amt NUMERIC(18,2),
    IN pDisc_Perc NUMERIC(5,2),
    IN pDisc_Amt NUMERIC(8,2),
    IN pFreg_Amt NUMERIC(8,2),
    IN pTaxble_Amt NUMERIC(12,2),
    IN pTot_Gst NUMERIC(10,2),
    IN pRound_Amt NUMERIC(5,2),
    IN pNet_Amt NUMERIC(12,2),
    IN pTrans_Mode TINYINT,
    IN pBank_Ledg INT,
    IN pUser_Id SMALLINT,
    IN pFin_Id INT,
    IN pMode INT,
    IN pInstrument_No VARCHAR(50)
)
BEGIN
    DECLARE pError_No INT DEFAULT 0;
    DECLARE pError_Message VARCHAR(200) DEFAULT '';
    DECLARE pData_Count INT DEFAULT 0;
    DECLARE pInvalidAvailableQty_Count INT DEFAULT 0;
    DECLARE pPur_Id INT;
    DECLARE pTrans_Id BIGINT;
    DECLARE pNpur_No VARCHAR(100);
    DECLARE pVouch_No VARCHAR(100);
    DECLARE pCr_Ledger INT;
    DECLARE pCGST_Ledg INT;
    DECLARE pSGST_Ledg INT;
    DECLARE pRound_Ledg INT;
    DECLARE pDisc_Ledg INT;
    DECLARE pFreg_Ledg INT;
    DECLARE pParty_Ledg INT;
    DECLARE pParty_Type TINYINT;
    DECLARE pTot_Cgst NUMERIC(18,2);
    DECLARE pTot_Sgst NUMERIC(18,2);
    DECLARE pTradingType TINYINT;
    DECLARE pVouTransType TINYINT;
    DECLARE pStockType TINYINT;
    DECLARE pStockAt TINYINT;

    SET pTradingType = (
        SELECT Value_Id
        FROM smartinventory_core.mst_options
        WHERE Option_Id = 6 AND Value_Name = 'Purchase Return Entry'
        LIMIT 1
    );
    SET pVouTransType = (
        SELECT Value_Id
        FROM smartinventory_core.mst_options
        WHERE Option_Id = 13 AND Value_Name = 'Purchase Return'
        LIMIT 1
    );
    SET pStockType = (
        SELECT Value_Id
        FROM smartinventory_core.mst_options
        WHERE Option_Id = 7 AND Value_Name = 'Purchase Return'
        LIMIT 1
    );
    SET pStockAt = (
        SELECT Value_Id
        FROM smartinventory_core.mst_options
        WHERE Option_Id = 8 AND Value_Name = 'Return'
        LIMIT 1
    );

    SET pTradingType = IFNULL(pTradingType, 4);
    SET pVouTransType = IFNULL(pVouTransType, 5);
    SET pStockType = IFNULL(pStockType, 7);
    SET pStockAt = IFNULL(pStockAt, 4);

    SET pParty_Type = (SELECT Party_Type FROM mst_party WHERE Party_Id = pParty_Id);
    SET pParty_Ledg = CASE
        WHEN pParty_Type = 1 THEN (SELECT Credit_Ledger FROM mst_default_ledger)
        WHEN pParty_Type = 2 THEN (SELECT Debit_Ledger FROM mst_default_ledger)
    END;
    SET pCr_Ledger = CASE
        WHEN pTrans_Mode = 1 THEN (SELECT Cash_Ledg FROM mst_default_ledger)
        WHEN pTrans_Mode = 2 THEN pBank_Ledg
        WHEN pTrans_Mode = 3 THEN pParty_Ledg
    END;

    SELECT CGST_Ledg, SGST_Ledg, Round_Ledg, Discp_Ledg, Freight_Ledg
    INTO pCGST_Ledg, pSGST_Ledg, pRound_Ledg, pDisc_Ledg, pFreg_Ledg
    FROM mst_default_ledger;

    SELECT COUNT(*) INTO pData_Count FROM temppurchase;

    IF pData_Count = 0 THEN
        SET pError_No = -1;
        SET pError_Message = 'No Purchase Item Found !!';
    ELSE
        SELECT COUNT(*) INTO pInvalidAvailableQty_Count
        FROM (
            SELECT item_id, SUM(qnty) AS Return_Qty
            FROM temppurchase
            GROUP BY item_id
        ) requested
        WHERE requested.Return_Qty > GREATEST(
            UDF_CAL_AVAIL_STOCK(requested.item_id, pPur_Date)
            + IF(
                pMode = 2,
                IFNULL((
                    SELECT SUM(currentReturn.Quantity)
                    FROM trns_stockinout currentReturn
                    WHERE currentReturn.Stock_Type = pStockType
                      AND currentReturn.Type_Id = pPurchase_Id
                      AND currentReturn.Prod_Id = requested.item_id
                      AND currentReturn.InOut_Date < DATE_ADD(pPur_Date, INTERVAL 1 DAY)
                ), 0),
                0
            ),
            0
        );

        IF pInvalidAvailableQty_Count > 0 THEN
            SET pError_No = -6;
            SET pError_Message = 'Return quantity cannot exceed the currently available quantity for the item.';
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 1 THEN
        SET pNpur_No = UDF_GEN_SYS_NO('PR', pFin_Id, pBranch_Id);
        IF pNpur_No IS NULL THEN
            SET pError_No = -2;
            SET pError_Message = 'Return Number Not Genereated !!';
        ELSE
            SET pVouch_No = UDF_GEN_SYS_NO(
                CASE WHEN pTrans_Mode = 1 THEN 'REC' ELSE 'JOU' END,
                pFin_Id,
                pBranch_Id
            );
            IF pVouch_No IS NULL THEN
                SET pError_No = -3;
                SET pError_Message = 'voucher Number Is Not Genereated !!';
            END IF;
        END IF;
    END IF;

    IF pError_No = 0 AND pMode = 1 THEN
        INSERT INTO trans_voucher_master (
            Year_Id, Vou_Date, Vou_Type, Vou_Mode, Vou_No, Ref_Vou_No, Particulars,
            Trans_Type, Instrument_No, Type_Id, Status, Created_By, Approved_By, Approved_On
        )
        VALUES (
            pFin_Id, pPur_Date,
            CASE WHEN pTrans_Mode = 1 THEN 1 ELSE 4 END,
            pTrans_Mode, pVouch_No, pNpur_No, CONCAT('By Purchase Return', pPur_No),
            pVouTransType,
            CASE WHEN pTrans_Mode = 2 THEN NULLIF(TRIM(IFNULL(pInstrument_No, '')), '') ELSE NULL END,
            pPur_Id, 2, pUser_Id, pUser_Id, CURRENT_TIMESTAMP()
        );

        SET pTrans_Id = LAST_INSERT_ID();

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pTrans_Id, 'D', pCr_Ledger, pNet_Amt);

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        SELECT pTrans_Id, 'C', c.Pur_Ledg, SUM(tp.tot_amt)
        FROM temppurchase tp
        JOIN mst_product product ON product.Prod_Id = tp.item_id
        JOIN mst_prod_category c ON c.Prd_CateId = product.Cate_Id
        GROUP BY c.Pur_Ledg;

        IF pTot_Gst <> 0 THEN
            SET pTot_Cgst = IFNULL((SELECT SUM(cgst_amt) FROM temppurchase), 0);
            SET pTot_Sgst = IFNULL((SELECT SUM(sgst_amt) FROM temppurchase), 0);
            IF pTot_Cgst <> 0 THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'C', pCGST_Ledg, pTot_Cgst);
            END IF;
            IF pTot_Sgst <> 0 THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pTrans_Id, 'C', pSGST_Ledg, pTot_Sgst);
            END IF;
        END IF;

        IF pRound_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (
                pTrans_Id,
                CASE WHEN pRound_Amt < 0 THEN 'D' ELSE 'C' END,
                pRound_Ledg,
                CASE WHEN pRound_Amt < 0 THEN (pRound_Amt * -1) ELSE pRound_Amt END
            );
        END IF;
        IF pDisc_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pTrans_Id, 'D', pDisc_Ledg, pDisc_Amt);
        END IF;
        IF pFreg_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pTrans_Id, 'C', pFreg_Ledg, pFreg_Amt);
        END IF;

        INSERT INTO trans_trading (
            Branch_Id, Tranding_Type, Invoice_No, Ref_No, Invoice_Date, Party_Id,
            Invoice_Amt, Disc_Percent, Disc_Amount, Freight_Amt, Taxable_Amt, GST_Amt,
            Round_Off, Net_Amt, Status_Cd, Voucher_Id, Created_By
        )
        VALUES (
            pBranch_Id, pTradingType, pNpur_No, pPur_No, pPur_Date, pParty_Id,
            pTot_Amt, pDisc_Perc, pDisc_Amt, pFreg_Amt, pTaxble_Amt, pTot_Gst,
            pRound_Amt, pNet_Amt, 1, pTrans_Id, pUser_Id
        );

        SET pPur_Id = LAST_INSERT_ID();

        UPDATE trans_voucher_master
        SET Type_Id = pPur_Id, Trans_Type = pVouTransType
        WHERE Voucher_Id = pTrans_Id;

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
            MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt,
            CGST_Prcnt, CGST_Amt, Net_Amt
        )
        SELECT pPur_Id, item_id, hsn_code, qnty, rate, unit, tot_amt, sale_mrp,
               disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
        FROM temppurchase;

        INSERT INTO trans_trading_gst (
            Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt,
            CGST_Prcnt, CGST_Amt, Tax_Amount
        )
        SELECT pPur_Id, hsn_code, SUM(tax_amt), sgst_perc, SUM(sgst_amt),
               cgst_perc, SUM(cgst_amt), SUM(sgst_amt) + SUM(cgst_amt)
        FROM temppurchase
        GROUP BY hsn_code, sgst_perc, cgst_perc;

        INSERT INTO trns_stockinout (
            Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
            Quantity, Rate, Total_Amount, MRP, Stock_At, Pack_Date, Barcode
        )
        SELECT pBranch_Id, pStockType, pPur_Id, 'R', pPur_Date, item_id, unit,
               qnty, rate, tot_amt, sale_mrp, pStockAt, Pack_Date, Barcode
        FROM temppurchase;

        INSERT INTO trn_party_trans (
            Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By
        )
        VALUES (
            pParty_Id, pPur_Date, 'D', pNet_Amt, pTrans_Id, pPur_Id,
            CONCAT('Purchase Return ', pNpur_No), pTrans_Mode, pUser_Id
        );

        SET pError_Message = CONCAT(
            'Purchase Return Is Successful Return No Is ', pNpur_No,
            ' Adn Voucher No Is ', pVouch_No
        );
    END IF;

    IF pError_No = 0 AND pMode = 2 THEN
        UPDATE trans_trading
        SET Ref_No = pPur_No,
            Invoice_Date = pPur_Date,
            Party_Id = pParty_Id,
            Invoice_Amt = pTot_Amt,
            Disc_Percent = pDisc_Perc,
            Disc_Amount = pDisc_Amt,
            Freight_Amt = pFreg_Amt,
            Taxable_Amt = pTaxble_Amt,
            GST_Amt = pTot_Gst,
            Round_Off = pRound_Amt,
            Net_Amt = pNet_Amt,
            Tranding_Type = pTradingType
        WHERE Trading_Id = pPurchase_Id;

        UPDATE trans_voucher_master
        SET Trans_Type = pVouTransType,
            Vou_Date = pPur_Date,
            Vou_Mode = pTrans_Mode,
            Vou_Type = CASE WHEN pTrans_Mode = 1 THEN 1 ELSE 4 END,
            Instrument_No = CASE
                WHEN pTrans_Mode = 2 THEN NULLIF(TRIM(IFNULL(pInstrument_No, '')), '')
                ELSE NULL
            END
        WHERE Voucher_Id = pVouch_Id;

        DELETE FROM trans_trading_products WHERE Trading_Id = pPurchase_Id;
        DELETE FROM trans_trading_gst WHERE Trading_Id = pPurchase_Id;
        DELETE FROM trns_stockinout WHERE Type_Id = pPurchase_Id;
        DELETE FROM trn_party_trans WHERE Trading_Id = pPurchase_Id;
        DELETE FROM trans_voucher_details WHERE Voucher_Id = pVouch_Id;

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        VALUES (pVouch_Id, 'D', pCr_Ledger, pNet_Amt);

        INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
        SELECT pVouch_Id, 'C', c.Pur_Ledg, SUM(tp.tot_amt)
        FROM temppurchase tp
        JOIN mst_product product ON product.Prod_Id = tp.item_id
        JOIN mst_prod_category c ON c.Prd_CateId = product.Cate_Id
        GROUP BY c.Pur_Ledg;

        IF pTot_Gst <> 0 THEN
            SET pTot_Cgst = IFNULL((SELECT SUM(cgst_amt) FROM temppurchase), 0);
            SET pTot_Sgst = IFNULL((SELECT SUM(sgst_amt) FROM temppurchase), 0);
            IF pTot_Cgst <> 0 THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pVouch_Id, 'C', pCGST_Ledg, pTot_Cgst);
            END IF;
            IF pTot_Sgst <> 0 THEN
                INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
                VALUES (pVouch_Id, 'C', pSGST_Ledg, pTot_Sgst);
            END IF;
        END IF;

        IF pRound_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (
                pVouch_Id,
                CASE WHEN pRound_Amt < 0 THEN 'D' ELSE 'C' END,
                pRound_Ledg,
                CASE WHEN pRound_Amt < 0 THEN (pRound_Amt * -1) ELSE pRound_Amt END
            );
        END IF;
        IF pDisc_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pVouch_Id, 'D', pDisc_Ledg, pDisc_Amt);
        END IF;
        IF pFreg_Amt <> 0 THEN
            INSERT INTO trans_voucher_details (Voucher_Id, Trans_Type, GlHead_Id, Vou_Amount)
            VALUES (pVouch_Id, 'C', pFreg_Ledg, pFreg_Amt);
        END IF;

        INSERT INTO trans_trading_products (
            Trading_Id, Prod_Id, Gst_Code, Item_Qty, Item_Rate, Unit_Id, Item_Total,
            MRP, Disc_Prcnt, Disc_Amt, Taxable_Amt, SGST_Prcnt, SGST_Amt,
            CGST_Prcnt, CGST_Amt, Net_Amt
        )
        SELECT pPurchase_Id, item_id, hsn_code, qnty, rate, unit, tot_amt, sale_mrp,
               disc_perc, disc_amt, tax_amt, sgst_perc, sgst_amt, cgst_perc, cgst_amt, net_amt
        FROM temppurchase;

        INSERT INTO trans_trading_gst (
            Trading_Id, HSN_No, Taxable_Amt, SGST_Prcnt, SGST_Amt,
            CGST_Prcnt, CGST_Amt, Tax_Amount
        )
        SELECT pPurchase_Id, hsn_code, SUM(tax_amt), sgst_perc, SUM(sgst_amt),
               cgst_perc, SUM(cgst_amt), SUM(sgst_amt) + SUM(cgst_amt)
        FROM temppurchase
        GROUP BY hsn_code, sgst_perc, cgst_perc;

        INSERT INTO trns_stockinout (
            Branch_Id, Stock_Type, Type_Id, Type_Nature, InOut_Date, Prod_Id, Unit_Id,
            Quantity, Rate, Total_Amount, MRP, Stock_At, Pack_Date, Barcode
        )
        SELECT pBranch_Id, pStockType, pPurchase_Id, 'R', pPur_Date, item_id, unit,
               qnty, rate, tot_amt, sale_mrp, pStockAt, Pack_Date, Barcode
        FROM temppurchase;

        INSERT INTO trn_party_trans (
            Party_Id, Trans_Date, Trans_Type, Amount, Txn_Id, Trading_Id, Remarks, Trans_Mode, Created_By
        )
        VALUES (
            pParty_Id, pPur_Date, 'D', pNet_Amt, pVouch_Id, pPurchase_Id,
            CONCAT('Purchase Return ', IFNULL(pNpur_No, pPur_No)), pTrans_Mode, pUser_Id
        );

        SET pError_Message = 'Purchase Return Is Successfully Updated !!';
    END IF;

    SELECT pError_No AS Error_No, pError_Message AS Message;
    DROP TEMPORARY TABLE IF EXISTS temppurchase;
END$$
DELIMITER ;
