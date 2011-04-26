<?php

/**
 * Class names updater.
 *
 * This file is part of the Nette Framework (http://nette.org)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 * @phpversion 5.3
 */

require __DIR__ . '/../../Nette-minified/nette.min.php';


echo '
ClassUpdater version 1.0
------------------------
';

$options = getopt('d:f');

if (!$options) { ?>
Usage: php class-updater.php [options]

Options:
	-d <path>  folder to scan (optional)
	-f         fixes files

<?php
}



class ClassUpdater extends Nette\Object
{
	public $readOnly = FALSE;

	/** @var array */
	private $uses;

	/** @var array */
	private $newUses;

	/** @var string */
	private $namespace;

	/** @var */
	private $fileName;

	/** @var array */
	private $replaces = array(
		// namespaces
		'nette\web' => 'Nette\Http',
		'nette\templates' => 'Nette\Templating',

		// php 5.2
		'arraytools' => 'Arrays',
		'narraytools' => 'NArrays',
		'string' => 'Strings',
		'nstring' => 'NStrings',
		'multirouter' => 'RouteList',
		'nmultirouter' => 'NRouteList',
		'dummystorage' => 'DevNullStorage',
		'ndummystorage' => 'NDevNullStorage',
		'debug' => 'Debugger',
		'ndebug' => 'NDebugger',
		'idebugpanel' => 'IBarPanel',
		'uri' => 'Url',
		'nuri' => 'NUrl',
		'urlscript' => 'UrlScript',
		'nurlscript' => 'NUrlScript',
		'downloadresponse' => 'FileResponse',
		'ndownloadresponse' => 'NFileResponse',
		'forwardingresponse' => 'ForwardResponse',
		'nforwardingresponse' => 'NForwardResponse',
		'redirectingresponse' => 'RedirectResponse',
		'nredirectingresponse' => 'NRedirectResponse',
		'renderresponse' => 'TextResponse',
		'nrenderresponse' => 'NTextResponse',
		'fileupload' => 'UploadControl',
		'nfileupload' => 'NUploadControl',

		// alpha / PHP 5.3
		'invalidstateexception' => 'Nette\InvalidStateException',
		'notimplementedexception' => 'Nette\NotImplementedException',
		'notsupportedexception' => 'Nette\NotSupportedException',
		'deprecatedexception' => 'Nette\DeprecatedException',
		'memberaccessexception' => 'Nette\MemberAccessException',
		'fatalerrorexception' => 'Nette\FatalErrorException',
		'filenotfoundexception' => 'Nette\FileNotFoundException',
		'directorynotfoundexception' => 'Nette\DirectoryNotFoundException',
		'ioexception' => 'Nette\IOException',
		'nette\regexpexception' => 'Nette\Utils\RegexpException',
		'nette\icomponent' => 'Nette\ComponentModel\IComponent',
		'nette\component' => 'Nette\ComponentModel\Component',
		'nette\icomponentcontainer' => 'Nette\ComponentModel\IContainer',
		'nette\componentcontainer' => 'Nette\ComponentModel\Container',
		'nette\ambiguousserviceexception' => 'Nette\DI\AmbiguousServiceException',
		'nette\icontext' => 'Nette\DI\IContext',
		'nette\context' => 'Nette\DI\Context',
		'nette\configurator' => 'Nette\DI\Configurator',
		'nette\debug' => 'Nette\Diagnostics\Debugger',
		'nette\idebugpanel' => 'Nette\Diagnostics\IBarPanel',
		'nette\debughelpers' => 'Nette\Diagnostics\Helpers',
		'nette\itranslator' => 'Nette\Localization\ITranslator',
		'nette\safestream' => 'Nette\Utils\SafeStream',
		'nette\finder' => 'Nette\Utils\Finder',
		'nette\arraytools' => 'Nette\Utils\Arrays',
		'nette\json' => 'Nette\Utils\Json',
		'nette\jsonexception' => 'Nette\Utils\JsonException',
		'nette\neon' => 'Nette\Utils\Neon',
		'nette\neonexception' => 'Nette\Utils\NeonException',
		'nette\paginator' => 'Nette\Utils\Paginator',
		'nette\string' => 'Nette\Utils\Strings',
		'nette\tokenizer' => 'Nette\Utils\Tokenizer',
		'nette\tokenizerexception' => 'Nette\Utils\TokenizerException',
		'nette\callbackfilteriterator' => 'Nette\Iterators\Filter',
		'nette\genericrecursiveiterator' => 'Nette\Iterators\Recursor',
		'nette\instancefilteriterator' => 'Nette\Iterators\InstanceFilter',
		'nette\mapiterator' => 'Nette\Iterators\Mapper',
		'nette\recursivecallbackfilteriterator' => 'Nette\Iterators\RecursiveFilter',
		'nette\smartcachingiterator' => 'Nette\Iterators\CachingIterator',
		'nette\application\downloadresponse' => 'Nette\Application\Responses\FileResponse',
		'nette\application\forwardingresponse' => 'Nette\Application\Responses\ForwardResponse',
		'nette\application\jsonresponse' => 'Nette\Application\Responses\JsonResponse',
		'nette\application\redirectingresponse' => 'Nette\Application\Responses\RedirectResponse',
		'nette\application\renderresponse' => 'Nette\Application\Responses\TextResponse',
		'nette\application\clirouter' => 'Nette\Application\Routers\CliRouter',
		'nette\application\multirouter' => 'Nette\Application\Routers\RouteList',
		'nette\application\route' => 'Nette\Application\Routers\Route',
		'nette\application\simplerouter' => 'Nette\Application\Routers\SimpleRouter',
		'nette\application\routingdebugger' => 'Nette\Application\Diagnostics\RoutingPanel',
		'nette\application\presenterrequest' => 'Nette\Application\Request',
		'nette\application\ipresenterresponse' => 'Nette\Application\IResponse',
		'nette\application\link' => 'Nette\Application\UI\Link',
		'nette\application\irenderable' => 'Nette\Application\UI\IRenderable',
		'nette\application\isignalreceiver' => 'Nette\Application\UI\ISignalReceiver',
		'nette\application\istatepersistent' => 'Nette\Application\UI\IStatePersistent',
		'nette\application\ipartiallyrenderable' => 'Nette\Application\UI\IPartiallyRenderable',
		'nette\application\presenter' => 'Nette\Application\UI\Presenter',
		'nette\application\presentercomponent' => 'Nette\Application\UI\PresenterComponent',
		'nette\application\presentercomponentreflection' => 'Nette\Application\UI\PresenterComponentReflection',
		'nette\application\control' => 'Nette\Application\UI\Control',
		'nette\application\appform' => 'Nette\Application\UI\Form',
		'nette\application\badsignalexception' => 'Nette\Application\UI\BadSignalException',
		'nette\application\invalidlinkexception' => 'Nette\Application\UI\InvalidLinkException',
		'nette\forms\button' => 'Nette\Forms\Controls\Button',
		'nette\forms\checkbox' => 'Nette\Forms\Controls\Checkbox',
		'nette\forms\fileupload' => 'Nette\Forms\Controls\UploadControl',
		'nette\forms\formcontrol' => 'Nette\Forms\Controls\BaseControl',
		'nette\forms\hiddenfield' => 'Nette\Forms\Controls\HiddenField',
		'nette\forms\imagebutton' => 'Nette\Forms\Controls\ImageButton',
		'nette\forms\multiselectbox' => 'Nette\Forms\Controls\MultiSelectBox',
		'nette\forms\radiolist' => 'Nette\Forms\Controls\RadioList',
		'nette\forms\selectbox' => 'Nette\Forms\Controls\SelectBox',
		'nette\forms\submitbutton' => 'Nette\Forms\Controls\SubmitButton',
		'nette\forms\textarea' => 'Nette\Forms\Controls\TextArea',
		'nette\forms\textbase' => 'Nette\Forms\Controls\TextBase',
		'nette\forms\textinput' => 'Nette\Forms\Controls\TextInput',
		'nette\forms\formgroup' => 'Nette\Forms\ControlGroup',
		'nette\forms\formcontainer' => 'Nette\Forms\Container',
		'nette\forms\iformcontrol' => 'Nette\Forms\IControl',
		'nette\forms\defaultformrenderer' => 'Nette\Forms\Rendering\DefaultFormRenderer',
		'nette\caching\icachestorage' => 'Nette\Caching\IStorage',
		'nette\caching\dummystorage' => 'Nette\Caching\Storages\DevNullStorage',
		'nette\caching\filejournal' => 'Nette\Caching\Storages\FileJournal',
		'nette\caching\filestorage' => 'Nette\Caching\Storages\FileStorage',
		'nette\caching\icachejournal' => 'Nette\Caching\Storages\IJournal',
		'nette\caching\memcachedstorage' => 'Nette\Caching\Storages\MemcachedStorage',
		'nette\caching\memorystorage' => 'Nette\Caching\Storages\MemoryStorage',
		'nette\config\configadapterini' => 'Nette\Config\IniAdapter',
		'nette\config\configadapterneon' => 'Nette\Config\NeonAdapter',
		'nette\config\iconfigadapter' => 'Nette\Config\IAdapter',
		'nette\database\databasepanel' => 'Nette\Database\Diagnostics\ConnectionPanel',
		'nette\database\selector\groupedtableselection' => 'Nette\Database\Table\GroupedSelection',
		'nette\database\selector\tablerow' => 'Nette\Database\Table\ActiveRow',
		'nette\database\selector\tableselection' => 'Nette\Database\Table\Selection',
		'nette\loaders\limitedscope' => 'Nette\Utils\LimitedScope',
		'nette\mail\mail' => 'Nette\Mail\Message',
		'nette\mail\mailmimepart' => 'Nette\Mail\MimePart',
		'nette\reflection\classreflection' => 'Nette\Reflection\ClassType',
		'nette\reflection\extensionreflection' => 'Nette\Reflection\Extension',
		'nette\reflection\functionreflection' => 'Nette\Reflection\GlobalFunction',
		'nette\reflection\methodreflection' => 'Nette\Reflection\Method',
		'nette\reflection\parameterreflection' => 'Nette\Reflection\Parameter',
		'nette\reflection\propertyreflection' => 'Nette\Reflection\Property',
		'nette\templates\lattefilter' => 'Nette\Latte\Engine',
		'nette\templates\lattemacros' => 'Nette\Latte\DefaultMacros',
		'nette\templates\latteexception' => 'Nette\Latte\ParseException',
		'nette\templates\cachinghelper' => 'Nette\Caching\OutputHelper',
		'nette\templates\templateexception' => 'Nette\Templating\FilterException',
		'nette\templates\templatecachestorage' => 'Nette\Templating\PhpFileStorage',
		'nette\templates\templatehelpers' => 'Nette\Templating\DefaultHelpers',
		'nette\templates\filetemplate' => 'Nette\Templating\FileTemplate',
		'nette\templates\ifiletemplate' => 'Nette\Templating\IFileTemplate',
		'nette\templates\itemplate' => 'Nette\Templating\ITemplate',
		'nette\templates\template' => 'Nette\Templating\Template',
		'nette\web\html' => 'Nette\Utils\Html',
		'nette\web\httpcontext' => 'Nette\Http\Context',
		'nette\web\ihttprequest' => 'Nette\Http\IRequest',
		'nette\web\httprequest' => 'Nette\Http\Request',
		'nette\web\ihttpresponse' => 'Nette\Http\IResponse',
		'nette\web\httpresponse' => 'Nette\Http\Response',
		'nette\web\httprequestfactory' => 'Nette\Http\RequestFactory',
		'nette\web\httpuploadedfile' => 'Nette\Http\FileUpload',
		'nette\web\isessionstorage' => 'Nette\Http\ISessionStorage',
		'nette\web\session' => 'Nette\Http\Session',
		'nette\web\sessionnamespace' => 'Nette\Http\SessionNamespace',
		'nette\web\uri' => 'Nette\Http\Url',
		'nette\web\uriscript' => 'Nette\Http\UrlScript',
		'nette\web\iuser' => 'Nette\Http\IUser',
		'nette\web\user' => 'Nette\Http\User',
	);



	public function run($folder)
	{
		set_time_limit(0);

		if ($this->readOnly) {
			echo "Running in read-only mode\n";
		}

		echo "Scanning folder $folder...\n";

		$counter = 0;
		foreach (Nette\Utils\Finder::findFiles('*.php')->from($folder)
			->exclude(array('.*', '*.tmp', 'tmp', 'temp', 'log')) as $file)
		{
			echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";

			$this->fileName = ltrim(str_replace($folder, '', $file), '/\\');

			$orig = file_get_contents($file);
			$new = $this->processFile($orig);
			if ($new !== $orig) {
				$this->report($this->readOnly ? 'FOUND' : 'FIX');
				if (!$this->readOnly) {
					file_put_contents($file, $new);
				}
			}
		}

		echo "\nDone.";
	}



	public function report($level, $message = '')
	{
		echo "[$level] $this->fileName   $message\n";
	}



	public function processFile($input)
	{
		$this->namespace = '';
		$this->uses = $this->newUses = array();
		$parser = new PhpParser($input);

		while (($token = $parser->fetch()) !== FALSE) {

			if ($parser->isCurrent(T_NAMESPACE)) {
				$this->namespace = (string) $parser->fetchAll(T_STRING, T_NS_SEPARATOR);

			} elseif ($parser->isCurrent(T_USE)) {
				if ($parser->isNext('(')) { // closure?
					continue;
				}
				do {
					$parser->fetchAll(T_WHITESPACE, T_COMMENT);

					$pos = $parser->position + 1;
					$class = $newClass = ltrim($parser->fetchAll(T_STRING, T_NS_SEPARATOR), '\\');
					if (isset($this->replaces[strtolower($class)])) {
						$parser->replace($newClass = $this->replaces[strtolower($class)], $pos);
					}

					if ($parser->fetch(T_AS)) {
						$as = $newAs = $parser->fetch(T_STRING);
					} else {
						$as = substr($class, strrpos("\\$class", '\\'));
						$newAs = substr($newClass, strrpos("\\$newClass", '\\'));
					}
					$this->uses[strtolower($as)] = $class;
					while (isset($this->newUses[strtolower($newAs)])) {
						$newAs .= '_';
						$parser->replace("$class as $newAs", $pos);
					}
					$this->newUses[strtolower($newAs)] = array($newClass, $newAs);

				} while ($parser->fetch(','));

			} elseif ($parser->isCurrent(T_INSTANCEOF, T_EXTENDS, T_IMPLEMENTS, T_NEW)) {
				do {
					$parser->fetchAll(T_WHITESPACE, T_COMMENT);
					$pos = $parser->position + 1;
					if ($class = $parser->fetchAll(T_STRING, T_NS_SEPARATOR)) {
						$parser->replace($this->renameClass($class), $pos);
					}
				} while ($class && $parser->fetch(','));

			} elseif ($parser->isCurrent(T_STRING, T_NS_SEPARATOR)) { // Class:: or typehint
				$pos = $parser->position;
				$identifier = $token . $parser->fetchAll(T_STRING, T_NS_SEPARATOR);
				if ($parser->isNext(T_DOUBLE_COLON, T_VARIABLE)) {
					$parser->replace($this->renameClass($identifier), $pos);
				}

			} elseif ($parser->isCurrent(T_DOC_COMMENT, T_COMMENT)) {
				// @var Class or \Class or Nm\Class or Class:: (preserves CLASS)
				$that = $this;
				$parser->replace(preg_replace_callback('#((?:@var(?:\s+array of)?|returns?|param|throws|@link|property[\w-]*)\s+)([\w\\\\|]+)#', function($m) use ($that) {
					$parts = array();
					foreach (explode('|', $m[2]) as $part) {
						$parts[] = preg_match('#^\\\\?[A-Z].*[a-z]#', $part) ? $that->renameClass($part) : $part;
					}
					return $m[1] . implode('|', $parts);
				}, $token));

			} elseif ($parser->isCurrent(T_CONSTANT_ENCAPSED_STRING)) {
				if (preg_match('#(^.\\\\?)(Nette(?:\\\\{1,2}[A-Z]\w+)*)(:.*|.$)#', $token, $m)) { // 'Nette\Object'
					$class = str_replace('\\\\', '\\', $m[2], $double);
					if (isset($that->replaces[strtolower($class)])) {
						$class = $that->replaces[strtolower($class)];
 						$parser->replace($m[1] . str_replace('\\', $double ? '\\\\' : '\\', $class) . $m[3]);
					}
				}
			}
		}

		$parser->position = 0;
		return $parser->fetchAll();
	}



	/**
	 * Renames class.
	 * @param  string class
	 * @return string new class
	 */
	function renameClass($class)
	{
		if ($class === 'parent' || $class === 'self' || !$class) {
			return $class;
		}
		$class = $this->resolveClass($class);
		if (isset($this->replaces[strtolower($class)])) {
			$class = $this->replaces[strtolower($class)];
		}
		return $this->applyUse($class);
	}



	/**
	 * Apply use statements.
	 * @param  string
	 * @return string
	 */
	function applyUse($class)
	{
		$best = strncasecmp($class, "$this->namespace\\", strlen("$this->namespace\\")) === 0
			? substr($class, strlen($this->namespace) + 1)
			: ($this->namespace ? '\\' : '') . $class;

		foreach ($this->newUses as $item) {
			list($use, $as) = $item;
			if (strncasecmp("$class\\", "$use\\", strlen("$use\\")) === 0) {
				$new = substr_replace($class, $as, 0, strlen($use));
				if (strlen($new) <= strlen($best)) {
					$best = $new;
				}
			}
		}

		return $best;
	}



	/**
	 * Resolve use statements.
	 * @param  string
	 * @return string|NULL
	 */
	function resolveClass($class)
	{
		$segment = strtolower(substr($class, 0, strpos("$class\\", '\\')));
		if ($segment === '') {
			$full = $class;
		} elseif (isset($this->uses[$segment])) {
			$full = $this->uses[$segment] . substr($class, strlen($segment));
		} else {
			$full = $this->namespace . '\\' . $class;
		}
		return ltrim($full, '\\');
	}

}



/**
 * Simple tokenizer for PHP.
 */
class PhpParser extends Nette\Utils\Tokenizer
{

	function __construct($code)
	{
		$this->ignored = array(T_COMMENT, T_DOC_COMMENT, T_WHITESPACE);
		foreach (token_get_all($code) as $token) {
			$this->tokens[] = is_array($token) ? self::createToken($token[1], $token[0]) : $token;
		}
	}



	function replace($s, $start = NULL)
	{
		for ($i = ($start === NULL ? $this->position : $start) - 1; $i < $this->position - 1; $i++) {
			$this->tokens[$i] = '';
		}
		$this->tokens[$this->position - 1] = $s;
	}

}



$updater = new ClassUpdater;
$updater->readOnly = !isset($options['f']);
$updater->run(isset($options['d']) ? $options['d'] : getcwd());
