<?php

/**
 * JSON-RPC request class file
 * @author Eugene Khorev <eugene.khorev@gmail.com>
 */

namespace jsonrpc;

/**
 * JSON-RPC controller
 */
class Request extends \yii\web\Request
{

	/**
	 * @inheritdoc
	 */
	public $parsers = [
		'application/json' => 'yii\web\JsonParser',
	];
}
