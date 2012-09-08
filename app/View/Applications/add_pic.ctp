
<div class="container">
Step 2
<? 	echo $this->Form->create('Application', array('type' => 'file', 'action'=>'add_pic/'.$id)); ?>
						
                        	<? 	echo $this->Form->file('photo', array('class'=>'text_box','div'=>false)); ?></span>
                    		<?	echo $this->Form->submit('UPLOAD',array('class'=>'btn_smallgreen')); ?></span>
							<?	echo $this->Form->end();
							?>
<div class="btn btn-primary"><? echo $this->Html->link('Skip',array('action'=>'add_pic/0')); ?></div>