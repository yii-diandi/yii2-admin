<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-11 15:07:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-07-07 09:03:44
 */

namespace diandi\admin\components;

use Yii;
use diandi\admin\models\BlocStore;
use diandi\admin\models\searchs\BlocStoreSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\controllers\BaseController;
use common\helpers\ErrorsHelper;
use common\helpers\ImageHelper;
use common\models\DdRegion;
use yii\web\HttpException;

/**
 * StoreController implements the CRUD actions for BlocStore model.
 */
class StoreController extends BaseController
{
    public $bloc_id;

    public $extras = [];

    public function actions()
    {
        $this->bloc_id = Yii::$app->request->get('bloc_id', 0);
        $actions = parent::actions();
        $actions['get-region'] = [
            'class' => \diandi\region\RegionAction::className(),
            'model' => DdRegion::className(),
        ];

        return $actions;
    }

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
     * Lists all BlocStore models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $bloc_id = $this->bloc_id ? $this->bloc_id : Yii::$app->params['bloc_id'];
        $searchModel = new BlocStoreSearch([
            'bloc_id' => $bloc_id,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BlocStore model.
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
        $model['logo'] = ImageHelper::tomedia($model['logo']);
        $model['extra'] = unserialize($model['extra']);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new BlocStore model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if ($this->module->id == 'admin') {
            $model = new BlocStore([
                'extras' => $this->extras,
                ]);
            if (Yii::$app->request->isPost) {
                $data = Yii::$app->request->post();
                // $data['BlocStore']['lng_lat'] = implode(',',$data['BlocStore']['lng_lat']);
                if ($model->load($data) && $model->save()) {
                    return $this->redirect(['view', 'id' => $model->store_id, 'bloc_id' => $model->bloc_id]);
                } else {
                    $msg = ErrorsHelper::getModelError($model);
                    throw new HttpException(400, $msg);
                }
            }

            return $this->render('create', [
                'model' => $model,
                'bloc_id' => $this->bloc_id,
            ]);
        } else {
            throw new HttpException('400', '请在公司管理中添加商户');
        }
    }

    /**
     * Updates an existing BlocStore model.
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
        $model['extra'] = unserialize($model['extra']);
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->store_id, 'bloc_id' => $model->bloc_id]);
            } else {
                $error = ErrorsHelper::getModelError($model);
                Yii::$app->session->setFlash('error', $error);

                return  $this->refresh();
            }
        }

        return $this->render('update', [
                'bloc_id' => $this->bloc_id,
                'model' => $model,
        ]);
    }

    /**
     * Deletes an existing BlocStore model.
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
        $bloc_id = $this->bloc_id;

        return $this->redirect(['index', 'bloc_id' => $bloc_id]);
    }

    /**
     * Finds the BlocStore model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return BlocStore the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $BlocStore = new BlocStore([
            'extras' => $this->extras,
        ]);
        if (($model = $BlocStore::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
