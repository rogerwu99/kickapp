<?php
class Application extends AppModel{
		var $name = 'Application';
		var $actsAs = array('Containable');
		var $hasMany = array('Rating','Click');
		
		var $hasAndBelongsToMany = array(
	 						'Tag'=>array(
	 							'className'=>'Tag',
								'unique'=>false,
							));
	
							
			 
}

