
<div class="container-fluid">
	<div class="row-fluid">
  		<div class="navbar">
			<div class="navbar-inner">
       		<br /> 
				<? echo $app['Application']['name']; ?>       
        	</div>
    	</div>
  	</div>
    <br />
    
    <div class="row-fluid">
    	<a href='/applications/click/<? echo $app['Application']['id'].'?site='.urlencode($app['Application']['link_url']); ?>' target="_new" >  
       <? if (!empty($app['Application']['image_url'])): ?>
		<div class="span2" style="height:120px;"><? echo $this->Html->image($app['Application']['image_url']); ?></div>
       <? else: ?>
    	<div class="span2"><? echo $this->Html->image('pinterest-logo.png'); ?>	</div>
       <? endif; ?>
       </a>
    	<div class="span3">
			<a href='/applications/click/<? echo $app['Application']['id'].'?site='.urlencode($app['Application']['link_url']); ?>' target="_new" >  
        		<? echo $app['Application']['name']; ?>
        	</a>
    	</div>
	    <div class="span5">
	   <? $counter = 0; ?>
	   <? foreach($app['Tag'] as $tag){ ?>
       <? if ($counter % 2 == 0){ ?>
	 		<span class="label label-important"><? echo $tag['name']; ?></span>
   	   <? } else { ?>
            <span class="label label-info"><? echo $tag['name']; ?></span>
	   <? } ?>
 	   <? $counter++; ?>
   	   <? } ?>
		</div>
        <div class="span8" style="border: 0px solid black;"><? echo $app['Application']['description']; ?></div>
        <? $master_rating = 0;
            $denominator = 1;
         foreach($app['Rating'] as $rating){ 
            $master_rating += $rating['value'];
            $denominator++;
         } 
         $denominator--;
         $total_points = $master_rating;
         $dec_avg = $total_points/$denominator;
         $whole_avg = round($dec_avg);
         
         
        ?>
        <div class="row">
        <div class="span8" style="border: 1px;">
            <div id="r<? echo $app['Application']['id']; ?>" class="rate_widget">  
        <? for($i=1; $i<=$whole_avg;$i++){ ?>
            <div class="star_<? echo $i; ?> ratings_vote ratings_stars"></div>  
        <? } ?>
        <? for($i=$whole_avg+1; $i<6;$i++){ ?>
            <div class="star_<? echo $i; ?> ratings_stars"></div>  
        <? } ?>     
        <script>
			var info = {"number_votes"  : <? echo $denominator; ?>,  "dec_avg" : <? echo $dec_avg; ?>,  "whole_avg" : <? echo $whole_avg; ?> } 
            $(r<? echo $app['Application']['id']; ?>).data('fsr', info);
			 
        </script>   
        <div class="total_votes"><? echo $denominator; ?> votes recorded (<? echo $dec_avg; ?> rating)</div>  
    	</div>
    </div>  
</div>
<br />
	<div class="row-fluid">
    <div class="span3"></div>
   	<div class="span8">
   	<div class="fb-comments" data-href="<? echo ROOT_URL; ?>/applications/view/<? echo $app['Application']['id']; ?>" data-num-posts="5" data-width="600"></div>
   </div>
   </div>
   </div>
   
</div>
  <script>
   $('.ratings_stars').hover(  
    // Handles the mouseover  
    function() {  
        $(this).prevAll().andSelf().addClass('ratings_over');  
        $(this).nextAll().removeClass('ratings_vote');   
    },  
    // Handles the mouseout  
    function() {  
        $(this).prevAll().andSelf().removeClass('ratings_over');  
      // 	alert($(this).parent().data('fsr').whole_avg);
	    set_votes($(this).parent());  
    }  
);
$('.ratings_stars').bind('click', function() {  
    <? if (!is_null($user)): ?>
	var star = this;  
    var widget = $(this).parent();  
      
    var clicked_data = {  
        clicked_on : $(star).attr('class'),  
        widget_id : widget.attr('id')  
    };  
    $.post(  
        '<? echo ROOT_URL; ?>/applications/ratings',  
        clicked_data,  
        function(INFO) { 
	//	alert('return'); 
            widget.data( 'fsr', INFO );  
            set_votes(widget);  
        },  
        'json'  
    ); 
	<? else : ?>
	alert("you must be logged in to rate");
	<? endif; ?>  
});     

function set_votes(widget) {  
	
	
    
	var avg = $(widget).data('fsr').whole_avg;  
    var votes = $(widget).data('fsr').number_votes;  
    var exact = $(widget).data('fsr').dec_avg;  
//alert ("value"+$(widget).data('fsr').whole_avg);
      
    $(widget).find('.star_' + avg).prevAll().andSelf().addClass('ratings_vote');  
    $(widget).find('.star_' + avg).nextAll().removeClass('ratings_vote');   
    $(widget).find('.total_votes').text( votes + ' votes recorded (' + exact + ' rating)' );  
}  

</script>
 