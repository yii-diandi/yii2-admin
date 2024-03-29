<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 13:12:18
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-01-13 11:19:46
 */

namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\Route;
use  backend\controllers\BaseController;
use diandi\admin\acmodels\AuthItem;
use yii\filters\VerbFilter;

/**
 * Description of RuleController.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class RouteController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                    'refresh' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Route models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Route();
        return $this->render('index', ['routes' => $model->getRoutes()]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        Yii::$app->getResponse()->format = 'json';
        $routes = Yii::$app->getRequest()->post('route', '');
        $routes = preg_split('/\s*,\s*/', trim($routes), -1, PREG_SPLIT_NO_EMPTY);
        $model = new Route();
        $model->addNew($routes);

        // 给item同步添加数据
        $AcmodelsAuthItem = new AuthItem();
        $items = [
            'permission_type' => 0,
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

        return $model->getRoutes();
    }

    /**
     * Assign routes.
     *
     * @return array
     */
    public function actionAssign()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $Res = $model->addNew($routes);
        Yii::$app->getResponse()->format = 'json';

        return $model->getRoutes();
    }

    /**
     * Remove routes.
     *
     * @return array
     */
    public function actionRemove()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new Route();
        $model->remove($routes);
        Yii::$app->getResponse()->format = 'json';

        return $model->getRoutes();
    }

    /**
     * Refresh cache.
     *
     * @return type
     */
    public function actionRefresh()
    {
        $model = new Route();
        $model->invalidate();
        Yii::$app->getResponse()->format = 'json';

        return $model->getRoutes();
    }
}
