<?php
/**
 * Provides the email templates
 *
 * This file is used to create the related email templates.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    content-collaboration-inline-commenting
 */

 /**
  * Add Comment / Reply Comment Email Template.
  *
  * @param array $args Contains all keys related to send the email.
  * @return void
  */
function cf_add_comment_email_template( $args ) {
    $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
    preg_match_all( $pattern, $args['thread'], $matches );

    // Making email address linkable in list of comments
    $open_comment_count = count( $args['list_of_comments'] );

    if( ! empty( $args['list_of_comments'] ) ) {
        $comment_list_html = '<ul>';
        foreach( $args['list_of_comments'] as $comment ) {
            $comment['thread'] = $this->convert_str_to_email($comment['thread']);
            $comment_list_html .= "
                <li>
                    <div class='comment-header'>
                        <div class='avtar'>
                            <img src='{$comment['profileURL']}' alt='{$comment['userName']}'/>
                        </div>
                        <div class='comment-details'>
                            <h3 class='author-name'>{$comment['userName']}</h3>
                            <p class='author-comment'>{$comment['thread']}</p>
                        </div>
                        <a href='{$args['post_edit_link']}&comment_id={$comment['timestamp']}' target='_blank'>View</a>
                    </div>
                </li>
            ";
        }
        $comment_list_html .= '</ul>';
    }

    // Make email address linkable in email body.
    $args['thread'] = $this->convert_str_to_email( $args['thread'] );

    $template = "
        <style>
            .comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
            .comment-box *{-webkit-box-sizing:border-box;box-sizing:border-box;}
            .comment-box a{color:#4B1BCE;}
            .comment-box .comment-box-header{margin-bottom:30px;border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
            .comment-box .comment-box-header p{margin:15px 0;}
            .comment-box .comment-box-header .comment-page-title{font-size:20px;}
            .comment-box .comment-box-header a{color:#4B1BCE;text-decoration:underline;display:inline-block;font-size:20px;}
            .comment-box .comment-header{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
            .comment-box .comment-header:last-child{margin-bottom:0;}
            .comment-box .avtar{width:40px;margin-right:10px;}
            .comment-box .avtar img{max-width:100%;border-radius:50%;}
            .comment-box .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
            .comment-box .comment-header .commenter-name-role{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
            .comment-box .comment-header .commenter-name{font-size:18px;font-family:Roboto,Arial,sans-serif;margin-right:7px;color:#141414;font-weight:600;}
            .comment-box .comment-header .commenter-name-role .comment-role{font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;}
            .comment-box .author-name{margin:0 0 5px;}
            .comment-box .comment,
            .comment-box .author-comment{font-family:Arial,serif;font-size:14px;color:#4C5056;}
            .comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
            .comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:15px;color:#4C5056;}
            .comment-box .comment-assigned-to{margin-bottom:20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
            .comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;margin-left:5px;}
            .comment-box .comment-assigned-to .icon-assign{margin-right:5px;}
            .comment-box ul{margin:0;padding:0;list-style:none;}
            .comment-box ul li{margin-bottom:20px;}
            .comment-box .latest-comment{margin:0 0 20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;font-family:Roboto,Arial,sans-serif;font-weight:600;}
            .comment-box .latest-comment .icon-comment{margin-right:10px;}
            .comment-box .comment-box-header h3{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
        </style>
        <div class='comment-box'>
            <div class='comment-box-header'>
                <p><span class='commenter-name'>{$args['commenter']}</span> - mentioned you in a comment in the following page.</p>
                <h2 class='comment-page-title'><a href='{$args['post_edit_link']}' target='_blank'>{$args['post_title']}</a></h2>
                <p class='open-comment'>Open - {$open_comment_count} Comment(s)</p>
            </div>
            <div class='comment-box-body'>
                <h2 class='latest-comment'>
                <span class='icon-comment'>
                <svg xmlns='http://www.w3.org/2000/svg' width='36.226' height='43.02' viewBox='0 0 36.226 43.02'>
                  <g id='Group_2' data-name='Group 2' transform='translate(-36.242 1.019)'>
                    <path id='Path_1' data-name='Path 1' d='M64.607,30.769,52.29,40l0-5.88-1.37-.279a17.1,17.1,0,1,1,13.683-3.072Z' transform='translate(0 0)' fill='none' stroke='#4b1bce' stroke-width='2'/>
                  </g>
                </svg></span>
                {$args['thread']} </h2>
                <div class='commented_text'>{$args['commented_text']}</div>
                <div class='comment-assigned-to'>
                <span class='icon-assign'>
                <svg xmlns='http://www.w3.org/2000/svg' width='22' height='22' viewBox='0 0 22 22'>
                  <g id='Group_22' data-name='Group 22' transform='translate(1 1)'>
                    <circle id='Ellipse_4' data-name='Ellipse 4' cx='10' cy='10' r='10' fill='none' stroke='#6ac359' stroke-width='2'/>
                    <path id='Path_7' data-name='Path 7' d='M93.92,119.6l-3.593-3.664,1.353-1.327,2.252,2.3,5.621-5.621,1.34,1.34Z' transform='translate(-85.327 -105.288)' fill='#6ac359'/>
                  </g>
                </svg></span>
                Assigned to <span class='commenter-name'>John Doe</span></div>
                {$comment_list_html}
            </div>
        </div>
    ";

    // Limit the page and site titles for Subject.
    $post_title = $this->cf_limit_characters( $args['post_title'], 30 );
    $site_name  = $this->cf_limit_characters( $args['site_name'], 20 );

    if( ! empty( $args['assign_to'] ) ) {
        $key = array_search( $args['assign_to'], $matches[0] );
        unset($matches[0][$key]);
    }
    if ( ! empty( $matches[0] ) ) {
        $to      = $matches[0];
        $subject = "New Comment - {$post_title} - {$site_name}";
        $body    = $template;
        $headers = 'Content-Type: text/html; charset=UTF-8';
        wp_mail( $to, $subject, $body, $headers );
    }

    if( ! empty( $args['assign_to'] ) ) {
        $assign_to      = $args['assign_to'];
        $assign_subject = "Assgined to you";
        $assign_body    = $template;
        $headers        = 'Content-Type: text/html; charset=UTF-8';
        wp_mail( $assign_to, $assign_subject, $assign_body, $headers );
    }
}