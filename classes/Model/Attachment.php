<?php
/**
 * 附件资源Model
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: resource.php 39 2012-07-09 10:05:40Z Jie.Liu $
 */
class Model_Attachment extends ORM {

	// 图片最大宽度和高度
	private $_max_width = 640;
	private $_max_height = 480;

	// 图片输出质量
	private $_quality = 80;

	protected $_search_row = array(
		'title',
	);

	protected $_list_row = array(
		'id',
		'title',
		'filename',
		'path',
		'dateline',
	);

	public function get_path() {
		return $this->path.'&nbsp;<a target="_blank" href="'.URL::site('/upload/'.$this->path).'"><img height="100" class="preview" src="'.URL::site('/upload/'.$this->path).'"><i class="icon-picture icon-green icon-large"></i></a>';
	}

	/**
	 * 删除数据的同时删除文件
	 * @see Kohana_ORM::delete()
	 */
	public function delete(){
		$filename = Upload::$default_directory.DIRECTORY_SEPARATOR.$this->attachment;
		if (file_exists($filename)){
			@unlink($filename);
		}
		parent::delete();
	}

	/**
	 * 文件保存,写入数据库
	 * @param array $file
	 * @param array $data
	 */
	public function save_file(array $file, $dir){
		//创建文件上传路径
		$upload_dir = $this->upload_dir($dir);
		$abs = Upload::$default_directory.DIRECTORY_SEPARATOR.$upload_dir;
		if ($filename = Upload::save($file, NULL, $abs)){
			// 大图片自动缩放
			$this->resize($filename);

			$default_data = array();
			$filename = str_replace('\\', '/', $filename);
			$file_info = pathinfo($filename);
			if (class_exists('Auth')) {
				$login_user = Auth::instance()->get_user();
				if ($login_user) {
					$default_data['user_id'] = $login_user->pk();
				}
			}

            $default_data['filename'] = $file['name'];
			$default_data['filesize'] = $file['size'];
            $file_path = str_replace('\\', '/', $upload_dir);
            $default_data['path'] = $file_path.$file_info['basename'];
			$default_data['mime'] = $file['type'];
			$default_data['filetype'] = $file_info['extension'];
			$default_data['dateline'] = $_SERVER['REQUEST_TIME'];
			$this->values($default_data)->save();
			return $default_data;
		}
	}

	/**
	 * 自动缩放图片到允许的尺寸
	 */
	private function resize($filename){
		$file = Image::factory($filename);
		if($file->width > $this->_max_width){
			$file->resize($this->_max_width, NULL)->save(NULL, $this->_quality);
		} elseif ($file->height > $this->_max_height){
			$file->resize(NULL, $this->_max_height)->save(NULL, $this->_quality);
		}
	}

	/**
	 * 生成上传文件路径
	 */
	private function upload_dir($dir){

		//生成年月文件夹和日期文件夹
		$dir .= DIRECTORY_SEPARATOR.date('Ym').DIRECTORY_SEPARATOR.date('d');
		$abs = Upload::$default_directory.DIRECTORY_SEPARATOR.$dir;
		if (!is_dir($abs)){
			mkdir($abs, 0777, TRUE);
		}

		return $dir.DIRECTORY_SEPARATOR;
	}

}
