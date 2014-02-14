<?php

namespace Nischenspringer\Mvc\Router {
    use Phalcon\Mvc\Router\Exception,
        Phalcon\Mvc\Router\Group;

    /**
     * Nischenspringer\Mvc\Router\Annotations
     *
     * A router that reads routes annotations from classes/resources.
     *
     * Differences to Phalcon's Annotations Router:
     * - Controller names are left as they (except for the first char which will be lowered) so you are able to use camelCase.
     *   ('class MyAwesomeTestController' will have controllerName 'myAwesomeTest' and look for views in /views/myAwesomeTest/)
     * - Action names are completely left as they are so you are able to use camelCase.
     *   ('function myAwesomeTestMethodAction' in abovementioned controller will have actionName 'myAwesomeTestMethodAction'
     *   and look for it's view in /views/myAwesomeTest/myAwesomeTestMethod.phtml)
     * - Annotations parameter name for defining supported request methods it not "methods" but "via" to go along with Phalcon\Mvc\Router.
     * - Method 'GET' is not implied, so defining only @Route("/") will lead to an exception, it must be either @Get("/") or
     *   @Route("/", via={"GET"}).
     *   Prevents unnoticed misbehaviour, e.g. when defining @Route("/", methods={"GET", "POST"}). (Usage of old 'methods' parameter).
     * - Annotation's parameter name for convertions is no more "conversors" or "converts" but, to go with the default Router,
     *   it now is "convert".
     *
     *<code>
     * $di['router'] = function() {
     *
     *        //Use the annotations router
     *        $router = new \Nischenspringer\Mvc\Router\Annotations(false);
     *
     *        //This will do the same as above but only if the handled uri starts with /robots
     *        $router->addResource('Robots', '/robots');
     *
     *        return $router;
     *    };
     *</code>
     */

    class Annotations extends \Phalcon\Mvc\Router implements \Phalcon\DI\InjectionAwareInterface, \Phalcon\Mvc\RouterInterface
    {
        protected $_controllerSuffix;
        protected $_controllerSuffixLength;
        protected $_actionSuffix;
        protected $_actionSuffixLength;

        protected $_handlers;
        protected $_processed;

        public function __construct($defaultRoutes = null)
        {
            $this->setControllerSuffix('Controller');
            $this->setActionSuffix('Action');
            $this->_handlers = array();
            $this->_processed = true;
        }

        protected function addHandler($handler, $prefix = null, $module = null)
        {
            $handler = $handler . $this->_controllerSuffix;

            if (!$di = $this->getDI()) {
                throw new Exception("A dependency injection container is required to access the 'annotations' service", E_ERROR);
            }

            /**
             * Phalcon is using string methods to determine the namespace/controller name, I'm using the reflection class.
             */
            try {
                $phpReflection = new \ReflectionClass($handler);
            } catch (\ReflectionException $e) {
                throw new Exception("Unable to create reflection of class '$handler'", E_ERROR, $e);
            }
            $className = $phpReflection->getShortName();
            if (substr($className, 0 - $this->_controllerSuffixLength) != $this->_controllerSuffix) {
                throw new Exception("Invalid controller suffix on class '$className'", E_ERROR);
            };

            $controller = lcfirst(substr($className, 0, 0-$this->_controllerSuffixLength));
            $namespace = $phpReflection->getNamespaceName();

            $phalconReflection = $di->getShared('annotations')->get($handler);

            if (!isset($prefix)) {
                try {
                    $prefix = $phalconReflection->getClassAnnotations()->get('RoutePrefix')->getArgument(0);
                } catch (\Phalcon\Annotations\Exception $e) {
                    $prefix = null;
                }
            }

            $methodsAnnotations = $phalconReflection->getMethodsAnnotations();
            $annotationTypes = array('Route', 'Get', 'Post', 'Put', 'Delete', 'Options');

            $group = new Group();

            if (isset($prefix)) {
                $group->setPrefix($prefix);
            }

            $groupPaths = array(
                'namespace' => $namespace,
                'controller' => $controller
            );

            if (isset($module)) {
                $groupPaths['module'] = $module;
            }

            $group->setPaths(
                $groupPaths
            );

            foreach ($methodsAnnotations as $methodName => $annotation) {
                if (substr($methodName, 0 - $this->_actionSuffixLength) != $this->_actionSuffix) continue;

                foreach ($annotationTypes as $annotationType) {
                    $routeAnnotations = $annotation->getAll($annotationType);
                    foreach ($routeAnnotations as $routeAnnotation) {
                        if (!$via = $routeAnnotation->getNamedArgument('via')) {
                            $via = array();
                        }
                        if (is_string($via)) {
                            $via = array($via);
                        }
                        if ($annotationType != 'Route') {
                            $via[] = strtoupper($annotationType);
                        }
                        $via = array_unique($via);
                        if (!count($via)) {
                            throw new Exception("No methods (via) found for route in '$namespace\\$className::$methodName'", E_ERROR);
                        }
                        if (!$routePaths = $routeAnnotation->getNamedArgument('paths')) {
                            $routePaths = array();
                        }
                        if (!isset($routePaths['action'])) {
                            $routePaths['action'] = substr($methodName, 0, 0 - $this->_actionSuffixLength);
                        }
                        $route = $group->add(
                            $routeAnnotation->getArgument(0),
                            $routePaths
                        );
                        if ($name = $routeAnnotation->getNamedArgument('name')) {
                            $route->setName($name);
                        }
                        $route->via($via);
                        if ($converts = $routeAnnotation->getNamedArgument('convert')) {
                            if (!is_array($converts)) {
                                throw new Exception("Annotation 'convert' must be an associative array if used", E_ERROR);
                            }
                            foreach ($converts as $convertName => $convertCallable) {
                                $route->convert($convertName, $convertCallable);
                            }
                        }
                    }
                }
            }
            $this->mount($group);
        }

        /**
         * Adds a resource to the annotations handler
         * A resource is a class that contains routing annotations
         *
         * @param string $handler
         * @param string $prefix
         * @return \Phalcon\Mvc\Router\Annotations
         */
        public function addResource($handler, $prefix = null)
        {
            if (!is_string($handler)) {
                throw new Exception("The handler must be a class name", E_ERROR);
            }
            $this->_handlers[] = array($handler, $prefix, null);
            $this->_processed = false;
            return $this;
        }


        /**
         * Adds a resource to the annotations handler
         * A resource is a class that contains routing annotations
         * The class is located in a module
         *
         * @param string $module
         * @param string $handler
         * @param string $prefix
         * @return \Phalcon\Mvc\Router\Annotations
         */
        public function addModuleResource($module, $handler, $prefix = null)
        {
            if (!is_string($module)) {
                throw new Exception("The module must be a valid string", E_ERROR);
            }
            if (!is_string($handler)) {
                throw new Exception("The handler must be a class name", E_ERROR);
            }
            $this->_handlers[] = array($handler, $prefix, $module);
            $this->_processed = false;
            return $this;
        }

        /**
         * Changes the controller class suffix
         *
         * @param string $controllerSuffix
         */
        public function setControllerSuffix($controllerSuffix)
        {
            $this->_controllerSuffix = $controllerSuffix;
            $this->_controllerSuffixLength = strlen($controllerSuffix);
        }


        /**
         * Changes the action method suffix
         *
         * @param string $actionSuffix
         */
        public function setActionSuffix($actionSuffix)
        {
            $this->_actionSuffix = $actionSuffix;
            $this->_actionSuffixLength = strlen($actionSuffix);
        }

        public function handle($uri = null) {
            /**
            * If 'uri' isn't passed as parameter it reads $_GET['_url']
            */
            if (!isset($uri)) {
                $uri = $this->getRewriteUri();
            }
            if (!$this->_processed) {
                for ($i = count($this->_handlers)-1; $i >= 0; $i--) {
                    $handle = array_pop($this->_handlers);
                    $this->addHandler($handle[0], $handle[1], $handle[2]);
                }
            }
            return parent::handle($uri);
        }

    }
}
