Basic usage
===========

1) Change your request component class in configuration to `\jsonrpc\Request`:

```
   'components' => [
		...
        'request' => [
            'class' => 'jsonrpc\Request',
            'cookieValidationKey' => '...',
        ],
		...
	]
```

2) Create a new controller that extends `\jsonrpc\Controller`:

```
class SomeController extends \jsonrpc\Controller
{
	/**
	 * This is regular controller action
	 */
	public function actionIndex()
	{
		return $this->render('index');
	}

	/**
	 * This JSON-RPC controller action
	 */
	public function rpcEcho($param1, $param2)
	{
		return ['recievedData' => ['param1' => $param1, 'param1' => $param1]];
	}
}
```

Now if you post this JSON-RPC request to `/some/rpc`:

```
{
	"jsonrpc": "2.0", 
	"method": "echo", 
	"params": { 
		"param1": "abc",
		"param2", "123"
	}
}
```

You get the following result

```
{
	"jsonrpc": "2.0",
	"id": 1,
	"result": {
		"recievedData": {
			"login": "test1",
			"password": "testpassword1"
		}
	}
},
```

Anf if you navigate your browser to `/some/index` you get regular `\Yii2\web\Controller` behavoir.