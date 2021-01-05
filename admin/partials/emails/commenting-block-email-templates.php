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
            .comment-box .comment-header .commenter-name-time{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
            .comment-box .comment-header .commenter-name{font-size:18px;font-family:Arial,serif;margin-right:10px;}
            .comment-box .comment-header .commenter-name-time .comment-time{font-size:12px;font-weight:400;font-family:Arial,serif;color:#808080;}
            .comment-box .author-name{margin:0 0 5px;}
            .comment-box .comment,
            .comment-box .author-comment{font-family:Arial,serif;font-size:14px;}
            .comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
            .comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:15px;}
            .comment-box .comment-assigned-to{margin-bottom:20px;}
            .comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;}
            .comment-box ul{margin:0;padding:0;list-style:none;}
            .comment-box ul li{margin-bottom:20px;}
            .comment-box .latest-comment{margin:0 0 15px;}
        </style>
        <div class='comment-box'>
            <div class='comment-box-header'>
                <p><span class='commenter-name'>{$args['commenter']}</span> - mentioned you in a comment in the following page.</p>
                <h2 class='comment-page-title'><a href='{$args['post_edit_link']}' target='_blank'>{$args['post_title']}</a></h2>
                <p class='open-comment'>Open - {$open_comment_count} Comment(s)</p>
            </div>
            <div class='comment-box-body'>
                <h2 class='latest-comment'>{icon} {$args['thread']} </h2>
                <div class='commented_text'>{$args['commented_text']}</div>
                <div class='comment-assigned-to'>Assigned to <span class='commenter-name'>John Doe</span></div>
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