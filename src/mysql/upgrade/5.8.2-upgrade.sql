-- cleanup the lists
DELETE FROM `list_lst` WHERE lst_ID IN
(
SELECT tmp1.lst_ID FROM
(
SELECT lst_ID FROM `list_lst`
WHERE lst_OptionName ='TEACHER' and `lst_ID` NOT IN
(
SELECT tmp.grp_RoleListID FROM
(
SELECT `grp_RoleListID` 
FROM `group_grp` 
WHERE `grp_Type`=4
) as tmp
)
) as tmp1
);


DELETE FROM `list_lst` WHERE lst_ID IN
(
SELECT tmp1.lst_ID FROM
(
SELECT lst_ID FROM `list_lst`
WHERE lst_OptionName ='STUDENT' and `lst_ID` NOT IN
(
SELECT tmp.grp_RoleListID FROM
(
SELECT `grp_RoleListID` 
FROM `group_grp` 
WHERE `grp_Type`=4
) as tmp
)
) as tmp1
);
