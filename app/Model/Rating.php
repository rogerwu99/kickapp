<?php
class Rating extends AppModel{
		var $name = 'Rating';
		var $actsAs = array('Containable');
		var $belongsTo = array('Application');
}

