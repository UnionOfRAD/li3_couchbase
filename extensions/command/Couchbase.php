<?php

namespace li3_couchbase\extensions\command;

use lithium\data\Connections;

class Couchbase extends \lithium\console\Command {

	/**
	 * Reads data from an endpoint and decodes it
	 *
	 * @param $url
	 * @return mixed
	 */
	protected function _get($url) {
		return json_decode(file_get_contents("{$this->hostname}{$url}"));
	}

	public function _init() {
		parent::_init();
		$this->db = Connections::get('default');
		$this->cluster = new \CouchbaseClusterManager(
			$this->db->_config['host'],
			$this->db->_config['login'],
			$this->db->_config['password']
		);
		$this->info = json_decode($this->cluster->getInfo());
		$this->hostname = "http://{$this->info->nodes[0]->hostname}";
		$this->viewPath = LITHIUM_APP_PATH . '/extensions/data/source/couchbase/views';
	}

	/**
	 * Loads design docs into Couchbase
	 *
	 * @todo Fix Iterator mess. I'm pretty sure I've done it wrong in my haste.
	 */
	public function import() {
		$data = array();
		$dir = new \RecursiveDirectoryIterator($this->viewPath);
		foreach ($dir as $fileinfo) {
			if ($fileinfo->isDir()) {
				$viewDir = new \RecursiveDirectoryIterator($fileinfo->getPathname());
				foreach ($viewDir as $fi) {
					$scripts = new \RecursiveDirectoryIterator($fi->getPathName());
					foreach ($scripts as $f) {
						$fn = str_replace('.js', '', $f->getFilename());
						$data[$fileinfo->getFilename()]['views'][$fi->getFilename()][$fn] =
							file_get_contents($f->getPathname());
					}
				}

			}
		}
		foreach ($data as $design => $v) {
			$this->db->setDesignDoc($design, json_encode($v));
			$this->out("wrote _design/{$design}");
		}
	}

	/**
	 * Reads design docs from the cluster and writes them to disk
	 */
	public function export() {
		if (!file_exists($this->viewPath)) {
			mkdir($this->viewPath, 0755, true);
			$this->stop();
		}
		$buckets = $this->_get($this->info->buckets->uri);
		$default = $buckets[0];
		$ddocs = $this->_get($default->ddocs->uri);
		foreach ($ddocs->rows as $doc) {
			$design = str_replace('_design', '', $doc->doc->meta->id);
			$views = $doc->doc->json->views;
			foreach ($views as $name => $scripts) {
				$out = "{$this->viewPath}{$design}/{$name}";
				if (!file_exists($out)) {
					mkdir($out, 0755, true);
				}
				if (!empty($scripts->map)) {
					file_put_contents("{$out}/map.js", $scripts->map);
					$this->out("wrote map.js to {$out}/map.js");
				}
				if (!empty($scripts->reduce)) {
					file_put_contents("{$out}/reduce.js", $scripts->reduce);
					$this->out("wrote reduce.js to {$out}/map.js");
				}
			}
		}
		$this->stop();
	}
}

