<?php
/**
 * 生成静态文件链接
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: media.php 42 2012-07-11 09:55:00Z Jie.Liu $
 */
class Kohana_Media{

	protected static $instance = NULL;

	private $module = NULL;

	private $css = array();

	private $js = array();

	/**
	 * 注意，合并后存在的文件夹一定要和原有的CSS文件处于同一个文件夹深度，因为css文件里面可能有图片文件引入使用的是相对路径
	 * @var string
	 */
	private $merge_path = 'media/compress/';

	public static function get_instance($module = NULL){
		if(self::$instance instanceof self){
			return self::$instance;
		}

		self::$instance = new self($module);

		return self::$instance;
	}

	private function __construct($module){
		$this->module = $module;
		if($module !== NULL){
			$this->merge_path = 'media/'.$module.'/compress/';
		}
	}

	public function css($css){
		$this->css[] = $css;
	}

	public function js($js){
		$this->js[] = 'media/'.$js;
	}

	public function render_css(){
		$style = '';
		if(!empty($this->css)){
			if(Kohana::$environment === Kohana::DEVELOPMENT){ // 如果是开发状态，则不合并CSS
				foreach($this->css as $css){
					$style .= HTML::style('media/'.$css, array(), TRUE)."\n";
				}
			} else { // 否则合并CSS文件
				$merge_css = $this->merge_css();
				$style = HTML::style($merge_css, array(), TRUE)."\n";
			}
		}

		return $style;
	}

	public function render_js(){
		$script = '';
		if(!empty($this->js)){
			foreach($this->js as $js){
				$script .= HTML::script($js, array(), TRUE)."\n";
			}
		}
		return $script;
	}

	/**
	 * 合并css文件返回合并后的文件路径
	 */
	private function merge_css(){

		$merge_path = $this->get_merge_file_realpath();
		//如果需要合并CSS文件
		if($this->need_merge($merge_path)){

			$compress_dir = pathinfo($merge_path, PATHINFO_DIRNAME);
			if(!is_dir($compress_dir)){
				mkdir($compress_dir);
			}
			$content = '';
			foreach($this->css as $file){
				$file_path = $this->getfile($file);
				$content .= file_get_contents($file_path);
			}

			file_put_contents($merge_path, $content);
		}

		return $this->get_merge_file();
	}

	private function get_file_name(){
		$salt = implode('', $this->css);
		return md5($salt);
	}

	/**
	 * 获取压缩后CSS样式的访问路径
	 * @return string
	 */
	private function get_merge_file(){
		$file_name = $this->get_file_name();
		if($this->module){
			return $this->merge_path.$file_name.'.css';
		} else {
			return $this->merge_path.$file_name.'.css';
		}
	}

	/**
	 * 返回JS与CSS的写入路径
	 * @return string
	 */
	private function get_merge_file_realpath(){
		$file_name = $this->get_file_name();
		$path = str_replace('/', DIRECTORY_SEPARATOR, $this->merge_path);
		if($this->module){
			return MODPATH.DIRECTORY_SEPARATOR.$this->module.DIRECTORY_SEPARATOR.$path.$file_name.'.css';
		} else {
			return APPPATH.$path.$file_name.'.css';
		}
	}

	/**
	 * 判断是否要进行CSS压缩，根据已生成的文件与源文件的时间来确定
	 * @param $path
	 */
	private function need_merge($path){

		if(!file_exists($path)){
			return TRUE;
		}

		$merge_filetime = filemtime($path);

		foreach($this->css as $file){

			$file_time = filemtime($this->getfile($file));

			// 如果源文件的更新时间比合并的文件新，则需要重新合并
			if($file_time > $merge_filetime){
				return TRUE;
			}
		}

		return FALSE;
	}

	private function getfile($file){
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		// Remove the extension from the filename
		$filename = substr($file, 0, -(strlen($ext) + 1));
		$file = Kohana::find_file('media', $filename, $ext);
		if($file){
			return $file;
		} else {
			throw new Kohana_Exception('No such file was loaded by the media module.');
		}
	}
}
