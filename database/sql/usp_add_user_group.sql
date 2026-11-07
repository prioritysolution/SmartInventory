DROP PROCEDURE IF EXISTS USP_ADD_USER_GROUP;
DELIMITER $$
CREATE PROCEDURE USP_ADD_USER_GROUP(
    pGrp_Id     Int,
    pGrp_Name   Varchar(50),
    pGrp_Desc   Varchar(150),
    pIs_Admin   TINYINT,
    pMode       Smallint
)
BEGIN
    Declare pExists_Count   Int;
    Declare pError_No       Int;
    Declare pError_Message  Varchar(100);
    Declare pGrp_Nid        Int;

    Set pError_No = 0;
    Set pError_Message = '';

    If(pMode=1) Then
        Set pExists_Count = (Select Count(*) From mst_user_group Where UGrp_Name=pGrp_Name);

        If(pExists_Count>0) Then
            Set pError_No = -1;
            Set pError_Message = 'Same User Group Already Exists !!';
        End If;

        If(pError_No=0) Then
            Insert Into mst_user_group (UGrp_Name,UGrp_Description,Is_Admin)
            Values (pGrp_Name,pGrp_Desc,pIs_Admin);
            Set pGrp_Nid = @@identity;

            Insert Into mst_user_permission (UGrp_Id,Menu_Id,Sub_men_Id,User_View,User_Add,User_Edit,User_Del,User_Print,Is_Active)
            Select pGrp_Nid, Parraint_Id, Child_Id, 1, 0, 0, 0, 0, 1 From tempmenue;
        End If;
    End If;

    If(pMode=2) Then
        Set pError_No = 0;
        Update mst_user_group
           Set UGrp_Name=pGrp_Name,
               UGrp_Description=pGrp_Desc,
               Is_Admin=pIs_Admin
         Where UGrp_Id=pGrp_Id;

        -- Always replace permissions, including when every role is unchecked.
        Delete From mst_user_permission Where UGrp_Id=pGrp_Id;
        Insert Into mst_user_permission (UGrp_Id,Menu_Id,Sub_men_Id,User_View,User_Add,User_Edit,User_Del,User_Print,Is_Active)
        Select pGrp_Id, Parraint_Id, Child_Id, 1, 0, 0, 0, 0, 1 From tempmenue;
    End If;

    Select pError_No As Error_No, pError_Message As Message;
    Drop temporary Table If Exists tempmenue;
END$$
DELIMITER ;
