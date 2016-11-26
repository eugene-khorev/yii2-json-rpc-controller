<?php

/**
 * JSON-RPC HttpBearerAuth class file
 * @author Eugene Khorev <eugene.khorev@gmail.com>
 */

namespace jsonrpc;

/**
 * JSON RPC HttpBearerAuth is an action filter that supports the authentication method based on HTTP Bearer token.
 */
class HttpBearerAuth extends \yii\filters\auth\HttpBearerAuth
{

    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $ex = new \yii\web\UnauthorizedHttpException('Your request was made with invalid credentials.', 403);
        
        $response->data = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'data' => YII_DEBUG ? $ex->getTraceAsString() : null,
            ]
        ];
    }

}
