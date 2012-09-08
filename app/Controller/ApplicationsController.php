<?php
class ApplicationsController extends AppController {

	var $name = 'Applications';
	var $helpers = array('Html', 'Form', 'Js'=>array('Jquery'));
	var $uses = array('Application','Click','Category','User','Rating','Tag');
	var $components = array('RequestHandler','Auth','Session');
	
	function index(){
		$this->redirect(array('action'=>'show'));
	}
	function get_app($id=null){
		return ($this->Application->findById($id));
	}
	function search(){
		$apps = $this->Application->find('all',array('conditions'=>array("Application.name LIKE" => "%".$this->request->data['Application']['search']."%")));
		$this->set('type','search');
		$this->set('apps',$apps);
		$this->render();
	}
	function show($type='pop',$page=1){
		$total_apps = $this->Application->find('count');
		$user = $this->Session->read('user');
		if ($type == 'pop'){
			$apps = $this->Click->find('all', array('limit'=>10,'page'=>$page,'group'=>array('Click.application_id'), 'recursive'=>2,'order'=> array('count(Click.id) DESC')));
			for($i=0;$i<sizeof($apps);$i++){
				$apps[$i]['Rating'] = $apps[$i]['Application']['Rating'];
				$apps[$i]['Tag'] = $apps[$i]['Application']['Tag'];
			}
		}
		elseif ($type == 'new'){
			$apps = $this->Application->find('all',array('limit'=>10,'page'=>$page,'conditions'=>array('approved'=>1),'order' => array('Application.modified DESC')));
		}
		elseif ($type == 'rate'){
			$ratings = $this->Rating->find('all',array('limit'=>10,'page'=>$page,'fields'=>array('count(Rating.user_id)','Application.id','sum(Rating.value)'),'group'=>array('Rating.application_id'),'order'=>array('sum(Rating.value)/count(Rating.user_id) DESC')));
			$app_ids = array();
			$app_string = '';
			foreach($ratings as $rating){
				if (!empty($rating['Application']['id'])){
					array_push($app_ids,$rating['Application']['id']);
					$app_string .= $rating['Application']['id'].',';
				}
			}
			$app_string=substr($app_string,0,-1);
			$apps = $this->Application->find('all',array('limit'=>10,'conditions'=>array('id'=>$app_ids), 'order'=>array('FIELD(Application.id, '.$app_string.')')));
		}
		else {
			$this->redirect(array('action'=>'category')); 
		}
		$this->set('user',$user);
		$this->set('type',$type);
		$this->set('apps',$apps);
		$this->set('page',$page);
		$this->set('total_apps',$total_apps);
	}
	function view($id=null){
		if (is_null($id)){
			$this->redirect(array('action'=>'show'));
		}
		else{
			$app = $this->Application->findById($id);
			$this->set('title_for_layout',$app['Application']['name']);
			$this->set('app',$app);
		}
	}
	function category($id=null,$page=1){
		if ($id==null){
			$cat = array();
			$num_tags = $this->Tag->find('count');
			for($i=1;$i<$num_tags;$i++){
				$cat[$i] = $this->Tag->find('first',array('conditions'=>array('id'=>$i)));
				$cat[$i]['count'] = $this->Tag->getCatNums($i);
			}
			$this->set('cat',$cat);
		}
		else {
			$total_apps = $this->Tag->getCatNums($id);
			$user = $this->Session->read('user');
			$app_ids = $this->Tag->getProds($id);
			$app_id = array();
			foreach($app_ids as $app){
				array_push($app_id,$app['ApplicationsTag']['application_id']); 
			}
			$apps = $this->Application->find('all',array('conditions'=>array('id'=>$app_id)));
			$this->set('user',$user);
			$this->set('type','cat');
			$this->set('apps',$apps);
			$this->set('page',$page);
			$this->set('total_apps',$total_apps);
	
		}
		$this->set('id',$id);
		$this->render();
	}
	function ratings(){
		$this->layout='ajax';
		$this->autoRender = false;
		$user = $this->Session->read('user');
		if (array_key_exists('fetch',$this->request->data)){
			$data['widget_id'] = $this->request->data['widget_id'];  
			$data['number_votes'] = 0;  
			$data['total_points'] = 0;  
			$data['dec_avg'] = 0;  
			$data['whole_avg'] = 0;  
			echo json_encode($data);  
		}
    	else {  
			$app_id = substr($this->request->data['widget_id'],1);
			preg_match('/star_([1-5]{1})/',$this->request->data['clicked_on'], $match); 
			$vote = $match[1];   
			$rating = $this->Rating->find('first',array('conditions'=>array('AND'=>array(
															array('application_id'=>$app_id),
															array('user_id'=>$user['User']['id'])
															))));
			if (empty($rating)){
				$this->Rating->create();
				$this->Rating->set(array(
					'application_id'=>$app_id,
					'user_id'=>$user['User']['id'],
					'value'=>$vote
					));
				$this->Rating->save();
			}
			else {
				$this->Rating->read(null,$rating['Rating']['id']);
				$this->Rating->set('value',$vote);
				$this->Rating->save();
			}
			$all_ratings = $this->Rating->find('all',array('conditions'=>array('application_id'=>$app_id)));
			$master_rating = 0;
			$denominator = 1;
			foreach($all_ratings as $rating){
				$master_rating += $rating['Rating']['value'];
				$denominator++;
	 		} 
     		$denominator--;
		 	$total_points = $master_rating;
	 		$dec_avg = $total_points/$denominator;
	 		$whole_avg = round($dec_avg);
			
			$data['widget_id'] = $this->request->data['widget_id'];  
       	 	$data['number_votes'] = $denominator;  
        	$data['total_points'] = $total_points;  
        	$data['dec_avg'] = $dec_avg;  
        	$data['whole_avg'] = $whole_avg;  
        	echo json_encode($data);  
			
		}
	}
	private function is_admin(){
		$admin = array('1');
		$user = $this->Session->read('user');
		$is_admin = false;
		if (in_array($user['User']['id'],$admin)){
			return true;
		}
		else {
			return false;
		}
	}	
	function add(){
		if (!empty($this->request->data)){
			$this->request->data['Application']['approved']=0;
			$user = $this->Session->read('user');
			$this->Application->create();
			$this->Application->save($this->request->data);
			$id = $this->Application->id;
			
			$this->Rating->create();
			$this->Rating->set(array(	'application_id'=>$id,
										'user_id'=>$user['User']['id'],
										'value'=>3
									));
			$this->Rating->save();						 
			$this->redirect(array('action'=>'add_tag/'.$id));
	
		}
	}
	function add_tag($id=null){
		if (!empty($this->request->data)){
			$temp_array = $this->request->data['Tag']['id'];
			$save_array = array();
			for($i=0;$i<sizeof($temp_array);$i++){
				$save_array[$i]['Tag']['id']=$temp_array[$i];
				$save_array[$i]['Application']['id']=$this->request->data['Application']['id'];
			}
			if ($this->Application->saveAll($save_array)){
				$this->redirect(array('action'=>'add_pic/'.$id));
			}
		}
		else {
			$tags = $this->Tag->find('all', array('fields'=>array('Tag.id','Tag.name')));
			$all_tags = array();
			foreach($tags as $tag){
				$all_tags[$tag['Tag']['id']] = $tag['Tag']['name'];
			}
			$this->set('all_tags',$all_tags);
			$this->set('id',$id);
			$this->render();
		}
	}
	function add_pic($id=null){
		if ($id == 0){
			$this->Session->setFlash("Application is being reviewed!");
			$this->redirect(array('action'=>'show'));	
		}
		if (!empty($this->data)) {
			
			App::import('Vendor', 'upload');
	    	$typelist=split('/', $_FILES['data']['type']['Application']['photo']);
			$allowed[0]='xxx';
            $allowed[1]='gif';
            $allowed[2]='jpg';
            $allowed[3]='jpeg';
            $allowed[4]='png';
        	$allowed_val='';
            $allowed_val=array_search($typelist[1], $allowed);
			if (!$allowed_val){
				$this->Session->setFlash('<span class="bodycopy" style="color:red;">Profile picture must be gif, jpg or png only.</span>');
			}
		   	else if(!empty($this->data) && $this->data['Application']['photo']['size']>0){
	    		$file = $this->data['Application']['photo']; 
	            $handle = new Upload($file);
		        if ($handle->uploaded){
					if($handle->image_src_x >= 100){
						$handle->image_resize = true;
		    			$handle->image_ratio_y = true;
		    			$handle->image_x = 100;
		    			if($handle->image_y >= 100){
		    				$handle->image_resize = true;
			    			$handle->image_ratio_x = true;
			    			$handle->image_y = 100;
		    			}
					}
	    			$handle->Process('img/uploads');
				}
	            if(!is_null($handle->file_dst_name) && $handle->file_dst_name!=''){
					$user_path = $handle->file_dst_name;
				}
    			$this->Application->read(NULL, $id);
				$this->Application->set('image_url','uploads/'.$user_path);
				$this->Application->save();
				$handle->clean();
	           }
			   $this->Session->setFlash("Application is being reviewed!");
			
            $this->redirect(array('action'=>'show'));
	        exit;
	    }
		else {
			$this->set('id',$id);
			$this->render();
		}
	}
	
	function approve(){
		if (!$this->is_admin()){
			Controller::render('/deny');
        }
		if (!empty($this->request->data)){
			$this->Application->updateAll(
				array('Application.approved'=>1),
				array('Application.id'=>$this->request->data['Application']['id'])
				);
			foreach($this->request->data['Application']['id'] as $app_id){
				$this->Click->create();
				$this->Click->set(array(
							'application_id'=>$app_id,
							'user_id'=>1
							));
				$this->Click->save();
				
			}
			$this->redirect(array('action'=>'show'));
		}
		else {
			$apps = $this->Application->find('all',array('conditions'=>array('approved'=>0)));
			$this->set('apps',$apps);
			$this->render();
		}
	}
	function click($id = null){
		$user = $this->Session->read('user');
		$url = $this->params['url']['site']; 
		$data=array();
		$data['Click']['application_id']=$id;
		$data['Click']['user_id']=$user['User']['id'];
		
		
		$this->Click->create();
		$this->Click->save($data);
		
		$this->redirect($url);
	}

}

?>