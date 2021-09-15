<?php

/**
 * @Author: Wang Chunsheng 2192138785@qq.com
 * @Date:   2020-04-14 00:49:51
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-06-21 14:46:39
 */


namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\Assignment;
use diandi\admin\models\searchs\Assignment as AssignmentSearch;
use backend\controllers\BaseController;
use diandi\admin\models\AuthAssignmentGroup;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AssignmentController implements the CRUD actions for Assignment model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AssignmentController extends BaseController
{
    public $userClassName;
    public $idField = 'id';
    public $usernameField = 'username';
    public $fullnameField;
    public $searchClass;
    public $extraColumns = [];

    public $type;

    public $module_name;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->userClassName === null) {
            $this->userClassName = Yii::$app->getUser()->identityClass;
            $this->userClassName = $this->userClassName ?: 'diandi\admin\models\User';
        }
        $this->module_name =  Yii::$app->request->get('module_name', 'sys');
        $this->type =  $this->module_name == 'sys' ? 0 : 1;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        Yii::$app->params['plugins'] = 'shop';

        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'assign' => ['post'],
                    'assign' => ['post'],
                    'revoke' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Assignment models.
     * @return mixed
     */
    public function actionIndex()
    {

        if ($this->searchClass === null) {
            $searchModel = new AssignmentSearch;
            $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams(), $this->userClassName, $this->usernameField);
        } else {
            $class = $this->searchClass;
            $searchModel = new $class;
            $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'module_name' => $this->module_name,
            'idField' => $this->idField,
            'usernameField' => $this->usernameField,
            'extraColumns' => $this->extraColumns,
        ]);
    }

    /**
     * Displays a single Assignment model.
     * @param  integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $items = $model->getItems($this->type);

        return $this->render('view', [
            'module_name' => $this->module_name,
            'items' => $items,
            'model' => $model,
            'idField' => $this->idField,
            'usernameField' => $this->usernameField,
            'fullnameField' => $this->fullnameField,
        ]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);

        $model = new Assignment([
            'id' => $id,
            'type' => $this->type
        ]);

        $success = $model->assign($items);
        Yii::$app->response->format = 'json';


        return array_merge($model->getItems($this->type), ['success' => $success]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment([
            'id' => $id,
            'type' => $this->type
        ]);
        $success = $model->revoke($items);
        Yii::$app->response->format = 'json';

        return array_merge($model->getItems($this->type), ['success' => $success]);
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userClassName;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment([
                'id' => $id,
                'type' => $this->type
            ], $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
