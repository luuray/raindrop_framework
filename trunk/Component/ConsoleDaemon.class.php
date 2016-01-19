<?php
/**
 * BoostQueue
 *
 *
 *
 * @author $Author$
 * @copyright Rainhan System
 * @date $Date$
 *
 * Copyright (c) 2010-2015, Rainhan System
 * Site: raindrop-php.rainhan.net
 *
 * $Id$
 *
 * @version $Rev$
 */

namespace Raindrop\Component;


use Raindrop\Application;
use Raindrop\Configuration;
use Raindrop\Logger;

class ConsoleDaemon
{
	protected $_oServer = null;
	protected $_aWorker = [];

	/**
	 * @var Configuration
	 */
	protected $_oConfig;

	public function __construct(Configuration $oConfig)
	{
		if ($oConfig == null) {
			exit('ERROR: Missing Config!');
		}

		$this->_oConfig = $oConfig;

		$this->_oServer = new \swoole_server(
			$this->_oConfig->Get('Address', '127.0.0.1'),
			$this->_oConfig->Get('Port', 9501), SWOOLE_BASE, SWOOLE_SOCK_TCP);

		$this->_fetchWorker();

		$this->_oServer->set([
			//'daemonize'=>true,
			'dispatch_mode'   => 2,
			'worker_num'      => count($this->_aWorkers) + 1,
			'task_worker_num' => $this->_oConfig->Get('TaskWorkerNum', 10),
			'max_request'     => $this->_oConfig->Get('MaxRequest', 32),
			'max_connection'  => $this->_oConfig->Get('MaxConnection', 256),
		]);

		#region Worker Events
		//OnWorkerStart => bind worker to services
		$this->_oServer->on('WorkerStart', [$this, 'onWorkerStart']);
		$this->_oServer->on('WorkerStop', [$this, 'onWorkerStop']);
		$this->_oServer->on('WorkerError', [$this, 'onWorkerError']);
		#endregion

		#region Server Events
		$this->_oServer->on('Start', [$this, 'onStart']);

		$this->_oServer->on('Connect', [$this, 'onConnect']);
		$this->_oServer->on('Receive', [$this, 'onReceive']);
		$this->_oServer->on('Shutdown', [$this, 'onShutdown']);
		#endregion

		#region Task Worker Events
		$this->_oServer->on('Task', [$this, 'onTask']);
		$this->_oServer->on('Finish', [$this, 'onFinish']);
		$this->_oServer->on('Timer', [$this, 'onTimer']);
		#endregion

		$this->_oServer->start();
	}

	/**
	 * @param \swoole_server $oServer
	 */
	public function onStart(\swoole_server $oServer)
	{
		Logger::Message(sprintf(
			'Server Start@%d, Listen: %s:%d, Setting JSON: %s, Swoole Version:%s',
			Application::GetRequestTime(),
			$this->_oConfig->Get('Address', '127.0.0.1'),
			$this->_oConfig->Get('Port', 9501),
			json_encode($oServer->setting),
			SWOOLE_VERSION));
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iConnId
	 * @param $iReactor
	 */
	public function onConnect(\swoole_server $oServer, $iConnId, $iReactor)
	{
		$aInfo = $oServer->connection_info($iConnId);
		Logger::Message('Connect: ' . json_encode($aInfo));

		$oServer->send($iConnId, json_encode([
			'StartTime'     => Application::GetRequestTime(),
			'Workers'       => $this->_aWorkers,
			'MemoryUsage'   => byte2string(memory_get_usage()),
			'CronTabCounts' => count($this->_aCronTabs)
		]));
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iConnId
	 * @param $iReactor
	 * @param $sData
	 */
	public function onReceive(\swoole_server $oServer, $iConnId, $iReactor, $sData)
	{
		Logger::Trace('Receive Data@' . time() . ':' . $sData);

		//command dispatch
		if ($sData == 'quit') {
			@$oServer->clearTimer();
			$oServer->shutdown();
		}
	}

	/**
	 * @param \swoole_server $oServer
	 */
	public function onShutdown(\swoole_server $oServer)
	{
		Logger::Message(sprintf(
			'Server Shutdown@%d, Runtime: %d',
			time(), (time() - Application::GetRequestTime())));
	}

	#region Worker Events
	/**
	 * @param \swoole_server $oServer
	 * @param $iWorkerId
	 */
	public function onWorkerStart(\swoole_server $oServer, $iWorkerId)
	{
		if (array_key_exists($iWorkerId, $this->_aWorkers)) {
			$aInstance = $this->_aWorkers[$iWorkerId];
			$oInstance = $aInstance['Instance']->newInstance();
			Logger::Message("Worker [{$aInstance['Name']}] Started@" . time());

			//bind ticker
			$mTicker = $oInstance->getTicker();
			if (is_array($mTicker)) {
				foreach ($mTicker AS $_item) {
					if ($_item instanceof CronTabTicker) {
						$oServer->tick($_item->getInterval(), $_item->getCallback());
						Logger::Message("Worker [{$aInstance['Name']}] add a ticker, interval:" . $_item->getInterval());
					} else {
						Logger::Warning("Worker [{$aInstance['Name']}]'s ticker invalid");
					}
				}
			} else if ($mTicker instanceof CronTabTicker) {
				$oServer->tick($mTicker->getInterval(), $mTicker->getCallback());
				Logger::Message("Worker [{$aInstance['Name']}] add a ticker, interval:" . $mTicker->getInterval());
			}
		}
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iWorkerId
	 */
	public function onWorkerStop(\swoole_server $oServer, $iWorkerId)
	{
		if (array_key_exists($iWorkerId, $this->_aWorkers)) {
			Logger::Message("Worker[{$this->_aWorkers[$iWorkerId]['Name']}] Stop@" . time());
		}
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iWorkerId
	 * @param $iWorkerPID
	 * @param $iExitCode
	 */
	public function onWorkerError(\swoole_server $oServer, $iWorkerId, $iWorkerPID, $iExitCode)
	{
	}
	#endregion

	#region Task Events
	/**
	 * @param \swoole_server $oServer
	 * @param $iTaskId
	 * @param $iFromWorker
	 * @param $sData
	 */
	public function onTask(\swoole_server $oServer, $iTaskId, $iFromWorker, $sData)
	{
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iTaskId
	 * @param $sData
	 */
	public function onFinish(\swoole_server $oServer, $iTaskId, $sData)
	{
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iInterval
	 */
	public function onTimer(\swoole_server $oServer, $iInterval)
	{
	}

	#endregion

	protected function _fetchWorker()
	{
		//CronTab
		$this->_aWorkers[] = [
			'Name'     => 'CronTab',
			'Instance' => new \ReflectionClass('Raindrop\Component\CronTab')
		];

		//fetch user defined workers
		$aFiles = glob(AppDir . '/worker/*.class.php');
		foreach ($aFiles AS $_item) {
			$aInfo = pathinfo($_item);
			if (str_endwith($aInfo['basename'], '.class.php')) {
				$sName = rtrim($aInfo['basename'], '.class.php');
				$oInstance = new \ReflectionClass(AppName . "\Worker\\{$sName}");
				if ($oInstance->isSubclassOf('Raindrop\AbstractClass\Worker') == false) throw new FatalErrorException('invalid_worker:' . $sName);

				$this->_aWorkers[] = [
					'Name'     => $sName,
					'Instance' => $oInstance,
				];
			}
		}

		return true;
	}
}