<?php
class Tag extends AppModel{
		var $name = 'Tag';
		var $actsAs = array('Containable');
		var $hasAndBelongsToMany = array(
	 						'Application'=>array(
	 							'className'=>'Application',
								'unique'=>false,
							));
		function getAppIDs($catID) {
    		$product = $this->ApplicationsTag->find('all', 
				array(
        		'fields' => array('application_id'),
        		'conditions' => array('tag_id' => $catID),
			));
    		return array_values($product);
		}
		function getCatNums($id){
			$product = $this->ApplicationsTag->find('count',array('conditions'=>array('tag_id'=>$id))); 
	  		return $product;
		}
		function getProds($id){
			$products = $this->ApplicationsTag->find('all',array('conditions'=>array('tag_id'=>$id)));
			return $products;
		}
}

