<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace tests\stub\one;

use yii\console\Controller;

class HelloController extends Controller
{
    
    public function init()
    {
        //$this->enableStreamLog([], ['info', 'error', 'trace', 'warning'], true);
        parent::init();
    }
    
    /**
     * This command echoes what you have entered as the message.
     *
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        for ($i = 0; $i < 100; $i++) {
            if ($i == 5) {
                $this->stdout('hello');
            }
            if ($i % 20 === 0) {
                $this->stdout('hello2');
            } elseif ($i % 15 === 0) {
                $this->stdout('hello3');
            }
        }
    }
    
    public function actionFaker()
    {
    
    }
}
