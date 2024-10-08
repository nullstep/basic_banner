<?php

/*
 * Plugin Name: basic_banner
 * Plugin URI: https://nullstep.com/wp-plugins
 * Description: a simple banner/slider
 * Author: nullstep
 * Author URI: https://nullstep.com
 * Version: 1.2.6
 */

defined('ABSPATH') or die('⎺\_(ツ)_/⎺');

// defines      

define('_PLUGIN_BASIC_BANNER', 'basic_banner');

define('_URL_BASIC_BANNER', plugin_dir_url(__FILE__));
define('_PATH_BASIC_BANNER', plugin_dir_path(__FILE__));

//   ▄████████   ▄██████▄   ███▄▄▄▄▄       ▄████████   ▄█      ▄██████▄   
//  ███    ███  ███    ███  ███▀▀▀▀██▄    ███    ███  ███     ███    ███  
//  ███    █▀   ███    ███  ███    ███    ███    █▀   ███▌    ███    █▀   
//  ███         ███    ███  ███    ███   ▄███▄▄▄      ███▌   ▄███         
//  ███         ███    ███  ███    ███  ▀▀███▀▀▀      ███▌  ▀▀███ ████▄   
//  ███    █▄   ███    ███  ███    ███    ███         ███     ███    ███  
//  ███    ███  ███    ███  ███    ███    ███         ███     ███    ███  
//  ████████▀    ▀██████▀    ▀█    █▀     ███         █▀      ████████▀ 

// basic_banner args

define('_ARGS_BASIC_BANNER', [
	'bb_active' => [
		'type' => 'string',
		'default' => 'yes'
	],
	'bb_indent' => [
		'type' => 'string',
		'default' => '2'
	],
	'bb_css' => [
		'type' => 'string',
		'default' => ''
	],
	'bb_css_minified' => [
		'type' => 'string',
		'default' => ''
	],
	'bb_js' => [
		'type' => 'string',
		'default' => ''
	],
	'bb_js_minified' => [
		'type' => 'string',
		'default' => ''
	]
]);

// basic_banner admin

define('_ADMIN_BASIC_BANNER', [
	'options' => [
		'label' => 'Options',
		'columns' => 4,
		'fields' => [
			'bb_active' => [
				'label' => 'Banners Active',
				'type' => 'check'
			],
			'bb_indent' => [
				'label' => 'Tab Indents',
				'type' => 'input'
			]
		]
	],
	'css' => [
		'label' => 'CSS',
		'columns' => 1,
		'fields' => [
			'bb_css' => [
				'label' => 'Banner Styles',
				'type' => 'code'
			]
		]
	],
	'js' => [
		'label' => 'JS',
		'columns' => 1,
		'fields' => [
			'bb_js' => [
				'label' => 'Banner Scripts',
				'type' => 'code'
			]
		]
	]
]);

// basic_banner api routes

define('_APIPATH_BASIC_BANNER',
	'settings'
);

define('_API_BASIC_BANNER', [
	[
		'methods' => 'POST',
		'callback' => 'update_settings',
		'args' => _bbSettings::args(),
		'permission_callback' => 'permissions'
	],
	[
		'methods' => 'GET',
		'callback' => 'get_settings',
		'args' => [],
		'permission_callback' => 'permissions'
	]
]);

//     ▄████████     ▄███████▄   ▄█ 
//    ███    ███    ███    ███  ███ 
//    ███    ███    ███    ███  ███▌
//    ███    ███    ███    ███  ███▌
//  ▀███████████  ▀█████████▀   ███▌
//    ███    ███    ███         ███ 
//    ███    ███    ███         ███ 
//    ███    █▀    ▄████▀       █▀ 

class _bbAPI {
	public function add_routes() {
		if (count(_API_BASIC_BANNER)) {

			foreach(_API_BASIC_BANNER as $route) {
				register_rest_route(_PLUGIN_BASIC_BANNER . '-api', '/' . _APIPATH_BASIC_BANNER, [
					'methods' => $route['methods'],
					'callback' => [$this, $route['callback']],
					'args' => $route['args'],
					'permission_callback' => [$this, $route['permission_callback']]
				]);
			}
		}
	}

	public function permissions() {
		return current_user_can('manage_options');
	}

	public function update_settings(WP_REST_Request $request) {
		$settings = [];
		foreach (_bbSettings::args() as $key => $val) {
			$settings[$key] = $request->get_param($key);
		}
		_bbSettings::save_settings($settings);
		return rest_ensure_response(_bbSettings::get_settings());
	}

	public function get_settings(WP_REST_Request $request) {
		return rest_ensure_response(_bbSettings::get_settings());
	}
}

//     ▄████████     ▄████████      ███          ███       ▄█   ███▄▄▄▄▄       ▄██████▄      ▄████████
//    ███    ███    ███    ███  ▀█████████▄  ▀█████████▄  ███   ███▀▀▀▀██▄    ███    ███    ███    ███
//    ███    █▀     ███    █▀      ▀███▀▀██     ▀███▀▀██  ███▌  ███    ███    ███    █▀     ███    █▀ 
//    ███          ▄███▄▄▄          ███   ▀      ███   ▀  ███▌  ███    ███   ▄███           ███       
//  ▀███████████  ▀▀███▀▀▀          ███          ███      ███▌  ███    ███  ▀▀███ ████▄   ▀███████████
//           ███    ███    █▄       ███          ███      ███   ███    ███    ███    ███           ███
//     ▄█    ███    ███    ███      ███          ███      ███   ███    ███    ███    ███     ▄█    ███
//   ▄████████▀     ██████████     ▄████▀       ▄████▀    █▀     ▀█    █▀     ████████▀    ▄████████▀ 

class _bbSettings {
	protected static $option_key = _PLUGIN_BASIC_BANNER . '-settings';

	public static function args() {
		$args = _ARGS_BASIC_BANNER;
		foreach (_ARGS_BASIC_BANNER as $key => $val) {
			$val['required'] = true;
			switch ($val['type']) {
				case 'integer': {
					$cb = 'absint';
					break;
				}
				default: {
					$cb = 'sanitize_text_field';
				}
				$val['sanitize_callback'] = $cb;
			}
		}
		return $args;
	}

	public static function get_settings() {
		$defaults = [];
		foreach (_ARGS_BASIC_BANNER as $key => $val) {
			$defaults[$key] = $val['default'];
		}
		$saved = get_option(self::$option_key, []);
		if (!is_array($saved) || empty($saved)) {
			return $defaults;
		}
		return wp_parse_args($saved, $defaults);
	}

	public static function save_settings(array $settings) {
		$defaults = [];
		foreach (_ARGS_BASIC_BANNER as $key => $val) {
			$defaults[$key] = $val['default'];
		}
		foreach ($settings as $i => $setting) {
			if (!array_key_exists($i, $defaults)) {
				unset($settings[$i]);
			}
			if ($i == 'bb_css') {
				$settings['bb_css_minified'] = bb_minify_css($setting);
			}
			if ($i == 'bb_js') {
				$settings['bb_js_minified'] = bb_minify_js($setting);
			}
		}
		update_option(self::$option_key, $settings);
	}
}

//    ▄▄▄▄███▄▄▄▄       ▄████████  ███▄▄▄▄▄    ███    █▄ 
//  ▄██▀▀▀███▀▀▀██▄    ███    ███  ███▀▀▀▀██▄  ███    ███
//  ███   ███   ███    ███    █▀   ███    ███  ███    ███
//  ███   ███   ███   ▄███▄▄▄      ███    ███  ███    ███
//  ███   ███   ███  ▀▀███▀▀▀      ███    ███  ███    ███
//  ███   ███   ███    ███    █▄   ███    ███  ███    ███
//  ███   ███   ███    ███    ███  ███    ███  ███    ███
//   ▀█   ███   █▀     ██████████   ▀█    █▀   ████████▀ 

class _bbMenu {
	protected $slug = _PLUGIN_BASIC_BANNER . '-menu';
	protected $assets_url;

	public function __construct($assets_url) {
		$this->assets_url = $assets_url;
		add_action('admin_menu', [$this, 'add_page']);
		add_action('admin_enqueue_scripts', [$this, 'register_assets']);
	}

	public function add_page() {
		add_menu_page(
			_PLUGIN_BASIC_BANNER,
			_PLUGIN_BASIC_BANNER,
			'manage_options',
			$this->slug,
			[$this, 'render_admin'],
			'data:image/svg+xml;base64,' . base64_encode(
				'<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="500px" height="500px" viewbox="0 0 500 500"><g><rect x="177.2" y="203.2" fill="#a7aaad" width="145.9" height="23.2"/><path fill="#a7aaad" d="M250.2,17.4L49,133.5v232.3L250.2,482l201.2-116.2V133.5L250.2,17.4z M371.9,203.2h-0.1v23.2v48.7v23.2V347 h-48.7H177.2h-48.7h-0.1v-48.7h0.1v-23.2h-0.1v-48.7h0.1v-23.2v-48.7h48.7h145.9h48.7h0.1V203.2z"/><rect x="177.2" y="275.1" fill="#a7aaad" width="145.9" height="23.2"/></g></svg>'
			),
			3
		);

		// add config submenu

		add_submenu_page(
			$this->slug,
			'Configuration',
			'Configuration',
			'manage_options',
			$this->slug
		);

		// add taxonomies menus

		$types = [
			'banner' => 'slide'
		];

		foreach ($types as $type => $child) {
			add_submenu_page(
				$this->slug,
				ucwords($type . 's'),
				ucwords($type . 's'),
				'manage_options',
				'/edit-tags.php?taxonomy=' . $type . '&post_type=' . $child
			);
		}

		// add posts menus

		$types = [
			'slide'
		];

		foreach ($types as $type) {
			add_submenu_page(
				$this->slug,
				ucwords($type . 's'),
				ucwords($type . 's'),
				'manage_options',
				'/edit.php?post_type=' . $type
			);
		}
	}

	public function register_assets() {
		$boo = microtime(false);
		wp_register_script($this->slug, $this->assets_url . '/' . _PLUGIN_BASIC_BANNER . '.js?' . $boo, ['jquery']);
		wp_register_style($this->slug, $this->assets_url . '/' . _PLUGIN_BASIC_BANNER . '.css?' . $boo);
		wp_localize_script($this->slug, _PLUGIN_BASIC_BANNER, [
			'strings' => [
				'saved' => 'Settings Saved',
				'error' => 'Error'
			],
			'api' => [
				'url' => esc_url_raw(rest_url(_PLUGIN_BASIC_BANNER . '-api/settings')),
				'nonce' => wp_create_nonce('wp_rest')
			]
		]);
	}

	public function enqueue_assets() {
		if (!wp_script_is($this->slug, 'registered')) {
			$this->register_assets();
		}

		wp_enqueue_script($this->slug);
		wp_enqueue_style($this->slug);
	}

	public function render_admin() {
		wp_enqueue_media();
		$this->enqueue_assets();

		$name = _PLUGIN_BASIC_BANNER;
		$form = _ADMIN_BASIC_BANNER;

		// build form

		echo '<div id="' . $name . '-wrap" class="wrap">';
			echo '<h1>' . $name . '</h1>';
			echo '<p>Configure your ' . $name . ' settings...</p>';
			echo '<form id="' . $name . '-form" method="post">';
				echo '<nav id="' . $name . '-nav" class="nav-tab-wrapper">';

				foreach ($form as $tid => $tab) {
					echo '<a href="#' . $name . '-' . $tid . '" class="nav-tab">' . $tab['label'] . '</a>';
				}
				echo '</nav>';
				echo '<div class="tab-content">';

				foreach ($form as $tid => $tab) {
					echo '<div id="' . $name . '-' . $tid . '" class="' . $name . '-tab">';

					foreach ($tab['fields'] as $fid => $field) {
						echo '<div class="form-block col-' . $tab['columns'] . '">';
						
						switch ($field['type']) {
							case 'input': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<input id="' . $fid . '" type="text" name="' . $fid . '">';
								break;
							}
							case 'select': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<select id="' . $fid . '" name="' . $fid . '">';
									foreach ($field['values'] as $value => $label) {
										echo '<option value="' . $value . '">' . $label . '</option>';
									}
								echo '</select>';
								break;
							}
							case 'text': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<textarea id="' . $fid . '" class="tabs" name="' . $fid . '"></textarea>';
								break;
							}
							case 'file': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<input id="' . $fid . '" type="text" name="' . $fid . '">';
								echo '<input data-id="' . $fid . '" type="button" class="button-primary choose-file-button" value="...">';
								break;
							}
							case 'colour': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<input id="' . $fid . '" type="text" name="' . $fid . '">';
								echo '<input data-id="' . $fid . '" type="color" class="choose-colour-button" value="#000000">';
								break;
							}
							case 'code': {
								echo '<label for="' . $fid . '">';
									echo $field['label'] . ':';
								echo '</label>';
								echo '<textarea id="' . $fid . '" class="code" name="' . $fid . '"></textarea>';
								break;
							}
							case 'check': {
								echo '<em>' . $field['label'] . ':</em>';
								echo '<label class="switch">';
									echo '<input type="checkbox" id="' . $fid . '" name="' . $fid . '" value="yes">';
									echo '<span class="slider"></span>';
								echo '</label>';
								break;
							}
						}
						echo '</div>';
					}
					echo '</div>';
				}
				echo '</div>';
				echo '<div>';
					submit_button();
				echo '</div>';
				echo '<div id="' . $name . '-feedback"></div>';
			echo '</form>';
		echo '</div>';
	}
}

//   ▄█   ███▄▄▄▄▄     ▄█       ███   
//  ███   ███▀▀▀▀██▄  ███   ▀█████████▄
//  ███▌  ███    ███  ███▌     ▀███▀▀██
//  ███▌  ███    ███  ███▌      ███   ▀
//  ███▌  ███    ███  ███▌      ███ 
//  ███   ███    ███  ███       ███ 
//  ███   ███    ███  ███       ███  
//  █▀     ▀█    █▀   █▀       ▄████▀

function bb_init($dir) {
	// set up post types

	$types = [
		'slide'
	];

	foreach ($types as $type) {
		$uc_type = ucwords($type);

		$labels = [
			'name' => $uc_type . 's',
			'singular_name' => $uc_type,
			'menu_name' => $uc_type . 's',
			'name_admin_bar' => $uc_type . 's',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New ' . $uc_type,
			'new_item' => 'New ' . $uc_type,
			'edit_item' => 'Edit ' . $uc_type,
			'view_item' => 'View ' . $uc_type,
			'all_items' => $uc_type . 's',
			'search_items' => 'Search ' . $uc_type . 's',
			'not_found' => 'No ' . $uc_type . 's Found'
		];

		register_post_type($type, [
			'supports' => [
				'title'
			],
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
			'has_archive' => false,
			'rewrite' => ['slug' => $type]
		]);
	}

	// set up taxonomies

	$types = [
		'banner' => 'slide'
	];

	foreach ($types as $type => $child) {
		$uc_type = ucwords($type);

		$labels = [
			'name' => $uc_type . 's',
			'singular_name' => $uc_type,
			'search_items' => 'Search ' . $uc_type . 's',
			'all_items' => 'All ' . $uc_type . 's',
			'parent_item' => 'Parent ' . $uc_type,
			'parent_item_colon' => 'Parent ' . $uc_type . ':',
			'edit_item' => 'Edit ' . $uc_type, 
			'update_item' => 'Update ' . $uc_type,
			'add_new_item' => 'Add New ' . $uc_type,
			'new_item_name' => 'New ' . $uc_type . ' Name',
			'menu_name' => $uc_type . 's',
			'not_found' => 'No ' . $uc_type . 's Found',
			'back_to_items' => 'Back to ' . $uc_type . 's'
		];

		register_taxonomy($type, [$child], [
			'hierarchical' => true,
			'labels' => $labels,
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_rest' => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite' => ['slug' => $type],
		]);
	}
}

//    ▄▄▄▄███▄▄▄▄       ▄████████      ███         ▄████████
//  ▄██▀▀▀███▀▀▀██▄    ███    ███  ▀█████████▄    ███    ███
//  ███   ███   ███    ███    █▀      ▀███▀▀██    ███    ███
//  ███   ███   ███   ▄███▄▄▄          ███   ▀    ███    ███
//  ███   ███   ███  ▀▀███▀▀▀          ███      ▀███████████
//  ███   ███   ███    ███    █▄       ███        ███    ███
//  ███   ███   ███    ███    ███      ███        ███    ███
//   ▀█   ███   █▀     ██████████     ▄████▀      ███    █▀ 

function bb_add_metaboxes() {
	$screens = ['slide'];
	foreach ($screens as $screen) {
		add_meta_box(
			'bb_meta_box',
			'Slide Data',
			'bb_slide_metabox',
			$screen
		);
	}
}

function bb_slide_metabox($post) {
	wp_enqueue_media();
	$prefix = '_bb-slide_';
	$keys = [
		'media',
		'align',
		'top',
		'text',
		'button',
		'url'
	];
	foreach ($keys as $key) {
		$$key = get_post_meta($post->ID, $prefix . $key, true);
	}
	wp_nonce_field(plugins_url(__FILE__), 'wr_plugin_noncename');
	?>
	<style>
		#bb_meta_box label {
			display: inline-block;
			width: 20%;
			font-weight: 700;
			padding-top: 4px;
		}
		#bb_meta_box input,
		#bb_meta_box select,
		#bb_meta_box textarea {
			box-sizing: border-box;
			display: inline-block;
			width: 53%;
			padding: 3px;
			vertical-align: middle;
			margin-top: 10px;
		}
		#bb_meta_box .group {
			display: inline-block;
			width: 53%;
		}
		#bb_meta_box .group input {
			display: inline-block;
			width: 90%;
		}
		#bb_meta_box .group input.choose-file-button {
			display: inline-block;
			position: relative;
			width: 8%;
			height: 34px;
			top: 0px;
			left: 2px;
		}
		#bb_meta_box span.desc {
			display: block;
			width: 18%;
			padding-top: 6px;
			clear: both;
			font-style: italic;
			font-size: 12px;
		}
		#bb_meta_box span.preview {
			padding-top: 8px;
			display: inline-block;
			width: 20%;
		}
		#bb_meta_box span.preview img {
			max-width: 100%;
		}
		#bb_meta_box div.middle {
			margin-bottom: 10px;
			padding-bottom: 10px;
			border-bottom: 1px dashed #ddd;
		}
		#bb_meta_box div.top {
			margin-top: 10px;
			margin-bottom: 10px;
			padding-bottom: 10px;
			border-bottom: 1px dashed #ddd;
		}
		#bb_meta_box div.bottom {
			margin-bottom: 0;
			padding-bottom: 0;
			border-bottom: 0;
		}
	</style>
	<div class="inside">
		<div class="top">
			<label>Media:</label>
<?php
	if ($media != '') {
		$img = '/uploads/' . $media;
	}
	else {
		$img = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAACQkWg2AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAARSURBVDhPYxgFo2AUQAEDAwADEAABuGyTOQAAAABJRU5ErkJggg==';
	}
?>
			<div class="group">
				<input type="text" id="bb-slide-media" name="_bb-slide_media" value="<?php echo $media; ?>">
				<input data-id="bb-slide-media" type="button" class="button-primary choose-file-button" value="...">
			</div>
			<span class="preview"><img id="bb-preview" src="<?php echo $img; ?>" style="height:64px"></span>
			<span class="desc">Media (image/video) for this slide</span>
		</div>
		<div class="middle">
			<label>Align:</label>
			<select id="bb-slide-align" name="_bb-slide_align">
			<?php
				$options = ['center', 'left', 'right'];
				foreach ($options as $o) {
					$selected = ($o == $align) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . ucwords($o) . '</option>';
				}
			?>
			</select>
			<span class="desc">Title and text alignment for this slide</span>
		</div>
		<div class="middle">
			<label>Top:</label>
			<input type="text" id="bb-slide-top" name="_bb-slide_top" value="<?php echo $top; ?>">
			<span class="desc">Top position for the text on this slide</span>
		</div>
		<div class="middle">
			<label>Content:</label>
			<textarea id="bb-slide-text" name="_bb-slide_text" style="height:60px"><?php echo $text; ?></textarea>
			<span class="desc">Text for this slide</span>
		</div>
		<div class="middle">
			<label>Button:</label>
			<input type="text" id="bb-slide-button" name="_bb-slide_button" value="<?php echo $button; ?>">
			<span class="desc">Button text for this slide (leave blank for no button)</span>
		</div>
		<div class="bottom">
			<label>URL:</label>
			<input type="text" id="bb-slide-url" name="_bb-slide_url" value="<?php echo $url; ?>">
			<span class="desc">URL for button on this slide</span>
		</div>
	</div>
	<script>
		jQuery(document).ready(function($) {
			var m = $('#bb-slide-media');
			m.on('change', function() {
				$('#bb-preview').attr('src', '/uploads/' + m.val());
			});
			var mediaUploader, bid;
			$('.choose-file-button').on('click', function(e) {
				bid = '#' + $(this).data('id');
				e.preventDefault();
				if (mediaUploader) {
					mediaUploader.open();
					return;
				}
				mediaUploader = wp.media.frames.file_frame = wp.media({
					title: 'Choose File',
					button: {
						text: 'Choose File'
					}, multiple: false
				});
				wp.media.frame.on('open', function() {
					if (wp.media.frame.content.get() !== null) {          
						wp.media.frame.content.get().collection._requery(true);
						wp.media.frame.content.get().options.selection.reset();
					}
				}, this);
				mediaUploader.on('select', function() {
					var attachment = mediaUploader.state().get('selection').first().toJSON();
					$(bid).val(attachment.url.split('/').pop()).trigger('change');
				});
				mediaUploader.open();
			});
		});
	</script>
<?php
}

function bb_save_postdata($post_id) {
	$prefix = '_bb-slide_';
	$keys = [
		'media',
		'align',
		'top',
		'text',
		'button',
		'url'
	];
	foreach ($keys as $key) {
		if (array_key_exists($prefix . $key, $_POST)) {
			update_post_meta(
				$post_id,
				$prefix . $key,
				$_POST[$prefix . $key]
			);
		}
	}
}

function bb_add_buttons_to_post_edit() {
		global $post;

		$type = $post->post_type;

		if (in_array($type, ['slide'])) {
			echo '<br><a class="button button-primary" href="/wp-admin/edit.php?post_type=' . $type . '">Back to ' . ucwords(bb_plural(str_replace('_', ' ', $type))) . ' List&hellip;</a><br><br>';
		}
	}

function bb_banner_save_form_fields($term_id) {
	$metas = [
		'height',
		'interval',
		'mode',
		'crossfade',
		'indicators',
		'accent',
		'cards'
	];

	foreach ($metas as $meta_name) {
		if (isset($_POST[$meta_name])) {
			$meta_value = $_POST[$meta_name];
			update_term_meta(
				$term_id,
				$meta_name,
				sanitize_text_field($meta_value)
			);
		}
	}
}

// add 'new' button to banner list page

function bb_pre_banner_edit_form_fields($taxonomy) {
	$screen = get_current_screen();
	$action = $_GET['action'] ?? 'list';

	if ($screen->base == 'edit-tags' && $action != 'new') {
		if ($taxonomy && $taxonomy == 'banner') {
			$button = " <a class='page-title-action' href='/wp-admin/edit-tags.php?taxonomy=banner&action=new'>Add New</a>";
			echo '<script>jQuery(function($){$("h1.wp-heading-inline").after("' . $button . '");});</script>';
		}
	}
}

function bb_banner_edit_form_fields($term) {
	global $pagenow;

	switch ($pagenow) {
		case 'edit-tags.php': {
			$action = $_GET['action'] ?? 'list';
			break;
		}
		case 'term.php': {
			$action = 'edit';
			break;
		}
		default: {
			$action = 'list';
		}
	}

	if (is_string($term)) {
		$height = '400px';
		$interval = '5000';
		$mode = 'light';
		$crossfade = 'no';
		$indicators = 'yes';
		$accent = 'yes';
		$cards = 1;

		$a = ($action == 'new') ? 'right' : 'left';
		$b = ($action == 'new') ? 'left' : 'right';

		echo '<style>#col-' . $a . '{display:none}#col-' . $b . '{width:100%}#titlediv #tag-name{padding:3px 8px;font-size:1.7em;line-height:100%;height:1.7em;width:100%;outline: 0;margin:0 0 3px;background-color:#fff}</style>';
	}
	else {
		$height = get_term_meta($term->term_id, 'height', true);
		$interval = get_term_meta($term->term_id, 'interval', true);
		$mode = get_term_meta($term->term_id, 'mode', true);
		$crossfade = get_term_meta($term->term_id, 'crossfade', true);
		$indicators = get_term_meta($term->term_id, 'indicators', true);
		$accent = get_term_meta($term->term_id, 'accent', true);
		$cards = (int) get_term_meta($term->term_id, 'cards', true);

		echo '<style>#edittag{max-width:100%}#titlediv #name{padding:3px 8px;font-size:1.7em;line-height:100%;height:1.7em;width:100%;outline: 0;margin:0 0 3px;background-color:#fff}</style>';
	}
?>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="height">Banner Height</label>
		</th>
		<td>
			<input type="text" id="height" name="height" value="<?php echo $height; ?>"/>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="interval">Banner Interval</label>
		</th>
		<td>
			<input type="text" id="interval" name="interval" value="<?php echo $interval; ?>"/>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="mode">Banner Mode</label>
		</th>
		<td>
			<select id="mode" name="mode">
			<?php
				$options = ['light', 'dark'];
				foreach ($options as $o) {
					$selected = ($o == $mode) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . ucwords($o) . '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="crossfade">Banner Crossfade</label>
		</th>
		<td>
			<select id="crossfade" name="crossfade">
			<?php
				$options = ['yes', 'no'];
				foreach ($options as $o) {
					$selected = ($o == $crossfade) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . ucwords($o) . '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="indicators">Banner Indicators</label>
		</th>
		<td>
			<select id="indicators" name="indicators">
			<?php
				$options = ['yes', 'no'];
				foreach ($options as $o) {
					$selected = ($o == $indicators) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . ucwords($o) . '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="accent">Banner Accent</label>
		</th>
		<td>
			<select id="accent" name="accent">
			<?php
				$options = ['yes', 'no'];
				foreach ($options as $o) {
					$selected = ($o == $accent) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . ucwords($o) . '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr class="form-field">
		<th valign="top" scope="row">
			<label for="cards">Number of Cards</label>
		</th>
		<td>
			<select id="cards" name="cards">
			<?php
				$options = [1, 2, 3, 4];
				foreach ($options as $o) {
					$selected = ($o == $cards) ? ' selected' : '';
					echo '<option value="' . $o . '"' . $selected . '>' . $o . '</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<br>
	<br>
	<style>
		#name-description { display: none; }
	</style>
<?php 
}

//     ▄████████   ▄█    ▄█            ███         ▄████████     ▄████████
//    ███    ███  ███   ███        ▀█████████▄    ███    ███    ███    ███
//    ███    █▀   ███▌  ███           ▀███▀▀██    ███    █▀     ███    ███
//   ▄███▄▄▄      ███▌  ███            ███   ▀   ▄███▄▄▄       ▄███▄▄▄▄██▀
//  ▀▀███▀▀▀      ███▌  ███            ███      ▀▀███▀▀▀      ▀▀███▀▀▀▀▀  
//    ███         ███   ███            ███        ███    █▄   ▀███████████
//    ███         ███   ███▌    ▄      ███        ███    ███    ███    ███
//    ███         █▀    █████▄▄██     ▄████▀      ██████████    ███    ███

function bb_add_filter_to_slides_list() {
	$type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

	if ($type == 'slide') {
		$banners = get_terms([
			'taxonomy' => 'banner',
			'hide_empty' => false,
		]);

		echo '<select name="banner_name">';
		echo '<option value="">Filter By...</option>';

		$current = isset($_GET['banner_name']) ? $_GET['banner_name'] : '';

		foreach ($banners as $banner) {
			printf(
				'<option value="%s"%s>%s</option>',
				$banner->name,
				$banner->name == $current ? ' selected="selected"' : '',
				$banner->name
			);
		}

		echo '</select>';
	}
}

function bb_slides_filter($query) {
	global $pagenow;
	$type = (isset($_GET['post_type'])) ? $_GET['post_type'] : 'post';

	if (is_admin() && $type == 'slide' && $pagenow == 'edit.php') {
		if (isset($_GET['banner_name']) && $_GET['banner_name'] != '') {
			$query->query_vars['banner'] = $_GET['banner_name'];
		}
	}
}

// menu stuff

function bb_set_current_menu($parent_file) {
	global $submenu_file, $current_screen, $pagenow;
	$taxonomy = 'banner';

	if ($current_screen->id == 'edit-' . $taxonomy) {
		if ($pagenow == 'post.php') {
			$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
		}
		if ($pagenow == 'edit-tags.php') {
			$submenu_file = 'edit-tags.php?taxonomy=' . $taxonomy . '&post_type=' . $current_screen->post_type;
		}
		$parent_file = _PLUGIN_BASIC_BANNER . '-menu';
	}
	return $parent_file;
}

//     ▄████████  ████████▄     ▄▄▄▄███▄▄▄▄     ▄█   ███▄▄▄▄▄  
//    ███    ███  ███   ▀███  ▄██▀▀▀███▀▀▀██▄  ███   ███▀▀▀▀██▄
//    ███    ███  ███    ███  ███   ███   ███  ███▌  ███    ███
//    ███    ███  ███    ███  ███   ███   ███  ███▌  ███    ███
//  ▀███████████  ███    ███  ███   ███   ███  ███▌  ███    ███
//    ███    ███  ███    ███  ███   ███   ███  ███   ███    ███
//    ███    ███  ███   ▄███  ███   ███   ███  ███   ███    ███
//    ███    █▀   ████████▀    ▀█   ███   █▀   █▀     ▀█    █▀ 

function bb_admin_scripts() {
	global $current_screen, $userdata;

	if ($current_screen === null) {
		return;
	}
	if ($current_screen->base == 'toplevel_page_' . _PLUGIN_BASIC_BANNER . '-menu') {
		wp_enqueue_code_editor(['type' => 'application/x-httpd-php']);
	}
	if ($current_screen->id == 'edit-slide') {
		$boo = microtime(false);
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		wp_register_script('bb', _URL_BASIC_BANNER . '/' . _PLUGIN_BASIC_BANNER . '.js?' . $boo, ['jquery']);
		wp_localize_script('bb', 'bb', [
			'post_type' => $current_screen->post_type,
			'archive_sort_nonce' => wp_create_nonce('bb_archive_sort_nonce_' . $userdata->ID) 
		]);
		wp_enqueue_script('bb');
	}
}

//     ▄████████     ▄█    █▄      ▄██████▄      ▄████████      ███    
//    ███    ███    ███    ███    ███    ███    ███    ███  ▀█████████▄
//    ███    █▀     ███    ███    ███    ███    ███    ███     ▀███▀▀██
//    ███          ▄███▄▄▄▄███▄▄  ███    ███   ▄███▄▄▄▄██▀      ███   ▀  
//  ▀███████████  ▀▀███▀▀▀▀███▀   ███    ███  ▀▀███▀▀▀▀▀        ███ 
//           ███    ███    ███    ███    ███  ▀███████████      ███ 
//     ▄█    ███    ███    ███    ███    ███    ███    ███      ███   
//   ▄████████▀     ███    █▀      ▀██████▀     ███    ███     ▄████▀

//   ▄████████   ▄██████▄   ████████▄      ▄████████
//  ███    ███  ███    ███  ███   ▀███    ███    ███
//  ███    █▀   ███    ███  ███    ███    ███    █▀ 
//  ███         ███    ███  ███    ███   ▄███▄▄▄  
//  ███         ███    ███  ███    ███  ▀▀███▀▀▀  
//  ███    █▄   ███    ███  ███    ███    ███    █▄ 
//  ███    ███  ███    ███  ███   ▄███    ███    ███
//  ████████▀    ▀██████▀   ████████▀     ██████████

function bb_shortcode($atts = [], $content = null, $tag = '') {
	$name = $content;
	$items = [];

	if ($name) {
		$banner = new WP_Query([
			'post_type' => 'slide',
			'tax_query' => [[
				'taxonomy' => 'banner',
				'field' => 'slug',
				'terms' => $name
			]],
			'orderby' => 'menu_order',
			'order' => 'ASC'
		]);
		
		if ($banner->have_posts()) {
			while ($banner->have_posts()) : $banner->the_post();
				$items[] = [
					'title' => get_the_title(),
					'image' => '/uploads/' . get_post_meta(get_the_ID(), '_bb-slide_media', true),
					'align' => get_post_meta(get_the_ID(), '_bb-slide_align', true),
					'top' => get_post_meta(get_the_ID(), '_bb-slide_top', true),
					'text' => get_post_meta(get_the_ID(), '_bb-slide_text', true),
					'button' => get_post_meta(get_the_ID(), '_bb-slide_button', true),
					'url' => get_post_meta(get_the_ID(), '_bb-slide_url', true)
				];
			endwhile;
		}
	}

	$term = get_term_by('slug', $name, 'banner');
	$height = get_term_meta($term->term_id, 'height', true);
	$interval = get_term_meta($term->term_id, 'interval', true);
	$mode = get_term_meta($term->term_id, 'mode', true);
	$crossfade = get_term_meta($term->term_id, 'crossfade', true);
	$indicators = get_term_meta($term->term_id, 'indicators', true);
	$accent = get_term_meta($term->term_id, 'accent', true);
	$cards = get_term_meta($term->term_id, 'cards', true);

	$num_items = count($items);
	$html = '';

	if ($num_items > 0) {
		ob_start();

		$html .= str_repeat("\t", _BB['bb_indent']) . '<style>' . _BB['bb_css_minified'] . '</style>' . "\n";
		$html .= str_repeat("\t", _BB['bb_indent']) . '<script>' . _BB['bb_js_minified'] . '</script>' . "\n";

		$id = 'bb-carousel_' . $name;
		$class = ($crossfade == 'yes') ? ' carousel-fade' : '';
		$class .= ($mode == 'dark') ? ' carousel-dark' : '';
		$style = ($accent == 'yes') ? ';border-bottom:5px solid transparent"' : '';

		$html .= str_repeat("\t", _BB['bb_indent']) . '<div id="' . $id . '" class="carousel slide' . $class . '" data-bs-ride="carousel" style="overflow:hidden;max-height:' . $height . $style . '">';

		if (($indicators == 'yes') && ($num_items > 1)) {
			$html .= '<div class="carousel-indicators">';

			for ($i = 0; $i < count($items); $i++) {
				$class = ($i == 0) ? 'active' : '';
				$html .= '<button type="button" data-bs-target="#' . $id . '" data-bs-slide-to="' . $i . '" class="' . $class . '" aria-current="true" aria-label="Slide ' . ($i + 1) . '"></button>';
			}

			$html .= '</div>';			
		}

		$html .= '<div class="carousel-inner">';

		if ($cards == 1) {
			for ($i = 0; $i < count($items); $i++) {
				$class = ($i == 0) ? ' active' : '';
				$html .= '<div class="carousel-item' . $class . '"  data-bs-interval="' . $interval . '">';
					$html .= '<img src="' . $items[$i]['image'] . '" class="d-block img-fluid" alt="...">';
					$align = ($items[$i]['align'] != '') ? 'text-align:' . $items[$i]['align'] . ' !important;' : '';
					$top = ($items[$i]['top'] != '') ? 'top:' . $items[$i]['top'] . ' xxx!important;' : '';

					$html .= '<div class="carousel-caption d-none d-md-block" style="' . $align . 'bottom:unset !important;' . $top . '">';
						$html .= '<h2 class="banner-title">' . str_replace('|', '<br>', $items[$i]['title']) . '</h2>';
						$html .= '<p class="banner-text">' . $items[$i]['text'] . '</p>';

						if ($items[$i]['button']) {
							$html .= '<a class="banner-url" href="' . $items[$i]['url'] . '"><button class="banner-button">' . $items[$i]['button'] . '</button></a>';
						}

					$html .= '</div>';
				$html .= '</div>';
			}
		}
		else {
			switch ($cards) {
				case 4: {
					$col = 3;
					break;
				}
				case 3: {
					$col = 4;
					break;
				}
				case 2: {
					$col = 6;
					break;
				}
			}

			for ($i = 0; $i < count($items); $i++) {
				$class = ($i == 0) ? ' active' : '';
				$html .= '<div class="carousel-item' . $class . '" data-bs-interval="' . $interval . '">';

					$html .= '<div class="col-md-' . $col . '">';
						$html .= '<div class="card">';
							$html .= '<div class="card-img">';
								$html .= '<img src="' . $items[$i]['image'] . '" class="img-fluid">';
							$html .= '</div>';
							$html .= '<div class="card-img-overlay"></div>';
						$html .= '</div>';
					$html .= '</div>';

				$html .= '</div>';
			}			
		}

		$html .= '</div>';

		if ($num_items > 1) {
			$html .= '<button class="carousel-control-prev" type="button" data-bs-target="#' . $id . '" data-bs-slide="prev">';
				$html .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
				$html .= '<span class="visually-hidden">Previous</span>';
			$html .= '</button>';
			$html .= '<button class="carousel-control-next" type="button" data-bs-target="#' . $id . '" data-bs-slide="next">';
				$html .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
				$html .= '<span class="visually-hidden">Next</span>';
			$html .= '</button>';
		}

		$html .= '</div>' . "\n";

		if ($cards > 1) {
			$html .= '<script>let items = document.querySelectorAll(".carousel .carousel-item");items.forEach((el) => {const minPerSlide = 4;let next = el.nextElementSibling;for (var i=1; i<minPerSlide; i++) {if (!next) {next = items[0];}let cloneChild = next.cloneNode(true);el.appendChild(cloneChild.children[0]);next = next.nextElementSibling;}});</script>';
			$html .= '<style>@media (max-width: 767px) {.carousel-inner .carousel-item > div {display: none;}.carousel-inner .carousel-item > div:first-child {
			display: block;}}.carousel-inner .carousel-item.active, .carousel-inner .carousel-item-next, .carousel-inner .carousel-item-prev {display: flex;} @media (min-width: 768px) {.carousel-inner .carousel-item-end.active, .carousel-inner .carousel-item-next {transform: translateX(25%);}.carousel-inner .carousel-item-start.active, .carousel-inner .carousel-item-prev {transform: translateX(-25%);}}.carousel-inner .carousel-item-end,.carousel-inner .carousel-item-start {transform: translateX(0);}.carousel-item .card{border-radius:0;border:0;}</style>';
		}
	}

	wp_reset_query();

	return $html;
}

//     ▄████████   ▄██████▄      ▄████████      ███   
//    ███    ███  ███    ███    ███    ███  ▀█████████▄
//    ███    █▀   ███    ███    ███    ███     ▀███▀▀██
//    ███         ███    ███   ▄███▄▄▄▄██▀      ███   ▀
//  ▀███████████  ███    ███  ▀▀███▀▀▀▀▀        ███ 
//           ███  ███    ███  ▀███████████      ███ 
//     ▄█    ███  ███    ███    ███    ███      ███ 
//   ▄████████▀    ▀██████▀     ███    ███     ▄████▀

function bb_get_previous_post_where($where, $in_same_term, $excluded_terms) {
	global $post, $wpdb;

	if (empty($post)) {
		return $where;
	}
	
	$taxonomy = 'banner';
	if (preg_match('/ tt.taxonomy = \'([^\']+)\'/i', $where, $match)) {
		$taxonomy = $match[1];
	}
	
	$_join = '';
	$_where = '';
	
	if ($in_same_term || !empty($excluded_terms)) {
		$_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
		$_where = $wpdb->prepare("AND tt.taxonomy = %s", $taxonomy);

		if (!empty($excluded_terms) && ! is_array($excluded_terms)) {
			$excluded_terms = explode(',', $excluded_terms);
			$excluded_terms = array_map('intval', $excluded_terms);
		}

		if ($in_same_term) {
			$term_array = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
			$term_array = array_diff($term_array, (array) $excluded_terms);
			$term_array = array_map('intval', $term_array);
	
			$_where .= " AND tt.term_id IN (" . implode(',', $term_array) . ")";
		}

		if (!empty($excluded_terms)) {
			$_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode(',', $excluded_terms) . '))';
		}
	}
		
	$current_menu_order = $post->menu_order;
	
	$query = $wpdb->prepare("SELECT p.* FROM $wpdb->posts AS p $_join WHERE p.post_date < %s  AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where" ,  $post->post_date, $current_menu_order, $post->post_type);
	$results = $wpdb->get_results($query);
			
	if (count($results) > 0) {
		$where .= $wpdb->prepare( " AND p.menu_order = %d", $current_menu_order );
	}
	else {
		$where = str_replace("p.post_date < '". $post->post_date  ."'", "p.menu_order > '$current_menu_order'", $where);
	}
	
	return $where;
}

function bb_get_previous_post_sort($sort) {
	global $post, $wpdb;
	
	$sort = 'ORDER BY p.menu_order ASC, p.post_date DESC LIMIT 1';
	return $sort;
}

function bb_get_next_post_where($where, $in_same_term, $excluded_terms) {
	global $post, $wpdb;

	if (empty($post)) {
		return $where;
	}
	
	$taxonomy = 'banner';
	if (preg_match('/ tt.taxonomy = \'([^\']+)\'/i',$where, $match)) {
		$taxonomy = $match[1];
	}
	
	$_join = '';
	$_where = '';
				
	if ($in_same_term || !empty($excluded_terms)) {
		$_join = " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
		$_where = $wpdb->prepare("AND tt.taxonomy = %s", $taxonomy);

		if (!empty($excluded_terms) && ! is_array($excluded_terms)) {
			$excluded_terms = explode(',', $excluded_terms);
			$excluded_terms = array_map('intval', $excluded_terms);
		}

		if ($in_same_term) {
			$term_array = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'ids']);
			$term_array = array_diff($term_array, (array) $excluded_terms);
			$term_array = array_map('intval', $term_array);
	
			$_where .= " AND tt.term_id IN (" . implode(',', $term_array) . ")";
		}

		if (!empty($excluded_terms)) {
			$_where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode(',', $excluded_terms) . '))';
		}
	}
		
	$current_menu_order = $post->menu_order;
	
	$query = $wpdb->prepare("SELECT p.* FROM $wpdb->posts AS p $_join WHERE p.post_date > %s AND p.menu_order = %d AND p.post_type = %s AND p.post_status = 'publish' $_where", $post->post_date, $current_menu_order, $post->post_type);
	$results = $wpdb->get_results($query);
			
	if (count($results) > 0) {
		$where .= $wpdb->prepare(" AND p.menu_order = %d", $current_menu_order);
	}
	else {
		$where = str_replace("p.post_date > '". $post->post_date  ."'", "p.menu_order < '$current_menu_order'", $where);
	}
	
	return $where;
}

function bb_get_next_post_sort($sort) {
	global $post, $wpdb; 
	
	$sort = 'ORDER BY p.menu_order DESC, p.post_date ASC LIMIT 1';	
	return $sort;    
}

function bb_pre_get_posts($query) {	
	return $query;
}

function bb_posts_orderby($order_by, $query) {
	global $wpdb;
	
	if ($query->query_vars['post_type'] != 'slide') {
		return $order_by;
	}
			
	return $order_by;
}

//     ▄████████       ▄█     ▄████████  ▀████    ▐████▀
//    ███    ███      ███    ███    ███    ███▌   ████▀ 
//    ███    ███      ███    ███    ███     ███  ▐███ 
//    ███    ███      ███    ███    ███     ▀███▄███▀ 
//  ▀███████████      ███  ▀███████████     ████▀██▄  
//    ███    ███      ███    ███    ███    ▐███  ▀███ 
//    ███    ███  █▄ ▄███    ███    ███   ▄███     ███▄
//    ███    █▀   ▀▀▀▀▀▀     ███    █▀   ████       ███▄

function bb_save_ajax_order() {
	global $wpdb;
	$nonce = $_POST['interface_sort_nonce'];
	
	if (!wp_verify_nonce($nonce, 'interface_sort_nonce')) {
		die();
	}
	
	parse_str($_POST['order'], $data);
	
	if (is_array($data)) {
		foreach($data as $key => $values) {
			if ($key == 'item') {
				foreach($values as $position => $id) {
					$id = (int)$id;
					$data = array('menu_order' => $position);
					$data = apply_filters('bb-save-ajax-order', $data, $key, $id);
					$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
				} 
			}
			else {
				foreach($values as $position => $id) {
					$id = (int)$id;
					$data = array('menu_order' => $position, 'post_parent' => str_replace('item_', '', $key));
					$data = apply_filters('bb-save-ajax-order', $data, $key, $id);
					$wpdb->update( $wpdb->posts, $data, array('ID' => $id) );
				}
			}
		}		
	}

	do_action('bb_order_update_complete');
}

function bb_save_archive_ajax_order() {
	global $wpdb, $userdata;
	
	$post_type = filter_var ( $_POST['post_type'], FILTER_SANITIZE_STRING);
	$paged = filter_var ( $_POST['paged'], FILTER_SANITIZE_NUMBER_INT);
	$nonce = $_POST['archive_sort_nonce'];
	
	if (!wp_verify_nonce($nonce, 'bb_archive_sort_nonce_' . $userdata->ID)) {
		die();
	}
	
	parse_str($_POST['order'], $data);
	
	if (!is_array($data) || count($data) < 1) {
		die();
	}
	
	$mysql_query = $wpdb->prepare("SELECT ID FROM " . $wpdb->posts . " WHERE post_type = %s AND post_status IN ('publish', 'pending', 'draft', 'private', 'future', 'inherit') ORDER BY menu_order, post_date DESC", $post_type);
	$results = $wpdb->get_results($mysql_query);
	
	if (!is_array($results) || count($results) < 1) {
		die();
	}
	
	$objects_ids = [];
	foreach($results as $result) {
		$objects_ids[] = (int)$result->ID;   
	}
	
	global $userdata;

	$objects_per_page = get_user_meta($userdata->ID ,'edit_' .  $post_type  .'_per_page', TRUE);
	$objects_per_page = apply_filters("edit_{$post_type}_per_page", $objects_per_page);
	
	if (empty($objects_per_page)) {
		$objects_per_page = 20;
	}
	
	$edit_start_at = $paged * $objects_per_page - $objects_per_page;
	$index = 0;
	for ($i = $edit_start_at; $i < ($edit_start_at + $objects_per_page); $i++) {
		if (!isset($objects_ids[$i])) {
			break;
		}
			
		$objects_ids[$i] = (int)$data['post'][$index];
		$index++;
	}
	
	foreach($objects_ids as $menu_order => $id) {
		$data = ['menu_order' => $menu_order];	
		$data = apply_filters('bb-save-ajax-order', $data, $menu_order, $id);
		$wpdb->update($wpdb->posts, $data, array('ID' => $id));
		clean_post_cache($id);
	}
		
	do_action('bb_order_update_complete');					
}

// some admin styling

function bb_admin_styling() {
	if (in_array(get_current_screen()->id, ['edit-banner', 'edit-slide'])) {
		echo '<style>';
			echo '.term-slug-wrap, .term-parent-wrap, .term-description-wrap { display: none }';
			echo '.ui-sortable-handle { background: transparent url("data:image/svg+xml;base64,' . base64_encode('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 50 50"><path fill="#a3a6a7" d="M 0 7.5 L 0 12.5 L 50 12.5 L 50 7.5 Z M 0 22.5 L 0 27.5 L 50 27.5 L 50 22.5 Z M 0 37.5 L 0 42.5 L 50 42.5 L 50 37.5 Z"/></svg>') . '") no-repeat 12px 35px; }';
		echo '</style>';
	}
}

// caller function

function basic_banner($banner) {
	echo do_shortcode('[banner]' . $banner . '[/banner]');
}

//     ▄█    █▄        ▄████████   ▄█           ▄███████▄
//    ███    ███      ███    ███  ███          ███    ███
//    ███    ███      ███    █▀   ███          ███    ███
//   ▄███▄▄▄▄███▄▄   ▄███▄▄▄      ███          ███    ███
//  ▀▀███▀▀▀▀███▀   ▀▀███▀▀▀      ███        ▀█████████▀ 
//    ███    ███      ███    █▄   ███          ███ 
//    ███    ███      ███    ███  ███▌    ▄    ███ 
//    ███    █▀       ██████████  █████▄▄██   ▄████▀

// minifying functions

function bb_minify_css($input) {
	if (trim($input) === '') {
		return $input;
	}
	return preg_replace([
			'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
			'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
			'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
			'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
			'#(background-position):0(?=[;\}])#si',
			'#(?<=[\s:,\-])0+\.(\d+)#s',
			'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
			'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
			'#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
			'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
			'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
		], [
			'$1',
			'$1$2$3$4$5$6$7',
			'$1',
			':0',
			'$1:0 0',
			'.$1',
			'$1$3',
			'$1$2$4$5',
			'$1$2$3',
			'$1:0',
			'$1$2'
		],
	$input);
}

function bb_minify_js($input) {
	if (trim($input) === '') {
		return $input;
	}
	return preg_replace([
			'#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
			'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
			'#;+\}#',
			'#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
			'#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
		], [
			'$1',
			'$1$2',
			'}',
			'$1$3',
			'$1.$3'
		],
	$input);
}

function bb_plural($string) {
	switch (substr($string, -1)) {
		case 'y': {
			$plural = rtrim($string, 'y') . 'ies';
			break;
		}
		case 'h': {
			$plural = $string . 'es';
			break;
		}
		case 's': {
			$plural = $string . 'es';
			break;
		}
		default: {
			$plural = $string . 's';
		}
	}

	return $plural;
}


//   ▄█   ███▄▄▄▄▄     ▄█       ███      
//  ███   ███▀▀▀▀██▄  ███   ▀█████████▄  
//  ███▌  ███    ███  ███▌     ▀███▀▀██  
//  ███▌  ███    ███  ███▌      ███   ▀  
//  ███▌  ███    ███  ███▌      ███      
//  ███   ███    ███  ███       ███      
//  ███   ███    ███  ███       ███      
//  █▀     ▀█    █▀   █▀       ▄████▀

define('_BB', _bbSettings::get_settings());

// actions

add_action('init', 'bb_init');
add_action('admin_head', 'bb_admin_styling');
add_action('admin_enqueue_scripts', 'bb_admin_scripts');
add_action('add_meta_boxes', 'bb_add_metaboxes');
add_action('save_post', 'bb_save_postdata');
add_action('edit_form_top','bb_add_buttons_to_post_edit');
add_action('banner_pre_add_form','bb_pre_banner_edit_form_fields');
add_action('banner_edit_form_fields','bb_banner_edit_form_fields', 10, 2);
add_action('banner_add_form_fields','bb_banner_edit_form_fields', 10, 2);
add_action('edited_banner', 'bb_banner_save_form_fields');
add_action('created_banner', 'bb_banner_save_form_fields');
add_action('restrict_manage_posts', 'bb_add_filter_to_slides_list');
add_action('wp_ajax_update-custom-type-order', 'bb_save_ajax_order');
add_action('wp_ajax_update-custom-type-order-archive', 'bb_save_archive_ajax_order');

// filters

if (is_admin()) {
	add_filter('parent_file', 'bb_set_current_menu');
	add_filter('parse_query', 'bb_slides_filter');
	add_filter('get_previous_post_where', 'bb_get_previous_post_where', 99, 3);
	add_filter('get_previous_post_sort', 'bb_get_previous_post_sort');
	add_filter('get_next_post_where', 'bb_get_next_post_where', 99, 3);
	add_filter('get_next_post_sort', 'bb_get_next_post_sort');
}

// shortcodes

add_shortcode('banner', 'bb_shortcode');

// boot plugin

add_action('init', function() {
	if (is_admin()) {
		new _bbMenu(_URL_BASIC_BANNER);

		// init updater

		if (get_option('auth_key') !== '') {
			$updater = new WPU(__FILE__);
			$updater->set_versions('6.4', '6.4.3');
			$updater->set_username('nullstep');
			$updater->set_repository('basic_banner');
			$updater->authorize(get_option('auth_key'));
			$updater->initialize();
		}
	}
});

add_action('rest_api_init', function() {
	_bbSettings::args();
	$api = new _bbAPI();
	$api->add_routes();
});

// eof