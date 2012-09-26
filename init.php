<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 初始化文件 包含路由信息
 *
 * @author  Jie.Liu (ljyf5593@gmail.com)
 * @Id $Id: init.php 64 2012-08-24 11:08:56Z Jie.Liu $
 */

// Static file serving (CSS, JS, images)
Route::set('image', 'upload(/<image>)', array('image' => '.+'))
	->defaults(array(
	'controller' => 'media',
	'action'     => 'image',
	'image'       => NULL,
));
Route::set('media', 'media(/<file>)', array('file' => '.+'))
    ->defaults(array(
        'controller' => 'media',
        'action'     => 'file',
        'file'       => NULL,
    ));