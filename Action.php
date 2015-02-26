<?php

/**
 * JSON-RPC controller action class file
 * @author Eugene Khorev <eugene.khorev@gmail.com>
 */

namespace jsonrpc;

use Yii;
use yii\helpers\ArrayHelper;
use jsonrpc\RequestModel;

/**
 * JSON-RPC controller action
 */
class Action extends \yii\base\Action
{

	public $scenario = 'rpc';

	/**
	 * @inheritdoc
	 */
	public function run()
	{
		$responseArray = [];
		
		try
		{
			\Yii::trace('Running JSON-RPC 2.0 request', 'jsonrpc\Action::run');
			
			$requestArray = $this->getRequestArray();
			foreach ($requestArray as $requestData)
			{
				$responseArray[] = $this->processRequest($requestData);
			}
		}
		catch (\Exception $ex)
		{
			$responseArray[] = ['jsonrpc' => '2.0',
				'error' => [
					'code'		 => $ex->getCode(),
					'message'	 => $ex->getMessage(),
					'data'		 => YII_DEBUG ? $ex->getTraceAsString() : null,
				],
			];
		}

		return count($responseArray) === 1 ? reset($responseArray) : $responseArray;
	}

	/**
	 * Parses POST data into array of JSON-PRC batch requests
	 * @return array
	 * @throws \yii\web\BadRequestHttpException
	 */
	protected function getRequestArray()
	{
		$requestArray = Yii::$app->request->getBodyParams();
		if (!is_array($requestArray))
		{
			throw new \yii\web\BadRequestHttpException('Invalid JSON-RPC request.');
		}

		if (!ArrayHelper::isIndexed($requestArray))
		{
			$requestArray = array($requestArray);
		}
		
		return $requestArray;
	}
	
	/**
	 * Validates JSON-RPC request, initializes controller action parameters and runs the action
	 * @param array $requestData
	 * @return array Controller action response
	 */
	protected function processRequest($requestData)
	{
		$responseData = ['jsonrpc' => '2.0'];

		try
		{
			\Yii::trace('Running JSON-RPC 2.0 method: '. ArrayHelper::getValue($requestData, 'method', '-'), 'jsonrpc\Action::processRequest');
			
			\Yii::beginProfile('jsonrpc.controller.rpc.'.  ArrayHelper::getValue($requestData, 'method', '-'));
			
			$requestModel = $this->getRequestModel($requestData);
			$responseData['id'] = $requestModel->id;

			$reflectionMethod = $this->getControllerMethod($requestModel->method);
			$methodParams = $this->getMethodParams($reflectionMethod, $requestModel->params);

			$responseData['result'] = $this->runControllerAction($reflectionMethod->getName(), $methodParams);
			
			\Yii::endProfile('jsonrpc.controller.rpc.'.  ArrayHelper::getValue($requestData, 'method', '-'));
		}
		catch (\Exception $ex) 
		{
			unset($responseData['result']);

			$responseData['error'] = [
				'code'		 => $ex->getCode(),
				'message'	 => $ex->getMessage(),
				'data'		 => YII_DEBUG ? $ex->getTraceAsString() : null,
			];
		}
		
		return $responseData;
	}
	
	/**
	 * Creates and validates JSON-RPC request model
	 * @param array $requestData
	 * @return RequestModel
	 * @throws \yii\web\BadRequestHttpException
	 */
	protected function getRequestModel($requestData)
	{
		$requestModel = new RequestModel();
		$requestModel->attributes = $requestData;
		
		if (!$requestModel->validate())
		{
			throw new \yii\web\BadRequestHttpException('Invalid JSON-RPC request parameters: ' . json_encode($requestModel->getErrors()));
		}
		
		return $requestModel;
	}
	
	/**
	 * Returns controller action method for specified JSON-RPC request method
	 * @param string $requestedMethod
	 * @return \ReflectionMethod
	 * @throws \yii\web\NotFoundHttpException
	 */
	protected function getControllerMethod($requestedMethod)
	{
		$methodName = 'rpc' . str_replace(' ', '', ucwords(implode(' ', explode('-', $requestedMethod))));
		if (!method_exists($this->controller, $methodName))
		{
			throw new \yii\web\NotFoundHttpException("Not found JSON-RPC method {$requestedMethod}.");
		}

		$method = new \ReflectionMethod($this->controller, $methodName);
		if (!$method->isPublic() || $method->getName() !== $methodName)
		{
			throw new \yii\web\NotFoundHttpException("Not found JSON-RPC method {$requestedMethod}.");
		}
		
		return $method;
	}
	
	/**
	 * Creates and initializes array of controller action method arguments
	 * @param \ReflectionMethod $reflectionMethod
	 * @param array $requestParams
	 * @return array
	 * @throws \yii\web\BadRequestHttpException
	 */
	protected function getMethodParams(\ReflectionMethod $reflectionMethod, $requestParams)
	{
		$actionParams = [];
		$methodParams = $reflectionMethod->getParameters();
		
		foreach ($methodParams as $param)
		{
			$paramName = $param->getName();
			if (!array_key_exists($paramName, $requestParams))
			{
				throw new \yii\web\BadRequestHttpException("Not found JSON-RPC request parameter '{$paramName}'.");
			}

			$paramClass = $param->getClass();
			$actionParams[$paramName] = !empty($paramClass) 
					? $this->getParameterModel($paramName, $paramClass->name, $requestParams)
					: $requestParams[$paramName];
		}
		
		return $actionParams;
	}
	
	/**
	 * Executes controller action method
	 * @param string $methodName
	 * @param array $methodParams
	 * @return mixed
	 */
	protected function runControllerAction($methodName, $methodParams)
	{
		return call_user_func_array([$this->controller, $methodName], $methodParams);
	}
	
	/**
	 * Creates and validates model for controller action argument
	 * @param string $paramName
	 * @param string $modelClassName
	 * @param array $requestParams
	 * @return \yii\base\Model
	 * @throws \yii\base\InvalidParamException
	 * @throws \yii\web\BadRequestHttpException
	 */
	protected function getParameterModel($paramName, $modelClassName, $requestParams)
	{
		$paramModel = new $modelClassName;

		if (!$paramModel instanceof \yii\base\Model)
		{
			throw new \yii\base\InvalidParamException("Parameter '{$paramName}' must be instance of \yii\base\Model");
		}

		if (in_array($this->scenario, $paramModel->scenarios()))
		{
			$paramModel->scenario = $this->scenario;
			$paramModel->setAttributes($requestParams[$paramName], true);
		}
		else
		{
			$paramModel->setAttributes($requestParams[$paramName], false);
		}

		if (!$paramModel->validate())
		{
			throw new \yii\web\BadRequestHttpException('Invalid JSON-RPC request parameters: ' . json_encode($paramModel->getErrors()));
		}
		
		return $paramModel;
	}
	
}
