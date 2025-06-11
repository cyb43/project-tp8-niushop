
DROP TABLE IF EXISTS `shop_discount`;
CREATE TABLE `shop_discount`
(
    `discount_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活动id',
    `name`        VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '活动名称',
    `remark`      TEXT           NOT NULL COMMENT '活动说明',
    `start_time`  INT(11)            NOT NULL DEFAULT 0 COMMENT '活动开始时间',
    `end_time`    INT(11)            NOT NULL DEFAULT 0 COMMENT '活动结束时间',
    `status`      VARCHAR(50)    NOT NULL DEFAULT '' COMMENT '活动状态',
    `create_time` INT(11)            NOT NULL DEFAULT 0 COMMENT '添加时间',
    `update_time` INT(11)            NOT NULL DEFAULT 0 COMMENT '修改时间',
    `order_money` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '活动累计金额',
    `order_num`   INT(11)            NOT NULL DEFAULT 0 COMMENT '活动累计订单数',
    `member_num`  INT(11)            NOT NULL DEFAULT 0 COMMENT '活动参与会员数',
    `success_num` INT(11)            NOT NULL DEFAULT 0 COMMENT '活动成功参与会员数',
    PRIMARY KEY (`discount_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='限时折扣表';


DROP TABLE IF EXISTS `shop_discount_goods`;
CREATE TABLE `shop_discount_goods`
(
    `discount_goods_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '活动商品id',
    `discount_id`       INT(11)            NOT NULL DEFAULT 0 COMMENT '活动id',
    `goods_id`          INT(11)            NOT NULL DEFAULT 0 COMMENT '商品id',
    `sku_id`            INT(11)            NOT NULL DEFAULT 0 COMMENT '商品规格id',
    `status`            VARCHAR(50)    NOT NULL DEFAULT '' COMMENT '商品状态',
    `type`              VARCHAR(255)   NOT NULL DEFAULT '' COMMENT '折扣类型',
    `rate`              DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣',
    `reduce_money`      DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '减钱',
    `discount_price`    DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '活动商品价格（展示，搜索）',
    `order_money`       DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '活动累计金额',
    `order_num`         INT(11)            NOT NULL DEFAULT 0 COMMENT '活动累计订单数',
    `member_num`        INT(11)            NOT NULL DEFAULT 0 COMMENT '活动参与会员数',
    `success_num`       INT(11)            NOT NULL DEFAULT 0 COMMENT '活动成功参与会员数',
    `is_enabled`        INT(11)            NOT NULL DEFAULT 1 COMMENT '是否参与活动',
    PRIMARY KEY (`discount_goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='限时折扣商品表';


DROP TABLE IF EXISTS `shop_coupon_send_records`;
CREATE TABLE `shop_coupon_send_records`
(
    `id`             INT(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
    `coupon_id`      INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优惠券id',
    `send_num`       INT(11) NOT NULL COMMENT '每位会员发放数量',
    `range_type`     VARCHAR(20)  NOT NULL DEFAULT '' COMMENT '发券范围',
    `range_param`    TEXT                  DEFAULT NULL COMMENT '范围对应参数',
    `success_num`    INT(11) NOT NULL DEFAULT 0 COMMENT '发放成功数',
    `status`         VARCHAR(255) NOT NULL DEFAULT '' COMMENT '状态 wait-待发送 process-发送中 finish-结束',
    `member_num`     INT(11) NOT NULL DEFAULT 0 COMMENT '发放会员数',
    `end_time`       INT(11) NOT NULL DEFAULT 0 COMMENT '发放结束时间',
    `admin_uid`      INT(11) NOT NULL DEFAULT 0 COMMENT '操作人id',
    `admin_username` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '操作人名称',
    `create_time`    INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time`    INT(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci COMMENT='优惠券发券记录表';

ALTER TABLE `shop_store` CHANGE COLUMN `trade_time` `trade_time` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '营业时间(文本展示使用)';

ALTER TABLE `shop_store` ADD COLUMN `time_week` TEXT DEFAULT NULL COMMENT '自定义的营业时间["0","1","2","3","4","5","6"]周日-周六';

ALTER TABLE `shop_store` ADD COLUMN `trade_time_json` TEXT DEFAULT NULL COMMENT '营业时间';

ALTER TABLE `shop_store` ADD COLUMN `time_interval` INT(11) NOT NULL DEFAULT 0 COMMENT '时段设置（分钟）';

ALTER TABLE `shop_store` MODIFY `create_time` INT (11) NOT NULL DEFAULT 0 COMMENT '添加时间' AFTER `time_interval`;

ALTER TABLE `shop_store` MODIFY `update_time` INT (11) NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `create_time`;

ALTER TABLE `shop_order` CHANGE COLUMN `delivery_time` `delivery_time` INT NOT NULL DEFAULT 0 COMMENT '订单发货时间/自提订单自提时间';

ALTER TABLE `shop_order` ADD COLUMN `buyer_ask_delivery_time` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '购买人要求的配送/发货/自提时间（文本）';

ALTER TABLE `shop_order` MODIFY `buyer_ask_delivery_time` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '购买人要求的配送/发货/自提时间（文本）' AFTER `delivery_time`;

ALTER TABLE `shop_point_exchange_order` MODIFY COLUMN `order_money` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT '订单金额';

ALTER TABLE `shop_order_batch_delivery` CHANGE COLUMN type type VARCHAR(255) NOT NULL DEFAULT '' COMMENT '操作类型 批量发货  批量打单 ....';

ALTER TABLE `shop_order_batch_delivery` MODIFY COLUMN create_time INT NOT NULL DEFAULT 0 COMMENT '创建时间';

ALTER TABLE `shop_goods_evaluate` MODIFY COLUMN content VARCHAR(3000) NOT NULL DEFAULT '' COMMENT '评价内容';

ALTER TABLE `shop_delivery_local_delivery` COMMENT = '自提点表';

ALTER TABLE `shop_delivery_company` COMMENT = '站点快递表';
