<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-01 11:43:39
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-01 19:46:48
 */

namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\UserBloc;
use diandi\admin\models\searchs\UserBlocSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\controllers\BaseController;
use diandi\admin\components\BlocUser;

/**
 * UserBlocController implements the CRUD actions for UserBloc model.
 */
class UserBlocController extends BaseController
{
    public $bloc_id;

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

    public function actions()
    {
        $bloc_id = Yii::$app->request->get('bloc_id');
        $this->bloc_id = $bloc_id;
    }

    /**
     * Lists all UserBloc models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserBlocSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // 获取当前用户所有的公司
        $blocs = BlocUser::getMybloc();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'bloc_id' => $this->bloc_id,
        ]);
    }

    /**
     * Displays a single UserBloc model.
     *
     * @param int $id
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'bloc_id' => $this->bloc_id,
        ]);
    }

    /**
     * Creates a new UserBloc model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserBloc();
        $model->status = 1;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'bloc_id' => $this->bloc_id,
        ]);
    }

    /**
     * Updates an existing UserBloc model.
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
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'bloc_id' => $this->bloc_id,
        ]);
    }

    /**
     * Deletes an existing UserBloc model.
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
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the UserBloc model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return UserBloc the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = UserBloc::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
