<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2020-04-30 16:23:11
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2020-08-03 14:55:50
 */

namespace diandi\admin\controllers;

use backend\controllers\BaseController;
use diandi\admin\models\Bloc;
use diandi\admin\models\form\Baidu;
use diandi\admin\models\form\Email;
use diandi\admin\models\form\Map;
use diandi\admin\models\form\Sms;
use diandi\admin\models\form\Wechatpay;
use diandi\admin\models\form\Wxapp;
use Yii;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Description of RuleController.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 1.0
 */
class SettingController extends BaseController
{
    public function actions()
    {
        global $_GPC,$_W;
        $bloc_id = $_GPC['bloc_id'];
        $bloc = Bloc::findOne($bloc_id);
    }

    public function actionBaidu()
    {
        global $_GPC,$_W;

        $model = new Baidu();
        $bloc_id = $_GPC['bloc_id'];
         
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }
        return $this->render('baidu', [
            'model' => $model,
        ]);
    }

    public function actionWechatpay()
    {
        $model = new Wechatpay();
        $bloc_id = Yii::$app->request->get('bloc_id');
          
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }
        return $this->render('wechatpay', [
            'model' => $model,
        ]);
    }

    public function actionSms()
    {
        $model = new Sms();
        $bloc_id = Yii::$app->request->get('bloc_id');
         
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }

        return $this->render('sms', [
            'model' => $model,
        ]);
    }

    public function actionEmail()
    {
        $model = new Email();
        $bloc_id = Yii::$app->request->get('bloc_id');
         
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }

        return $this->render('email', [
            'model' => $model,
        ]);
    }

    public function actionWxapp()
    {
        $model = new Wxapp();
        $bloc_id = Yii::$app->request->get('bloc_id');
         
         
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }

        return $this->render('wxapp', [
            'model' => $model,
        ]);
    }

    public function actionMap()
    {
        $model = new Map();
        $bloc_id = Yii::$app->request->get('bloc_id');
         
        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $Res = $model->saveConf($bloc_id);
            if ($Res['code']==200) {
                Yii::$app->session->setFlash('success',$Res['message']);
            } else {
                Yii::$app->session->setFlash('error',$Res['message']);
            }
            
        } else {
            $model->getConf($bloc_id);
        }
        
        return $this->render('map', [
            'model' => $model,
        ]);
    }
}
