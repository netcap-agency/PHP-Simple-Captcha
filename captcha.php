<?php
/*
*	Rebuild of A simple PHP CAPTCHA script
*	Copyright 2013 by Cory LaViska for A Beautiful Site, LLC.
*
*	@author - netcap.fr
*	@change - build static class instead of function with get params
*
*/
class captcha{


	/*
	*	Contains captcha option
	*/
	protected $config = array(
		'code' => '',
		'min_length' => 5,
		'max_length' => 5,
		'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
		'min_font_size' => 28,
		'max_font_size' => 28,
		'color' => '#666',
		'angle_min' => 0,
		'angle_max' => 10,
		'shadow' => true,
		'shadow_color' => '#fff',
		'shadow_offset_x' => -1,
		'shadow_offset_y' => 1
	);
	
	/*
	*	Full path to background folder
	*/
	protected $backgroundURL;
	
	/*
	*	Full path to fonts folder
	*/
	protected $fontURL;
	
	
	/*
	*	Class init function
	*
	*/
	public static function run($option = array()){
		try {

			$captcha = new captcha();
			$return = $captcha->init($option);
			unset($captcha);

			return $return;

		} catch (\Exception $e) {

			if (isset($captcha)) {
			    unset($captcha);
			}
			throw $e;
		}
	}
	
	
	/*
	*	Init captcha config
	*
	*/
	private function init($option) {
		
		if( !function_exists('gd_info') ) {
			throw new Exception('Required GD library is missing');
		}
		
		/*
		*	Set default paths
		*/
		$this->backgroundURL = dirname(__FILE__) . '/backgrounds/';
		$this->fontURL = dirname(__FILE__) . '/fonts/';
		
		/*
		*	Default values
		*/
		$this->config['backgrounds'] = array(
			$this->backgroundURL . '45-degree-fabric.png',
			$this->backgroundURL . 'cloth-alike.png',
			$this->backgroundURL . 'grey-sandbag.png',
			$this->backgroundURL . 'kinda-jean.png',
			$this->backgroundURL . 'polyester-lite.png',
			$this->backgroundURL . 'stitched-wool.png',
			$this->backgroundURL . 'white-carbon.png',
			$this->backgroundURL . 'white-wave.png'
		);
		
		$this->config['fonts'] = array(
			$this->fontURL . 'times_new_yorker.ttf'
		);
		
		
		/*
		*	Override default values
		*/
		if( is_array($option) ) {
			foreach( $option as $key => $value ) $this->config[$key] = $value;
		}
		
		
		/*
		*	Restrict of values
		*/
		if( $this->config['min_length'] < 1 ) $this->config['min_length'] = 1;
		if( $this->config['angle_min'] < 0 ) $this->config['angle_min'] = 0;
		if( $this->config['angle_max'] > 10 ) $this->config['angle_max'] = 10;
		if( $this->config['angle_max'] < $this->config['angle_min'] ) $this->config['angle_max'] = $this->config['angle_min'];
		if( $this->config['min_font_size'] < 10 ) $this->config['min_font_size'] = 10;
		if( $this->config['max_font_size'] < $this->config['min_font_size'] ) $this->config['max_font_size'] = $this->config['min_font_size'];
		
		
		/*
		*	Use milliseconds instead of seconds
		*/
		srand(microtime() * 100);
		
		
		/*
		*	Generate code if empty
		*/
		if( empty($this->config['code']) ) {
			$this->config['code'] = '';
			$length = rand($this->config['min_length'], $this->config['max_length']);
			while( strlen($this->config['code']) < $length ) {
				$this->config['code'] .= substr($this->config['characters'], rand() % (strlen($this->config['characters'])), 1);
			}
		}

		return array(
			'code' => $this->config['code'],
			'image' => $this->draw()
		);
		
	}
	
	
	/*
	*	Draw image source into base64 string
	*
	*/
	private function draw(){
	
		if( !$this->config ) exit();
		
		/*
		*	Use milliseconds instead of seconds
		*/
		srand(microtime() * 100);
		
		/*
		*	Pick random background, get info, and start captcha
		*/
		$background = $this->config['backgrounds'][rand(0, count($this->config['backgrounds']) -1)];
		list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);
		
		$captcha = imagecreatefrompng($background);
		
		$color = $this->hex2rgb($this->config['color']);
		$color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);
		
		/*
		*	Determine text angle
		*/
		$angle = rand( $this->config['angle_min'], $this->config['angle_max'] ) * (rand(0, 1) == 1 ? -1 : 1);
		
		/*
		*	Select font randomly
		*/
		$font = $this->config['fonts'][rand(0, count($this->config['fonts']) - 1)];
		
		/*
		*	Verify font file exists
		*/
		if( !file_exists($font) ) throw new Exception('Font file not found: ' . $font);
		
		/*
		*	Set the font size
		*/
		$font_size = rand($this->config['min_font_size'], $this->config['max_font_size']);
		$text_box_size = imagettfbbox($font_size, $angle, $font, $this->config['code']);
		
		/*
		*	Determine text position
		*/
		$box_width = abs($text_box_size[6] - $text_box_size[2]);
		$box_height = abs($text_box_size[5] - $text_box_size[1]);
		$text_pos_x_min = 0;
		$text_pos_x_max = ($bg_width) - ($box_width);
		$text_pos_x = rand($text_pos_x_min, $text_pos_x_max);			
		$text_pos_y_min = $box_height;
		$text_pos_y_max = ($bg_height) - ($box_height / 2);
		$text_pos_y = rand($text_pos_y_min, $text_pos_y_max);
		
		/*
		*	Draw shadow
		*/
		if( $this->config['shadow'] ){
			$shadow_color = $this->hex2rgb($this->config['shadow_color']);
			$shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
			imagettftext($captcha, $font_size, $angle, $text_pos_x + $this->config['shadow_offset_x'], $text_pos_y + $this->config['shadow_offset_y'], $shadow_color, $font, $this->config['code']);	
		}
		
		/*
		*	Draw text
		*/
		imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $this->config['code']);	
		
		/*
		*	Output image
		*/
		ob_start();
		imagepng($captcha);
		imagedestroy($captcha);
		$captcha = ob_get_contents();
		ob_clean();
		return 'data:image/png;base64,' . base64_encode($captcha);
	
	}
	
	/*
	*	Convert hex color to rgb
	*
	*/
	private function hex2rgb($hex_str, $return_string = false, $separator = ','){
	
		$hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str);
		$rgb_array = array();
		if( strlen($hex_str) == 6 ) {
			$color_val = hexdec($hex_str);
			$rgb_array['r'] = 0xFF & ($color_val >> 0x10);
			$rgb_array['g'] = 0xFF & ($color_val >> 0x8);
			$rgb_array['b'] = 0xFF & $color_val;
		} elseif( strlen($hex_str) == 3 ) {
			$rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
			$rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
			$rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
		} else {
			return false;
		}
		return $return_string ? implode($separator, $rgb_array) : $rgb_array;
	
	}

}