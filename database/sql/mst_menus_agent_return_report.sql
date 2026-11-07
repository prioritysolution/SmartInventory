-- Place Agent Return Report immediately before Agent Commission Report
-- (Inventory Reports Menu_Id = 8).

UPDATE mst_menus
   SET SubMenu_Id = 17
 WHERE Menu_Id = 8
   AND Route = 'agent-commission'
   AND SubMenu_Id = 16;

UPDATE mst_user_permission
   SET Sub_men_Id = 17
 WHERE Menu_Id = 8
   AND Sub_men_Id = 16;

INSERT INTO mst_menus (Menu_Id, Menu_Name, SubMenu_Id, SubMenu_Name, Icon, Route, Is_Active)
SELECT 8, 'Inventory Reports', 16, 'Agent Return Report', NULL, 'agent-office-return-report', 1
  FROM DUAL
 WHERE NOT EXISTS (
           SELECT 1 FROM mst_menus
            WHERE Menu_Id = 8 AND Route = 'agent-office-return-report'
       );

INSERT INTO mst_user_permission (UGrp_Id, Menu_Id, Sub_men_Id, User_View, User_Add, User_Edit, User_Del, User_Print, Is_Active)
SELECT p.UGrp_Id, 8, 16, p.User_View, p.User_Add, p.User_Edit, p.User_Del, p.User_Print, p.Is_Active
  FROM mst_user_permission p
 WHERE p.Menu_Id = 8
   AND p.Sub_men_Id = 15
   AND NOT EXISTS (
           SELECT 1 FROM mst_user_permission x
            WHERE x.UGrp_Id = p.UGrp_Id AND x.Menu_Id = 8 AND x.Sub_men_Id = 16
       );

INSERT INTO mst_user_permission (UGrp_Id, Menu_Id, Sub_men_Id, User_View, User_Add, User_Edit, User_Del, User_Print, Is_Active)
SELECT p.UGrp_Id, 8, 17, p.User_View, p.User_Add, p.User_Edit, p.User_Del, p.User_Print, p.Is_Active
  FROM mst_user_permission p
 WHERE p.Menu_Id = 8
   AND p.Sub_men_Id = 15
   AND NOT EXISTS (
           SELECT 1 FROM mst_user_permission x
            WHERE x.UGrp_Id = p.UGrp_Id AND x.Menu_Id = 8 AND x.Sub_men_Id = 17
       );
