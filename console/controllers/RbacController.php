<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        
        // add "administrator" permission
        // TODO: This is now how RBAC is meant to be done
        $administrate = $auth->createPermission('administrator');
        $administrate->description = 'Administrate the system';
        $auth->add($administrate);

        // add "admin" role
        // with "administrator" permissions
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $administrate);

        // Make userid 1 an admin
        $auth->assign($admin, 1);
    }
}
