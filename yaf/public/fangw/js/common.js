$(document).ready(function(){ 


 

		jQuery(".banner").slide({mainCell:".bd ul",effect:"leftLoop",autoPlay:true,vis:1,scroll:1,titCell:".hd ul",autoPage:"<li></li>",interTime:5000});


		jQuery(".i_35").slide({mainCell:".bd ul",effect:"leftLoop",autoPlay:true,vis:1,scroll:1,titCell:".hd ul",autoPage:"<li></li>",interTime:5000});


		


		jQuery(".main-2-2").slide({mainCell:".bd ul",effect:"topLoop",autoPlay:true,vis:1,scroll:1,titCell:".hd ul",autoPage:"true",interTime:5000});




/*





	$(".main-1").hover(function(){
		var flag=$(this).attr("flag");
		$(this).find(".main-1-1").css("background-image",'url(./images/aa'+flag+".png)");

		$(this).find(".main-1-1").addClass("act");
		
	
	},function(){
		var flag=$(this).attr("flag");
		$(this).find(".main-1-1").css("background-image",'url(./images/a'+flag+".png)");
		$(this).find(".main-1-1").removeClass("act");
	});


	$(".main-2-1").hover(function(){
		//$(this).parent().addClass("act-1");
		$(this).find(".main-2-1-1-1").addClass("act");
		

	},function(){
		//$(this).parent().removeClass("act-1");
		$(this).find(".main-2-1-1-1").removeClass("act");
	});

	


	jQuery(".main-2").slide({mainCell:".bd ul",effect:"leftLoop",autoPlay:true,vis:4,scroll:4,titCell:".hd li",autoPage:"true",interTime:5000});


	FindPh(".main-3 img",4);

	

	FindPh(".frends-1",4);


*/




 








		
	
});
	

function clearWz(obj,flag){
	
	

	$(obj+" input").keydown(function(){
	
			$(this).next("span").hide(0);
	});

	$(obj+" span").click(function(){
	
		$(this).prev("input").focus();
	})

    $(obj+" input").blur(function(){
	
		if($(this).val()=="")
			$(this).next("span").show(0);
	});


    if( flag==true){
	
		$(obj+" textarea").keydown(function(){
		
				$(this).next("span").hide(0);
		});

		$(obj+" textarea").click(function(){
		
			$(this).prev("input").focus();
		})

		$(obj+" textarea").blur(function(){
		
			if($(this).val()=="")
				$(this).next("span").show(0);
		});
	}

}









 function about(){
		$(".menbang").show(0);
		$(".about").show(0);
	 }




	 function cplist(){
		$(".cplist").show(0);
	   $(".menbang").show(0);
	 }


	 function contact(){
		$(".contact").show(0);
	   $(".menbang").show(0);
	 }


	 function fanfa(){
		$(".fanfa").show(0);
	   $(".menbang").show(0);
	 }


	 
	 function newslist(){
		$(".newslist").show(0);
	   $(".menbang").show(0);
	 }



	 function newsDetail(){
		 $(".newsDetail").show(0);
		$(".menbang").show(0);
	 
	 }

	 function cpDetail(){
		$(".cpDetail").show(0);
		$(".menbang").show(0);
		$("body").height(($(".cparccontent").height()+600)+"px");
	 }



	 function message(){
		$(".message").show(0);
		$(".menbang").show(0);
	  
	 }






function tp(obj){
	$(obj).each(function(){
		$(this).click(function(){
			$(obj).each(function(){
				$(this).removeClass('act');
			});
			$(this).addClass('act');
		});
	});
}


var timer=null;
function uhide(obj){
	clearTimeout(timer);
	timer=setTimeout(function(){
		$(obj).css('display','none');
	
	},200)
}

function ushow(obj){
	clearTimeout(timer);
	$(obj).css('display','block');

}



function put(obj){
	$(obj).focus(function(){
		$(this).text("");
	});
}






 function FindPh(obj,num){
		
        var total=$(obj).length;
		
        var k=0;
        var arr=new Array();
        for(var i =1;i<=total;i++){

            if(i%num==0){
                arr[k]=i-1;
                k+=1;
            }
        }
        

       for(var i=0 ;i<arr.length;i++){
            $(obj).eq(arr[i]).css("margin-right","0px");
            $(obj).eq(arr[i]).css("float","right");
       }




        $(obj).show(0);

            
    }




