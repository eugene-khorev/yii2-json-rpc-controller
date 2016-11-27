<?php

/**
 * JSON-RPC controller class file
 * @author Eugene Khorev <eugene.khorev@gmail.com>
 */

namespace jsonrpc;

use yii\web\Response;
use yii\filters\Cors;
use yii\filters\ContentNegotiator;

/**
 * JSON-RPC controller
 */
class Controller extends \yii\web\Controller
{

	/**
	 * @inheritdoc
	 */
	public $enableCsrfValidation = false;
        public $accessControlRequestOrigin = ['*'];
        public $accessControlRequestHeaders = ['*'];
        public $accessControlRequestMethod = ['GET', 'POST'];

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
                        [
                            'class' => Cors::className(),
                            'cors' => [
                                'Origin' => $this->accessControlRequestOrigin,
                                'Access-Control-Request-Headers' => $this->accessControlRequestHeaders,
                                'Access-Control-Request-Method' => $this->accessControlRequestMethod,
                            ],
                        ],
			'contentNegotiator' => [
				'class'		 => ContentNegotiator::className(),
				'formats'	 => [
					'application/json'	 => Response::FORMAT_JSON,
					'text/html'			 => Response::FORMAT_HTML,
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function actions()
	{
		return [
			'rpc' => [
				'class'		 => Action::className(),
				'scenario'	 => 'rpc',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		\Yii::beginProfile('jsonrpc.controller.' . $action->id);
		return parent::beforeAction($action);
	}

	/**
	 * @inheritdoc
	 */
	public function afterAction($action, $result)
	{
		\Yii::endProfile('jsonrpc.controller.' . $action->id);
		return parent::afterAction($action, $result);
	}
}
