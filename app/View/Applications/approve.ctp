
<div class="container">
	<div class="row">
  	<div class="navbar">
		<div class="navbar-inner">
       <br /> 
         
        </div>
    </div>
  </div>
  <? echo $this->Form->create('Application',array('action'=>'approve')); ?>
  <?
  //var_dump($apps); 
  foreach($apps as $item){
  	?>
    <div class="row">
    <? //var_dump($item); ?>
    <? if (!empty($item['Application']['image_url'])): ?>
	<div class="span2"><? echo $this->Html->image($item['Application']['image_url']); ?></div>
    <? else: ?>
    <div class="span2">No picture</div>
    
    <? endif; ?>
    <div class="span2">
		<a href='/applications/click/<? echo $item['Application']['id'].'?site='.urlencode($item['Application']['link_url']); ?>' target="_new" >  
        <? echo $item['Application']['name']; ?>
        </a>
    </div>
    <div class="span8"><? echo $item['Application']['description']; ?></div>
    <? $master_rating = 0;
		$denominator = 1;
	 foreach($item['Rating'] as $rating){ 
    	$master_rating += $rating['value'];
	 } ?>
     
   <!--  // this will be ajax link
   --> <div class="span8"><? echo $master_rating/$denominator; ?><? //echo $this->Html->link('Rate',array('action'=>'rate')); ?></div>
    <? foreach($item['Tag'] as $tag){ ?>
	    <div class="span8"><? echo $tag['name']; ?></div>
    <? } ?>
   
 Approved?
   <input name="data[Application][id][]" value="<? echo $item['Application']['id']; ?>" id="ApplicationApproved" type="checkbox">
   
 <? //  echo $this->Form->checkbox('Approved', array('value'=>$item['Application']['id'], 'name'=>'approved_'.$item['Application']['id'])); ?>

   </div>
   
   
   <? 
  }
  ?>
  <?	echo $this->Form->submit('approve',array('class'=>'btn_smallgreen')); ?></span>
							<?	echo $this->Form->end();
							?>
  
</div>