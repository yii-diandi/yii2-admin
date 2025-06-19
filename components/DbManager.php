<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 19:56:41
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2023-07-18 16:45:18
 */

namespace diandi\admin\components;

use admin\models\BlocAddons;
use admin\models\User;
use diandi\admin\acmodels\AuthItem;
use diandi\admin\acmodels\AuthItemChild;
use diandi\admin\acmodels\AuthRoute;
use diandi\admin\acmodels\AuthUserGroup;
use diandi\admin\models\AuthAssignment;
use diandi\admin\models\AuthAssignmentGroup;
use diandi\admin\models\AuthError;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Query;
use yii\rbac\Rule;
use yii\web\NotFoundHttpException;

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
        try {
            // child_type:1 表示权限
            $children = [];
            foreach (AuthItem::find()
                         ->alias('a')
                         ->joinWith(['childs as c'], false)
                         ->where(['c.parent_id' => $id])
                         ->select([
                             'a.name',
                             'a.id',
                             'a.description',
                             'a.rule_name',
                             'a.module_name',
                             'a.data',
                             'c.child',
                             'c.parent_id',
                             'a.created_at',
                             'a.updated_at',
                             'a.permission_type',
                             'a.permission_level',
                             'a.is_sys',
                             'a.data'
                         ])
                         ->asArray()
                         ->batch(100) as $batch) {
                foreach ($batch as $row) {
                    $children[$row['id']] = $this->populateItem($row, 'itemTable');
                }
            }
            return $children;


        } catch (\Exception $e) {
            throw new InvalidConfigException($e->getMessage());
        }

    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     *
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     *
     * @return bool whether a loop exists
     */
    public function detectLoop($parent, $child, $isPc = false)
    {
        // 确定两者不存在相互包含的情况
        if ($child->item_id === $parent->item_id) {
            Yii::debug("Parent and child have the same item_id: {$parent->item_id}", __METHOD__);
            return true;
        }

        $parent_item_id = $parent->item_id;
        $child_item_id = $child->item_id;

        /**
         * 使用db 查询，避免循环引用导致死循环
         */
        $childContainsParent = (new Query())->from($this->itemChildTable)->where([
            'item_id' => $parent_item_id,
            'parent_item_id' => $child_item_id
        ])->exists();

        // 反向查询子级是否包含父级

        Yii::debug("Child contains parent: " . var_export($childContainsParent, true), __METHOD__);

        // 如果两者相互包含，返回 true
        if ($childContainsParent) {
            Yii::debug("Loop detected between parent and child", __METHOD__);
            return true;
        }

        return false;
    }

    /**
     * @param $roleName
     * @return array
     */
    public function getPermissionsByRoleId($roleId)
    {
        $childrenList = $this->getChildrenListIndexId();
        $result = [];

        $this->getChildrenRecursiveByItemId($roleId, $childrenList, $result);
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
     * @param int $parent_type 父级类型  0:路由1：规则2：用户组  3权限
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
                ])->andWhere($where)->select(['c.child as name', 'u.is_sys', 'c.id', 'c.item_id', 'child_type', 'description', 'created_at', 'updated_at', 'c.item_id'])->asArray()->all();

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
                ])->andWhere($where)->select(['p.permission_type', 'c.id', 'c.parent_id', 'c.child as name', 'item_id', 'child_type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])->indexBy('item_id')->asArray()->all();

                foreach ($list as $row) {
                    $children[$row['item_id']] = $this->populateItem($row, 'itemTable');
                }

                break;

            default:

                $list = AuthItem::find()->alias('p')->joinWith('childs as c')->where([
                    'c.item_id' => $id,
                    'parent_type' => $parent_type,
                ])->andWhere($where)->select(['c.id', 'c.parent_id', 'c.child as name', 'item_id', 'child_type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])->indexBy('item_id')->asArray()->all();
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

        unset($this->_checkAccessAssignments[(string)$userId]);

        return $this->db->createCommand()
                ->delete($this->assignmentGroupTable, ['user_id' => (string)$userId, 'item_name' => $role->name])
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
        Yii::info([
            'sql' => (new Query())->from($this->routeTable)
                ->where($where)->createCommand()->getRawSql(),
            'row' => $row
        ], 'getRoute');
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

        // 分批处理数据
        foreach ($query->batch(100, $this->db) as $batch) {
            foreach ($batch as $row) {
                $items[$row['id']] = $this->populateItem($row);
            }
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
            'route_type' => $row['route_type'],
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
            $itemChild = AuthItemChild::findOne(['or', '[[parent]]=:name', '[[item]]=:name'], [':name' => $item->name]);
            $itemChild->delete();
            $assignment = AuthAssignment::findOne(['item_name' => $item->name]);
            $assignment->delete();
        }

        $route = AuthRoute::find()->where(['name' => $item->name])->one();
        $route->delete();

        $this->invalidateCache();

        return true;
    }

    public function removeChild($parent, $child)
    {
        $parent_item_id = $parent->item_id;
        $child_type = $child->child_type;
        if ($child instanceof Item) {
            $item_id = $child->id;
        } else {
            $item_id = $child->item_id;
        }
        $Res = AuthItemChild::deleteAll([
            'parent_item_id' => $parent_item_id,
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
                'permission_level' => $item->permission_level,
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

        $AuthRoute = new AuthRoute();
        $route_name = str_replace('/', '-', ltrim($item->name, '/'));
        $exists = $AuthRoute->find()->where(['route_name' => $route_name])->exists();
        if ($exists) {
            $route_name .= '-' . 'route';
        }

        $route_type = 1;
        //        路由级别:0: 目录1: 页面 2: 按钮 3: 接口
        if (str_contains($route_name, "vue")) {
            if (str_contains($route_name, "create") || str_contains($route_name, "update")) {
                $route_type = 2; //按钮
            } else {
                $route_type = 1; //页面
            }
        } else {
            if (strpos($route_name, "*")) {
                $route_type = 0;//目录
            } else {
                $route_type = 3;//接口
            }
        }

        [$module_name,] = explode('/', ltrim($item->name, '/'));
        $modules = array_keys(Yii::$app->modules);

        if (!in_array($module_name, $modules)) {
            $module_name = 'system';
        }
        if (!($AuthRoute->load([
                'name' => $item->name,
                'route_name' => $route_name,
                'pid' => $item->pid,
                'route_type' => $route_type,//1页面2按钮3接口
                'item_id' => 0,
                'module_name' => $module_name,
                'is_sys' => $module_name === 'sysytem' ? 1 : 0,
                'description' => $item->description,
                'data' => $item->data === null ? null : serialize($item->data),
                'created_at' => $item->createdAt,
                'updated_at' => $item->updatedAt,
            ], '') && $AuthRoute->save())) {
            $msg = $this->getModelError($AuthRoute);
            throw new InvalidArgumentException($msg);
        }

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
            'id' => $row['id'],
            'name' => $row['name'],
            'module_name' => $row['module_name'],
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
                    'module_name' => $row['module_name'],
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
                    'module_name' => $row['module_name'],
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
                    'module_name' => $row['module_name'] ?? 'system',
                    // 'type' => $row['type'],
                    'is_sys' => $row['is_sys'] ?? 1,
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

        $parent_id = $parent->id;
        $exists = (new Query())->from($this->itemChildTable)->where([
            'parent_id' => $parent_id,
            'item_id' => $child->item_id,
        ])->exists();
        try {
            if (!$exists) {
                if ($this->detectLoop($parent, $child, true)) {
                    throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
                }

                $Res = $this->db->createCommand()
                    ->insert($this->itemChildTable, [
                        'parent' => $parent->name,
                        'item_id' => $child->item_id,
                        'parent_id' => $parent_id,
                        'route_type' => $child->route_type ?? 1,
                        'parent_item_id' => $parent->item_id,
                        'child' => $child->name,
                        'is_sys' => $child->is_sys,
                        'module_name' => $child->module_name,
                        'child_type' => $child->child_type,
                        'parent_type' => $child->parent_type,
                    ])->execute();

                $this->invalidateCache();
                return $Res;
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            Yii::debug($e->getMessage(), __METHOD__);
            throw new InvalidCallException($e->getMessage());
        }

    }

    /**
     * function_description.
     *
     * @param mixed $model
     * @return string
     * @throws NotFoundHttpException
     */
    private function getModelError(mixed $model)
    {
        $errors = $model->getErrors();    //得到所有的错误信息
        if (!is_array($errors)) return '';
        $firstError = array_shift($errors);
        if (!is_array($firstError)) return '';
        return array_shift($firstError);
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
            ->where(['user_id' => (string)$userId]);

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
            ->where(['user_id' => (string)$userId]);

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
     * 获取用户权限
     * {@inheritdoc}
     */
    public function getPermissionsByUser($userId)
    {
        if ($this->isEmptyUserId($userId)) {
            return [];
        }

        /**
         * 路由授权
         */
        $directPermission = $this->getDirectPermissionsByUser($userId);
        /**
         * 权限授权
         */
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

        unset($manager->_checkAccessAssignments[(string)$userId]);

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
            unset($this->_checkAccessAssignments[(string)$userId]);
        } catch (\Exception $e) {
            throw new InvalidCallException($e->getMessage());
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
            ->andWhere(['a.user_id' => (string)$userId]);
        // ->andWhere(['b.type' => Item::TYPE_PERMISSION]);
//        echo $query->createCommand()->getRawSql();
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
        Yii::info('准备校验权限', 'checkAccess');
        if (isset($this->_checkAccessAssignments[(string)$userId])) {
            Yii::info('准备校验权限-0', 'checkAccess');

            $assignments = $this->_checkAccessAssignments[(string)$userId];
        } else {
            Yii::info('准备校验权限-1', 'checkAccess');

            $assignments = $this->getAssignments($userId);
            $this->_checkAccessAssignments[(string)$userId] = $assignments;
        }
        Yii::info('准备校验权限-2', 'checkAccess');

        if ($this->hasNoAssignments($assignments)) {
            Yii::info('准备校验权限-3', 'checkAccess');
            return false;
        }

        $this->loadFromCache();
        if ($this->items !== null) {
            Yii::info('准备校验权限-4', 'checkAccess');

            return $this->checkAccessFromCache($userId, $permissionName, $params, $assignments);
        }
        Yii::info('准备校验权限-5', 'checkAccess');
        return $this->checkAccessRecursiveAll($userId, $permissionName, $params, $assignments, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function revoke($role, $userId)
    {
        if ($this->isEmptyUserId($userId) || !$role) {
            return false;
        }

        unset($this->checkAccessAssignments[(string)$userId]);
        $result = $this->db->createCommand()
                ->delete($this->assignmentTable, ['user_id' => (string)$userId, 'item_name' => $role->name])
                ->execute() > 0;

        $this->invalidateCache();

        return $result;
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
     * 深入改造
     * @return array
     */
    protected function getChildrenListIndexId()
    {
        $user_id = yii::$app->user->id;
        $is_sys = User::find()->andWhere(['id' => $user_id])->select('is_sys')->scalar();

        if ($is_sys == 1) {
            /**
             * 查找所有的接口和目录对应的item_id
             */
            $query = (new Query())->from($this->itemChildTable)->where(['>', 'parent_item_id', 0]);

        } else {
            /**
             * 查找所有的接口和目录对应的item_id
             */
//        $api_item_id = (new Query())->from($this->routeTable)->where(['route_type'=>3])->orWhere(['route_type'=>0])->select('item_id')->column();
//        $query = (new Query())->from($this->itemChildTable)->where(['>', 'parent_item_id', 0]);

//       route_type 路由级别:0: 目录1: 页面 2: 按钮 3: 接口 放弃目录权限，接口校验单独处理，这个给路由和菜单权限数据
            $query = (new Query())->from($this->itemChildTable)->leftJoin($this->routeTable, $this->routeTable . '.item_id = ' . $this->itemChildTable . '.item_id')
                ->where(['>', $this->itemChildTable . '.parent_item_id', 0])
                ->andWhere([$this->routeTable . '.route_type' => [1, 2]]);
//        $query->andWhere(['not in', 'item_id', $api_item_id]);
//        echo $query->createCommand()->getRawSql();

        }

        $parents = [];
        ini_set('memory_limit', '1024M');
        $query->select([$this->itemChildTable . '.item_id', 'child', 'parent_item_id']);
        foreach ($query->all($this->db) as $row) {
            $parents[$row['parent_item_id']][] = [
                'item_id' => $row['item_id'],
                'child' => $row['child']
            ];
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

        $query = (new Query())->select('item_id')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string)$userId]);
        $assignment1 = $query->column($this->db);
        $query = (new Query())->select('item_id')
            ->from($this->assignmentGroupTable)
            ->where(['user_id' => (string)$userId]);

        $assignment2 = $query->column($this->db);

        /**
         * 业务中心管理员 给业务中心管理员对应公司的插件权限
         */
        $assignment3 = [];

        $user = User::find()->andWhere(['id' => $userId])->select(['is_business_admin', 'bloc_id','is_sys'])->asArray()->one();
        if ($user['is_business_admin'] == 1) {
            $authAddons = BlocAddons::find()->where(['bloc_id' => $user['bloc_id']])->select('module_name')->column();
            $assignment3 = AuthItem::find()
                ->where(['module_name' => $authAddons])->select('id')->column();
        }
        $assignment = array_merge($assignment1, $assignment2, $assignment3);

        $childrenList = $this->getChildrenListIndexId();
        $result = [];
        foreach ($assignment as $item_id) {
            $this->getChildrenRecursiveByItemId($item_id, $childrenList, $result);
        }
        if (empty($result)) {
            return [];
        }


        $query = (new Query())->from($this->routeTable)->where([
            'item_id' => array_keys($result),
        ]);
        $is_sys = $user['is_sys'];
        if ($is_sys == 0){
            /**
             * 非接口权限
             */
            $query->andWhere(['!=', 'route_type', 3]);
        }

        $permissions = [];

        foreach (array_keys($result) as $itemId) {
            if (isset($this->items[$itemId]) && $this->items[$itemId] instanceof Permission) {
                $permissions[$itemId] = $this->items[$itemId];
            }
        }
        foreach ($query->all($this->db) as $row) {
            $row['parent_id'] = 0;
            $row['child_type'] = 0;
            $row['rule_name'] = 0;
            $permissions[$row['item_id']] = $this->populateItem($row, 'routeTable');
        }
        yii::$app->cache->set($cacheKey, $permissions);
        return $permissions;
    }

    /**
     * @param $item_id
     * @param $childrenList
     * @param $result
     * @return void
     */
    protected function getChildrenRecursiveByItemId($item_id, $childrenList, &$result)
    {
        if (isset($childrenList[$item_id])) {
            foreach ($childrenList[$item_id] as $child) {
                $result[$child['item_id']] = true;
                $this->getChildrenRecursiveByItemId($child['item_id'], $childrenList, $result);
            }
        }
    }

    public function addError($itemName, $params, $assignments, $parent_type)
    {
        $AuthError = new AuthError();
        $AuthError->setAttributes([
            'user_id' => Yii::$app->user->id,
            'itemName' => (string)$itemName,
            'params' => json_encode($params),
            'assignments' => json_encode($assignments),
            'parent_type' => (string)$parent_type
        ]);
        if (!$AuthError->save()) {
            $error = $AuthError->getErrors();
            throw new \Exception(current($error));
        }
    }

    protected function checkAccessRecursiveAll($user, $itemName, $params, $assignments, $parent_type)
    {
        /**
         * 如果$itemName包含星号，直接放行
         */
        if (strpos($itemName, '*') !== false) {
            return true;
        }
        Yii::info(['user' => $user, 'itemName' => $itemName, 'params' => $params, 'assignments' => $assignments, 'parent_type' => $parent_type], 'checkAccessRecursiveAll');
        if (strpos($itemName, '/') !== false) {
            // 校验路由权限是否存在，不存在就没有权限
            if (($item = $this->getRoute($itemName)) === null && ($item = $this->getRoute($itemName, 2)) === null) {
                Yii::info('checkAccessRecursiveAll-0', 'checkAccessRecursiveAll');
                $this->addError($itemName, $params, $assignments, $parent_type);
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
                    Yii::info('checkAccessRecursiveAll-1', 'checkAccessRecursiveAll');
                    $this->addError($itemName, $params, $assignments, $parent_type);

                    return false;
                }
            } elseif ($parent_type == 2) {
                // 用户组
                if (($item = $this->getGroup($itemName)) === null && ($item = $this->getGroup($itemName, 1)) === null) {
                    Yii::info('checkAccessRecursiveAll-2', 'checkAccessRecursiveAll');
                    $this->addError($itemName, $params, $assignments, $parent_type);

                    return false;
                }

                // 查询用户是否有组的权限
                $groupsArr = AuthAssignmentGroup::find()->where(['user_id' => $user])->select(['item_name', 'group_id'])->asArray()->one();
                if (!empty($groupsArr)) {
                    $group_id = $groupsArr['group_id'];
                    $group_child = AuthItemChild::find()->where(['parent_id' => $group_id])->select(['child'])->column();
                    array_push($group_child, $groupsArr['item_name']);

                    if (!in_array($itemName, $group_child) && !empty($group_child)) {
                        Yii::info('checkAccessRecursiveAll-3', 'checkAccessRecursiveAll');
                        $this->addError($itemName, $params, $assignments, $parent_type);

                        return false;
                    }
                }

            } elseif ($parent_type == 3) {
                if (($item = $this->getItem($itemName)) === null) {
                    Yii::info('checkAccessRecursiveAll-4', 'checkAccessRecursiveAll');
                    $this->addError($itemName, $params, $assignments, $parent_type);

                    return false;
                }
            }
        }
        $item = $this->getItem($itemName);
        if (!$this->executeRule($user, $item, $params)) {
            Yii::info('checkAccessRecursiveAll-5', 'checkAccessRecursiveAll');
            $this->addError($itemName, $params, $assignments, $parent_type);

            return false;
        }

        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            Yii::info('checkAccessRecursiveAll-6', 'checkAccessRecursiveAll');

            return true;
        }

        $query = new Query();
        //  权限： parent_type = 3
        $parents = $query->select(['parent', 'parent_type'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->all($this->db);
        Yii::info('checkAccessRecursiveAll-7', 'checkAccessRecursiveAll');

        foreach ($parents as $parent) {
            if ($this->checkAccessRecursiveAll($user, $parent['parent'], $params, $assignments, $parent['parent_type'])) {
                return true;
            }
        }

        Yii::info('checkAccessRecursiveAll-8', 'checkAccessRecursiveAll');
        $this->addError($itemName, $params, $assignments, $parent_type);

        return false;
    }


    /**
     * Executes the rule associated with the specified auth item.
     *
     * If the item does not specify a rule, this method will return true. Otherwise, it will
     * return the value of [[Rule::execute()]].
     *
     * @param string|int $user the user ID. This should be either an integer or a string representing
     *                           the unique identifier of a user. See [[\yii\web\User::id]].
     * @param Item $item the auth item that needs to execute its rule
     * @param array $params parameters passed to [[CheckAccessInterface::checkAccess()]] and will be passed to the rule
     *
     * @return bool the return value of [[Rule::execute()]]. If the auth item does not specify a rule, true will be returned.
     *
     * @throws InvalidConfigException if the auth item has an invalid rule
     */
    protected function executeRule($user, $item, $params)
    {
        if (!is_object($item) && !is_string($item)){
            return true;
        }
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
