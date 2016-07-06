<?php

require('../vendor/autoload.php');
// define('API_KEY', 'DocomoAPIKey');
// require_once(__DIR__ . '/vendor/autoload.php');
// use jp3cki\docomoDialogue\Dialogue;
// $dialog = new Dialogue(API_KEY);


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->before(function (Request $request) use($bot) {
    // TODO validation
});

$app->get('/callback', function (Request $request) use ($app) {
    $response = "";
    if ($request->query->get('hub_verify_token') === getenv('FACEBOOK_PAGE_VERIFY_TOKEN')) {
        $response = $request->query->get('hub_challenge');
    }

    return $response;
});

$app->post('/callback', function (Request $request) use ($app) {
    // Let's hack from here!
    $body = json_decode($request->getContent(), true);
    $client = new Client(['base_uri' => 'https://graph.facebook.com/v2.6/']);

    foreach ($body['entry'] as $obj) {
        $app['monolog']->addInfo(sprintf('obj: %s', json_encode($obj)));

        foreach ($obj['messaging'] as $m) {
            $app['monolog']->addInfo(sprintf('messaging: %s', json_encode($m)));
            $from = $m['sender']['id'];
			
			// 相手からのメッセージをtextに代入
            $text = $m['message']['text'];
			
			//Docomo
	        // 送信パラメータの準備
	        // $dialog->parameter->reset();
// 	        $dialog->parameter->utt = $text;
// 	        $dialog->parameter->context = $context;
// 	        $dialog->parameter->mode = $mode;
// 	        $ret = $dialog->request();
// 	        if($ret === false) {
// 	            $text = "通信に失敗しました";
// 	        }
// 	        $text = $ret->utt;

            if ($text) {
                $path = sprintf('me/messages?access_token=%s', getenv('FACEBOOK_PAGE_ACCESS_TOKEN'));
                $json = [
                    'recipient' => [
                        'id' => $from, 
                    ],
                    'message' => [
                        'text' => sprintf('%sなのだよ', $text), 
                    ],
                ];
                $client->request('POST', $path, ['json' => $json]);
            }
        }

    }

    return 0;
});

$app->run();
