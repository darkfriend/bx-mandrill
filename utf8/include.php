<?php
/**
 * @author dev2fun (darkfriend)
 * @copyright darkfriend
 * @version 1.0.0
 */

namespace Dev2fun\Mandrill;

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

IncludeModuleLangFile(__FILE__);
include_once __DIR__.'/vendor/autoload.php';

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use darkfriend\helpers\CurlHelper;

Loader::registerAutoLoadClasses(
    'dev2fun.mandrill',
    [
        'Dev2fun\Mandrill\Base' => __FILE__,
        'Dev2fun\Mandrill\Config' => 'classes/general/Config.php',
    ]
);

class Base
{
    public static $module_id = 'dev2fun.mandrill';
    public static $url = 'https://mandrillapp.com/api/1.0/messages/send.json';

    public static function getOption($name, $default='')
    {
        return Option::get(self::$module_id, $name, $default);
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return bool|\Bitrix\Main\Event
     */
    public static function OnBeforeMailSend($event)
    {
        if(self::getOption('enabled', 'Y') !== 'Y') {
            return false;
        }
        if(!self::getOption('apiKey', '')) {
            return false;
        }

        $params = \array_shift($event->getParameters());

//        if(
//            isset($params['HEADER']['X-EVENT_NAME'])
//            && strpos($params['HEADER']['X-Priority'], 'Highest') === false
//        ) {
//            return $event;
//        }

        $args = [
            'subject' => $params['SUBJECT'],
            'to' => [
                [
                    'email' => $params['TO'],
                ]
            ],
            'track_opens' => self::getOption('trackOpens', 'Y') === 'Y',
            'track_clicks' => self::getOption('trackClicks', 'Y') === 'Y',
        ];

        if(
            isset($params['HEADER']['X-Priority'])
            && strpos($params['HEADER']['X-Priority'], 'Highest') !== false
        ) {
            $args['important'] = true;
        }

        if($params['CONTENT_TYPE']=='text') {
            $args['text'] = $params['BODY'];
            $args['html'] = null;
        } else {
            $args['html'] = $params['BODY'];
            $args['text'] = null;
        }
        $args['from_email'] = $params['HEADER']['From'];
        $args['from_name'] = self::getOption('fromName', '');

        if(!empty($params['BCC'])) {
            $args['bcc_address'] = $params['BCC'];
        }

        if(!empty($params['ATTACHMENT'])) {
            foreach ($params['ATTACHMENT'] as $file) {
                $args['attachments'][] = [
                    'name' => $file['NAME'],
                    'content' => self::getBase64($file['PATH']),
                    'type' => $file['CONTENT_TYPE'],
                ];
            }
        }

        $result = self::send($args);

        \CEventLog::Add([
            'SEVERITY' => 'INFO',
            'AUDIT_TYPE_ID' => 'MailChimp',
            'MODULE_ID' => self::$module_id,
            'DESCRIPTION' => print_r($result,true),
        ]);

//        $event->addResult(new \Bitrix\Main\EventResult(
//            \Bitrix\Main\EventResult::ERROR,
//            [
//                'TO' => '',
//                'Успешно отправлено',
//            ]
//        ));

        if(isset($result['status']) && $result['status']=='error') {
            return false;
        }

        if(!defined('ONLY_EMAIL')) {
            define('ONLY_EMAIL', 'Y');
        }
        $event->addResult(new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                'TO' => ONLY_EMAIL,
                'RESULT' => 'SUCCESS',
            ],
            self::$module_id
        ));

        return $event;
    }

    public static function getBase64($path)
    {
        $data = \file_get_contents($path);
        return \base64_encode($data);
    }

    public static function send($args)
    {
        $curl = CurlHelper::getInstance(true);
        return $curl->request(
            self::$url,
            [
                'key' => self::getOption('apiKey', ''),
                'message' => $args,
            ],
            'post',
            'json',
            'json'
        );
    }
}