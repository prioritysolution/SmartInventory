DROP PROCEDURE IF EXISTS USP_GET_AGENT_LIST;
DELIMITER $$
CREATE PROCEDURE USP_GET_AGENT_LIST(
    IN pBranch_Id INT,
    IN pSearch VARCHAR(100),
    IN pPage INT,
    IN pPageSize INT
)
BEGIN
    DECLARE pOffset INT;
    SET pOffset = (pPage - 1) * pPageSize;

    IF pPageSize = 0 THEN
        SELECT Agent_Id, Agent_Code, Agent_Name, Address, Contact_No,
               Join_Date, Stock_Limit, Credit_Sell_Limit, Branch_Id, Status_Cd,
               Village_Id, Ps_Id, Post_Id, Pin_Id, Dist_Id,
               UDF_GET_OPTION_NAME(1, Status_Cd) AS Status_Name
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND Status_Cd = 1
          AND (
                COALESCE(pSearch, '') = ''
                OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                OR Agent_Code LIKE CONCAT('%', pSearch, '%')
                OR Address LIKE CONCAT('%', pSearch, '%')
                OR Contact_No LIKE CONCAT('%', pSearch, '%')
          )
        ORDER BY Agent_Name;

        SELECT COUNT(*) AS Total
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND Status_Cd = 1
          AND (
                COALESCE(pSearch, '') = ''
                OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                OR Agent_Code LIKE CONCAT('%', pSearch, '%')
                OR Address LIKE CONCAT('%', pSearch, '%')
                OR Contact_No LIKE CONCAT('%', pSearch, '%')
          );
    ELSE
        SELECT Agent_Id, Agent_Code, Agent_Name, Address, Contact_No,
               Join_Date, Stock_Limit, Credit_Sell_Limit, Branch_Id, Status_Cd,
               Village_Id, Ps_Id, Post_Id, Pin_Id, Dist_Id,
               UDF_GET_OPTION_NAME(1, Status_Cd) AS Status_Name
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND (
                COALESCE(pSearch, '') = ''
                OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                OR Agent_Code LIKE CONCAT('%', pSearch, '%')
                OR Address LIKE CONCAT('%', pSearch, '%')
                OR Contact_No LIKE CONCAT('%', pSearch, '%')
          )
        ORDER BY Agent_Id
        LIMIT pPageSize OFFSET pOffset;

        SELECT COUNT(*) AS Total
        FROM mst_agent
        WHERE Branch_Id = pBranch_Id
          AND (
                COALESCE(pSearch, '') = ''
                OR Agent_Name LIKE CONCAT('%', pSearch, '%')
                OR Agent_Code LIKE CONCAT('%', pSearch, '%')
                OR Address LIKE CONCAT('%', pSearch, '%')
                OR Contact_No LIKE CONCAT('%', pSearch, '%')
          );
    END IF;
END$$
DELIMITER ;
