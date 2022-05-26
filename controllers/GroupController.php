<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 17:44:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-13 23:53:28
 */

namespace diandi\admin\controllers;

use backend\controllers\BaseController;
use diandi\admin\acmodels\AuthItem as AcmodelsAuthItem;
use diandi\admin\components\Configs;
use diandi\admin\components\Item;
use diandi\admin\components\Route;
use diandi\admin\models\AuthItem;
use diandi\admin\models\Route as ModelsRoute;
use diandi\admin\models\searchs\UserGroupSearch;
use diandi\admin\models\UserGroup;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * GroupController implements the CRUD actions for UserGroup model.
 */
class GroupController extends BaseController
{
    public $is_sys = 0; //是否是系统

    public $module_name = 'sys';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                    'remove' => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
        $this->module_name = Yii::$app->request->get('module_name', 'sys');
        $this->is_sys = $this->module_name === 'sys' ? 1 : 0;
    }

    /**
     * Lists all UserGroup models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserGroupSearch([
            'module_name' => $this->module_name,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single UserGroup model.
     *
     * @param int $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $manager = Configs::authManager();

        $items = $manager->getAuths($model->item_id, $this->is_sys);

        return $this->render('view', [
            'model' => $model,
            'module_name' => $this->module_name,
            'items' => $items,
        ]);
    }

    /**
     * Assign items.
     *
     * @param string $id
     *
     * @return array
     */
    public function actionAssign($id)
    {
        $manager = Configs::authManager();

        $items = Yii::$app->getRequest()->post('items', []);
        
        $parentGroup = UserGroup::find()->where(['id'=>$id])->one();
      
        $model =  new UserGroup($parentGroup);
      
        $success = 0;

        // 规则
        if ($items['group']) {
            $success += $model->addChildren($items);
        }

        // 权限
        if ($items['permission']) {
            $item = new Item([
                'name' => $model['name'],
                'module_name' => $model['module_name'],
                'is_sys' => $model['is_sys'],
                'id' => $model['item_id'],
                'item_id' => $model['item_id'],
                'child_type' => 1,
                'ruleName' => '',
                'description' => $model['description'],
                'data' => '',
            ]);

            $permission = new AuthItem($item);
            
            $success += $permission->addChildren($items, 2);
        }

        // 路由
        if ($items['route']) {
            $item = new Route([
                'id' => $model['id'],
                'name' => $model['name'],
                'title' => '',
                'module_name' => $model['module_name'],
                'is_sys' => $model['is_sys'],
                'id' => $model['item_id'],
                'item_id' => $model['item_id'],
                'child_type' => 0,
                'description' => $model['description'],
                'data' => '',
                'pid' => 0,
            ]);
            $route = new ModelsRoute($item);

            $success += $route->addChildren($items, 2);
        }

        Yii::$app->getResponse()->format = 'json';

        $items = $manager->getAuths($model['item_id'], $this->is_sys);

        return array_merge($items, ['success' => $success]);
    }

    /**
     * Assign or remove items.
     *
     * @return array
     */
    public function actionRemove($id)
    {
        global $_GPC;
        $items = $_GPC['items'];
           
        $parentGroup = UserGroup::find()->where(['id'=>$id])->one();
        $model =  new UserGroup($parentGroup);
        $success = 0;
     
        // 规则
        if ($items['group']) {
            $success += $model->removeChildren($items);
        }
   
        // 权限
        if ($items['permission']) {
            $item = new Item([
                'name' => $model['name'],
                'module_name' => $model['module_name'],
                'is_sys' => $model['is_sys'],
                'id' => $model['item_id'],
                'item_id' => $model['item_id'],
                'permission_type' => 1,
                'permission_level' => 0,
                'parent_type' => 2,
                'child_type' => 1,
                'ruleName' => '',
                'description' => $model['description'],
                'data' => '',
            ]);
            $permission = new AuthItem($item);
            $success += $permission->removeChildren($items);
        }

        // 路由
        if ($items['route']) {
            $item = new Route([
                'name' => $model['name'],
                'title' => $model['name'],
                'module_name' => $model['module_name'],
                'is_sys' => $model['is_sys'],
                'id' => $model['id'],
                'item_id' => $model['item_id'],
                'parent_type' => 2,
                'description' => $model['description'],
                'data' => '',
                'pid' => 0,
            ]);
            $route = new ModelsRoute($item);
            $success += $route->removeChildren($items);
        }

        $manager = Configs::authManager();

        $items = $manager->getAuths($model['item_id'], $this->is_sys);

        Yii::$app->response->format = Response::FORMAT_JSON;

        return array_merge($items, ['success' => $success]);
    }

    /**
     * Creates a new UserGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserGroup();
        $model->is_sys = $this->is_sys;
        $model->module_name = $this->module_name;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // 给item同步添加数据
            $AcmodelsAuthItem = new AcmodelsAuthItem();
            $items = [
                'permission_type' => 2,
                'name' => $model->name,
                'is_sys' => $model->is_sys,
                'parent_id' => 0,
                'permission_level' => 0,
                'module_name' => $model->module_name,
            ];

            if ($AcmodelsAuthItem->load($items, '') && $AcmodelsAuthItem->save()) {
                $model->updateAll([
                    'item_id' => $AcmodelsAuthItem->id,
                ], [
                    'id' => $model->id,
                ]);
            }

            return $this->redirect(['view', 'id' => $model->id, 'module_name' => $this->module_name]);
        }

        return $this->render('create', [
            'module_name' => $this->module_name,
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing UserGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = UserGroup::findOne($id);

        $model->is_sys = $this->is_sys;
        $model->module_name = $this->module_name;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // 给item同步添加数据
            $AcmodelsAuthItem = new AcmodelsAuthItem();
            $items = [
                  'permission_type' => 2,
                  'name' => $model->name,
                  'is_sys' => $model->is_sys,
                  'parent_id' => 0,
                  'permission_level' => 0,
                  'module_name' => $model->module_name,
            ];

            $AcmodelsAuthItem->updateAll($items, [
                'id' => $model->item_id,
            ]);

            return $this->redirect(['view', 'id' => $model->id, 'module_name' => $this->module_name]);
        }

        return $this->render('update', [
            'module_name' => $this->module_name,
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing UserGroup model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $UserGroup = $this->findModel($id);
        $AcmodelsAuthItem = AcmodelsAuthItem::findOne($UserGroup->item_id);
        if ($AcmodelsAuthItem) {
            $AcmodelsAuthItem->delete();
        }
        UserGroup::findOne($id)->delete();

        return $this->redirect(['index', 'module_name' => $this->module_name]);
    }

    /**
     * Finds the UserGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return UserGroup the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserGroup::find()->where(['id'=>$id])->one()) !== null) {
            
            return new UserGroup($model);
        }

        throw new NotFoundHttpException('检查数据ID');
    }
}
