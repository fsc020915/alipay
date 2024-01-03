<?php
namespace Fsc\Alipay\AliPay;

require_once __DIR__.'/AopClient.php';
require_once __DIR__.'/AopCertification.php';
require_once __DIR__.'/request/AlipayTradeQueryRequest.php';
require_once __DIR__.'/AopClient.php';
require_once __DIR__.'/AopCertClient.php';
require_once __DIR__.'/AopCertification.php';
require_once __DIR__.'/AlipayConfig.php';
require_once __DIR__.'/request/AlipayFundTransUniTransferRequest.php';
require_once __DIR__.'/request/AlipayFundTransCommonQueryRequest.php';


//use app\videosf\service\ConfigService;
//use app\videosf\service\FileService;

class Pay
{
    protected $site = [];

    public function config(){
        try {
            $aop = new \AopCertClient();
//            $appCertPath = FileService::getFileUrl(ConfigService::get('alipay_config','appCertPath',''));  // 应用公钥证书路径------必填
//            $alipayCertPath =FileService::getFileUrl(ConfigService::get('alipay_config','alipayCertPath',''));  // 支付宝公钥证书路径------必填
//            $rootCertPath = FileService::getFileUrl(ConfigService::get('alipay_config','rootCertPath',''));  // 根证书路径------必填
//            $aop->appId =ConfigService::get('alipay_config','app_id');//应用ID--------------必填
//            $aop->rsaPrivateKey = ConfigService::get('alipay_config','privateKey');//私钥--------------必填
            $appCertPath = $_SERVER['DOCUMENT_ROOT'] . '/aliPay/appCertPublicKey_2021003184650267.crt';  // 应用公钥证书路径------必填
            $alipayCertPath = $_SERVER['DOCUMENT_ROOT'] . '/aliPay/alipayCertPublicKey_RSA2.crt';  // 支付宝公钥证书路径------必填
            $rootCertPath = $_SERVER['DOCUMENT_ROOT'] . '/aliPay/alipayRootCert.crt';  // 根证书路径------必填
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId ='app_id';//应用ID--------------必填
            $aop->rsaPrivateKey = 'privateKey';//私钥--------------必填
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";

            // 调用getPublicKey从支付宝公钥证书中提取公钥
            $aop->alipayrsaPublicKey = $aop->getPublicKey($alipayCertPath);
            // 是否校验自动下载的支付宝公钥证书，如果开启校验要保证支付宝根证书在有效期内
            $aop->isCheckAlipayPublicCert = true;
            // 调用getCertSN获取证书序列号
            $aop->appCertSN = $aop->getCertSN($appCertPath);
            // 调用getRootCertSN获取支付宝根证书序列号
            $aop->alipayRootCertSN = $aop->getRootCertSN($rootCertPath);
            return $aop;
        }catch (\Exception $e){
            return false;
        }

    }

    /**
     * 支付宝转账
     * @param $info
     * @return array
     * @throws \Exception
     */
    public function payPerson($info = [])
    {
        try {
            $aop = $this->config();
            if (!$aop){
                return ['code'=>0,'msg'=>'请检查支付宝配置信息'];
            }
            $request = new \AlipayFundTransUniTransferRequest();
            $bizcontent = [
                'out_biz_no' => $info['number'], // 订单号------必填
                'trans_amount' => $info['price'],   // 提现实际金额------必填
                'product_code' => "TRANS_ACCOUNT_NO_PWD", //转账为:TRANS_ACCOUNT_NO_PWD
                'biz_scene' => 'DIRECT_TRANSFER', //单笔无密转账到支付宝:DIRECT_TRANSFER
                'payee_info' => [
                    'identity' => $info['phone'], // 收款人帐户------必填
                    'identity_type' => 'ALIPAY_LOGON_ID', //支付宝登录id:ALIPAY_LOGON_ID------必填
                    'name' => $info['name'], // 收款人姓名------必填
                ],
                'remark' => $info['remark']??'提现', // 转帐备注
            ];
            $request->setBizContent(json_encode($bizcontent));
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode;
            if (!empty($resultCode->code) && $resultCode->code == 10000) {
                return ['code'=>10000,'msg'=>'success'];
            } else {
                return ['code'=>$resultCode->code,'msg'=>$resultCode->sub_msg];
            }
        }catch (\Exception $e){
            return ['code'=>0,'msg'=>'请检查支付宝配置信息'];
        }

    }

    /**
     * 转账记录查询
     * @param $info
     * @return array
     * @throws \Exception
     */
    public function transferQuery($info=[]){
        $aop = $this->config($info['sass_id']);
        $request = new \AlipayFundTransCommonQueryRequest();
        $bizcontent = [
            'out_biz_no' => $info['number'], // 系统订单号------必填
            'product_code'=>'TRANS_ACCOUNT_NO_PWD',
            'biz_scene'=>'DIRECT_TRANSFER'
//        'pay_fund_order_id'=>'20231019020070011500880099245957'
        ];
        $request->setBizContent(json_encode($bizcontent));
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode;
        if (!empty($resultCode->code) && $resultCode->code == 10000) {
            return ['code'=>10000,'msg'=>'success'];
        } else {
            return ['code'=>$resultCode->code,'msg'=>$resultCode->sub_msg];
        }
    }

}