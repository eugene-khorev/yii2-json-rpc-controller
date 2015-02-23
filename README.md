Basic usage
===========

Change your request component class in configuration to `\jsonrpc\Request`:

```php
   'components' => [
		...
        'request' => [
            'class' => 'jsonrpc\Request',
            'cookieValidationKey' => '...',
        ],
		...
	]
```

Create a new controller that extends `\jsonrpc\Controller`:

```php
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
	 * This is JSON-RPC 2.0 controller action
	 */
	public function rpcEcho($param1, $param2)
	{
		return ['recievedData' => ['param1' => $param1, 'param1' => $param1]];
	}
}
```

Now if you post this JSON-RPC 2.0 request to `/some/rpc`:

```json
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

```json
{
	"jsonrpc": "2.0",
	"id": 1,
	"result": {
		"recievedData": {
			"param1": "abc",
			"param2": "123"
		}
	}
}
```

And if you navigate your browser to `/some/index` you get regular `\Yii2\web\Controller` behavoir.

Advanced usage
==============

Create a new model:

```php
class SomeModel extends yii\base\Model
{
	public $value1;
	public $value2;
}
```

Make changes to you RPC controller action so it looks like this:

```php
	public function rpcEcho($param1, SomeModel $modelParam)
	{
		return ['recievedData' => [
					'param1' => $param1, 
					'modelParam' => $modelParam->attributes
		]];
	}
```

Now if you post this JSON-RPC 2.0 request to `/some/rpc`:

```json
{
	"jsonrpc": "2.0",
	"id": 1,
	"params": {
		"param1": "abc",
		"modelParam": {
			"value1": "123",
			"value2": "321",
		}
	}
}
```

You get valid `$modelParam` model in your method. The library uses `rpc` or `default` scenario to validate parameter models. 
If validation fails a client recieves correct JSON-RPC 2.0 answer containing validation error, but your RPC action method doesn't even run.

You can also use batch JSON-RPC 2.0 requests:

```json
[
	{"jsonrpc": "2.0", "method": "some-method", "params": { "somethinf": "anything"}, "id": 1},
	{"jsonrpc": "2.0", "method": "another-method", "params": { "data": [1, 2, 3, 4] }, "id": 2}
]
```

In this case two controller methods `rpcSomeMethod` and `rpcAnotherMethod` will be called sequentially.
And the client will recieve the correct batch results event if there are errors occurred in one of the methods.
