<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-13 12:27:30
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-10-26 02:15:40
 */

namespace diandi\admin\models;

use admin\models\auth\AuthRoute;
use diandi\admin\components\Configs;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "menu".
 * @property int    $id         Menu id(autoincrement)
 * @property string $name       Menu name
 * @property int    $parent     Menu parent
 * @property string $route      Route for this menu
 * @property int    $order      Menu order
 * @property string $data       Extra information for this menu
 * @property Menu   $menuParent Menu parent
 * @property Menu[] $menus      Menu children
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class Menu extends \yii\db\ActiveRecord
{
    public $parent_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return Configs::instance()->menuTable;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDb()
    {
        if (Configs::instance()->db !== null) {
            return Configs::instance()->db;
        } else {
            return parent::getDb();
        }
    }

    public static function getRegion($parentId = 0)
    {
        $result = static::find()->where(['parent' => $parentId])->asArray()->all();

        return ArrayHelper::map($result, 'id', 'name');
    }
    

    public function getRouter()
    {
        return $this->hasOne(AuthRoute::className(),['id'=>'route_id']);
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type', 'icon', 'module_name'], 'string'],
            [
                ['parent_name'], 'in',
                'range' => static::find()->select(['name'])->column(),
                'message' => 'Menu "{value}" not found.',
            ],
            [['parent', 'route', 'data', 'order'], 'default'],
            [['parent'], 'filterParent', 'when' => function () {
                return !$this->isNewRecord;
            }],
            [['order', 'is_show','route_id','level_type','is_sys'], 'integer'],
            [['is_sys'], 'in', 'range' => [1, 0]],
            [
                ['route'], 'in',
                'range' => static::getSavedRoutes(),
                'message' => 'Route "{value}" not found.',
            ],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (!is_numeric($this->order) && isset($this->order)) {
                //字段
                $this->order = 0;
            }
            
            if(is_numeric($this->route)){
                $router_id = $this->route;
                $this->route_id = $router_id; 
                $this->route = AuthRoute::find()->where(['id'=>$router_id])->select('name')->scalar();
            }

            return true;
        } else {
            return false;
        }
    }



    /**
     * Use to loop detected.
     */
    public function filterParent()
    {
        $parent = $this->parent;
        $db = static::getDb();
        $query = (new Query())->select(['parent'])
            ->from(static::tableName())
            ->where('[[id]]=:id');
        while ($parent) {
            if ($this->id == $parent) {
                $this->addError('parent_name', 'Loop detected.');

                return;
            }
            $parent = $query->params([':id' => $parent])->scalar($db);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('rbac-admin', 'ID'),
            'name' => Yii::t('rbac-admin', 'Name'),
            'parent' => Yii::t('rbac-admin', 'Parent'),
            'parent_name' => Yii::t('rbac-admin', 'Parent Name'),
            'route' => Yii::t('rbac-admin', 'Route'),
            'order' => Yii::t('rbac-admin', 'Order'),
            'data' => Yii::t('rbac-admin', 'Data'),
            'type' => '类型',
            'icon' => '图标',
			'level_type'=> '菜单等级类型',
            'module_name' => '所属模块',
            'is_sys' => '菜单类别',
        ];
    }

    /**
     * Get menu parent.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenuParent()
    {
        return $this->hasOne(Menu::className(), ['id' => 'parent']);
    }

    /**
     * Get menu children.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMenus()
    {
        return $this->hasMany(Menu::className(), ['parent' => 'id']);
    }

    private static $_routes;

    /**
     * Get saved routes.
     *
     * @return array
     */
    public static function getSavedRoutes()
    {
        if (self::$_routes === null) {
            self::$_routes = [];
            foreach (Configs::authManager()->getRoutePermissions(2) as $name => $value) {
                if ($name[0] === '/' && substr($name, -1) != '*') {
                    self::$_routes[] = $name;
                }
            }
        }
        return self::$_routes;
    }

    public static function getMenuSource()
    {
        $tableName = static::tableName();

        return (new \yii\db\Query())
            ->select(['m.id', 'm.name', 'm.route', 'parent_name' => 'p.name', 'm.parent'])
            ->from(['m' => $tableName])
            ->leftJoin(['p' => $tableName], '[[m.parent]]=[[p.id]]')
            ->all(static::getDb());
    }
}
