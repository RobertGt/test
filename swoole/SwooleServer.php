<?php

/**
 * Swoole服务端
 */
class SwooleServer {

    private $_setting = array();
    public function __construct($host = '0.0.0.0', $port = 9501) {
        $this->_setting = array(
            'host' => $host,
            'port' => $port,
            'worker_num' => 4, //一般设置为服务器CPU数的1-4倍
            'daemonize' => 1, //以守护进程执行
            'dispatch_mode' => 2,
			'task_worker_num' => 10,
            'log_file' => SWOOLE_PATH  . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Logs' . DIRECTORY_SEPARATOR . 'SwooleError.log',  //日志
        );
    }

    /**
     * 运行swoole服务
     */
    public function run() {
        $server = new Swoole\Http\Server($this->_setting['host'], $this->_setting['port']);
        $server->set(array(
            'worker_num' => $this->_setting['worker_num'],
            'daemonize' => $this->_setting['daemonize'],
            'dispatch_mode' => $this->_setting['dispatch_mode'],
			'task_worker_num' => $this->_setting['task_worker_num'],
            'log_file' => $this->_setting['log_file']
        ));
        $server->on('Start', function($serv){
			setProcessName(SWOOLE_TASK_NAME_PRE . '-master');
			//记录进程id,脚本实现自动重启
			$pid = "{$serv->master_pid}\n{$serv->manager_pid}";
			file_put_contents(SWOOLE_TASK_PID_PATH, $pid);
		});
		$server->on('WorkerStart', function($serv, $workerId){
			$Config = require_once(SWOOLE_PATH . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'Config.php');
			$this->Domian = new DomianModel($Config['ICE']);
			if ($workerId >= $this->_setting['worker_num']) {
				setProcessName(SWOOLE_TASK_NAME_PRE . '-task');
			} else {
				$this->Server = $serv;
				setProcessName(SWOOLE_TASK_NAME_PRE . '-event');
			}
		});
		$server->on('Request', function ($request,$response) {
			//日志
			$log_path = SWOOLE_PATH . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'Logs' . DIRECTORY_SEPARATOR . date("Ymd") . DIRECTORY_SEPARATOR;  
			$log_file_name = $request->server['path_info'];  
			$log_obj = new Logs($log_path, $log_file_name);
			$Start = microtime(true);
			$log_obj->LogDebug("StartTtime : ".$Start);
			$post = json_decode($request->rawContent() , true);
			if(!$post)$post = array();
			$get = isset($request->get) ? $request->get : array();
			$req['request'] = array_merge($post , $get);
			$info = pathinfo(isset($request->server['path_info'])?$request->server['path_info']:"");
			$controller = str_replace("/extraapi/","",!empty($info['dirname'])?($info['dirname'] == "/"?"":strtolower($info['dirname'])):"");
			$action =  !empty($info['basename'])?$info['basename']:"";
			$req['controller'] = isset($controller)?ucfirst($controller)."Controller":"InitController";
			$req['action'] = isset($action)?$action."Action":"InitAction";
			$exce = $this->Server->taskwait($req , 1);
			if(!$exce)$exce = json_encode(array('error'=>504,'reason'=>'TimeOut','result'=>NULL));
			$log_obj->LogDebug("Request : ". print_r ($req,true));
			$End = microtime(true);
			$log_obj->LogDebug("Response : ". $exce);
			$log_obj->LogDebug("EndTime : ".$End);
			$log_obj->LogDebug("ExecuteTime : ".($End - $Start)."s");
			$response->header("Content-Type", "application/json; charset=utf-8");
			return $response->end($exce);
		});
		$server->on("Task", function($serv , $task_id , $worker_id , $request){
			$action = $request['action'];
			try {
				$newClass = new $request['controller']($this->Domian , $request['request']);
				if(method_exists($newClass,$action)){
					$exce = $newClass->$action();
				}else{
					$exce = json_encode(array('error'=>404,'reason'=>'Not Found','result'=>NULL));
				}
			} catch (Exception $e) {
				echo print_r ($e , true);
				$newClass = new InitController($this->Domian);
				$exce = $newClass->InitAction();
			}
			$newClass = NULL;
			$serv->finish($exce);
		});
		$server->on("Finish", function($serv , $task_id , $response){
			
		});
		$server->on("WorkerError", function($serv,$workerId,$worker_pid,$exit_code,$signal){
			echo "Worker is CoreDump,WorkerID:".$workerId.",WorkerPID:".$worker_pid.",ExitCode:".$exit_code.",Signal:".$signal."\n";
		});
		$server->on("Close", function($serv,$fd,$reactor){
			
		});
        $server->start();
    }
}