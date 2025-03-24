/**
 * Main function to be called for required JS actions.
 */

// Define process env for util.js error.
window.process = {
	env: {
		NODE_ENV: "development",
	},
};

(function ($) {
	$(document).ready(function () {

		$(document).on('click', '#cf-comment-board-wrapper .cls-board-outer:not(.focus)', function (e) {

			var iframe = $('iframe[name="editor-canvas"]');
			var iframeDocument = iframe.length ? iframe[0].contentWindow.document : null;
			const userCapability = wp.data.select('mdstore').getUserCapability()?.capability;
  
			if (iframeDocument) {
			  // Select elements within the iframe document
			  var elementsWithDataText = iframeDocument.querySelectorAll('[data-rich-text-format-boundary="true"]');
			  elementsWithDataText.forEach(function(element) {
				element.removeAttribute('data-rich-text-format-boundary');
			  });
			} else {
			  // Select elements in the main document
			  var elementsWithDataText = document.querySelectorAll('[data-rich-text-format-boundary="true"]');
			  elementsWithDataText.forEach(function(element) {
				element.removeAttribute('data-rich-text-format-boundary');
			  });
			}
  
			// Exclude focus on specific elements.
			var target = $(e.target);
			if (target.is(".commentContainer .comment-actions, .commentContainer .comment-actions *")) {
				return;
			}
			const _this = $(this);
			// Reset Comments Float.
			$('#cf-comment-board-wrapper .cls-board-outer').removeAttr('style');
			cfRemoveClass('#cf-comment-board-wrapper .cls-board-outer', 'is-open focus');
			cfRemoveClass('#cf-comment-board-wrapper .comment-delete-overlay', 'show');
			$('#cf-comment-board-wrapper .comment-resolve .resolve-cb').prop("checked", false);
			$('#cf-comment-board-wrapper .cls-board-outer .buttons-wrapper').removeClass('active');
			$('#cf-comment-board-wrapper .cls-board-outer').css('opacity', '0.4');
			let realTimeMode = wp.data.select('core/editor').getEditedPostAttribute('meta')?._is_real_time_mode ;
			if(true !== realTimeMode){
				$('.btn-wrapper').css('display', 'none');
			}
  
			const selectedText = _this.attr('id');
			const currentUser = wp.data.select('core').getCurrentUser()?.id;
			if(realTimeMode){
			  var hide = commentLock(selectedText, currentUser);
			  if(hide){
				  return;
			  }
			}
			if (iframeDocument) {
				$(iframeDocument).find('.cf-icon__addBlocks, .cf-icon__removeBlocks, .cf-icon-wholeblock__comment').removeClass('focus');
			} else {
				$('.cf-icon__addBlocks, .cf-icon__removeBlocks, .cf-icon-wholeblock__comment').removeClass('focus');
			}
			removeFloatingIcon();
  
			_this.addClass('focus');
			_this.addClass('is-open');
			_this.css('opacity', '1');
			
			//let referenceElement = document.getElementById(selectedText); 
			//let boardTopOfText = referenceElement ? referenceElement.getBoundingClientRect().top : 0;
			const { singleBoardIdSuggestion, singleBoardIdComment, combineBoardId } = getBoardIds(selectedText);
  
			const selectedTextWithoutSg = selectedText.replace( 'sg', '' );
  
			let referenceElement = iframeDocument 
			  ? (selectedTextWithoutSg.match(/^el/m) === null 
				  ? iframeDocument.getElementById(selectedTextWithoutSg) 
				  : iframeDocument.querySelector(`[datatext="${combineBoardId}"]`)) 
			  : (selectedTextWithoutSg.match(/^el/m) === null 
				  ? document.getElementById(selectedTextWithoutSg) 
				  : document.querySelector(`[datatext="${combineBoardId}"]`));
  	
  		if(!referenceElement){
          cfgetCustomAttribute().forEach((attrValue) => {
            if(iframeDocument){
              if (!referenceElement && iframeDocument.querySelector(`[${attrValue}="${selectedTextWithoutSg}"]`)) {
                referenceElement = iframeDocument.querySelector(`[${attrValue}="${selectedTextWithoutSg}"]`);
              }
            }else{
              if (!referenceElement && document.querySelector(`[${attrValue}="${selectedTextWithoutSg}"]`)) {
                referenceElement = document.querySelector(`[${attrValue}="${selectedTextWithoutSg}"]`);
              }
            }
            
          });
      } 
			let boardTopOfText = referenceElement ? referenceElement.getBoundingClientRect().top : 0;
			
			if (selectedText.match(/^el/m) !== null) {    
			  if (iframeDocument) {
				var $element = $(iframeDocument).find('[datatext="' + selectedText + '"]');
				if ($element.hasClass('cf-icon-wholeblock__comment')) {
					$element.addClass('focus');
				}
			  }else{
				if ($('[datatext="' + selectedText + '"]').hasClass('cf-icon-wholeblock__comment')) {
				  $('[datatext="' + selectedText + '"]').addClass('focus');
				}
			  }
			  
			} else {
				if (iframeDocument) {
				  // Inside iframe
				  var sid = $('#' + selectedText).attr('data-sid');
				  // Check the element with the matching sid in iframe content
				  var $sidElement = $(iframeDocument).find('[id="' + sid + '"]');
				  // Check for custom attribute suggestions inside iframe
				  const customAttrSuggestion = cfgetCustomAttribute();
				  customAttrSuggestion.map((attrValue) => {
					  var checkExist = $(iframeDocument).find('[' + attrValue + '="' + sid + '"]').length > 0;
					  if (checkExist) {
						  $(iframeDocument).find('[data-' + attrValue + '="' + sid + '"]').addClass('focus');
					  }
				  });
			  
				  $(iframeDocument).find('#' + sid).addClass('sg-format-class');
				} else {
				  let sid = $('#' + selectedText).attr('data-sid');  
				  // For the lock, align suggestions @author - Mayank / since 3.6
				  const customAttrSuggestion = cfgetCustomAttribute();
				  customAttrSuggestion.map((attrValue) => {
					  var checkExist = $('[' + attrValue + '="' + sid + '"]').length > 0;
					  if (checkExist) {
						  $('[data-' + attrValue + '="' + sid + '"]').addClass('focus');
					  }
				  })
				  $('#' + sid).addClass('sg-format-class');
				}
			}  
			if( ! referenceElement ) {
			  var blockClientID = wp.data.select( 'core/block-editor' ).getSelectedBlock()?.clientId;
			  if( iframeDocument ) {
				referenceElement = iframeDocument.getElementById( 'block-' + blockClientID );
			  } else {
				referenceElement = document.getElementById( 'block-' + blockClientID );
			  }
			}  
			if (referenceElement) {
			  referenceElement?.setAttribute('data-rich-text-format-boundary', 'true');
			  referenceElement.scrollIntoView({
				  behavior: "smooth", // Optional: to scroll smoothly (instead of instantly)
				  block: "center" // Optional: specifies vertical alignment (start, center, end, nearest)
			  });
			  
			  setTimeout(() => {
				const rect = referenceElement.getBoundingClientRect();
				boardTopOfText = rect.top + window.scrollY;  // Adds scroll position to get position relative to the document
				if (iframeDocument) {
          const elementNotice = document.querySelector('.components-notice-list.components-editor-notices__dismissible');
          let heightOfelementNotice;
          if(elementNotice){
            heightOfelementNotice = elementNotice.offsetHeight;
          }
          if('viewer' === userCapability){
            boardTopOfText = boardTopOfText + heightOfelementNotice;
          } else {
            boardTopOfText = boardTopOfText + 60 + heightOfelementNotice;
          }
        }
	  
				jQuery("#" + selectedText).offset({
				  top: boardTopOfText,
				});
			  }, 1000);
			}
			if (iframeDocument) {
			  if ($(iframeDocument).find(`#${selectedText}`).hasClass('sg-board')) {
				  let sid = $(iframeDocument).find(`#${selectedText}`).attr('data-sid');
				  $(iframeDocument).find(`#${sid}`).attr('data-rich-text-format-boundary', 'true');
			  }
			  $(iframeDocument).find(`[datatext="${selectedText}"]`).attr('data-rich-text-format-boundary', true);
			} else {
				if ($(`#${selectedText}`).hasClass('sg-board')) {
					let sid = $(`#${selectedText}`).attr('data-sid');
					$(`#${sid}`).attr('data-rich-text-format-boundary', 'true');
				}
				$('[datatext="' + selectedText + '"]').attr('data-rich-text-format-boundary', true);
			}
  
		  });

		$(document).on('click', '.cf-activity-centre .cls-board-outer:not(.active)', function (e) {

          var iframe = $('iframe[name="editor-canvas"]');
          var iframeDocument = iframe.length ? iframe[0].contentWindow.document : null;

          // Exclude focus on specific elements.
          var target = $(e.target);
          if (target.is(".commentContainer .comment-actions, .commentContainer .comment-actions *")) {
              return;
          }
          const _this = $(this);
          const selectedText = _this.attr('id');
          let referenceElement = document.getElementById(selectedText);

          if (referenceElement && referenceElement.classList.contains('is-open')) {
            _this.addClass('active');
            return;
          }

          if (iframeDocument) {
            // Select elements within the iframe document
            var elementsWithDataText = iframeDocument.querySelectorAll('[data-rich-text-format-boundary="true"]');
            elementsWithDataText.forEach(function(element) {
              element.removeAttribute('data-rich-text-format-boundary');
            });
          } else {
            // Select elements in the main document
            var elementsWithDataText = document.querySelectorAll('[data-rich-text-format-boundary="true"]');
            elementsWithDataText.forEach(function(element) {
              element.removeAttribute('data-rich-text-format-boundary');
            });
          }

          // Reset Comments Float.
          $('.cf-activity-centre .cls-board-outer').removeAttr('style');
          cfRemoveClass('.cf-activity-centre .cls-board-outer', 'is-open active');
          //cfRemoveClass('#cf-comment-board-wrapper .comment-delete-overlay', 'show');
          //$('#cf-comment-board-wrapper .comment-resolve .resolve-cb').prop("checked", false);
          $('.cf-activity-centre .cls-board-outer .buttons-wrapper').removeClass('active');

          let realTimeMode = wp.data.select('core/editor').getEditedPostAttribute('meta')?._is_real_time_mode ;


          
          const currentUser = wp.data.select('core').getCurrentUser()?.id;
          if(realTimeMode){
            var hide = commentLock(selectedText, currentUser);
            if(hide){
                return;
            }
          }
          if (iframeDocument) {
              $(iframeDocument).find('.cf-icon__addBlocks, .cf-icon__removeBlocks, .cf-icon-wholeblock__comment').removeClass('focus');
          } else {
              $('.cf-icon__addBlocks, .cf-icon__removeBlocks, .cf-icon-wholeblock__comment').removeClass('focus');
          }
          

          _this.addClass('active');
          _this.addClass('is-open');
          //_this.css('opacity', '1');
          

          let boardTopOfText = referenceElement ? referenceElement.getBoundingClientRect().top : 0;
          
          let topOfText;
          if (selectedText.match(/^el/m) !== null) {  


            if (iframeDocument) {
              var $element = $(iframeDocument).find('[datatext="' + selectedText + '"]');
              topOfText = $element.length ? $element.offset().top - boardTopOfText : null;

              if ($element.hasClass('cf-icon-wholeblock__comment')) {
                  $element.addClass('focus');
              }
            }else{
              topOfText = $('[datatext="' + selectedText + '"]').offset().top;
              if ($('[datatext="' + selectedText + '"]').hasClass('cf-icon-wholeblock__comment')) {
                $('[datatext="' + selectedText + '"]').addClass('focus');
              }
            }
            
          } else {
              if (iframeDocument) {
                // Inside iframe
                var sid = $('#' + selectedText).attr('data-sid');
                // Check the element with the matching sid in iframe content
                var $sidElement = $(iframeDocument).find('[id="' + sid + '"]');
                topOfText = $sidElement.length ? $sidElement.offset().top - boardTopOfText : null;
                
                // Check for custom attribute suggestions inside iframe
                const customAttrSuggestion = cfgetCustomAttribute();
                customAttrSuggestion.map((attrValue) => {
                    var checkExist = $(iframeDocument).find('[' + attrValue + '="' + sid + '"]').length > 0;
                    if (!topOfText && checkExist) {
                        topOfText = $(iframeDocument).find('[' + attrValue + '="' + sid + '"]').offset().top - boardTopOfText;
                        $(iframeDocument).find('[data-' + attrValue + '="' + sid + '"]').addClass('focus');
                    }
                });
            
                $(iframeDocument).find('#' + sid).addClass('sg-format-class');
              } else {
                let sid = $('#' + selectedText).attr('data-sid');
                topOfText = $('[id="' + sid + '"]').offset()?.top;
  
                // For the lock, align suggestions @author - Mayank / since 3.6
                const customAttrSuggestion = cfgetCustomAttribute();
                customAttrSuggestion.map((attrValue) => {
                    var checkExist = $('[' + attrValue + '="' + sid + '"]').length > 0;
                    if (!topOfText && checkExist) {
                        topOfText = $('[' + attrValue + '="' + sid + '"]').offset()?.top;
                        $('[data-' + attrValue + '="' + sid + '"]').addClass('focus');
                    }
                })
                $('#' + sid).addClass('sg-format-class');
              }
          }
          setTimeout(function () {
              if (!target.is(".btn-delete")){
              	if (iframeDocument) {
                  const elementNotice = document.querySelector('.components-notice-list.components-editor-notices__dismissible');
                  let heightOfelementNotice;
                  if(elementNotice){
                    heightOfelementNotice = elementNotice.offsetHeight;
                  }
                  topOfText = topOfText + heightOfelementNotice;
                }
                scrollBoardToPosition(topOfText);
              }
          }, 800);
          const sgID = selectedText.replace('sg', '');
          if (iframeDocument) {
            if ($(iframeDocument).find(`#${sgID}`).length > 0) {
                $(iframeDocument).find(`#${sgID}`).attr('data-rich-text-format-boundary', 'true');
            }
            $(iframeDocument).find(`[datatext="${selectedText}"]`).attr('data-rich-text-format-boundary', true);
          } else {
              var targetElement = document.getElementById(sgID);
              if (targetElement) {
                  targetElement.setAttribute('data-rich-text-format-boundary', 'true');
              }

              // Check if the element with the selected text attribute exists
              var selectedElement = document.querySelector(`[datatext="${selectedText}"]`);
              if (selectedElement) {
                  selectedElement.setAttribute('data-rich-text-format-boundary', true);
              }
          }

    });
	});
})(jQuery);

window.addEventListener("click", function (e) {
		// Trigger to close sidebar on post editor focus
		const editPostLayout = document.querySelector(".edit-post-layout");
		const sidebar = document.querySelector(
			".interface-interface-skeleton__sidebar"
		);
		const visualEditor = document.querySelector(".edit-post-visual-editor");
	
		if (
			editPostLayout &&
			editPostLayout.classList.contains("is-sidebar-opened")
		) {
			sidebar.classList.remove("cf-sidebar-closed");
		}
	
		if (visualEditor && visualEditor.contains(e.target)) {
			sidebar.classList.add("cf-sidebar-closed");
			setTimeout(handleEditorLayoutChange( true ), 100);
			closeMulticollabSidebar();
		}
	});

	window.addEventListener("load", function () {
		/**
		 * Strips out unwanted <mdspan> tags from the content.
		 * This function finds all elements with the 'mdspan' tag, and if the 'datatext' attribute is null, it sanitizes the text content of the element and replaces the element with the sanitized text.
		 */
		var findMdSpan = "mdspan";
		document.querySelectorAll(findMdSpan).forEach(function (element) {
			var datatext = element.getAttribute("datatext");
			if (datatext === null) {
				var sanitizedText = DOMPurify.sanitize(element.textContent); //phpcs:ignore
				element.replaceWith(document.createTextNode(sanitizedText));
			}
		});
	
		// Storing necessary user info in local storage.
		fetch(ajaxurl, {
			method: "POST",
			headers: {
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body: new URLSearchParams({
				action: "cf_get_user",
				nonce: multicollab_general_nonce.nonce,
			}),
		})
			.then((response) => response.json())
			.then((user) => {
				localStorage.setItem("userID", user.id);
				localStorage.setItem("userName", user.name);
				localStorage.setItem("userRole", user.role);
				localStorage.setItem("userURL", user.url);
			})
			.catch((error) => console.error("Error:", error));
		
	});

	document.addEventListener("DOMContentLoaded", function () {
	    /**
	     * Sets a cookie to track whether a promotional banner has been shown to the user.
	     *
	     * The cookie is set for each element with the class 'onBannerCookieSet', and the cookie name includes the value of the 'attr-value' attribute of the element.
	     * The cookie is set to expire in 7 days.
	     */
	    var onBannerCookieSet = document.querySelectorAll(".onBannerCookieSet");
	    if (null !== onBannerCookieSet) {
	      Array.from(onBannerCookieSet).forEach((element, index) => {
	        var date = new Date();
	        date.setTime(date.getTime() + 7 * 24 * 60 * 60 * 1000);
	  
	        document.cookie =
	          "banner_show_" +
	          element.getAttribute("attr-value") +
	          "=yes; expires=" +
	          date +
	          ";";
	        document.cookie =
	          "banner_show_once_" + element.getAttribute("attr-value") + "=yes;";
	      });
	    }
	  
	    setTimeout(function () {
	      if (
	        "remind" === multicollab_cf_alert.cf_give_alert_message ||
	        "stop" === multicollab_cf_alert.cf_give_alert_message
	      ) {
	        var publishBtn = document.querySelector(
	          ".editor-post-publish-button__button"
	        );
	        if (publishBtn) {
	          publishBtn.addEventListener("click", publishBtnClick);
	        }
	      }
	    }, 1000);
	  
	    // Set body overflow-y to unset
	    document.body.style.overflowY = "unset";
	  
	    // Save show_avatar option in a localstorage.
	    const data = {
	      action: "cf_store_in_localstorage",
	      nonce: multicollab_general_nonce.nonce,
	    };
	    fetch(ajaxurl, {
	      method: "POST",
	      headers: {
	        "Content-Type": "application/x-www-form-urlencoded",
	      },
	      body: new URLSearchParams(data).toString(),
	    })
	      .then((response) => response.json())
	      .then((response) => {
	        localStorage.setItem("showAvatars", response.showAvatars);
	        localStorage.setItem("commentingPluginUrl", response.commentingPluginUrl);
	      })
	      .catch((error) => console.error("Error:", error));
	  
	    document
	      .querySelectorAll(".shareCommentContainer textarea")
	      .forEach(function (textarea) {
	        textarea.addEventListener("click", function () {
	          this.parentElement.classList.add("hovered");
	        });
	      });
	  
	    // Get Caret Position
	    var ie =
	      typeof document.selection != "undefined" &&
	      document.selection.type != "Control" &&
	      true;
	    var w3 = typeof window.getSelection != "undefined" && true;
	    var cursorPos = 0;
	    var range = "";
	  
	    // Get the overlay, popup, and close button elements
	    var overlay = document.querySelector(".cf-plugin-dashboard-overlay");
	    var popup = document.querySelector(".cf-plugin-dashboard-popup");
	    var closeButton = document.querySelector(".cf_plugin_btncls");
	    var cfSettingsButton = document.getElementById("cf-settings-button");
	  
	    // Function to open the popup and show the overlay
	    function openPopup() {
	      overlay.style.display = "flex";
	      popup.style.display = "block";
	    }
	  
	    // Function to close the popup and hide the overlay
	    function closePopup() {
	      popup.style.display = "none";
	      overlay.style.display = "none";
	  
	      // Do AJAX call to update the settings.
	      var data = new FormData();
	      data.append("action", "cf_guide_popup_reset");
	      data.append("nonce", adminLocalizer.nonce);
	  
	      fetch(ajaxurl, {
	        method: "POST",
	        body: data,
	      })
	        .then((response) => response.json())
	        .then((res) => {
	          // Handle success
	        })
	        .catch((err) => {
	          console.error(err);
	        });
	    }
	  
	    // Add click event listener to the close button
	    if (closeButton) {
	      closeButton.addEventListener("click", closePopup);
	    }
	  
	    // Add click event listener to the cf-settings button
	    if (cfSettingsButton) {
	      cfSettingsButton.addEventListener("click", openPopup);
	    }

	    document.addEventListener("editorLayoutUpdate", function () {
			var commentBoardWrapper = document.getElementById(
			  "cf-comment-board-wrapper"
			);
			var commentSidebarCount = document.getElementById(
			  "cf-comment-board-wrapper"
			)?.children.length;
			
			var iframe = document.querySelector('iframe[name="editor-canvas"]');
			let body;
			let mainBodyClass = document.getElementsByClassName( 'wp-admin' )?.[0];
			if( iframe ) {
			  var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
			  body = iframeDocument.body;
		
			  if( mainBodyClass.classList.contains('hide-comments') ) {
				body.classList.add('hide-comments');
				if(iframe){
				  iframe.contentDocument?.body.classList.add('hide-comments');
				}
			  } else {
				body.classList.remove('hide-comments');
				if(iframe){
				  iframe.contentDocument?.body.classList.remove('hide-comments');
				}
			  }
		
			  if( mainBodyClass.classList.contains('hide-sg') ) {
				body.classList.add('hide-sg');
				if(iframe){
				  iframe.contentDocument?.body.classList.add('hide-sg');
				}
			  } else {
				body.classList.remove('hide-sg');
				if(iframe){
				  iframe.contentDocument?.body.classList.remove('hide-sg');
				}
			  }
		
			} else {
			  body = document.body;
			}
		
			if (body.classList.contains("commentOn") && commentSidebarCount > 0) {
			  return;
			}
		
			if (
			  // !commentBoardWrapper ||
			  // commentBoardWrapper.innerHTML.trim() === "" ||
			  (body.classList.contains("hide-sg") &&
				body.classList.contains("hide-comments"))
			) {
			  mainBodyClass.classList.remove('commentOn');
			  //body.classList.remove("commentOn");
			  if(iframe){
				iframe.contentDocument?.body.classList.remove('commentOn');
			  }
			} else {
			  var sgBoard = commentBoardWrapper?.querySelector(".sg-board");
			  var cmBoard = commentBoardWrapper?.querySelector(".cm-board");
			  if (
				(
				  !mainBodyClass.classList.contains("hide-sg") 
				  //&& sgBoard
				) ||
				(
				  !mainBodyClass.classList.contains("hide-comments") 
				  //&& cmBoard
				)
			  ) {
				//body.classList.add("commentOn");
				mainBodyClass.classList.add('commentOn');
				if(iframe){
				  iframe.contentDocument?.body.classList.add('commentOn');
				}
			  } else if (
				(mainBodyClass.classList.contains("hide-comments")
				  && !mainBodyClass.classList.contains("hide-sg")
				  //&& !sgBoard
				) ||
				(mainBodyClass.classList.contains("hide-sg")
				&& !mainBodyClass.classList.contains("hide-comments")
				//&& !cmBoard
				)
			  ) {
				mainBodyClass.classList.remove('commentOn');
				//body.classList.remove("commentOn");
				if(iframe){
				  iframe.contentDocument?.body.classList.remove('commentOn');
				}
			  }
			}
		
			// Resolved #633 issue @author - Mayank
			var browser = (function (agent) {
			  if (agent.indexOf("firefox") > -1) {
				return "firefox";
			  } else {
				return "other";
			  }
			})(window.navigator.userAgent.toLowerCase());
		
			if (browser === "firefox") {
			  document.querySelectorAll(".wp-block").forEach(addBorderToAlignmentBlock); // Resolved #633 issue @author - Mayank
			}
		
			// Call the function to apply the styles
			addBorderStyle();
		  });
	  
	    // Event listener for showHideComments
	    document.addEventListener("showHideComments", function () {
	      var commentBoardWrapper = document.getElementById(
	        "cf-comment-board-wrapper"
	      );
	  
	      // Check if the comment board is not empty
	      if (commentBoardWrapper && commentBoardWrapper.innerHTML.trim() !== "") {
	        var commentBoardItems = document.querySelectorAll(
	          "#cf-comment-board-wrapper .cls-board-outer:not(.focus)"
	        );
	  
	        commentBoardItems.forEach(function (item) {
	          var commentContainers = item.querySelectorAll(
	            ".boardTop .commentContainer"
	          );
	          var commentCount = commentContainers.length;
	  
	          // If the number of comments exceeds the limit and the item does not have the 'focus' class
	          if (
	            commentCount > getCommentsLimit() &&
	            !item.classList.contains("focus")
	          ) {
	            var showAllComments = item.querySelector(".show-all-comments");
	            if (showAllComments) {
	              // Update the text to show how many replies are hidden
	              showAllComments.innerHTML = sprintf(
	                wp.i18n.__(
	                  "Show all %d replies",
	                  "content-collaboration-inline-commenting"
	                ),
	                commentCount - 1
	              );
	              showAllComments.style.display = "block"; // Make the "Show all comments" visible
	            }
	  
	            // Hide all comments and then show only up to the comment limit
	            commentContainers.forEach(function (container, index) {
	              if (index >= getCommentsLimit()) {
	                container.style.display = "none";
	              } else {
	                container.style.display = "block";
	              }
	            });
	          } else {
	            // Show all comments and hide the "Show all comments" link
	            commentContainers.forEach(function (container) {
	              container.style.display = "block";
	            });
	            var showAllComments = item.querySelector(".show-all-comments");
	            if (showAllComments) {
	              showAllComments.style.display = "none";
	            }
	          }
	        });
	      }
	    });
	  
	    assignThisToUser();
	    showAssignableEmailList();
	    createAutoEmailMention();
	 });
	

	document.addEventListener("click", function (event) {
		// Display Delete Overlay Box
		if (event.target.closest(".js-resolve-comment")) {
			const commentContainer = event.target.closest(".commentContainer");
			if(commentContainer){
				commentContainer
					.siblings()
					.forEach((sibling) =>
						sibling
							.querySelectorAll(".comment-delete-overlay")
							.forEach((overlay) => overlay.classList.remove("show"))
					);
				commentContainer
					.querySelector(".comment-delete-overlay")
					.classList.add("show");
				commentContainer
					.querySelector(".buttons-wrapper")
					.classList.remove("active");
				commentContainer.querySelector(
					".comment-delete-overlay .comment-overlay-text"
				).textContent = wp.i18n.__(
					"Delete this thread?",
					"content-collaboration-inline-commenting"
				);
			}
		}
	
		if (event.target.closest(".resolve-cb")) {
			const commentContainer = event.target.closest(".commentContainer");
			if(commentContainer){
				commentContainer
					.siblings()
					.forEach((sibling) =>
						sibling
							.querySelectorAll(".comment-delete-overlay")
							.forEach((overlay) => overlay.classList.remove("show"))
					);
				commentContainer.querySelector(
					".comment-delete-overlay .comment-overlay-text"
				).textContent = wp.i18n.__(
					"Resolve this thread?",
					"content-collaboration-inline-commenting"
				);
				commentContainer.querySelector(
					".comment-delete-overlay .btn-delete"
				).textContent = wp.i18n.__("Yes", "content-collaboration-inline-commenting");
				commentContainer.querySelector(
					".comment-delete-overlay .btn-cancel"
				).textContent = wp.i18n.__("No", "content-collaboration-inline-commenting");
				commentContainer
					.querySelector(".comment-delete-overlay")
					.classList.add("show");
			}
		}
	
		if (event.target.closest(".js-trash-comment")) {
			const commentContainer = event.target.closest(".commentContainer");
			if(commentContainer){
				commentContainer
					.siblings()
					.forEach((sibling) =>
						sibling
							.querySelectorAll(".comment-delete-overlay")
							.forEach((overlay) => overlay.classList.remove("show"))
					);
				commentContainer
					.querySelector(".comment-delete-overlay")
					.classList.add("show");
				commentContainer
					.querySelector(".buttons-wrapper")
					.classList.remove("active");
			}
		}
	
		if (event.target.closest(".js-trash-suggestion")) {
			const commentContainer = event.target.closest(".commentContainer");
			if(commentContainer){
				commentContainer
					.siblings()
					.forEach((sibling) =>
						sibling
							.querySelectorAll(".comment-delete-overlay")
							.forEach((overlay) => overlay.classList.remove("show"))
					);
				commentContainer.querySelector(
					".comment-delete-overlay .comment-overlay-text"
				).textContent = wp.i18n.__(
					"Delete this Suggestion?",
					"content-collaboration-inline-commenting"
				);
				commentContainer.querySelector(
					".comment-delete-overlay .btn-delete"
				).textContent = wp.i18n.__("Yes", "content-collaboration-inline-commenting");
				commentContainer.querySelector(
					".comment-delete-overlay .btn-cancel"
				).textContent = wp.i18n.__("No", "content-collaboration-inline-commenting");
				commentContainer
					.querySelector(".comment-delete-overlay")
					.classList.add("show");
				commentContainer
					.querySelector(".buttons-wrapper")
					.classList.remove("active");
			}    
		}
	
		if (event.target.closest(".comment-delete-overlay .btn-cancel")) {
			const commentContainer = event.target.closest(".commentContainer");
			if(commentContainer){
			commentContainer
				.querySelector(".comment-delete-overlay")
				.classList.remove("show");
			}
			const resolveCheckbox = commentContainer.querySelector(
				".comment-resolve .resolve-cb"
			);
			if (resolveCheckbox) {
				resolveCheckbox.checked = false; // Set checked to false only if the element exists
			}
		}
		// show hide toggle for commentendOn text in dashboard activity log in commented section very long text
		if (event.target.matches("#cf-dashboard .user-data-row .cf-show-more")) {
			const button = event.target;
			const userCommentedOn = button.closest(".user-commented-on");
			const showAll = userCommentedOn.querySelector(".show-all");
			const showLess = userCommentedOn.querySelector(".show-less");
	
			if (showAll.classList.contains("js-hide")) {
				showAll.classList.remove("js-hide");
				showLess.classList.add("js-hide");
				button.textContent = wp.i18n.__(
					"Show less",
					"content-collaboration-inline-commenting"
				);
			} else {
				showAll.classList.add("js-hide");
				showLess.classList.remove("js-hide");
				button.textContent = wp.i18n.__(
					"Show more",
					"content-collaboration-inline-commenting"
				);
			}
		}
	
		// Editor layout width changes sidebar multicollab btn click
		if (
			event.target.closest(
			  ".interface-pinned-items .components-button, .edit-post-header-toolbar__inserter-toggle, .editor-document-tools__inserter-toggle"
			) ||
			event.target.closest(
			  ".editor-document-tools .editor-document-tools__document-overview-toggle"
			) ||
			( event.target?.parentElement?.getAttribute("aria-label") === "Close" || event.target?.parentElement?.getAttribute("aria-label") === "Close Settings" || event.target?.parentElement?.getAttribute("aria-label") === "Close plugin" )
		  ) {
			setTimeout(handleEditorLayoutChange( true ), 100);
		  }
	
		// If thread focused via an activity center
		if (event.target.closest(".block-editor-block-list__layout .wp-block")) {
			if (
				document.querySelector(".cls-board-outer")?.classList.contains("locked")
			) {
				// Reset Comments Float. This will reset the positions of all comments.
				document.querySelector(
					"#cf-comment-board-wrapper .cls-board-outer"
				).style.opacity = "1";
				cfRemoveClass(
					"#cf-comment-board-wrapper .cls-board-outer",
					"is-open focus"
				);
				// document.querySelector('#cf-comment-board-wrapper .cls-board-outer').classList.remove('focus');
				// document.querySelector('#cf-comment-board-wrapper .cls-board-outer').classList.remove('is-open');
				document
					.querySelector("#cf-comment-board-wrapper .cls-board-outer")
					.removeAttribute("style");
				document
					.querySelector(
						"#cf-comment-board-wrapper .cls-board-outer .buttons-wrapper"
					)
					.classList.remove("active");
	
				if (e.target.localName === "mdspan") {
					const dataid = e.target.getAttribute("datatext");
					// Trigger card click to focus.
					document.getElementById(dataid).click();
				}
				document.querySelectorAll(".cls-board-outer").forEach(function (element) {
					element.classList.remove("locked");
				});
			}
		}
	
		// Scroll to the commented text and its popup from History Popup.
		const scrollCommentTarget = event.target.closest(".user-commented-on");
		if (scrollCommentTarget) {
			event.preventDefault();
	
			document
				.querySelectorAll(
					"#custom-history-popup, #history-toggle, .custom-buttons"
				)
				.forEach((el) => {
					el.classList.toggle("active");
				});
	
			// Triggering comments-toggle if it is closed when clicking on a particular commented link from activity center.
			const commentsToggle = document.getElementById("comments-toggle");
			if (commentsToggle && commentsToggle.classList.contains("active")) {
				commentsToggle.click();
			}
	
			const dataid = scrollCommentTarget.getAttribute("data-id");
	
			// Trigger card click to focus.
			const card = document.getElementById(dataid);
			if (card) {
				card.click();
			}
	
			// Focus and Lock the popup to prevent on hover issue.
			document.querySelectorAll(".cls-board-outer").forEach((el) => {
				el.classList.remove("locked");
			});
			if (card) {
				card.classList.add("locked");
			}
	
			const dataTextElements = document.querySelectorAll(
				`[datatext="${dataid}"]`
			);
			dataTextElements.forEach((el) => {
				el.classList.add("focus");
			});
	
			setTimeout(function () {
				dataTextElements.forEach((el) => {
					el.classList.remove("focus");
				});
			}, 1500);
		}
	
		if (event.target.matches(".readmoreComment, .readlessComment")) {
			const commentText = event.target.closest(".commentText");
			if (commentText) {
				const readLess = event.target?.closest(".readlessTxt");
				const readMore = event.target?.closest(".readmoreTxt");
				if (readLess) {
					readLess.classList.remove("active");
					commentText.querySelector(".readmoreTxt").classList.add("active");
				}
				if (readMore) {
					readMore.classList.remove("active");
					commentText.querySelector(".readlessTxt").classList.add("active");
				}
			}
		}
	
		if (event.target.matches(".cls-board-outer .buttons-wrapper")) {
			const target = event.target;
	
			if (target.classList.contains("active")) {
				target.classList.toggle("active");
				const commentContainer = target.closest(".commentContainer");
				if (commentContainer) {
					const siblings = Array.from(
						commentContainer.parentElement.children
					).filter((child) => child !== commentContainer);
					siblings.forEach((sibling) => {
						const buttonsWrapper = sibling.querySelector(".buttons-wrapper");
						if (buttonsWrapper) {
							buttonsWrapper.classList.remove("active");
						}
					});
				}
			} else {
				document
					.querySelectorAll(
						"#cf-comment-board-wrapper .cls-board-outer .buttons-wrapper"
					)
					.forEach((el) => {
						el.classList.remove("active");
					});
				target.classList.toggle("active");
			}
		}
	
		if (
			!event.target.closest(".cls-board-outer .commentContainer .buttons-wrapper")
		) {
			document
				.querySelectorAll(".cls-board-outer .commentContainer .buttons-wrapper")
				.forEach(function (element) {
					element.classList.remove("active");
				});
		}
	
		if (event.target.matches(".cf-slack-integration-box__acc-setting")) {
			const target = event.target;
			const cfCntBoxBody = target.closest(".cf-settings-panel__repeater-body");
	
			if (cfCntBoxBody) {
				const cfSlackInnerIntegrationBox = cfCntBoxBody.querySelector(
					".cf-slack-integration-innerbox"
				);
	
				if (cfSlackInnerIntegrationBox) {
					cfSlackInnerIntegrationBox.classList.toggle("hidden");
				}
			}
		}
	
		if (event.target.matches(".md-plugin-tooltip svg")) {
			event.preventDefault();
			// Hide all .cf-suggestion-tooltip-box elements except the one within the clicked .md-plugin-tooltip
			document
				.querySelectorAll(".cf-suggestion-tooltip-box")
				.forEach(function (tooltipBox) {
					const parentTooltip = event.target.closest(".md-plugin-tooltip");
					if (!parentTooltip.contains(tooltipBox)) {
						tooltipBox.style.display = "none";
					}
				});
	
			// Toggle the .cf-suggestion-tooltip-box within the clicked .md-plugin-tooltip
			const targetTooltipBox = event.target
				.closest(".md-plugin-tooltip")
				.querySelector(".cf-suggestion-tooltip-box");
			if (targetTooltipBox) {
				if (
					targetTooltipBox.style.display === "none" ||
					targetTooltipBox.style.display === ""
				) {
					targetTooltipBox.style.display = "block";
				} else {
					targetTooltipBox.style.display = "none";
				}
			}
	
			// Hide all .cf_feedback_layout elements within .cf-feedback-dashboard
			document
				.querySelectorAll(".cf-feedback-dashboard .cf_feedback_layout")
				.forEach(function (feedbackLayout) {
					feedbackLayout.style.display = "none";
				});
		}
	
		const handleShowAllComments = (
			selector,
			parentSelector,
			targetSelector,
			hideSelf = false
		) => {
			if (event.target.matches(selector)) {
				const button = event.target;
				const parent = button.closest(parentSelector);
				if (parent) {
					parent
						.querySelectorAll(targetSelector)
						.forEach((element) => (element.style.display = "block"));
					if (hideSelf) {
						button.style.display = "none";
					}
				}
			}
		};
	
		// Show all comments in #cf-comment-board-wrapper
		handleShowAllComments(
			"#cf-comment-board-wrapper .cls-board-outer .show-all-comments",
			".boardTop",
			".commentContainer",
			true
		);
		// Show all comments in #cf-dashboard
		handleShowAllComments(
			"#cf-dashboard .user-data-row .show-all-comments",
			".user-data-row",
			".user-data-box",
			true
		);
		// Show all comments in activity center
		handleShowAllComments(
			".js-activity-centre .user-data-row .show-all-comments",
			".user-data-row",
			".user-data-box",
			true
		);
		handleShowAllComments(
			".js-activity-centre .user-data-row .show-all-comments",
			".user-data-row",
			".show-all-comments",
			true
		);
	
		/**
		 * Resets the classes on various elements in the activity center when a board element is clicked.
		 * This function performs the following actions:
		 * - Removes the "is-selected" class from specific elements like "cf-bodyrowupdate", "cf-columnalign", etc.
		 * - Adds the "is-selected" class to the row or column elements that match the clicked board element.
		 * - Removes the "active" class from all user data rows and adds it to the specific user data row.
		 * - Removes the "is-selected" class from all comment icons.
		 * - Updates the "datatext" attribute on elements to match the clicked board element.
		 * - Triggers a custom "showHideComments" event.
		 *
		 */
		const boardElement = event.target?.closest(".cls-board-outer");
		if (boardElement) {
			const boardID = boardElement.getAttribute("id");
			const cleanedBoardID = boardID.replace("sg", "");
			const cList = document.getElementById(cleanedBoardID)?.classList;
	
			// Remove "is-selected" class from specific elements
			document
				.querySelectorAll(
					".cf-bodyrowupdate, .cf-columnalign, .cf-bodycolumnupdate, .cf-bodycolumndelete, .cf-bodyrowdeleteupdate"
				)
				?.forEach((element) => {
					element.classList.remove("is-selected");
				});
	
			// Add "is-selected" class to rows
			if (
				!document.body.classList.contains("hide-sg") &&
				(cList?.contains("bodyupdate-row") ||
					cList?.contains("bodyupdate-delete-row"))
			) {
				if (
					cList.contains("bodyupdate-row") ||
					cList.contains("bodyupdate-delete-row")
				) {
					document
						.getElementById(cleanedBoardID)
						.closest("tr")
						.classList.add("is-selected");
				}
			}
	
			// Add "is-selected" class to columns
			if (
				!document.body.classList.contains("hide-sg") &&
				(cList?.contains("bodyupdate-column") ||
					cList?.contains("bodyupdate-delete-column") ||
					cList?.contains("columnalign"))
			) {
				document
					.querySelectorAll(`[id="${cleanedBoardID}"]`)
					.forEach((element) => {
						element.closest("td").classList.add("is-selected");
					});
			}
	
			// Remove "active" class from user data rows
			document
				.querySelectorAll(".js-activity-centre .user-data-row")
				.forEach((element) => {
					element.classList.remove("active");
				});
	
			// Remove "is-selected" class from comment icons
			document.querySelectorAll(".commentIcon")?.forEach((element) => {
				element.classList.remove("is-selected");
			});
	
			// Dispatch data text
			wp.data.dispatch("mdstore").setDataText(boardID);
	
			// Check if URL has a datatext param
			const queryString = window.location.search;
			const urlParams = new URLSearchParams(queryString);
			const current_url = urlParams.get("current_url");
			const shareUrl = getCookie("current_url");
	
			if (current_url) {
				urlParams.delete("current_url");
				window.history.replaceState({}, "", `${location.pathname}?${urlParams}`);
			}
	
			if (shareUrl) {
				deleteCookie("current_url");
			}
	
			// Add "is-selected" class to elements with datatext attribute
			document.querySelectorAll(`[datatext="${boardID}"]`).forEach((element) => {
				element.classList.add("is-selected");
			});
	
			// Trigger custom event
			const event = new Event("showHideComments");
			document.dispatchEvent(event);
		}
	
		/**
		 * Handles the click event on the CF tabs in the dashboard.
		 * Redirects the user to the appropriate page based on the selected tab.
		 *
		 * @param {Event} event - The click event object.
		 */
		if (
			event.target.closest(
				".cf-dashboard-layout__tabs-list li:not(.cf_subscription_tab)"
			)
		) {
			var dataId = document
				.querySelector(".cf-tab-active a")
				.getAttribute("data-id");
			var queryString = window.location.search;
			var urlParams = new URLSearchParams(queryString);
			var current_url = urlParams.get("page");
			var curruntUrl =
				location.protocol +
				"//" +
				location.host +
				location.pathname +
				"?page=" +
				current_url;
	
			if ("cf-dashboard" === dataId) {
				window.location.href = curruntUrl + "&view=web-activity";
			} else if ("cf-reports" === dataId) {
				window.location.href = curruntUrl + "&view=post-activity";
			} else if ("cf-settings" === dataId) {
				window.location.href = curruntUrl + "&view=settings";
			} else if ("cf-roles-slack-integration" === dataId) {
				window.location.href = curruntUrl + "&view=intigrations";
			}
		}
	
		if (event.target.matches(".markup")) {
			document.querySelectorAll(".markup").forEach(function (element) {
				element.classList.remove("my-class");
				element.removeAttribute("data_name");
			});
			event.target.setAttribute("data_name", true);
			event.target.classList.add("my-class");
		}
	
		if (event.target.matches(".dashicon.dashicons-ellipsis")) {
			var buttonsHolder = event.target.closest(".buttons-holder");
			if (buttonsHolder) {
				buttonsHolder.classList.toggle("is_active");
			}
			event.stopPropagation();
		} else {
			document.querySelectorAll(".buttons-holder").forEach(function (element) {
				element.classList.remove("is_active");
			});
		}
	
		if (event.target.closest(".js-cf-hide-assign-list")) {
			event.preventDefault();
			const target = event.target.closest(".js-cf-hide-assign-list");
			const el = target.closest(".cls-board-outer").id;
			const assignableListPopup = document.querySelector(
				`#${el} .cf-assignable-list-popup`
			);
			if (assignableListPopup) {
				assignableListPopup.remove();
			}
			target.classList.remove("js-cf-hide-assign-list");
			target.classList.add("js-cf-show-assign-list");
		}
	});

	document.addEventListener("focusin", function (event) {
		// Cache commonly used DOM elements
		const target = event.target;
		const boardOuter = target.closest("#cf-comments-suggestions-parent .cls-board-outer");
  		const activityBoardOuter = target.closest(".cf-activity-centre .cls-board-outer");
		const shareCommentContainer = target.closest(".shareCommentContainer");
		const btnWrapper = shareCommentContainer ? shareCommentContainer.querySelector(".btn-wrapper") : null;
		const focusParentElement = boardOuter ? boardOuter.getAttribute("id") : null;
	
		// Handle focus event for comment input
		if (target.matches(".cf-share-comment") && activityBoardOuter) {
		    if (btnWrapper) {
		      btnWrapper.style.display = "block";
		    }
		}

		if (target.matches(".cf-share-comment") && boardOuter) {
		target.classList.add("comment-focus");
	
		// Remove unnecessary classes for all boards
		cfRemoveClass(".cls-board-outer", "focus onGoing");
	
		// Add focus and onGoing classes to the current board
		boardOuter.classList.add("focus", "onGoing");
	
		// Show the comment button
		if (btnWrapper) {
			btnWrapper.style.display = "block";
		}
	
		// Handle floating comments for suggestion feature
		if (focusParentElement) {
			const boardsWithOpenClass = document.querySelectorAll(".cls-board-outer.is-open");
			const boardsWithFocusClass = document.querySelectorAll(".cls-board-outer.focus");
			if (boardsWithOpenClass.length) {
			  const focusParentElementWithoutSg = focusParentElement.replace("sg", "");
	  
			  const sortedBoardsWithOpenClass = Array.from(boardsWithOpenClass);
	  
					// Sort the array based on the id attribute, prioritizing those that start with 'el'
			  sortedBoardsWithOpenClass.sort((a, b) => {
				const idA = a.getAttribute("id");
				const idB = b.getAttribute("id");
	  
				// Check if `idA` starts with 'el' and `idB` does not, so `a` should come first
				if (idA && idA.startsWith('el') && !(idB && idB.startsWith('el'))) {
				  return -1;
				}
				
				// Check if `idB` starts with 'el' and `idA` does not, so `b` should come first
				if (!(idA && idA.startsWith('el')) && idB && idB.startsWith('el')) {
				  return 1;
				}
				// Otherwise, maintain the original order
				return 0;
			  });
	  
			  if( sortedBoardsWithOpenClass.length > 1 && sortedBoardsWithOpenClass[0].getAttribute("id").replace("sg", "") !== focusParentElementWithoutSg ) {
				floatCommentsBoard(focusParentElementWithoutSg);
			  }

			  sortedBoardsWithOpenClass.map((board, i) => {
				if( i > 0 ) {
				  board.classList.remove("is-open");
				  board.removeAttribute("style");
				}
			  });
			  
			} else if (boardsWithFocusClass.length) {
			  boardOuter.classList.remove("focus");
			  // Commented out unnecessary setTimeout
			  // setTimeout(function () { jQuery(`#${focusParentElement}`).trigger('click'); }, 800);
			}
		  }
		}
	
		// Handle focus event for editing comment
		if (target.matches(".js-cf-edit-comment") && boardOuter) {
		// Disable reply box on other boards and adjust opacity
		document.querySelectorAll(".cls-board-outer").forEach((element) => {
			element.classList.remove("cf-removeReply");
			element.style.opacity = "0.4";
		});
	
		boardOuter.classList.add("cf-removeReply");
		boardOuter.style.opacity = "1.0";
	
		// Hide share comment container if present
		if (boardOuter.querySelector(".shareCommentContainer")) {
			boardOuter.querySelector(".shareCommentContainer").style.display = "none";
		}
		}
	});

	document.addEventListener("focusout", function (event) {
		if (event.target.matches(".cf-share-comment")) {
			event.target.classList.remove("comment-focus");
		}
		if (event.target.matches(".js-cf-edit-comment")) {
			const boardOuter = event.target.closest(".cls-board-outer");
			if (boardOuter) {
				boardOuter.classList.remove("cf-removeReply");
			}
		}
	});

	// Helper function to get siblings
Element.prototype.siblings = function () {
		return Array.prototype.filter.call(
			this.parentNode.children,
			function (child) {
				return child !== this;
			},
			this
		);
	};

/**
 * Checks if the given element is currently in the viewport.
 *
 * @param {Element} elem - The element to check.
 * @returns {boolean} - True if the element is in the viewport, false otherwise.
 */
function isIntoView(elem) {
		var documentViewTop = window.scrollY;
		var documentViewBottom = documentViewTop + window.innerHeight;
	
		var elementTop = elem.getBoundingClientRect().top + window.scrollY;
		var elementBottom = elementTop + elem.offsetHeight;
	
		return elementBottom <= documentViewBottom && elementTop >= documentViewTop;
	}

	/**
 * Removes a <mdspan> tag from a block's attributes.
 *
 * This function is responsible for removing a specific <mdspan> tag from the attributes of a block. It first finds the block element in the DOM, then checks the block type to determine the appropriate removal logic. For core/gallery and core/table blocks, it calls dedicated removal functions. For ACF blocks, it calls a separate removal function. For all other blocks, it iterates through the allowed attribute tags and removes the specified tag from the block's content.
 *
 * @param {string} elIDRemove - The ID of the tag to remove.
 */
function removeTag(elIDRemove) {
  
  const iframe = document.querySelector('iframe[name="editor-canvas"]');
  const iframeDocument = iframe ? iframe.contentWindow.document : null;
  let element;
  if (iframeDocument) {
    element = iframeDocument.querySelector('[datatext="' + elIDRemove + '"]');
  }else{
    element = document.querySelector('[datatext="' + elIDRemove + '"]');
  }
  
  const clientId = element?.closest("[data-block]").getAttribute("data-block");
  
  const blockType = clientId 
    ? wp.data.select('core/block-editor').getBlock(clientId)?.name 
    : null;
  
  const findAttributes = window.adminLocalizer.allowed_attribute_tags;
  const blockAttributes = wp.data
    .select("core/block-editor")
    .getBlockAttributes(clientId); // eslint-disable-line
  var prefixAcf = "acf/";

  if ("core/gallery" === blockType) {
    removeGalleryTag(blockAttributes, clientId, elIDRemove);
  }
  if ("core/table" === blockType) {
    removeTableTag(blockAttributes, clientId, elIDRemove);
  }
  if (blockType?.startsWith(prefixAcf)) {
    removeAcfTag(blockAttributes, clientId, elIDRemove);
  }

  if (null !== blockAttributes && !blockType.startsWith(prefixAcf)) {
    findAttributes.forEach(function (attrb) {
      var content = blockAttributes[attrb];

      if (undefined !== content && -1 !== content.indexOf(elIDRemove)) {
        if ("" !== content) {
          let tempDiv = document.createElement("div");
          tempDiv.innerHTML = content; // phpcs:ignore
          let childElements = tempDiv.getElementsByTagName("mdspan");
          for (let i = 0; i < childElements.length; i++) {
            if (elIDRemove === childElements[i].attributes.datatext.value) {
              // Change logic to keep other HTML Tag in content..only remove mdspan tag

              var parent = childElements[i].parentNode;

              while (childElements[i].firstChild) {
                parent.insertBefore(
                  childElements[i].firstChild,
                  childElements[i]
                ); // phpcs:ignore
              }
              parent.removeChild(childElements[i]);

              const finalContent = tempDiv.innerHTML;

              if (findAttributes.indexOf(attrb) !== -1) {
                wp.data
                  .dispatch("core/editor")
                  .updateBlock(
                    clientId,
                    createNewAttributeWithFinalContent(attrb, finalContent)
                  );
              }
              break;
            }
          }
        }
      }
    });
  }
}

/**
 * Recursively removes 'mdspan' tags with a specific 'datatext' attribute value from the 'data' property of a block's attributes.
 *
 * @param {Object} blockAttributes - The block attributes object.
 * @param {string} clientId - The unique identifier of the block.
 * @param {string} elIDRemove - The 'datatext' attribute value to be removed.
 * @returns {Object} The updated block attributes object.
 */
function removeAcfTag(blockAttributes, clientId, elIDRemove) {
		const updatedAttributes = {
			data: deepCopy(blockAttributes.data), // Ensure that the original object is not mutated
		};
	
		let targetObject = null;
	
		// Recursive function to traverse nested objects
		const checkAndRemoveDatatext = (obj, parentObject, parentKey) => {
			for (const key in obj) {
				if (obj.hasOwnProperty(key)) {
					const value = obj[key];
	
					// Check if the attribute value contains both 'mdspan' and 'datatext'
					if (
						typeof value === "string" &&
						value.includes("<mdspan") &&
						value.includes('datatext="' + elIDRemove + '"')
					) {
						// Use DOM manipulation to remove mdspan tags only for the specific datatext value
						const tempDiv = document.createElement("div");
						tempDiv.innerHTML = value; // phpcs:ignore
	
						const mdspans = tempDiv.querySelectorAll(
							'mdspan[datatext="' + elIDRemove + '"]'
						);
						for (let i = 0; i < mdspans.length; i++) {
							const mdspan = mdspans[i];
							// Replace mdspan with its content (keeping only the text content)
							mdspan.parentNode.replaceChild(
								document.createTextNode(mdspan.textContent),
								mdspan
							);
						}
						targetObject = parentObject;
	
						var $currentElement = jQuery('[datatext="' + elIDRemove + '"]');
						var $currentMdspan = $currentElement.closest("mdspan");
						$currentMdspan.replaceWith($currentMdspan.contents()); // phpcs:ignore
	
						const matchnum = key.match(/(\d+)_/);
						const numericValue = matchnum ? matchnum[1] : null;
						const matchResult = key.match(/\d+_(.+)/);
						const dataName = matchResult ? matchResult[1] : key;
	
						let selector = key.startsWith("field_")
							? `[data-key="${key}"]`
							: `[data-name="${dataName}"]`;
						if (parentKey !== null) {
							selector += `${selector} input[type="text"][id*="${parentKey}"], ${selector} textarea[id*="${parentKey}"]`;
						} else {
							if (numericValue !== null) {
								selector += `${selector} input[type="text"][id*="row-${numericValue}"], ${selector} textarea[id*="row-${numericValue}"]`;
							} else {
								selector += `${selector} input[type="text"], ${selector} textarea`;
							}
						}
						const $inputField = jQuery(selector);
						$inputField.val(tempDiv.innerHTML);
	
						obj[key] = tempDiv.innerHTML;
					} else if (typeof value === "object") {
						// If the value is an object, recursively check and remove datatext
						checkAndRemoveDatatext(value, obj, key);
					}
				}
			}
		};
	
		// Start the recursive check
		checkAndRemoveDatatext(updatedAttributes.data, null, null);
	
		// Update block attributes if needed
		wp.data
			.dispatch("core/block-editor")
			.updateBlockAttributes(clientId, updatedAttributes);
	
		return targetObject;
	}
	
	/**
	 * Removes the 'mdspan' tags from the captions of images in a gallery block.
	 *
	 * @param {Object} blockAttributes - The attributes of the gallery block.
	 * @param {string} clientId - The ID of the gallery block.
	 * @param {string} elIDRemove - The ID of the 'mdspan' element to be removed.
	 */
	function removeGalleryTag(blockAttributes, clientId, elIDRemove) {
		document.querySelectorAll(".blocks-gallery-item").forEach(function (el) {
			if (el.querySelector("figure figcaption")) {
				blockAttributes.images?.forEach((image) => {
					const caption = image.caption;
					let tempDiv = document.createElement("div");
					tempDiv.innerHTML = caption; // phpcs:ignore
					let childElements = tempDiv.getElementsByTagName("mdspan");
					for (let i = 0; i < childElements.length; i++) {
						if (elIDRemove === childElements[i].attributes.datatext.value) {
							// Change logic to keep other HTML Tag in content..only remove mdspan tag
	
							var parent = childElements[i].parentNode;
	
							while (childElements[i].firstChild) {
								parent.insertBefore(
									childElements[i].firstChild,
									childElements[i]
								); // phpcs:ignore
							}
							parent.removeChild(childElements[i]);
							image.caption = tempDiv.innerHTML;
							wp.data.dispatch("core/editor").updateBlockAttributes(clientId, {
								images: blockAttributes.images.map((img) =>
									img.id === image.id ? { ...img, caption: image.caption } : img
								),
							});
	
							break;
						}
					}
				});
			}
		});
	}
	
	/**
	 * Removes the 'mdspan' tags from the content of table cells in a table block.
	 *
	 * This function iterates through the 'head', 'body', and 'foot' sections of a table block, and for each cell, it removes any 'mdspan' tags that match the provided 'elIDRemove' parameter.
	 *
	 * @param {Object} blockAttributes - The attributes of the table block.
	 * @param {string} clientId - The ID of the table block.
	 * @param {string} elIDRemove - The ID of the 'mdspan' element to be removed.
	 */
	function removeTableTag(blockAttributes, clientId, elIDRemove) {
		let table_attrb = ["head", "body", "foot"];
		table_attrb.forEach(function (attrb) {
			blockAttributes[attrb]?.forEach((tableCells) => {
				var cells = tableCells.cells;
				cells.forEach(function (data) {
					var content = data.content;
	
					if ("" !== content) {
						let tempDiv = document.createElement("div");
						tempDiv.innerHTML = content; // phpcs:ignore
						let childElements = tempDiv.getElementsByTagName("mdspan");
						for (let i = 0; i < childElements.length; i++) {
							if (elIDRemove === childElements[i].attributes.datatext.value) {
								// Change logic to keep other HTML Tag in content..only remove mdspan tag
	
								var parent = childElements[i].parentNode;
	
								while (childElements[i].firstChild) {
									parent.insertBefore(
										childElements[i].firstChild,
										childElements[i]
									); // phpcs:ignore
								}
	
								parent.removeChild(childElements[i]);
								data.content = tempDiv.innerHTML;
								wp.data.dispatch("core/editor").updateBlockAttributes(clientId, {
									[attrb]: blockAttributes[attrb].map((tc) =>
										tc === tableCells
											? {
													...tc,
													cells: tc.cells.map((cell) =>
														cell === data
															? { ...cell, content: data.content }
															: cell
													),
												}
											: tc
									),
								});
							}
						}
					}
				});
			});
		});
	}
	
	function deepCopy(obj) {
		if (obj) {
			return JSON.parse(JSON.stringify(obj));
		} else {
			return obj;
		}
	}
	
	function getCommentsLimit() {
		return 5;
	}
	
 /**
 * Formats a timestamp into a human-readable time ago string.
 *
 * This function takes a timestamp and converts it into a string that represents the time since that timestamp, such as "few seconds ago", "about a minute ago", or "2 minutes ago". It also handles cases where the timestamp is from today or yesterday.
 *
 * @param {number} time - The timestamp to format.
 * @returns {string} - A human-readable time ago string.
 */
function timeAgo(time) {
		try {
			/* for time formats of time in seconds and minutes */
			var templates = {
				prefix: "",
				suffix: wp.i18n.__(" ago", "content-collaboration-inline-commenting"),
				seconds: wp.i18n.__(
					"few seconds",
					"content-collaboration-inline-commenting"
				),
				minute: wp.i18n.__(
					"about a minute",
					"content-collaboration-inline-commenting"
				),
				minutes: wp.i18n.__(
					"%d minutes",
					"content-collaboration-inline-commenting"
				),
			};
			/* for time format like hrs + today */
			var forhrsToday = function (timeInHrs) {
				return (
					timeInHrs +
					" " +
					wp.i18n.__("Today", "content-collaboration-inline-commenting")
				);
			};
			var template = function (t, n) {
				return (
					templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)))
				);
			};
	
			if (!time) return;
			/* function for converting timestamp into required format */
			var convertedDatetime = function (date) {
				date = new Date(date * 1000);
				let dateFormat = "m/d/Y";
				let timeFormat = "H:i:s";
				let dateTime = wp.date.gmdate(dateFormat + " " + timeFormat, date);
				return dateTime;
			};
			let newtime;
			var convertedTime = convertedDatetime(time);
			time = new Date(convertedTime * 1000 || convertedTime);
			var now = new Date(convertedDatetime(getTimestampWithTimezone()));
			var timeinDate = String(time).split(" ");
			var dispTime = String(time);
			dispTime = dispTime.match(/\s([A-z]*)\s[0-9]{1,2}/g);
			dispTime =
				time.toLocaleString("en-US", {
					minute: "numeric",
					hour: "numeric",
					hour12: true,
				}) + dispTime[0];
			if (timeinDate[2] != now.getDate()) {
				if (now.getTime() - time.getTime() < 0) {
					return dispTime;
				} else {
					if (
						timeinDate[2] < now.getDate() &&
						parseInt(now.getDate()) - parseInt(timeinDate[2]) < 2
					) {
						newtime = time.toLocaleString("en-US", {
							minute: "numeric",
							hour: "numeric",
							hour12: true,
						});
						newTimeString =
							translateTimeString(newtime) +
							" " +
							wp.i18n.__("Yesterday", "content-collaboration-inline-commenting");
						return newTimeString;
					} else {
						return dispTime;
					}
				}
			} else {
				if (now.getTime() - time.getTime() < 0) {
					newtime = time.toLocaleString("en-US", {
						minute: "numeric",
						hour: "numeric",
						hour12: true,
					});
					newTimeString =
						translateTimeString(newtime) +
						" " +
						wp.i18n.__("Today", "content-collaboration-inline-commenting");
					return newTimeString;
				} else {
					var seconds = ((now.getTime() - time) * 0.001) >> 0;
					var minutes = seconds / 60;
					var hrsFormat = translateTimeString(
						time.toLocaleString("en-US", {
							minute: "numeric",
							hour: "numeric",
							hour12: true,
						})
					);
					return (
						templates.prefix +
						((seconds < 60 && template("seconds", seconds)) ||
							(minutes < 60 && template("minutes", minutes)) ||
							(minutes > 60 && forhrsToday(hrsFormat))) +
						(minutes < 60 ? templates.suffix : "")
					);
				}
			}
		} catch (error) {
			console.log(error);
		}
	} 
 
/**
 * Creates a new "attributes" object to update, based on the passed attribute name and final content
 *
 * @param string attributeName The custom Gutenberg block attribute name to be changed
 * @param string finalContent The final content generated by cleaning out the string from mdspan
 * @return {object}
 */
function createNewAttributeWithFinalContent(attributeName, finalContent) {
		const conf = {
			attributes: {},
		};
		conf.attributes[attributeName] = finalContent;
		return conf;
	}  

	function emailList(appendTo, data) {
		setTimeout(function () {
			var listItem = "";
			if (data.length > 0) {
				data.forEach(function (user, listIndex) {
					if (listIndex == 0) {
						listItem += `
											<li class="cf-user-list-item active" role="option" data-user-id="${
												user.ID
											}" data-email="${user.user_email}" data-display-name="${
							user.display_name
						}" data-full-name="${user.full_name}">
													<img src="${user.avatar}" alt="${
							user.display_name
						}" width="24" height="24" />
													<div class="cf-user-info">
															<p class="cf-user-display-name">${
																user.display_name
															} <small class="cf-user-role">${userroleDisplay(
							user.role
						)}</small></p>
													</div>
											</li>`;
					} else {
						listItem += `
											<li class="cf-user-list-item" role="option" data-user-id="${
												user.ID
											}" data-email="${user.user_email}" data-display-name="${
							user.display_name
						}" data-full-name="${user.full_name}">
													<img src="${user.avatar}" alt="${
							user.display_name
						}" width="24" height="24" />
													<div class="cf-user-info">
															<p class="cf-user-display-name">${
																user.display_name
															} <small class="cf-user-role">${userroleDisplay(
							user.role
						)}</small></p>
													</div>
											</li>`;
					}
				});
				var emailList = `
									<div class="cf-mentioned-user-popup">
											<ul class="cf-system-user-email-list" role="listbox" tabindex="0">
													${listItem}
											</ul>
									</div>
							`;
				emailList = DOMPurify.sanitize(emailList);
				document
					.querySelector(appendTo)
					?.insertAdjacentHTML("afterend", emailList);
			}
		}, 100);
	}

// Make matched text highlighted.
function makeMatchedTextHighlighted(term, markEmail, markName) {
		term = term.substring(1);
		var markEmailElement = document.querySelector(markEmail);
		var markNameElement = document.querySelector(markName);
	
		if (term) {
			if (markEmailElement) {
				mark(markEmailElement, term);
			}
			if (markNameElement) {
				mark(markNameElement, term);
			}
		}
	}
	function mark(element, term) {
		var innerHTML = element.innerHTML;
		var regex = new RegExp(`(${term})`, "gi");
		element.innerHTML = innerHTML.replace(regex, "<mark>$1</mark>");
	}
	
	function getCaretPosition(editableDiv) {
		var caretPos = 0,
			sel;
		if (window.getSelection) {
			sel = window.getSelection();
	
			if (sel.rangeCount) {
				range = sel.getRangeAt(0);
				if (range.commonAncestorContainer.parentNode === editableDiv) {
					caretPos = range.endOffset;
				} else if (
					range.commonAncestorContainer.ownerDocument.activeElement ===
					editableDiv
				) {
					caretPos = sel.focusOffset;
				}
			}
		} else if (document.selection && document.selection.createRange) {
			range = document.selection.createRange();
			if (range.parentElement() === editableDiv) {
				var tempEl = document.createElement("span");
				editableDiv.insertBefore(tempEl, editableDiv.firstChild); // phpcs:ignore
				var tempRange = range.duplicate();
				tempRange.moveToElementText(tempEl);
				tempRange.setEndPoint("EndToEnd", range);
				caretPos = tempRange.text.length;
			}
		}
		return caretPos;
	}
// Insert Display Name.
function insertDisplayName(setRange, email, userId, fullName, displayName) {
		var gapElContent = document.createTextNode("\u00A0"); // Adding whitespace after the name.
		var anchor = document.createElement("a");
	
		var splitDisplayName = displayName.split(" ");
		anchor.setAttribute("contenteditable", false);
		anchor.setAttribute("href", `mailto:${email}`);
		anchor.setAttribute("title", fullName);
		anchor.setAttribute("data-email", email);
		anchor.setAttribute("class", "js-mentioned");
		anchor.setAttribute("data-display-name", displayName.substr(1));
		anchor.setAttribute("data-user-id", userId);
		var anchorContent = document.createTextNode(splitDisplayName[0]);
		anchor.appendChild(anchorContent);
		setRange.insertNode(anchor);
		anchor.after(gapElContent); // phpcs:ignore
	}
	// Cases when we should show the suggestion list.
	function showSuggestion(tracker) {
		var allowedStrings = ["", "@", ";", ">"];
		if (allowedStrings.includes(tracker)) {
			return true;
		}
		return false;
	}
// Chrome, Edge Clearfix.
function chromeEdgeClearFix(typedContent) {
		if (typedContent) {
			typedContent = typedContent.replace(/(<div>)/gi, "<br>");
			typedContent = typedContent.replace(/(<\/div>)/gi, "");
		}
		return typedContent;
	}
	function assignThisToUser() {
		let el = "";
		const parentBoardClass = ".cls-board-outer";
		const appendTo = ".cf-share-comment";
		const mentionedEmail = ".cf-system-user-email-list li";
		const checkBoxContainer = ".cf-assign-to";
	
		// Grab the current board ID.
		document.body.addEventListener(
			"focus",
			function (event) {
				if (event.target.matches(appendTo)) {
					el = event.target.closest(parentBoardClass).id;
				}
			},
			true
		);
	
		// On Suggested Email Click.
		document.body.addEventListener("click", function (event) {
			if (event.target.closest(mentionedEmail)) {
				const target = event.target.closest(mentionedEmail);
				const thisUserId = target?.dataset?.userId;
				const thisDisplayName = target?.dataset?.displayName;
				const thisUserEmail = target?.dataset?.email;
				const currentBoardAssinger = document.querySelector(
					`#${el} .cf-board-assigned-to`
				)?.dataset?.userId;
				const assigntoText = currentBoardAssinger
					? wp.i18n.__("Reassign to", "content-collaboration-inline-commenting")
					: wp.i18n.__("Assign to", "content-collaboration-inline-commenting");
	
				const assignToElement = document.querySelector(
					`#${el} ${checkBoxContainer}`
				);
	
				if (!assignToElement) {
					let checkbox = `
									<div class="cf-assign-to">
									<div class="cf-assign-to-inner">
											<label for="${el}-cf-assign-to-user">
													<input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(
						wp.i18n.__("%1$s %2$s", "content-collaboration-inline-commenting"),
						assigntoText,
						thisDisplayName
					)}</i>
											</label>
											<span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
									</div>
									<span class="assignMessage">${wp.i18n.__(
										"Your @mention will add people to this discussion and send an email.",
										"content-collaboration-inline-commenting"
									)}</span>     
									</div>`;
					const currentBoardAssingerID = document.querySelector(
						`#${el} .cf-board-assigned-to`
					)?.dataset?.userId;
	
					if (thisUserId !== currentBoardAssingerID) {
						if (el !== "") {
							const checkBoxContainerElement = document.querySelector(
								`#${el} ${checkBoxContainer}`
							);
	
							if (
								checkBoxContainerElement?.children.length <= 1 ||
								null === checkBoxContainerElement
							) {
								if (checkBoxContainerElement) {
									checkBoxContainerElement.innerHTML = "";
								}
								checkbox = DOMPurify.sanitize(checkbox);
								const targetElement = document.querySelector(
									`#${el} ${appendTo}`
								);
								targetElement.insertAdjacentHTML("afterend", checkbox);
							}
						}
					}
				}
	
				// Change assignee message when checkbox selected
				document.addEventListener("click", function (event) {
					if (event.target.matches(`#${el}-cf-assign-to-user`)) {
						const checked = document.querySelector(
							`#${el} .cf-assign-to-user`
						).checked;
						document.querySelector(`#${el} .assignMessage`).textContent = checked
							? wp.i18n.__(
									"The Assigned person will be notified and responsible for marking as done.",
									"content-collaboration-inline-commenting"
								)
							: wp.i18n.__(
									"Your @mention will add people to this discussion and send an email.",
									"content-collaboration-inline-commenting"
								);
					}
				});
			}
		});
	
		// Paste comment text with check assign user
		document.body.addEventListener("keyup", handleCommentEvent);
		document.body.addEventListener("paste", handleCommentEvent);
	
		function handleCommentEvent(event) {
			if (event.target.matches(".js-cf-share-comment, .js-cf-edit-comment")) {
				if (
					document.querySelector(".js-cf-share-comment a.js-mentioned") ||
					document.querySelector(".js-cf-edit-comment a.js-mentioned")
				) {
					const thisUserId = event.target
						.closest(".shareCommentContainer")
						?.querySelector("a.js-mentioned")?.dataset?.userId;
					const thisDisplayName = event.target
						.closest(".shareCommentContainer")
						?.querySelector("a.js-mentioned")?.dataset?.displayName;
					const thisUserEmail = event.target
						.closest(".shareCommentContainer")
						?.querySelector("a.js-mentioned")?.dataset?.email;
					const currentBoardAssinger = document.querySelector(
						`#${el} .cf-board-assigned-to`
					)?.dataset?.userId;
					const assigntoText = currentBoardAssinger
						? wp.i18n.__("Reassign to", "content-collaboration-inline-commenting")
						: wp.i18n.__("Assign to", "content-collaboration-inline-commenting");
	
					const assignToElement = document.querySelector(
						`#${el} ${checkBoxContainer}`
					);
					if (!assignToElement) {
						let checkbox = `
											<div class="cf-assign-to">
											<div class="cf-assign-to-inner">
													<label for="${el}-cf-assign-to-user">
															<input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(
							wp.i18n.__("%1$s %2$s", "content-collaboration-inline-commenting"),
							assigntoText,
							thisDisplayName
						)}</i>
													</label>
													<span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span> 
											</div>
											<span class="assignMessage">${wp.i18n.__(
												"Your @mention will add people to this discussion and send an email.",
												"content-collaboration-inline-commenting"
											)}</span>     
											</div>`;
						const currentBoardAssingerID = document.querySelector(
							`#${el} .cf-board-assigned-to`
						)?.dataset?.userId;
						if (thisUserId !== currentBoardAssingerID) {
							if (el !== "") {
								const checkBoxContainerElement = document.querySelector(
									`#${el} ${checkBoxContainer}`
								);
								if (checkBoxContainerElement?.children?.length <= 1) {
									checkBoxContainerElement.innerHTML = "";
									checkbox = DOMPurify.sanitize(checkbox);
									const targetElement = document.querySelector(
										`#${el} ${appendTo}`
									);
									targetElement.insertAdjacentHTML("afterend", checkbox);
								}
							}
						}
					}
	
					// Change assignee message when checkbox selected
					document.addEventListener("click", function (event) {
						if (event.target.matches(`#${el}-cf-assign-to-user`)) {
							const checked = document.querySelector(
								`#${el} .cf-assign-to-user`
							).checked;
							document.querySelector(`#${el} .assignMessage`).textContent =
								checked
									? wp.i18n.__(
											"The Assigned person will be notified and responsible for marking as done.",
											"content-collaboration-inline-commenting"
										)
									: wp.i18n.__(
											"Your @mention will add people to this discussion and send an email.",
											"content-collaboration-inline-commenting"
										);
						}
					});
				}
			}
		}
	
		// On Assignable Email Click.
		document.body.addEventListener("click", function (event) {
			if (event.target.closest(".cf-assignable-list li")) {
				const target = event.target.closest(".cf-assignable-list li");
				if (target.closest(parentBoardClass).classList.contains("cm-board")) {
					el = target.closest(parentBoardClass).id;
					const thisUserId = target?.dataset?.userId;
					const thisUserEmail = target?.dataset?.email;
					const thisDisplayName = target?.dataset?.displayName;
					const currentBoardAssingerID = document.querySelector(
						`#${el} .cf-board-assigned-to`
					)?.dataset?.userId;
					const assigntoText = currentBoardAssingerID
						? wp.i18n.__("Reassign to", "content-collaboration-inline-commenting")
						: wp.i18n.__("Assign to", "content-collaboration-inline-commenting");
	
					const checkbox = `
											<div class="cf-assign-to-inner">
													<label for="${el}-cf-assign-to-user">
															<input id="${el}-cf-assign-to-user" data-user-email="${thisUserEmail}" class="cf-assign-to-user" name="cf_assign_to_user" type="checkbox" value="${thisUserId}" /><i>${sprintf(
						wp.i18n.__("%1$s %2$s", "content-collaboration-inline-commenting"),
						assigntoText,
						thisDisplayName
					)}</i>
													</label>
													<span class="js-cf-show-assign-list dashicons dashicons-arrow-down-alt2"></span>
											</div>    
											<span class="assignMessage">${wp.i18n.__(
												"Your @mention will add people to this discussion and send an email.",
												"content-collaboration-inline-commenting"
											)}</span>  
									`;
	
					const checkboxFragments = document
						.createRange()
						.createContextualFragment(checkbox);
					const appendToSelector = document.querySelector(`#${el} .cf-assign-to`);
					appendToSelector.innerHTML = "";
					appendToSelector.appendChild(checkboxFragments);
					document.querySelector(`#${el} .cf-assignable-list-popup`)?.remove();
				}
			}
		});
	}
	function assignableList(_self, data) {
		let listItem = "";
		if (data.length > 0) {
			listItem += `<ul class="cf-assignable-list">`;
			data.forEach((user) => {
				listItem += `
							<li data-user-id="${user.ID}" data-email="${
					user.user_email
				}" data-display-name="${user.display_name}">
									<img src="${user.avatar}" alt="${
					user.display_name
				}" width="24" height="24" />
									<div class="cf-user-info">
											<p class="cf-user-display-name">${
												user.display_name
											} <small class="cf-user-role">${userroleDisplay(
					user.role
				)}</small></p>
									</div>
							</li>
							`;
			});
			listItem += `</ul>`;
		} else {
			listItem += `<strong class="cf-no-assignee"> ${wp.i18n.__(
				"Sorry! No user found!",
				"content-collaboration-inline-commenting"
			)} </strong>`;
		}
		let assignListTemplate = `
					<div class="cf-assignable-list-popup">
							${listItem}
					</div>
			`;
		assignListTemplate = DOMPurify.sanitize(assignListTemplate);
		setTimeout(() => {
			const assignListTargetElement = document.querySelector(
				`${_self} .cf-assign-to-inner`
			);
			assignListTargetElement.insertAdjacentHTML("afterend", assignListTemplate);
		}, 200);
	}
  // Show Assignable Email List
	function showAssignableEmailList() {
	  const triggerLink = ".js-cf-show-assign-list";
	  const parentBoardClass = ".cls-board-outer";

	  document.body.addEventListener("click", function (e) {
	    if (e.target.closest(triggerLink)) {
	      e.preventDefault();
	      
	      const el = e.target.closest(parentBoardClass).id;
	      const textarea = `#${el} .js-cf-share-comment`;
	      const appendTo = `#${el} .shareCommentContainer .cf-assign-to`;
	      const content = document.querySelector(textarea).innerHTML;

	      e.target.classList.remove("js-cf-show-assign-list");
	      e.target.classList.add("js-cf-hide-assign-list");

	      const assignableListPopup = document.querySelector(
	        `#${el} .cf-assignable-list-popup`
	      );
	      if (assignableListPopup) {
	        assignableListPopup.remove();
	      }else{
	        // Get the assigner id of the current board.
	      const currentBoardAssingerID = document.querySelector(
	        `#${el} .cf-board-assigned-to`
	      )?.dataset?.userId;

	      // Checked cached user list first.
	      let emailSet = content.match(
	        /[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/gim
	      );
	      emailSet = new Set(emailSet);
	      const emailAddresses = Array.from(emailSet);
	      const dataItems = [];

	      // Send Ajax Request.
	      fetch(ajaxurl, {
	        method: "POST",
	        headers: {
	          "Content-Type": "application/x-www-form-urlencoded",
	        },
	        body: new URLSearchParams({
	          action: "cf_get_assignable_user_list",
	          content: content,
	          nonce: adminLocalizer.nonce,
	        }),
	      })
	        .then((response) => response.json())
	        .then((data) => {
	          emailAddresses.forEach((email) => {
	            const pattern = new RegExp(email);
	            data.forEach((item) => {
	              const userEmail = item.user_email;
	              const isMatched = userEmail.match(pattern);
	              if (isMatched && item.ID !== currentBoardAssingerID) {
	                dataItems.push(item);
	              }
	            });
	          });
	          assignableList(appendTo, dataItems);
	        });
	      }

	      
	    }
	  });
	}

	function createAutoEmailMention() {
		let el = "";
		let currentBoardID = "";
		let currentCommentBoardID = "";
		let typedText = "";
		let trackedStr = "";
		let isEmail = false;
		let createTextarea = "";
		let appendIn = "";
		let assignablePopup = "";
		let editLink = "";
		let doingAjax = false;
		const keysToAvoid = [
			"Enter",
			"Tab",
			"Shift",
			"Control",
			"Alt",
			"CapsLock",
			"Meta",
			"ArrowLeft",
			"ArrowRight",
			"ArrowUp",
			"ArrowDown",
		];
		const currentPostID = document.getElementById("post_ID")?.value;
		const parentBoardClass = ".cls-board-outer";
		let mood = "create";
	
		// Grab the current board ID.
		document.body.addEventListener("click", function (event) {
			if (event.target.closest(parentBoardClass)) {
				el = event.target.closest(parentBoardClass).id;
				currentBoardID = `#${el}`;
				appendIn = `${currentBoardID} .cf-mentioned-user-popup`;
				assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
				editLink = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
				mood = "create";
				if ("create" === mood && "" === createTextarea) {
					createTextarea = `${currentBoardID} .js-cf-share-comment`;
				}
			}
		});
	
		if ("" === el) {
			document.body.addEventListener(
				"focus",
				function (event) {
					if (event.target.closest(".shareCommentContainer")) {
						el = event.target.closest(parentBoardClass).id;
						currentBoardID = `#${el}`;
						appendIn = `${currentBoardID} .cf-mentioned-user-popup`;
						assignablePopup = `${currentBoardID} .cf-assignable-list-popup`;
						editLink = `${currentBoardID} .comment-actions .buttons-wrapper .js-edit-comment`;
						mood = "create";
						if ("create" === mood) {
							createTextarea = `${currentBoardID} .js-cf-share-comment`;
						}
					}
				},
				true
			);
		}
	
		document.body.addEventListener(
			"focus",
			function (event) {
				if (event.target.matches(".js-cf-edit-comment")) {
					mood = "edit";
					el = event.target.closest(parentBoardClass).id;
					currentCommentBoardID = event.target?.id;
					if ("edit" === mood) {
						if (currentCommentBoardID) {
							createTextarea = `#${currentCommentBoardID}.js-cf-edit-comment`;
						}
					}
				}
			},
			true
		);
	
		if (navigator.userAgent.toLowerCase().indexOf("firefox") === -1) {
			document.body.addEventListener("keydown", function (event) {
				if (
					event.target.matches(".cf-share-comment") &&
					event.keyCode === 13 &&
					!event.shiftKey
				) {
					document.execCommand("insertHTML", false, "<br><br>");
					event.preventDefault();
				}
			});
		}
	
		document.addEventListener("click", function (event) {
			if (
				event.target.matches(
					`${currentBoardID} .js-cancel-comment, ${currentBoardID} .save-btn`
				)
			) {
				document
					.querySelectorAll(`${currentBoardID} .cf-assign-to`)
					.forEach((el) => el.remove());
			}
		});
	
		document.body.addEventListener("click", function (event) {
			if (typeof editLink !== "undefined" && editLink !== "") {
				if (event.target.matches(editLink)) {
					document.querySelectorAll(appendIn).forEach((el) => el.remove());
					document.querySelectorAll(assignablePopup).forEach((el) => el.remove());
				}
			}
		});
	
		let mentioncounter = 0;
		document.body.addEventListener("click", function (event) {
			if (
				event.target.closest(
					".cls-board-outer, .commentInnerContainer .btn-comment, .cf-share-comment-wrapper .btn, .block-editor-writing-flow .is-root-container"
				)
			) {
				mentioncounter = 0;
				removeFloatingIcon();
			}
		});
	
		jQuery(document.body).on('keyup keypress', createTextarea, function (e) {

	        var _self = jQuery(createTextarea);
	        var that = this;
	        typedText = _self.html();

	        // Clearing out any junk left when clearing the textarea.
	        if ('<br>' === _self.html() || '&nbsp;' === _self.html()) {

	            typedText = '';
	            jQuery(createTextarea).html('');
	        }

	        // Removing assignable checkbox if that user's email is not in the content or removed.

	        if (undefined !== typedText && typedText.length > 0) {
	            var assignCheckBoxId = `${currentBoardID}-cf-assign-to-user`;
	            var emailSet = typedText.match(/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/igm);
	            emailSet = new Set(emailSet);
	            var emailAddresses = Array.from(emailSet);

	            // Get the assigner email of the current board.
	            var currentBoardAssingerEmail = jQuery(`${currentBoardID} .cf-board-assigned-to`).data('user-email');

	            if (undefined !== emailAddresses && emailAddresses.length > 0) {
	                if (assignCheckBoxId.length > 0) {
	                    var assignCheckBoxUserEmail = jQuery(assignCheckBoxId).attr('data-user-email');

	                    let checkEmailPattern = new RegExp(assignCheckBoxUserEmail, 'igm');
	                    let isThere = typedText.match(checkEmailPattern);
	                    if (!isThere && !doingAjax) {

	                        doingAjax = true;
	                        var appendInCheckbox = [];
	                        jQuery.ajax({
	                            url: ajaxurl, // eslint-disable-line
	                            type: 'post',
	                            data: {
	                                action: 'cf_get_user_email_list',
	                                postID: currentPostID,
	                                nonce: adminLocalizer.nonce, // eslint-disable-line
	                            },
	                            beforeSend: function () { },
	                            success: function (res) {
	                                jQuery(appendIn).remove(); // Remove previous DOM.
	                                jQuery(assignablePopup).remove(); // Remove previous DOM.
	                                var data = JSON.parse(res);

	                                data.forEach(function (item) {
	                                    if (currentBoardAssingerEmail === emailAddresses[0]) {
	                                        if (item.user_email === emailAddresses[1]) {
	                                            appendInCheckbox.push(item);
	                                        }
	                                    } else {
	                                        if (item.user_email === emailAddresses[0]) {
	                                            appendInCheckbox.push(item);
	                                        }
	                                    }

	                                })
	                                if (appendInCheckbox.length > 0) {
	                                    jQuery(assignCheckBoxId).prop('checked', false);
	                                    jQuery(assignCheckBoxId).data('user-email', appendInCheckbox[0].user_email)
	                                    jQuery(assignCheckBoxId).val(appendInCheckbox[0].ID);

	                                    // code - added by meet - solution of assign to when delete
	                                    jQuery(assignCheckBoxId).next('i').text(`${sprintf(wp.i18n.__('Assign to %s', 'content-collaboration-inline-commenting'), appendInCheckbox[0].display_name)}`);
	                                }

	                            }
	                        })

	                    }
	                }
	            } else {
	                jQuery(`${currentBoardID} .cf-assign-to`).remove();
	            }

	            // Remove assigner dom if there is no email in the editor.
	            var findEmails = typedText.match(/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i);

	            if (!findEmails || undefined === findEmails) {
	                jQuery(`${currentBoardID} .cf-assign-to`).remove();
	            }

	            // Removing assigner checkbox if it is matched with current board assignees email.
	            if (emailAddresses.length === 1 && emailAddresses[0] === currentBoardAssingerEmail) {
	                jQuery(`${currentBoardID} .cf-assign-to`).remove();
	            }

	        }

	        // If textarea is blank then remove email list.
	        if (undefined !== typedText && typedText.length <= 0) {
	            jQuery(appendIn).remove();
	            jQuery(assignablePopup).remove();
	            jQuery('.cf-assign-to').remove();
	        }

	        if (typedText && typedText.length > 0) {
	            var refinedText = typedText.replace(/<br>/igm, '');
	            typedText = refinedText;
	        }

	        // Handling space. As if someone types space with no intention to write email.
	        // So we make isEmail false and trackedStr to blank.
	        if (32 === e.which) {
	            isEmail = false;
	            trackedStr = '';
	        }

	        // Get current cursor position.
	        var el = jQuery(createTextarea).get(0);
	        cursorPos = getCaretPosition(el);

	        var keynum;
	        if (window.event) { // IE                  
	            keynum = e.keyCode;
	        } else if (e.which) { // Netscape/Firefox/Opera                 
	            keynum = e.which;
	        }

	        if ('@' === String.fromCharCode(keynum) || '@' === e.key || 50 === e.which && (typedText && typedText.length > 0) && jQuery(createTextarea).is(':focus') === true && '2' !== e.key) { 
	            doingAjax = true;
	            // Fetch all email list.
	            doingAjax = false;
	            var prevCharOfEmailSymbol;
	            mentioncounter++;
	            var showSuggestionFunc;

	            if (undefined !== typedText) {
	                prevCharOfEmailSymbol = typedText.substr(-1, 1);
	                if ('@' === prevCharOfEmailSymbol) {
	                    showSuggestionFunc = showSuggestion(prevCharOfEmailSymbol);
	                } else {
	                    var index = typedText.indexOf("@");
	                    var preText = typedText.charAt(index);
	                    if (preText.indexOf(" ") > 0 || preText.length > 0) {
	                        var words = preText.split(" ");
	                        var prevWords = (words[words.length - 1]);
	                    }
	                    if ('@' === prevWords || ('keypress' === e.type && '@' === String.fromCharCode(keynum))) {
	                        prevWords = '@';
	                        showSuggestionFunc = showSuggestion(prevWords);
	                    }
	                }
	            }

	            if (showSuggestionFunc && mentioncounter <= 1 && !doingAjax) {
	                if ('keypress' === e.type || 'keyup' === e.type) {
	                    mentioncounter = 0;
	                }
	                const actBoardID = currentBoardID.replace('#', '#txt');
                  if ('keypress' === e.type && ( true === jQuery(createTextarea).is(':focus') || true === jQuery(actBoardID).is(':focus') ) ) {
	                    isEmail = true;

	                    jQuery.ajax({
	                        url: ajaxurl, // eslint-disable-line
	                        type: 'post',
	                        data: {
	                            action: 'cf_get_user_email_list',
	                            postID: currentPostID,
	                            nonce: adminLocalizer.nonce, // eslint-disable-line
	                        },
	                        beforeSend: function () { },
	                        success: function (res) {
	                            jQuery(appendIn).remove(); // Remove previous DOM.
	                            jQuery(assignablePopup).remove(); // Remove previous DOM.
	                            var data = JSON.parse(res);
	                            emailList(createTextarea, data);
	                            mentioncounter = 0;  // Issue solved for adding 2 mentions consecutively without space.
	                        }
	                    })
	                }
	            } else {
	                jQuery(appendIn).remove();
	                jQuery(assignablePopup).remove();
	            }
	        }

	        if ('keypress' !== e.type && 'Backspace' === e.key && (typedText && typedText.length > 0)) {
	            let currentTextAt = typedText.substr(-1, 1);
	            var sel = document.getSelection();
	            var selNodeChar = sel?.baseNode?.data?.charAt(sel.anchorOffset - 1) || sel?.anchorNode?.data?.charAt(sel.anchorOffset - 1);
	            var startTextAt = sel?.baseNode?.data?.charAt(0) || sel?.anchorNode?.data?.charAt(0); 

	            if ('@' === startTextAt || '@' === currentTextAt || '@' === typedText.charAt(cursorPos - 1) || '@' === selNodeChar) {
	                isEmail = true;

	                jQuery.ajax({
	                    url: ajaxurl, // eslint-disable-line
	                    type: 'post',
	                    data: {
	                        action: 'cf_get_user_email_list',
	                        postID: currentPostID,
	                        nonce: adminLocalizer.nonce, // eslint-disable-line
	                    },
	                    beforeSend: function () { },
	                    success: function (res) {
	                        jQuery(appendIn).remove(); // Remove previous DOM.
	                        jQuery(assignablePopup).remove(); // Remove previous DOM.
	                        var data = JSON.parse(res);
	                        emailList(createTextarea, data);
	                        mentioncounter = 0;  // Issue solved for adding 2 mentions consecutively without space.
	                    }
	                })
	            }
	            // resolved git issue #920
	            if (sel.rangeCount > 0) {
	                var range = sel.getRangeAt(0);
	                if (range.startContainer.nodeType === Node.TEXT_NODE) {
	                    var parent = range.startContainer.parentElement;
	                    if (parent.tagName.toLowerCase() === 'a' && parent.className === 'js-mentioned') {
	                        while (parent.firstChild) {
	                            parent.removeChild(parent.firstChild);
	                        }
	                    }
	                }
	            }
	        }

	        if ((32 === e.which) || (13 === e.which) || (8 === e.which)) {
	            mentioncounter = 0;
	        }

	        if ('Backspace' === e.key) {
	            if (!jQuery(createTextarea).text()) {
	                trackedStr = '';
	            }
	        }

	        if (true === isEmail && (typedText && typedText.length > 0) && jQuery(createTextarea).is(':focus') === true) {
	            var checkKeys = function (key) {
	                if (key === e.key) {
	                    return true;
	                }
	                return false;
	            }
	            if (!keysToAvoid.find(checkKeys)) {

	                // Check for backspace.
	                if ('keypress' !== e.type) {
	                    if ('Backspace' === e.key) {
	                        let prevCharOfEmailSymbol = typedText.substr(-1, 1);
	                        trackedStr = (prevCharOfEmailSymbol === '@') ? '@' : trackedStr.slice(0, -1);
	                    } else if (50 === e.which) {
	                        if ('@' !== trackedStr) {
	                            trackedStr += '@';
	                        }
	                    } else {
	                        if (50 !== e.which) {
	                            trackedStr += e.key;
	                        }
	                    }
	                }
	            }
	            if (13 === e.which) {
	                jQuery(appendIn).remove();
	                jQuery(assignablePopup).remove();
	            }
	            doingAjax = false;
	            if ('@' !== trackedStr && jQuery(createTextarea).is(':focus') === true) {

	                let checkEmailSymbol = trackedStr.match(/^@\w+$/ig);
	                if (checkEmailSymbol && cursorPos != 0) {
	                    var refinedCachedusersList = [];
	                    let niddle = trackedStr.substr(1);
	                    if ('' !== niddle && niddle.length > 2) {
	                        // Sending Ajax Call to get the matched email list(s).
	                        jQuery.ajax({
	                            url: ajaxurl, // eslint-disable-line
	                            type: 'post',
	                            data: {
	                                action: 'cf_get_matched_user_email_list',
	                                niddle: trackedStr,
	                                postID: currentPostID,
	                                nonce: adminLocalizer.nonce, // eslint-disable-line
	                            },
	                            success: function (res) {
	                                jQuery(appendIn).remove(); // Remove user list popup DOM.
	                                jQuery(assignablePopup).remove(); // Remove assignable user list popup DOM.
	                                var data = JSON.parse(res);
	                                emailList(createTextarea, data);
	                                makeMatchedTextHighlighted(trackedStr, '.cf-user-email', '.cf-user-display-name');
	                            }
	                        })
	                    }
	                } else {
	                    jQuery(appendIn).remove(); // Remove user list popup DOM.
	                    jQuery(assignablePopup).remove(); // Remove assignable user list popup DOM.
	                }
	            }

	            if (!trackedStr || '' === trackedStr) {
	                jQuery(appendIn).remove();
	                jQuery(assignablePopup).remove();
	            }
	        }

	        if (32 === e.which) {
	            jQuery(appendIn).remove();
	            jQuery(assignablePopup).remove();
	        }
	  	});
	
		document.body.addEventListener("keydown", function (event) {
			if (event.target.matches(".cf-share-comment")) {
				if ([38, 40].indexOf(event.keyCode) > -1) {
					event.preventDefault();
				}
				if (event.which === 40) {
					document
						.querySelectorAll("li.cf-user-list-item.active")
						.forEach((el) => el.classList.remove("active"));
					document
						.querySelector(".cf-system-user-email-list")
						?.querySelector(".cf-user-list-item:nth-child(2)")
						.classList.add("active");
					document.querySelector(".cf-share-comment")?.blur();
					document.querySelector(".cf-system-user-email-list")?.focus();
				}
			}
		});
	
		document.body.addEventListener("keydown", function (e) {
			const popupSelector = ".cf-mentioned-user-popup .cf-system-user-email-list";
			const userListItemSelector = ".cf-user-list-item";
			const activeItemSelector = "li.cf-user-list-item.active";
	
			if (e.target.closest(popupSelector)) {
				const popup = e.target.closest(popupSelector);
				const userListItems = popup.querySelectorAll(userListItemSelector);
				const firstIndex = 0;
				const lastIndex = userListItems.length - 1;
				let index = Array.from(userListItems).findIndex((item) =>
					item.classList.contains("active")
				);
	
				if ([38, 40].includes(e.keyCode)) {
					e.preventDefault();
				}
	
				switch (e.which) {
					case 38: // Up arrow
						index = index === firstIndex ? lastIndex : index - 1;
						document.querySelector(".cf-system-user-email-list").focus();
						e.stopPropagation();
						break;
					case 40: // Down arrow
						index = index === lastIndex ? 0 : index + 1;
						document.querySelector(".cf-system-user-email-list").focus();
						break;
					case 13: // Enter key
						const selectedItem = userListItems[index];
						const fullName = selectedItem.getAttribute("data-full-name");
						const displayName =
							"@" + selectedItem.getAttribute("data-display-name");
						const email = selectedItem.getAttribute("data-email");
						const userId = selectedItem.getAttribute("user-id");
	
						// Insert Display Name.
						insertDisplayName(
							range,
							email,
							userId,
							fullName,
							displayName,
							createTextarea
						);
	
						const createTextareaText = document.querySelector(createTextarea);
						let typedContent = createTextareaText.innerHTML;
						typedContent = typedContent.replace(/@<a/g, "<a");
						var browserType = browser(window.navigator.userAgent.toLowerCase());
						if ("firefox" !== browserType) {
							typedContent = chromeEdgeClearFix(typedContent);
						}
						let refinedContent = typedContent.replace(
							/(^@|\s@)([a-z0-9]\w*)/gim,
							" "
						);
						refinedContent = refinedContent.replace(/@\w+<a/gim, " <a");
						const fragments = document
							.createRange()
							.createContextualFragment(refinedContent);
						const getCurrentTextAreaID = createTextareaText.id;
						const currentTextAreaNode =
							document.getElementById(getCurrentTextAreaID);
						currentTextAreaNode.innerHTML = "";
						currentTextAreaNode.appendChild(fragments);
						//document.querySelector(appendIn)?.remove();
						//document.querySelector(assignablePopup)?.remove();
				        jQuery(appendIn).remove(); // Remove previous DOM.
				        jQuery(assignablePopup).remove(); // Remove previous DOM.
						trackedStr = "";
	
						// Setup the caret position after appending the Display Name.
						const children = currentTextAreaNode.lastElementChild;
						if (children && children.tagName === "BR") {
							currentTextAreaNode.removeChild(children);
						}
						e.preventDefault();
	
						const selectChild = currentTextAreaNode.childNodes.length - 1;
						const el = currentTextAreaNode.childNodes[selectChild];
						const cursorSel = window.getSelection();
						range = cursorSel.getRangeAt(0);
	
						// Ensure the offset is within the valid range
						const offset = Math.min(range.startOffset, el.textContent.length);
						range.setStart(el, offset);
						range.collapse(true);
						cursorSel.removeAllRanges();
						cursorSel.addRange(range);
						break;
				}
	
				userListItems.forEach((item) => item.classList.remove("active"));
				userListItems[index].classList.add("active");
			}
		});
	
		document.body.addEventListener("click", handleUserListClick);
		document.body.addEventListener("keypress", handleUserListClick);
	
		function handleUserListClick(e) {
			if (e.target.closest(".cf-system-user-email-list li")) {
				e.stopPropagation();
	
				if (e.which === 1) {
					const target = e.target.closest(".cf-system-user-email-list li");
					const fullName = target.getAttribute("data-full-name");
					const displayName = "@" + target.getAttribute("data-display-name");
					const email = target.getAttribute("data-email");
					const userId = target.getAttribute("user-id");
					// Insert Display Name.
					insertDisplayName(
						range,
						email,
						userId,
						fullName,
						displayName,
						createTextarea
					);
					const createTextareaText = document.querySelector(createTextarea);
					let typedContent = createTextareaText.innerHTML;
					if (typedContent) {
						typedContent = typedContent.replace(/@<a/g, "<a");
						typedContent = typedContent.replace(/<\/?span .[^>]*>/g, "");
						typedContent = typedContent.replaceAll("</span>", ""); // phpcs:ignore
	
						var browserType = browser(window.navigator.userAgent.toLowerCase());
						if ("firefox" !== browserType) {
							typedContent = chromeEdgeClearFix(typedContent);
						}
	
						let refinedContent = typedContent.replace(
							/(^@|\s@)([a-z0-9]\w*)/gim,
							" "
						);
						refinedContent = refinedContent.replace(/@\w+<a/gim, "<a");
						const fragments = document
							.createRange()
							.createContextualFragment(refinedContent);
						const getCurrentTextAreaID = createTextareaText.id;
	
						const currentTextAreaNode =
							document.getElementById(getCurrentTextAreaID);
						currentTextAreaNode.innerHTML = "";
						currentTextAreaNode.appendChild(fragments);
						//document.querySelector(appendIn)?.remove();
						//document.querySelector(assignablePopup)?.remove();
				        jQuery(appendIn).remove(); // Remove previous DOM.
				        jQuery(assignablePopup).remove(); // Remove previous DOM.
						trackedStr = "";
	
						// Setup the caret position after appending the Display Name.
						const children = currentTextAreaNode.lastElementChild;
						if (children && children.tagName === "BR") {
							currentTextAreaNode.removeChild(children);
						}
	
						const selectChild = currentTextAreaNode.childNodes.length - 1;
						const el = currentTextAreaNode.childNodes[selectChild];
						const cursorSel = window.getSelection();
						range = cursorSel.getRangeAt(0);
	
						// Ensure the offset is within the valid range
						const offset = Math.min(range.startOffset, el.textContent.length);
						range.setStart(el, offset);
						range.collapse(true);
						cursorSel.removeAllRanges();
						cursorSel.addRange(range);
					}
				}
			}
		}
	}

/**
 * Detects the browser type based on the user agent string.
 * @returns {string} The name of the detected browser.
 */
 function browser(agent) {
	 switch (true) {
		 case agent.indexOf("edge") > -1:
			 return "MS Edge (EdgeHtml)";
		 case agent.indexOf("edg") > -1:
			 return "MS Edge Chromium";
		 case agent.indexOf("opr") > -1 && !!window.opr:
			 return "opera";
		 case agent.indexOf("chrome") > -1 && !!window.chrome:
			 return "chrome";
		 case agent.indexOf("trident") > -1:
			 return "Internet Explorer";
		 case agent.indexOf("firefox") > -1:
			 return "firefox";
		 case agent.indexOf("safari") > -1:
			 return "safari";
		 default:
			 return "other";
	 }
 }
// Function to add border to alignment blocks based on certain class conditions
function addBorderToAlignmentBlock(block) {
		// Select all child elements inside the block
		var children = block.querySelectorAll("*");
	
		// Loop through each child to check if it contains any specific class
		for (var i = 0; i < children.length; i++) {
			if (
				children[i].classList.contains("alignupdate") ||
				children[i].classList.contains("textalignupdate") ||
				children[i].classList.contains("lockupdate") ||
				children[i].classList.contains("headingupdate")
			) {
				// If any of the conditions match, add a border to the block
				block.style.border = "2px solid #188651";
				break; // Exit the loop as we only need to add the border once
			}
		}
	}
	
	// Function to update and apply border styles to table rows and cells based on certain conditions
	function addBorderStyle() {
		// Helper function to apply the border style updates
		function updateBorderStyle() {
			// Select all table rows
			var tableRows = document.querySelectorAll("tr");
	
			// Loop through each row
			tableRows.forEach(function (row) {
				var hasBodyRowUpdate = false; // Flag to check if 'bodyupdate-row' span is found
				var hasBodyDeleteRowUpdate = false; // Flag for delete row updates
				var tableCells = row.querySelectorAll("td"); // Get all cells in the current row
	
				// Loop through each cell in the row
				tableCells.forEach(function (cell) {
					// Check for specific spans inside the cell to determine the class to add/remove
					var spanBodyRowUpdate = cell.querySelector("span.bodyupdate-row");
					var spanBodyDeleteRowUpdate = cell.querySelector(
						"span.bodyupdate-delete-row"
					);
					var spanBodyDeleteColumnUpdate = cell.querySelector(
						"span.bodyupdate-delete-column"
					);
					var spanBodyColumnUpdate = cell.querySelector("span.bodyupdate-column");
					var spanColumnAlign = cell.querySelector("span.columnalign");
	
					// Set flags based on the presence of specific spans
					if (spanBodyRowUpdate) {
						hasBodyRowUpdate = true;
					}
					if (spanBodyDeleteRowUpdate) {
						hasBodyDeleteRowUpdate = true;
					}
	
					// Add or remove class based on the presence of respective spans
					if (spanBodyColumnUpdate) {
						cell.classList.add("cf-bodycolumnupdate");
					} else {
						cell.classList.remove("cf-bodycolumnupdate");
					}
	
					if (spanColumnAlign) {
						cell.classList.add("cf-columnalign");
					} else {
						cell.classList.remove("cf-columnalign");
					}
	
					if (spanBodyDeleteColumnUpdate) {
						cell.classList.add("cf-bodycolumndelete");
					} else {
						cell.classList.remove("cf-bodycolumndelete");
					}
				});
	
				// Add or remove the 'cf-bodyrowupdate' class to the row based on the flag
				if (hasBodyRowUpdate) {
					row.classList.add("cf-bodyrowupdate");
				} else {
					row.classList.remove("cf-bodyrowupdate");
				}
	
				// Add or remove the 'cf-bodyrowdeleteupdate' class based on the delete row update flag
				if (hasBodyDeleteRowUpdate) {
					row.classList.add("cf-bodyrowdeleteupdate");
				} else {
					row.classList.remove("cf-bodyrowdeleteupdate");
				}
			});
		}
	
		// Call the update function to apply the styles
		updateBorderStyle();
	}