<?php

namespace Mirror\Plugin;

use Mirror\Plugin\AbstractPlugin;
use Mirror\Event\MirrorEvent;
use Mirror\Html;

class XVideosPlugin extends AbstractPlugin {

	protected $url_pattern = 'xvideos.com';
	
	public function onCompleted(MirrorEvent $event){
	
		$response = $event['response'];
		$html = $response->getContent();
		
		if(preg_match('@setVideoUrlHigh\(\'([^\']+)@', $html, $matches)){
			
			$video_url = rawurldecode($matches[1]);
			$player = vid_player($video_url, 938, 476, 'mp4');
			
			// insert our own video player
			$html = Html::replace_inner("#video-player-bg", $player, $html);
		}
		
		// remove useless scripts
		//$html = Html::remove_scripts($html);
		
		$response->setContent($html);
	}
}

?>