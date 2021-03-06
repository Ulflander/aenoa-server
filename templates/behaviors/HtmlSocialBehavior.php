<?php

class HtmlSocialBehavior extends Behavior {
	
	static public $LIKES = array (
		'facebook' => '<iframe src="//www.facebook.com/plugins/like.php?href=URI&amp;send=false&amp;layout=button_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:21px;" allowTransparency="true"></iframe>',
		'vkontakte' => '<div id="vk_like"></div><script type="text/javascript">VK.Widgets.Like("vk_like", {type: "mini"});</script>'
	);
	
	function getLikeButton ( $url = null, $type = 'facebook' )
	{
		if ( !ake ( $type , self::$LIKES) )
		{
			throw new ErrorException('This type of like button does not exists') ;
		}
		
		return str_replace ( 'URI' , urlencode($url) , self::$LIKES[$type] ) ;
		
	}
	
}

?>
