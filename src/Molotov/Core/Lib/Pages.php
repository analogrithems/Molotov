<?php
namespace Molotov\Core\Lib;

use Phalcon\Mvc\View\Simple as View;

class Pages extends View{
	public $css = array();
	public $js = array();
	public $menu = array();
	public $page, $di, $app, $view, $layoutDir, $viewDir, $viewContent, $before_content;
	private $widget;
	protected static $settings;
	
	public function __construct( $app = null){
		parent::__construct();
		$this->di = \Phalcon\DI::getDefault();
		$this->setDI($this->di);
		$this->registerEngines(array(
			'.view' => 'Phalcon\Mvc\View\Engine\Volt'
		));
		$this->app = $app;
		$this->setLayoutDir(APP_ROOT_DIR.$this->di->get('config')['default_layout']);
		$this->setViewDir($this->di->get('config')['cache']);
	}
	
	
	/**
	 *
	 * addPage - creates a new page and defines js and css files as well as template and callbacks
	 * $page = array(
	 * 		'alias'=>'dashboard',
	 *		'path'=>'/uri'
	 *		'js'=>array('js/jquery','src/Molotov/Modules/Auth/Views/Web/Auth/js/main.js'),
	 *		'css'=>array('boostrap.css'),
	 *		'viewDir'=> 'module_dir_path'
	 *		'layout'=>'path_to_payout'
	 * )
	 */
	public function addPage($page = null){
		if(is_null($page)){
			$this->di->get('log')->critical('Missing Page Alias for setPage');
			throw new \Exception('Missing Page Alias for setPage');
		}else{
			
			$this->app->map(
				$page['path'], 
				function() use($page){
					$di = \Phalcon\DI::getDefault();
					if(isset($page['alias']) && !empty($page['alias'])){
						$this->view = $page['alias'];
					}
					
					//run callback send it this
					if(isset($page['callback'])){
						$page['callback']($this);
					}

					//run before_content_callback send it this
					if(isset($page['before_content'])){
						$this->before_content = $page['before_content']($this);
					}
										
					if(isset($page['viewDir'])){
						$this->setViewDir($page['viewDir']);
					}
					
					$this->viewContent = parent::render($page['alias']);
										
					if( property_exists($this,'layoutDir')){
						$layout = $this->getLayoutDir().'/index.php';
						if(file_exists($layout)){
							ob_start();
							include_once($layout);
							$this->viewContent = ob_get_contents();
							ob_end_clean();
						}else{
							throw new \Exception("Layout not valid:".print_r($layout,1));
						}
					}
					echo $this->resolveScripts($this->viewContent, $page);
				}
			)->via(
				array(
					'GET',
					'POST'
				)
			);
		}
	}
	
	public function getContent(){
		return $this->viewContent;
	}
	
	public function setViewDir( $path ){
		$this->setViewsDir($path);
		$this->viewDir = $path;
	}
	
	public function getViewDir(){
		return $this->viewDir;
	}	
	
	public function setLayoutDir( $path ){
		$this->layoutDir = $path;
	}
	
	public function getLayoutDir(){
		return $this->layoutDir;
	}
	
	/**
	 * addWidget - a widget is a reusable piece of code
	 * $widget = array(
	 *		'alias'=>'unique_widget_name',
	 * 		'js'=>array(),
	 *		'css'=>array(),
	 *		'callback'=>function(){},// or array(MyWidget,'login')
	 * )
	 *
	 */
	public function addWidget( $widget = null ){
		if( !isset($widget['alias']) ){
			throw new \Exception("Missing Widget Alias");
		}else{
			$this->widget[$widget['alias']] = $widget;
		}
	}
	
	/**
	 * getWidget - Fetch a pre-registered widget for inclusion in a page
	 *
	 */
	public function getWidget( $alias ){
		if( !isset($alias) ){
			throw new \Exception("Missing Widget Alias");
		}else{
			if( isset($this->widget[$alias]) ){
				
				if(isset($this->widget[$alias]['viewDir'])){
					$this->setViewDir($this->widget[$alias]['viewDir']);
				}
				
				$content = parent::render($alias);
				return $content;
			}else{
				throw new \Exception("Missing Widget {$alias}");
			}
		}
	}
	
	/**
	 * skinclude - is a fancy implementation of include_once. But it checks for files
	 * in multiple locations to allow for overwritting default layouts to skin or theme a page
	 */
	public static function skinclude( $pageName ){
		//check theme directory
		
		//check 
	}
	
	public static function addToMenu( $page, $link ){
		if(is_null($page)){
			throw new \Exception('Missing Page Name in addToMenu');
		}else{
			if(isset($this->$settings[$page])){
				$this->$settings[$page]['links'] = $link;	
			}
		}
	}
	
	public function resolveScripts(&$source, $page){

		$config = $this->di->get('config');
		$jsFiles = $this->getDependencies( $this->getjs( $source) );
		$cssFiles = $this->getCSS( $source);
		
		$assetManager = $this->di->get('assets');
		$cssCollection = $assetManager->collection('cssHeader');
		$jsCollection = $assetManager->collection('jsHeader');
		//die("JS:".print_r($js,1)."\nCSS:".print_r($css,1));
		foreach($jsFiles as $_js){
			$jsCollection->addJs($_js);
		}
		
		foreach($cssFiles as $_css){
			$cssCollection->addCss($_css);
		}
		
		if(!$this->di->get('config')['debug']){
			//minify and combine js
			$jsFile = '/js/'.$page['alias'].'_combined.js';
			$jsCollection->setTargetPath($this->di->get('config')['cache'].$jsFile)
				->setTargetUri('web/cache'.$jsFile)
				->join(true)
				->addFilter(new \Phalcon\Assets\Filters\Jsmin());

			//minify and combine css
			$cssFile = '/css/'.$page['alias'].'_combined.css';
			$cssCollection->setTargetPath($this->di->get('config')['cache'].$cssFile)
				->setTargetUri('web/cache'.$cssFile)
				->join(true)
				->addFilter(new \Phalcon\Assets\Filters\CSSMin());
			
		}
		$css_html = $js_html = '';
		
		foreach ($assetManager->collection('jsHeader') as $resource) {
			$js_html .= \Phalcon\Tag::javascriptInclude($resource->getPath());
		}
		
		foreach ($assetManager->collection('cssHeader') as $resource) {
			$css_html .= \Phalcon\Tag::stylesheetLink($resource->getPath());
		}		
		//cleanup and remaining include defs
		$source = preg_replace("/{CSS+.*}/", '', $source);
		$source = preg_replace("/{JS+.*}/", '', $source);
		$source = str_replace('{SCRIPTS}', $js_html, $source);
		$source = str_replace('{STYLES}', $css_html, $source);
		
		return $source;
	}
	
	private function getJs( $source ){
		$list = array();
		preg_match_all("/{JS+.*}/", $source, $matches);
		foreach($matches[0] as $match) {
			$source = str_replace($match, '', $source);
			$match = str_replace('{JS', '', $match);
			$match = str_replace('}', '', $match);
			$match = str_replace(' ', '', $match);
			$scripts = explode(',', $match);
			foreach($scripts as $s){
				if( !in_array($s, $list)){
					$list[] = $s;
				}
			}
		}
		return $list;
	}
	
	private function getCSS( $source ){
		$cssList = array();
		preg_match_all("/{CSS+.*}/", $source, $matches);
		foreach($matches[0] as $match) {
			$source = str_replace($match, '', $source);
			$match = str_replace('{CSS', '', $match);
			$match = str_replace('}', '', $match);
			$match = str_replace(' ', '', $match);
			$scripts = explode(',', $match);
			foreach($scripts as $s){
				if( !in_array($s, $cssList)){
					$cssList[] = $s;
				}
			}
		}
		return $cssList;
	}
	
	public function getDependencies($scripts){
		if(!is_array($scripts))
			$scripts = array($scripts);

		$dependencies = array();
		foreach($scripts as $script){
			$this->resolve($script,$dependencies);
		}
		$list = $this->flatten($dependencies);
		return $list;
	}
	
	private function resolve($model,&$out,$level = 0){
		if(array_key_exists($model, $out)){
			self::moveUp($model,$out,$level);
			return;
		}

		$f = @fopen(APP_ROOT_DIR.$model,"rb");
		if(!$f){
			throw new \Exception("js module not found: '" . APP_ROOT_DIR.$model . "'");
		}
		$l = fgets($f);
		$l = trim($l,"\\\/\t \n\r\*");
		$l = json_decode($l,1);

		$out[$model] = array('requires' => array(), 'level' => $level);
		if($l && array_key_exists('requires', $l))
			$out[$model]['requires'] = $l['requires'];

		foreach($out[$model]['requires'] as $r){
			self::resolve($r,$out,$level+1);
		}
	}

	private function moveUp($model,&$out,$level){
		if($out[$model]['level'] > $level) return;
		$out[$model]['level'] = $level;
		foreach($out[$model]['requires'] as $l){
			self::moveUp($l,$out,$level+1);
		}
	}	

	private function flatten($deps){
		$levels = array();
		$out = array();

		foreach($deps as $k => $d){
			$no = $d['level'];
			if(!array_key_exists($no, $levels))
				$levels[$no] = array();
			$levels[$no][] = $k;
		}

		ksort($levels);

		foreach($levels as $l){
			foreach($l as $o){
				array_unshift($out, $o);
			}
		}

		return $out;
	}
}