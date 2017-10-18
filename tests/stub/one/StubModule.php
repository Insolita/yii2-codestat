<?php
/**
 * Created by solly [18.10.17 23:03]
 */

namespace tests\stub\one;

use yii\base\Module;

class StubModule extends Module
{
    /**
     * @event ActionEvent an event raised before executing a controller action.
     * You may set [[ActionEvent::isValid]] to be `false` to cancel the action execution.
     */
    const EVENT_BEFORE_ACTION = 'beforeAction';
    /**
     * @event ActionEvent an event raised after executing a controller action.
     */
    const EVENT_AFTER_ACTION = 'afterAction';
    
    /**
     * @var array custom module parameters (name => value).
     */
    public $params = [];
    /**
     * @var string an ID that uniquely identifies this module among other modules which have the same [[module|parent]].
     */
    public $id;
    /**
     * @var Module the parent module of this module. `null` if this module does not have a parent.
     */
    public $module;
    /**
     * @var string|bool the layout that should be applied for views within this module. This refers to a view name
     * relative to [[layoutPath]]. If this is not set, it means the layout value of the [[module|parent module]]
     * will be taken. If this is `false`, layout will be disabled within this module.
     */
    public $layout;
    /**
     * @var array mapping from controller ID to controller configurations.
     * Each name-value pair specifies the configuration of a single controller.
     * A controller configuration can be either a string or an array.
     * If the former, the string should be the fully qualified class name of the controller.
     * If the latter, the array must contain a `class` element which specifies
     * the controller's fully qualified class name, and the rest of the name-value pairs
     * in the array are used to initialize the corresponding controller properties. For example,
     *
     * ```php
     * [
     *   'account' => 'app\controllers\UserController',
     *   'article' => [
     *      'class' => 'app\controllers\PostController',
     *      'pageTitle' => 'something new',
     *   ],
     * ]
     * ```
     */
    public $controllerMap = [];
    /**
     * @var string the namespace that controller classes are in.
     * This namespace will be used to load controller classes by prepending it to the controller
     * class name.
     *
     * If not set, it will use the `controllers` sub-namespace under the namespace of this module.
     * For example, if the namespace of this module is `foo\bar`, then the default
     * controller namespace would be `foo\bar\controllers`.
     *
     * See also the [guide section on autoloading](guide:concept-autoloading) to learn more about
     * defining namespaces and how classes are loaded.
     */
    public $controllerNamespace;
    /**
     * @var string the default route of this module. Defaults to `default`.
     * The route may consist of child module ID, controller ID, and/or action ID.
     * For example, `help`, `post/create`, `admin/post/create`.
     * If action ID is not given, it will take the default value as specified in
     * [[Controller::defaultAction]].
     */
    public $defaultRoute = 'default';
}
