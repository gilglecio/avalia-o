<?php session_start();

date_default_timezone_set('America/Sao_Paulo');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('APP', ROOT.DS.'app');
define('VENDOR', ROOT.DS.'vendor');
define('WWW', ROOT.DS.'www');
define('VIEWS', APP.DS.'views');
define('HELPERS', APP.DS.'helpers');
define('ROUTES', APP.DS.'routes');
define('CACHE', APP.DS.'cache');
define('MODELS', APP.DS.'models');
define('ENV_DEFAULT', ($_SERVER['SERVER_NAME'] != 'localhost' ? 'prod' : 'dev' ));

if (ENV_DEFAULT == 'dev') {
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 1);
}

require VENDOR.DS.'autoload.php';
require HELPERS.DS.'toolkit.php';
require HELPERS.DS.'ValidateDate.php';

Twig_Autoloader::register();

// ini_set("SMTP", config('mail.smtp'));
// ini_set("smtp_port", config('mail.port'));

\ActiveRecord\Config::initialize(function($cfg) {
	$cfg->set_model_directory(MODELS);
	$cfg->set_connections(array(
		'development' => config('db.driver').'://'.config('db.user').':'.config('db.pass').'@'.config('db.host').'/'.config('db.base')
	));
});

\Slim\Extras\Views\Twig::$twigOptions = array(
    'charset'           => 'utf-8',
    'cache'             => CACHE,
    'auto_reload'       => true,
    'strict_variables'  => false,
    'autoescape'        => true
);

\Slim\Extras\Views\Twig::$twigExtensions = array(
    'Twig_Extensions_Slim',
);

$app = new \Slim\Slim(array(
	'templates.path' => VIEWS,
	'view' => new \Slim\Extras\Views\Twig(),
	'urladm' => '/admin',
	'urlbase_adm' => config('domain').'admin'
));

// $app->add(new \Slim\Extras\Middleware\CsrfGuard());
$app->add( new \Slim\Middleware\SessionCookie());
$app->add( new \Slim\Middleware\Flash());

$authenticate = function ($rule = 'RULE_ADMIN') use ($app) {
	
	$_SESSION['before'] = $_SERVER['REQUEST_URI'];

	if (! isset($_SESSION['app.user_id'])) {
		return $app->redirect(config('domain'));
	}
	
	$app->view->appendData(array(
		'app_user' => User::getUserLogger()
	));
};

// dd(Sending::encodeToken('$1$OvH3q9io$i6dMOmFCssPomWsQkdlCO/'));


$prod_domain = '';

if (ENV_DEFAULT == 'prod') $prod_domain = '/avaliacao';

$app->view->appendData(array(
	'urladm' => $prod_domain.$app->settings['urladm'],
	'referer' => $app->request->getReferrer(),
	'profile_types' => User::$profile_types,
	'base' => config('domain'),
	'env' => ENV_DEFAULT,
	'evaluation_tags' => Evaluation::$evaluation_tags,
	'csrf_key' => 'csrf_token',
	'csrf_token' => 'token'
));


\Slim\Route::setDefaultConditions(array(
	'id' => '[0-9]+',
	'profile_type' => 'appraiser|valued|admin'
));

foreach (array(
	'admin', 
	'front',
	'hooks'
) as $route) require ROUTES.DS.$route.'.php';


$app->run();