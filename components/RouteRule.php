<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 16:37:42
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-11 00:04:42
 */
 

namespace diandi\admin\components;

use Yii;
use yii\rbac\Rule;

/**
 * RouteRule Rule for check route with extra params.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class RouteRule extends Rule
{
    const RULE_NAME = 'route_rule';

    /**
     * @inheritdoc
     */
    public $name = self::RULE_NAME;

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        $routeParams = isset($item->data['params']) ? $item->data['params'] : [];
         foreach ($routeParams as $key => $value) {
            if (!array_key_exists($key, $params) || $params[$key] != $value) {
                return false;
            }
        }
        return true;
    }
}
