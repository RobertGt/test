<?php

/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 2017/5/19
 * Time: 11:33
 * Email: 1183@mapgoo.net
 */
require_once 'Ice.php';
require_once 'Ice/BuiltinSequences.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/CAP.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/RDP.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/OSS.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/MFS.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/DAP.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/CmdPack.php';
include SWOOLE_PATH. DIRECTORY_SEPARATOR .'library/Ice/AppAgent.php';
class DomianModel
{
    private static $CAPhandle;
	private static $RDPhandle;
	private static $MFShandle;
	private static $OSShandle;
	private static $DAPhandle;
	private static $MRShandle;
	private static $IAShandle = [];
	private static $init_data;
	private static $Region = ['times' => 0 , 'datas' => []];
	private static $RegionCacheTime = 60 * 60 * 24 * 30;
	public function __construct($Config)
    {
        try {
            $Ice_Session = $Config['CAP']['Session'];
            $Ice_MessageSizeMax = $Config['CAP']['MessageSizeMax'];
            self::$init_data = new Ice_InitializationData;
            self::$init_data->properties = Ice_createProperties();
            self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
            $ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
            self::$CAPhandle = CacheProxy_CacheSessionPrxHelper::checkedCast($obj);
            if (!self::$CAPhandle) {
				echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: CAP handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: CAP connect error :".print_r ($ex,true);
			echo "\n";
        }
		try {
			$Ice_Session = $Config['RDP']['Session'];
			$Ice_MessageSizeMax = $Config['RDP']['MessageSizeMax'];
			self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
			$ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
			self::$RDPhandle = RealDataProxy_RealDataSessionPrxHelper::checkedCast($obj);
            if (!self::$RDPhandle) {
                echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: RDP handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: RDP connect error :".print_r ($ex,true);
			echo "\n";
        }

		try {
			$Ice_Session = $Config['OSS']['Session'];
			$Ice_MessageSizeMax = $Config['OSS']['MessageSizeMax'];
			self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
            $ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
			self::$OSShandle = OSS_OSSSessionPrxHelper::checkedCast($obj);
			if (!self::$OSShandle) {
                echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: OSS handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: OSS connect error :".print_r ($ex,true);
			echo "\n";
        }

		try {
			$Ice_Session = $Config['MFS']['Session'];
			$Ice_MessageSizeMax = $Config['MFS']['MessageSizeMax'];
			self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
            $ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
			self::$MFShandle = MFS_MFSSessionPrxHelper::checkedCast($obj);
			if (!self::$MFShandle) {
                echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: MFS handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: MFS connect error :".print_r ($ex,true);
			echo "\n";
        }

		try {
			$Ice_Session = $Config['DAP']['Session'];
			$Ice_MessageSizeMax = $Config['DAP']['MessageSizeMax'];
			self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
            $ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
			self::$DAPhandle = DAPProxy_DAPSessionPrxHelper::checkedCast($obj);
			if (!self::$DAPhandle) {
                echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: DAP handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: DAP connect error :".print_r ($ex,true);
			echo "\n";
        }

		try {
			$Ice_Session = $Config['MRS']['Session'];
			$Ice_MessageSizeMax = $Config['MRS']['MessageSizeMax'];
			self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
            $ic = Ice_initialize(self::$init_data);
            $obj = $ic->stringToProxy($Ice_Session);
			self::$MRShandle = CmdPack_CmdPackSessionPrxHelper::checkedCast($obj);
			if (!self::$MRShandle) {
                echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: MRS handle is NULL:";
				echo "\n";
            }
        } catch (Ice_LocalException $ex) {
			echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: MRS connect error :".print_r ($ex,true);
			echo "\n";
        }
    }
	public function getIAShandle($ip , $port){
		if(empty(self::$IAShandle[$ip . ':' . $port])){
			try {
				$Ice_Session = "AgentSession:tcp -p ". $port ." -h " . $ip;
				$Ice_MessageSizeMax = 0;
				self::$init_data->properties->setProperty('Ice.MessageSizeMax', $Ice_MessageSizeMax);
				$ic = Ice_initialize(self::$init_data);
				$obj = $ic->stringToProxy($Ice_Session);
				self::$IAShandle[$ip . ':' . $port] = AppAgent_AgentSessionPrxHelper::checkedCast($obj);
				if (!self::$IAShandle[$ip . ':' . $port]) {
					echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: IAS handle is NULL:";
					echo "\n";
				}
			} catch (Ice_LocalException $ex) {
				echo "[".date("Y-m-d H:i:s")."] debug.DEBUG: IAS connect error :".print_r ($ex,true);
				echo "\n";
				return false;
			}
		}
		return self::$IAShandle[$ip . ':' . $port];
	}
	public function sendIASCmd($IAShandle , $cmd){
		return $IAShandle->pushCmd($cmd);
	}
    public function getDevStatusDec($param,$getHoldAlarmList,$alarmType = -1){	
		$Dec['Lists'] = $Dec['statusTypeId'] = array();
		$Dec['Dec'] = "";
        $data = $this->getAlarmAndStatus($param);
		if(!empty($data)){
			foreach($data as $v){
				foreach($v->status as $key=>$val){
					if ($val > 0) {
						$number = decbin($val);
						$len = strlen($number);
						$number = strrev($number);
						for ($i = 0; $i < $len; $i++) {
							$flag = $number{$i} & 1;
							if ($flag) {
								$RecID = 1000 + $key * 10 + $i;
								switch($getHoldAlarmList){
									case 2:
										$Dec['statusTypeId'][] = $RecID;
									break;
									case 3:
										$AlarmTypeID = $RecID;
										if($AlarmTypeID && ($alarmType == -1 || $AlarmTypeID == $alarmType)){
											$Dec['Lists'][$v->objId][] = $AlarmTypeID;
										}
									break;
								}
							}
						}
					}
				}
			}
		}
		unset($data);		
		return $Dec;
    }
	//报警统计数据动态获取
	public function getStatsByAlarmTypeGroup($objArr){
		$alarmCount = $this->getDevStatusDec($objArr,2);
		$countAr = $datas = array();
		if(!empty($alarmCount['statusTypeId'])){
			$countArr = array_count_values($alarmCount['statusTypeId']);
			$i = 0;
			foreach($countArr as $k=>$v){
				if($k && $k!=1092) {
					$datas[$i]['alarmTypeID'] = $k;
					$datas[$i]['alarmCount'] = isset($v) ? $v : 0;
					$i++;
				}
			}
		}
		return $datas;
	}
	public function getCountBydistrictName($param){
		if($param['districtType'] == 1){
			$name = "cityName";
			$code = "cityCode";
			$datas = $this->getStatisticsByCity($param['holdID'],$param['districtName']);
		}else if($param['districtType'] == 2){
			$name = "regionName";
			$code = "regionCode";
			$datas = $this->getStatisticsByRegion($param['holdID'],$param['districtName']);
			$param['districtName'] = $param['cityName'];
		}else{
			$name = "provinceName";
			$code = "provinceCode";
			$datas = $this->getStatisticsByProvince($param['holdID']);
		}
		if($datas['status']->errorcode != 0)return array();
		$lists = $datas['data'];
		$k = 0;
		$districList = $reutrnData = array();
		foreach($lists as $row){
			$districList[$k][$code] = $this->getRegionIDByName($param['districtName'] , $row->districtName , $param['districtType']);
			$districList[$k][$name] = $row->districtName;
			$districList[$k]['onlineCount'] = !empty($row->statistics->online)?$row->statistics->online:0;
			$districList[$k]['offlineCount'] = !empty($row->statistics->offline)?$row->statistics->offline:0;
			$districList[$k]['alarmCount'] = !empty($row->statistics->alarm)?$row->statistics->alarm:0;
			$districList[$k]['invalidCount'] = !empty($row->statistics->invalid)?$row->statistics->invalid:0;
			$num = $districList[$k]['onlineCount'] + $districList[$k]['offlineCount'] + $districList[$k]['alarmCount'] + $districList[$k]['invalidCount'];
			if($num > 0){
				$reutrnData[] = $districList[$k];
			}
			$k++;
		}
		//按在线状态排序
		$sort = array(  
			'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
			'field'     => 'onlineCount',       //排序字段  
		);  
		$arrSort = array();  
		foreach($reutrnData as $uniqid => $row){  
			foreach($row as $key=>$value){  
				$arrSort[$key][$uniqid] = $value;  
			}
		}
		if($sort['direction']){  
			array_multisort($arrSort[$sort['field']], constant($sort['direction']), $reutrnData);  
		}
		return $reutrnData;
	}
	public function getAlarmAndStatus($param){
		$data = array();
		$check = self::$CAPhandle->getAlarmAndStatus($param,$data);
		return $data;
	}
    public function getTracks($param){
        $data = array();
        $check  = self::$CAPhandle->getTracks($param,$data);
        return array('status'=>$check,'data'=>$data);
    }
    public function getTravel($param){
        $data = array();
        $check = self::$CAPhandle->getTravel($param,$data);
		return $check===0?$data:array();
    }
	public function getObjectRouter($objectID){
		$data = array();
        $check = self::$CAPhandle->getObjectRouter($objectID,$data);
		return array('status'=>$check,'data'=>$data);
	}
	public function getObjsByHoldId($param){
		$total= 0;
        $data = array();
        $pageParam = new OSS_Pagesort($param['pageSize'],$param['pageNum']);
        $status = self::$OSShandle->getObjsByHoldId($param['holdID'],$param['statisticsType'],$pageParam,$total,$data);
        return array('status'=>$status,'data'=>$data,'total'=>$total);
    }
	public function getStatisticsByHoldId($param){
		$data = array();
        $check  = self::$OSShandle->getStatisticsByHoldId($param,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function getObjsBydistrictName($param){
		if($param['districtType'] == 1){
			return $this->getObjsByCity($param);
		}else if($param['districtType'] == 2){
			return $this->getObjsByRegion($param);
		}else{
			return $this->getObjsByProvince($param);
		}
        return array();
	}
	
	public function getObjsByProvince($param){
		$total= 0;
        $data = array();
        $pageParam = new OSS_Pagesort($param['pageSize'],$param['pageNum']);
        $status = self::$OSShandle->getObjsByProvince($param['holdID'],$param['districtName'],$param['statisticsType'],$pageParam,$total,$data);
        return array('status'=>$status,'data'=>$data,'total'=>$total);
	}
	public function getObjsByCity($param){
		$total= 0;
        $data = array();
        $pageParam = new OSS_Pagesort($param['pageSize'],$param['pageNum']);
        $status = self::$OSShandle->getObjsByCity($param['holdID'],$param['districtName'],$param['statisticsType'],$pageParam,$total,$data);
        return array('status'=>$status,'data'=>$data,'total'=>$total);
	}
	public function getObjsByRegion($param){
		$total= 0;
        $data = array();
        $pageParam = new OSS_Pagesort($param['pageSize'],$param['pageNum']);
        $status = self::$OSShandle->getObjsByRegion($param['holdID'],$param['districtName'],$param['statisticsType'],$pageParam,$total,$data);
        return array('status'=>$status,'data'=>$data,'total'=>$total);
	}
	//MRS 
	public function packs($cmdInfo){
		$data = array();
		$info = new CmdPack_CmdInfo($cmdInfo['objId'] , $cmdInfo['imei'] , $cmdInfo['factory'] , $cmdInfo['brand'] , $cmdInfo['protocol'] , $cmdInfo['content']);
        $check = self::$MRShandle->pack($info,$data);
		return array('status'=>$check,'data'=>$data);
	}
	//RDP

	/**
     * @param $param
     */
    public function getRealDatas($param){
        $datas = $data = array();
        $check  = self::$RDPhandle->getRealDatas($param,$data);
		if($check === 0){
			foreach($data as $k=>$object){
				$datas[$k]['ObjectID'] = $object->objId;
				$datas[$k]['IsOBD'] = !empty($object->IsOBD) ? $object->IsOBD : ""; //字段无法获取
				$datas[$k]['ObjectName'] = !empty($object->ObjectName) ? $object->ObjectName : ""; //字段无法获取
				$datas[$k]['theDayMileage'] = $object->gpsDataEx->gpsData->mileage > $object->theDayInitMileage ? ($object->gpsDataEx->gpsData->mileage - $object->theDayInitMileage) / 1000.0 : 0;
				$travel = $this->getTravel($object->objId);
				$co = Api_Desc::$comma;
				$datas[$k]['travelMileage'] = !empty($travel->travelMileage) ? round($travel->travelMileage / 1000, 3) : 0;
                $datas[$k]['TravelOil'] = !empty($travel->travelOil) ? round($travel->travelOil / 1000, 3) : 0;
				$datas[$k]['CurrentMileage'] = !empty($travel->travelMileage) ? round($travel->travelMileage / 1000, 3) : 0;
				$datas[$k]['AlarmDesc'] = empty($object->gpsDataEx->gpsData->alarmFlag) ? "" : $object->gpsDataEx->gpsData->alarmDesc;
				$datas[$k]['IsStop'] = empty($travel)?1:($travel->isCompleted?1:0);
				$datas[$k]['TransType'] = !empty($object->isOnline) ? (int)$object->isOnline: 0;//是否在线
				$datas[$k]['IsLink'] = !empty($object->isLink) ? (int)$object->isLink: 0;//是否断开链接
				$datas[$k]['BeidouSignal'] = 0;   //没有读取到
				$datas[$k]['AlarmFlag'] = empty($object->gpsDataEx->gpsData->alarmFlag) ? 0 : 1;
				$datas[$k]['provinceName'] = !empty($object->gpsDataEx->adminRegion->province) ? $object->gpsDataEx->adminRegion->province : 0;
                $datas[$k]['Lat'] = !empty($object->gpsDataEx->gpsData->point->lat) ? number_format($object->gpsDataEx->gpsData->point->lat / 1000000.0, 6) : '';
                $datas[$k]['Lon'] = !empty($object->gpsDataEx->gpsData->point->lng) ? number_format($object->gpsDataEx->gpsData->point->lng / 1000000.0, 6) : '';
                $datas[$k]['Voltage'] = !empty($object->gpsDataEx->gpsData->battery) && $object->gpsDataEx->gpsData->battery!=255 ? $object->gpsDataEx->gpsData->battery : 0;
                $datas[$k]['Speed'] = !empty($object->gpsDataEx->gpsData->speed) ? $object->gpsDataEx->gpsData->speed : 0;
                $datas[$k]['RcvTime'] = $object->gpsDataEx->gpsData->rcvTime > 0 ? date('Y-m-d H:i:s', $object->gpsDataEx->gpsData->rcvTime) : '';
                $datas[$k]['Mileage'] = !empty($object->gpsDataEx->gpsData->mileage) ? round($object->gpsDataEx->gpsData->mileage / 1000, 3) : 0;
                $datas[$k]['Direct'] = !empty($object->gpsDataEx->gpsData->direction) ? $object->gpsDataEx->gpsData->direction : 0;
                $datas[$k]['GSMSignal'] = !empty($object->gpsDataEx->gpsData->gsmStrength) && $object->gpsDataEx->gpsData->gsmStrength!=255? $object->gpsDataEx->gpsData->gsmStrength : 0;
                $datas[$k]['GPSSignal'] = !empty($object->gpsDataEx->gpsData->satelliteNum) && $object->gpsDataEx->gpsData->satelliteNum!=255 ? $object->gpsDataEx->gpsData->satelliteNum : 0;
				$StatusDes = Api_Desc::getStatusDesc($object->gpsDataEx->gpsData,1);
				$datas[$k]['StatusDes'] = $datas[$k]['AlarmDesc']?trim($datas[$k]['AlarmDesc'].$co.$StatusDes,$co):$StatusDes;
				$datas[$k]['BSLat'] = !empty($object->bsLat) ? number_format($object->bsLat / 1000000.0, 6) : 0;
				$datas[$k]['BSLon'] = !empty($object->bsLon) ? number_format($object->bsLon / 1000000.0, 6) : 0;
				$datas[$k]['WifiLat'] = !empty($object->wifiLat) ? number_format($object->wifiLat / 1000000.0, 6) : 0;
				$datas[$k]['WifiLon'] = !empty($object->wifiLon) ? number_format($object->wifiLon / 1000000.0, 6) : 0;
				$datas[$k]['WifiTime'] = !empty($object->wifiTime) ? date('Y-m-d H:i:s', $object->wifiTime) : '';
				$datas[$k]['BSTime'] = !empty($object->bsTime) ? date('Y-m-d H:i:s', $object->bsTime) : '';
				$datas[$k]['GPSTime'] = !empty($object->gpsDataEx->gpsData->gpsTime) ? date('Y-m-d H:i:s', $object->gpsDataEx->gpsData->gpsTime) : '';
				$datas[$k]['GPSFlag'] = 0;
				$gpsAccuracyType = $object->gpsDataEx->gpsData->gpsAccuracyType;
				if (isset($gpsAccuracyType) && $gpsAccuracyType == 2) {
					$datas[$k]['GPSFlag'] = 318;
				} else if (isset($gpsAccuracyType) && ($gpsAccuracyType == 0 || $gpsAccuracyType == 1)) {
					$datas[$k]['GPSFlag'] = $gpsAccuracyType == 1 ? 308 : 307;
				} else if(isset($gpsAccuracyType) && $gpsAccuracyType == 3){
					$datas[$k]['GPSFlag'] = 347;
				}
			}
		}
		unset($data);
        return $datas;
    }

	//mfs
	public function Records($objectId, $beginTime, $endTime, $speed_limit, $Exact, $limit)
    {
	
        $rs = $this->getRecords($objectId, $beginTime, $endTime);
		$data = array();
        if ($rs['status'] === 0) {
            $alldata = $rs['data'];
            $pre_mileage = 0;
            //$pre_gpsTime = 0;
            $total_mileage = 0;
            $total = 0;

            foreach ($alldata as $k=>$v) {
				
                $v = (array)$v;
                if ($v['lon'] == 0 || $v['lat'] == 0)continue;
                if ($v['speed'] <= $speed_limit)continue;
                if ($Exact > 0 && !$this->filterAccuracyType($v['gpsAccuracyType'], $Exact))continue;
                if ($v['gpsTime'] < $beginTime || $v['gpsTime'] > $endTime)continue;
                if ($pre_mileage == 0)$pre_mileage = $v['mileage'];
                //if ($pre_gpsTime == 0)$pre_gpsTime = $v['gpsTime'];
                $item = array();
                $item['id'] = $objectId;
                $item['lon'] = number_format($v['lon'] / 1000000.0, 6);
                $item['lat'] = number_format($v['lat'] / 1000000.0, 6);
                $transform = Api_CoordinateTransform::WGS2BD(array('Lng' => $item['lon'], 'Lat' => $item['lat']));
                $item['rlon'] = number_format($transform['Lng'], 6);
                $item['rlat'] = number_format($transform['Lat'], 6);
				$item['voltage'] = !empty($v['battery']) && $v['battery']!=255 ? $v['battery'] : 0;
				$item['gpssignal'] = !empty($v['satelliteNum']) && $v['satelliteNum']!=255 ? $v['satelliteNum'] : 0;
                $item['speed'] = $v['speed'];
                $item['direct'] = $v['direct'];
				$item['totalmile'] = $v['mileage'];
                $cur_mileage = $v['mileage'];
                //$cur_gpsTime = $v['gpsTime'];
                $mileage_span = (($cur_mileage < $pre_mileage) ? 0 : $cur_mileage - $pre_mileage);
				$total_mileage += $mileage_span;
                $pre_mileage = $cur_mileage;
                //$pre_gpsTime = $cur_gpsTime;
                $item['mile'] = number_format($total_mileage / 1000.0, 2);
                $item['gpsTime'] = date('Y-m-d H:i:s', $v['gpsTime']);
                $item['rcvTime'] = date('Y-m-d H:i:s', $v['rcvTime']);
                $item['status'] = Api_Desc::getStatusDesc($v);
                $item['posmode'] = Api_Desc::$GPSAccuracyTypeArray[$v['gpsAccuracyType']];
                $item['gpsFlag'] = $this->getGPSAccuracyTypeCode($v['lon'], $v['lat'], $v['gpsAccuracyType']);
                array_push($data, $item);
                ++$total;
                if ($limit > 0 && $total >= $limit) {
                    break;
                }
            }
		}
		return $data;
    }
	public function getRecords($objectId, $beginTime, $endTime){
		$data = array();
        $check = self::$MFShandle->getRecords($objectId,$beginTime,$endTime,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function getStatisticsByProvince($holdID){
		$data = array();
        $check = self::$OSShandle->getStatisticsByProvince($holdID,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function getStatisticsByCity($holdID,$districtName){
		$data = array();
        $check = self::$OSShandle->getStatisticsByCity($holdID,$districtName,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function getStatisticsByRegion($holdID,$districtName){
		$data = array();
        $check = self::$OSShandle->getStatisticsByRegion($holdID,$districtName,$data);
        return array('status'=>$check,'data'=>$data);
	}
	//DAP
	public function GetObjectinfoByImei($param){
		$data = array();
        $check  = self::$DAPhandle->GetObjectinfoByImei($param,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function GetObjectinfoById($objectID){
		$data = array();
        $check  = self::$DAPhandle->GetObjectinfoById($objectID,$data);
        return array('status'=>$check,'data'=>$data);
	}
	public function SaveCommands($param){
		$message = new DAPProxy_DownInfo($param['DownID'] , $param['ObjectID'] , $param['sendContent'] , $param['CMDTypeID'] , $param['SubmitTime'] , $param['sendUserID'] , $param['sim'] , $param['remark'] , $param['sendFlag'] , $param['sendsource'] , $param['TransType'] , $param['DownInfoType']);
		return self::$DAPhandle->SaveCommands($message);
	}
	public function setRegionDistricts(){
		$data = array();
		self::$DAPhandle->GetRegionDistricts($data);
		self::$Region['times'] = time();
		foreach($data as $val){
			self::$Region['datas'][$val->recid] = array(
				'province' => $val->province,
				'city' => $val->city,
				'region' => $val->region,
			);
		}
	}
	public function getRegionByID($ID){
		if(!$ID)return array();
		if((time() - self::$Region['times']) > self::$RegionCacheTime){
			$this->setRegionDistricts();
		}
		if(empty(self::$Region['datas'][$ID])){
			$data = array();
			self::$DAPhandle->GetRegionDistrictById($ID , $data);
			if(!empty($data)){
				self::$Region['datas'][$ID] = array(
					'province' => $data->province,
					'city' => $data->city,
					'region' => $data->region,
				);
			}else{
				self::$Region['datas'][$ID] = array(
					'province' => "",
					'city' => "",
					'region' => "",
				);
			}
		}
		return self::$Region['datas'][$ID];
	}
	public function getRegionIDByName($city = "" , $region = "" , $type = 0){
		if((time() - self::$Region['times']) > self::$RegionCacheTime){
			$this->setRegionDistricts();
		}
		$city = $city ? $city : $region;
		$ID = 0;
		if($type == 2){
			$f = 'city';
			$s = 'region';
		}else{
			$f = 'province';
			$s = 'city';
		}
		foreach(self::$Region['datas'] as $key => $val){
			if(strpos($val[$f] , $city) !== false && strpos($val[$s] , $region) !== false){
				$ID = $key;
				break;
			}
		}
		return $ID;
	}
	//ISA
	public function pushCmd($IAS , $param){
		$message = new AppAgent_AppCmd($param['SequenceNo'] , $param['Imei'] , $param['Content'] , $param['Digest']);
		return $IAS->pushCmd($message);
	}
	private function filterAccuracyType($gpsAccuracyType, $Exact)
    {
        $result = true;
        switch ($Exact) {
            case 1:
                if ($gpsAccuracyType == 1) {
                    $result = false;
                }
                break;
            case 2:
                if ($gpsAccuracyType == 2 || $gpsAccuracyType == 3) {
                    $result = false;
                }
                break;
            case 3:
                if ($gpsAccuracyType == 1 || $gpsAccuracyType == 2 || $gpsAccuracyType == 3) {
                    $result = false;
                }
                break;
            default:
                $result = true;
                break;
        }

        return $result;
    }
	private function getGPSAccuracyTypeCode($lon, $lat, $type)
    {
        if ($lon > 0) {
            if ($lat > 0) {
                return self::$gpsAccuracyTypeCode["eastNorth"][$type];
            } else# if ($lat < 0)
            {
                return self::$gpsAccuracyTypeCode["eastSouth"][$type];
            }
        } else if ($lon < 0) {
            if ($lat > 0) {
                return self::$gpsAccuracyTypeCode["westNorth"][$type];
            } else# if ($lat < 0)
            {
                return self::$gpsAccuracyTypeCode["westSouth"][$type];
            }
        }
    }
	private static $gpsAccuracyTypeCode = array(
        'eastNorth' => array(0 => 307, 1 => 308, 2 => 318, 3 => 347, 4 => 337),
        'eastSouth' => array(0 => 305, 1 => 306, 2 => 316, 3 => 345, 4 => 335),
        'westNorth' => array(0 => 303, 1 => 304, 2 => 314, 3 => 343, 4 => 333),
        'westSouth' => array(0 => 301, 1 => 302, 2 => 312, 3 => 341, 4 => 331),
    );
}
