<?php

namespace eeTools\eeWeChat\Message;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 */
use eeTools\common\eeNet;
use eeTools\eeWeChat\eeWeChatBased;
use eeTools\common\eeDebug;
use eeTools\common\eeDate;
use eeTools\common\eeFile;
use common\models\Client;
use common\models\SiteElement;
use common\components\CommonConst;
use eeTools\eeWeChat\KF\wcKF;
use yii\helpers\VarDumper;
use common\models\ClientKF;

/**
 * WeChat function class
 * 
 * @author boylee
 * 
 * @property Client $client
 *        
 */
class wcReplyMsg extends eeWeChatBased {
    
    public $msgTo = ''; //out wechat account
    public $msgFrom = ''; //sender wechat account
    public $msgCreateTime = '';
    public $msgType = '';
    public $msgEvent = '';
    public $msgEventKey = '';
    public $msgContent = '';
    public $msgId = '';
    
    public $client;
    
    public function init(){
        parent::init();
        
        
        //load xml from post when init
        $this->loadMsg();
    }
    public function loadMsg() {
        //get xml
        $tmpXML = file_get_contents('php://input');
        eeFile::saveFile($tmpXML);
        /**
         * <xml>
         *  <URL><![CDATA[http://t965.yiilib.com/frontend/web/index.php/site/w-c]]></URL>
         *  <ToUserName><![CDATA[gh_dae694fa0718]]></ToUserName>
         *  <FromUserName><![CDATA[ot2EGwWc2e42aJq4vtqptSS1dtKc]]></FromUserName>
         *  <CreateTime>1348831860</CreateTime>
         *  <MsgType><![CDATA[text]]></MsgType>
         *  <Content><![CDATA[1122]]></Content>
         *  <MsgId>1234567890123456</MsgId>
         * </xml>
         */
//         eeFile::saveFile($tmpXML);
        if (empty($tmpXML)) {
            return '';
        }//xml content not get.
        
        //xml to array
        $tmpXMLArr = $this->FromXml($tmpXML);
//         eeDebug::varDump($tmpXMLArr);
        
        if (!empty($tmpXMLArr)) {
            
            $this->msgTo = @$tmpXMLArr['ToUserName']; 
            $this->msgFrom = @$tmpXMLArr['FromUserName']; 
            $this->msgCreateTime = @$tmpXMLArr['CreateTime']; 
            $this->msgType = @$tmpXMLArr['MsgType']; 
            $this->msgEvent = @$tmpXMLArr['Event']; 
            $this->msgEventKey = @$tmpXMLArr['EventKey']; 
            $this->msgContent = @$tmpXMLArr['Content']; 
            $this->msgId = @$tmpXMLArr['MsgId']; 
        }
        
        
        $this->loadClient();
        
    }
    
    /**
     * load client by msgFrom
     */
    public function loadClient() {
        if (empty($this->client)) {
            $this->client = Client::find()->where(['c_openId'=>$this->msgFrom])->one();
        }
        
        if (empty($this->client)) {
            //create new one if empty
            $this->client = new Client();
            $this->client->c_openId = $this->msgFrom;
            $this->client->scenario = 'create-from-msg';
            $this->client->save();
        }
    }
    
    /**
     * make a xml reply format
     */
    public function autoReply() {
        /**
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
</xml>
         */
        
        if (empty($this->client)) {
            return false;
        }
        
        //1.check last reply date from client table
        $interval = \Yii::$app->params['wechat.msg.autoReplyIntervalSec']+1;
        if (!empty($this->client->c_lastAutoReplyDate)) {
            $interval = time() - strtotime($this->client->c_lastAutoReplyDate);
        }

        if ($interval > \Yii::$app->params['wechat.msg.autoReplyIntervalSec']) {
            //2. send reply msg if validate.
            $msg = new wcServiceMsg();
            
            $se = SiteElement::find()->where(['se_code'=>CommonConst::SECODE_AUTOREPLYMSG])->one();
            if (empty($se)) {
                return false;
            }
            $msg->sendServiceMsg($this->client->c_openId, $se->se_v4);
           
            
            
            //3. update $client.aut data
            $this->client->c_lastAutoReplyDate = date('Y-m-d H:i:s');
            $this->client->save();
            
        }
        
        
        
        
    }
    
    
    public function reply() {
        //check follow type
        if ($this->msgType == 'event') {
            if (in_array($this->msgEvent, ['unsubscribe'])) {
                exit;
            }//do nothing
            
            if (in_array($this->msgEvent, ['subscribe'])) {
                //return follow welcome msg
                $this->replyFollowWelcome();
                exit;
            }//subscribe and unsubscribe
            
            
            if (in_array($this->msgEvent, ['CLICK'])) {
                //return waiting welcome msg.
                if ($this->msgEventKey == 'SPLASH_Welcome') {
                    $this->replyWelcomeWaiting();
                    exit;
                }
            }//subscribe and unsubscribe
            
            
            //only reply two type events
            exit;
        }
        
        
        
        //do auto reply check
//         eeFile::saveFile(15);
        $this->autoReply();
//         eeFile::saveFile(16);
        
        
        //1. get online kf list
        $kf = new wcKF();
        $onlineKF = $kf->listKF(true);
//         eeFile::saveFile(VarDumper::dumpAsString($onlineKF));
        
        if (empty($onlineKF)) {
            $replayMSG = 'not KF online plz try again later.';
            $se = SiteElement::find()->where(['se_code'=>CommonConst::SECODE_NOKFONLINE])->one();
            if (!empty($se)) {
                $replayMSG = $se->se_v4;
            }
            
            
            $msg = new wcServiceMsg();
            $msg->sendServiceMsg($this->msgFrom, $replayMSG);
            return '';
        }//skip for no KF online
        
        //2. check if client already have one last talk KF and if the KF online
        $lastKFOnline = false;
        $kfAccount = $this->client->c_staffWeChat; 
        if (!empty($kfAccount)) {
            //already have last talk kf, check if online
            foreach ($onlineKF as $oneKF) {
                if ($oneKF->kf_account == $kfAccount) {
                    $lastKFOnline = true;
                    break;
                }//found and stop
            }
        }
        
        if (!$lastKFOnline) {
            //last KF not avaiable, use random new KF
            $max = count($onlineKF) - 1;
//             eeFile::saveFile(VarDumper::dumpAsString($max));
            $randomKey = mt_rand(0, $max);
            
            $kfAccount = $onlineKF[$randomKey]->kf_account;
            
            //update new kf into db
            $this->client->c_staffWeChat = $kfAccount;
            $this->client->scenario = 'update-from-msg-reply';
            $this->client->save();
        }
        
        //send to found kf.
        $xmlArr = [];
        $xmlArr['ToUserName'] = $this->msgFrom;
        $xmlArr['FromUserName'] = $this->msgTo;
        $xmlArr['CreateTime'] = time();
        $xmlArr['MsgType'] = 'transfer_customer_service';
        
        $kfArr['KfAccount'] = $kfAccount;
        $xmlArr['TransInfo'] = $kfArr;
        
        //save client kf relation in db, for chat log
        $ckf = new ClientKF();
        $ckf->ckf_client_id = $this->client->c_id;
        $ckf->ckf_kfAccount = $kfAccount;
        $ckf->scenario = 'create';
        $ckf->save();
        
        echo $this->generateXML($xmlArr);
        exit;
    }
    
    
    public function replyFollowWelcome() {
        /**
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
</xml>
         */
        
        if (empty($this->client)) {
            return false;
        }
        
        //1. send follow welcome msg
        $msg = new wcServiceMsg();
    
        $se = SiteElement::find()->where(['se_code'=>CommonConst::SECODE_WELCOMEMSG])->one();
        if (empty($se)) {
            return false;
        }
        $msg->sendServiceMsg($this->client->c_openId, $se->se_v4);
        
    }
    
    public function replyWelcomeWaiting() {
        /**
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>12345678</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[你好]]></Content>
</xml>
         */
//         $tmpTime = time();
//         $toUser = $this->client->c_openId;
//         $xml = "
// <xml>
// <ToUserName><![CDATA[$toUser]]></ToUserName>
// <FromUserName><![CDATA[fromUser]]></FromUserName>
// <CreateTime>$tmpTime</CreateTime>
// <MsgType><![CDATA[text]]></MsgType>
// <Content><![CDATA[你好]]></Content>
// </xml>
// ";
        
        $xmlArr = [];
        $xmlArr['ToUserName'] = $this->msgFrom;
        $xmlArr['FromUserName'] = $this->msgTo;
        $xmlArr['CreateTime'] = time();
        $xmlArr['MsgType'] = 'text';
        $xmlArr['Content'] = '即将上线，内容更新中！';
        
        echo $this->generateXML($xmlArr);
        exit;
        if (empty($this->client)) {
            return false;
        }
        
        //1. send follow welcome msg
        $msg = new wcServiceMsg();
        $msg->sendServiceMsg($this->client->c_openId, '即将上线，内容更新中！');
        
    }
    
}