<?php

namespace Mirror\Plugin;

use Mirror\Plugin\AbstractPlugin;
use Mirror\Event\MirrorEvent;

use Mirror\Html;

class TwitterPlugin extends AbstractPlugin {

	protected $url_pattern = 'twitter.com';

	public function onCompleted(MirrorEvent $event){
	
		// there is some problem with content-length when submitting form...
		$response = $event['response'];
		$content = $response->getContent();
		
		// remove all javascript
		$content = Html::remove_scripts($content);
			
		$response->setContent($content);
	}
}

?>