<?php
/**
 * Created by solly [18.10.17 8:07]
 */

namespace tests\stub\two;

use yii\base\BootstrapInterface;
use yii\base\Model;

class Bootstrapper implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param \yii\base\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        \Yii::$app->set('dummy', Model::class);
    }
}
