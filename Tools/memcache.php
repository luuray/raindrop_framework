<?php
/**
 * Raindrop Framework for PHP
 *
 * Memcached Manager
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

$sAct = empty($_GET['act']) ? 'index' : $_GET['act'];
if ($sAct == 'index') {
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

		<link href="/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<script src="/static/jquery/jquery.min.js" type="text/javascript"></script>
		<script src="/static/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
		<script src="/static/angular/angular.min.js" type="text/javascript"></script>

		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body>
	<div class="container-fluid" ng-controller="IndexController">
		<div class="alert alert-warning" role="alert" ng-show="hasAlert">{{message}}</div>
		<div ng-controller="ListController">
			<table class="table table-striped table-hover table-condensed" ng-repeat="(k, v) in servers">
				<caption>Server: <span class="text-info">{{ k }}</span>,
					Total Items: <span class="text-info">{{ v.curr_items }}</span>,
					Size: <span class="text-info">{{ v.limit_maxbytes / 1048576.0 | number:2 }} MByte</span></caption>
				<thead>
				<tr>
					<td>Key</td>
					<td>Value</td>
					<td class="danger">Delete <i class="glyphicon glyphicon-warning-sign"></i></td>
				</tr>
				</thead>
				<tbody id="list">
				</tbody>
			</table>
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

			.controller('ListController', ['$scope', '$http', function ($scope, $http) {
				$scope.servers = [];

				$http.get('?act=stats')
					.success(function (data, status) {
						if (data.status == false) {
							$scope.$broadcast('triggerError', data.message)
						}
						else {
							$scope.servers = data.data;
						}
					});
			}])
	</script>
	</body>
	</html>
	<?php
	exit;
}

$aConfig = [
	'Server' => '127.0.0.1',
	'Port'   => 11211
];

try {
	$oMemcache = new Memcache();
	if ($oMemcache->connect($aConfig['Server'], $aConfig['Port']) == false) {
		echo json_encode(['status' => 'error', 'message' => 'connect_fail']);
	}

	if ($sAct == 'stats') {
		echo json_encode(['status' => 'success', 'data' => $oMemcache->getextendedstats()]);
	}
	if ($sAct == 'list') {

	}
	if ($sAct == 'del') {

	}
} catch (Exception $ex) {
	echo json_encode(['status' => 'error', 'message' => $ex]);
}