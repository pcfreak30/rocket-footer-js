<?php


namespace Rocket\Footer\JS\Rewrite;


class UseProof extends RewriteAbstract {
	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		$tag = $this->tags->current();
		if ( 'cdn.useproof.com' === parse_url( $src, PHP_URL_HOST ) ) {
			$tag->removeAttribute( 'src' );
			$tag->setAttribute( 'id', 'useproof' );
			$this->set_no_minify();

			$override_src = <<<JS
			(function() {
    var script = document.getElementById('useproof');
    if (!script) {
        return;
    }
    var oldCurrentScript = null;
    var new_script = document.createElement('script');
    Object.defineProperty(new_script, 'src', {
        get: function() {
            return '$src';
        }
    });
    Object.defineProperty(document, 'currentScript', {
        get: function() {
        	if(oldCurrentScript){
        		return oldCurrentScript;
        	}
            return new_script;
        },
        set: function (v) { 
        	oldCurrentScript = v;
         },
        enumerable: true,
    	configurable: true
    });
})();
JS;
			$this->inject_tag( $this->create_script( $override_src ) );
			$this->inject_tag( $this->create_script( null, $src ) );
		}
	}
}