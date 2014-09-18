<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 资源管理控制器
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: attachment.php 33 2012-06-29 07:32:34Z Jie.Liu $
 */
class Controller_Editor extends Controller {

	/**
	 * 文件上传
	 */
	public function action_upload(){
		if(!empty($_FILES)) {
			$attachment = new Model_Attachment();
			$result = $attachment->uploadFile($_FILES['imgFile']);
			echo json_encode($result);exit;
		}
	}

	/**
	 * 编辑器图片管理
	 */
	public function action_manage(){
		$attachment = new Model_Attachment();
		$result = array();
		//相对于根目录的上一级目录
		$result['moveup_dir_path'] = '';
		//相对于根目录的当前目录
		$result['current_dir_path'] = '';
		//当前目录的URL
		$result['current_url'] = '';
		//文件数
		$result['total_count'] = $attachment->count_all();
        
		$pagination = new Pagination(array(
			'total_items' => $result['total_count'],
			'view' => 'pagination/admin',
		));

		$attachment_list = $attachment->limit($pagination->items_per_page)->offset($pagination->offset)->find_all();
		$file_list = array();
		foreach($attachment_list as $item){
			$file_url = URL::site(Upload::$default_directory.'/'.$item->path, TRUE);
			$filepath = DOCROOT.Upload::$default_directory.'/'.$item->path;
			$file = array();
			if(file_exists($filepath)){
				$file['is_dir'] = false;
				$file['has_file'] = false;
				$file['filesize'] = filesize($filepath);
				$file['dir_path'] = '';
				$file['is_photo'] = TRUE;
				$file['filetype'] = File::mime($filepath);
				$file['filename'] = $file_url; //文件名，包含扩展名
				$file['datetime'] = date('Y-m-d H:i:s', filemtime($filepath)); //文件最后修改时间
				$file_list[] = $file;
			}
		}

		//文件列表数组
		$result['file_list'] = $file_list;
		$result['page'] = $pagination->current_page;
		$result['pagination'] = $pagination->render();
		echo json_encode($result);exit;
	}
}