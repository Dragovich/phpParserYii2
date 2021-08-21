<?php

namespace app\commands;

use yii\console\Controller;

class UpdateController extends Controller {
    /**
     * @throws \yii\db\Exception
     */
    public function actionIndex() {
        ParsePages::start();
    }
}