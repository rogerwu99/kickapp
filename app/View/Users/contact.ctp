
<div class="container-fluid">
	<div class="row-fluid">
    <div class="span12" style="border: 0px solid black;">
	<? echo $this->Form->create('User',array('action'=>'contact')); ?>
	<? if (is_null($user)): ?>
	<? echo $this->Form->input('email',array('class'=>'span12'));  ?>
	<? else: ?>
	<div class="span12" style="margin-left:-1px;">
	From:
	<? echo $this->Html->image('https://graph.facebook.com/'.$user['User']['fb_uid'].'/picture',array('width'=>25,'height'=>25)); ?>
	<? echo $user['User']['username']; ?>
	<input type='hidden' name='data[User][email]' value='<? echo $user['User']['username']; ?>'>
	</div>
	<? endif; ?>
	<? echo $this->Form->input('Subject',array('class'=>'span12'));  ?>
	<? echo $this->Form->input('Message',array('type'=>'textarea','class'=>'span12'));  ?>
	<div class="span2 offset7" style="float:right;margin-right:35px;">
	<? echo $this->Form->end('Send'); ?>
	</div>
    </div>
    </div>
</div>