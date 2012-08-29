/**
 * @author: CodeFlavors [www.codeflavors.com]
 * @version: 1.0
 * @framework: jQuery
 */

(function($){
	
	$(document).ready(function(){
		var menu = $('#cfn_floating_menu').find('ul').first(),
			items = menu.children('li'),
			options = $.parseJSON(CFM_MENU_PARAMS);
		
		$('#cfn_floating_menu').css({'top':options.top_distance});
		
		if( 1 == options.animate ){
			$(window).scroll(function(e){
				var st = $(window).scrollTop();
				if( st > options.top_distance + 20 ){
					$('#cfn_floating_menu').animate({'top':st+options.top_distance},{'queue':false, 'duration':500});
				}else{
					$('#cfn_floating_menu').animate({'top':options.top_distance},{'queue':false, 'duration':500});	
				}
			});		
		}
		
		// show submenus
		$(menu).find('li').mouseenter(function(){
			$(this).children('ul').show(100);			
		}).mouseleave(function(){
			$(this).children('ul').hide(200);
		});
		
		// highlight current item from menu
		$(menu).find('li.current-menu-item').children('a').addClass('currentItem');
		
		// if first item is the trigger, show the menu only when hovering that item
		if( $(items[0]).attr('id') == 'cfm_menu_title_li' ){			
			var main = items.splice(0,1);
			$(main).find('a').click(function(e){
				e.preventDefault();
			})
			
			$(items).hide();

			$(menu).mouseenter(function(){
				$(items).show(100);
			}).mouseleave(function(){
				$(items).hide(200);				
			})			
		}
		
	})	
	
})(jQuery);