<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2017/4/6
 * Time: 17:28
 * Email: 1183@mapgoo.net
 */
class StatisticsController extends InitController
{

    /**
     * 报警统计数据动态获取
     */
    public function getHoldObjStatisticsByAlarmTypeGroupAction(){
        if (empty($this->request['holdID'])) {
            return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . 'holdID不能为空');
        }
        $params = array(
            'holdID' => intval($this->request['holdID']),
            'statisticsType' =>3,
            'pageSize' =>100000,
            'pageNum'=>1
        );
		
        $data = $this->domian->getObjsByHoldId($params);
		if ($data['status']->errorcode != 0) {
			return $this->jsonResponse($data['status']->errorcode, $data['status']->errormsg);
		} else {
			$return = $this->domian->getStatsByAlarmTypeGroup($data['data']);
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
        }
    }

	//获取目标统计(所有状态)
    public function getHoldObjStatisticsAction(){
       if (empty($this->request['holdID'])) {
            return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . 'holdID不能为空');
        }
		$data = $this->domian->getStatisticsByHoldId((int)$this->request['holdID']);
		if ($data['status']->errorcode != 0) {
			return $this->jsonResponse($data['status']->errorcode, $data['status']->errormsg);
		} else {
			$data['data'] = json_decode(json_encode($data['data']), true);
			foreach ($data['data']['statistics'] as $k => $v) {
				$data['data'][$k] = $v;
			}
			unset($data['data'] ['statistics']);
			foreach ($data['data'] as $k => $v) {
				$data['data'][$k . 'Count'] = $v;
				unset($data['data'][$k]);

			}
			$data['data']['totalCount'] = $data['data']['offlineCount'] + $data['data']['onlineCount']+$data['data']['invalidCount'];
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$data['data']);
		}
    }
	//获取各省目标统计
    public function getHoldObjCountByStatisticsTypeProvinceGroupAction(){
		if (empty($this->request['holdID'])) {
            return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . 'holdID不能为空');
        }
		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"districtType" => 0 ,
			"districtName" => ''
		);
		$datas = $this->domian->getCountBydistrictName($param);
		if(!empty($datas)){
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$datas);
		}else{
			return $this->jsonResponse(Api_Define::$DATA_RESULT_EMPTY['status'], Api_Define::$DATA_RESULT_EMPTY['info']);
		}
	}
	//获取省辖市目标统计
	public function getHoldObjCountByStatisticsTypeCityOfProviceGroupAction(){
		$verifyArr = array('holdID', 'provinceCode');
		foreach($verifyArr as $k=>$v){
			$value = (int)$this->request[$v];
			if(empty($value)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$province = $this->domian->getRegionByID((int)$this->request['provinceCode']);
		$provinceName = !empty($province['province']) ? $province['province'] : "";
		if(!$provinceName)return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info']);
		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"districtType" => 1,
			"districtName" => $provinceName
		);
		$datas = $this->domian->getCountBydistrictName($param);
		if(!empty($datas)){
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$datas);
		}else{
			return $this->jsonResponse(Api_Define::$DATA_RESULT_EMPTY['status'], Api_Define::$DATA_RESULT_EMPTY['info']);
		}
	}
	//获取市辖区目标统计
	public function getHoldObjCountByStatisticsTypeRegionOfCityGroupAction(){
		$verifyArr = array('holdID', 'cityCode');
		foreach($verifyArr as $k=>$v){
			$value = (int)$this->request[$v];
			if(empty($value)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$city = $this->domian->getRegionByID((int)$this->request['cityCode']);
		$cityName = !empty($city['city']) ? $city['province'] . "-" . $city['city'] : "";
		if(!$cityName)return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info']);
		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"districtType" => 2,
			"districtName" => $cityName,
			"cityName" => $city['city'],
		);
		$datas = $this->domian->getCountBydistrictName($param);
		if(!empty($datas)){
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$datas);
		}else{
			return $this->jsonResponse(Api_Define::$DATA_RESULT_EMPTY['status'], Api_Define::$DATA_RESULT_EMPTY['info']);
		}
	}
	//获取省目标列表
	public function getHoldObjIdListByStatisticsTypeProvinceGroupAction(){
		$verifyArr = array('holdID', 'statisticsType','provinceCode','pageSize','pageNum');
		foreach($verifyArr as $k=>$v){
			$value = (int)$this->request[$v];
			if(empty($value)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$province = $this->domian->getRegionByID((int)$this->request['provinceCode']);
		$provinceName = !empty($province['province']) ? $province['province'] : "";
		if(!$provinceName)return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info']);
		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"statisticsType" => (int)$this->request['statisticsType'],
			"districtName" => $provinceName,
			"districtType" => 0,
			"pageSize" => $this->request['pageSize'],
			"pageNum" => $this->request['pageNum']
		);
		$datas = $this->domian->getObjsBydistrictName($param);
		if ($datas['status']->errorcode != 0) {
			return $this->jsonResponse($datas['status']->errorcode, $datas['status']->errormsg);
		} else {
			$return = array();
			$return['total'] = $datas['total']?$datas['total']:0;
			$return['objIDList'] = !empty($datas['data'])?$datas['data']:array();
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
		}
	}
	//获取省辖市目标列表
	public function getHoldObjIdListByStatisticsTypeCityOfProvinceGroupAction(){
		$verifyArr = array('holdID', 'statisticsType','cityCode','pageSize','pageNum');
		foreach($verifyArr as $k=>$v){
			$value = (int)$this->request[$v];
			if(empty($value)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$city = $this->domian->getRegionByID((int)$this->request['cityCode']);
		$cityName = !empty($city['city']) ? $city['province'] . "-" . $city['city'] : "";
		if(!$cityName)return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info']);

		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"statisticsType" => (int)$this->request['statisticsType'],
			"districtName" => $cityName,
			"districtType" => 1,
			"pageSize" => $this->request['pageSize'],
			"pageNum" => $this->request['pageNum']
		);
		$datas = $this->domian->getObjsBydistrictName($param);
		
		if ($datas['status']->errorcode != 0) {
			return $this->jsonResponse($datas['status']->errorcode, $datas['status']->errormsg);
		} else {
			$return['total'] = $datas['total']?$datas['total']:0;
			$return['objIDList'] = !empty($datas['data'])?$datas['data']:array();
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
		}
	}
	//获取市辖区目标列表
	public function getHoldObjIdListByStatisticsTypeRegionOfCityGroupAction(){
		$verifyArr = array('holdID', 'statisticsType','regionCode','pageSize','pageNum');
		foreach($verifyArr as $k=>$v){
			$value = (int)$this->request[$v];
			if(empty($value)){
				return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info'] . $v .'不能为空');
			}
		}
		$region = $this->domian->getRegionByID((int)$this->request['regionCode']);
		$regionName = !empty($region['region']) ? $region['city'] . "-" . $region['region'] : "";
		if(!$regionName)return $this->jsonResponse(Api_Define::$PARAM_DEFAULT_ERROR['status'], Api_Define::$PARAM_DEFAULT_ERROR['info']);

		$param = array(
			"holdID"       => (int)$this->request['holdID'],
			"statisticsType" => (int)$this->request['statisticsType'],
			"districtName" => $regionName,
			"districtType" => 2,
			"pageSize" => $this->request['pageSize'],
			"pageNum" => $this->request['pageNum']
		);
		$datas = $this->domian->getObjsBydistrictName($param);
		if ($datas['status']->errorcode != 0) {
			return $this->jsonResponse($datas['status']->errorcode, $datas['status']->errormsg);
		} else {
			$return['total'] = $datas['total']?$datas['total']:0;
			$return['objIDList'] = !empty($datas['data'])?$datas['data']:array();
			return $this->jsonResponse(Api_Define::$RETURN_SUCCESS['status'], Api_Define::$RETURN_SUCCESS['info'],$return);
		}
	}
}