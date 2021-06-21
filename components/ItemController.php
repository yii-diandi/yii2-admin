<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-03 19:03:01
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2021-03-31 09:56:07
 */

namespace diandi\admin\components;

use common\helpers\ArrayHelper;
use common\helpers\ErrorsHelper;
use diandi\addons\models\DdAddons;
use Yii;
use diandi\admin\models\AuthItem;
use diandi\admin\models\AuthItemModel;
use diandi\admin\models\searchs\AuthItem as AuthItemSearch;
use diandi\admin\models\searchs\AuthItemSearch as SearchsAuthItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\NotSupportedException;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\HttpException;

/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 *
 * @property int   $type
 * @property array $labels
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class ItemController extends Controller
{
    public $type;
    
    public $module_name;
    
    public $parent_type;
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all AuthItem models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $authManager = Configs::authManager();
        $searchModel = new SearchsAuthItemSearch(['type' => $this->type,'module_name'=>$this->module_name]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
      
        // $query = AuthItemModel::find();
        

        // $dataProvider = new ActiveDataProvider([
        //     'query' => $query,
        //     'pagination' => false,
        // ]);
        $DdAddons = new DdAddons();
        $addons = [];
        $addons = $DdAddons->find()->indexBy('identifie')->select(['title'])->asArray()->column();
        $addons['sys'] = '系统';
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'addons'=>$addons,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single AuthItem model.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem(null);
        $model->type = $this->type;

        $module_name = $this->module_name;
        
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->getRequest()->post();
            if($model->load($data) && $model->save()){
                return $this->redirect(['view', 'id' => $model->name,'module_name'=>$module_name]);
            }else{
                $msg = ErrorsHelper::getModelError($model);
                Yii::$app->session->setFlash('error', $msg);
            }
        } 


        $parentMent = AuthItemModel::find()->where(['module_name'=>$module_name])->asArray()->all();
        $parentItem =  ArrayHelper::itemsMergeDropDown(ArrayHelper::itemsMerge($parentMent,0,"id",'parent_id','-'),"id",'name');

        
        $addons = DdAddons::find()->asArray()->all();
        return $this->render('create', [
            'addons' => $addons,
            'model' => $model,
            'module_name' => $module_name,
            'parentItem' => $parentItem
        ]);
        
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $module_name = $this->module_name;

        if(yii::$app->request->isPost){
            
            if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->name,'module_name'=>$module_name]);

            }else{
                throw new HttpException('400',ErrorsHelper::getModelError($model));
            }
        }

        $addons = DdAddons::find()->asArray()->all();
        $parentMent = AuthItemModel::find()->where(['module_name'=>$module_name])->asArray()->all();
        $parentItem =  ArrayHelper::itemsMergeDropDown(ArrayHelper::itemsMerge($parentMent,0,"id",'parent_id','-'),"id",'name');

        return $this->render('update', [
            'addons' => $addons,
            'model' => $model,
            'module_name' => $module_name,
            'parentItem' => $parentItem

            ]);
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        Configs::authManager()->remove($model->item);
        Helper::invalidate();
        $module_name = $this->module_name;
        return $this->redirect(['index','module_name'=>$module_name]);
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
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->addChildren($items);
        if(!$success){
           $msg = ErrorsHelper::getModelError($model); 
        }
        
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success,'error'=>$msg]);
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
        $success = $model->removeChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath()
    {
        return $this->module->getViewPath().DIRECTORY_SEPARATOR.'item';
    }

    /**
     * Label use in view.
     *
     * @throws NotSupportedException
     */
    public function labels()
    {
        throw new NotSupportedException(get_class($this).' does not support labels().');
    }

    /**
     * Type of Auth Item.
     *
     * @return int
     */
    public function getType()
    {
       return  $this->type;
    }

 

     /**
     * Type of Auth Item.
     *
     * @return int
     */
    public function getModule_name()
    {
       return  $this->module_name;

    }

 

     /**
     * @inheritdoc
     */
    public function getParent_type()
    {
       return  $this->parent_type;
    }

 
    
    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param string $id
     *
     * @return AuthItem the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $auth = Configs::authManager();  
        // $item = $this->type === Item::TYPE_PERMISSION ? $auth->getRole($id) : $auth->getPermission($id);
        $item = $auth->getPermission($id);
        
        if ($item) {
            // $item->type = $this->parent_type;  
            return new AuthItem($item);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
