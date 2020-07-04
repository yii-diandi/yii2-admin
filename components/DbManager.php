<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 19:56:41
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-04 17:41:26
 */

namespace diandi\admin\components;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\caching\CacheInterface;
use yii\db\Expression;
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
            $this->items[$row['name']] = $this->populateItem($row);
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
    public function getChildren($name)
    {
        $query = (new Query())
            ->select(['name', $this->itemTable.'.type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])
            ->from([$this->itemTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);
        $children = [];
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }

        return $children;
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
            'type' => Item::TYPE_PERMISSION,
            'name' => array_keys($result),
        ]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

        return $permissions;
    }

   

    /**
     * 获取所有路由.
     */
    public function getRoutePermissions($type=1)
    {
        return $this->getRoutes($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes($type)
    {
        $where = [];
        if(in_array($type,[0,1])){
            $where['type'] = $type;
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
    public function getAuths($group_name, $type = 0)
    {
        $available = [];
        $assigned = [];

        // 用户组授权
        foreach (array_keys($this->getGroups($type)) as $name) {
            $available[$name] = 'role';
        }
        // 权限授权
        foreach (array_keys($this->getPermissions($type)) as $name) {
            $available[$name] = 'permission';
        }
        // 路由授权
        foreach (array_keys($this->getRoutes($type)) as $name) {
            $available[$name] = 'route';
        }
        // p($this->getItemChildren($group_name));
        // 子权限授权
        foreach ($this->getItemChildren($group_name) as $item) {
            $child_type = ['route', 'permission', 'role'];
            $assigned[$item->name] = $child_type[$item->child_type];
            unset($available[$item->name]);
        }
        unset($available[$group_name]);

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemChildren($name)
    {
        $children = [];
        // 获取权限已授权
        $query = (new Query())
            ->select(['name', $this->itemTable.'.type', 'child_type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'])
            ->from([$this->itemTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }
        // 获取role已授权
        $query = (new Query())
            ->select(['name', $this->groupTable.'.type', 'child_type', 'description', 'created_at', 'updated_at'])
            ->from([$this->groupTable, $this->itemChildTable])
            ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);

        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }
        // 获取route已授权
        $query = (new Query())
        ->select(['name', $this->routeTable.'.type', 'child_type', 'description', 'data', 'created_at', 'updated_at'])
        ->from([$this->routeTable, $this->itemChildTable])
        ->where(['parent' => $name, 'name' => new Expression('[[child]]')]);
        foreach ($query->all($this->db) as $row) {
            $children[$row['name']] = $this->populateItem($row);
        }

        return $children;
    }

    public function getGroup($name, $type = 0)
    {
        $query = (new Query())
            ->from($this->groupTable)
            ->where([
                'type' => $type,
                'name' => $name,
                ]);
        $item = $query->one($this->db);
        if ($item === false) {
            return null;
        }
        $items = $this->populateGroup($item);

        return $items;
    }

    public function getGroups($type)
    {
        $query = (new Query())
            ->from($this->groupTable)
            ->where(['type' => $type]);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
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
     * {@inheritdoc}
     */
    public function getPermissions($type=0)
    {
        return $this->getItems($type);
    }

    /**
     * Populates an auth item with the data fetched from database.
     *
     * @param array $row the data from the auth item table
     *
     * @return Route the populated auth item instance (either Role or Permission)
     */
    protected function populateRoute($row,$parent_type=1)
    {
        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }

        return new Route([
            'id' => $row['id'],
            'name' => $row['name'],
            'pid' => $row['pid'],
            'module_name' => $row['module_name'],
            'type' => $row['type'],
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
        $permission->pid = 0;
        $permission->type = 1;

        return $permission;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePermission($name,$parent_type=1)
    {
        $item = $this->getRoute($name,$parent_type);

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoute($name,$parent_type=1)
    {
        if (empty($name)) {
            return null;
        }

        if (!empty($this->routes[$name])) {
            return $this->routes[$name];
        }

        $row = (new Query())->from($this->routeTable)
            ->where(['name' => $name])
            ->one($this->db);

        if ($row === false) {
            return null;
        }
        return $this->populateRoute($row,$parent_type);
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

    public function getParentItem($type, $module_name)
    {
        $query = (new Query())
        ->from($this->itemTable)
        ->where(['type' => $type, 'module_name' => $module_name,'parent_id'=>[0,null,'']]);

        $items = [];
        foreach ($query->all($this->db) as $row) {
            $items[$row['name']] = $this->populateItem($row);
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

        $row = (new Query())->from($this->itemTable)
            ->where(['name' => $name])
            ->orWhere(['id' => $name])
            ->one($this->db);

        if ($row === false) {
            return null;
        }
        $row['child_type'] = 1;

        return $this->populateItem($row);
    }

    /**
     * {@inheritdoc}
     */
    public function getPermission($name)
    {
        $item = $this->getItem($name);
        return $item instanceof Item  ? $item : null;

        // return $item instanceof Item && $item->type == Item::TYPE_PERMISSION ? $item : null;
    }

    public function getGroupPermission($name,$type=0)
    {
        $item = $this->getGroup($name,$type);

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

    /**
     * {@inheritdoc}
     */
    public function update($name, $object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Yii::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }

            return $this->updateItem($name, $object);
        } elseif ($object instanceof Rule) {
            return $this->updateRule($name, $object);
        }

        throw new InvalidArgumentException('Updating unsupported object type.');
    }

    /**
     * {@inheritdoc}
     */
    protected function updateItem($name, $item)
    {
        if ($item->name !== $name && !$this->supportsCascadeUpdate()) {
            $this->db->createCommand()
                ->update($this->itemChildTable, ['parent' => $item->name], ['parent' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->itemChildTable, ['child' => $item->name], ['child' => $name])
                ->execute();
            $this->db->createCommand()
                ->update($this->assignmentTable, ['item_name' => $item->name], ['item_name' => $name])
                ->execute();
        }

        $item->updatedAt = time();

        $this->db->createCommand()
            ->update($this->itemTable, [
                'name' => $item->name,
                'parent_id' => $item->parent_id,
                'module_name' => $item->module_name,
                'description' => $item->description,
                'rule_name' => $item->ruleName,
                'data' => $item->data === null ? null : serialize($item->data),
                'updated_at' => $item->updatedAt,
            ], [
                'name' => $name,
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
                'type' => $item->type,
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
                'module_name' => $item->module_name,
                'type' => $item->type,
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
            'type' => $row['type'],
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
    protected function populateItem($row)
    {
        // $class = $row['type'] == Item::TYPE_PERMISSION ? Permission::className() : Role::className();
        $class = Permission::className();

        if (!isset($row['data']) || ($data = @unserialize(is_resource($row['data']) ? stream_get_contents($row['data']) : $row['data'])) === false) {
            $data = null;
        }

        return new $class([
            'id' => $row['id'],
            'name' => $row['name'],
            'parent_id' => $row['parent_id'],
            'module_name' => $row['module_name'],
            'type' => $row['type'],
            'child_type' => $row['child_type'],
            'parent_type' => 1,
            'description' => $row['description'],
            'ruleName' => $row['rule_name'] ?: null,
            'data' => $data,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ]);
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

        $this->db->createCommand()
            ->insert($this->itemChildTable, [
                'parent' => $parent->name,
                'child' => $child->name,
                'type' => $child->type,
                'module_name' => $child->module_name,
                'child_type' => $child->child_type,
                'parent_type' => $child->parent_type,
            ])
            ->execute();

        $this->invalidateCache();

        return true;
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
                'createdAt' => $row['created_at'],
                'parent_type'=>1,
            ]);
        }
        // 获取用户组
        $query = (new Query())
            ->from($this->assignmentGroupTable)
            ->where(['user_id' => (string) $userId]);
        
        foreach ($query->all($this->db) as $row) {
            $assignments[$row['item_name']] = new Assignment([
                'userId' => $row['user_id'],
                'roleName' => $row['item_name'],
                'createdAt' => $row['created_at'],
                'parent_type'=>2,
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
        
        $key1 = 'getDirectPermissionsByUser_'.$userId;
        $key2 = 'getInheritedPermissionsByUser_'.$userId;
        
        if(Yii::$app->cache->get($key1)){
            $directPermission = Yii::$app->cache->get($key1);
        }else{
            $directPermission = $this->getDirectPermissionsByUser($userId);
            Yii::$app->cache->set($key1,$directPermission);
        }

        if(Yii::$app->cache->get($key2)){
            $inheritedPermission = Yii::$app->cache->get($key2);
            
        }else{
            $inheritedPermission = $this->getInheritedPermissionsByUser($userId);
            Yii::$app->cache->set($key2,$directPermission);
        }
        

        
        
    
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
            'roleName' => $role->name,
            'createdAt' => time(),
        ]);

        $this->db->createCommand()
            ->insert($this->assignmentGroupTable, [
                'group_id' => $assignment->group_id,
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
        $assignment = new Assignment([
            'item_id'=>$role->id,
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
        $query = (new Query())->select('b.*')
            ->from(['a' => $this->assignmentTable, 'b' => $this->routeTable])
            ->where('{{a}}.[[item_name]]={{b}}.[[name]]')
            ->andWhere(['a.user_id' => (string) $userId]);
        // ->andWhere(['b.type' => Item::TYPE_PERMISSION]);
        $permissions = [];
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }

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
        return $this->checkAccessRecursiveAll($userId, $permissionName, $params, $assignments,2);
    }

     /**
     * Returns the children for every parent.
     * @return array the children list. Each array key is a parent item name,
     * and the corresponding array value is a list of child item names.
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
        $assignment = [];

        $query = (new Query())->select('item_name')
            ->from($this->assignmentTable)
            ->where(['user_id' => (string) $userId]);
        
        $assignment1 =    $query->column($this->db);
        
        $query = (new Query())->select('item_name')
        ->from($this->assignmentGroupTable)
        ->where(['user_id' => (string) $userId]);
    
        $assignment2 =    $query->column($this->db);

        $assignment = array_merge($assignment1,$assignment2);
        
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
        foreach ($query->all($this->db) as $row) {
            $permissions[$row['name']] = $this->populateItem($row);
        }
        return $permissions;
    }

    /**
     * 
     */
    protected function checkAccessRecursiveAll($user, $itemName, $params, $assignments,$parent_type)
    {   
        if (strpos($itemName, '/') !== false) {
            // 校验路由权限是否存在，不存在就没有权限
            if (($item = $this->getRoute($itemName)) === null && ($item = $this->getRoute($itemName,2)) === null) {
                return false;
            }
        } else {
            // 路由0
            // 规则1
            // 用户组2
            if($parent_type==1){
                 // 检测权限是否存在
                if (($item = $this->getItem($itemName)) === null) {
                    return false;
                }
            }else if($parent_type==2){
               
                if (($item = $this->getGroup($itemName)) === null && ($item = $this->getGroup($itemName,1)) === null) {
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
        $parents = $query->select(['parent','parent_type'])
            ->from($this->itemChildTable)
            ->where(['child' => $itemName])
            ->all($this->db);
            
        foreach ($parents as $parent) {
            if ($this->checkAccessRecursiveAll($user, $parent['parent'], $params, $assignments,$parent['parent_type'])) {
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
