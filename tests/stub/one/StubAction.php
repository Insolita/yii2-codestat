<?php
/**
 * Created by solly [18.10.17 7:58]
 */

namespace tests\stub\one;

use yii\base\Action;
use function compact;

class StubAction extends Action
{
    public $viewName;
    
    public function run($id)
    {
        return $this->controller->render($this->viewName, compact('id'));
    }
}
