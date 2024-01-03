<?php

namespace Fsc\Alipay\AliPay;

require_once __DIR__ . '/AopClient.php';
require_once __DIR__ . '/AopCertification.php';
require_once __DIR__ . '/request/AlipayTradeQueryRequest.php';
require_once __DIR__ . '/AopClient.php';
require_once __DIR__ . '/AopCertClient.php';
require_once __DIR__ . '/AopCertification.php';
require_once __DIR__ . '/AlipayConfig.php';
require_once __DIR__ . '/request/AlipayFundTransUniTransferRequest.php';
require_once __DIR__ . '/request/AlipayFundTransCommonQueryRequest.php';



class Pay
{
    protected string $appCertPath = ''; // 应用公钥证书路径------必填
    protected string $alipayCertPath = ''; // 支付宝公钥证书路径------必填
    protected string $rootCertPath = ''; // 根证书路径------必填
    protected string $gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    protected string $appId = '';
    protected string $rsaPrivateKey = '';

    public function __construct($info)
    {
        $this->appCertPath = $info['appCertPath'];
        $this->alipayCertPath = $info['alipayCertPath'];
        $this->rootCertPath = $info['rootCertPath'];
        $this->appId = $info['appId'];
        $this->rsaPrivateKey = $info['rsaPrivateKey'];
    }

    public function config()
    {
        try {
            $aop = new \AopCertClient();
            $aop->gatewayUrl = $this->gatewayUrl;
            $aop->appId = $this->appId;//应用ID--------------必填
            $aop->rsaPrivateKey = $this->rsaPrivateKey;//私钥--------------必填
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";

            // 调用getPublicKey从支付宝公钥证书中提取公钥
            $aop->alipayrsaPublicKey = $aop->getPublicKey($this->alipayCertPath);
            // 是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $aop->isCheckAlipayPublicCert = true;
            // 调用getCertSN获取证书序列号
            $aop->appCertSN = $aop->getCertSN($this->appCertPath);
            // 调用getRootCertSN获取支付宝根证书序列号
            $aop->alipayRootCertSN = $aop->getRootCertSN($this->rootCertPath);
            return $aop;
        } catch (\Exception $e) {
            return false;
        }

    }

    /**
     * 支付宝转账
     * @param array $info
     * @return array
     */
    public function payPerson(array $info = []): array
    {
        try {
            $aop = $this->config();
            if (!$aop) {
                return ['code' => 0, 'msg' => '请检查支付宝配置信息'];
            }
            $request = new \AlipayFundTransUniTransferRequest();
            $bizcontent = [
                'out_biz_no' => $info['out_biz_no'], // 订单号------必填
                'trans_amount' => $info['trans_amount'],   // 提现实际金额------必填
                'product_code' => "TRANS_ACCOUNT_NO_PWD", //转账为:TRANS_ACCOUNT_NO_PWD
                'biz_scene' => 'DIRECT_TRANSFER', //单笔无密转账到支付宝:DIRECT_TRANSFER
                'payee_info' => [
                    'identity' => $info['identity'], // 收款人帐户------必填
                    'identity_type' => $info['identity_type']?? 'ALIPAY_LOGON_ID', //支付宝登录id:ALIPAY_LOGON_ID------必填
                    'name' => $info['name'], // 收款人姓名------必填
                ],
                'remark' => $info['remark'] ?? '单笔转账', // 转帐备注
            ];
            return $this->handle($request, $bizcontent, $aop);
        } catch (\Exception $e) {
            return ['code' => 0, 'msg' => '请检查支付宝配置信息'];
        }

    }

    /**
     * 转账记录查询
     * @param array $info
     * @return array
     * @throws \Exception
     */
    public function transferQuery(array $info = []): array
    {
        $aop = $this->config();
        $request = new \AlipayFundTransCommonQueryRequest();
        $bizcontent = [
            'out_biz_no' => $info['out_biz_no'], // 商户转账唯一订单号------必填
            'order_id' => $info['order_id'], // 支付宝转账单据号
            'product_code' =>  $info['product_code']??'TRANS_ACCOUNT_NO_PWD', // 销售产品码  STD_RED_PACKET：现金红包 TRANS_ACCOUNT_NO_PWD：单笔无密转账到支付宝账户 TRANS_BANKCARD_NO_PWD：单笔无密转账到银行卡
            'biz_scene' => $info['biz_scene']??'DIRECT_TRANSFER', // 业务场景 PERSONAL_PAY：C2C现金红包-发红包； PERSONAL_COLLECTION：C2C现金红包-领红包； REFUND：C2C现金红包-红包退回； DIRECT_TRANSFER：B2C现金红包、单笔无密转账
            'pay_fund_order_id' => $info['pay_fund_order_id'] // 支付宝支付资金流水号
        ];
        return $this->handle($request, $bizcontent, $aop);

    }


    /**
     * 处理请求
     * @param $request
     * @param $bizcontent
     * @param $aop
     * @return array
     */
    public function handle($request, $bizcontent, $aop)
    {
        $request->setBizContent(json_encode($bizcontent));
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if (!empty($resultCode->code) && $resultCode->code == 10000) {
            return ['code' => 10000, 'msg' => 'success'];
        } else {
            return ['code' => $resultCode->code, 'msg' => $resultCode->sub_msg];
        }
    }

}