<?php
namespace Arez\Core\Lib;

class Asset
{
	public function __construct($file, $type)
	{
		$this->file = $file;
		$this->type = $type;
		$this->mime = $this->getMime($file);
	}

	public function getMime($file)
	{
		switch($this->type) {
			case 'javascript':
				$mime = 'application/javascript';
				break;
			case 'css': case 'sass':
				$mime = 'text/css';
				break;
			default: 
				$contents = file_get_contents($file);
				$file_info = new \finfo(FILEINFO_MIME);
				$mime = $file_info->buffer($contents);
				$mime = split($mime,';')[0];
				break;
		}
		return $mime;
	}

	public function render()
	{
		//header("X-Sendfile: $this->file");
		header("Content-Type: {$this->mime}");
		readfile($this->file);
	}
}