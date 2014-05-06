<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 初始化文件 包含路由信息
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: init.php 64 2012-08-24 11:08:56Z Jie.Liu $
 */

// 图片请求URL，加载水印信息
Route::set('image', 'upload(/<image>)', array('image' => '.+'))
	->defaults(array(
	'controller' => 'media',
	'action'     => 'image',
	'image'       => NULL,
));
// 静态文件请求URL
Route::set('media', 'media(/<file>)', array('file' => '.+'))
    ->defaults(array(
        'controller' => 'media',
        'action'     => 'file',
        'file'       => NULL,
    ));