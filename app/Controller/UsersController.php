<?php
App::import('Vendor', 'OAuth/OAuthClient');
App::uses('Sanitize', 'Utility');

class UsersController extends AppController {

	var $name = 'Users';
	var $helpers = array('Html');
	var $components = array('Auth','Email');
	var $uses = array('User');
	public function index(){
		$this->redirect(array('controller'=>'applications','action'=>'index'));
	}
	
    private function createClient() {
        return new OAuthClient(CLIENT_ID,CLIENT_SECRET);
    }
	function login(){
//		$this->redirect(array('controller'=>'applications','action'=>'index'));
	}
	function contact(){
		if (!empty($this->request->data)){
			$this->request->data = Sanitize::clean($this->request->data, array('encode' => true, 'remove_html'=>true));
			
			$this->Email->from    = $this->request->data['User']['email'];
			$this->Email->to      = 'rogerwu99@gmail.com';
			$this->Email->subject = $this->request->data['User']['Subject'];
			
			$status = $this->Email->send(htmlspecialchars_decode($this->request->data['User']['Message']));
			$this->Session->setFlash('Message Sent');
			$this->redirect(array('controller'=>'applications','action'=>'index'));
		}
	}
	function logout(){
		$this->Session->destroy();
		$this->redirect($this->Auth->logout());
	}
	function fb_login(){
		
		$client = $this->createClient();
		$redirect_url = 'https://www.facebook.com/dialog/oauth?client_id='.CLIENT_ID.'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/users/callback/facebook'.'&scope=email';
		$this->redirect($redirect_url);
	}
	function callback($service=NULL){
		$consumer = $this->createClient();
		$access_url = 'https://graph.facebook.com/oauth/access_token?client_id='.CLIENT_ID.'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].'/users/callback/facebook&client_secret='.CLIENT_SECRET.'&code='.$this->params['url']['code'];
		$accessToken = file_get_contents($access_url);
		$user = json_decode(file_get_contents('https://graph.facebook.com/me?' . $accessToken));
	//echo 'user';
	//var_dump($user);
		$user_record = $this->User->find('first', array('conditions' => array('User.fb_uid'=>$user->id)));
		
		
		//var_dump($user_record);
		
		if (empty($user_record)) {
			$this->User->create();
			$user_record['User']['name']=$user->name;
			$user_record['User']['fb_uid']=$user->id;
			$user_record['User']['username']=$user->email;
			$user_record['User']['password']=$this->Auth->password($this->__randomString());
			$user_record['User']['new_password'] = $user_record['User']['password'];	
			$user_record['User']['confirm_password'] =  $user_record['User']['new_password'];
			if (!$this->User->save($user_record)){
				echo 'user not save';
			}
			$id = $this->User->id;
		}
		else {
			$id = $user_record['User']['id'];
		}
		//$this->_login($db_results['User']['username'],$db_results['User']['password']);
		//$this->Auth->fields = array('username' => 'username', 'password' => 'password');
		$auth_array = array_merge($user_record,array('id'=>$id));
		$this->Auth->login($auth_array);
		
		$this->redirect(array('action'=>'index'));
			
	}
	private  function __randomString($minlength = 20, $maxlength = 20, $useupper = true, $usespecial = false, $usenumbers = true){
        $charset = "abcdefghijklmnopqrstuvwxyz";
        if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($usenumbers) $charset .= "0123456789";
        if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";
        if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
        else $length = mt_rand ($minlength, $maxlength);
        $key = '';
        for ($i=0; $i<$length; $i++){
            $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        }
        return $key;
    }
	
	
}
/*
	function _login($username=null, $password=null){
		if ($username && $password){
			$user_record_1=array();
			$user_record_1['Auth']['username']=$username;
			$user_record_1['Auth']['password']=$password;
			$this->Auth->authenticate_from_oauth($user_record_1['Auth']);
			return;		
		}
	}
	function login(){
		$this->_login($this->data['Auth']['username'],$this->Auth->hasher($this->data['Auth']['password']));
		$this->Session->write('cart',0);
		$this->redirect(array('action'=>'view_my_friends',45));
	}
	function register(){}
	function logout(){
		$user=$this->Auth->getUserInfo();
		$this->Session->destroy();
		$url = 'https://www.facebook.com/logout.php?next='.ROOT_URL.'&'.$user['facebook_access_key'].'&confirm=1';
		$this->Auth->logout($url);
	}
	private function createConsumer($type) {
		switch ($type) {
			case 'facebook':
				return new OAuth_Consumer(FB_CLIENT,FB_SECRET);
		}
    }
	function getOAuth($service=NULL){
		$consumer = $this->createConsumer($service);
		$redirect_url = '';
		switch ($service){
			case 'facebook':
				$redirect_url = 'https://www.facebook.com/dialog/oauth?client_id='.FB_CLIENT.'&redirect_uri='.ROOT_URL.'/users/callback/facebook'.'&scope=user_about_me,user_activities,user_birthday,user_education_history,user_events,user_groups,user_hometown,user_interests,user_relationships,user_religion_politics,user_status,user_website,user_work_history,email,user_checkins,user_likes,friends_likes,friends_interests,friends_checkins,friends_activities,friends_work_history,friends_relationship_details,friends_website,friends_religion_politics,friends_relationships,friends_location,friends_relationship_details,friends_hometown,friends_education_history,friends_birthday,friends_about_me,offline_access,friends_status';
				break;
		}	
		$this->redirect($redirect_url);
	}
	function callback($service=NULL){
		$consumer = $this->createConsumer($service);
		$requestTokenName = $service.'_request_token';
		$accessTokenName = $service.'_access_token';
		$accessKeyName = $service.'_access_key';
		$accessSecretName = $service.'_access_secret';
		$access_url = '';
		switch ($service){
			case 'facebook':
				$access_url = 'https://graph.facebook.com/oauth/access_token?client_id='.FB_CLIENT.'&redirect_uri='.ROOT_URL.'/users/callback/facebook&client_secret='.FB_SECRET.'&code='.$this->params['url']['code'];
				break;
		}
		$accessToken = file_get_contents($access_url);
	//	if ($service=='facebook'){
			$this->Session->write('facebook_access_key',$accessToken);
		//}
		//$this->User->save($this->data);
		if ($this->Session->check('get_new_token')){
			$this->Session->delete('get_new_token');
		}
		$this->redirect(array('action'=>'fb_callback'));
	}
	function fb_callback(){	
		if (!empty($this->data)){
			$email = $this->data['User']['email'];
			$password = $this->data['User']['new_password'];
			$confirm =$this->data['User']['confirm_password'];
			$accept = $this->data['User']['accept'];
			$fb_uid = $this->data['User']['fb_uid'];
			$first_name = $this->data['User']['first_name'];
			$last_name = $this->data['User']['last_name'];
			$month = $this->data['User']['smonth'];
			$date = $this->data['User']['sdate']+1;
			$year = $this->data['User']['syear'];
			$sex = $this->data['User']['sex'];
			$this->data=array();
			$this->User->create();
			$this->data['User']['first_name'] = $first_name;
			$this->data['User']['last_name'] = $last_name;
			$this->data['User']['email'] = (string) $email;
			$this->data['User']['new_password']=$password;
			$this->data['User']['confirm_password']=$confirm;
			$this->data['User']['accept']=$accept;
			$this->data['User']['sex']=$sex;
			$final_year = (int)date('Y')-$year-13;
			$this->data['User']['birthday']= date('Ymd',strtotime($month.' '.$date.' '.$final_year));
			$password = $this->data['User']['password'] = $this->Auth->hasher($password); 
			$username = $this->data['User']['username']= (string) $email;
			$this->data['User']['fb_pic_url']='http://graph.facebook.com/'.$fb_uid.'/picture';
			$this->data['User']['facebook_access_key'] = $this->Session->read('facebook_access_key');
			$this->data['User']['fb_uid']=$fb_uid;
			$this->User->set($this->data);
			if ($this->User->validates()){
				$this->User->save();
				$this->_login($username,$password);
				$friend = $this->Friend->find('first', array('conditions' => array('Friend.fb_uid'=>$fb_uid)));
				$fb_results = file_get_contents('https://graph.facebook.com/me?'.$this->Session->read('facebook_access_key'));
				$fb_results = ereg_replace('"uid":([0-9]+)', '"uid":"\1"', $fb_results);
				$fb_user = json_decode($fb_results,true);
				//if (!empty($friend)) $this->Search->clean_data($friend,$fb_user);
				//else {
					$this->data = array();
					$this->data['Userprofile']['fb_uid'] = $fb_uid;
					$this->data['Userprofile']['first_name'] = (!array_key_exists('first_name',$fb_user))? NULL : $fb_user['first_name'];
					$this->data['Userprofile']['last_name'] = (!array_key_exists('last_name',$fb_user))? NULL : $fb_user['last_name'];
					$this->data['Userprofile']['birthday'] = 	(!array_key_exists('birthday',$fb_user))? NULL : date('Y-m-d', strtotime($fb_user['birthday']));
					$this->data['Userprofile']['gender'] = 	(!array_key_exists('gender',$fb_user))? NULL : $fb_user['gender'];
					$this->data['Userprofile']['relationship'] = 	(!array_key_exists('relationship_status',$fb_user))? NULL : $fb_user['relationship_status'];
					$this->data['Userprofile']['religion'] = 	(!array_key_exists('religtion',$fb_user))? NULL : $fb_user['religion'];
					$this->data['Userprofile']['political'] = 	(!array_key_exists('political',$fb_user))? NULL : $fb_user['political'];
					$this->data['Userprofile']['hometown'] = 	(!array_key_exists('hometown',$fb_user))? NULL : $fb_user['hometown']['name'];
					$this->data['Userprofile']['location'] = 	(!array_key_exists('location',$fb_user))? NULL : $fb_user['location']['name'];
					$this->data['Userprofile']['pic_url'] = 'https://graph.facebook.com/'.$fb_uid.'/picture';
					$this->data['Userprofile']['bio'] = 	(!array_key_exists('bio',$fb_user))? NULL : $fb_user['bio'];
					$work_info = "";
					$work_id_list = "";
					if (array_key_exists('work',$fb_user)){
						foreach($fb_user['work'] as $job){
							if (array_key_exists('employer',$job)) {
								$work_info.= $job['employer']['name'].' ';
								$work_id_list .= $job['employer']['id'].',';
							}
							if (array_key_exists('position',$job)) {
								$work_info.= $job['position']['name'].' ';
								$work_id_list .= $job['position']['id'].',';
							}
							if (array_key_exists('description',$job)) $work_info.= $job['description'].' ';
						}
					}
					$this->data['Userprofile']['work_data'] = ($work_info=='') ? NULL : (string)$this->getOpenCalaisThought($this->check_calais_length($work_info));
					$this->data['Userprofile']['work_data'] .= ($work_id_list=='') ? NULL : (string)' ****'.$work_id_list;
					$education_info="";
					$education_id_list = "";
					if (array_key_exists('education',$fb_user)){
						foreach ($fb_user['education'] as $school){
							if (array_key_exists('school',$school)) {
								$education_info .= $school['school']['name']. '|' ;
								$education_id_list .= $school['school']['id'].',';
							}
							if (array_key_exists('concentration',$school)){
								foreach($school['concentration'] as $degree){
									$education_info .= $degree['name']. '|';
									$education_id_list .= $degree['id'].',';
								}
							}
						}
					}
					$this->data['Userprofile']['edu_data'] = ($education_info=='') ? NULL :(string)	$education_info;
					$this->data['Userprofile']['edu_data'] .= ($education_id_list=='') ? NULL : (string)' ****'.$education_id_list;
					$this->data['Userprofile']['detail']=1;
					$this->Userprofile->save($this->data);
				// we have to make sure friends are not duplicates - we are overwriting them!!!
				
					$this->saveFriends($this->getFriends());
					$this->redirect(array('action'=>'view_my_friends/45'));
				//}
			}
			else {
				$accessToken = $this->Session->read('facebook_access_key');
				$fb_user = json_decode(file_get_contents('https://graph.facebook.com/me?' . $accessToken));
				$this->set('fb_user',$fb_user);
				$this->set('errors', $this->User->validationErrors);
				unset($this->data['User']['new_password']);
	   			unset($this->data['User']['confirm_password']);
			}
		}
		else {
			$accessToken = $this->Session->read('facebook_access_key');
			$fb_user = json_decode(file_get_contents('https://graph.facebook.com/me?' . $accessToken));
			$db_results = $this->User->find('first', array('conditions' => (array('User.fb_uid'=>$fb_user->id))));
			if (empty($db_results)) {
				//$this->set('fb_user',$fb_user);
				
				
				
				$this->temp_user($fb_user);
					$this->redirect(array('action'=>'view_my_friends/45'));
			
			// let's get all of your friends here
		//		$this->render();
			}
			else {
				$this->_login($db_results['User']['username'],$db_results['User']['password']);
				$this->User->read(null,$this->Auth->getUserId());
				$this->data['User']['facebook_access_key'] = $accessToken;
				$this->User->set($this->data);
				$this->User->save();
				$user = $this->Auth->getUserInfo();
				$me = $this->Userprofile->find('first',array('conditions'=>array('Userprofile.fb_uid'=>$user['fb_uid'])));
				$my_likes = $this->_getMyLikes($user['fb_uid'],true);	
				if (!empty($me)){
					$this->data=array();
					$update_me = false;
					if (is_null($me['Userprofile']['likes'])){
						$my_like_cats = $this->analyzeLikes($my_likes);
						$this->data['Userprofile']['likes'] = ($my_like_cats=='') ? NULL : $my_like_cats;
						$update_me = true;
					}
					else {
						$my_like_cats = $me['Userprofile']['likes'];
					}
					if (is_null($me['Userprofile']['interests'])){
						$my_interests = $this->getInterests($my_likes);
						$this->data['Userprofile']['interests'] = $my_interests;
						$update_me = true;
					}
					else {
						$my_interests=$me['Userprofile']['interests'];
					}
					if ($update_me){
						$this->Userprofile->read(null,$me['Userprofile']['fb_uid']);
						$this->data['Userprofile']['detail']=1;
						$this->Userprofile->save($this->data);
					}
				}
			// let's check if you have new friends?
			
			$this->redirect(array('action'=>'view_my_friends/45'));
			}
		}
	}
	function getInterests($likes){
		$ints = '';
		for($i=0;$i<sizeof($likes);$i++) {
			if ($likes[$i]['category']=='Interest')	$ints .= strtolower($likes[$i]['name']).',';
		}
		return $ints;
	
	}

	function _getMyLikes($friend_uid = null,$me=false){
		$user = $this->Auth->getUserInfo();
		$friend_likes_result =  file_get_contents('https://graph.facebook.com/me/likes?' . $this->Session->read('facebook_access_key'));
		$friend_likes = ereg_replace('"uid":([0-9]+)', '"uid":"\1"', $friend_likes_result);
		$friend_likes = json_decode($friend_likes,true);
		$likes = $friend_likes['data'];
		return $likes;
	}
	function analyzeLikes($likes){
		$cats = array();
		$test = array();
		$testCalais='';
		if ($likes > 200) array_splice($likes,200);
		for($i=0;$i<sizeof($likes);$i++){
			if (array_key_exists($likes[$i]['category'],$cats))	$cats[$likes[$i]['category']]++;
			else $cats[$likes[$i]['category']]=1;
		}
		$total = array_sum($cats);
		$count =0;
		for ($i=0;$i<sizeof($likes);$i++){
			if ($likes[$i]['category']!='Interest'){
				if ($cats[$likes[$i]['category']] > (0.05 * $total)){  // relevant categories only 
					$test[$count]="SELECT description,company_overview,mission,products from page where page_id=".$likes[$i]['id'];
					if ($count == 125) break;
					$count++;
				}
			}
		}
		$testCalais=$this->get_product_detail($test);
		$testCalais = $this->check_calais_length($testCalais);
		if ($testCalais=='') return '';
		else return $this->getOpenCalaisThought($testCalais);
	}
	function check_calais_length($text){
		if (strlen($text) > 100000) $text = substr($text,0,99500);
		return $text;
	}
	function get_product_detail($id_array){
		$return_string = '';
		for ($j=0;$j<ceil(sizeof($id_array)/25);$j++){
			$query_string = '{';
			$loop_limit = ($j*25+25 > sizeof($id_array)) ? sizeof($id_array) : $j*25+25;
			for($i=0*$j;$i<$loop_limit;$i++){
				$query_string .= '"query'.$i.'":"'.$id_array[$i].'"';
				if ($i!=$loop_limit-1) $query_string .= ',';
				else $query_string .= '}';
			}
			$query_string=urlencode($query_string);
			$all_friends_profile_result_string ='https://api.facebook.com/method/fql.multiquery?queries='.$query_string.'&format=json&'.$this->Session->read('facebook_access_key');
		 	$all_friends_profile_result = @file_get_contents($all_friends_profile_result_string);
 			$all_friends_profile = json_decode( preg_replace('/:(\d+)/', ':"${1}"', $all_friends_profile_result ) );
			if (!empty($all_friends_profile)){
				for ($k=0;$k<sizeof($all_friends_profile);$k++){
					$return_string .= ' '.strip_tags($all_friends_profile[$k]->fql_result_set[0]->description);				
					$return_string .= ' '.strip_tags($all_friends_profile[$k]->fql_result_set[0]->company_overview);				
					$return_string .= ' '.strip_tags($all_friends_profile[$k]->fql_result_set[0]->mission);				
					$return_string .= ' '.strip_tags($all_friends_profile[$k]->fql_result_set[0]->products);				
				}
			}
		}
		return $return_string;
	}
	
	function saveFriends($friends){
		$user = $this->Auth->getUserInfo();
		$friends_to_user = array();
		$fb_user_profiles = array();
		$service = 'facebook'; // hardcoded value for now
		$all_friends_profile_result = @file_get_contents('https://api.facebook.com/method/fql.query?query=select+uid,first_name,last_name,birthday_date+from+user+where+uid+in+(select+uid2+from+friend+where+uid1=me())&format=json&'.$this->Session->read('facebook_access_key'));
		$all_friends_profile = json_decode( preg_replace('/:(\d+)/', ':"${1}"', $all_friends_profile_result ) );
		$friends = array();
		foreach($all_friends_profile as $friend_profile){	
			if($friend_profile->uid == null)
				continue;
			if($friend_profile->uid == 'NULL')
				continue;
			if(ereg('([0-9]{1,2})/([0-9]{1,2})/([0-9]{4})', $friend_profile->birthday_date)){		
				$birthday = date('Y-m-d', strtotime($friend_profile->birthday_date));
			}
			elseif(ereg('([0-9]{1,2})/([0-9]{1,2})',$friend_profile->birthday_date)){
				$birthday = date('Y-m-d',strtotime($friend_profile->birthday_date.'/2010'));	
			}
			
			else 
				$birthday = null;
				
			$friends[] = array(
				'id' => (string)$friend_profile->uid,
				'birthday' => $birthday,
				'first_name' => $friend_profile->first_name,
				'last_name'=>$friend_profile->last_name
			);		
		}
	
		foreach($friends as $friend){
			
			$friends_to_user[] = array(
					'Friend' =>  array(
						'user_id'=>$user['id'], 
						'fb_uid' => $friend['id']
				)
			);		
			$fb_user_profiles[] = array('Userprofile' => array(
						'fb_uid' => $friend['id'], 
						'first_name' => $friend['first_name'], 
						'last_name' => $friend['last_name'], 
						'birthday' => $friend['birthday'],
						'pic_url' => 'https://graph.facebook.com/'.$friend['id'].'/picture'
					)
				);
		}
		
		
		$this->Friend->saveAll($friends_to_user);
		$this->Userprofile->saveAll($fb_user_profiles);
	}
	

	
	
	function view_my_friends($limit=45){
		$user = $this->Auth->getUserInfo();
		if (is_null($user)) $this->redirect('/');
		$this->set('user',$user);
		$friends = $this->Friend->find('all', array(
			'conditions'=>array('Friend.user_id' => $user['id'],),
			'order' => array(
				'ISNULL(birthday)', 				
				"IF( STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) >= CURDATE( ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH(`birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) +1, '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) )", 
				'birthday'
			),
			'limit' => 45,
			'page'=>$limit/45
			
		));
		$this->set('page',$limit/45);
		$this->set('friends',$friends);
		$this->set('search',false);		
	}
	function more_friends($limit=45){
		$user = $this->Auth->getUserInfo();
		$this->set('user',$user);
		$friends = $this->Friend->find('all', array(
			'conditions'=>array('Friend.user_id' => $user['id'],),
			'order' => array(
				'ISNULL(birthday)', 				
				"IF( STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) >= CURDATE( ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH(`birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) +1, '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) )", 
				'birthday'
			),
			'limit' => 45,
			'page'=>$limit/45
			
		));
		$this->set('page',$limit/45);
		$this->set('friends',$friends);
		$this->set('search',false);					
	
	}
	function getFriends(){
		$user = $this->Auth->getUserInfo(); 
		$friend_url = json_decode(file_get_contents('https://graph.facebook.com/me/friends?' . $user['facebook_access_key']));
		return $friend_url->data;
	}


	

	
	
	
	
	
	

	function clean_data($friend,$new_data){
		//var_dump($new_data);
		//var_dump($friend);
		
		
		// we should check if data is the same
		
		if (is_null($friend['Userprofile']['first_name'])) $friend['Userprofile']['first_name'] = (!array_key_exists('first_name',$new_data))? NULL : $new_data['first_name'];
		if (is_null($friend['Userprofile']['last_name'])) $friend['Userprofile']['last_name'] = (!array_key_exists('last_name',$new_data))? NULL : $new_data['last_name'];
		if (is_null($friend['Userprofile']['birthday'])) $friend['Userprofile']['birthday'] = 	(!array_key_exists('birthday',$new_data))? NULL : date('Y-m-d', strtotime($new_data['birthday']));
		if (is_null($friend['Userprofile']['gender'])) $friend['Userprofile']['gender'] = 	(!array_key_exists('gender',$new_data))? NULL : $new_data['gender'];
		if (is_null($friend['Userprofile']['relationship'])) $friend['Userprofile']['relationship'] = 	(!array_key_exists('relationship_status',$new_data))? NULL : $new_data['relationship_status'];
		if (is_null($friend['Userprofile']['religion'])) $friend['Userprofile']['religion'] = 	(!array_key_exists('religtion',$new_data))? NULL : $new_data['religion'];
		if (is_null($friend['Userprofile']['political'])) $friend['Userprofile']['political'] = 	(!array_key_exists('political',$new_data))? NULL : $new_data['political'];
		if (is_null($friend['Userprofile']['hometown'])) $friend['Userprofile']['hometown'] = 	(!array_key_exists('hometown',$new_data))? NULL : $new_data['hometown']['name'];
		if (is_null($friend['Userprofile']['location'])) $friend['Userprofile']['location'] = 	(!array_key_exists('location',$new_data))? NULL : $new_data['location']['name'];
		
		// need to sanitize
		if (is_null($friend['Userprofile']['bio'])) $friend['Userprofile']['bio'] = 	(!array_key_exists('bio',$new_data))? NULL : $new_data['bio'];
		
		
		
		//echo $new_data['bio'];
		
		
		// let's make sure this stuff isn't the same;
		// at some point we should care how often we hit open calais
	
		$work_info = "";
		$work_id_list = "";
		if (array_key_exists('work',$new_data)){
			echo '<pre>';
			var_dump($new_data['work']);
			echo '</pre>';
			foreach($new_data['work'] as $job){
				if (array_key_exists('employer',$job)) {
					$work_info.= $job['employer']['name'].' ';
					$work_id_list .= $job['employer']['id'].',';
				}
				if (array_key_exists('position',$job)) {
					$work_info.= $job['position']['name'].' ';
					$work_id_list .= $job['position']['id'].',';
				}
				if (array_key_exists('description',$job)) $work_info.= $job['description'].' ';
			}
			if (is_null($friend['Userprofile']['work_data'])) {
				$friend['Userprofile']['work_data'] = ($work_info=='') ? NULL : (string)$this->getOpenCalaisThought($this->check_calais_length($work_info));
				$friend['Userprofile']['work_data'] .= ' ****'.$work_id_list;
			}
		}
	
	$education_info="";
		$education_id_list = "";
		if (array_key_exists('education',$new_data)){
			echo '<pre>';
			var_dump($new_data['education']);
			echo '</pre>';
			
			foreach ($new_data['education'] as $school){
				if (array_key_exists('school',$school)) {
					$education_info .= $school['school']['name']. '|' ;
					$education_id_list .= $school['school']['id'].',';
				}
				if (array_key_exists('concentration',$school)){
					foreach($school['concentration'] as $degree){
						$education_info .= $degree['name']. '|';
						$education_id_list .= $degree['id'].',';
					}
				}
			}
			if (is_null($friend['Userprofile']['edu_data'])) {
				$friend['Userprofile']['edu_data'] = ($education_info=='') ? NULL :(string)	$education_info;
				$friend['Userprofile']['edu_data'] .= ' ****'.$education_id_list;
			
			}
			
		}	$friend['Userprofile']['detail']=1;
	
	return $friend;
	
	
	}
	function friend_search(){
		$limit = 45;
		$user = $this->Auth->getUserInfo();
		$this->set('user',$user);
		$name = explode(' ',$this->data['User']['friendsearch']);
		$first_name = (strlen($name[0]) > 3) ? substr($name[0],0,3) : $name[0];
		if (sizeof($name) > 1) $last_name = (strlen($name[1]) > 3) ? substr($name[1],0,3) : $name[1];
		else $last_name = '';
		$first_name = '%'.$first_name.'%';
		$last_name = '%'.$last_name.'%';
		//echo $first_name.' '.$last_name.' '.$this->Auth->getUserId();
		$results = $this->Friend->find('all',array('conditions'=>array(
																'Friend.user_id' => $this->Auth->getUserId(),				
																'AND'=>array(
																			array('Userprofile.first_name LIKE' => $first_name),
																			array('Userprofile.last_name LIKE' => $last_name)
																			)
																),
																'order'=> array(
				'ISNULL(birthday)', 				
				"IF( STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) >= CURDATE( ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH(`birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) +1, '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) )", 
				'birthday'
			),
			'limit' => 45,
			'page'=>$limit/45
																
																));
		if (empty($results)){
			$results = $this->Friend->find('all',array('conditions'=>array(
																'Friend.user_id' => $this->Auth->getUserId(),				
																'AND'=>array(
																			array('Userprofile.last_name LIKE' => $first_name),
																			array('Userprofile.first_name LIKE' => $last_name)
																			)
																),
																'order'=> array(
				'ISNULL(birthday)', 				
				"IF( STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) >= CURDATE( ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) , '/', MONTH(`birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) , STR_TO_DATE( CONCAT( YEAR( CURDATE( ) ) +1, '/', MONTH( `birthday` ) , '/', DAY( `birthday` ) ) , '%Y/%c/%e' ) )", 
				'birthday'
			),
			'limit' => 45,
			'page'=>$limit/45
																
																));
		}
		$this->set('page',$limit/45);
		$this->set('search',true);					
		$this->set('friends',$results);
		$this->render('view_my_friends');														 
	
	
	
	
	
	
	
	
	
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function getOpenCalaisThought($text){


// Your license key (obatined from api.opencalais.com)
$apiKey = "3pa5mdury493c6vtqg2vpfbv";

// Content and input/output formats
$content = $text;
$contentType = "text/txt"; // simple text - try also text/html
$outputFormat = "application/json";//"text/simple"; // simple output format - try also xml/rdf and text/microformats

$restURL = "http://api.opencalais.com/enlighten/rest/";
$paramsXML = "<c:params xmlns:c=\"http://s.opencalais.com/1/pred/\" " . 
			"xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"> " .
			"<c:processingDirectives c:contentType=\"".$contentType."\" " .
			"c:outputFormat=\"".$outputFormat."\" c:enableMetadataType=\"SocialTags\"".
			"></c:processingDirectives> " .
			"<c:userDirectives c:allowDistribution=\"false\" " .
			"c:allowSearch=\"false\" c:externalID=\" \" " .
			"c:submitter=\"Calais REST Sample\"></c:userDirectives> " .
			"<c:externalMetadata><c:Caller>Calais REST Sample</c:Caller>" .
			"</c:externalMetadata></c:params>";
// Construct the POST data string
$data = "licenseID=".urlencode($apiKey);
$data .= "&paramsXML=".urlencode($paramsXML);
$data .= "&content=".urlencode($content); 

// Invoke the Web service via HTTP POST
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $restURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$response = curl_exec($ch);
curl_close($ch);




$json_response = json_decode($response,true);
$description= "";
if (!is_null($json_response)){
foreach ($json_response as $name => $value)

{
	
	
if (array_key_exists('_typeGroup',$value) && $value['_typeGroup']=='topics'){
	if (array_key_exists('score',$value)){
		if ($value['score']>0.5){
			$cats = explode("_",$value['categoryName']);
			for ($i=0;$i<sizeof($cats);$i++){
				$description .= $cats[$i].',';
			}
		}
	}
}
if (array_key_exists('_typeGroup',$value) && $value['_typeGroup']=='entities' && $value['relevance']>0.25){
	if ($value['_type']!='Person' && $value['_type']!='URL' && $value['_type']!='EmailAddress' && $value['_type']!='MedicalCondition'  && $value['_type']!='Currency' && $value['_type']!='MedicalTreatment' && $value['_type']!='PhoneNumber'){
		$description .= $value['name'].",";
	}
}
if (array_key_exists('_typeGroup',$value) && $value['_typeGroup']=='socialTag'){
	$description .= $value['name'].",";
}

}
		$description=trim($description,',');
	
}
return $description;

	}
		function loggedIn(){
			$user = $this->Auth->getUserInfo();
			
			if (!is_null($user)){
				$graph_url = "https://graph.facebook.com/me?" . $user['facebook_access_key'];
				$response = json_decode($this->curl_get_file_contents($graph_url));
				var_dump($response);
				if (isset($response->error)) {
					if ($response->error->type== "OAuthException") {
    					$dialog_url= "https://www.facebook.com/dialog/oauth?client_id=153658484724768&redirect_uri=" . ROOT_URL.'/users/callback/facebook&scope=user_about_me,user_activities,user_birthday,user_education_history,user_events,user_groups,user_hometown,user_interests,user_likes,user_location,user_notes,user_relationships,user_religion_politics,user_status,user_website,user_work_history,email,user_checkins,friends_about_me,friends_likes,friends_interests,friends_checkins,friends_activities,friends_work_history,friends_website,friends_religion_politics,friends_relationships,friends_location,friends_relationship_details,friends_hometown,friends_education_history,friends_birthday,offline_access,friends_status';
						$this->redirect($dialog_url);
					}
   				}	 
  				else {
  					if ($response->id != $user['fb_uid']) $this->logout();
					else return true;
				}
			}
		
	}
		function curl_get_file_contents($URL) {
	    $c = curl_init();
    	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($c, CURLOPT_URL, $URL);
    	$contents = curl_exec($c);
    	$err  = curl_getinfo($c,CURLINFO_HTTP_CODE);
    	curl_close($c);
    	if ($contents) return $contents;
    	else return FALSE;
  	}
	function edit($user_id=null){
		if(!empty($this->data)){
		
		}
		else {
		 $user = $this->Auth->getUserInfo();
		 $profile = $this->Userprofile->find('first',array('conditions'=>array('Userprofile.fb_uid'=>$user['fb_uid'])));
		 $months = array(
							"Jan"=>"Jan",
							"Feb"=>"Feb",
							"Mar"=>"Mar",
							"Apr"=>"Apr",
							"May"=>"May",
							"Jun"=>"Jun",
							"Jul"=>"Jul",
							"Aug"=>"Aug",
							"Sep"=>"Sep",
							"Oct"=>"Oct",
							"Nov"=>"Nov",
							"Dec"=>"Dec"
							);
				$this->set(compact('months'));
					$this->set('dates',range(1,31));
				$this->set('years',range(1900,(int)date('Y')-13));
				$relationship = array(
							"In a relationship"=>"In a relationship",
							"Single"=>"Single",
							"Married"=>"Married"
							);
				$this->set(compact('relationship'));			
		 
		 $this->set(compact('user'));
		 $this->set('profile',$profile['Userprofile']);
		
		}
	
	
	}
	
	function temp_user($fb_user){	
		$this->data=array();
		$this->User->create();
		$this->data['User']['first_name'] = $fb_user->first_name;
		$this->data['User']['last_name'] = $fb_user->last_name;
		$this->data['User']['email'] = (string) $fb_user->email;
		$this->data['User']['new_password']=$this->__randomString();
		$this->data['User']['confirm_password']=$this->data['User']['new_password'];
		$this->data['User']['accept']="1";
		$this->data['User']['sex']=$fb_user->gender;
			//$final_year = (int)date('Y')-$year-13;
		$this->data['User']['birthday']= date('Ymd',strtotime($fb_user->birthday));
		$password = $this->data['User']['password'] = $this->Auth->hasher($this->data['User']['new_password']); 
		$username = $this->data['User']['username']= (string) $fb_user->email;
		$this->data['User']['fb_pic_url']='http://graph.facebook.com/'.$fb_user->id.'/picture';
		$this->data['User']['facebook_access_key'] = $this->Session->read('facebook_access_key');
		$this->data['User']['fb_uid']=$fb_user->id;
		$this->User->set($this->data);
		
		if ($this->User->validates()){
			$fb_uid = $fb_user->id;
			$this->User->save();
			$this->_login($username,$password);
			$friend = $this->Friend->find('first', array('conditions' => array('Friend.fb_uid'=>$fb_user->id)));
			$fb_results = file_get_contents('https://graph.facebook.com/me?'.$this->Session->read('facebook_access_key'));
			$fb_results = ereg_replace('"uid":([0-9]+)', '"uid":"\1"', $fb_results);
			$fb_user = json_decode($fb_results,true);
				//if (!empty($friend)) $this->Search->clean_data($friend,$fb_user);
				//else {
			$this->data = array();
			$this->data['Userprofile']['fb_uid'] = $fb_uid;
			$this->data['Userprofile']['first_name'] = (!array_key_exists('first_name',$fb_user))? NULL : $fb_user['first_name'];
			$this->data['Userprofile']['last_name'] = (!array_key_exists('last_name',$fb_user))? NULL : $fb_user['last_name'];
			$this->data['Userprofile']['birthday'] = 	(!array_key_exists('birthday',$fb_user))? NULL : date('Y-m-d', strtotime($fb_user['birthday']));
			$this->data['Userprofile']['gender'] = 	(!array_key_exists('gender',$fb_user))? NULL : $fb_user['gender'];
			$this->data['Userprofile']['relationship'] = 	(!array_key_exists('relationship_status',$fb_user))? NULL : $fb_user['relationship_status'];
			$this->data['Userprofile']['religion'] = 	(!array_key_exists('religtion',$fb_user))? NULL : $fb_user['religion'];
			$this->data['Userprofile']['political'] = 	(!array_key_exists('political',$fb_user))? NULL : $fb_user['political'];
			$this->data['Userprofile']['hometown'] = 	(!array_key_exists('hometown',$fb_user))? NULL : $fb_user['hometown']['name'];
			$this->data['Userprofile']['location'] = 	(!array_key_exists('location',$fb_user))? NULL : $fb_user['location']['name'];
			$this->data['Userprofile']['pic_url'] = 'https://graph.facebook.com/'.$fb_uid.'/picture';
			$this->data['Userprofile']['bio'] = 	(!array_key_exists('bio',$fb_user))? NULL : $fb_user['bio'];
			$work_info = "";
			$work_id_list = "";
			if (array_key_exists('work',$fb_user)){
				foreach($fb_user['work'] as $job){	
					if (array_key_exists('employer',$job)) {
						$work_info.= $job['employer']['name'].' ';
						$work_id_list .= $job['employer']['id'].',';
					}
					if (array_key_exists('position',$job)) {	
						$work_info.= $job['position']['name'].' ';
						$work_id_list .= $job['position']['id'].',';
					}
					if (array_key_exists('description',$job)) $work_info.= $job['description'].' ';
				}
			}
			$this->data['Userprofile']['work_data'] = ($work_info=='') ? NULL : (string)$this->getOpenCalaisThought($this->check_calais_length($work_info));
			$this->data['Userprofile']['work_data'] .= ($work_id_list=='') ? NULL : (string)' ****'.$work_id_list;
			$education_info="";
			$education_id_list = "";
			if (array_key_exists('education',$fb_user)){
				foreach ($fb_user['education'] as $school){
					if (array_key_exists('school',$school)) {
						$education_info .= $school['school']['name']. '|' ;
						$education_id_list .= $school['school']['id'].',';
					}
					if (array_key_exists('concentration',$school)){
						foreach($school['concentration'] as $degree){
							$education_info .= $degree['name']. '|';
							$education_id_list .= $degree['id'].',';
						}
					}
				}
			}
			$this->data['Userprofile']['edu_data'] = ($education_info=='') ? NULL :(string)	$education_info;
			$this->data['Userprofile']['edu_data'] .= ($education_id_list=='') ? NULL : (string)' ****'.$education_id_list;
			$this->data['Userprofile']['detail']=1;
			$this->Userprofile->save($this->data);
				// we have to make sure friends are not duplicates - we are overwriting them!!!
				
			$this->saveFriends($this->getFriends());
			$this->redirect(array('action'=>'view_my_friends/45'));
				//}
		}
		var_dump( $this->User->validationErrors);
		
	}
	private  function __randomString($minlength = 20, $maxlength = 20, $useupper = true, $usespecial = false, $usenumbers = true){
        $charset = "abcdefghijklmnopqrstuvwxyz";
        if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($usenumbers) $charset .= "0123456789";
        if ($usespecial) $charset .= "~@#$%^*()_+-={}|][";
        if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
        else $length = mt_rand ($minlength, $maxlength);
        $key = '';
        for ($i=0; $i<$length; $i++){
            $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
        }
        return $key;
    }
	
	*/
	


?>