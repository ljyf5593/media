<?php
/**
 * 附件上传控制器
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: Attachment.php 68 2012-08-28 10:16:13Z Jie.Liu $
 */
class Controller_Attachment extends Controller {

    public function action_upload() {
        $result = array(
            'status' => 'error',
            'message' => '操作失败',
        );
        $file = current($_FILES);
        $attachment = new Model_Attachment();
        if (Upload::not_empty($file)){
            if (Upload::valid($file)){ //检查数据是否正常
                if (Upload::type($file, array('jpg', 'png', 'gif'))){
                    if ($file = $attachment->save_file($file)){
                        $result['status'] = 'success';
                        $result['path'] = '/'.Upload::$default_directory.'/'.$file['path'];
                        $result['url'] = URL::site($result['path'], TRUE);
                        $result['message'] = '操作成功';
                    }
                }else {
                    $result['message'] = '非法文件类型';
                }
            }else {
                $result['message'] = '文件数据错误，请重新选择上传';
            }
        }else {
            $result['message'] = '请选择文件';
        }

        // 文本编辑器需要这个数据判断是否上传成功
        $result['error'] = intval($result['status'] != 'success');
        echo json_encode($result);
        exit;
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
            $file['is_dir'] = false;
            $file['has_file'] = false;
            $file['dir_path'] = '';
            $file['is_photo'] = TRUE;
            $file['filename'] = $file_url; //文件名，包含扩展名
            if (file_exists($filepath)) {
                $file['filetype'] = File::mime($filepath);
                $file['filesize'] = filesize($filepath);
                $file['datetime'] = date('Y-m-d H:i:s', filemtime($filepath)); //文件最后修改时间
            }
            
            $file_list[] = $file;
        }

        //文件列表数组
        $result['file_list'] = $file_list;
        $result['page'] = $pagination->current_page;
        $result['pagination'] = $pagination->render();
        echo json_encode($result);exit;
    }   
}