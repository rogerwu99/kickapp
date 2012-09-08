<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container-fluid">
    	<a class="brand" href="/applications/show">
    		<? echo $this->Html->image('logo_header.png',array('height'=>40,'alt'=>'Pinterest Applications')); ?><br /> 
	        
		</a>
   		<div class="pull-right">
	        <? $user = $this->Session->read('user'); ?>
            <? if ($user): ?>
        	<ul class="nav">
              <? if($this->params['action']=='show'): ?>
              <li class="active" style="margin: 0 0;">
                <a href="#">Home</a>
              <? else: ?>
              <li  style="margin: 0 0;">
                <a href="/applications/show">Home</a>
              
              <? endif; ?>  
              </li>
              <? if($this->params['action']=='add'): ?>
              <li class="active"  style="margin: 0 0;">
                <a href="#">Add</a>
              <? else: ?>
              <li  style="margin: 0 0;">
                <a href="/applications/add">Add</a>
              <? endif; ?>
              </li>
              <? if($this->params['action']=='contact'): ?>
              <li class="active" style="margin: 0 0;">
                <a href="#">Contact</a>
              <? else: ?>
              <li  style="margin: 0 0;">
                <a href="/users/contact">Contact</a>
              <? endif; ?>
              </li>
              <li><a href="/users/logout" rel="tooltip" title="Click to Logout"><? echo $this->Html->image('https://graph.facebook.com/'.$user['User']['fb_uid'].'/picture', array('alt' => $user['User']['name'],'height'=>35,'width'=>35))?></a></li>
              <? else: ?>
              <li style="margin-top: 10px;"><a href="/users/fb_login"><? echo $this->Html->image('fbconnect.png', array('alt' => 'Login with Facebook'))?></a></li>
              <? endif; ?>
           	</ul>
		</div>
	</div>
  </div>
</div>