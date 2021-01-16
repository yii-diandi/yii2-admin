<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 15:43:16
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-08 18:01:44
 */
 

namespace diandi\admin\models\searchs;

use diandi\addons\models\DdAddons;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use diandi\admin\components\Configs;
use diandi\admin\components\Item;
use diandi\admin\models\AuthItem as ModelsAuthItem;
use yii\data\ActiveDataProvider;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItem extends Model
{
    const TYPE_ROUTE = 101;
    

    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;
    public $parent_id;
    
    public $module_name;
    

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ruleName', 'description','parent_id','module_name'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('rbac-admin', 'Name'),
            'item_name' => Yii::t('rbac-admin', 'Name'),
            'type' => Yii::t('rbac-admin', 'Type'),
            'description' => Yii::t('rbac-admin', 'Description'),
            'ruleName' => Yii::t('rbac-admin', 'Rule Name'),
            'data' => Yii::t('rbac-admin', 'Data'),
            'parent_id' => Yii::t('rbac-admin', 'parent_id'),
            'module_name' => Yii::t('rbac-admin', 'module_name'),
        ];
    }

        /* 获取模块 */
        public function getAddons()
        {
            return $this->hasOne(DdAddons::className(), ['module_name' => 'identifie']);
        }
    

    /**
     * Search authitem
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        /* @var \yii\rbac\Manager $authManager */
        $authManager = Configs::authManager();
      
        $items = array_filter($authManager->getPermissions(), function($item) {
            return $this->type == Item::TYPE_PERMISSION xor strncmp($item->name, '/', 1) === 0;
        });
        $this->load($params);
        if ($this->validate()) {

            $search = mb_strtolower(trim($this->name));
            $desc = mb_strtolower(trim($this->description));
            $module_name = $this->module_name;
            $ruleName = $this->ruleName;
            foreach ($items as $name => $item) {
                $f = (empty($search) || mb_strpos(mb_strtolower($item->name), $search) !== false) &&
                    (empty($desc) || mb_strpos(mb_strtolower($item->description), $desc) !== false) &&
                    (empty($module_name) || mb_strpos(mb_strtolower($item->module_name), $module_name) !== false) &&
                    (empty($ruleName) || $item->ruleName == $ruleName);
                if (!$f) {
                    unset($items[$name]);
                }
            }
        }

        return new ArrayDataProvider([
            'key'=>function($model){
                return $model->name;
            },
            'allModels' => $items,
        ]);
    }

     
}
