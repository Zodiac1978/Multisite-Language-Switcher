<?php
/**
 * MslsOptionsTax
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.9.8
 */

/**
 * Taxonomy options
 * @package Msls
 */
class MslsOptionsTax extends MslsOptions {

	/**
	 * Separator
	 * @var string
	 */
	protected $sep = '_term_';

	/**
	 * Autoload
	 * @var string
	 */
	protected $autoload = 'no';

	/**
	 * Factory method
	 * @param int $id
	 * @return MslsOptionsTax
	 */
	public static function create( $id = 0 ) {
		if ( is_admin() ) {
			$obj = MslsContentTypes::create();

			$id  = (int) $id;
			$req = $obj->acl_request();
		}
		else {
			global $wp_query;

			$id  = $wp_query->get_queried_object_id();
			$req = ( is_category() ? 'category' : ( is_tag() ? 'post_tag' : '' ) );
		}

		if ( 'category' == $req ) {
			return new MslsOptionsTaxTermCategory( $id );
		}
		elseif ( 'post_tag' == $req ) {
			return new MslsOptionsTaxTerm( $id );
		}
		return new MslsOptionsTax( $id );
	}

	/**
	 * Get the queried taxonomy
	 * @return string
	 */
	public function get_tax_query() {
		global $wp_query;

		return(
			isset( $wp_query->tax_query->queries[0]['taxonomy'] ) ?
			$wp_query->tax_query->queries[0]['taxonomy'] :
			''
		);
	}

	/**
	 * Check and correct URL
	 * @param string $url
	 * @return string
	 */
	public function check_url( $url ) {
		if ( empty( $url ) || ! is_string( $url ) ) {
			return '';
		}

		/**
		 * The 'blog'-slug-problem :/
		 */
		if ( ! is_subdomain_install() ) {
			$count = 1;
			$url   = str_replace( home_url(), '', $url, $count );

			if ( is_main_site() ) {
				$parts = explode( '/%', get_option( 'permalink_structure' ), 2 );
				$url   = home_url( $parts[0] . $url );
			}
			else {
				$url = home_url( preg_replace( '|^/?blog|', '', $url ) );
			}
		}

		return $url;
	}

	/**
	 * Get postlink
	 * @param string $language
	 * @return string
	 */
	public function get_postlink( $language ) {
		if ( $this->has_value( $language ) ) {
			$link = $this->get_term_link( (int) $this->__get( $language ) );
			if ( ! empty( $link ) ) {
				return $this->check_url( $link );
			}
		}
		return '';
	}

	/**
	 * Get current link
	 * @return string
	 */
	public function get_current_link() {
		return $this->get_term_link( $this->get_arg( 0, '' ) );
	}

	/**
	 * Wraps the call to get_term_link
	 */
	public function get_term_link( $term_id ) {
		if ( ! empty( $term_id ) ) {
			$taxonomy = $this->get_tax_query();
			if ( ! empty( $taxonomy ) ) {
				$link = get_term_link( $term_id, $taxonomy );
				if ( ! is_wp_error( $link ) ) {
					return $link;
				}
			}
		}
		return '';
	}

}
