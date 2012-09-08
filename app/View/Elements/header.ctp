<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container-fluid">
    	<a class="brand" href="/applications/show">
    		<? //echo $this->Html->image('logo_header.png',array('height'=>40,'alt'=>'Pinterest Applications')); ?><br /> 
	        
		</a>
   		<div class="pull-right">
        	<? if ($user): ?>
            	GET READY FOR A BALL KICKING! | <a href="/users/logout">Logout</a>
                
	        <? else: ?>
              <div style="margin-top: 10px;"><a href="/users/fb_login"><? echo $this->Html->image('fbconnect.png', array('alt' => 'Login with Facebook'))?></a></div>
           	<? endif; ?>
            </ul>
		</div>
	</div>
  </div>
</div>