# alipay
支付宝单笔转账
# 公共参数
appCertPath：应用公钥证书路径
alipayCertPath：支付宝公钥证书路径
rootCertPath：根证书路径
appId：应用id
rsaPrivateKey：私钥

# 转账传参
out_biz_no：订单号
trans_amount：订单总金额，单位为元，不支持千位分隔符，精确到小数点后两位，取值范围[0.1,100000000]
identity_type：参与方的标识类型，支付宝的会员ID: ALIPAY_USER_ID，默认为支付宝登录号: ALIPAY_LOGON_ID
identity：参与方的标识 ID，当 identity_type=ALIPAY_USER_ID 时，填写支付宝用户 UID，当 identity_type=ALIPAY_LOGON_ID 时，填写支付宝登录号（默认为ALIPAY_LOGON_ID）
name：参与方真实姓名
remark：备注

# 转账业务单据查询接口
out_biz_no：商户转账唯一订单号,本参数和order_id（支付宝转账单据号）、pay_fund_order_id（支付宝支付资金流水号）三者不能同时为空。 当三者同时传入时，将用pay_fund_order_id（支付宝支付资金流水号）进行查询，忽略其余两者； 当本参数和支付宝转账单据号同时提供时，将用支付宝转账单据号进行查询，忽略本参数
order_id：支付宝转账单据号,本参数和out_biz_no（商户转账唯一订单号）、pay_fund_order_id（支付宝支付资金流水号）三者不能同时为空。 当三者同时传入时，将用pay_fund_order_id（支付宝支付资金流水号）进行查询，忽略其余两者； 当本参数和pay_fund_order_id（支付宝支付资金流水号）同时提供时，将用支付宝支付资金流水号进行查询，忽略本参数； 当本参数和out_biz_no（商户转账唯一订单号）同时提供时，将用本参数进行查询，忽略商户转账唯一订单号
pay_fund_order_id：支付宝支付资金流水号,本参数和支付宝转账单据号、商户转账唯一订单号三者不能同时为空。 当本参数和out_biz_no（商户转账唯一订单号）、order_id（支付宝转账单据号）同时提供时，将用本参数进行查询，忽略其余两者； 当本参数和order_id（支付宝转账单据号）同时提供时，将用本参数进行查询，忽略支付宝转账单据号； 当本参数和out_biz_no（商户转账唯一订单号）同时提供时，将用本参数进行查询，忽略商户转账唯一订单号
product_code：销售产品码  STD_RED_PACKET：现金红包 TRANS_ACCOUNT_NO_PWD：单笔无密转账到支付宝账户 TRANS_BANKCARD_NO_PWD：单笔无密转账到银行卡
biz_scene：业务场景 PERSONAL_PAY：C2C现金红包-发红包； PERSONAL_COLLECTION：C2C现金红包-领红包； REFUND：C2C现金红包-红包退回； DIRECT_TRANSFER：B2C现金红包、单笔无密转账
