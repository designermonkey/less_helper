<?php

	class extension_less_helper extends Extension {

		public $workspace_position = NULL;
		public $less_exec = "lessc";
		
		/*-------------------------------------------------------------------------
			Extension definition
		-------------------------------------------------------------------------*/
		
		public function about() {

			return array(
				'name' => 'LESS Helper',
				'version' => '1.0',
				'release-date' => '2012-08-17',
				'author' => array(
					'name' => 'Joh Porter',
					'email' => 'john@designermonkey.co.uk',
				)
			);
		}
 
		public function getSubscribedDelegates() {

			return array(
				array(
					'page' => '/frontend/',
					'delegate' => 'FrontendOutputPostGenerate',
					'callback' => 'find_matches'          
				)
			);
		}
		
		/*-------------------------------------------------------------------------
			Delegates
		-------------------------------------------------------------------------*/
		public function find_matches(&$context) {

			$context['output'] = preg_replace_callback('/(\"|\')([^\"\']+)\.less/', array($this, '__replace_matches'), $context['output']);
		}
		
		/*-------------------------------------------------------------------------
			Helpers
		-------------------------------------------------------------------------*/
		private function __replace_matches($matches) {

			$this->workspace_position = strpos($matches[0], 'workspace');
			if (!$this->workspace_position) $this->workspace_position = 1;
			
			$path = DOCROOT . "/" . substr($matches[0], $this->workspace_position);
			$path = $this->__generate_css($path);
			$mtime = @filemtime($path);
			
			return str_replace('.less', ($mtime ? '.css?' . 'mod-' . $mtime : NULL), $matches[0]);
		}
		
		private function __generate_css($filename) {

			# Setup .css and .less filenames
			$less_filename = $filename;
			$css_filename = str_replace('.less', '.css', $filename);
			$css_filename = str_replace('less', 'css', $filename);

			# If Sass doesn't exist, throw an error in the CSS
			if (!file_exists($less_filename)) {

				file_put_contents($css_filename, "/** Error: Less file not found **/");
			}
			else if (!file_exists($css_filename) || filemtime($css_filename) < filemtime($less_filename))
			{
				@unlink($css_filename);
				# Generate .css via shell command
				exec($this->less_exec . ' ' . escapeshellcmd($less_filename) . ' ' . escapeshellcmd($css_filename));
			}
			
			return $css_filename;
		}
	}
