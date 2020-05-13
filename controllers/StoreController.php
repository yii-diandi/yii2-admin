<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-05-11 15:07:52
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-05-12 08:01:27
 */
 

namespace diandi\admin\controllers;

use Yii;
use diandi\admin\models\BlocStore;
use diandi\admin\models\searchs\BlocStoreSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\controllers\BaseController;
use common\helpers\ErrorsHelper;
use common\helpers\ImageHelper;
use common\models\DdRegion;
use diandi\admin\components\StoreController as ComponentsStoreController;
use yii\web\HttpException;

/**
 * StoreController implements the CRUD actions for BlocStore model.
 */
class StoreController extends ComponentsStoreController
{
    public $bloc_id;
    
   
}
