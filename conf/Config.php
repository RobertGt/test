<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2017/3/2
 * Time: 16:51
 * Email: 1183@mapgoo.net
 */
return array(
    'ICE' => array(
		//RDP链接端口配置
		'RDP' => array(
            'Session' => 'RealDataSession:tcp -p 10050 -h 192.168.100.203',
            'MessageSizeMax' => 0,
        ),
		//CAP链接端口配置
		'CAP' => array(
			'Session' => 'CacheSession:tcp -p 10050 -h 192.168.100.203',
			'MessageSizeMax' => 0,
		),
		//DAP链接端口配置
		'DAP' => array (
			'Session' => 'DAPSession:tcp -p 12000 -h 192.168.100.203',
			'MessageSizeMax' => 0,
		),
		//MFS链接端口配置
		'MFS' => array (
			'Session' => 'MFSSession:tcp -p 10051 -h 192.168.100.158',
			'MessageSizeMax' => 0,
		),
		//OSS链接端口配置
		'OSS' => array (
			'Session' => 'OSSSession:tcp -p 10070 -h 192.168.100.203',
			'MessageSizeMax' => 0,
		),
		//MRS链接端口配置
		'MRS' => array (
			'Session' => 'CmdPackSession:tcp -p 11777 -h 192.168.100.203',
			'MessageSizeMax' => 0,
		)
    )
);
