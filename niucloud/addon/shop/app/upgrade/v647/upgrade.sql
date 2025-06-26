
ALTER TABLE `shop_order_delivery` ADD COLUMN third_delivery VARCHAR(50) NOT NULL DEFAULT '' COMMENT '配送方（三方配送）';

ALTER TABLE `shop_order_delivery` MODIFY `remark` VARCHAR(1000) NOT NULL DEFAULT '' AFTER `third_delivery`;

ALTER TABLE `shop_order_delivery` MODIFY `create_time` INT(11) NOT NULL DEFAULT 0 COMMENT '创建时间' AFTER `remark`;

ALTER TABLE `shop_goods` CHANGE COLUMN `attr_id` `attr_ids` TEXT DEFAULT NULL COMMENT '商品参数id，支持多个';

ALTER TABLE `shop_delivery_local_delivery` CHANGE COLUMN `delivery_time` `delivery_time` TEXT DEFAULT NULL COMMENT '配送时间段';
