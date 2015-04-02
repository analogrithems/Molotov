<?php
namespace Arez\Core\Lib;

class Cache
{
	protected $dir;
	protected $filename;
	protected $contents;
	protected $expire;
	protected $file;

	public function __construct($dir, $filename, $expire = null)
	{
		$this->dir = $dir;
		$this->filename = $filename;
		$this->expire = $expire;
		$this->file = $this->dir . '/' . $this->filename;
	}

	public function setContents($contents)
	{
		$this->contents = $contents;
		return $this;
	}

	public function getFile()
	{
		if(!$this->exists() || $this->expired()) {
			$this->write();
		}
		return $this->file;
	}

	public function write()
	{
		if(!is_dir(dirname($this->file))) {
			$rs = @mkdir(dirname($this->file),0775,true);

			if(!$rs){
				\Phalcon\DI::getDefault()->get('logger')->log( 'Failed to make new Directory:'.dirname($this->file)." verify the parent exists and you have permission" );
			}
		}
		file_put_contents($this->file, $this->contents);
		return $this;
	}

	public function exists()
	{
		if(file_exists($this->file)) {
			return true;
		} else {
			return false;
		}
	}

	public function expired($time)
	{
		if(!$time || !$this->exists()) {
			return false;
		}
		if((time() - filemtime($this->file)) > $time) {
			return true;
		} else {
			return false;
		}
	}
}