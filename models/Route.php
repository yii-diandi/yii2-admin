<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-27 18:10:43
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-08-22 00:16:33
 */

namespace diandi\admin\models;

use Exception;
use diandi\admin\components\Configs;
use diandi\admin\components\Helper;
use diandi\admin\components\RouteRule;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\VarDumper;

/**
 * Description of Route.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class Route extends \diandi\admin\BaseObject
{
    const CACHE_TAG = 'diandi.admin.route';

    const PREFIX_ADVANCED = '@';
    const PREFIX_BASIC = '/';

    private $_routePrefix;

    public $id;
    public $pid;

    public $module_name;
    /**
     * @var int the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     */
    public $type;
    /**
     * @var string the name of the item. This must be globally unique.
     */
    public $name;

    public $title;
    /**
     * @var string the item description
     */
    public $description;
    /**
     * @var int UNIX timestamp representing the item creation time
     */
    public $createdAt;
    /**
     * @var int UNIX timestamp representing the item updating time
     */
    public $updatedAt;
    /**
     * @var mixed the additional data associated with this item
     */
    public $data;

    /**
     * @var Item
     */
    private $_item;

    /**
     * Initialize object.
     *
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->data = $item->data;
            $this->pid = $item->pid;
            $this->title = $item->title;
            $this->id = $item->id;
            $this->name = $item->name;
            $this->module_name = $item->module_name;
            $this->type = $item->type;
            $this->description = $item->description;
        }
        parent::__construct($config);
    }

    /**
     * Assign or remove items.
     *
     * @param array $routes
     *
     * @return array
     */
    public function addNew($routes)
    {
        $manager = Configs::authManager();
        foreach ($routes as $route) {
            try {
                $r = explode('&', $route);
                $item = $manager->createRoutePermission($this->getPermissionName($route));

                if (count($r) > 1) {
                    $action = '/'.trim($r[0], '/');
                    if (($itemAction = $manager->getRoutePermission($action)) === null) {
                        $itemAction = $manager->createRoutePermission($action);
                        $manager->add($itemAction);
                    }
                    unset($r[0]);
                    foreach ($r as $part) {
                        $part = explode('=', $part);
                        $item->data['params'][$part[0]] = isset($part[1]) ? $part[1] : '';
                    }
                    $this->setDefaultRule();

                    $manager->add($item);
                    $manager->addChild($item, $itemAction);
                } else {
                    $manager->add($item);
                }
            } catch (Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
    }

    /**
     * Assign or remove items.
     *
     * @param array $routes
     *
     * @return array
     */
    public function remove($routes)
    {
        $manager = Configs::authManager();
        foreach ($routes as $route) {
            try {
                $item = $manager->createRoutePermission($this->getPermissionName($route));
                $manager->remove($item);
            } catch (Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
    }

    /**
     * Returns route prefix depending on the configuration.
     *
     * @return string Route prefix
     */
    public function getRoutePrefix()
    {
        if (!$this->_routePrefix) {
            $this->_routePrefix = Configs::instance()->advanced ? self::PREFIX_ADVANCED : self::PREFIX_BASIC;
        }

        return $this->_routePrefix;
    }

    /**
     * Returns the correct permission name depending on the configuration.
     *
     * @param string $route Route
     *
     * @return string Permission name
     */
    public function getPermissionName($route)
    {
        if (self::PREFIX_BASIC == $this->routePrefix) {
            return self::PREFIX_BASIC.trim($route, self::PREFIX_BASIC);
        } else {
            return self::PREFIX_ADVANCED.ltrim(trim($route, self::PREFIX_BASIC), self::PREFIX_ADVANCED);
        }
    }

    /**
     * Get available and assigned routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        $manager = Configs::authManager();
        // Get advanced configuration
        $advanced = Configs::instance()->advanced;
        if ($advanced) {
            // Use advanced route scheme.
            // Set advanced route prefix.
            $this->_routePrefix = self::PREFIX_ADVANCED;
            // Create empty routes array.
            $routes = [];
            // Save original app.
            $yiiApp = Yii::$app;
            // Step through each configured application
            foreach ($advanced as $id => $configPaths) {
                // Force correct id string.
                $id = $this->routePrefix.ltrim(trim($id), $this->routePrefix);
                // Create empty config array.
                $config = [];
                // Assemble configuration for current app.
                foreach ($configPaths as $configPath) {
                    // Merge every new configuration with the old config array.
                    $config = yii\helpers\ArrayHelper::merge($config, require(Yii::getAlias($configPath)));
                }
                // Create new app using the config array.
                unset($config['bootstrap']);
                $app = new yii\web\Application($config);
                // Get all the routes of the newly created app.
                $r = $this->getAppRoutes($app);
                // Dump new app
                unset($app);
                // Prepend the app id to all routes.
                foreach ($r as $route) {
                    $routes[$id.$route] = $id.$route;
                }
            }
            // Switch back to original app.
            Yii::$app = $yiiApp;
            unset($yiiApp);
        } else {
            // Use basic route scheme.
            // Set basic route prefix
            $this->_routePrefix = self::PREFIX_BASIC;
            // Get basic app routes.
            $routes = $this->getAppRoutes();
        }
        $exists = [];
        // 获取当前类型--系统:0  模块:1 全部:2
        foreach (array_keys($manager->getRoutePermissions(2)) as $name) {
            if ($name[0] !== $this->routePrefix) {
                continue;
            }
            $exists[] = $name;
            unset($routes[$name]);
        }
        return [
            'available' => array_keys($routes),
            'assigned' => $exists,
        ];
    }

    /**
     * Get list of application routes.
     *
     * @return array
     */
    public function getAppRoutes($module = null)
    {
        if ($module === null) {
            $module = Yii::$app;
        } elseif (is_string($module)) {
            $module = Yii::$app->getModule($module);
        }
        $key = [__METHOD__, Yii::$app->id, $module->getUniqueId()];
        $cache = Configs::instance()->cache;
        
        if ($cache === null || ($result = $cache->get($key)) === false) {
            $result = [];
            $this->getRouteRecursive($module, $result);

            if ($cache !== null) {
                $cache->set($key, $result, Configs::instance()->cacheDuration, new TagDependency([
                    'tags' => self::CACHE_TAG,
                ]));
            }
        }

        return $result;
    }

    /**
     * Get route(s) recursive.
     *
     * @param \yii\base\Module $module
     * @param array            $result
     */
    protected function getRouteRecursive($module, &$result)
    {
        $token = "Get Route of '".get_class($module)."' with id '".$module->uniqueId."'";
        Yii::beginProfile($token, __METHOD__);
        try {
            foreach ($module->getModules() as $id => $child) {
                if (($child = $module->getModule($id)) !== null) {
                    $this->getRouteRecursive($child, $result);
                }
            }

            foreach ($module->controllerMap as $id => $type) {
                $this->getControllerActions($type, $id, $module, $result);
            }
            $namespace = trim($module->controllerNamespace, '\\').'\\';

            $this->getControllerFiles($module, $namespace, '', $result);

            $all = '/'.ltrim($module->uniqueId.'/*', '/');
            $result[$all] = $all;
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get list controllers under module.
     *
     * @param \yii\base\Module $module
     * @param string           $namespace
     * @param string           $prefix
     * @param mixed            $result
     *
     * @return mixed
     */
    protected function getControllerFiles($module, $namespace, $prefix, &$result)
    {
        $path = Yii::getAlias('@'.str_replace('\\', '/', $namespace), false);
        $token = "Get controllers from '$path'";
        Yii::beginProfile($token, __METHOD__);
        try {
            if (!is_dir($path)) {
                return;
            }
            foreach (scandir($path) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_dir($path.'/'.$file) && preg_match('%^[a-z0-9_/]+$%i', $file.'/')) {
                    $this->getControllerFiles($module, $namespace.$file.'\\', $prefix.$file.'/', $result);
                } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                    $baseName = substr(basename($file), 0, -14);
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $baseName));
                    $id = ltrim(str_replace(' ', '-', $name), '-');
                    $className = $namespace.$baseName.'Controller';
                    if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
                        $this->getControllerActions($className, $prefix.$id, $module, $result);
                    }
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get list action of controllers.
     *
     * @param mixed            $type
     * @param string           $id
     * @param \yii\base\Module $module
     * @param string           $result
     */
    protected function getControllerActions($type, $id, $module, &$result)
    {
        $token = 'Create controllers with cofig='.VarDumper::dumpAsString($type)." and id='$id'";
        Yii::beginProfile($token, __METHOD__);
        try {
            /* @var $controller \yii\base\Controller */
            $controller = Yii::createObject($type, [$id, $module]);
            $this->getActionRoutes($controller, $result);
            $all = "/{$controller->uniqueId}/*";
            $result[$all] = $all;
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Get route of action.
     *
     * @param \yii\base\Controller $controller
     * @param array                $result     all controllers action
     */
    protected function getActionRoutes($controller, &$result)
    {
        $token = "Get actions of controllers '".$controller->uniqueId."'";
        Yii::beginProfile($token, __METHOD__);
        try {
            $prefix = '/'.$controller->uniqueId.'/';
            foreach ($controller->actions() as $id => $value) {
                $result[$prefix.$id] = $prefix.$id;
            }
            $class = new \ReflectionClass($controller);
            foreach ($class->getMethods() as $method) {
                $name = $method->getName();
                if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                    $name = strtolower(preg_replace('/(?<![A-Z])[A-Z]/', ' \0', substr($name, 6)));
                    $id = $prefix.ltrim(str_replace(' ', '-', $name), '-');
                    $result[$id] = $id;
                }
            }
        } catch (\Exception $exc) {
            Yii::error($exc->getMessage(), __METHOD__);
        }
        Yii::endProfile($token, __METHOD__);
    }

    /**
     * Adds an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function addChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item) {
            foreach ($items as $name) {
                $child = $manager->getRoutePermission($name);
                try {
                    $manager->addChild($this->_item, $child);
                    ++$success;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    /**
     * Remove an item as a child of another item.
     *
     * @param array $items
     *
     * @return int
     */
    public function removeChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $manager->getRoutePermission($name);
                try {
                    $manager->removeChild($this->_item, $child);
                    ++$success;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }

        return $success;
    }

    /**
     * Ivalidate cache.
     */
    public static function invalidate()
    {
        if (Configs::cache() !== null) {
            TagDependency::invalidate(Configs::cache(), self::CACHE_TAG);
        }
    }

    /**
     * Set default rule of parameterize route.
     */
    protected function setDefaultRule()
    {
        if (Configs::authManager()->getRule(RouteRule::RULE_NAME) === null) {
            Configs::authManager()->add(new RouteRule());
        }
    }
}
