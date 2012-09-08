
<div class="container">
<? echo $this->Session->flash(); ?>
Step 1.
<? echo $this->Form->create('Application'); ?>
    <div class="row">
<?    echo $this->Form->input('name',array('class'=>'span10'));   //text
echo $this->Form->input('link_url',array('class'=>'span10'));  
echo 'you must add http:// to the beginning';
 //password
echo $this->Form->input('description',array('class'=>'span10'));   //day, month, year, hour, minute, meridian
	?>



  	</div>
  	<?php echo $this->Form->end('Next'); ?>
                            
  
</div>