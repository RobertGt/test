<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2017/9/27
 * Time: 16:46
 * Email: 1183@mapgoo.net
 */
class CmdController extends InitController
{
	
    public function sendAction(){
		$verifyArr = array('objID', 'content');
		foreach($verifyArr as $k=>$v){
			if(empty($v)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$objID = (int)$this->request['objID'];
		$message = array(
			'DownID' => 11, 
			'ObjectID' => $objID, 
			'sendContent' => !empty($this->request['content']) ?  $this->request['content'] : "",
			'CMDTypeID'  => (int)$this->request['cmdTypeID'], 
			'SubmitTime' => date("Y-m-d H:i:s"), 
			'sendUserID' => (int)$this->request['sendUserID'], 
			'sim' => !empty($this->request['sim']) ?  $this->request['sim'] : "",
			'remark' => !empty($this->request['remark']) ?  $this->request['remark'] : "",
			'sendFlag' => 0,
			'sendsource' => 153,
			'TransType' => 0,
			'DownInfoType' => 1502,
		);
		$res = $this->domian->GetObjectinfoById($objID);
		if($res['status'] === 0){
			$router = $this->domian->getObjectRouter($objID);
			if($router['status'] === 0){
				$ip = !empty($router['data']->IASIP) ? $router['data']->IASIP : "";
				$port = !empty($router['data']->IASPort) ? $router['data']->IASPort : "";
				$IAS = $this->domian->getIAShandle($ip , $port);
				if(!$IAS){
					$message['sendFlag'] = 252;
					$this->SaveCommands($message);
					return $this->jsonResponse(Api_Define::$ROUTE_ISNULL['status'], Api_Define::$ROUTE_ISNULL['info']);
				}
				$cmdInfo['objId'] = $objID;
				$cmdInfo['imei'] = !empty($res['data']->IMEI) ? $res['data']->IMEI : "";
				$cmdInfo['factory'] = !empty($res['data']->Factory) ? $res['data']->Factory : "";
				$cmdInfo['brand'] = !empty($res['data']->Brand) ? $res['data']->Brand : "";
				$cmdInfo['protocol'] = !empty($res['data']->Protocol) ? $res['data']->Protocol : "";
				$cmdInfo['content'] = $this->request['content'];
				$pack = $this->domian->packs($cmdInfo);
				if($pack['status'] === 0){
					$string = "";
					foreach($pack['data'] as $v){
						$string .= chr($v);
					}
					$cmd['SequenceNo'] = time();
					$cmd['Imei'] = $cmdInfo['imei'];
					$cmd['Content'] = $string;
					$md5String = 'SequenceNo:'. $cmd['SequenceNo'] .',Imei:' . $cmd['Imei'] . ',rYRYU54QUSGF562e3dwc3eT,Content:' . $cmd['Content'];
					$cmd['Digest'] = md5($md5String);
					$send = $this->domian->pushCmd($IAS , $cmd);
					if($send === 0){
						$message['sendFlag'] = 0;
						$res = $this->SaveCommands($message);
						return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'] , $res);
					}else{
						$message['sendFlag'] = 252;
						$res = $this->SaveCommands($message);
						return $this->jsonResponse(Api_Define::$RETURN_FALL['status'], Api_Define::$RETURN_FALL['info'] , $res);
					}
				}else{
					$message['sendFlag'] = 252;
					$res = $this->SaveCommands($message);
					return $this->jsonResponse(Api_Define::$CODING_FALL['status'], Api_Define::$CODING_FALL['info'] , $res);
				}
			}else{
				if(empty($res['data']->IsWireless)){
					$message['sendFlag'] = 255;
				}else{
					$message['sendFlag'] = 254;
				}
				$res = $this->SaveCommands($message);
				return $this->jsonResponse(Api_Define::$RETURN_SEND_IN['status'], Api_Define::$RETURN_SEND_IN['info'] , $res);
			}
		}else{
			return $this->jsonResponse(Api_Define::$RETURN_NOT_FOUND['status'], Api_Define::$RETURN_NOT_FOUND['info']);
		}
    }
	private function saveCommands($message = array()){
		if(empty($message))return -10;
		$i = 0;
		//重试3次
		while($i < 3){
			$status = $this->domian->SaveCommands($message);
			if($status === 0)break;
			$i++;
		}
		return $status;
	}
}