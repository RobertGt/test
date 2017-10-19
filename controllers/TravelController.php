<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2017/4/6
 * Time: 16:46
 * Email: 1183@mapgoo.net
 */
class TravelController extends InitController
{
	//获取最后一笔行程信息
    public function getObjCurrentTravelAction(){
		$objID = $this->request['objID'];
        if (empty($objID)) {
            return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . 'objID不能为空');
        }
        $datas = $this->domian->getTravel((int)$objID);
		$result = array();
		//if(!empty($datas)){
			$result['Complete'] = empty($datas->isCompleted)?0:1;
			$result['Remark'] = !empty($datas->remark)?$datas->remark:"";
			$result['StartLon'] = !empty($datas->startPos->point->lng)?number_format($datas->startPos->point->lng/ 1000000.0, 6):0;
			$result['StartLat'] = !empty($datas->startPos->point->lat)?number_format($datas->startPos->point->lat/ 1000000.0, 6):0;
			$result['StartTime'] =  !empty($datas->startPos->gpsTime) && $datas->startPos->gpsTime>0?date('Y-m-d H:i:s',$datas->startPos->gpsTime):"";
			$result['StartMileage'] = !empty($datas->startPos->mileage)?round($datas->startPos->mileage/1000,3):0;
			if ($result['Complete'] == 1){
				$result['StopLon'] = !empty($datas->stopPos->point->lng)?number_format($datas->stopPos->point->lng/ 1000000.0, 6):0;
				$result['StopLat'] = !empty($datas->stopPos->point->lat)?number_format($datas->stopPos->point->lat/ 1000000.0, 6):0;
				$result['StopTime'] =  !empty($datas->stopPos->gpsTime) && $datas->stopPos->gpsTime>0?date('Y-m-d H:i:s',$datas->stopPos->gpsTime):"";
			}else{
				$result['StopLon'] = 0;
				$result['StopLat'] = 0;
				$result['StopTime'] = 0;
			}
			$result['BDCount'] = count($datas->seqFaultCode);
			$result['BDCode'] = implode('，',$datas->seqFaultCode);
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$result);
		//}else{
		//	return $this->jsonResponse(Api_Define::$DATA_RESULT_EMPTY['status'], Api_Define::$DATA_RESULT_EMPTY['info']);
		//}
    }
}