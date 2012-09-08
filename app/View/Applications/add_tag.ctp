
<div class="container">
<? echo $this->Session->flash(); ?>
Step 2
<? echo $this->Form->create('Application',array('action'=>'add_tag/'.$id));  ?>
<? echo $this->Form->input('Tag.id', array(
    'type' => 'select', 
    'multiple' => 'checkbox',
    'options' => $all_tags
	
	
	
			/*array(
            '1' => 'Analytics',
            '2' => 'Shopping',
			'3' => 'Photos',
            '4' => 'Mobile',
			'5' => 'Fashion',
            '6' => 'Home',
			 '7' => 'Food',
            '8' => 'Crafts',
			 '9' => 'Humor',
            '10' => 'Games',
			'11'=>'Wedding',
			'12'=>'Beauty',
			'13'=>'Travel',
			'14'=>'Utility (app)',
			'15'=>'Utility (browser)'
			
    )*/
));?>
<? echo $this->Form->hidden('id',array('value'=>$id));
	 echo $this->Form->end('Done'); ?>


