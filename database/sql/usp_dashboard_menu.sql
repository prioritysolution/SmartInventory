-- Dashboard was stored as Menu_Id = 0, which PHP/jQuery treat as empty
-- so it never appeared in User Group "Select Role".

UPDATE mst_user_permission
   SET Menu_Id = 10
 WHERE Menu_Id = 0;

UPDATE mst_menus
   SET Menu_Id = 10,
       Menu_Name = 'Dashboard',
       Icon = 'fa-solid fa-house',
       Route = 'user-dashboard',
       Is_Active = 1
 WHERE Menu_Name = 'Dashboard'
   AND SubMenu_Id IS NULL
   AND Menu_Id = 0;

INSERT INTO mst_menus (Menu_Id, Menu_Name, SubMenu_Id, SubMenu_Name, Icon, Route, Is_Active)
SELECT 10, 'Dashboard', NULL, NULL, 'fa-solid fa-house', 'user-dashboard', 1
  FROM DUAL
 WHERE NOT EXISTS (
           SELECT 1 FROM mst_menus
            WHERE Menu_Name = 'Dashboard' AND SubMenu_Id IS NULL
       );

DROP PROCEDURE IF EXISTS USP_GET_ALL_MENUE;
DELIMITER $$
CREATE PROCEDURE USP_GET_ALL_MENUE()
BEGIN
    SELECT
        Menu_Id AS Parraint_Id,
        Menu_Name AS Parraint_Name,
        SubMenu_Id AS Child_Id,
        SubMenu_Name AS Child_Name
    FROM mst_menus
    WHERE Is_Active
    ORDER BY CASE WHEN Route = 'user-dashboard' OR Menu_Name = 'Dashboard' THEN 0 ELSE 1 END,
             Menu_Id,
             IFNULL(SubMenu_Id, 0);
END$$
DELIMITER ;
