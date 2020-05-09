<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 16:36:46
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-08 22:15:45
 */
 

namespace diandi\admin\controllers;

use diandi\admin\components\ItemController;
use diandi\admin\components\Item;
use Yii;

/**
 * PermissionController implements the CRUD actions for AuthItem model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class PermissionController extends ItemController
{
    public $type;
    
    public $module_name;
    
    public $parent_type=1;

    

    public function actions()
    {
        $this->module_name =  Yii::$app->request->get('module_name','sys');   
        $this->type =  $this->module_name=='sys'?0:1;   
        
    }

    /**
     * @inheritdoc
     */
    public function labels()
    {
        
        return[
            'Item' => 'Permission',
            'Items' => 'Permissions',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Item::TYPE_PERMISSION;
    }

    
}
