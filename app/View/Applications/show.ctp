<div class="container-fluid">
	<div class="row-fluid">
  		<div class="navbar">
			<div class="navbar-inner">
              <div class="pull-right" style="margin-left:0px;width:250px;margin-top:1px;">
               <form class="form-search" action="/applications/search" method="post">
  <input type="text" class="input-medium search-query" name="data[Application][search]">
  <button type="submit" class="btn">Search</button>
</form></div>
       		<br /> 
            <span style="margin-left:10px;">
			  <? if ($type == "pop"): ?>
              Most Popular
              <? else: ?>
              <a href="/applications/show/pop">Most Popular</a>	 
              <? endif; ?>
              | 
              <? if ($type == "new"): ?>
              Newest 
              <? else: ?>
              <a href="/applications/show/new">Newest</a>	 
              <? endif; ?>
              |
              <? if ($type == "cat"): ?>
              Category
              <? else: ?>
              <a href="/applications/show/cat">Category</a>	 
              <? endif; ?>
              |
              <? if ($type == "rate"): ?>
              Top Rated
              <? else: ?>
              <a href="/applications/show/rate">Top Rated</a>	 
              <? endif; ?>
              </span>
        	</div>
    	</div>
  	</div>
    <div class="row-fluid" style="background-color:#FDF6E5; border: 1px solid black">
    	<!--<div class="span8" style="border: 0px solid black;">-->
    		<? echo $this->element('ad', array("app_id" => 2)); ?>
<!--    	</div>
   --> </div>
    <br />
    
  <? foreach($apps as $item){ ?>
    <div class="row-fluid" style="margin-left:5px;">
    <a href='/applications/view/<? echo $item['Application']['id']; ?>'>  
       
       <? if (!empty($item['Application']['image_url'])): ?>
		<div class="span2" style="height:120px;"><? echo $this->Html->image($item['Application']['image_url']); ?></div>
       <? else: ?>
    	<div class="span2"><? echo $this->Html->image('pinterest-logo.png'); ?>	</div>
       <? endif; ?>
    	</a>
        <div class="span4">
			<a href='/applications/view/<? echo $item['Application']['id']; ?>'> <!--.'?site='.urlencode($item['Application']['link_url']); ?>' target="_new" >  -->
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
        <!--<div class="span4" style="height: 10px;"></div>-->
        <div class="span9" style="border: 0px solid black;"><? echo $item['Application']['description']; ?></div>
        <? $master_rating = 0;
            $denominator = 1;
         foreach($item['Rating'] as $rating){ 
            $master_rating += $rating['value'];
            $denominator++;
         } 
         $denominator--;
         $total_points = $master_rating;
         $dec_avg = $total_points/$denominator;
         $whole_avg = round($dec_avg);
         
         
        ?>
        <div class="row-fluid">
        <div class="span8" style="border: 1px; margin-left: 20px;">

			
 

<? $image = (!empty($item['Application']['image_url'])) ? $item['Application']['image_url'] : 'pinterest-logo.png'; ?>

<input type = hidden id = "image<? echo $item['Application']['id']; ?>" value="<? echo $image; ?>" />
<input type = hidden id = "name<? echo $item['Application']['id']; ?>" value = "<? echo $item['Application']['name']; ?>" /> 
 
 
   <p id='msg'></p>
 
            <div id="r<? echo $item['Application']['id']; ?>" class="rate_widget">  
        <? for($i=1; $i<=$whole_avg;$i++){ ?>
            <div class="star_<? echo $i; ?> ratings_vote ratings_stars"></div>  
        <? } ?>
        <? for($i=$whole_avg+1; $i<6;$i++){ ?>
            <div class="star_<? echo $i; ?> ratings_stars"></div>  
        <? } ?>     
        <script>
			var info = {"number_votes"  : <? echo $denominator; ?>,  "dec_avg" : <? echo $dec_avg; ?>,  "whole_avg" : <? echo $whole_avg; ?> } 
            $(r<? echo $item['Application']['id']; ?>).data('fsr', info);
	
        </script>   
        <div class="total_votes"><? echo $denominator; ?> votes recorded (<? echo number_format($dec_avg,2); ?> rating)</div>  
        
               


    	</div>
    </div>  
</div>
   
   
   </div>
   <hr />
   
   <? 
  }
  ?>
  <? if ($page >1): ?>
  <? echo $this->Html->link('Prev',array('action'=>'show',$type,$page-1)); ?>  
  <? endif ;?>
  <? if ($page > 1 && $page*10 < $total_apps): ?> | <? endif; ?>
  <? if ($page*10 < $total_apps) : ?>
  <? echo $this->Html->link('Next',array('action'=>'show',$type,$page+1)); ?>
  <? endif; ?>
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
	postToFeed(clicked_data.widget_id.substring(1), clicked_data.clicked_on.substring(5,6));
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
  
    <script> 
      FB.init({appId: "<? echo CLIENT_ID; ?>", status: true, cookie: true});

      function postToFeed(id, rating) {

		image = document.getElementById('image'+id).value;
		name = document.getElementById('name'+id).value;
	        // calling the API ...
        var obj = {
          method: 'feed',
          link: '<? echo ROOT_URL; ?>/applications/view/'+id+'/',
          picture: '<? echo ROOT_URL; ?>/img/'+image+'',
          name: 'I just rated '+name+' on PinterestApplications.com!',
          caption: 'I gave ' + name + ' '+ rating +' stars!',
          description: 'See what others wrote at pinterestapplications.com.'
        };

        function callback(response) {
			if (response['post_id']!=null){
          		document.getElementById('msg').innerHTML = "Successfully posted to your wall!";
        	}
		}

        FB.ui(obj, callback);
      }
    
    </script>
  </body>