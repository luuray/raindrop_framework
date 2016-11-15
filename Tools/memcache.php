<?php
/**
 * Raindrop Framework for PHP
 *
 * Memcached Manager
 *
 * @author $Author: Luuray $
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

$aServer['main'] = '127.0.0.1:11211';
$aServer['second'] = 'unix://tmp/memcached.sock';

$sAct = empty($_GET['act'])?'index':$_GET['act'];
if($sAct == 'index'){
	?>
<!DOCTYPE html>
<html lang="zh-cn" ng-app="memCache">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" href="">

		<title>Memcached Manager</title>

		<link href="//cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
		<script src="//cdn.bootcss.com/angular.js/1.4.14/angular.min.js" type="text/javascript"></script>
		<script src="//cdn.bootcss.com/angular-ui-bootstrap/2.2.0/ui-bootstrap-tpls.min.js" type="text/javascript"></script>

		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		<style type="text/css">
			table{
				table-layout: fixed;
				word-wrap: break-word;
			}
		</style>
	</head>
	<body>
	<div class="container-fluid" ng-controller="IndexController">
		<div class="alert alert-warning" role="alert" ng-show="hasAlert">{{message}}</div>
		<div ng-controller="ListController">
			<div ng-repeat="(k, v) in servers">
				<table ng-if="v.flag == true" class="table table-striped table-hover table-condensed table-bordered">

					<caption>Server: <span class="text-info">[{{ k }}]{{ v.connection }}</span>,
						Total Items: <span class="text-info">{{ v.data.curr_items }}</span>,
						Size: <span class="text-info">{{ v.data.bytes/1048576.0 |number:2}} MByte of {{ v.data.limit_maxbytes / 1048576.0 | number:2 }} MByte</span>,
						Hit Rate: <span class="text-info">{{ (v.data.get_hits/(v.data.get_hits*1 + v.data.get_misses*1))*100 |number: 4 }} %</span>,
						Next Refresh In <span class="text-danger">{{ refreshInterval/1000 }}</span> Seconds.
					</caption>
					<thead>
					<tr>
						<td class="col-xs-1">Key</td>
						<td>Value</td>
						<td class="danger col-xs-1">Delete <i class="glyphicon glyphicon-warning-sign"></i></td>
					</tr>
					</thead>
					<tbody ng-controller="GetItemsController" ng-init="loadItems(k)">
						<tr ng-if="loadSuccess" ng-repeat="item in items">
							<td ng-bind="item.key"></td>
							<td ng-bind="item.value"></td>
							<td class="danger"><a href="#" ng-click="del($index, item.key)"><i class="glyphicon glyphicon-remove"></i> Delete</a></td>
						</tr>
						<tr ng-if="!loadSuccess">
							<td colspan="3" class="warning">{{ message }}</td>
						</tr>
					</tbody>
				</table>
				<p ng-if="v.flag==false" class="text-warning">Server [{{k}}]{{ v.connection }}, Error: {{v.message}}</p>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		angular.module('memCache', [])
			.controller('IndexController', ['$rootScope', '$scope', function ($rootScope, $scope, $http) {
				$scope.hasAlert = false;
				$scope.message = '';

				$scope.$on('triggerError', function (evt, message) {
					$scope.hasAlert = true;
					$scope.message = message;
				})
			}])

			.controller('ListController', ['$scope', '$http', '$interval', function ($scope, $http, $interval) {
				var refreshInterval = 30000;
				var refreshTimer = null;

				$scope.servers = [];
				$scope.refreshInterval = refreshInterval;

				var loader = function () {
					refreshTimer == null ? null : $interval.cancel(refreshTimer);
					$scope.refreshInterval = refreshInterval;
					$scope.servers = [];

					$http.get('?act=stats', {timeout: 3000})
						.then(function (response) {
							//timer
							refreshTimer = $interval(function(){
								$scope.refreshInterval -= 1000;
							}, 1000);

							$scope.servers = response.data;
						}, function (error) {
							alert(error.data);
						});
				};

				loader();

				$interval(loader, refreshInterval);
			}])
			.controller('GetItemsController', ['$scope', '$http', '$interval', function ($scope, $http, $interval) {
				$scope.server = '';
				$scope.items = null;
				$scope.loadSuccess = false;
				$scope.message = '';

				$scope.loadItems = function (server) {
					$scope.server = server;

					$scope.refreshItems();
				};

				$scope.refreshItems = function () {
					$http.post('?act=list', {server: $scope.server}, {timeout: 3000})
						.then(function (response) {
							$scope.items = response.data;
							$scope.loadSuccess = true;
						}, function (error) {
							$scope.loadSuccess = false;
							$scope.message = error.data;
						})
				};

				$scope.del = function (index, key) {
					$http.post('?act=del', {server:$scope.server, key: key}, {timeout: 3000})
						.then(function (response) {
							$scope.items.splice(index, 1);
						}, function (error) {
							alert(error.data);
						});
					$scope.items.splice(index, 1);
				}
			}])

	</script>
	</body>
</html>
<?php
	exit;
}

try {
	@header_remove();
	@header('Content-Type: application/json; charset=UTF-8');
	if($sAct == 'stats'){
		$aResult = [];
		foreach($aServer AS $_name => $_srv){
			$oServer = getMemcache($_srv);

			$aStats = $oServer->getStats();
			$aStats = isset($aStats['pid'])? $aStats : array_shift($aStats);

			if($aStats['pid'] != -1){
				$aResult[$_name]=['connection'=>$_srv,'flag'=>true, 'data'=>$aStats];
			}
			else {
				$aResult[$_name] = ['connection'=>$_srv, 'flag'=>false, 'message'=>'no_connect'];
			}
		}
		echo json_encode($aResult);
		exit;
	}
	if($sAct == 'list'){
		$aRequest = @file_get_contents('php://input');
		$oResult = @json_decode($aRequest);

		if($oResult == false){
			@header('invalid_request', true, 500);
			exit;
		}

		$sServer = array_key_exists($oResult->server, $aServer) ? $aServer[$oResult->server] : null;
		if($sServer == null){
			@header('undefined_server', true, 500);
			exit;
		}

		$oServer = getMemcache($sServer);
		if($oServer == false){
			@header('no_connect', true, 500);
			exit;
		}

		$aResult = [];

		if($oServer instanceof Memcache) {
			$aSlabs = $oServer->getExtendedStats('slabs');
			$aItems = $oServer->getExtendedStats('items');
			foreach ($aSlabs AS $_slab) {
				foreach ($_slab AS $_sid => $_meta) {
					if (!is_int($_sid)) continue;

					$aDumps = $oServer->getExtendedStats('cachedump', (int)$_sid, 65535);

					foreach ($aDumps AS $_srv => $_entries) {
						if ($_entries) {
							foreach ($_entries AS $_key => $_meta) {
								$aResult[] = ['key' => $_key, 'value' => $oServer->get($_key)];
							}
						}
					}
				}
			}
		}
		else{
			$aKeys = $oServer->getAllKeys();
			foreach($aKeys AS $_key){
				$aResult[] = ['key'=>$_key, 'value'=>$oServer->get($_key)];
			}
		}

		echo json_encode($aResult);
		exit;
	}
	if($sAct == 'del'){
		$aRequest = @file_get_contents('php://input');
		$oResult = @json_decode($aRequest);

		if($oResult == false){
			@header('invalid_request', true, 500);
			exit;
		}

		$sServer = array_key_exists($oResult->server, $aServer) ? $aServer[$oResult->server] : null;
		if($sServer == null){
			@header('undefined_server', true, 500);
			exit;
		}

		$oServer = getMemcache($sServer);
		if($oServer == false){
			@header('no_connect', true, 500);
			exit;
		}

		if($oServer->delete($oResult->key)){
			echo json_encode(['message'=>'success']);
		}
		else{
			@header('delete_fail', true, 500);
			exit;
		}
	}
}catch(Exception $ex){
	//echo json_encode(['status' => 'error', 'message' => $ex]);
	@header($ex->getMessage(), true, 500);
}

/**
 * @param $sConn
 *
 * @return Memcache
 */
function getMemcache($sConn)
{
	if (substr($sConn, 0, 7) == 'unix://') {
		$sServer = $sConn;
		$iPort   = null;
	} else {
		$sServer = parse_url($sConn, PHP_URL_HOST);
		$iPort   = parse_url($sConn, PHP_URL_PORT);

		$sServer = $sServer == null ? 'localhost' : $sServer;
		$iPort   = $iPort == null ? 11211 : $iPort;
	}

	$oMemcache = class_exists('Memcached')?new Memcached():new Memcache();

	$oMemcache->addServer($sServer, $iPort);

	return $oMemcache;
}