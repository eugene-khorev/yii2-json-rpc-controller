<?php

namespace jsonrpc;

class ValidationException extends \yii\base\UserException
{

    protected $data;
    
    public function __construct($message = "", $code = 0, $data = [], Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    public function getData()
    {
        $result = $this->data;
        if (YII_DEBUG) {
            $result['debug'] = $this->getTraceAsString();
        }
        return $result;
    }
    
}