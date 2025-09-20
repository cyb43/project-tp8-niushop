<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

return [
    //系统常用
    'SUCCESS' => '操作成功',
    'EDIT_SUCCESS' => '编辑成功',
    'DELETE_SUCCESS' => '删除成功',
    'MODIFY_SUCCESS' => '更新成功',
    'VERIFY_SUCCESS' => '核销成功',
    'FAIL' => '操作失败',
    'SAVE_FAIL' => '保存失败',
    'EDIT_FAIL' => '修改失败',
    'DELETE_FAIL' => '删除失败',
    'MODIFY_FAIL' => '更新失败',
    'ADD_FAIL' => '添加失败',
    'ADD_SUCCESS' => '添加成功',
    'UPLOAD_FAIL' => '上传失败',
    'ATTACHMENT_DELETE_FAIL' => '附件删除失败',
    'DATA_NOT_EXIST' => '数据不存在',
    'DOWNLOAD_FAIL' => '下载失败',
    'SET_SUCCESS' => '设置成功',
    'AGREEMENT_TYPE_NOT_EXIST' => '协议类型不存在',
    'FIELD_NOT_FOUND' => '找不到要修改的字段',
    'REFRESH_SUCCESS' => '刷新成功',
    'CAPTCHA_ERROR' => '验证码有误',
    'ADDON_INSTALL_SUCCESS' => '插件安装成功',
    'ADDON_UNINSTALL_SUCCESS' => '插件卸载成功',
    'DATA_GET_FAIL' => '数据获取失败',
    'SERVER_CROSS_REQUEST_FAIL' => '服务器跨域请求异常',
    'ADDON_INSTALL_NOT_EXIST' => '未找到插件安装任务',
    'ADDON_INSTALL_EXECUTED' => '插件安装任务已执行',
    'ADDON_INSTALLING' => '插件安装中',
    'INSTALL_CHECK_NOT_PASS' => '安装校验未通过',
    'ADMIN_INDEX_VIEW_PATH_NOT_EXIST' => '当前首页路径不存在',
    'ADDON_SQL_FAIL' => '插件sql执行失败',
    'ADDON_DIR_FAIL' => '插件文件操作失败',
    'LAYOUT_NOT_EXIST' => '该布局不存在',
    'ZIP_FILE_NOT_FOUND' => '找不到可用的压缩文件',
    'ZIP_ARCHIVE_NOT_INSTALL' => 'ZipArchive扩展未安装',
    'DOWNLOAD_SUCCESS' => '下载成功',
    'ADDON_INSTALL_FAIL' => '插件安装失败',
    'ADMIN_DIR_NOT_EXIST' => '未找到admin源码所在目录, <a style="text-decoration: underline;" href="https://www.kancloud.cn/niucloud/niucloud-admin-develop/3213544" target="blank">点击查看相关手册</a>',
    'WEB_DIR_NOT_EXIST' => '未找到web源码所在目录, <a style="text-decoration: underline;" href="https://www.kancloud.cn/niucloud/niucloud-admin-develop/3213544" target="blank">点击查看相关手册</a>',
    'UNIAPP_DIR_NOT_EXIST' => '未找到uni-app源码所在目录, <a style="text-decoration: underline;" href="https://www.kancloud.cn/niucloud/niucloud-admin-develop/3213544" target="blank">点击查看相关手册</a>',
    'OPEN_BASEDIR_ERROR' => '请关闭防跨站攻击, 具体操作方法<a style="text-decoration: underline;" href="https://www.kancloud.cn/niucloud/niucloud-admin-develop/3213542" target="blank">点击查看相关手册</a>',
    'ADDON_DOWNLOAD_VERSION_EMPTY' => '该插件还没有发布过版本',
    'ADDON_IS_NOT_EXIST' => '插件不存在',
    'ADDON_KEY_IS_EXIST' => '已存在相同插件标识的应用',
    'ADDON_IS_INSTALLED_NOT_ALLOW_DEL' => '已安装的插件不允许删除',
    'ADDON_ZIP_ERROR' => '插件压缩失败',
    'PHP_SCRIPT_RUNNING_OUT_OF_MEMORY' => 'PHP脚本运行内存不足, 具体操作方法<a style="text-decoration: underline;" href="https://www.kancloud.cn/niushop/niushop_v6/3248604" target="blank">点击查看相关手册</a>',
    'BEFORE_UPGRADING_NEED_UPGRADE_FRAMEWORK' => '升级插件前需要先升级框架',
    'UPGRADE_RECORD_NOT_EXIST' => '升级记录不存在',
    'UPGRADE_BACKUP_CODE_NOT_FOUND' => '未找到备份的源码文件',
    'UPGRADE_BACKUP_SQL_NOT_FOUND' => '未找到备份的数据库文件',
    'NOT_EXIST_UPGRADE_CONTENT' => '没有获取到可以升级的内容',
    'CLOUD_BUILD_AUTH_CODE_NOT_FOUND' => '请先填写授权码',
    'TASK_CYCLE_ERROR' => '任务周期填写错误',
    //登录注册重置账号....

    'LOGIN_SUCCESS' => '登录成功',
    'MUST_LOGIN' => '请登录',
    'LOGIN_EXPIRE' => '登录过期,请重新登录',
    'LOGIN_STATE_ERROR' => '登录状态有误,请重新登录',
    'USER_LOCK' => '账号被锁定',
    'USER_ERROR' => '账号或密码错误',
    'LOGOUT' => '登陆退出',
    'OLD_PASSWORD_ERROR' => '原始密码不正确',
    'MOBILE_LOGIN_UNOPENED' => '手机号登录注册未开启',
    'APP_TYPE_NOT_EXIST' => '无效的登录端口',
    "SUPER_ADMIN_NOT_ALLOW_DEL" => "超级管理员不允许删除",

    //用户组权限

    'NO_PERMISSION' => '您没有访问权限',

    //插件安装相关
    'REPEAT_INSTALL' => '当前插件已安装,不能重复安装',
    'NOT_UNINSTALL' => '当前插件未安装,不能进行卸载操作',
    'ADDON_INFO_FILE_NOT_EXIST' => '未找到插件的info.json文件',

    //菜单管理
    'MENU_NOT_EXIST' => '菜单不存在',
    'MENU_NOT_ALLOW_DELETE' => '存在子级菜单的目录或菜单不允许删除',

    //用户管理
    'USER_NOT_EXIST' => '用户不存在',
    'ADMIN_NOT_ALLOW_EDIT_ROLE' => '超级管理员不允许改动权限',
    'USERNAME_REPEAT' => '账号重复',

    //角色管理
    'USER_ROLE_NOT_EXIST' => '角色不存在',
    'USER_ROLE_NOT_ALLOW_DELETE' => '存在管理员使用当前角色,不允许删除',

    //素材管理
    'ATTACHMENT_GROUP_NOT_EXIST' => '附件组不存在',
    'ATTACHMENT_GROUP_NOT_ALLOW_DELETE' => '当前分组,不允许删除',
    'ATTACHMENT_NOE_EXIST' => '附件不存在',
    'ATTACHMENT_GROUP_HAS_IMAGE' => '附件组中存在图片不允许删除',
    'OSS_TYPE_NOT_EXIST' => '云存储类型不存在',
    'URL_FILE_NOT_EXIST' => '获取不到网址指向的文件',
    'PLEACE_SELECT_IMAGE' => '请选择要删除的图片',
    'UPLOAD_TYPE_ERROR' => '不是有效的上传类型',
    'OSS_FILE_URL_NOT_EXIST' => '远程资源文件地址不能为空',
    'BASE_IMAGE_FILE_NOT_EXIST' => 'base图片资源不能为空',
    'UPLOAD_TYPE_NOT_SUPPORT' => '不支持的上传类型',
    'FILE_ERROR' => '无效的资源',
    'UPLOAD_STORAGE_TYPE_ALL_CLOSE' => '至少要有一个启用的存储方式',
    'STORAGE_NOT_HAS_HTTP_OR_HTTPS' => '空间域名请补全http://或https://',


    //消息管理
    'NOTICE_TYPE_NOT_EXIST' => '消息类型不存在',
    'SMS_TYPE_NOT_EXIST' => '短信类型不存在',
    'SMS_DRIVER_NOT_EXIST' => '短信驱动不存在',
    'NO_SMS_DRIVER_OPEN' => '没有启用的短信',
    'SMS_DRIVER_NOT_OPEN' => '短信没有启用',
    'WECHAT_TEMPLATE_NOTICE_NOT_OPEN' => '微信模板消息没有启用',
    'WEAPP_TEMPLATE_NOTICE_NOT_OPEN' => '微信小程序订阅没有启用',
    'SMS_TYPE_NOT_OPEN' => '没有启用的短信方式',
    'NOTICE_TEMPLATE_NOT_EXIST' => '消息模板不存在',
    'WECHAT_TEMPLATE_NEED_NO' => '微信消息模板缺少模板编号',
    'NOTICE_NOT_OPEN_SMS' => '当前消息未开启短信发送',
    'NOTICE_SMS_EMPTY' => '手机号为空',
    'NOTICE_SMS_NOT_OPEN' => '短信未启用',
    'NOTICE_TEMPLATE_IS_NOT_EXIST' => '消息不存在',

    'WEB_ADV_POSITION_NOT_EXIST' => '广告位不存在',

    //会员相关
    'MOBILE_IS_EXIST' => '当前手机号已绑定账号',
    'ACCOUNT_INSUFFICIENT' => '账户余额不足',
    'ACCOUNT_OR_PASSWORD_ERROR' => '账号或密码错误',
    'MEMBER_LOCK' => '账号被锁定',
    'MEMBER_NOT_EXIST' => '账号不存在',
    'MEMBER_OPENID_EXIST' => 'openid已存在',
    'MEMBER_LOGOUT' => '账号退出',
    'MEMBER_TYPE_NOT_EXIST' => '账户类型不存在',
    'MEMBER_IS_EXIST' => '账号已存在',
    'MEMBER_NO_IS_EXIST' => '会员编号已存在',
    'MEMBER_NO_CREATE_ERROR' => '会员编号创建失败',
    'REG_CHANNEL_NOT_EXIST' => '无效的注册渠道',
    'MEMBER_USERNAME_LOGIN_NOT_OPEN' => '未开启账号登录注册',
    'AUTH_LOGIN_NOT_OPEN' => '未开启第三方登录注册',
    'MOBILE_NEEDED' => '手机号必须填写',
    'MOBILE_CAPTCHA_ERROR' => '手机验证码有误',
    'MEMBER_IS_BIND_AUTH' => '当前账号已绑定授权',
    'MEMBER_MOBILE_CAPTCHA_ERROR' => '无效的短信验证码',
    'AUTH_LOGIN_TAG_NOT_EXIST' => '第三方授权标识不能为空',
    'PASSWORD_RESET_SUCCESS' => '密码重置成功',
    'MOBILE_NOT_BIND_MEMBER' => '当前填写的手机号没有绑定此账号',
    'MOBILE_NOT_EXIST_MEMBER' => '当前填写的手机号不存在账号',
    'MOBILE_IS_BIND_MEMBER' => '当前账号已绑定手机号',
    'MOBILE_NOT_CHANGE' => '绑定的手机号与原手机号一致',
    'QRCODE_EXPIRE' => '登录二维码失效',
    'PASSWORD_REQUIRE' => '密码不能为空',
    'LEVEL_NOT_ALLOWED_DELETE' => '该等级下存在会员不允许删除',
    'MEMBER_LEVEL_MAX' => '最多只能有十个等级',

    // 地址相关
    'ADDRESS_ANALYSIS_ERROR' => '地址解析异常',

    //会员提现
    'CASHOUT_NOT_OPEN' => '会员提现业务未开启',
    'CASHOUT_TYPE_NOT_OPEN' => '当前会员提现方式未启用',
    'CASHOUT_LOG_NOT_EXIST' => '提现记录不存在',
    'CASHOUT_AUDITED' => '当前提现记录已被审核',
    'TRANSFER_TYPE_NOT_EXIST' => '存在未定义的转账方式',
    'CASHOUT_IS_REFUSE' => '提现被拒绝,返还零钱',
    'MEMBER_APPLY_CASHOUT' => '会员申请提现,扣除零钱',
    'CASHOUT_MONEY_TOO_LITTLE' => '会员提现金额不能小于最低提现金额',
    'CASHOUT_STATUS_NOT_IN_WAIT_TRANSFER' => '当前提现申请未处于待转账状态',
    'CASHOUT_STATUS_NOT_IN_CANCEL' => '只有进行中的提现才可以取消',
    'CASHOUT_STATUS_NOT_IN_WAIT_AUDIT' => '当前提现申请未处于待审核状态',
    'MEMBER_CASHOUT_TRANSFER' => '会员提现转账',
    'CASH_OUT_ACCOUNT_NOT_EXIST' => '提现账户不存在',
    'CASH_OUT_WECHAT_ACCOUNT_NOT_ALLOW_ADMIN' => '在转账到微信零钱的提现场景下,提现操作应该由用户在客户端发起',

    'CASH_OUT_ACCOUNT_NOT_FOUND_VALUE' => '转账到微信零钱缺少参数',

    //DIY
    'PAGE_NOT_EXIST' => '页面不存在',
    'DIY_THEME_COLOR_NOT_EXIST' => '主题配色不存在',
    'DIY_THEME_DEFAULT_COLOR_CAN_NOT_DELETE' => '系统默认配色不能删除',
    'DIY_THEME_SELECTED_CAN_NOT_DELETE' => '主题配色已选中不能删除',

    //海报
    'POSTER_NOT_EXIST' => '海报不存在',
    'POSTER_IN_USE_NOT_ALLOW_MODIFY' => '海报使用中禁止修改状态',
    'POSTER_CREATE_ERROR' => '海报组件未配置完善,请联系管理员',

    //万能表单
    'DIY_FORM_NOT_EXIST' => '表单不存在',
    'DIY_FORM_NOT_OPEN' => '该表单已关闭',
    'DIY_FORM_EXCEEDING_LIMIT' => '已达提交次数上限',
    'ON_STATUS_PROHIBIT_DELETE' => '启用状态下禁止删除',
    'DIY_FORM_TYPE_NOT_EXIST' => '表单类型不存在',

    //渠道相关  占用 4******
    //微信
    'WECHAT_NOT_EXIST' => '微信公众号未配置完善',
    'KEYWORDS_NOT_EXIST' => '关键词回复不存在',
    'WECHAT_EMPOWER_NOT_EXIST' => '微信授权信息不存在',
    'SCAN_SUCCESS' => '扫码成功',
    'WECHAT_SNAPSHOUTUSER' => '返回的是虚拟账号',
    //小程序
    'WEAPP_NOT_EXIST' => '微信小程序未配置完善',
    'WEAPP_EMPOWER_NOT_EXIST' => '微信小程序授信信息不存在',
    'WEAPP_EMPOWER_TEL_NOT_EXIST' => '微信小程序授信手机号不存在',
    'CURR_SITE_IS_NOT_OPEN_SSL' => '微信小程序请求地址只支持https请先配置ssl',
    'WECHAT_MINI_PROGRAM_CODE_GENERATION_FAILED' => '微信小程序码生成失败',

    //支付相关(todo  注意:7段不共享)
    'ALIPAY_TRANSACTION_NO_NOT_EXIST' => '无效的支付交易号',
    'PAYMENT_METHOD_NOT_SUPPORT' => '您选择到支付方式不受业务支持',
    'WECHAT_TRANSFER_CONFIG_NOT_EXIST' => '微信零钱打款设置未配置完善',
    'PAYMENT_LOCK' => '支付中,请稍后再试',
    'PAY_SUCCESS' => '当前支付已完成',
    'PAY_IS_REMOVE' => '当前支付已取消',
    'PAYMENT_METHOD_NOT_EXIST' => '你选择的支付方式未启用',
    'PAYMENT_METHOD_NOT_SCENE' => '你选择的支付方式不适用于当前场景',
    'TREAT_PAYMENT_IS_OPEN' => '只有待支付时可以关闭',
    'TRANFER_STATUS_NOT_IN_WAIT_TANSFER' => '当前转账未处于待转账状态',
    'TRANSFER_ORDER_INVALID' => '无效的转账单据',
    'TRANSFER_IS_FAILING' => '单据正在撤销中,请等待片刻或稍后再试',
    'TRANFER_CONFIG_ERROR' => '参数有误或未开通转账业务',
    'IS_PAY_REMOVE_NOT_RESETTING' => '已支付和已取消的单据不可以重置',
    'DOCUMENT_IS_PAY_REMOVE' => '当前单据已支付或已取消',
    'PATMENT_METHOD_INVALID' => '无效的支付方式',
    'CHANNEL_MARK_INVALID' => '无效的渠道标识',
    'TEMPLATE_NOT_EXIST' => '模板不存在',
    'IS_EXIST_TEMPLATE_NOT_MODIFY' => '已存在的支付模板不支持修改支付类型',
    'ONLY_PAYING_CAN_PAY' => '只有待支付的订单可以支付',
    'VOUCHER_NOT_EMPTY' => '支付单据不能为空',
    'ONLY_PAYING_CAN_AUDIT' => '只有待支付的订单才可以操作',
    'ONLY_OFFLINEPAY_CAN_AUDIT' => '只有线下支付的单据才可以审核',
    'TRADE_NOT_EXIST' => '支付单据不存在',
    'PAY_NOT_FOUND_TRADE' => '找不到可支付的交易',

    'MERCHANT_TRANSFER_SCENARIOS_THAT_DO_NOT_EXIST' => '不存在的商户转账场景',
    //退款相关
    'REFUND_NOT_EXIST' => '退款单据不存在',
    //订单相关  8***
    'ORDER_NOT_EXIST' => '订单不存在',
    'ORDER_CLOSED' => '订单已关闭',
    'DOCUMENT_IS_PAID' => '单据已支付',
    'REFUND_IS_CHANGE' => '退款状态已发生变化',
    'TRANFER_IS_CHANGE' => '转账状态已发生变化',

    // 退款相关
    'NOT_ALLOW_APPLY_REFUND' => '该订单不允许退款',
    'ITEM_REFUND_NOT_EXIST' => '退款单不存在',
    'REFUND_STATUS_ABNORMAL' => '退款单状态异常',
    'NO_REFUNDABLE_AMOUNT' => '会员账户金额为0不允许进行退款',
    'REFUND_HAD_APPLIED' => '订单已申请退款',
    'ORDER_UNPAID_NOT_ALLOW_APPLY_REFUND' => '订单尚未支付不能进行退款',

    //会员套餐
    'RECHARGE_NOT_EXIST' => '充值套餐不存在',

    // 缓存相关
    'CLEAR_MYSQL_CACHE_SUCCESS' => '数据表缓存清除成功',
    'CACHE_CLEAR_SUCCESS' => '缓存清除成功',

    //任务队列相关
    'JOB_NOT_EXISTS' => '任务类不存在',
    'JOB_CREATE_FAIL' => '任务创建失败',
    'SCHEDULE_NOT_EXISTS' => '人物不存在',
    //小程序版本
    'APPLET_VERSION_NOT_EXISTS' => '小程序版本不存在',
    'APPLET_VERSION_PACKAGE_NOT_EXIST' => '小程序版本包不存在',
    //验证码
    'CAPTCHA_INVALID' => '无效的验证码',

    // 授权相关
    'AUTH_NOT_EXISTS' => '未获取到授权信息',

    // 云服务
    'CLOUD_WEAPP_COMPILE_NOT_EXIST' => '未找到微信小程序编译包',
    'WEAPP_APPID_EMPTY' => '还没有配置微信小程序',
    'UPLOAD_KEY_EMPTY' => '还没有配置微信小程序代码上传秘钥',
    'UPLOAD_KEY_NOT_EXIST' => '未找到微信小程序代码上传秘钥',
    'NEED_TO_AUTHORIZE_FIRST' => '使用云服务需先进行授权',
    'WEAPP_UPLOADING' => '小程序有正在上传的版本，请等待上一版本上传完毕后再进行操作',
    'CLOUD_BUILD_TASK_EXIST' => '已有正在执行中的编译任务',
    'CONNECT_FAIL' => '连接失败',

    //核销相关
    'VERIFY_TYPE_ERROR' => '核销类型错误',
    'VERIFY_CODE_EXPIRED' => '当前核销码已核销或已失效',
    'VERIFIER_NOT_EXIST' => '核销员不存在',
    'VERIFIER_EXIST' => '核销员已存在',
    'VERIFIER_NOT_AUTH' => '该核销员没有权限',

    //签到相关
    'SIGN_NOT_USE' => '签到未开启',
    'SIGN_NOT_SET' => '签到参数未配置',
    'SIGNED_TODAY' => '今日已签到',
    'CONTINUE_SIGN' => '连签',
    'DAYS' => '天！',
    'SIGN_SUCCESS' => '签到成功',
    'SIGN_AWARD' => '签到奖励',
    'GET_AWARD' => '恭喜您获得以下奖励',
    'WILL_GET_AWARD' => '您将获得以下奖励',
    'SIGN_PERIOD_CANNOT_EMPTY' => '签到周期不能为空',
    'SIGN_PERIOD_BETWEEN_2_365_DAYS' => '签到周期为2-365天',
    'CONTINUE_SIGN_BETWEEN_2_365_DAYS' => '连签天数为2-365天',
    'CONTINUE_SIGN_CANNOT_GREATER_THAN_SIGN_PERIOD' => '连签天数不能大于签到周期',

    //导出相关
    'EXPORT_SUCCESS' => '导出成功',
    'EXPORT_FAIL' => '导出失败',
    'EXPORT_NO_DATA' => '暂无可导出数据',
    'DIRECTORY' => '目录',
    'WAS_NOT_CREATED' => '创建失败',

    'PRINTER_NOT_EXIST' => '打印机不存在',
    /*******************************************牛云短信 start ********************************************************/
    'NIU_SMS_ENABLE_FAILED' => '需登录账号并配置签名后才能启用牛云短信',
    'ACCOUNT_ERROR_RELOGIN' => '牛云短信账号异常,请重新登录账号',
    'ACCOUNT_BIND_MOBILE_ERROR' => '手机号错误',
    'TEMPLATE_NOT_SMS_CONTENT' => '当前模版未配置短信内容',
    'TEMPLATE_IS_PASS' => '审核通过的模版不允许修改',
    'TEMPLATE_NOT_REPORT' => '短信模版暂未报备',
    'URL_NOT_FOUND' => '未配置远程服务地址，请在ENV中配置{NIU_SHOP_PREFIX}',
    'SYSTEM_IS_ERROR' => '远程服务器异常，请联系售后人员',

    'TEMPLATE_ERROR' => '短信模版ID错误或审核未通过',
    'TEMPLATE_USE_ERROR' => '短信模版参数不一致需修改/重新报备',
    /*******************************************牛云短信 end ********************************************************/
];
