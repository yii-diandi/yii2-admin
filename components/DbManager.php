<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 19:56:41
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2023-07-18 16:45:18
 */

namespace diandi\admin\components;

use common\helpers\ErrorsHelper;
use common\helpers\loggingHelper;
use diandi\admin\acmodels\AuthItem;
use diandi\admin\acmodels\AuthItemChild;
use diandi\admin\acmodels\AuthRoute;
use diandi\admin\acmodels\AuthUserGroup;
use diandi\admin\models\AuthAssignmentGroup;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Query;
use yii\rbac\Rule;

/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[$db]]. The database schema could be initialized by applying migration:
 *
 * ```
 * yii migrate --migrationPath=@yii/rbac/migrations/
 * ```
 *
 * If you don't want to use migration and need SQL instead, files for all databases are in migrations directory.
 *
 * You may change the names of the three tables used to store the authorization data by setting [[\yii\rbac\DbManager::$itemTable]],
 * [[\yii\rbac\DbManager::$itemChildTable]] and [[\yii\rbac\DbManager::$assignmentTable]].
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class DbManager extends \yii\rbac\DbManager
{
    /**
     * Memory cache of assignments.
     *
     * @var array
     */
    private $_assignments = [];

    private $_childrenList;

    public $routes;

    public $routeTable = '{{%auth_route}}';

    public $groupTable = '{{%auth_user_group}}';

    public $assignmentGroupTable = '{{%auth_assignment_group}}';

    private $_checkAccessAssignments = [];

    // 权限类型
    public $auth_type = [
        // 路由
        0 => 'route',
        // 权限
        1 => 'permission',
        // 用户组
        2 => 'role',
    ];

    /**
     * item->item-child :权限库
     * user-group:用户组
     * route：路由
     * menu:菜单
     * auth_rule：规则
     * assignment：权限分配.
     */
    public function loadFromCache()
    {
        if ($this->items !== null || !$this->cache instanceof CacheInterface) {
            return;
        }
        $data = $this->cache->get($this->cacheKey);
        if (is_array($data) && isset($data[0], $data[1], $data[2])) {
            list($this->items, $this->rules, $this->parents) = $data;

            return;
        }

        $query = (new Query())->from($this->itemTable);
        $this->items = [];
        foreach ($query->all($this->db) as $row) {
            $this->items[$row['name']] = $this->populateItem($row, 'itemTable');
        }

        $query = (new Query())->from($this->routeTable);
        $this->routes = [];
        foreach ($query->all($this->db) as $row) {
            $this->routes[$row['name']] = $this->populateRoute($row);
        }

        $query = (new Query())->from($this->ruleTable);
        $this->rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $this->rules[$row['name']] = unserialize($data);
        }

        $query = (new Query())->from($this->itemChildTable);
        $this->parents = [];
        foreach ($query->all($this->db) as $row) {
            if (isset($this->items[$row['child']])) {
                $this->parents[$row['child']][] = $row['parent'];
            }
        }

        $this->cache->set($this->cacheKey, [$this->items, $this->rules, $this->parents]);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($id)
    {
        // child_type:1 表示权限
        $list = AuthItem::find()->alias('a')->joinWith(['childs as c'])->where(['c.parent_id' => $id])->select(['name', 'a.id', 'a.description', 'rule_name', 'data', 'c.child', 'c.parent_id', 'created_at', 'updated_at', 'a.permission_type', 'a.permission_level', 'a.is_sys', 'a.data'])->all();
        $children = [];
        foreach ($list as $row) {
            $children[$row['id']] = $this->populateItem($row, 'itemTable');
        }

        return $children;
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent the parent item
     * @param Item $child  the child item to be added to the hierarchy
     *
     * @return bool whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        // 确定两者不存在相互包含的情况
        if ($child->name === $parent->name && $child->id === $parent->id) {
            return true;
        }

        foreach ($this->getChildren($child->id) as $grandchild) {
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取指定角色的权限.
     */
    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $query = (new Query())->from($this->routeTable)->where([
            'is_sys' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row, 'routeTable');
        }

        return $permissions;
    }

    /**
     * 获取所有路由.
     */
    public function getRoutePermissions($is_sys = 3, $module_name = '')
    {
        return $this->getRoutes($is_sys, $module_name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($is_sys = 3, $module_name = '')
    {
        $where = [];

        if (!empty($module_name)) {
            $where['module_name'] = $module_name;
        }
        if (in_array($is_sys, [0, 1], true)) {
            $where['is_sys'] = $is_sys;
        }
        $query = (new Query())
            ->from($this->routeTable)
            ->where($where);
        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateRoute($row);
        }

        return $items;
    }

    // 权限库汇总

    /**
     * Get items.
     *
     * @return array
     */
    public function getAuths($group_name, $is_sys = 3)
    {
        $available = [];
        $assigned = [];
        $auth_type = $this->auth_type;

        $all = [];

        $where = [];
        if (is_numeric($group_name)) {
            $where['item_id'] = $group_name;
        } else {
            $where['name'] = $group_name;
        }

        $groupId = AuthUserGroup::find()->where($where)->select('id')->scalar();

        // 用户组授权
        foreach ($this->getGroups($is_sys) as $name => $item) {
            $id = $item->item_id;
            $available['role'][$id] = $item;
        }

        // 权限授权
        foreach ($this->getPermissions($is_sys) as $id => $item) {
            $name = $item->name;
            $id = $item->item_id;
            if ($item->permission_type === 1) {
                $available['permission'][$id] = $item;
            }
        }

        // 路由授权

        foreach ($this->getRoutes($is_sys) as $name => $item) {
            $id = $item->item_id;

            $available['route'][$id] = $item;
        }

        $all = $available;

        foreach ($this->getChildren($groupId) as $item => $val) {
            $key = $auth_type[$val->permission_type];
            $id = $val->item_id;
            $assigned[$key][$id] = $val;

            unset($available[$key][$id]);
        }

        // 子权限授权
        foreach ($this->getItemChildren($group_name, $is_sys, 2) as $id => $item) {
            $key = $auth_type[$item->child_type];

            $assigned[$key][$item->item_id] = $item;

            unset($available[$key][$item->item_id]);
        }

        unset($available['role'][$group_name]);

        return [
            'all' => $all,
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * 获取权限子项.
     *
     * @param [type] $id          父级ID或name
     * @param int    $parent_type 父级类型  0:路由1：规则2：用户组  3权限
     *
     * @return array
     */
    public function getItemChildren($id, $is_sys = 3, $parent_type = 0)
    {
        $children = [];
        $where = [];
        if (in_array($is_sys, [0, 1], true)) {
            $where['c.is_sys'] = $is_sys;
        }

        //child_type: 0:route,1:permission,2:role
        switch ($parent_type) {
            case 0:
                // 路由
                // 获取route已授权
                $list = AuthRoute::find()->alias('r')->joinWith('childs as c')->where([
                    'c.item_id' => $id,
                    'parent_type' => $parent_type,
                ])->andWhere($where)->select(['c.child as name', 'r.is_sys', 'c.id', 'child_type', 'description', 'data', 'created_at', 'updated_at', 'c.item_id'])->asArray()->all();

                foreach ($list as $row) {
                    $children[$row['id']] = $this->populateItem($row, 'routeTable');
                }

                break;
            case 1:
                // 规则
                // 获取role已授权

                break;
            case 2:
                // 用户组
                $list = AuthUserGroup::find()->alias('u')->joinWith('childs as c')->where([
                    'u.item_id' => $id,
                    'parent_type' => $parent_type,
                ])->andWhere($where)->select(['c.child as name', 'u.type', 'c.id', 'c.item_id', 'child_type', 'description', 'created_at', 'updated_at', 'c.item_id'])->asArray()->all();

                foreach ($list as $row) {
                    $children[$row['id']] = $this->populateItem($row, 'groupTable');
                }

                break;
            case 3:
                // 权限
                // 获取权限已授权
                $list = AuthItem::find()->alias('p')->joinWith('childs as c')->where([
                    'c.item_id' => $id,
                    'parent_type' => $parent_type,
                ])->andWhere($where)->select(['p.permission_type', 'c.id', 'c.parent_id',  'c.child as name', 'item_id', 'child_type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])->indexBy('item_id')->asArray()->all();

                foreach ($list as $row) {
                    $children[$row['item_id']] = $this->populateItem($row, 'itemTable');
                }

                break;

            default:

                $list = AuthItem::find()->alias('p')->joinWith('childs as c')->where([
                    'c.item_id' => $id,
                    'parent_type' => $parent_type,
                ])->andWhere($where)->select(['p.type', 'c.id', 'c.parent_id',  'c.child as name', 'item_id', 'child_type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])->indexBy('item_id')->asArray()->all();
                foreach ($list as $row) {
                    $children[$row['item_id']] = $this->populateItem($row, 'itemTable');
                }

                break;
        }

        return $children;
    }

    public function getGroup($name, $is_sys = 3)
    {
        $where = [];

        if (is_numeric($name)) {
            $where['item_id'] = $name;
        } else {
            $where['name'] = $name;
        }

        if (in_array($is_sys, [0, 1], true)) {
            $where['is_sys'] = $is_sys;
        }

        $query = (new Query())
            ->from($this->groupTable)
            ->where($where);

        $item = $query->one($this->db);
        if ($item === false) {
            return null;
        }
        $items = $this->populateGroup($item);

        return $items;
    }

    public function getGroups($is_sys = 3)
    {
        $where = [];
        if (in_array($is_sys, [0, 1], true)) {
            $where['is_sys'] = $is_sys;
        }

        $query = (new Query())
            ->from($this->groupTable)
            ->where($where);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row, 'groupTable');
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeGroup($role, $userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return false;
        }

        unset($this->_checkAccessAssignments[(string) $userId]);

        return $this->db->createCommand()
            ->delete($this->assignmentGroupTable, ['user_id' => (string) $userId, 'item_name' => $role->name])
            ->execute() > 0;
    }

    /**
     * 获取权限路由子项.
     */
    public function getRoutePermission($name, $parent_type = 1)
    {
        $item = $this->getRoute($name, $parent_type);

        return $item;
    }

    protected function getRoute($name, $parent_type = 1)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->routes[$name])) {
            return $this->routes[$name];
        }

        $where = [];
        if (is_numeric($name)) {
            $where['item_id'] = $name;
        } else {
            $where['name'] = $name;
        }

        $row = (new Query())->from($this->routeTable)
            ->where($where)
            ->one($this->db);

        if ($row === false) {
            return null;
        }

        return $this->populateRoute($row, $parent_type);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions($is_sys = 3)
    {
        return $this->getItems($is_sys, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles($is_sys = 3)
    {
        return $this->getItems($is_sys, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function getRule($id)
    {
        // 替换为ID查询
        if ($this->rules !== null) {
            return isset($this->rules[$id]) ? $this->rules[$id] : null;
        }

        $row = (new Query())->select(['data'])
            ->from($this->ruleTable)
            ->where(['id' => $id])
            ->one($this->db);
        if ($row === false) {
            return null;
        }
        $data = $row['data'];
        if (is_resource($data)) {
            $data = stream_get_contents($data);
        }

        return unserialize($data);
    }

    public function getRules()
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $query = (new Query())->from($this->ruleTable);
        $rules = [];
        foreach ($query->all($this->db) as $row) {
            $data = $row['data'];
            if (is_resource($data)) {
                $data = stream_get_contents($data);
            }
            $rule = unserialize($data);
            $rules[$row['id']] = $rule;
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItems($is_sys = 3, $permission_type = 1)
    {
        $module_name = Yii::$app->request->get('module_name');

        $where = [];

        // 默认读取权限数据

        $where['permission_type'] = $permission_type;

        if (in_array($is_sys, [0, 1], true)) {
            $where['is_sys'] = $is_sys;
        }

        if (!empty($module_name)) {
            $where['module_name'] = $module_name;
        }

        $query = (new Query())
            ->from($this->itemTable)
            ->where($where);

        $items = [];

        foreach ($query->all($this->db) as $row) {
            $items[$row['id']] = $this->populateItem($row);
        }

        return $items;
    }

    /**
     * Populates an auth item with the data fetched from database.
     *
     * @param array $row the data from the auth item table
     *
     * @return Route the populated auth item instance (either Role or Permission)
     */
    protected function populateRoute($row, $parent_type = 1)
    {
        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }

        return new Route([
            'id' => $row['id'],
            'item_id' => $row['item_id'],
            'name' => $row['name'],
            'pid' => $row['pid'],
            'module_name' => $row['module_name'],
            'is_sys' => $row['is_sys'],
            'child_type' => 0,
            'parent_type' => $parent_type,
            'description' => $row['description'],
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function createPermission($name)
    {
        $permission = new Permission();
        $permission->name = $name;

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function createRoutePermission($name)
    {
        $permission = new Route();
        $permission->name = $name;
        if (strpos($name, '_') !== false) {
            $list = explode('/', $name);
            $permission->is_sys = 1;
            $permission->module_name = $list[1];
        } else {
            $permission->is_sys = 0;
        }

        $permission->pid = 0;

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function add($object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->addItem($object);
        } elseif ($object instanceof Route) {
            return $this->addRoute($object);
        } elseif ($object instanceof Rule) {
            return $this->addRule($object);
        }

        throw new InvalidArgumentException('Adding unsupported object type.');
    }

    /**
     * Undocumented function.
     *
     * @param [type] $is_sys      是否是系统
     * @param [type] $module_name 系统为sys 非系统为模块英文标识
     *
     * @return void
     */
    public function getParentItem($is_sys = 3, $module_name = '')
    {
        $where = [];
        if (in_array($is_sys, [0, 1], true)) {
            $where['is_sys'] = $is_sys;
        }

        $where['parent_id'] = 0;

        if (!empty($module_name)) {
            $where['module_name'] = $module_name;
        }

        $query = (new Query())
            ->from($this->itemTable)
            ->where($where);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row, 'itemTable');
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    protected function getItem($name)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->items[$name])) {
            return $this->items[$name];
        }

        $where = [];
        if (is_numeric($name)) {
            $where['id'] = $name;
        } else {
            $where['name'] = $name;
        }

        $row = (new Query())->from($this->itemTable)
            ->where($where)
            ->one($this->db);

        if ($row === false) {
            return null;
        }
        $row['child_type'] = 1;

        return $this->populateItem($row, 'itemTable');
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission($name)
    {
        $item = $this->getItem($name);

        return $item instanceof Item ? $item : null;

        // return $item instanceof Item && $item->type == Item::TYPE_PERMISSION ? $item : null;
    }

    public function getGroupPermission($name, $is_sys = 3)
    {
        $item = $this->getGroup($name, $is_sys);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    protected function addRule($rule)
    {
        $time = time();
        if ($rule->createdAt === null) {
            $rule->createdAt = $time;
        }
        if ($rule->updatedAt === null) {
            $rule->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->routeTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'created_at' => $rule->createdAt,
                'updated_at' => $rule->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateRule($name, $rule)
    {
        if ($rule->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemTable, ['rule_name' => $rule->name], ['rule_name' => $name])
                ->execute();
        }

        $rule->updatedAt = time();

        $this->db->createCommand()
            ->update($this->routeTable, [
                'name' => $rule->name,
                'data' => serialize($rule),
                'updated_at' => $rule->updatedAt,
            ], [
                'name' => $name,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        if ($object instanceof Item) {
            return $this->removeItem($object);
        } elseif ($object instanceof Route) {
            return $this->removeRoute($object);
        } else {
            return $this->removeRule($object);
        }

        throw new InvalidArgumentException('Removing unsupported object type.');
    }

    public function removeRoute($item)
    {
        if (!$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->delete($this->itemChildTable, ['or', '[[parent]]=:name', '[[child]]=:name'], [':name' => $item->name])
                ->execute();

            $this->db->createCommand()
                ->delete($this->assignmentTable, ['item_name' => $item->name])
                ->execute();
        }

        $this->db->createCommand()
            ->delete($this->routeTable, ['name' => $item->name])
            ->execute();

        $this->invalidateCache();

        return true;
    }

    public function removeChild($parent, $child)
    {
        $parent_id = $parent->item_id;

        $child_type = $child->child_type;
        if ($child instanceof Item) {
            $item_id = $child->id;
        } else {
            $item_id = $child->item_id;
        }

        $Res = AuthItemChild::deleteAll([
            'parent_id' => $parent_id,
            'child_type' => $child_type,
            'item_id' => $item_id,
        ]);

        return $Res;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->updateItem($id, $object);
        } elseif ($object instanceof Rule) {
            return $this->updateRule($id, $object);
        }

        throw new InvalidArgumentException('Updating unsupported object type.');
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($id, $item)
    {
        $query = (new Query())->from($this->itemTable)->where(['id' => $id]);
        $itemOne = $query->one($this->db);
        $name = $itemOne['name'];
        if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemChildTable, ['parent' => $item->name], ['parent_id' => $id])
                ->execute();
            $this->db->createCommand()
                ->update($this->itemChildTable, ['child' => $item->name], ['item_id' => $id])
                ->execute();
            $this->db->createCommand()
                ->update($this->assignmentTable, ['item_name' => $item->name], ['item_id' => $id])
                ->execute();
        }

        $item->updatedAt = time();

        $this->db->createCommand()
            ->update($this->itemTable, [
                'name' => $item->name,
                'parent_id' => $item->parent_id,
                'permission_type' => $item->permission_type,
                'module_name' => $item->module_name,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'updated_at' => $item->updatedAt,
            ], [
                'id' => $id,
            ])->execute();
        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addRoute($item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }

        $Re = $this->db->createCommand()
            ->insert($this->routeTable, [
                'name' => $item->name,
                'pid' => $item->pid,
                'module_name' => $item->module_name,
                'is_sys' => $item->is_sys,
                'description' => $item->description,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function addItem($item)
    {
        $time = time();
        if ($item->createdAt === null) {
            $item->createdAt = $time;
        }
        if ($item->updatedAt === null) {
            $item->updatedAt = $time;
        }
        $this->db->createCommand()
            ->insert($this->itemTable, [
                'name' => $item->name,
                'parent_id' => $item->parent_id,
                'permission_type' => $item->permission_type,
                'permission_level' => $item->permission_level,
                'module_name' => $item->module_name,
                'is_sys' => $item->is_sys,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ])->execute();

        $this->invalidateCache();

        return true;
    }

    public function populateGroup($row)
    {
        // 路由   0
        // 规则   1
        // 用户组 2
        return new Group([
            'id' => $row['item_id'],
            'name' => $row['name'],
            'module_name' => $row['module_name'],
            'type' => $row['type'],
            'is_sys' => $row['is_sys'],
            'item_id' => $row['item_id'],
            'child_type' => 2,
            'parent_type' => 2,
            'description' => $row['description'],
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
    }

    /**
     * Populates an auth item with the data fetched from database.
     *
     * @param array $row the data from the auth item table
     *
     * @return Item the populated auth item instance (either Role or Permission)
     */
    protected function populateItem($row, $type = 'itemTable')
    {
        // $class =   $type=='Permission' ? Permission::className() : Role::className();
        $class = Permission::className();

        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }
        switch ($type) {
            case 'itemTable':
                return new $class([
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'is_sys' => $row['is_sys'],
                    'parent_type' => 3,
                    'permission_type' => $row['permission_type'],
                    'permission_level' => $row['permission_level'],
                    'item_id' => $row['id'],
                    'parent_id' => $row['parent_id'],
                    'child_type' => isset($row['child_type']) ? $row['child_type'] : 0,
                    'description' => $row['description'],
                    'ruleName' => $row['rule_name'] ?: null,
                    'data' => $data,
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at'],
                ]);
                break;
            case 'routeTable':
                return new $class([
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'item_id' => $row['item_id'],
                    'is_sys' => $row['is_sys'],
                    'child_type' => isset($row['child_type']) ? $row['child_type'] : 0,
                    'description' => $row['description'],
                    'data' => $data,
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at'],
                ]);
                break;
            case 'groupTable':
                return new $class([
                    'id' => $row['id'],
                    'item_id' => $row['item_id'],
                    'name' => $row['name'],
                    // 'type' => $row['type'],
                    'is_sys' => $row['is_sys'],
                    'child_type' => isset($row['child_type']) ? $row['child_type'] : 0,
                    'description' => $row['description'],
                    'data' => $data,
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at'],
                ]);
                break;
            case 'assignmentTable':
                return new $class([
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'is_sys' => $row['is_sys'],
                    'permission_type' => $row['permission_type'],
                    'parent_id' => $row['parent_id'],
                    'module_name' => $row['module_name'],
                    // 'type' => $row['type'],
                    'child_type' => isset($row['child_type']) ? $row['child_type'] : 0,
                    'parent_type' => 1,
                    'description' => $row['description'],
                    'ruleName' => $row['rule_name'] ?: null,
                    'data' => $data,
                    'createdAt' => $row['created_at'],
                    'updatedAt' => $row['updated_at'],
                ]);
                break;
            default:
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($parent, $child)
    {
        if ($parent->name === $child->name) {
            throw new InvalidArgumentException("Cannot add '{$parent->name}' as a child of itself.");
        }

        if ($parent instanceof Permission && $child instanceof Role) {
            throw new InvalidArgumentException('Cannot add a role as a child of a permission.');
        }

        if ($this->detectLoop($parent, $child)) {
            throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
        }

        $AuthItemChild = new AuthItemChild();
        $AuthItemChild->load([
            'parent' => $parent->name,
            'item_id' => $child->item_id,
            'parent_id' => $parent->item_id,
            'child' => $child->name,
            'is_sys' => $child->is_sys,
            'module_name' => $child->module_name,
            'child_type' => $child->child_type,
            'parent_type' => $child->parent_type,
        ], '');

        $Res = $AuthItemChild->save();
        $msg = ErrorsHelper::getModelError($AuthItemChild);
        if (!empty($msg)) {
            throw new InvalidCallException($msg);
        }
        $this->invalidateCache();

        return $Res;
    }

    // 权限获取 start

    /**
     * {@inheritdoc}
     */
    public function getAssignments($userId)
    {
        if (!isset($this->_assignments[$userId])) {
            // $this->_assignments[$userId] =  parent::getAssignments($userId);
            $this->_assignments[$userId] = $this->getAssignmentsByUid($userId);
        }

        return $this->_assignments[$userId];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssignmentsByUid($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }
        $assignments = [];

        // 获取授权的权限
        $query = (new Query())
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'name' => $row['item_name'],
                'item_id' => $row['item_id'],
                'createdAt' => $row['created_at'],
                'parent_type' => 1,
            ]);
        }

        // 获取用户组
        $query = (new Query())
            ->from($this->assignmentGroupTable)
            ->where(['user_id' => (string) $userId]);

        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'group_id' => $row['group_id'],
                'item_id' => $row['item_id'],
                'roleName' => $row['item_name'],
                'name' => $row['item_name'],
                'createdAt' => $row['created_at'],
                'parent_type' => 2,
            ]);
        }

        return $assignments;
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissionsByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        $directPermission = $this->getDirectPermissionsByUser($userId);
        $inheritedPermission = $this->getInheritedPermissionsByUser($userId);

        return array_merge($directPermission, $inheritedPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function assignGroup($role, $userId)
    {
        $manager = Configs::authManager();

        $assignment = new Assignment([
            'group_id' => $role->id,
            'userId' => $userId,
            'item_id' => $role->item_id,
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        $this->db->createCommand()
            ->insert($this->assignmentGroupTable, [
                'group_id' => $assignment->group_id,
                'item_id' => $assignment->item_id,
                'user_id' => $assignment->userId,
                'item_name' => $assignment->roleName,
                'created_at' => $assignment->createdAt,
            ])->execute();

        unset($manager->_checkAccessAssignments[(string) $userId]);

        return $assignment;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($role, $userId)
    {
        $assignment = '';
        try {
            $assignment = new Assignment([
                'item_id' => $role->id,
                'userId' => $userId,
                'roleName' => $role->name,
                'createdAt' => time(),
            ]);
            $this->db->createCommand()
                ->insert($this->assignmentTable, [
                    'item_id' => $assignment->item_id,
                    'user_id' => $assignment->userId,
                    'item_name' => $assignment->roleName,
                    'created_at' => $assignment->createdAt,
                ])->execute();
            unset($this->_checkAccessAssignments[(string) $userId]);
        } catch (\Exception $e) {
            throw new InvalidCallException($e->message);
        }

        return $assignment;
    }

    /**
     * Returns all permissions that are directly assigned to user.
     *
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     *
     * @return Permission[] all direct permissions that the user has. The array is indexed by the permission names.
     *
     * @since 2.0.7
     */
    protected function getDirectPermissionsByUser($userId)
    {
        $cacheKey = 'getDirectPermissionsByUser_' . $userId;

        $_permissions = yii::$app->cache->get($cacheKey);

        if (!empty($_permissions)) {
            return $_permissions;
        }

        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->routeTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId]);
        // ->andWhere(['b.type' => Item::TYPE_PERMISSION]);

        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row, 'assignmentTable');
        }

        yii::$app->cache->set($cacheKey, $permissions);

        return $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        if (isset($this->_checkAccessAssignments[(string) $userId])) {
            $assignments = $this->_checkAccessAssignments[(string) $userId];
        } else {
            $assignments = $this->getAssignments($userId);
            $this->_checkAccessAssignments[(string) $userId] = $assignments;
        }
        if ($this->hasNoAssignments($assignments)) {
            return false;
        }

        $this->loadFromCache();
        if ($this->items !== null) {
            return $this->checkAccessFromCache($userId, $permissionName, $params, $assignments);
        }

        return $this->checkAccessRecursiveAll($userId, $permissionName, $params, $assignments, 2);
    }

    /**
     * Returns the children for every parent.
     *
     * @return array the children list. Each array key is a parent item name,
     *               and the corresponding array value is a list of child item names.
     */
    protected function getChildrenList()
    {
        $query = (new Query())->from($this->itemChildTable);
        $parents = [];
        foreach ($query->all($this->db) as $row) {
            $parents[$row['parent']][] = $row['child'];
        }

        return $parents;
    }

    /**
     * Returns all permissions that the user inherits from the roles assigned to him.
     *
     * @param string|int $userId the user ID (see [[\yii\web\User::id]])
     *
     * @return Permission[] all inherited permissions that the user has. The array is indexed by the permission names.
     *
     * @since 2.0.7
     */
    protected function getInheritedPermissionsByUser($userId)
    {
        // 使用缓存
        $cacheKey = 'permissions_' . $userId;

        $_permissions = yii::$app->cache->get($cacheKey);

        if (!empty($_permissions)) {
            return $_permissions;
        }

        $assignment = [];

        $query = (new Query())->select('item_name')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);

        $assignment1 = $query->column($this->db);

        $query = (new Query())->select('item_name')
            ->from($this->assignmentGroupTable)
            ->where(['user_id' => (string) $userId]);

        $assignment2 = $query->column($this->db);

        $assignment = array_merge($assignment1, $assignment2);

        $childrenList = $this->getChildrenList();

        $result = [];
        foreach ($assignment as $roleName) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }

        if (empty($result)) {
            return [];
        }

        $query = (new Query())->from($this->routeTable)->where([
            // 'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);

        $permissions = [];
        foreach (array_keys($result) as $itemName) {
            if (isset($this->items[$itemName]) && $this->items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->items[$itemName];
            }
        }

        foreach ($query->all($this->db) as $row) {
            $row['parent_id'] = 0;
            $row['child_type'] = 0;
            $row['rule_name'] = 0;

            $permissions[$row['name']] = $this->populateItem($row, 'Role');
        }

        yii::$app->cache->set($cacheKey, $permissions);

        return $permissions;
    }

    protected function checkAccessRecursiveAll($user, $itemName, $params, $assignments, $parent_type)
    {
        loggingHelper::writeLog('yii2-admin', 'DbManager', '权限校验日志', ['user' => $user, 'itemName' => $itemName, 'params' => $params, 'assignments' => $assignments, 'parent_type' => $parent_type]);
        if (strpos($itemName, '/') !== false) {
            // 校验路由权限是否存在，不存在就没有权限
            if (($item = $this->getRoute($itemName)) === null && ($item = $this->getRoute($itemName, 2)) === null) {
                return false;
            }
        } else {
            // 0:路由1：规则2：用户组;3权限
            // echo '权限名称'.$itemName.PHP_EOL;
            // echo '权限类型'.$parent_type.PHP_EOL;
            if ($parent_type == 1) {
                // 规则
                // 检测权限是否存在
                if (($item = $this->getItem($itemName)) === null) {
                    return false;
                }
            } elseif ($parent_type == 2) {
                // 用户组
                if (($item = $this->getGroup($itemName)) === null && ($item = $this->getGroup($itemName, 1)) === null) {
                    return false;
                }

                // print_r($user);
                // 查询用户是否有组的权限
                $groupsList = AuthAssignmentGroup::find()->where(['user_id' => $user])->select('item_name')->column();

                if (!in_array($itemName, $groupsList) && !empty($groupsList)) {
                    return false;
                }
            } elseif ($parent_type == 3) {
                if (($item = $this->getItem($itemName)) === null) {
                    return false;
                }
            }
        }

        Yii::debug($item instanceof Role ? "Checking role: $itemName" : "Checking permission: $itemName", __METHOD__);

        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }

        $query = new Query();
        //  权限： parent_type = 3
        $parents = $query->select(['parent', 'parent_type'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->all($this->db);
        foreach ($parents as $parent) {
            if ($this->checkAccessRecursiveAll($user, $parent['parent'], $params, $assignments, $parent['parent_type'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Executes the rule associated with the specified auth item.
     *
     * If the item does not specify a rule, this method will return true. Otherwise, it will
     * return the value of [[Rule::execute()]].
     *
     * @param string|int $user   the user ID. This should be either an integer or a string representing
     *                           the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item       $item   the auth item that needs to execute its rule
     * @param array      $params parameters passed to [[CheckAccessInterface::checkAccess()]] and will be passed to the rule
     *
     * @return bool the return value of [[Rule::execute()]]. If the auth item does not specify a rule, true will be returned.
     *
     * @throws InvalidConfigException if the auth item has an invalid rule
     */
    protected function executeRule($user, $item, $params)
    {
        // 规则检查
        if ((property_exists($item, 'ruleName') && $item->ruleName === null) || !property_exists($item, 'ruleName')) {
            return true;
        }
        $rule = $this->getRule($item->ruleName);

        if ($rule instanceof Rule) {
            return $rule->execute($user, $item, $params);
        }

        throw new InvalidConfigException("Rule not found: {$item->ruleName}");
    }
}
