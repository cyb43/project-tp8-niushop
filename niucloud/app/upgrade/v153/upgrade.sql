
ALTER TABLE `web_friendly_link` MODIFY COLUMN link_title VARCHAR (100) NOT NULL DEFAULT '' COMMENT '标题';

ALTER TABLE `web_friendly_link` MODIFY COLUMN link_url VARCHAR (100) NOT NULL DEFAULT '' COMMENT '链接';

ALTER TABLE `web_friendly_link` MODIFY COLUMN link_pic VARCHAR (100) NOT NULL DEFAULT '' COMMENT '图片';

ALTER TABLE `pay_transfer` CHANGE COLUMN `extra` `extra` VARCHAR (1000) NOT NULL DEFAULT '' COMMENT '扩展信息';

DROP TABLE IF EXISTS `sys_backup_records`;
CREATE TABLE `sys_backup_records`
(
    `id`            INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `version`       VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备份版本号',
    `backup_key`    VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备份标识',
    `content`       VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备份内容',
    `status`        VARCHAR(255) NOT NULL DEFAULT '' COMMENT '状态',
    `fail_reason`   LONGTEXT              DEFAULT NULL COMMENT '失败原因',
    `remark`        VARCHAR(500) NOT NULL DEFAULT '' COMMENT '备注',
    `create_time`   INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
    `complete_time` INT(11) NOT NULL DEFAULT 0 COMMENT '完成时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='备份记录表';

DROP TABLE IF EXISTS `sys_upgrade_records`;
CREATE TABLE `sys_upgrade_records`
(
    `id`              INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `upgrade_key`     VARCHAR(255) NOT NULL DEFAULT '' COMMENT '升级标识',
    `app_key`         VARCHAR(255) NOT NULL DEFAULT '' COMMENT '插件标识',
    `name`            VARCHAR(255) NOT NULL DEFAULT '' COMMENT '升级名称',
    `content`         LONGTEXT DEFAULT NULL COMMENT '升级内容',
    `prev_version`    VARCHAR(255) NOT NULL DEFAULT '' COMMENT '前一版本',
    `current_version` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '当前版本',
    `status`          VARCHAR(255) NOT NULL DEFAULT '' COMMENT '状态',
    `fail_reason`     LONGTEXT              DEFAULT NULL COMMENT '失败原因',
    `create_time`     INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
    `complete_time`   INT(11) NOT NULL DEFAULT 0 COMMENT '完成时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='升级记录表';

ALTER TABLE `pay` MODIFY COLUMN `money` DECIMAL (10, 2) NOT NULL DEFAULT 0.00 COMMENT '支付金额';

ALTER TABLE `jobs` MODIFY COLUMN `queue` VARCHAR (255) NOT NULL DEFAULT '';

ALTER TABLE `diy_form_submit_config` MODIFY COLUMN `tips_type` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '提示内容类型，default：默认提示，diy：自定义提示';

ALTER TABLE `diy_theme` CHANGE COLUMN `title` `title` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标题';
