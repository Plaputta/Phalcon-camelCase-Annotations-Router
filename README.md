Phalcon-camelCase-Annotations-Router
====================================

An annotations router that uses the same parameter names as Phalcon\Mvc\Router and leaves the action/controller names untouched!

The behaviour of Phalcon\Mvc\Router\Annotations is inconsistent and changed from 1.2.0 over 1.2.3 to 1.2.6 (still is not consistent).

####Differences to Phalcon's Annotations Router:

* Controller names are left as they are (except for the first char which will be lowered) so you are able to use camelCase. ('class MyAwesomeTestController' will have controllerName 'myAwesomeTest' and look for views in /views/myAwesomeTest/)
* Action names are completely left as they are so you are able to use camelCase. ('function myAwesomeTestMethodAction' in abovementioned controller will have actionName 'myAwesomeTestMethodAction' and look for it's view in /views/myAwesomeTest/myAwesomeTestMethod.phtml)
* Annotations parameter name for defining supported request methods it not "methods" but "via" to go along with Phalcon\Mvc\Router.
* Method 'GET' is not implied, so defining only @Route("/") will lead to an exception, it must be either @Get("/") or @Route("/", via={"GET"}). Prevents unnoticed misbehaviour, e.g. when defining @Route("/", methods={"GET", "POST"}). (Usage of old 'methods' parameter).
* Annotation's parameter name for convertions is no more "conversors" or "converts" but, to go with the default Router, it now is "convert".
