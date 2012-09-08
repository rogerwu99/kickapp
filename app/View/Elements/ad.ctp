<!--    <div class="row" style="background-color:#FDF6E5; border: 1px solid black">
    -->
       	<div style="margin-left: 20px;margin-right:20px;">
         	<span style="font-size:14px;">
            <i class="icon-eye-open"></i> Promoted Application
            </span>
            <span class="pull-right"><a href="/users/contact">Want to promote your app?<i class="icon-info-sign"></i></a>
            </span>
      		<br /><br />
       		<? $item = $this->requestAction('/applications/get_app/'.$app_id); ?>
       		<a href='/applications/click/<? echo  $item['Application']['id'].'?site='.urlencode($item['Application']['link_url']); ?>' target="_new" > 
				<? if (!empty($item['Application']['image_url'])): ?>
                <div class="span2" style="height:100px;"><? echo $this->Html->image($item['Application']['image_url']); ?></div>
                <? else: ?>
                <div class="span2"><? echo $this->Html->image('pinterest-logo.png'); ?>	</div>
                <? endif; ?>
	       	</a>
    		<div class="span4">
				<a href='/applications/click/<? echo $item['Application']['id'].'?site='.urlencode($item['Application']['link_url']); ?>' target="_new" >  
        			<? echo $item['Application']['name']; ?>
        		</a>
    		</div>
        	<div class="span5">
				 <? $counter = 0; ?>
                 <? foreach($item['Tag'] as $tag){ ?>
                 <? if ($counter % 2 == 0){ ?>
                      <span class="label label-important"><? echo $tag['name']; ?></span>
                 <? } else { ?>
                      <span class="label label-info"><? echo $tag['name']; ?></span>
                 <? } ?>
                 <? $counter++; ?>
                 <? } ?>
			</div>
		    <div class="span9" style="border: 0px solid black;"><? echo $item['Application']['description']; ?></div>
        	<br />   
      	</div>