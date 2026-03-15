<?php

namespace addons\wechat\library;

use addons\signin\model\Signin;
use addons\third\model\Third;
use app\common\model\User;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use fast\Date;
use fast\Http;
use fast\Random;
use think\Session;
use think\Config;

/**
 * 微信服务类
 */
class Wechat
{
    protected $appList = [];

    public static function getAppList()
    {
        $appList = [];
        $dirList = scandir(__DIR__ . '/response');
        foreach ($dirList as $key => $value) {
            if (in_array($value, ['.', '..'])) {
                unset($dirList[$key]);
            } else {
                $name = substr($value, 0, -4);
                $className = '\\addons\\wechat\\library\\response\\' . $name;
                $appList[strtolower($name)] = new $className();
            }
        }
        return $appList;
    }

    public static function appConfig()
    {
        $appConfigList = [];
        $appList = self::getAppList();
        foreach ($appList as $key => $value) {
            if (method_exists($value, 'config')) {
                $appConfigList[$key] = $value->config();
                $appConfigList[$key]['available'] = $value->available();
                $appConfigList[$key]['name'] .= ($appConfigList[$key]['available'] ? '' : '(未安装或未启用)');
            }
        }
        return $appConfigList;
    }

    /**
     * 应用交互
     * @return array|bool|mixed|string
     */
    public function response($obj, $openid, $message, $content, $context, $matches = null)
    {
        if (isset($content['app'])) {
            $response = '';
            $entry = null;
            $keyword = isset($content['searchregexindex']) && $content['searchregexindex'] > -1 && $matches && isset($matches[$content['searchregexindex']])
                ? $matches[$content['searchregexindex']] : $message;
            $appList = self::getAppList();
            if (isset($appList[$content['app']])) {
                $app = $appList[$content['app']];
                if (method_exists($app, 'response')) {
                    $entry = $app->response($obj, $openid, $message, $content, $context, $matches, $keyword);
                }
            }
            if ($entry) {
                if (is_string($entry)) {
                    return $entry;
                }
                if ($entry instanceof News) {
                    $news = $entry;
                } elseif ($entry instanceof NewsItem) {
                    $news = new News([$entry]);
                } elseif (is_array($entry) || $entry instanceof \think\Model) {
                    if (isset($entry['title']) || isset($entry['description'])) {
                        $items = [
                            new NewsItem([
                                'title'       => $entry['title'] ?? ($entry['nickname'] ?? ''),
                                'description' => $entry['description'] ?? '',
                                'url'         => $entry['fullurl'],
                                'image'       => cdnurl($entry['image'], true),
                            ]),
                        ];
                        $news = new News($items);
                    } else {
                        $items = [];
                        foreach ($entry as $item) {
                            if ($item instanceof NewsItem) {
                                $items[] = $item;
                            } else {
                                $items[] = new NewsItem([
                                    'title'       => $item['title'] ?? ($item['nickname'] ?? ''),
                                    'description' => $item['description'] ?? '',
                                    'url'         => $item['fullurl'],
                                    'image'       => cdnurl($item['image'], true),
                                ]);
                            }
                        }
                        $news = new News($items);
                    }
                }

                if (isset($news)) {
                    $response = $news;
                }
            }
        } else {
            $response = $content['content'] ?? '';
        }
        return $response;
    }

    /**
     * 获取Token
     */
    public static function getAccessToken()
    {
        $app = Factory::officialAccount(\addons\wechat\library\Config::load());
        $accessToken = $app->access_token;
        $token = $accessToken ? $accessToken->getToken() : [];
        return $token['access_token'] ?? '';
    }
}
