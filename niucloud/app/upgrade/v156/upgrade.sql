
ALTER TABLE `verify` CHANGE COLUMN `verifier_member_id` `verifier_member_id` INT NOT NULL DEFAULT 0 COMMENT '核销员的会员id';

ALTER TABLE `verify` ADD COLUMN `is_admin` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否后台核销0-否1-是';

DROP TABLE IF EXISTS `activity_exchange_code`;
CREATE TABLE `activity_exchange_code`
(
    `id`             INT(11) NOT NULL AUTO_INCREMENT,
    `code`           VARCHAR(255) NOT NULL DEFAULT '' COMMENT '兑换码',
    `status`         VARCHAR(20)  NOT NULL DEFAULT 'normal' COMMENT 'normal-正常  received-被领取',
    `activity_type`  VARCHAR(20)  NOT NULL DEFAULT '' COMMENT '例seckill-秒杀活动',
    `activity_id`    INT(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
    `type`           VARCHAR(20)  NOT NULL DEFAULT '' COMMENT '类型    例seckill_goods-秒杀商品',
    `type_id`        INT(11) NOT NULL DEFAULT 0 COMMENT '类型对应id  秒杀商品id',
    `expire_time`    INT(11) NOT NULL DEFAULT 0 COMMENT '过期时间 0-不过期',
    `member_id`      INT(11) NOT NULL DEFAULT 0 COMMENT '领取会员',
    `received_time`  INT(11) NOT NULL DEFAULT 0 COMMENT '领取时间',
    `order_id`       INT(11) NOT NULL DEFAULT 0 COMMENT '对应订单id',
    `admin_username` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '操作人名称（添加人）',
    `admin_id`       INT(11) NOT NULL DEFAULT 0 COMMENT '操作人id',
    `create_time`    INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='活动兑换码表';

ALTER TABLE `member` CHANGE COLUMN `id_card` `id_card` VARCHAR (30) NOT NULL DEFAULT '' COMMENT '身份证号';

ALTER TABLE `sys_user` CHANGE COLUMN `status` `status` TINYINT(3) NOT NULL DEFAULT 1 COMMENT '后台管理员状态 1有效0无效';

