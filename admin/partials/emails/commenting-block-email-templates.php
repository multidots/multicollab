<?php
/**
 * Provides the email templates.
 *
 * This file is used to create the related email templates.
 *
 * @link       #
 * @since      1.1.0
 *
 * @package    content-collaboration-inline-commenting.
 */

class Commenting_Block_Email_Templates {

    /**
     * Limiting the characters of a string.
     *
     * @param string $string The string that is going to be limiting.
     * @param integer $limit The limiting value.
     * @return void
     */
    public function cf_limit_characters( $string, $limit = 100 ) {
        return strlen( $string ) > $limit ? substr( $string, 0, $limit ) . '...' : $string;
    }

    /**
     * Add Comment / Reply Comment Email Template.
     *
     * @param array $args Contains all keys related to send the email.
     * @return void
     */
    public function cf_add_comment_email_template( $args ) {
       $allowed_tags = [
               'a' => [ 'id' => [], 'title' => [], 'href' => [], 'target'=> [], 'style' => [], 'class' => [], 'data-email' => [], 'contenteditable' => [],
           ]
       ];
       $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
       preg_match_all( $pattern, $args['thread'], $matches );
    
       if( ! empty( $args['list_of_comments'] ) ) {
           $comment_list_html = '<ul class="comment-list">';
           foreach( $args['list_of_comments'] as $comment ) {
               $user_role = ucwords( $comment['userRole'] );
               $comment_list_html .= "
                   <li>
                       <div class='comment-box-wrap'>
                           <div class='avtar'>
                               <img src='".esc_url( $comment['profileURL'] )."' alt='".esc_attr( $comment['userName'] )."'/>
                           </div>
                           <div class='comment-details'>
                               <div class='commenter-name-role'>
                                   <h3 class='commenter-name'>".esc_html( $comment['userName'] )."</h3>
                                   <span class='commenter-role'>( ".esc_html( $user_role )." )</span>
                               </div>
                               <div class='comment'>".wp_kses( $comment['thread'], $allowed_tags )."</div>
                           </div>
                       </div>
                   </li>
               ";
           }
           $comment_list_html .= '</ul>';
       }
    
       $assigned_to_who = '';
       if( ! empty( $args['assign_to'] ) ) {
           $assinged_user   = get_user_by( 'email', $args['assign_to'] );
           $assigned_to_who = "
               <div class='comment-assigned-to'>
                   <span class='icon-assign'>
                       <svg id='Group_31' data-name='Group 31' xmlns='http://www.w3.org/2000/svg' width='19.644' height='20' viewBox='0 0 19.644 20'>
                           <g id='Group_28' data-name='Group 28' transform='translate(2.21)'>
                           <path id='Path_11' data-name='Path 11' d='M149.786,160.469a10.107,10.107,0,0,1-7.123-2.907.885.885,0,0,1,0-1.279.885.885,0,0,1,1.275,0,8.254,8.254,0,0,0,5.78,2.439,7.905,7.905,0,0,0,5.776-2.436,8.236,8.236,0,0,0,0-11.632,8.253,8.253,0,0,0-5.779-2.438,8.032,8.032,0,0,0-5.779,2.438,1.047,1.047,0,0,1-1.255.018.771.771,0,0,1-.29-.564.949.949,0,0,1,.269-.73,9.992,9.992,0,0,1,7.126-2.909,10.107,10.107,0,0,1,7.124,2.907,9.761,9.761,0,0,1,2.912,7.128,10.1,10.1,0,0,1-2.907,7.124A9.619,9.619,0,0,1,149.786,160.469Z' transform='translate(-142.388 -140.469)' fill='#6ac359'/>
                           </g>
                           <g id='Group_29' data-name='Group 29' transform='translate(0 9.055)'>
                           <path id='Path_12' data-name='Path 12' d='M141.088,151.342a.909.909,0,1,1,0-1.818h5.727a.909.909,0,1,1,0,1.818Z' transform='translate(-140.178 -149.524)' fill='#6ac359'/>
                           </g>
                           <g id='Group_30' data-name='Group 30' transform='translate(4.564 4.705)'>
                           <path id='Path_13' data-name='Path 13' d='M148.645,155.834a.844.844,0,0,1-.638-.271.884.884,0,0,1,0-1.276l2.945-2.945h-5.3a.909.909,0,0,1,0-1.818h5.159l-2.8-2.8a.884.884,0,0,1,0-1.276.884.884,0,0,1,1.276,0l4.492,4.492a.8.8,0,0,1,.2.566.845.845,0,0,1-.271.639l-4.421,4.42A.841.841,0,0,1,148.645,155.834Z' transform='translate(-144.742 -145.174)' fill='#6ac359'/>
                           </g>
                       </svg>
                   </span>
                   Assigned to <a href='mailto:".sanitize_email( $assinged_user->user_email )."' title='".esc_attr( $assinged_user->display_name )."' class='commenter-name'>@".esc_html( $assinged_user->first_name )."</a>
               </div>
           ";
       }
    
       $template = "
           <style>
               .comment-box{background:#fff;-webkit-box-sizing:border-box;box-sizing:border-box;width:70%;font-family:Arial,serif;margin:40px 0 0;}
               .comment-box *{-webkit-box-sizing:border-box;box-sizing:border-box;}
               .comment-box a{color:#4B1BCE;text-decoration:none;}
               .comment-box .comment-box-header{margin-bottom:30px;border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
               .comment-box .comment-box-header p{margin:0 0 20px;}
               .comment-box .comment-box-header .comment-page-title{font-size:20px;margin:0;}
               .comment-box .comment-box-header a{color:#4B1BCE;display:inline-block;}
               .comment-box .comment-page-title a{text-decoration:underline;font-size:20px;}
               .comment-box .comment-box-wrap{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:start;-ms-flex-align:start;align-items:flex-start;width:100%;margin-bottom:20px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
               .comment-box .comment-box-wrap:last-child{margin-bottom:0;}
               .comment-box .avtar{width:40px;margin-right:10px;}
               .comment-box .avtar img{max-width:100%;border-radius:50%;}
               .comment-box .comment-details{margin-right:0;width:60%;width:calc(100% - 55px);}
               .comment-box .comment-box-wrap .commenter-name-role{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;margin-bottom:7px;-ms-flex-wrap:wrap;flex-wrap:wrap;}
               .comment-box .comment-box-wrap .commenter-name{font-size:18px;font-family:Roboto,Arial,sans-serif;margin:0 7px 0 0;color:#141414;font-weight:600;}
               .comment-box .comment-box-wrap .commenter-role{font-size:14px;font-weight:400;font-family:Arial,serif;color:#4C5056;margin-right:10px;}
               .comment-box .comment{font-family:Arial,serif;font-size:14px;color:#4C5056;}
               .comment-box .comment-box-body{border:1px solid rgb(0 0 0 / 0.1);border-radius:20px;padding:30px;}
               .comment-box .commented_text{background-color:#F8F8F8;border:1px solid rgb(0 0 0 / 0.1);font-size:16px;padding:20px;border-radius:8px;border-left:5px solid #4B1BCE;margin-bottom:20px;color:#4C5056;}
               .comment-box .comment-assigned-to{margin-bottom:20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
               .comment-box .comment-assigned-to .commenter-name{color:#4B1BCE;margin-left:5px;}
               .comment-box .comment-assigned-to .icon-assign{margin-right:5px;line-height:1;}
               .comment-box ul{margin:0 0 20px;padding:0;list-style:none;}
               .comment-box ul li{margin-bottom:20px;}
               .comment-box ul li:last-child{margin-bottom:10px;}
               .comment-box .head-with-icon{margin:0 0 20px;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;font-family:Roboto,Arial,sans-serif;font-weight:600;}
               .comment-box .head-with-icon .icon-comment{margin-right:10px;line-height:1;}
               .comment-box .head-with-icon .icon-resolved{margin-right:10px;}
               .comment-box .head-with-icon h3{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
               .comment-box .cf-marked-resolved-by{margin:0 10px 20px 0;display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-align:center;-ms-flex-align:center;align-items:center;-ms-flex-wrap:wrap;flex-wrap:wrap;}
               .comment-box .cf-marked-resolved-by .icon-resolved{margin-right:5px;line-height:1;}
               .comment-box .cf-marked-resolved-by a{margin-left:5px;}
               .comment-box.new-comment .comment-list li:last-child .commenter-name-role:after{content:'New';padding:5px 10px;background-color:#4B1BCE;color:#fff;font-size:12px;}
               .comment-box .view_reply{margin:10px 0;}
               .comment-box .view_reply_btn{display:inline-block;padding:15px 25px;font-size:20px;background-color:#4B1BCE;border-radius:8px;color:#fff;}
               .comment-box .view_reply_btn a{text-decoration:underline;color:#fff;}
               @media (max-width:1400px){.comment-box{width:90%;}}
           </style>
           <div class='comment-box new-comment'>
               <div class='comment-box-header'>
                   <p><span class='commenter-name'>".esc_html( $args['commenter'] )."</span> - mentioned you in a comment in the following page.</p>
                   <h2 class='comment-page-title'><a href='".esc_url( $args['post_edit_link'] )."' target='_blank'>".esc_html( $args['post_title'] )."</a></h2>
               </div>
               <div class='comment-box-body'>
                   <h2 class='head-with-icon'>
                       <span class='icon-comment'>
                           <svg xmlns='http://www.w3.org/2000/svg' width='36.226' height='43.02' viewBox='0 0 36.226 43.02'>
                               <g id='Group_2' data-name='Group 2' transform='translate(-36.242 1.019)'>
                                   <path id='Path_1' data-name='Path 1' d='M64.607,30.769,52.29,40l0-5.88-1.37-.279a17.1,17.1,0,1,1,13.683-3.072Z' transform='translate(0 0)' fill='none' stroke='#4b1bce' stroke-width='2'/>
                               </g>
                           </svg>
                       </span>
                       Comments
                   </h2>
                   <div class='commented_text'>".esc_html( $args['commented_text'] )."</div>
                   {$assigned_to_who}
                   {$comment_list_html}
                   <div class='view_reply'>
                       <div class='view_reply_btn'><a href='".esc_url( $args['post_edit_link'] )."'>Click here</a> - View or reply to this comment</div>
                   </div>
               </div>
           </div>
       ";
    
       // Limit the page and site titles for Subject.
       $post_title = $this->cf_limit_characters( $args['post_title'], 30 );
       $site_name  = $this->cf_limit_characters( $args['site_name'], 20 );
    
       if( ! empty( $args['assign_to'] ) ) {
           $key = array_search( $args['assign_to'], $matches[0] );
           unset( $matches[0][$key] );
       }
    
       // Notify Site Admin if setting enabled.
       $cf_admin_notif = get_option( 'cf_admin_notif' );
    
       if ( ! empty( $matches[0] ) ) {
           $to      = $matches[0];
           $subject = "New Comment - ".esc_html( $post_title )." - ".esc_html( $site_name );
           $body    = $template;
           $headers = 'Content-Type: text/html; charset=UTF-8';
           // Add admin email to notify when email is sent.
           if ( '1' === $cf_admin_notif ) {
               $to[] = get_option( 'admin_email');
           }
           wp_mail( $to, $subject, $body, $headers );
       }
    
       if( ! empty( $args['assign_to'] ) ) {
           $assign_to      = $args['assign_to'];
           $assign_subject = "Assigned to you - ".esc_html( $post_title );
           $assign_body    = $template;
           $headers        = 'Content-Type: text/html; charset=UTF-8';
           wp_mail( $assign_to, $assign_subject, $assign_body, $headers );
       }
    }
}

