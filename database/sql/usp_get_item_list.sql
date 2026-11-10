DROP PROCEDURE IF EXISTS USP_GET_ITEM_LIST;
DELIMITER $$
CREATE PROCEDURE USP_GET_ITEM_LIST(
    IN pCat_Id INT,
    IN pSub_Cat INT,
    IN pProd_Code VARCHAR(20),
    IN pPage INT,
    IN pPageSize INT
)
BEGIN
    DECLARE vPage BIGINT;
    DECLARE vPageSize INT;
    DECLARE vOffset BIGINT;
    SET vPage = GREATEST(COALESCE(pPage, 1), 1);
    SET vPageSize = GREATEST(COALESCE(pPageSize, 25), 1);
    SET vOffset = (vPage - 1) * vPageSize;

    SELECT
        m.Prod_Id, m.Prod_Code, m.Prod_ShortNm, m.Unit_Id,
        m.Cate_Id    AS Prd_CateId,
        m.SubCate_Id AS Prd_SubCateId,
        UDF_GET_UNIT_NAME(m.Unit_Id)       AS Unit_Name,
        UDF_GET_ITEM_GST_DETAILS(m.Gst_Id) AS GST_Data,
        c.Prd_CateNm, s.Prd_SubCateNm, c.Is_Fmcg, m.Gst_Id,
        IFNULL(m.Sale_Margin, 0) AS Sale_Margin
    FROM mst_product m
    INNER JOIN mst_prod_category c    ON c.Prd_CateId    = m.Cate_Id
    INNER JOIN mst_prod_subcategory s ON s.Prd_SubCateId = m.SubCate_Id
    WHERE (pCat_Id    = 0 OR m.Cate_Id    = pCat_Id)
      AND (pSub_Cat   = 0 OR m.SubCate_Id = pSub_Cat)
      AND (COALESCE(pProd_Code, '') = ''
           OR m.Prod_Code LIKE CONCAT(pProd_Code, '%')
           OR m.Prod_ShortNm LIKE CONCAT('%', pProd_Code, '%'))
    ORDER BY m.Prod_Code, m.Prod_Id
    LIMIT vPageSize OFFSET vOffset;

    SELECT COUNT(*) AS Total
    FROM mst_product m
    INNER JOIN mst_prod_category c    ON c.Prd_CateId    = m.Cate_Id
    INNER JOIN mst_prod_subcategory s ON s.Prd_SubCateId = m.SubCate_Id
    WHERE (pCat_Id    = 0 OR m.Cate_Id    = pCat_Id)
      AND (pSub_Cat   = 0 OR m.SubCate_Id = pSub_Cat)
      AND (COALESCE(pProd_Code, '') = ''
           OR m.Prod_Code LIKE CONCAT(pProd_Code, '%')
           OR m.Prod_ShortNm LIKE CONCAT('%', pProd_Code, '%'));
END$$
DELIMITER ;
