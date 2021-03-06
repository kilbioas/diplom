<?php
class Url {
	private $url;
	private $ssl;
	private $rewrite = array();
	
	public function __construct($url, $ssl = '') {
		$this->url = $url;
		$this->ssl = $ssl;
	}
		
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}
		
	public function link($route, $args = '', $connection = 'NONSSL') {
		$url = ($connection ==  'NONSSL') ? $this->url : $url = $this->ssl;
		$url .= 'index.php?route=' . $route;
		if ($args)
			$url .= '&' . trim($args, '&');
		foreach ($this->rewrite as $rewrite)
			$url = $rewrite->rewrite($url);
		return $url;
	}
}
?>