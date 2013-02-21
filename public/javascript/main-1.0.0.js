$(document).ready(function() {
	
	// global vars
	var urlArray = document.URL.split('/');
	var username = urlArray[urlArray.length-1];		
	
	
	// make the flash div disappear after 5 secs
	setTimeout(function() {
		$('#flash').fadeOut();
	}, 5000);

	// confirm the summoner deletion
	$('.sumDelete').click(function() {
		var sure = confirm("Are you sure that you want to delete?");
		if (sure) {
			return true;
		} else {
			return false;
		}

	});
	
	// post add menu trigger
	$('#add-menu').click(function(){
		var postMenu = $('#post-add-menu');
		if(postMenu.is(':visible')){
			$('#post-add-menu').fadeOut();
			$(this).html('Add a new post (+)');
		}else{
			$('#post-add-menu').fadeIn();
			$(this).html('Close the menu (-)');
		}
	});
	
	// tab-related code
	var previous = "tabs-1 tab-active";
	$('span[class^="tabs-"]').click(function(){
		var tab = $(this).attr('class');
		if(previous != tab){
			$('#'+previous).hide();
			$('.'+previous).removeClass('tab-active');
			
			$('#'+tab).show();
			$('.'+tab).addClass('tab-active');
			
			previous = tab;
		}
	});
	
	// submit post
	$("#post-add-form").submit(function(){
		event.preventDefault();
		var form = $(this);
		var content = form.find('input[name="status"]');
		if(!empty(content)){
			$.post('./post/add/format/json',{targetName:username,status:content.val()},function(data){
				if(data.success == 1){
					loadPosts(username);
					content.val('');
				}else{
					addMessage(data.message);
				}
				$("#errors").hide();
			});
		}else{
			showErrors('The status box is empty!','#post-add-menu');
		}
	});
	
	function loadPostTime(){
		$('.post').each(function(){
			var post = $(this);
			var postId = post.attr('data-post-id');
			$.get('./post/get-time/postId/'+postId+'/format/json',function(data){
				if(data.success == 1){
					var timeDiv = post.find('.post-time');		
					timeDiv.html('');
					timeDiv.append(data.time);
				}else{
					addMessage(data.message);
				}
			});
		});
	}
	
	function loadCommentTime(comment){
		$('.comment').each(function(){
			var comment = $(this);
			var commentId = comment.attr('id').slice(8);
			$.get('./comment/get-time/comId/'+commentId+'/format/json',function(data){
				console.log('edw');
				if(data.success == 1){
					var timeDiv = comment.find('.comment-time');	
					timeDiv.html('');
					timeDiv.append(data.time);
				}else{
					addMessage(data.message);
				}
			});
		});
	}	
	
	function loadRatingBar(postId){
		$.get('./post/get-rating/postId/' +postId +'/format/json',function(data){
			if(data.success == 1){
				var likeWidth = data.likeWidth;
				var dislikeWidth = data.dislikeWidth;
				output = '<span class="like-bar" style="width:' + likeWidth + '%;"></span><span class="dislike-bar" style="width:'+ dislikeWidth + '%;"></span>';
				
				var postDiv = 'div[data-post-id="'+postId+'"]';
				var postRating = $(postDiv).find('.post-rating');
				postRating.html('');
				postRating.append(output);
			}else{
				addMessage(data.message);
			}
		});
	}
	
	function empty(variable){
		if(variable != null && variable != "" && variable != 'undefined'){
			return false;
		}
		return true;
	}
	
	function showErrors(errors,section){
		var errorList = '<ul id="errors">There were some errors';
		if(errors instanceof Array){
			for(error in errors){
				errorList += "<li>"+error+"</li>";
			}
		}else{
			errorList += "<li>"+errors+"</li>";
		}
		errorList += "</ul>";
		$(section).append(errorList);
	}
	
	function addMessage(message){
		var flashDiv = '<div id="flash">'+message+'</div>';
		if($('#flash').is(":visible")){
			$('#flash').detach();
		}
		$('#main').before(flashDiv);
		setTimeout(function() {
			$('#flash').detach();
		}, 5000);		
	}
	
	function loadComments(postId,section){
		$(section).html("");
		$(section).load('./comment/list/postId/'+postId+'/format/html',function(){
			
			// show the delete content span
			$('.comment').hover(function(){
				$(this).find('.comment-remove').show();
			}, function(){
				$(this).find('.comment-remove').hide();
			});
			
			// delete post action
			$('.comment-remove').click(function(){
				var conf = confirm('Are you sure you want to delete this comment? (This action can\'t be undone)');
				var com = $(this).parent();
				var comId = com.attr('id').slice(8);
				var cauthor = com.find('.content-author .author').html();
				console.log('id: ' + comId + ' author: ' + cauthor);
				if(conf){
					// we pass the postId in order to find the post owner, he has the rights to delete all the comments
					$.post('./comment/remove/format/json',{id:comId,author:cauthor,postId:postId},function(data){
						if(data.success == 1){
							loadComments(postId,section);
						}else{
							addMessage(data.message);
						}
					});
				}
			});	
			
			setInterval(function(){
				loadCommentTime();
			}, 5000);			
		});
	}
	
	function loadPosts(username){
		$('#post-list').load('./post/list/user/' + username + '/format/html',function(){

			/* ----------- post related ------------ */
			
			// show the delete content span
			$('.post').hover(function(){
				$(this).find('.post-remove').show();
			}, function(){
				$(this).find('.post-remove').hide();
			});
			
			// delete post action
			$('.post-remove').click(function(){
				var conf = confirm('Are you sure you want to delete this post? (This action can\'t be undone)');
				if(conf){
					var post = $(this).parent();
					var cpostId = post.attr('data-post-id');
					var cauthor = post.find('.content-author .author').html();
					$.post('./post/remove/format/json',{postId:cpostId,author:cauthor},function(data){
						if(data.success == 1){
							loadPosts(username);
						}else{
							addMessage(data.message);
						}
					});
				}
			});
			
			
			/* ---------- comment related ---------- */
			
			// trigger for the comment loading
			$('.show-all-comments').click(function(){
				var commentList = $(this).parent().find('.comment-list');
				var postId = $(this).parent().parent().attr('data-post-id');
				// if the list is empty, load the comments
				if($.trim(commentList.html()) == ""){
					loadComments(postId,commentList);					
				}
				if(commentList.is(':visible')){
					commentList.fadeOut();
					$(this).html('Show all comments');
				}else{
					commentList.fadeIn();
					$(this).html('Hide all comments');
				}
			});

			// submit comment
			$(".add-comment-form").submit(function(){
				event.preventDefault();
				var form = $(this);
				var ccontent = form.find('input[name="status"]');
				var cpostId = form.parent().parent().parent().attr('data-post-id');
				var commentList = form.parent().parent().find('ul.comment-list');
				if(!empty(content)){
					$.post('./comment/add/format/json',{name:username,content:ccontent.val(),postId:cpostId},function(data){
						if(data.success == 1){
							loadComments(cpostId,commentList);
							ccontent.val('');
							$(commentList).show();
						}else{
							addMessage(data.message);
						}
						$("#errors").hide();
					});
				}else{
					showErrors('The comment box is empty!',$(this).parent());
				}
			});		
			
			/* ------------- Rating Related ---------------- */
			
			// the following two may be combined, we will see
			
			$('.like').click(function(){
				var spanLike = $(this);
				var spanDislike = spanLike.parent().find('.dislike');
				var postId = spanLike.parent().parent().attr('data-post-id');
				var action = 1;
				if(spanLike.hasClass('disabled')){
					return;
				}else{
					if(spanDislike.hasClass('disabled')){
						spanDislike.removeClass('disabled');
					}
					$.post('./post/rate/format/json',{postId:postId,actionId:action},function(data){
						if(data.success == 1){
							// successfully rated
							loadRatingBar(postId);
						}else{
							addMessage(data.message);
						}
					});
					$(this).addClass('disabled');
				}
			});
			
			$('.dislike').click(function(){
				var spanDislike = $(this);
				var spanLike = spanDislike.parent().find('.like');
				var postId = spanDislike.parent().parent().attr('data-post-id');
				var action = 2;
				if(spanDislike.hasClass('disabled')){
					return;
				}else{
					if(spanLike.hasClass('disabled')){
						spanLike.removeClass('disabled');
					}
					$.post('./post/rate/format/json',{postId:postId,actionId:action},function(data){
						if(data.success == 1){
							// successfully rated
							loadRatingBar(postId);
						}else{
							addMessage(data.message);
						}
					});					
					$(this).addClass('disabled');
				}				
			});
			

			setInterval(function(){
				loadPostTime(); 
			}, Math.floor((Math.random()*30000)+5000));
		});
	}		

	// load the posts
	loadPosts(username);	
	

});

