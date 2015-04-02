<?php
namespace Molotov\Core\Lib;

/*
 * This class is used all through out the system to sanatize user input and make sure only acceptable content is ever used
 * using the default wordpress list as a start we make our list using this little snip
 * get the list here https://core.trac.wordpress.org/browser/tags/4.1.1/src/wp-includes/kses.php#L60
 *
 *	$master = array("'*[style|title|rel|alt]'");
 *	foreach( $allowedposttags as $sk=>$v){
 *		$attr = '';
 *		if( !empty($v) ){
 *			$attr = '['.join('|',array_keys($v)).']';
 *		}
 *		$master[] = "'".$sk.$attr."'";
 *	}
 *
 *	echo join(",\n",$master)."\n";
 *
 */
class HTMLSanitize
{
	public $htmlPurifier=false;
	public $config;

	public function buildConfig( $callback = null ){
		$this->config = \HTMLPurifier_Config::createDefault();
		
		/*
		 * Define html rules here
		 * Config rules @ http://htmlpurifier.org/live/configdoc/plain.html#HTML.AllowedAttributes
		 *
		 * Based of the wp kses default rules + style attr
		 * @link https://core.trac.wordpress.org/browser/tags/4.1.1/src/wp-includes/kses.php#L60
		 */
        $this->config->set('Core.Encoding', 'UTF-8');
        
        //according to docs, this is how we define our html sanatize rules
        $allowed = array(
        	'*[style|title|rel|alt]',
			'address',
			'a[href|rel|rev|name|target]',
			'abbr',
			'acronym',
			'area[alt|coords|href|nohref|shape|target]',
			'article[align|dir|lang|xml:lang]',
			'aside[align|dir|lang|xml:lang]',
			'audio[autoplay|controls|loop|muted|preload|src]',
			'b',
			'big',
			'blockquote[cite|lang|xml:lang]',
			'br',
			'button[disabled|name|type|value]',
			'caption[align]',
			'cite[dir|lang]',
			'code',
			'col[align|char|charoff|span|dir|valign|width]',
			'colgroup[align|char|charoff|span|valign|width]',
			'del[datetime]',
			'dd',
			'dfn',
			'details[align|dir|lang|open|xml:lang]',
			'div[align|dir|lang|xml:lang]',
			'dl',
			'dt',
			'em',
			'fieldset',
			'figure[align|dir|lang|xml:lang]',
			'figcaption[align|dir|lang|xml:lang]',
			'font[color|face|size]',
			'footer[align|dir|lang|xml:lang]',
			'form[action|accept|accept-charset|enctype|method|name|target]',
			'h1[align]',
			'h2[align]',
			'h3[align]',
			'h4[align]',
			'h5[align]',
			'h6[align]',
			'header[align|dir|lang|xml:lang]',
			'hgroup[align|dir|lang|xml:lang]',
			'hr[align|noshade|size|width]',
			'i',
			'img[alt|align|border|height|hspace|longdesc|vspace|src|usemap|width]',
			'ins[datetime|cite]',
			'kbd',
			'label[for]',
			'legend[align]',
			'li[align|value]',
			'map[name]',
			'mark',
			'menu[type]',
			'nav[align|dir|lang|xml:lang]',
			'p[align|dir|lang|xml:lang]',
			'pre[width]',
			'q[cite]',
			's',
			'samp',
			'span[dir|align|lang|xml:lang]',
			'section[align|dir|lang|xml:lang]',
			'small',
			'strike',
			'strong',
			'sub',
			'summary[align|dir|lang|xml:lang]',
			'sup',
			'table[align|bgcolor|border|cellpadding|cellspacing|dir|rules|summary|width]',
			'tbody[align|char|charoff|valign]',
			'td[abbr|align|axis|bgcolor|char|charoff|colspan|dir|headers|height|nowrap|rowspan|scope|valign|width]',
			'textarea[cols|rows|disabled|name|readonly]',
			'tfoot[align|char|charoff|valign]',
			'th[abbr|align|axis|bgcolor|char|charoff|colspan|headers|height|nowrap|rowspan|scope|valign|width]',
			'thead[align|char|charoff|valign]',
			'title',
			'tr[align|bgcolor|char|charoff|valign]',
			'track[default|kind|label|src|srclang]',
			'tt',
			'u',
			'ul[type]',
			'ol[start|type]',
			'var',
			'video[autoplay|controls|height|loop|muted|poster|preload|src|width]'
		);
		$this->config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$this->config->set('CSS.AllowTricky', true);
		$this->config->set('Cache.SerializerPath', '/tmp');
		
		// Allow iframes from:
		// o YouTube.com
		// o Vimeo.com
		$this->config->set('HTML.SafeIframe', true);
		$this->config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
		
		$this->config->set('HTML.Allowed', implode(',', $allowed));
		
		// Set some HTML5 properties
		$this->config->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
		$this->config->set('HTML.DefinitionRev', 1);
		
		if ($def = $this->config->maybeGetRawHTMLDefinition()) {
			// http://developers.whatwg.org/sections.html
			$def->addElement('section', 'Block', 'Flow', 'Common');
			$def->addElement('nav',     'Block', 'Flow', 'Common');
			$def->addElement('article', 'Block', 'Flow', 'Common');
			$def->addElement('aside',   'Block', 'Flow', 'Common');
			$def->addElement('header',  'Block', 'Flow', 'Common');
			$def->addElement('footer',  'Block', 'Flow', 'Common');
			$def->addElement('summary',  'Block', 'Flow', 'Common');
			$def->addElement('textarea',  'Block', 'Flow', 'Common');
			
			// Content model actually excludes several tags, not modelled here
			$def->addElement('address', 'Block', 'Flow', 'Common');
			$def->addElement('button', 'Block', 'Flow', 'Common');
			$def->addElement('fieldset', 'Block', 'Flow', 'Common');
			$def->addElement('details', 'Block', 'Flow', 'Common');
			$def->addElement('map', 'Block', 'Flow', 'Common');
			$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
			
			// http://developers.whatwg.org/grouping-content.html
			$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
			
			// http://developers.whatwg.org/the-video-element.html#the-video-element
			$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
			));
			$def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
			));

			$def->addElement('track', 'Block', 'Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'kind' => 'Text',
				'label' => 'Text',
				'srclang' => 'Text'
			));
						
			$def->addElement('source', 'Block', 'Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
			));
			
			// http://developers.whatwg.org/text-level-semantics.html
			$def->addElement('s',    'Inline', 'Inline', 'Common');
			$def->addElement('var',  'Inline', 'Inline', 'Common');
			$def->addElement('sub',  'Inline', 'Inline', 'Common');
			$def->addElement('sup',  'Inline', 'Inline', 'Common');
			$def->addElement('mark', 'Inline', 'Inline', 'Common');
			$def->addElement('legend', 'Inline', 'Inline', 'Common');
			$def->addElement('label', 'Inline', 'Inline', 'Common');
			$def->addElement('wbr',  'Inline', 'Empty', 'Core');
			
			// http://developers.whatwg.org/edits.html
			$def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			$def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			
			// TinyMCE
			$def->addAttribute('img', 'data-mce-src', 'Text');
			$def->addAttribute('img', 'data-mce-json', 'Text');
			
			// Others
			$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
			$def->addAttribute('table', 'height', 'Text');
			$def->addAttribute('td', 'border', 'Text');
			$def->addAttribute('th', 'border', 'Text');
			$def->addAttribute('tr', 'width', 'Text');
			$def->addAttribute('tr', 'height', 'Text');
			$def->addAttribute('tr', 'border', 'Text');
		}
        
        //allow others to override the config
        if(is_callable($callback)){
	        call_user_func($callback,$this->config);
        }
        
        //build instance
		$this->htmlPurifier = new \HTMLPurifier($this->config);
	}
	
	public function sanitize( $input ){
		if(false === $this->htmlPurifier){
			$this->buildConfig();
		}
		return $this->htmlPurifier->purify($input);
	}
	
	public function load_htmlpurifier($allowed) {
		$config = HTMLPurifier_Config::createDefault();
		$this->config->set('HTML.Doctype', 'HTML 4.01 Transitional');
		$this->config->set('CSS.AllowTricky', true);
		$this->config->set('Cache.SerializerPath', '/tmp');
		
		// Allow iframes from:
		// o YouTube.com
		// o Vimeo.com
		$this->config->set('HTML.SafeIframe', true);
		$this->config->set('URI.SafeIframeRegexp', '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%');
		
		$this->config->set('HTML.Allowed', implode(',', $allowed));
		
		// Set some HTML5 properties
		$this->config->set('HTML.DefinitionID', 'html5-definitions'); // unqiue id
		$this->config->set('HTML.DefinitionRev', 1);
		
		if ($def = $this->config->maybeGetRawHTMLDefinition()) {
			// http://developers.whatwg.org/sections.html
			$def->addElement('section', 'Block', 'Flow', 'Common');
			$def->addElement('nav',     'Block', 'Flow', 'Common');
			$def->addElement('article', 'Block', 'Flow', 'Common');
			$def->addElement('aside',   'Block', 'Flow', 'Common');
			$def->addElement('header',  'Block', 'Flow', 'Common');
			$def->addElement('footer',  'Block', 'Flow', 'Common');
			
			// Content model actually excludes several tags, not modelled here
			$def->addElement('address', 'Block', 'Flow', 'Common');
			$def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');
			
			// http://developers.whatwg.org/grouping-content.html
			$def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
			$def->addElement('figcaption', 'Inline', 'Flow', 'Common');
			
			// http://developers.whatwg.org/the-video-element.html#the-video-element
			$def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
				'width' => 'Length',
				'height' => 'Length',
				'poster' => 'URI',
				'preload' => 'Enum#auto,metadata,none',
				'controls' => 'Bool',
			));
			$def->addElement('source', 'Block', 'Flow', 'Common', array(
				'src' => 'URI',
				'type' => 'Text',
			));
			
			// http://developers.whatwg.org/text-level-semantics.html
			$def->addElement('s',    'Inline', 'Inline', 'Common');
			$def->addElement('var',  'Inline', 'Inline', 'Common');
			$def->addElement('sub',  'Inline', 'Inline', 'Common');
			$def->addElement('sup',  'Inline', 'Inline', 'Common');
			$def->addElement('mark', 'Inline', 'Inline', 'Common');
			$def->addElement('wbr',  'Inline', 'Empty', 'Core');
			
			// http://developers.whatwg.org/edits.html
			$def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			$def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
			
			// TinyMCE
			$def->addAttribute('img', 'data-mce-src', 'Text');
			$def->addAttribute('img', 'data-mce-json', 'Text');
			
			// Others
			$def->addAttribute('iframe', 'allowfullscreen', 'Bool');
			$def->addAttribute('table', 'height', 'Text');
			$def->addAttribute('td', 'border', 'Text');
			$def->addAttribute('th', 'border', 'Text');
			$def->addAttribute('tr', 'width', 'Text');
			$def->addAttribute('tr', 'height', 'Text');
			$def->addAttribute('tr', 'border', 'Text');
		}
		
	return new HTMLPurifier($config);
	}
	
}