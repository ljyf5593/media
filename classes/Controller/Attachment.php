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
        $file = $_FILES['file'];
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
        echo json_encode($result);
        exit;
    }    
}