( function( window, wp ){

	// check if gutenberg's editor root element is present.
	var editorEl = document.getElementById( 'editor' );
	if( ! editorEl ){ // do nothing if there's no gutenberg root element on page.
		return;
	}

	var postCreated = 0;

	var unsubscribe = wp.data.subscribe( function () { 

		setTimeout( function () { 

			const currentPost = wp.data.select('core/editor').getCurrentPost();
			const postStatus = currentPost.status;

			if( 'publish' == postStatus ) {
				return;
			}

			var checkbox_html = '<div id="wpzoom-gutenberg-checkbox" class="components-base-control__field"><span class="components-checkbox-control__input-container"><input id="inspector-checkbox-control-create-post" class="components-checkbox-control__input" type="checkbox" value="1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg></span><label class="components-checkbox-control__label" for="inspector-checkbox-control-create-post">Create draft post with this recipe</label></div>';
			var toolbalEl = editorEl.querySelector( '.edit-post-header__settings' );

			// Add event listener to the publish button
			var publishButton = document.querySelector('.editor-post-publish-button');

			if ( ! document.getElementById( 'inspector-checkbox-control-create-post' ) ) {

				if( toolbalEl instanceof HTMLElement ) {
					toolbalEl.insertAdjacentHTML( 'afterbegin', checkbox_html );
				}
			}

			if ( publishButton ) {
				// Add event listener to the publish button
				publishButton.addEventListener( 'click', handlePublishButtonClick, { once: true });
			}

			if ( postCreated > 0 ) {
				var checkboxContainer = document.getElementById('wpzoom-gutenberg-checkbox');
				if ( checkboxContainer ) {
					checkboxContainer.remove();
				}
			}


		}, 1 );

		

		function handlePublishButtonClick(event) {

			// Check if the custom checkbox is checked
			if ( isCustomCheckboxChecked() ) {
				// Create a new post with the content from the recipe custom post
				createNewPostFromRecipe( wp.data.select('core/editor').getCurrentPost(), postCreated );
			}
			postCreated++; // Set flag to indicate that a post has been created
            

		}

		// Check if the post is published and custom checkbox is checked
		function isCustomCheckboxChecked() {
		
			// Get the custom checkbox element
			var checkbox = document.getElementById('inspector-checkbox-control-create-post');
		
			// Check if the checkbox is checked
			if ( ! checkbox ) {
				return false;
			}
			return checkbox.checked;
		
		}

		// Create a new post with the content from the recipe custom post
		function createNewPostFromRecipe(recipePost, postCreated ) {

			// Check if a post has already been created
			if ( postCreated > 0) {
				return;
			}			

			var recipeTitle = '';
			var recipeTitleContainer = document.querySelector('.recipe-card-heading');
			if ( recipeTitleContainer ) {
				recipeTitle = recipeTitleContainer.children[0].innerText;
			}

			var postTitle = recipeTitle || recipePost.title.raw || recipePost.title.rendered || recipePost.title;

			// Prepare data for the new post
			const newPostData = {
				title: postTitle, // Set the title of the new post
				content: '<!-- wp:wpzoom-recipe-card/recipe-block-from-posts {"postId":"' + recipePost.id + '"} /-->', // Use the WPZoom Recipe Card block with the recipe post ID
				status: 'draft', // Set the status of the new post to draft
				postType: 'post', // Set the post type of the new post
			};
	
			// Create the new post
			wp.apiFetch({
				path: '/wp/v2/posts',
				method: 'POST',
				data: newPostData,
			})
			.then(data => {
				console.log('New post created with title:', postTitle);
			})
			.catch(error => {
				console.error('Error creating new post:', error);
			});

		}


	} );


} )( window, wp );