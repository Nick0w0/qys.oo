<?php
namespace addons\discover;

use app\common\library\Menu;
use think\Addons;

class Discover extends Addons
{
    public function install()
    {
        $menu = [];
        $config_file = ADDON_PATH . 'discover' . DS . 'config' . DS . 'menu.php';
        if (is_file($config_file)) {
            $menu = include $config_file;
        }
        if ($menu) {
            Menu::create($menu);
        }
        return true;
    }

    public function uninstall()
    {
        $info = get_addon_info('discover');
        Menu::delete(isset($info['first_menu']) ? $info['first_menu'] : 'discover');
        return true;
    }

    public function enable()
    {
        $menu = [];
        $config_file = ADDON_PATH . 'discover' . DS . 'config' . DS . 'menu.php';
        if (is_file($config_file)) {
            $menu = include $config_file;
        }
        if ($menu) {
            Menu::upgrade('discover', $menu);
        }
        $info = get_addon_info('discover');
        Menu::enable(isset($info['first_menu']) ? $info['first_menu'] : 'discover');
    }

    public function disable()
    {
        $info = get_addon_info('discover');
        Menu::disable(isset($info['first_menu']) ? $info['first_menu'] : 'discover');
    }
}
