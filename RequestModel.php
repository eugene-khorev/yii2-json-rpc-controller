<?php

/**
 * JSON-RPC request model file
 * @author Eugene Khorev <eugene.khorev@gmail.com>
 */

namespace jsonrpc;

/**
 * JSON-RPC request model
 */
class RequestModel extends \yii\base\Model
{

	public $jsonrpc;
	public $method;
	public $params;
	public $id;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$checkParams = function ($attribute) {
			if (!is_array($this->$attribute)) {
				$this->addError($attribute, 'Invalid request params.');
			}
		};
		
		return [
			[['jsonrpc', 'method', 'id'], 'required'],
			['jsonrpc', 'compare', 'compareValue' => '2.0', 'operator' => '=='],
			['params', $checkParams],
		];
	}
}
