<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-27 20:26:30
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2023-07-18 16:46:34
 */

namespace diandi\admin\components;

use common\helpers\loggingHelper;
use diandi\admin\models\Menu;
use Yii;
use yii\caching\TagDependency;

/**
 * MenuHelper used to generate menu depend of user role.
 * Usage.
 *
 * ```
 * use diandi\admin\components\MenuHelper;
 * use yii\bootstrap\Nav;
 *
 * echo Nav::widget([
 *    'items' => MenuHelper::getAssignedMenu(Yii::$app->user->id)
 * ]);
 * ```
 *
 * To reformat returned, provide callback to method.
 *
 * ```
 * $callback = function ($menu) {
 *    $data = eval($menu['data']);
 *    return [
 *        'label' => $menu['name'],
 *        'url' => [$menu['route']],
 *        'options' => $data,
 *        'items' => $menu['children']
 *        ]
 *    ]
 * }
 *
 * $items = MenuHelper::getAssignedMenu(Yii::$app->user->id, null, $callback);
 * ```
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class MenuHelper
{
    /**
     * Use to get assigned menu of user.
     *
     * @param mixed    $userId
     * @param int      $root
     * @param \Closure $callback use to reformat output.
     *                           callback should have format like
     *
     * ```
     * function ($menu) {
     *    return [
     *        'label' => $menu['name'],
     *        'url' => [$menu['route']],
     *        'options' => $data,
     *        'items' => $menu['children']
     *        ]
     *    ]
     * }
     * ```
     * @param bool $refresh
     *
     * @return array
     */
    public static function getAssignedMenu($userId, $root = null, $callback = null, $menuwhere = ['is_sys' => 1], $refresh = false)
    {
        $config = Configs::instance();
        /* @var $manager \yii\rbac\BaseManager */
        $manager = $config::authManager();

        $menus = Menu::find()->where($menuwhere)->orderBy('order')->asArray()->indexBy('id')->all();
        $module_name = !empty($menuwhere['module_name']) ? $menuwhere['module_name'] : '';
        $key = [__METHOD__, $userId, $module_name, $manager->defaultRoles];
        $cache = $config->cache;

        if ($refresh || $cache === null || ($assigned = $cache->get($key)) === false) {
            $routes = $filter1 = $filter2 = [];

            if ($userId !== null) {
                // 获取所有的权限
                foreach ($manager->getPermissionsByUser($userId) as $name => $value) {
                    if ($name[0] === '/') {
                        if (substr($name, -2) === '/*') {
                            $name = substr($name, 0, -1);
                        }
                        $routes[] = $name;
                    }
                }
            }
            foreach ($manager->defaultRoles as $role) {
                foreach ($manager->getPermissionsByRole($role) as $name => $value) {
                    if ($name[0] === '/') {
                        if (substr($name, -2) === '/*') {
                            $name = substr($name, 0, -1);
                        }
                        $routes[] = $name;
                    }
                }
            }
            $routes = array_unique($routes);

            sort($routes);
            $prefix = '\\';

            foreach ($routes as $route) {
                if (strpos($route, $prefix) !== 0) {
                    if (substr($route, -1) === '/') {
                        $prefix = $route;
                        $filter1[] = $route . '%';
                    } else {
                        $filter2[] = $route;
                    }
                }
            }
            $assigned = [];
            $query = Menu::find()->select(['id'])->orderBy('order')->asArray();

            if (count($filter2)) {
                $assigned = $query->where(['route' => $filter2])->andWhere($menuwhere)->column();
            }
            if (count($filter1)) {
                $query->where('route like :filter')->andWhere($menuwhere);
                foreach ($filter1 as $filter) {
                    $assigned = array_merge($assigned, $query->params([':filter' => $filter])->column());
                }
            }

            $assigned = static::requiredParent($assigned, $menus);

            if ($cache !== null) {
                $cache->set($key, $assigned, $config->cacheDuration, new TagDependency([
                    'tags' => Configs::CACHE_TAG,
                ]));
            }
        }

        $key = [__METHOD__, $assigned, $root];

        if ($refresh || $callback !== null || $cache === null || (($result = $cache->get($key)) === false)) {
            $result = static::normalizeMenu($assigned, $menus, $callback, $root);

            if ($cache !== null && $callback === null) {
                $cache->set($key, $result, $config->cacheDuration, new TagDependency([
                    'tags' => Configs::CACHE_TAG,
                ]));
            }
        }

        return $result;
    }

    /**
     * Ensure all item menu has parent.
     *
     * @param array $assigned
     * @param array $menus
     *
     * @return array
     */
    private static function requiredParent($assigned, &$menus)
    {
        $l = count($assigned);

        for ($i = 0; $i < $l; ++$i) {
            $id = $assigned[$i];
            $parent_id = isset($menus[$id]) ? $menus[$id]['parent'] : '';
            if (!empty($parent_id) && !in_array($parent_id, $assigned)) {
                $assigned[$l++] = $parent_id;
            }
        }

        return $assigned;
    }

    /**
     * Parse route.
     *
     * @param string $route
     *
     * @return mixed
     */
    public static function parseRoute($route)
    {
        if (!empty($route)) {
            $url = [];
            $r = explode('&', $route);
            $url[0] = $r[0];
            unset($r[0]);
            foreach ($r as $part) {
                $part = explode('=', $part);
                $url[$part[0]] = isset($part[1]) ? $part[1] : '';
            }

            return $url;
        }

        return '#';
    }

    /**
     * Normalize menu.
     *
     * @param array   $assigned
     * @param array   $menus
     * @param Closure $callback
     * @param int     $parent
     *
     * @return array
     */
    private static function normalizeMenu(&$assigned, &$menus, $callback, $parent = 0)
    {
        $result = [];
        $order = [];
        foreach ($assigned as $id) {
            if (!array_key_exists($id,$menus) || empty($menus[$id])) {
                continue;
            }
            $menu = $menus[$id];
            if (!empty($menu) && $menu['parent'] == $parent) {
                loggingHelper::writeLog('menuhelp', 'normalizeMenu', '哪里的问题', [$parent]);
                $menu['children'] = static::normalizeMenu($assigned, $menus, $callback, $id);
                if (!empty($callback)) {
                    $item = call_user_func($callback, $menu);
                } else {
                    $item = [
                        'label' => $menu['name'],
                        'url' => static::parseRoute($menu['route']),
                    ];
                    if ($menu['children'] != []) {
                        $item['items'] = $menu['children'];
                    }
                }
                if ($item) {
                    $result[] = $item;
                }
            }
        }

        return $result;
    }
}
