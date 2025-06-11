<?php
return [
    'pages' => <<<EOT
        // PAGE_BEGIN
        {
            "root": "addon/shop",
            "pages": [
                // *********************************** 商城 ***********************************
                {
                    "path": "pages/index",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.index%"
                    }
                },
                {
                    "path": "pages/coupon/list",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.coupon.list%"
                    }
                },
                {
                    "path": "pages/coupon/detail",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.coupon.detail%"
                    }
                },
                {
                    "path": "pages/discount/list",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.discount.list%"
                    }
                },
                {
                    "path": "pages/evaluate/list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.evaluate.list%"
                    }
                },
                {
                    "path": "pages/evaluate/order_evaluate",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.evaluate.order_evaluate%"
                    }
                },
                {
                    "path": "pages/evaluate/order_evaluate_view",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.evaluate.order_evaluate_view%"
                    }
                },
                {
                    "path": "pages/member/my_coupon",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.member.my_coupon%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/member/index",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.member.index%"
                    }
                },
                {
                    "path": "pages/goods/search",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.search%"
                    }
                },
                {
                    "path": "pages/goods/list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.list%"
                    }
                },
                {
                    "path": "pages/goods/rank",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.goods.rank%"
                    }
                },
                {
                    "path": "pages/newcomer/list",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.newcomer.list%"
                    }
                },
                {
                    "path": "pages/goods/detail",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.detail%",
                        "navigationStyle": "custom"
                    }
                },
                {
                    "path": "pages/goods/cart",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.cart%"
                    }
                },
                {
                    "path": "pages/goods/collect",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.collect%"
                    },
					"needLogin": true
                },
                {
                    "path": "pages/goods/browse",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.browse%"
                    },
					"needLogin": true
                },
                {
                    "path": "pages/goods/category",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.goods.category%"
                    }
                },
                {
                    "path": "pages/order/detail",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.order.detail%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/order/list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.order.list%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/order/payment",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.order.payment%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/refund/apply",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.refund.apply%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/refund/edit_apply",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.refund.edit_apply%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/refund/list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.refund.list%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/refund/detail",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.refund.detail%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/refund/log",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.refund.log%"
                    },
                    "needLogin": true
                },
                {
                    "path": "pages/point/index",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.point.index%"
                    }
                },
                {
                    "path": "pages/point/list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.point.list%"
                    }
                },
                {
                    "path": "pages/point/detail",
                    "style": {
                        "navigationStyle": "custom",
                        "navigationBarTitleText": "%shop.pages.point.detail%"
                    }
                },
                {
                    "path": "pages/point/payment",
                    "style": {
                        // #ifndef H5
                        "navigationStyle": "custom",
                        // #endif
                        "navigationBarTitleText": "%shop.pages.point.payment%"
                    }
                },
                {
                    "path": "pages/point/order_list",
                    "style": {
                        "navigationBarTitleText": "%shop.pages.point.order_list%"
                    }
                }
            ]
        },
        // PAGE_END
EOT
];