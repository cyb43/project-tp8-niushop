
ALTER TABLE `shop_discount_goods` CHANGE COLUMN `rate` `rate` DECIMAL (10, 2) NOT NULL DEFAULT 0.00 COMMENT '折扣';

ALTER TABLE `shop_order_refund_log` CHANGE COLUMN `main_type` `main_type` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '操作人类型';

ALTER TABLE `shop_order_refund_log` CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT 0 COMMENT '退款状态';

ALTER TABLE `shop_order_refund_log` CHANGE COLUMN `content` `content` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '日志内容';

ALTER TABLE `shop_order_refund_log` CHANGE COLUMN `create_time` `create_time` INT NOT NULL DEFAULT 0 COMMENT '创建时间';

ALTER TABLE `shop_order_refund` ADD COLUMN `delete_time` INT(11) NOT NULL DEFAULT 0 COMMENT '是否删除(针对后台)';

ALTER TABLE `shop_order_log` CHANGE COLUMN `main_type` `main_type` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '操作人类型';

ALTER TABLE `shop_order_log` CHANGE COLUMN `content` `content` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '日志内容';

ALTER TABLE `shop_order_goods` ADD COLUMN `delete_time` INT(11) NOT NULL DEFAULT 0 COMMENT '是否删除(针对后台)';

ALTER TABLE `shop_order_discount` ADD COLUMN `member_id` INT(11) NOT NULL DEFAULT 0 COMMENT '会员id';

ALTER TABLE `shop_order_discount` ADD COLUMN `goods_id` INT(11) NOT NULL DEFAULT 0 COMMENT '商品id';

ALTER TABLE `shop_order_discount` ADD COLUMN `sku_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'sku_id';

ALTER TABLE `shop_order_delivery` CHANGE COLUMN `third_delivery` `third_delivery` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '配送方（三方配送）';

ALTER TABLE `shop_order_delivery` CHANGE COLUMN `remark` `remark` VARCHAR (1000) NOT NULL DEFAULT '' COMMENT '备注';

ALTER TABLE `shop_order` ADD COLUMN `relate_order_id` INT(11) NOT NULL DEFAULT 0 COMMENT '关联活动来源订单id';

ALTER TABLE `shop_order` ADD COLUMN `relate_source` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'seckill 秒杀系统 ';

ALTER TABLE `shop_goods` ADD COLUMN `diy_detail_id` INT(11) NOT NULL DEFAULT 0 COMMENT '自定义详情id';

ALTER TABLE `shop_goods` MODIFY `diy_detail_id` INT (11) NOT NULL DEFAULT 0 COMMENT '自定义详情id' AFTER form_id;

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `interface_type` CHAR(20) NOT NULL DEFAULT '' COMMENT '快递公司类型 kdbird:快递鸟 kd100:快递100';

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `exp_type_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '物流公司业务类型名称';

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `temp_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '主模版:快递100用';

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `child_temp_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '子模版:快递100用';

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `back_temp_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '回单模版:快递100用';

ALTER TABLE `shop_delivery_electronic_sheet` ADD COLUMN `interface_data` TEXT DEFAULT NULL COMMENT '接口参数';

ALTER TABLE `shop_delivery_company` CHANGE COLUMN `express_no` `express_no` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '快递鸟:物流公司编号(用于物流跟踪)';

ALTER TABLE `shop_delivery_company` CHANGE COLUMN `express_no_electronic_sheet` `express_no_electronic_sheet` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '快递鸟:物流公司编号(用于电子面单)';

ALTER TABLE `shop_delivery_company` ADD COLUMN `kd100_express_no` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '快递100:物流公司编号(用于物流跟踪)';

ALTER TABLE `shop_delivery_company` ADD COLUMN `kd100_express_no_electronic_sheet` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '快递100:物流公司编号(用于电子面单)';

ALTER TABLE `shop_point_exchange` MODIFY COLUMN `title` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '副标题';

ALTER TABLE `shop_coupon_send_records` MODIFY COLUMN `send_num` INT NOT NULL DEFAULT 0 COMMENT '每位会员发放数量';
