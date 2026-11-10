-- Deploy on each organisation schema (e.g. smartinventory_bccsl)

DROP PROCEDURE IF EXISTS USP_GET_PARTY_LIST;
DELIMITER $$
CREATE PROCEDURE USP_GET_PARTY_LIST(
    IN pParty_Type TINYINT,
    IN pBranch_Id SMALLINT,
    IN pSearch_Mode TINYINT,
    IN pPage INT,
    IN pPageSize INT,
    IN pSearch VARCHAR(100)
)
BEGIN
    DECLARE vPage BIGINT UNSIGNED;
    DECLARE vPageSize BIGINT UNSIGNED;
    DECLARE vOffset BIGINT UNSIGNED;
    DECLARE vLimit BIGINT UNSIGNED;
    DECLARE vSearch VARCHAR(100);

    SET vPage = GREATEST(COALESCE(pPage, 1), 1);
    SET vPageSize = GREATEST(COALESCE(pPageSize, 25), 0);
    SET vOffset = (vPage - 1) * vPageSize;
    SET vLimit = IF(vPageSize = 0, 18446744073709551615, vPageSize);
    SET vSearch = TRIM(COALESCE(pSearch, ''));

    SELECT
        p.Party_Id,
        p.Party_Code,
        p.Party_Name,
        p.Contact_No,
        p.AltContact_No,
        p.EMail,
        p.Contact_Person,
        p.Designation,
        p.Address_Line1,
        p.Address_Line2,
        p.City,
        p.State,
        p.Pan_No,
        p.GstIn,
        p.State_Cd,
        p.Start_Date,
        p.Credit_Limit,
        p.Opening_Bal,
        p.Cust_Agent_Id,
        p.Status_Cd,
        p.Village_Id,
        p.Ps_Id,
        p.Post_Id,
        p.Pin_Id,
        p.Dist_Id,
        v.Village_Name,
        ps.Ps_Name,
        po.Post_Name,
        po.Pin_Code,
        d.Dist_Name
    FROM mst_party p
    LEFT JOIN mst_village v ON v.Village_Id = p.Village_Id
    LEFT JOIN mst_ps ps ON ps.Ps_Id = p.Ps_Id
    LEFT JOIN mst_post po ON po.Post_Id = p.Post_Id
    LEFT JOIN mst_dist d ON d.Dist_Id = p.Dist_Id
    WHERE p.Party_Type = pParty_Type
      AND p.Branch_Id = pBranch_Id
      AND p.Status_Cd = 1
      AND (
            vSearch = ''
         OR p.Party_Code LIKE CONCAT('%', vSearch, '%')
         OR p.Party_Name LIKE CONCAT('%', vSearch, '%')
         OR p.Contact_No LIKE CONCAT('%', vSearch, '%')
         OR p.Address_Line1 LIKE CONCAT('%', vSearch, '%')
      )
    ORDER BY p.Party_Name, p.Party_Id
    LIMIT vLimit OFFSET vOffset;

    SELECT COUNT(*) AS Total
    FROM mst_party p
    WHERE p.Party_Type = pParty_Type
      AND p.Branch_Id = pBranch_Id
      AND p.Status_Cd = 1
      AND (
            vSearch = ''
         OR p.Party_Code LIKE CONCAT('%', vSearch, '%')
         OR p.Party_Name LIKE CONCAT('%', vSearch, '%')
         OR p.Contact_No LIKE CONCAT('%', vSearch, '%')
         OR p.Address_Line1 LIKE CONCAT('%', vSearch, '%')
      );
END$$
DELIMITER ;
