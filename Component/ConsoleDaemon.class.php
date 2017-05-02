<?php
/**
 * Raindrop Framework for PHP
 *
 * Swoole Console Daemon
 *
 * @author Luuray
 * @copyright Rainhan System
 * @id $Id$
 *
 * Copyright (c) 2010-2017, Rainhan System
 * Site: raindrop-php.rainhan.net
 */

namespace Raindrop\Component;

use Raindrop\Application;
use Raindrop\Configuration;
use Raindrop\Console\CronTab;
use Raindrop\Console\CronTabTicker;
use Raindrop\Console\Listener;
use Raindrop\Exceptions\FatalErrorException;
use Raindrop\Logger;

class ConsoleDaemon
{
	protected $_oServer = null;
	protected $_aListener = [];
	protected $_aWorker = [];
	protected $_aCronTab = [];
	protected $_aTicker = [];
	protected $_iMasterPort;

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
			$this->_oConfig->Get('Host', '127.0.0.1'),
			$this->_oConfig->Get('Port', 9501), SWOOLE_BASE, SWOOLE_SOCK_TCP);

		$this->_aListener[$this->_oConfig->Get('Port', 9501)] = new Listener(
			$this->_oConfig->Get('Host', '127.0.0.1'),
			$this->_oConfig->Get('Port', 9501),
			SWOOLE_SOCK_TCP);

		$this->_iMasterPort = $this->_oConfig->Get('Port', 9501);


		$this->_fetchWorker();

		$this->_oServer->set([
			//'daemonize'=>true,
			'dispatch_mode'   => 2,
			'worker_num'      => count($this->_aWorkers),
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

		if ($aInfo['server_port'] == $this->_iMasterPort) {
			$oServer->send($iConnId, json_encode($this->getRuntimeStatus()));
		}
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
		} else if ($sData == 'ping') {
			$oServer->send($iConnId, json_encode($this->getRuntimeStatus()));
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
			$oInstance = $aInstance['Instance'];
			$oInstance->setWorkerId($iWorkerId);
			$oInstance->run();

			//bind ticker
			$mTicker = $oInstance->getTicker();

			if ($mTicker == null) {
				return;
			} else if ($mTicker instanceof CronTabTicker) {
				$mTicker = [0 => $mTicker];
			}
			Logger::Trace('WorkerId:' . $iWorkerId . ', Ticker:' . count($mTicker));

			foreach ($mTicker AS $_item) {
				if ($_item instanceof CronTabTicker) {
					$oServer->tick($_item->getInterval(), $_item->getCallback());

					$this->_aTicker[] = ['WorkerId' => $iWorkerId, 'Worker' => $aInstance['Name'], 'Interval' => $_item->getInterval()];

					Logger::Message("Worker [{$aInstance['Name']}] add a ticker, interval:" . $_item->getInterval());
				} else {
					Logger::Warning("Worker [{$aInstance['Name']}]'s ticker invalid");
				}
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
		Logger::Trace('onWorkerError, WorkerId:' . $iWorkerId . ', WorkerPID:' . $iWorkerPID . ', ExitCode:' . $iExitCode);
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
		Logger::Trace('onTask, TaskId:' . $iTaskId . ', FromWorker:' . $iFromWorker . ', Data:' . $sData);
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iTaskId
	 * @param $sData
	 */
	public function onFinish(\swoole_server $oServer, $iTaskId, $sData)
	{
		Logger::Trace('onFinish, TaskId:' . $iTaskId . ', Data:' . $sData);
	}

	/**
	 * @param \swoole_server $oServer
	 * @param $iInterval
	 */
	public function onTimer(\swoole_server $oServer, $iInterval)
	{
		Logger::Trace('onTimer, Interval:' . $iInterval);
	}

	#endregion

	public function getRuntimeStatus()
	{
		return [
			'StartTime'   => Application::GetRequestTime(),
			'MemoryUsage' => byte2string(memory_get_usage()),
			'Worker'      => $this->_aWorkers,
			'CronTab'     => count($this->_aCronTab),
			'Ticker'      => $this->_aTicker
		];
	}

	/**
	 * Fetch Worker
	 *
	 * @return bool
	 * @throws FatalErrorException
	 * @throws \Raindrop\Exceptions\InvalidArgumentException
	 */
	protected function _fetchWorker()
	{
		//CronTab
		$this->_aWorkers[] = [
			'Name'     => 'CronTab',
			'Instance' => new CronTab($this->_oServer)
		];

		//fetch user defined workers
		$aFiles = glob(AppDir . '/worker/*.class.php');
		foreach ($aFiles AS $_item) {
			$aInfo = pathinfo($_item);
			if (str_endwith($aInfo['basename'], '.class.php')) {
				$sName     = AppName . '\Worker\\' . rtrim($aInfo['basename'], '.class.php');
				$oInstance = new $sName($this->_oServer);

				//bind extra ports
				$mListener = $oInstance->getListener();
				if ($mListener instanceof Listener) {
					$mListener = [0 => $mListener];
				}

				if ($mListener != null) {
					foreach ($mListener AS $_item) {
						if ($_item instanceof Listener) {
							if (array_key_exists($_item->getPort(), $this->_aListener)) {
								throw new FatalErrorException(sprintf('multi_listener:[%s] %s:%d', $sName, $_item->getHost(), $_item->getPort()));
							}

							$oHandler = $this->_oServer->listen($_item->getHost(), $_item->getPort(), $_item->getType());
							if ($oHandler == false) throw new FatalErrorException(sprintf('add_listener_fail:[%s] %s:%d', $_item->getHost(), $_item->getPort()));

							$this->_aListener[$_item->getPort()] = $_item;

							$oInstance->setHandler($oHandler);
						} else {
							throw new FatalErrorException('invalid_listener_define');
						}
					}
				}

				$this->_aWorkers[] = [
					'Name'     => $sName,
					'Instance' => $oInstance,
				];
			}
		}

		return true;
	}
}