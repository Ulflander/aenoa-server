<?php

/**
 * The AeSocialShare class helps you using sharing services.
 * 
 * @author xavier
 *
 */
class AeSocialShare {

	/**
	 * Contains codes for 'I Like' widgets  
	 * @var array
	 */
	static public $LIKES = array (
		'facebook' => '<iframe src="http://www.facebook.com/plugins/like.php?href=URI&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=arial&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>'
	);
	
	/**
	 * Contains codes for 'Share' widgets  
	 * @var array
	 */
	static public $SHARES = array (
	    'facebook' => array
		(
	            'share_url' => 'http://www.facebook.com/sharer.php?u=URI&amp;t=TITLE',
				'title' => 'Facebook',
				'influence' => array('fr','us', 'is', 'no', 'dk','ca','sg','hk','gb','au','cl','worldwide')
		),
	    'twitter' => array
		(
	            'share_url' => 'http://twitter.com/home/?status=TITLE+@URI',
				'title' => 'Twitter',
				'influence' => array ('fr','us','gb','worldwide')
		),
		'baidu' => array (
				'share_url' => 'http://www.douban.com/recommend/?url=URI&title=TITLE',
				'title'=> '百度搜藏',
				'influence' => array('cn','tw')
		),
		'bebo' => array (
				'share_url' => 'http://www.bebo.com/c/share?Url=URI',
				'title' => 'Bebo',
				'influence' => array('ir','nz')
		),
	    'delicious' => array (
	            'share_url' => 'http://delicious.com/post?url=URI&amp;title=TITLE',
				'title' => 'Del.icio.us',
				'influence' => array('worldwide')
		),
	    'digg' => array (
	            'share_url' => 'http://digg.com/submit?url=URI&amp;title=TITLE&amp;bodytext=EXCERPT',
				'title' => 'Digg',
				'influence' => array ('worldwide')
		),
		'douban' => array (
				'share_url' => 'http://www.douban.com/recommend/?url=URI&title=TITLE',
				'title'=> '豆瓣',
				'influence' => array('cn','tw')
		),
	    'google-buzz' => array
		(
	            'share_url' => 'http://www.google.com/buzz/post?url=URI&amp;title=TITLE',
				'title' => 'Google Buzz',
				'influence' => array ('worldwide')
		),
		'kaixin001' => array (
				'share_url' => 'http://www.kaixin001.com/repaste/share.php?rtitle=TITLE&rurl=URI',
				'title'=> '开心网',
				'influence' => array('cn','tw')
		),
	    'linkedin' => array
		(
	            'share_url' => 'http://www.linkedin.com/shareArticle?mini=true&amp;url=URI&amp;title=TITLE&amp;&amp;summary=EXCERPT',
				'title' => 'LinkedIn',
				'influence' => array ('worldwide')
		),
	    'mixx' => array
		(
	            'share_url' => 'http://www.mixx.com/submit?page_url=URI',
				'title' => 'Mixx',
				'influence' => array ('worldwide')
		),
	    'myspace' => array
		(
	            'share_url' => 'http://www.myspace.com/Modules/PostTo/Pages/?u=URI',
				'title' => 'MySpace',
				'influence' => array('au', 'it', 'gr','worldwide')
		),
	    'orkut' => array
		(
	            'share_url' => 'http://promote.orkut.com/preview?nt=orkut.com&amp;du=URI&amp;tt=TITLE',
				'title' => 'Orkut',
				'influence' => array('br', 'in')
		),
		'qq' => array 
		(
				'share_url' => 'http://shuqian.qq.com/post?from=3&title=TITLE&uri=URI&jumpback=2&noui=1',
				'title' => 'QQ书签',
				'influence' => array('cn','tw')
		),
	    'reddit' => array
		(
	            'share_url' => 'http://www.reddit.com/submit?url=URI&amp;title=TITLE',
				'title' => 'reddit',
				'influence' => array ('worldwide')
		),
		'renren' => array (
				'share_url' => 'http://share.renren.com/share/buttonshare.do?link=URI&title=TITLE',
				'title'=> '人人网',
				'influence' => array('cn','tw')
		),
	    'stumble' => array
		(
	            'share_url' => 'http://www.stumbleupon.com/submit?url=URI&amp;title=TITLE',
				'title' => 'Stumble Upon',
				'influence' => array ('worldwide')
		),
	    'technorati' => array
		(
	            'share_url' => 'http://technorati.com/faves?add=URI',
				'title' => 'Technorati',
				'influence' => array ('worldwide')
		),
	    'tumblr' => array
		(
	            'share_url' => 'http://www.tumblr.com/share?v=3&amp;u=URI&amp;t=TITLE',
				'title' => 'Tumblr',
				'influence' => array ('worldwide')
		),
	    'vkontakte' => array
		(
	            'share_url' => 'http://vkontakte.ru/share.php?url=URI&amp;title=TITLE&amp;description=EXCERPT',
				'title' => 'ВКонтакте',
				'influence' => array('ru')
		),
	    'xing' => array
		(
	            'share_url' => 'http://www.xing.com/app/user?op=share;url=URI',
				'title' => 'Xing',
				'influence' => array ('worldwide')
		),
	    'yahoo-buzz' => array
		(
	            'share_url' => 'http://buzz.yahoo.com/buzz?targetUrl=URI',
				'title' => 'Yahoo Buzz',
				'influence' => array ('worldwide')
		),

	);
	
	static public function mapShares ( $pageTitle = null , $pageURL = null , $excerpt = '' )
	{
		$geoloc = new AeGeoloc () ;
		$country = $geoloc->getCountry () ;
		
		if ( is_null($pageTitle) )
		{
			$pageTitle = Config::get ( App::APP_NAME ) ;
		}
		
		if ( is_null ( $pageURL ) ) {
			$pageURL = url() ;
		}
		
		$ret = array () ;
		$prioritized = array () ;
		$from = array ( 'URI' , 'TITLE' , 'EXCERPT' ) ;
		$to = array ( urlencode($pageURL) , urlencode($pageTitle) , urlencode($excerpt) ) ;
		
		foreach ( self::$SHARES as $k => $share )
		{
			$a = array (
				'title' => $share['title'] ,
				'share_url' => str_replace($from,$to, $share['share_url']) ,
			);
			
			$a['influent'] =  ( !empty( $share['influence'] ) && in_array ( $country , $share['influence'] ) ) ;
			
			if ( $a['influent'] === true )
			{
				$prioritized[$k] = $a ;
			} else if ( in_array ( 'worldwide' , $share['influence'] ) )
			{
				$ret[$k] = $a ;
			}
		}
		
		return array_merge ( $prioritized , $ret ) ;
	}
	
}
?>