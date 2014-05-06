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

	private $js_code = array();

	/**
	 * 注意，合并后存在的文件夹一定要和原有的CSS文件处于同一个文件夹深度，因为css文件里面可能有图片文件引入使用的是相对路径
	 * @var string
	 */
	private $merge_path = 'compress/css/';

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
			$this->merge_path = 'compress/'.$module.'/css/';
		}
	}

	public function css($css){
		$this->css[] = $css;
	}

	public function js($js){
		$this->js[] = 'media/'.$js;
	}

	/**
	 * javascript代码
	 * @param $js_code
	 */
	public function js_code($js_code){
		$this->js_code[] = $js_code;
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
				if($merge_css){
					$style .= HTML::style($merge_css, array(), TRUE)."\n";
				} else {
					foreach($this->css as $css){
						$style .= HTML::style('media/'.$css, array(), TRUE)."\n";
					}
				}
			}
		}

		return $style;
	}

	public function render_js(){
		$script = '';

		if(!empty($this->js_code)){
			$code = implode(";\n",$this->js_code);
			$script .= <<<JS
<script type="text/javascript">
	{$code};
</script>

JS;
		}

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
			try{
				if(!is_dir($compress_dir)){
					mkdir($compress_dir, 02777, TRUE);
				}

				// 如果合并的css文件存在则删除
				if(file_exists($merge_path)){
					unlink($merge_path);
				}
				$new_merge_handle = fopen($merge_path, 'a');

				foreach($this->css as $file){
					$file_path = $this->getfile($file);
					$file_handle = fopen($file_path, 'r');
					$i = 0;
			        while (!feof($file_handle))
			        {
			        	fseek($file_handle, $i*4096);
			        	fwrite($new_merge_handle, fread($file_handle, 4096));
			            $i++;
			        }
			        fclose($file_handle);
				}

				fclose($new_merge_handle);
			} catch (Exception $e){
				Kohana_Exception::log($e);
				return FALSE;
			}
			
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
		return $this->merge_path.$file_name.'.css';
	}

	/**
	 * 返回JS与CSS的写入路径
	 * @return string
	 */
	private function get_merge_file_realpath(){
		$file_name = $this->get_file_name();
		$path = str_replace('/', DIRECTORY_SEPARATOR, $this->merge_path);
		return DOCROOT.$path.$file_name.'.css';
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
