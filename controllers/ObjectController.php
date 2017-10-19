<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2017/4/10
 * Time: 9:27
 * Email: 1183@mapgoo.net
 */
class ObjectController extends InitController
{
    /**
     * 批量获取目标实告警
     *
     * 存在问题：统计模块和最终数据有所出入，遍历匹配速度很慢
     */
    public function getHoldAlarmListByObjIdAlarmTypeIdAction(){
		$verifyArr = array( 'holdID', 'alarmTypeID' , 'pageSize', 'pageNum' );
		foreach($verifyArr as $k=>$v){
			$val = empty($this->request[$v])?0:(int)$this->request[$v];
			if(empty($val)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		if (empty($this->request['objIDs']) || !is_array($this->request['objIDs'])) {
            $param['holdID'] = (int)$this->request['holdID'];
            $param['statisticsType'] = 3;  //告警
            $param['pageNum'] =  (int)$this->request['pageNum'];
            $param['pageSize'] = (int)$this->request['pageSize'];
            $objIdData = $this->domian->getObjsByHoldId($param);
            if ($objIdData['status']->errorcode != 0) {
                return $this->jsonResponse($objIdData['status']->errorcode, $objIdData['status']->errormsg);
            } else {
                $this->request['objIDs'] =  $objIdData['data'];
            }
		}
		$DevStatus = $this->domian->getDevStatusDec($this->request['objIDs'] , 3 , $this->request['alarmTypeID']);
		$Tracks = $this->domian->getTracks($this->request['objIDs']);
		$i = 0;
		$lists = $alarm = array();
		if(!empty($Tracks['data'])){
			$lists = $DevStatus['Lists'];
			unset($DevStatus);
			foreach($lists as $key => $val){
				foreach($Tracks['data'] as $k=>$v){
					if($key == $v->objId){
						foreach($val as $alarmtype){
							$alarm[$i]['Lat'] = isset($v->gpsDataEx->gpsData->point->lat) ? number_format($v->gpsDataEx->gpsData->point->lat / 1000000.0, 6) : '';
							$alarm[$i]['Lon'] = isset($v->gpsDataEx->gpsData->point->lng) ? number_format($v->gpsDataEx->gpsData->point->lng / 1000000.0, 6) : '';
							$alarm[$i]['Direct'] = isset($v->gpsDataEx->gpsData->direction) ? $v->gpsDataEx->gpsData->direction : 0;
							$alarm[$i]['Speed'] = isset($v->gpsDataEx->gpsData->speed) ? $v->gpsDataEx->gpsData->speed : 0;
							$alarm[$i]['GPSTime'] = isset($v->gpsDataEx->gpsData->gpsTime) && $v->gpsDataEx->gpsData->gpsTime > 0 ? date('Y-m-d H:i:s', $v->gpsDataEx->gpsData->gpsTime) : '';
							$alarm[$i]['RcvTime'] = isset($v->gpsDataEx->gpsData->rcvTime) && $v->gpsDataEx->gpsData->rcvTime > 0 ? date('Y-m-d H:i:s', $v->gpsDataEx->gpsData->rcvTime) : '';
							$alarm[$i]['AlarmID'] = 0; //告警记录ID，无用字段，赋值为1
                            $alarm[$i]['AlarmTypeID'] = $alarmtype; //告警类型ID
							$alarm[$i]['ObjectID'] = $key; //告警类型ID
							$i++;
						}
					}
				}
			}
		}
		unset($Tracks);
		unset($lists);
		$return['total'] = $i > 0 ? $i - 1 : 0;
		$return['recordList'] = $alarm;
		return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
		
	}
	public function getHoldObjIdListByStatisticsTypeAction()
    {	
		$verifyArr = array("holdID","statisticsType","pageNum","pageSize");
		foreach($verifyArr as $k=>$v){
			$val = empty($this->request[$v])?0:(int)$this->request[$v];
			if(empty($val)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$params = array('holdID' => $this->request['holdID'], 'pageSize' => $this->request['pageSize'], 'pageNum' => $this->request['pageNum'], 'statisticsType' => $this->request['statisticsType']);
		$iceData = $this->domian->getObjsByHoldId($params);
		if ($iceData['status']->errorcode != 0) {
			return $this->jsonResponse($iceData['status']->errorcode, $iceData['status']->errormsg);
		}else {
			$return = array();
			$return['total'] = $iceData['total'];
			$return['objIDList'] = $iceData['data'];
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
		}
    }
}
