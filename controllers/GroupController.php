<?php

/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-04 17:44:12
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-09-09 16:25:51
 */

namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\UserGroup;
use diandi\admin\models\searchs\UserGroupSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\controllers\BaseController;
use diandi\admin\components\Configs;
use diandi\admin\components\Item;
use diandi\admin\models\AuthItem;
use diandi\admin\components\Route;
use diandi\admin\models\Route as ModelsRoute;

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
                ],
            ],
        ];
    }

    /**
     * Lists all UserGroup models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserGroupSearch([
            'module_name' => $this->module_name
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

        $items = $manager->getAuths($id, $this->is_sys);

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
        $model = $this->findModel($id);

        $success = 0;
        // 规则
        if ($items['group']) {
            $success += $model->addChildren($items['group']);
        }

        // 权限
        if ($items['permission']) {
            $item = new Item([
                'name' => $model['name'],
                'module_name' => $model['module_name'],
                'type' => $model['type'],
                'id' => $id,
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
                'type' => $model['type'],
                'child_type' => 0,
                'description' => $model['description'],
                'data' => '',
                'pid' => 0,
            ]);
            $route = new ModelsRoute($item);

            $success += $route->addChildren($items['route'], 2);
        }

        Yii::$app->getResponse()->format = 'json';

        $items = $manager->getAuths($model['id'], $this->is_sys);
        return array_merge($items, ['success' => $success]);
    }

    /**
     * Assign or remove items.
     *
     * @param string $id
     *
     * @return array
     */
    public function actionRemove($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
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
                'type' => $model['type'],
                'id' =>  $model['id'],
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
                'type' => $model['type'],
                'id' => $model['id'],
                'parent_type' => 2,
                'description' => $model['description'],
                'data' => '',
                'pid' => 0,
            ]);
            $route = new ModelsRoute($item);
            $success += $route->removeChildren($items);
        }

        Yii::$app->getResponse()->format = 'json';
        $manager = Configs::authManager();

        $items = $manager->getAuths($model['id'], $this->is_sys);
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
        if (($model = UserGroup::findOne($id)) !== null) {
            return new UserGroup($model);
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
