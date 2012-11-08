<?php
/**
 * 静态文件控制器
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: media.php 68 2012-08-28 10:16:13Z Jie.Liu $
 */
class Controller_Media extends Controller{

    private $gzip = TRUE;

    /**
     * 输出合并后的静态文件文件
     */
    public function action_file()
    {

        // Get the file path from the request
        $file = $this->request->param('file');

        $compress = new Compress();

        $compress_file = $compress->load($file);

        if ($compress_file !== FALSE)
        {

            // Smush.it png, gif and jpg files
            if (in_array($compress_file->ext, array('png', 'gif', 'jpg', 'jpeg', 'ico')))
            {
                //$compress_file = $compress_file->smushit();
            }

            // Minify js and css files
            if (in_array($compress_file->ext, array('js', 'css')))
            {
                $compress_file = $compress_file->minify();
            }

            // Gzip files if flagged
            if ($this->gzip === TRUE)
            {
                $compress_file = $compress_file->gzip();
                $this->response->headers('content-encoding', 'gzip');
            }

            // Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
            $this->check_cache(sha1($this->request->uri()).filemtime($compress_file->file), $this->request);

            // Send the file content as the response
            $this->response->body($compress_file->render());

            // Set the proper headers to allow caching
            $this->response->headers('content-type',  File::mime_by_ext($compress_file->ext));
            $this->response->headers('last-modified', date('r', filemtime($compress_file->file)));
        }
        else
        {
            // Return a 404 status
            $this->response->status(404);
        }
    }

    /**
     * 图片处理
     * 处理图片缩放
     * 处理图片水印
     */
    public function action_image(){
        $image = $this->request->param('image');
        // 如果url连接中有图片的尺寸信息，则需要进行裁剪，然后返回裁剪后的图片
        if(preg_match('/^(.*\.(jpg|png|gif))\.([\d]+x[\d]+)\.\2$/', $image, $matches)){
            $image = $matches[1]; // 文件地址
            $ext = $matches[2];  // 文件后缀
            $size = $matches[3];  // 文件缩放尺寸
            $image = $this->resize($image, $ext, $size, 'upload');
            $watermark_url = Watermark::instance()->watermark($image);
        } else {
            // 给图片添加水印
            $watermark_url = Watermark::instance()->watermark($image, 'upload');
        }

        $this->request->redirect($watermark_url);
    }


    /**
     * 图片裁剪
     * @param $image
     * @param $size
     * @param null $dir
     * @return string 返回裁剪后的图片地址
     */
    private function resize($image, $ext, $size, $dir= NULL){
        $resize_url = 'thumb/'.$image.'.'.$size.'.'.$ext;
        if($dir){
            $source = $dir.'/'.$image;
        }else{
            $source = $image;
        }

        $source = DOCROOT.$source;
        if(is_file($source)){
            $resize_file = DOCROOT.$resize_url;
            if( ! is_file($resize_file)){

                $pathinfo = pathinfo($resize_file);
                if(!is_dir($pathinfo['dirname'])){
                    mkdir($pathinfo['dirname'], 0777, TRUE);
                }
                $source_image = Image::factory($source);
                list($width, $height) = explode('x', $size);
                $source_image->resize($width, $height);
                $source_image->save($resize_file);
            }

        } else{
            throw new HTTP_Exception_404();
        }

        return $resize_url;
    }
}