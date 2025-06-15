<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-03-28 16:42:33
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-07-08 15:57:52
 */


namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\Menu;
use diandi\admin\models\searchs\Menu as MenuSearch;
use backend\controllers\BaseController;
use common\helpers\ArrayHelper as HelpersArrayHelper;
use diandi\addons\models\searchs\DdAddons;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use diandi\admin\components\Helper;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * MenuController implements the CRUD actions for Menu model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class MenuController extends BaseController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        Yii::$app->params['plugins'] = 'sysai';

        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch;
        // $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $query = Menu::find()->where(['is_sys' => 1])->orderBy('order');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param  integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Menu;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Helper::invalidate();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $addons = DdAddons::find()->asArray()->all();
            $parentMent = Menu::find()->where(['is_sys' => 1])->asArray()->all();
            $parentMenu =  HelpersArrayHelper::itemsMergeDropDown(HelpersArrayHelper::itemsMerge($parentMent, 0, "id", 'parent', '-'), "id", 'name');

            return $this->render('create', [
                'model' => $model,
                'addons' => $addons,
                'parentMenu' => $parentMenu,

            ]);
        }
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionUpdate($id)
   {

        $model = $this->findModel($id);
        if ($model->menuParent) {
            $model->parent_name = $model->menuParent->name;
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Helper::invalidate();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $addons = DdAddons::find()->asArray()->all();

            $parentMent = Menu::find()->where(['is_sys' => 1])->asArray()->all();
            $parentMenu =  HelpersArrayHelper::itemsMergeDropDown(HelpersArrayHelper::itemsMerge($parentMent, 0, "id", 'parent', '-'), "id", 'name');

            return $this->render('update', [
                'model' => $model,
                'addons' => $addons,
                'parentMenu' => $parentMenu,

            ]);
        }
    }

    public function actionUpdateFiles()
    {
        if (Yii::$app->request->isPost) {

            $pk = Yii::$app->request->post('pk');
            $id = unserialize(base64_decode($pk));

            $model = $this->findModel($id);

            $files = Yii::$app->request->post('name');
            $value = Yii::$app->request->post('value');
            $Res = $model->updateAll([$files => $value], ['id' => $id]);
            return true;
        }
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Helper::invalidate();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
