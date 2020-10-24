<?php
/*
Plugin Name: URL as Number Segment
Plugin URI: http://yourls.org/
Description: Generated or custom URL in 12 digits only, like (123-456-789-001)
Version: 1.0
Author: Rajendra Rajsri
Author URI: https://github.com/rajendrarajsri
ripped from Peter Ryan Berbec Keywords, Charset & Length plugin.
*/

// Add "-" as valid charecter
yourls_add_filter( 'get_shorturl_charset', 'raj_hyphen_in_charset' );
function raj_hyphen_in_charset( $in ) { return $in.'-'; }

// Add monitoring field to option table
	if ( yourls_get_option( 'min_url' ) === false )
	{
		yourls_add_option ( 'min_url', 1 );
	}

	if ( yourls_get_option( 'full_url' ) === false )
	{
		yourls_add_option ( 'full_url', 1234567890 );
	}

/////Auto generated short url
yourls_add_filter( 'random_keyword', 'raj_random_keyword' );
function raj_random_keyword() {
	$ctime = time();
	if ( yourls_get_option( 'min_url' ) !== false )
	{
		$lsr_url = yourls_get_option( 'min_url' );
	}

	if ( yourls_get_option( 'full_url' ) !== false )
	{
		$fsr_url = yourls_get_option( 'full_url' );
	}

          if ( $fsr_url >= $ctime )
	{
                    if ( $lsr_url == 99 )
          	{
          		$fsr_url = $fsr_url + 1;
                              $lsr_url = 1;
                              yourls_update_option ( 'min_url', $lsr_url );
                              yourls_update_option ( 'full_url', $fsr_url );
          	} else {
                              $lsr_url = $lsr_url + 1;
                              yourls_update_option ( 'min_url', $lsr_url );
                           }
	}

          if ( $fsr_url < $ctime )
	    {
                    yourls_update_option ( 'full_url', $ctime );
              }

$lsr_url = substr(('0'.$lsr_url),-2);

$time1 = substr($ctime,0,3).'-';
$time2 = substr($ctime,3,3).'-';
$time3 = substr($ctime,6,3).'-';
$time4 = substr($ctime,9,1).$lsr_url;
$keyword = $time1.$time2.$time3.$time4;
return $keyword;
}

// Don't increment sequential keyword tracker
yourls_add_filter( 'get_next_decimal', 'raj_random_keyword_next_decimal' );
function raj_random_keyword_next_decimal( $next ) {
        return ( $next - 1 );
}

/////Custom entered short url check                   ///////////////

yourls_add_filter( 'shunt_add_new_link', 'raj_limit_keyword_length' );
// Check the keyin format and return an error if not match
function raj_limit_keyword_length( $too_long, $url, $keyword ) {
	$keyin = trim( $keyword );
	$max_keyword_length = 15;
	$keyword_length = strlen($keyin);
	$keyword_format = true;

// only digits and "-" filter
	$keyword_string =  "";
	$digits = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
	for ( $loop = 0; $loop < $keyword_length; $loop++ ) {
	$char = substr( $keyin, $loop, 1);
	if (in_array( $char, $digits )) {
			$keyword_string =  $keyword_string . $char;
			} else {
				$keyword_string =  $keyword_string . "-";
				}
			}
// Four segments of digits
	$segment = explode( "-", $keyword_string );
	if ( count( $segment ) != 4 ) {
			$keyword_format = false;
			}

// Each segment contains three digits
	foreach ( $segment as $seg_value )
	{
	$seg_length = strlen($seg_value);
	if ( $seg_length != 3) {
			  $keyword_format = false;
			  }
   	}
// valid format is true for blank
	if ( $keyword_length < 1 ) $keyword_format = true;

// check if any format mismatch occure
	if (!$keyword_format) {
		$return['status']   = 'fail';
		$return['code']     = 'error:keyword';
		$return['message']  = "Sorry, the keyword " . $keyword . " is not in format as required (###-###-###-###)";
		return yourls_apply_filter( 'add_new_link_keyword_too_long', $return );
							}
	return false;
}
