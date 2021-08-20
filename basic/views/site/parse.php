<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;

try {
    echo app\commands\ParsePages::updateDBRows();
} catch (\yii\db\Exception $e) {
    echo 'Не успешно';
}

?>
