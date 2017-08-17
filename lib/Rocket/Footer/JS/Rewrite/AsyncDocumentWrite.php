<?php


namespace Rocket\Footer\JS\Rewrite;


class AsyncDocumentWrite extends RewriteAbstract {

	protected $injected = false;

	/**
	 * @param string  $content
	 *
	 * @param  string $src
	 *
	 * @return void
	 */
	protected function do_rewrite( $content, $src ) {
		if ( ! $this->injected ) {
			$this->inject_tag( $this->create_script( 'document.old_write=document.old_write||document.write;document.write=function(data){if(document.currentScript)(function check(){if(typeof jQuery==="undefined")setTimeout(10,check);else jQuery(document.currentScript).before(data)})()};' ) );
			$this->injected = true;
		}
	}
}