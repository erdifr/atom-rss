<?php
class pluginAtomRSS extends Plugin {

	// Begin - Initialize Database
	public function init()
	{
		$this->dbFields = array(
			'feedCopyright' => 'DISABLED',
			'feedFileAtom' => 'feed.atom',
			'feedFileRSS' => 'feed.rss',
			'feedGenerator' => 'Bludit - Flat-File CMS',
			'feedGeneratorEnable' => true,
			'feedItemLimit' => 10,
			'feedTTL' => 60
		);
	} // Cease - Initialize Database


	// Begin - Plugin Settings Page
	public function form()
	{
		// Language
		global $L;

		$feedCopyright = $this->getValue('feedCopyright');
		$feedFileAtom = $this->getValue('feedFileAtom');
		$feedFileRSS = $this->getValue('feedFileRSS');
		$feedGeneratorEnable = $this->getValue('feedGeneratorEnable');
		$feedItemLimit = $this->getValue('feedItemLimit');
		$feedTTL = $this->getValue('feedTTL');

		// Begin - HTML Template
		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

		// Feed URL Atom
		$html .= '<div>';
		$html .= '<label>'.$L->get('feed-url-atom').'</label>';
		$html .= '<a href="'.DOMAIN_BASE.$feedFileAtom.'">'.DOMAIN_BASE.$feedFileAtom.'</a>';
		$html .= '<span class="tip">'.$L->get('feed-url-atom-tip').'</span>';
		$html .= '</div>';

		// Feed URL RSS
		$html .= '<div>';
		$html .= '<label>'.$L->get('feed-url-rss').'</label>';
		$html .= '<a href="'.DOMAIN_BASE.$feedFileRSS.'">'.DOMAIN_BASE.$feedFileRSS.'</a>';
		$html .= '<span class="tip">'.$L->get('feed-url-rss-tip').'</span>';
		$html .= '</div>';

		// Feed Item Limit
		$html .= '<div>';
		$html .= '<label>'.$L->get('feed-item-limit').'</label>';

		// Reset to default if 0 is selected
		if ($feedItemLimit === 0) {
			$feedItemLimit = 10;
		}

		$html .= '<input id="jsfeedItemLimit" name="feedItemLimit" type="number" title="Valid input range: -1 - 100" min="-1" max="100" value="'.$feedItemLimit.'">';
		$html .= '<span class="tip">'.$L->get('feed-item-limit-tip').'</span>';
		$html .= '</div>';

		// Feed Copyright
		$html .= '<div>';
		$html .= '<label>'.$L->get('feed-copyright').'</label>';
		$html .= '<select id="jsfeedCopyright" name="feedCopyright">';

		if ($feedCopyright !== 'DISABLED') {
			$html .= '<option value="'.$feedCopyright.'">'.$feedCopyright.'</option>';
		} else {
			$html .= '<option value="DISABLED">Disabled</option>';
		}

		$html .= '<option value="CC BY 4.0">CC BY 4.0</option>';
		$html .= '<option value="CC BY-SA 4.0">CC BY-SA 4.0</option>';
		$html .= '<option value="CC BY-ND 4.0">CC BY-ND 4.0</option>';
		$html .= '<option value="CC BY-NC 4.0">CC BY-NC 4.0</option>';
		$html .= '<option value="CC BY-NC-SA 4.0">CC BY-NC-SA 4.0</option>';
		$html .= '<option value="CC BY-NC-ND 4.0">CC BY-NC-ND 4.0</option>';
		$html .= '<option value="DISABLED">Disabled</option>';
		$html .= '</select>';
		$html .= '<span class="tip">'.$L->get('feed-copyright-tip').' '.$L->get('Disabled').'</span>';
		$html .= '</div>';

		// Feed Generator
		$html .= '<div>';
		$html .= '<label>'.$L->get('feed-generator-tag').'</label>';
		$html .= '<select name="feedGeneratorEnable">';
		$html .= '<option value="true" '.($feedGeneratorEnable===true?'selected':'').'>'.$L->get('Enabled').'</option>';
		$html .= '<option value="false" '.($feedGeneratorEnable===false?'selected':'').'>'.$L->get('Disabled').'</option>';
		$html .= '</select>';
		$html .= '<span class="tip">'.$L->get('feed-generator-tag-tip').' '.$L->get('Enabled').'</span>';
		$html .= '</div>';
		// Cease - HTML Template

		// Output Template
		return $html;
	} // Cease - Plugin Settings Page


	// Begin - Atom
	private function createAtom()
	{
		global $site;
		global $pages;
		global $url;

		$feedCopyright = $this->getValue('feedCopyright');
		$feedDomain = parse_url(DOMAIN);
		$feedFileAtom = $this->getValue('feedFileAtom');
		$feedGenerator = $this->getValue('feedGenerator');
		$feedGeneratorEnable = $this->getValue('feedGeneratorEnable');
		$feedItemLimit = $this->getValue('feedItemLimit');
		$feedSubtitle = $site->slogan();
		$feedUUID = 'tag:'.$feedDomain['host'].',2019-03-31:1640';

		// Reset to default if 0 is selected
		if ($feedItemLimit === 0) {
			$feedItemLimit = 10;
		}

		$list = $pages->getList(
			$pageNumber = 1,
			$feedItemLimit,
			$published = true,
			$static = true,
			$sticky = true,
			$draft = false,
			$scheduled = false
		);

		// Begin - Atom Template
		$atom = '<?xml version="1.0" encoding="utf-8" ?>';
		$atom .= '<feed xmlns="http://www.w3.org/2005/Atom">';
		$atom .= '<title>'.$site->title().'</title>';

		if (!empty($feedSubtitle)) {
			$atom .= '<subtitle>'.$site->slogan().'</subtitle>';
		}

		$atom .= '<id>'.$feedUUID.'</id>';
		$atom .= '<updated>'.date(DATE_ATOM).'</updated>';
		$atom .= '<link href="'.DOMAIN_BASE.$feedFileAtom.'" rel="self" type="application/atom+xml" />';
		$atom .= '<link href="'.$site->domain().'" hreflang="'.Theme::lang().'" rel="alternate" type="text/html" />';

		if ($feedCopyright !== 'DISABLED') {
			$atom .= '<rights>'.$feedCopyright.'</rights>';
		}

		if ($feedGeneratorEnable === true) {
			$atom .= '<generator>'.$feedGenerator.'</generator>';
		}

		foreach ($list as $pageKey) {
			try {
				// Create the page object from the page key
				$page = new Page($pageKey);
				$atom .= '<entry>';
				$atom .= '<title>'.$page->title().'</title>';
				$atom .= '<link href="'.$page->permalink().'" hreflang="'.Theme::lang().'" rel="alternate" />';
				$atom .= '<id>'.$feedUUID.'.'.$page->uuid().'</id>';
				$atom .= '<published>'.$page->date(DATE_ATOM).'</published>';

				// Better way to do this?
				if (!empty($page->dateModified())) {
					$dm = $page->dateModified();
					// Convert $dm to DATE_ATOM
					$dmatom = Date::format($dm, DB_DATE_FORMAT, DATE_ATOM);
					// Display DATE_ATOM
					$atom .= '<updated>'.$dmatom.'</updated>';
				} else {
					$atom .= '<updated>'.$page->date(DATE_ATOM).'</updated>';
				}

				// Add category
				if (!empty($page->category())) {
					$atom .= '<category scheme="'.DOMAIN_BASE.'" term="'.$page->category().'" />';
				}

				$atom .= '<author><name>'.$page->username().'</name></author>';
				$atom .= '<summary type="html">'.Sanitize::html($page->contentBreak()).'</summary>';
				$atom .= '</entry>';
			} catch (Exception $e) {
				// Continue
			}
		}

		$atom .= '</feed>';
		// Cease - Atom Template

		// Create Atom File
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->loadXML($atom);

		// Save Atom File
		return $doc->save($this->workspace().$feedFileAtom);
	} // Cease - Atom


	// Begin - RSS
	private function createRSS()
	{
		global $site;
		global $pages;
		global $url;

		$feedCopyright = $this->getValue('feedCopyright');
		$feedFileRSS = $this->getValue('feedFileRSS');
		$feedGenerator = $this->getValue('feedGenerator');
		$feedGeneratorEnable = $this->getValue('feedGeneratorEnable');
		$feedItemLimit = $this->getValue('feedItemLimit');
		$feedTTL = $this->getValue('feedTTL');

		// Reset to default if 0 is selected
		if ($feedItemLimit === 0) {
			$feedItemLimit = 10;
		}

		$list = $pages->getList(
			$pageNumber = 1,
			$feedItemLimit,
			$published = true,
			$static = true,
			$sticky = true,
			$draft = false,
			$scheduled = false
		);

		// Begin - RSS Template
		$rss = '<?xml version="1.0" encoding="UTF-8" ?>';
		$rss .= '<rss version="2.0">';
		$rss .= '<channel>';
		$rss .= '<title>'.$site->title().'</title>';
		$rss .= '<link>'.$site->url().'</link>';
		$rss .= '<description>'.$site->description().'</description>';
		$rss .= '<language>'.Theme::lang().'</language>';

		if ($feedCopyright !== 'DISABLED') {
			$rss .= '<copyright>'.$feedCopyright.'</copyright>';
		}

		$rss .= '<lastBuildDate>'.date(DATE_RSS).'</lastBuildDate>';

		if ($feedGeneratorEnable === true) {
			$rss .= '<generator>'.$feedGenerator.'</generator>';
		}

		if (!empty($feedTTL) && ($feedTTL !== 0)) {
			$rss .= '<ttl>'.$feedTTL.'</ttl>';
		}

		foreach ($list as $pageKey) {
			try {
				$page = new Page($pageKey);
				$rss .= '<item>';
				$rss .= '<title>'.$page->title().'</title>';
				$rss .= '<link>'.$page->permalink().'</link>';
				$rss .= '<description>'.Sanitize::html($page->contentBreak()).'</description>';

				if (!empty($page->category())) {
					$rss .= '<category>'.$page->category().'</category>';
				}

				$rss .= '<pubDate>'.$page->date(DATE_RSS).'</pubDate>';
				$rss .= '<guid isPermaLink="false">'.$page->uuid().'</guid>';
				$rss .= '</item>';
			} catch (Exception $e) {
				// Continue
			}
		}

		$rss .= '</channel>';
		$rss .= '</rss>';
		// Cease - RSS Template

		// Create RSS File
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$doc->loadXML($rss);

		// Save RSS File
		return $doc->save($this->workspace().$feedFileRSS);
	} // Cease - RSS


	public function install($position = 0)
	{
		parent::install($position);
		$this->createAtom();
		$this->createRSS();
		return;
	}


	public function post()
	{
		parent::post();
		$this->createAtom();
		$this->createRSS();
		return;
	}


	public function afterPageCreate()
	{
		$this->createAtom();
		$this->createRSS();
	}


	public function afterPageModify()
	{
		$this->createAtom();
		$this->createRSS();
	}


	public function afterPageDelete()
	{
		$this->createAtom();
		$this->createRSS();
	}


	public function siteHead()
	{
		global $site;

		$feedFileAtom = $this->getValue('feedFileAtom');
		$feedFileRSS = $this->getValue('feedFileRSS');

		// Add Atom Link
		$addLink = '<link rel="alternate" type="application/atom+xml" href="'.DOMAIN_BASE.$feedFileAtom.'" title="'.$site->title().' - Atom Feed">'.PHP_EOL;
		// Add RSS Link 
		$addLink .= '<link rel="alternate" type="application/rss+xml" href="'.DOMAIN_BASE.$feedFileRSS.'" title="'.$site->title().' - RSS Feed">'.PHP_EOL;

		return $addLink;
	}


	public function beforeAll()
	{
		$feedFileAtom = $this->getValue('feedFileAtom');
		$feedFileRSS = $this->getValue('feedFileRSS');

		// Atom - http://example.com/feed.atom
		if ($this->webhook($feedFileAtom)) {
			// Send XML header
			header('Content-type: application/atom+xml');
			$doc = new DOMDocument();

			// Load XML
			libxml_disable_entity_loader(false);
			$doc->load($this->workspace().$feedFileAtom);
			libxml_disable_entity_loader(true);

			// Print the XML
			echo $doc->saveXML();

			// Stop Bludit execution
			exit(0);
		}


		// RSS - http://example.com/feed.rss
		if ($this->webhook($feedFileRSS)) {
			// Send XML header
			header('Content-type: text/xml');
			$doc = new DOMDocument();

			// Load XML
			libxml_disable_entity_loader(false);
			$doc->load($this->workspace().$feedFileRSS);
			libxml_disable_entity_loader(true);

			// Print the XML
			echo $doc->saveXML();

			// Stop Bludit execution
			exit(0);
		}
	}


}

