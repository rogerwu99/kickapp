<?php
class Click extends AppModel{
		var $name = 'Click';
		var $actsAs = array('Containable');
		var $belongsTo = array('Application');
	//	var $recursive = 2;
}

