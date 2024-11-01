<?php 
/**
* Google News Sitemap Generate XML
*/
class GNS_Generate
{
	public $sitemap;

	function __construct( $method )
	{
		switch ( $method ) {
			case 'sitemap':
				$this->get_xml();
				break;
			case 'create':
				$this->gns_generate();
				break;
		}
	}

	public function gns_generate()
	{
		$xml = new XMLWriter;

		$xml->openMemory();
		$xml->startDocument( '1.0' , 'UTF-8' );

		$xml->startElement( 'urlset' );
		$xml->writeAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		$xml->writeAttribute( 'xmlns:news', 'http://www.google.com/schemas/sitemap-news/0.9' );

		$news = $this->gns_get_news();
		foreach ( $news->publi as $id => $new ) {
			$xml->startElement( 'url' );
				$xml->writeElement( 'loc', $new->url );
				$xml->startElement( 'news:news' );
					$xml->startElement( 'news:publication' );
						$xml->writeElement( 'news:name' , 		$news->name );
						$xml->writeElement( 'news:language' , 	$news->lang );
					$xml->endElement();
					if ( $new->access ) {
						$xml->writeElement( 'news:access', 				$new->access );
					}
					if ( $new->genre ) {
						$xml->writeElement( 'news:genres', 				$new->genre );
					}
					$xml->writeElement( 'news:publication_date',	$new->date );
					$xml->writeElement( 'news:title', 				$new->title );
					if ( $new->keyword ) {
						$xml->writeElement( 'news:keywords', 			$new->keyword );
					}
					if ( $new->stock ) {
						$xml->writeElement( 'news:stock_tickers', 		$new->stock );
					}
				$xml->endElement();
			$xml->endElement();
		}

		$xml->endElement();

		$file = fopen( ABSPATH . 'news-sitemap.xml', 'w+' );
		fwrite( $file, $xml->outputMemory( true ) );
		fclose( $file );
	}

	public function get_xml()
	{
		$this->gns_generate();
		$file = esc_url( get_home_url() . '/news-sitemap.xml' );

		$this->sitemap = $file;
	}

	// Utils
	private function gns_get_news()
	{
		$news->name = $this->gns_view_field( 'gns_name' );
		$news->lang = $this->gns_view_field( 'gns_lang' );

		$posts = $this->gns_view_field( 'gns_posts' );
		$rs_news = new WP_Query( array(
			'post_type'			=> $posts,
			'posts_per_page'	=> -1
		));
		while ( $rs_news->have_posts() ) {
			$rs_news->the_post();

			$id = get_the_ID();
			if ( !get_post_meta( $id, 'gns_exclude', true ) ) {
				$news->publi->$id->url 		= get_permalink();
				$news->publi->$id->access 	= $this->gns_view_field( 'gns_access', $id );
				$news->publi->$id->genre 	= $this->gns_get_genres( $this->gns_view_field( 'gns_genre', $id ) );
				$news->publi->$id->title 	= get_the_title();
				$news->publi->$id->date 	= get_the_time( 'Y-m-d' );
				$news->publi->$id->keyword 	= $this->gns_view_field( 'gns_keyword', $id );
				$news->publi->$id->stock 	= $this->gns_view_field( 'gns_stock_tickers', $id );
			}
		}
		wp_reset_postdata();
		wp_reset_query();

		return $news;
	}

	private function gns_get_genres( $genre )
	{
		for ( $i=0; $i < count( $genre ); $i++ ) { 
			$genres .= ( $i == 0 ) ? $genre[ $i ] : ', ' . $genre[ $i ];
		}

		return $genres;
	}

	private function gns_view_field( $field, $post_id=null )
	{
		$post_meta 	= get_post_meta( $post_id, $field, true );
		$option 	= get_option( $field );
		if ( $post_meta ) {
			return $post_meta;
		} else if ( $option ) {
			return $option;
		} else {
			return $this->gns_set_defaut_field( $field );
		}
	}

	private function gns_set_defaut_field( $field )
	{
		switch ( $field ) {
			case 'gns_name':
				$default = get_bloginfo( 'title' );
				break;
			case 'gns_lang':
				$default = 'en';
				break;
			case 'gns_posts':
				$default = 'post';
				break;
			case 'gns_genre':
				$default = '';
				break;
			case 'gns_keyword':
				$default = '';
				break;
			case 'gns_access':
				$default = '';
				break;
			case 'gns_stock_tickers':
				$default = '';
				break;
		}

		return $default;
	}
}
?>