<?php

function vid_player($url, $width, $height, $extension = false){

	$path = parse_url($url, PHP_URL_PATH);
	
	$html5 = false;
	
	if($path){
	
		$extension = $extension ? $extension : pathinfo($path, PATHINFO_EXTENSION);
		
		if($extension == 'mp4' || $extension == 'webm' || $extension == 'ogg'){
			$html5 = true;
		}
	}
	
	// this better be an absolute url and proxify_url function better already be included from somewhere
	$video_url = proxify_url($url);

	if($html5){
	
		$html = '<video width="100%" height="100%" controls autoplay>
			<source src="'.$video_url.'" type="video/'.$extension.'">
			Your browser does not support the video tag.
		</video>';
		
	} else {
	
		// encode before embedding it into player's parameters
		$video_url = rawurlencode($video_url);
	
		$html = '<object id="flowplayer" width="'.$width.'" height="'.$height.'" data="//releases.flowplayer.org/swf/flowplayer-3.2.18.swf" type="application/x-shockwave-flash">
 	 
       	<param name="allowfullscreen" value="true" />
		<param name="wmode" value="transparent" />
        <param name="flashvars" value=\'config={"clip":"'.$video_url.'", "plugins": {"controls": {"autoHide" : false} }}\' />
		
		</object>';
	}
	
	return $html;
}

function sig_js_decode($player_html){

    // what javascript function is responsible for signature decryption?
    // var l=f.sig||Xn(f.s)
    // a.set("signature",Xn(c));return a
    if(preg_match('/signature",([a-zA-Z0-9]+)\(/', $player_html, $matches)){

        $func_name = $matches[1];

        // extract code block from that function
        // xm=function(a){a=a.split("");wm.zO(a,47);wm.vY(a,1);wm.z9(a,68);wm.zO(a,21);wm.z9(a,34);wm.zO(a,16);wm.z9(a,41);return a.join("")};
        if(preg_match("/{$func_name}=function\([a-z]+\){(.*?)}/", $player_html, $matches)){

            $js_code = $matches[1];

            // extract all relevant statements within that block
            // wm.vY(a,1);
            if(preg_match_all('/([a-z0-9]{2})\.([a-z0-9]{2})\([^,]+,(\d+)\)/i', $js_code, $matches) != false){

                // must be identical
                $obj_list = $matches[1];

                //
                $func_list = $matches[2];

                // extract javascript code for each one of those statement functions
                preg_match_all('/('.implode('|', $func_list).'):function(.*?)\}/m', $player_html, $matches2,  PREG_SET_ORDER);

                $functions = array();

                // translate each function according to its use
                foreach($matches2 as $m){

                    if(strpos($m[2], 'splice') !== false){
                        $functions[$m[1]] = 'splice';
                    } else if(strpos($m[2], 'a.length') !== false){
                        $functions[$m[1]] = 'swap';
                    } else if(strpos($m[2], 'reverse') !== false){
                        $functions[$m[1]] = 'reverse';
                    }
                }

                // FINAL STEP! convert it all to instructions set
                $instructions = array();

                foreach($matches[2] as $index => $name){
                    $instructions[] = array($functions[$name], $matches[3][$index]);
                }

                return $instructions;
            }
        }
    }

    return false;
}

?>