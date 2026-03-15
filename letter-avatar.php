<?php
/**
 * Plugin Name: letter-avatar
 * Version: 1.0.0
 * Plugin URI: https://github.com/SSzzPP/letter-avatar-plugin
 * Description: Letter Avatar Plugin, If no Gravatar avatar is set, the first character of the nickname will be used as the avatar.
 */

if(!function_exists('letter_avatar')) {
    
    // Generate a color list for avatars
    $AVATAR_COLORS = array(
        '#e57373', '#f06292', '#ba68c8', '#64b5f6', '#64b5f6', '#4db6ac', '#81c784', '#fff176', '#ffb74d', '#ff8a65',
        '#a1887f', '#d7ccc8', '#cfd8dc', '#fbc02d', '#f57f17', '#c2185b', '#8e24aa', '#5e35b1', '#3949ab', '#1976d2'
    );

    function get_html_tag_attribute($html, $attribute, $default = '') {
        if(preg_match('/'.$attribute.'\s*=\s*[\"\']([^\"\']*)[\"\']/isU', $html, $result)) {
            if(isset($result[1])) return $result[1];
        }
        return $default;
    }

    function get_avatar_name($id_or_email) {
        if(have_comments()) {
            return get_comment_author();
        }
        
        $user = null;
        if(empty($id_or_email)) {
            return null;
        } else if(is_object($id_or_email)) {
            if(!empty($id_or_email->comment_author)) {
                return $id_or_email->comment_author;
            } else if(!empty($id_or_email->user_id)) {
                $id = (int) $id_or_email->user_id;
                $user = get_user_by('id', $id);
            }
        } else if(is_numeric($id_or_email)) {
            $id = (int) $id_or_email;
            $user = get_user_by('id', $id);
        } else if(is_string($id_or_email)) {
            if (!filter_var($id_or_email, FILTER_VALIDATE_EMAIL)) {
                return $id_or_email;
            } else {
                $user = get_user_by('email', $id_or_email);
            }
        }
        if(!empty($user) && is_object($user)) {
            return $user->display_name;
        }
        return null;
    }
    
    function letter_avatar($avatar, $id_or_email = '', $size = '40', $default = '', $alt = '') { 
        $src = get_html_tag_attribute($avatar, 'src');
        if(!$src) return $avatar;
        
        $alt = get_avatar_name($id_or_email);
        $alt = $alt? $alt: get_html_tag_attribute($avatar, 'alt', $alt);
        if(!$alt) return $avatar;
        
        $src = htmlspecialchars_decode($src);
        $src = preg_replace('/[\?\&]d[^&]+/is', '', $src);
        $src = $src.'&d=404';
        $src = htmlspecialchars($src);
        
        $class = get_html_tag_attribute($avatar, 'class', 'avatar avatar-'.$size.' photo');
        $title = get_html_tag_attribute($avatar, 'title', $alt);
        
        $avatar = '<img src="'.$src.'" alt="'.$alt.'" title="'.$title.'" class="'.$class.' letter-avatar" height="'.$size.'" width="'.$size.'" onerror="onerror=null;src=\'\';src=letterAvatar(alt,'.$size.')" />';
        return $avatar;
    }

    global $pagenow;

    if(!is_admin() || $pagenow != 'options-discussion.php') {
        add_filter('get_avatar', 'letter_avatar', 999999, 5);
	}
    
    /**
     * LetterAvatar https://github.com/daolavi/LetterAvatar
     * Artur Heinze
     * Create Letter avatar based on Initials
     * based on https://gist.github.com/leecrossley/6027780
     */
    function letter_avatar_js() {
        global $AVATAR_COLORS;
        ?><style>.letter-avatar[src=""]{visibility: hidden;}</style><script>(function(a,b){window.letterAvatar=function(d,l,j){d=d||"";l=l||60;var h="<?php echo implode(' ', $AVATAR_COLORS); ?>".split(" "),f,c,k,g,e,i;f=String(d);f=f.replace(/\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDE4F]/g,"");f=f?f.charAt(0):"?";if(a.devicePixelRatio){l=(l*a.devicePixelRatio)}c=(f=="?"?72:f.charCodeAt(0))-64;k=c%h.length;g=b.createElement("canvas");g.width=l;g.height=l;e=g.getContext("2d");e.fillStyle=j?j:h[k-1];e.fillRect(0,0,g.width,g.height);e.font=Math.round(g.width/2)+"px 'Microsoft Yahei'";e.textAlign="center";e.fillStyle="#fff";e.fillText(f,l/2,l/1.5);i=g.toDataURL();g=null;return i}})(window,document);</script><?php
    }

    add_action('wp_head',    'letter_avatar_js');
    
    add_action('admin_head', 'letter_avatar_js');
    
}
