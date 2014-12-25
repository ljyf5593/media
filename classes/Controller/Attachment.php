<?php
/**
 * 附件上传控制器
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: Attachment.php 68 2012-08-28 10:16:13Z Jie.Liu $
 */
class Controller_Attachment extends Controller {

    private $order = 'name';

    public function action_upload() {
        $result = array(
            'status' => 'error',
            'message' => '操作失败',
        );
        $file = current($_FILES);
        $dir = $this->request->query('dir');
        $attachment = new Model_Attachment();
        if (Upload::not_empty($file)){
            if (Upload::valid($file)){ //检查数据是否正常
                if (Upload::type($file, array('jpg', 'png', 'gif'))){
                    if ($file = $attachment->save_file($file, $dir)){
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
    public function action_manage() {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = DOCROOT.'/upload/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = URL::site('/upload/', 'http').'/';
        //图片扩展名
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        //目录名
        $dir_name = $this->request->query('dir');
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                mkdir($root_path);
            }
        }

        //根据path参数，设置各路径和URL
        $path = $this->request->query('path');
        if (empty($path)) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $path;
            $current_url = $root_url . $path;
            $current_dir_path = $path;
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }
        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }

        //排序形式，name or size or type
        $this->order = strtolower($this->request->query('order'));
        if (!$this->order) {
            $this->order = 'name';
        }
        usort($file_list, array($this, 'cmp_func'));

        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        $this->response->headers('Content-type', 'application/json; charset=UTF-8')->send_headers()->body(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    private function cmp_func($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($this->order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($this->order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }
}
