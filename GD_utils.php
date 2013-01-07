<?php

/*
  GD_utils
	*The class is an abstract layer over the GD librar php provides
	*Provides the most basic features to create/save/echo an image,
	*place text on the image & place an image over the source image

	Sample Usage

	*With an existing image as the background
		$img = new GD_utils( 0, 0, ROOT.'images/bg.jpg' );
		$img->place_text( 'HEY', ROOT.'fonts/cheese.ttf', 50, 0, 50, 100, array( 'r' => 255, 'g' => 255, 'b' => 255) );
		$img->place_image( ROOT.'images/module.png', 20, 100 );
		$path = ROOT.'images/';
		$img->save_image( $path );

	
	*Creating a new image of a specific size
		$img = new GD_utils( 500, 500 );
		$img->set_bg_color( 0, 0, 60 );
		$img->place_text( 'HEY', ROOT.'fonts/cheese.ttf', 50, 0, 50, 100, array( 'r' => 255, 'g' => 255, 'b' => 255) );
		header( 'Content-type: image/png' );
		$img->echo_image();


	Author: Manpreet Singh Sethi
*/

class GD_utils {
	private static $img_format		= 'PNG';			//Default image format (Output)
	private static $img_quality		= 9;				//0-9 (Compression, less the number, poorer the quality)
	
	var $img;					//Image resource
	var $width, $height;		//Width & height
	var $background_image;		//Background image path
	var $resources = array();	//Resources used for Image manipulation, is used in the destructor
	
	function __construct( $w, $h, $bg = '' ) {		
		if( empty( $bg ) ) {
			$this->width = $w;
			$this->height = $h;
			
			$this->img = imagecreate( $w, $h );
		}
		else {
			$this->background_image = $bg;
			$background_ext = strtolower( pathinfo( $this->background_image, PATHINFO_EXTENSION ) ); //Extract the image's extension/type
			
			//Create image from an image, and set it as background
			if( $background_ext == 'jpeg' || $background_ext == 'jpg' )
				$this->img = imagecreatefromjpeg( $this->background_image );	//Create image from a JPEG
			else if( $background_ext == 'png' )
				$this->img = imagecreatefrompng( $this->background_image );	//Create image from a PNG
			else if( $background_ext == 'gif' )
				$this->img = imagecreatefromgif( $this->background_image );	//Create image from a GIF
				
			list( $this->width, $this->height ) = getimagesize( $this->background_image );	//Extract the image's dimensions
		}
	}

	function __destruct() {
		if( isset( $this->resources['text_color'] ) )
			imagecolordeallocate( $this->img, $this->resources['text_color'] );
		
		if( isset( $this->resources['bg_color'] ) )
			imagecolordeallocate( $this->img, $this->resources['bg_color'] );
		
		imagedestroy( $this->img );

	}

	public function set_bg_color( $r, $g, $b ) {
		$this->resources['bg_color'] = imagecolorallocate( $this->img, $r, $g, $b );
	}
	
	/*
		Use this function to place a piece of string on the image @(x, y)
		Where;
		text		=> STRING,
		font		=> Path to the font file (TTF)
		font_size	=> Size of the font in pt (Points)
		angle		=> The angle at which the text needs to be placed
		x			=> X coordinate on the image
		y			=> Y coordinate on the image [Coordinate (0, 0) is the top-left corner]
		color		=> Array( Red, Green, Blue ) (RGB values)
	*/
	
	public function place_text( $text, $font, $font_size, $angle, $x, $y, $color ) {
		
		$this->resources['text_color'] = $text_color = imagecolorallocate( $this->img, $color['r'], $color['g'], $color['b'] );
		
		imagettftext($this->img, $font_size, $angle, $x, $y, $text_color, $font, $text);
	}

	/*
		Use this function to place an image over the current image as a layer
		Where;
		copy_img_path	=> Path to the file that needs to be placed over
		x 				=> X coordinate on the image
		y 				=> Y coordinate on the image [Coordinate (0, 0) is the top-left corner]

		*x & y are optional parameters and if not set explicitly, the image will be copied starting from the top-left corner, that is, (0, 0)

		TODO: Add a feature to be able to crop the image being copied
	*/

	public function place_image( $copy_img_path, $x = 0, $y = 0 ) {
		
		$copy_img_ext = strtolower( pathinfo( $copy_img_path, PATHINFO_EXTENSION ) ); //Extract the image's extension/type
		
		//Create image
		if( $copy_img_ext == 'jpeg' || $copy_img_ext == 'jpg' )
			$copy_img = imagecreatefromjpeg( $copy_img_path );	//Create image from a JPEG
		else if( $copy_img_ext == 'png' )
			$copy_img = imagecreatefrompng( $copy_img_path );	//Create image from a PNG
		else if( $copy_img_ext == 'gif' )
			$copy_img = imagecreatefromgif( $copy_img_path );	//Create image from a GIF
			
		list( $copy_img_width, $copy_img_height ) = getimagesize( $copy_img_path );	//Extract the image's dimensions

		imagecopy ( $this->img, $copy_img, $x, $y, 0, 0, $copy_img_width, $copy_img_height );

	}

	public function save_image( $save_path ) {
		switch( self::$img_format ) {
			
			case 'PNG':
				imagepng( $this->img, $save_path.md5( time() ).'.png', self::$img_quality );
			break;
			
			case 'GIF':
				imagegif( $this->img, $save_path.md5( time() ).'.gif' );
			break;
			
			case 'JPEG':
				imagejpeg( $this->img, $save_path.md5( time() ).'.jpeg', self::$img_quality );
			break;
			
		}

	}
	
	public function echo_image( ) {
		switch( self::$img_format ) {
			
			case 'PNG':
				imagepng( $this->img, NULL, self::$img_quality );
			break;
			
			case 'GIF':
				imagegif( $this->img );
			break;
			
			case 'JPEG':
				imagejpeg( $this->img, NULL, self::$img_quality );
			break;
			
		}

	}
	
}

?>
