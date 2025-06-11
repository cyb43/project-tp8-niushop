
DROP TABLE IF EXISTS `niu_sms_template`;
CREATE TABLE `niu_sms_template` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `sms_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '短信服务商类型 niuyun-牛云 aliyun-阿里云 tencent-腾讯',
  `username` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '子账号名称',
  `template_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模版key',
  `template_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模版id',
  `template_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模版类型',
  `template_content` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '模版内容',
  `param_json` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '参数变量',
  `status` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '上下架状态',
  `audit_status` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '报备、审核状态',
  `audit_msg` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '审核结果/拒绝原因',
  `report_info` TEXT DEFAULT NULL COMMENT '报备、审核信息',
  `create_time` INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` INT(11) NOT NULL DEFAULT 0 COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='牛云短信模板表';

ALTER TABLE `member` ADD COLUMN  `id_card` VARCHAR(30) DEFAULT '' COMMENT '身份证号';

ALTER TABLE `member` MODIFY `id_card` VARCHAR(30) DEFAULT '' COMMENT '身份证号' AFTER `birthday`;

ALTER TABLE `member` ADD COLUMN `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注';

ALTER TABLE `member` MODIFY `remark` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '备注' AFTER `location`;

ALTER TABLE `addon` CHANGE COLUMN `key` `key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '插件标识';
