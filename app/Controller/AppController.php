<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
	var $components = array('Auth','Session');
	var $uses = array('User');
	function  beforeFilter() {
        $this->Auth->allow('index','login','fb_login','callback','logout','show','category','contact','get_app','view','click','search');
		$user = NULL;
		if (!$this->Session->check('user') && $this->Auth->loggedIn()){
			$user_id = $this->Auth->user('id');
			$user = $this->User->findById($user_id);
			$this->Session->write('user',$user);
		}
		elseif ($this->Auth->loggedIn()){
			$user = $this->Session->read('user');
		}
	//	var_dump($user);
		$this->set('user', $user);
		$this->set('title_for_layout','Pinterest Applications');
	}
}
