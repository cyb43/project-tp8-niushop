
ALTER TABLE `sys_menu` ADD COLUMN `parent_select_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '上级key';

ALTER TABLE `member` CHANGE COLUMN `remark` `remark` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '备注';

ALTER TABLE `diy_page` CHANGE COLUMN `template` `template` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '页面模板名称';

ALTER TABLE `sys_user_log` CHANGE COLUMN `url` `url` VARCHAR(300) NOT NULL DEFAULT '' COMMENT '链接';

ALTER TABLE `sys_user_log` ADD COLUMN `operation` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '操作描述';

ALTER TABLE `sys_user_log` MODIFY `operation` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '操作描述' AFTER `username`;

ALTER TABLE `sys_user_log` CHANGE COLUMN `username` `username` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '管理员姓名';

ALTER TABLE `sys_backup_records` CHANGE COLUMN `content` `content` TEXT DEFAULT NULL COMMENT '备份内容';
