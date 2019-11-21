<?php
/*
Plugin Name: WP Thumbnail Linkbox Shortcode
Plugin URI: http://e-joint.jp/works/wp-thumbnail-linkbox-shortcode/
Description: You can easily create links with thumbnails with shortcode.
Version: 0.4.0
Author: Takashi Fujisaki
Author URI: http://e-joint.jp
Text Domain: wp-thumbnail-linkbox-shortcode
Domain Path: /languages
License: GPL2
*/

/*  Copyright 2017 e-JOINT.jp (email : mail@e-joint.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
     published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Wp_thumbnail_linkbox_shortcode
{

  private $options;
  const VERSION = '0.4.0';

  public function __construct(){

    //翻訳ファイルの読み込み
    load_plugin_textdomain('wp-thumbnail-linkbox-shortcode', false, basename(dirname(__FILE__)) . '/languages');

    //設定画面を追加
    add_action( 'admin_menu', array(&$this, 'add_plugin_page') );

    //設定画面の初期化
    add_action( 'admin_init', array(&$this, 'page_init') );

    //スタイルシートの読み込み
    add_action( 'wp_enqueue_scripts', array(&$this, 'add_styles') );

    //ショートコードを使えるようにする
    add_shortcode('link', array(&$this, 'generate_shortcode') );

    $this->options = get_option('wptls-setting');
  }

  //設定画面を追加
  public function add_plugin_page() {

    add_options_page(
      __('WP Thumbnail Linkbox', 'wp-thumbnail-linkbox-shortcode' ),
      __('WP Thumbnail Linkbox', 'wp-thumbnail-linkbox-shortcode' ),
      'manage_options',
      'wptls-setting',
      array(&$this, 'create_admin_page')
    );
  }

  //設定画面を生成
  public function create_admin_page() {

    ?>
    <div class="wrap">
      <h2>WP Thumbnail Linkbox Shortcode</h2>
      <?php

      global $parent_file;
      if ( $parent_file != 'options-general.php' ) {
        require(ABSPATH . 'wp-admin/options-head.php');
      }
      ?>

      <form method="post" action="options.php">
      <?php
        settings_fields( 'wptls-setting' );
        do_settings_sections( 'wptls-setting' );
        submit_button();
      ?>
      </form>

      <h3><?php echo __('How to Use', 'wp-thumbnail-linkbox-shortcode'); ?></h3>
      <p><?php echo __('1. Please drag and drop the link (bookmarklet) below to the bookmark bar.', 'wp-thumbnail-linkbox-shortcode'); ?></p>
      <p><a href="javascript:(function(){var n='WP Thumbnail Linkbox Shortcode';var p='[link href=&quot;'+location.href+'&quot; title=&quot;'+document.title+'&quot;]';window.prompt(n,p);void(0);})();">WP Thumbnail Linkbox Shortcode</a></p>
      <p><?php echo __('2. Open the page for which you want to create a link and execute the bookmarklet.', 'wp-thumbnail-linkbox-shortcode'); ?></p>
      <p><?php echo __('3. A short code will be displayed in the dialog box, please copy and paste it in the WordPress article.', 'wp-thumbnail-linkbox-shortcode'); ?></p>
</p>

    </div>
  <?php
  }

  //設定画面の初期化
  public function page_init(){
    register_setting('wptls-setting', 'wptls-setting', array(&$this, 'sanitize'));
    add_settings_section('wptls-setting-section-id', '', '', 'wptls-setting');

    add_settings_field( 'nocss', __('Do not use default CSS', 'wp-thumbnail-linkbox-shortcode'), array( &$this, 'nocss_callback' ), 'wptls-setting', 'wptls-setting-section-id' );
    add_settings_field( 'target', __('Value of target attribute', 'wp-thumbnail-linkbox-shortcode'), array( &$this, 'target_callback' ), 'wptls-setting', 'wptls-setting-section-id' );
    add_settings_field( 'width', __('Width of thumbnail to be acquired (px)(Positive integer)', 'wp-thumbnail-linkbox-shortcode') . '*', array( &$this, 'width_callback' ), 'wptls-setting', 'wptls-setting-section-id' );
    add_settings_field( 'ratio', __('Ratio of width（Positive integer or decimal)', 'wp-thumbnail-linkbox-shortcode') . '*', array( &$this, 'ratio_callback' ), 'wptls-setting', 'wptls-setting-section-id' );
    add_settings_field( 'thumbnail-size', __('For internal links, use "eyecatch" for thumbnails (Select thumbnail size to enable it.)', 'wp-thumbnail-linkbox-shortcode'), array(&$this, 'thumbnail_size_callback'), 'wptls-setting', 'wptls-setting-section-id');
  }


  public function sanitize( $input ){

      $new_input = array();

      $new_input['nocss'] = $input['nocss'];
      $new_input['target'] = $input['target'];
      $new_input['thumbnail-size'] = $input['thumbnail-size'];

      //整数かどうかチェック
      if($input['width'] == '' || $this->is_numeric($input['width'])){
        $new_input['width'] = $input['width'];

      } else {
        //エラーを出力
        add_settings_error('wptls-setting', 'message', __('Please enter a positive integer for Width of thumbnail to be acquired.', 'wp-thumbnail-linkbox-shortcode'));

        //値をDBの設定値に戻す
        $new_input['width'] = isset($this->options['width']) ? $this->options['width'] : '';
      }

      //正の整数または小数かどうかチェック
      if($input['ratio'] == '' || $this->is_numeric_or_decimal($input['ratio'])){
        $new_input['ratio'] = $input['ratio'];

      } else {
        //エラーを出力
        add_settings_error('wptls-setting', 'message', __('Please enter a positive integer or decimal for Ratio of width.', 'wp-thumbnail-linkbox-shortcode'));

        //値をDBの設定値に戻す
        $new_input['ratio'] = isset($this->options['ratio']) ? $this->options['ratio'] : '';
      }
      return $new_input;
  }

  public function nocss_callback(){
    $checked = isset($this->options['nocss']) ? checked($this->options['nocss'], 1, false) : '';
    ?><input type="checkbox" id="nocss" name="wptls-setting[nocss]" value="1"<?php echo $checked; ?>><?php
  }

  public function target_callback()
  {
    ?><select name="wptls-setting[target]">
      <option value=""><?php echo __('None', 'wp-thumbnail-linkbox-shortcode'); ?></option>
      <option value="_blank"<?php selected($this->options['target'], '_blank'); ?>>_blank</option>
      </select><?php
  }

  public function width_callback(){
    ?><input type="text" name="wptls-setting[width]" value="<?php echo isset($this->options['width']) ? $this->options['width'] : ''; ?>">
    <p><?php echo __('Default', 'wp-thumbnail-linkbox-shortcode'); ?>: 72 （<?php echo __('If nothing is entered it will be the default value.', 'wp-thumbnail-linkbox-shortcode'); ?>）</p>
    <?php

  }

  public function ratio_callback(){
    ?><input type="text" name="wptls-setting[ratio]" value="<?php echo isset($this->options['ratio']) ? $this->options['ratio'] : ''; ?>">
    <p><?php echo __('Default', 'wp-thumbnail-linkbox-shortcode'); ?>: 1 （<?php echo __('If nothing is entered it will be the default value.', 'wp-thumbnail-linkbox-shortcode'); ?>）</p>
    <?php
  }

  public function thumbnail_size_callback() {
    //設定されているサムネイルサイズの一覧を取得
    $size_list = get_intermediate_image_sizes();

    ?><select name="wptls-setting[thumbnail-size]">
        <?php echo '<option value="disabled"' . selected($this->options['thumbnail-size'], 'disabled') . '>' . __('Disabled', 'wp-thumbnail-linkbox-shortcode') . '</option>' . "\n";?>
        <?php foreach( $size_list as $size ){
          echo '<option value="' . $size . '"' . selected($this->options['thumbnail-size'], $size) . '>' . $size . '</option>' . "\n";
        } ?>
        <?php echo '<option value="full"' . selected($this->options['thumbnail-size'], 'full') . '>full</option>' . "\n";?>
      </select>
    <?php
  }

  //スタイルシートの追加
  public function add_styles() {

    if(isset($this->options['nocss'])) {
      if ( !$this->options['nocss'] ) {
        wp_enqueue_style( 'wptls', plugins_url( 'assets/css/wp-thumbnail-linkbox-shortcode.css', __FILE__ ), array(), null, 'all' );
      }
    } else {
      wp_enqueue_style( 'wptls', plugins_url( 'assets/css/wp-thumbnail-linkbox-shortcode.css', __FILE__ ), array(), null, 'all' );
    }
  }

  // サムネイルを選択
  private function use_eyecatch() {

    if(isset($this->options['thumbnail-size'])) {
      if($this->options['thumbnail-size'] !== 'disabled') {
        return true;
      } else {
        return false;
      }
    }
  }

  private function is_internal_link($href) {
    $site_domain = get_bloginfo('url');

    if(strpos($href, $site_domain) === 0) {
      return true;
    } else {
      return false;
    }
  }

  // 内部リンクのeyecatchが利用できる場合はpostIDを返す
  public function is_eyecatch($href) {
    $post_id = url_to_postid($href);

    if($this->use_eyecatch() && $this->is_internal_link($href) && has_post_thumbnail($post_id)) {
      return $post_id;
    } else {
      return false;
    }
  }

  //正の整数かどうか判別する
  private function is_numeric($num){
    // return ctype_digit(strval($num)) && $num != 0 ? true : false;
    return preg_match('/^[1-9][0-9]*$/u', $num) ? true : false;
  }

  //正の整数または小数かどうか判別する
  private function is_numeric_or_decimal($num){
    return preg_match('/^([1-9]\d*|0)(\.\d+)?$/u', $num) ? true : false;
  }

  //URLの表記が正しいかどうかチェックする
  private function is_url( $text ) {
    if ( preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $text ) ) {
      return true;
    } else {
      return false;
    }
  }


  //サムネイル付きリンクを作成するショートコード(要is_url())
  public function generate_shortcode( $atts ) {
    extract( shortcode_atts( array(
      'href' => null, //URL
      'width' => null, //画像の横幅
      'ratio' => null, //画像の縦に対して横の比率。2なら横が2倍
      'show_domain' => true, //ドメインをタイトルの下に表示させるかどうか
      'title' => null,
      'target' => null
    ), $atts) );

    $options = get_option('wptls-setting');

    $name = 'wptls'; //クラスの接頭辞

    //ショートコードから引数が渡されたら優先する｡ない場合は設定ページで設定された数値を使用する
    $width = $this->is_numeric($width) ? $width : $this->options['width'];
    $ratio = $this->is_numeric_or_decimal($ratio) ? $ratio : $this->options['ratio'];

    //ショートコードでtarget=noneが渡された場合はターゲットなし
    if($target == 'none'){
      $target = "";
    } else {
      //ショートコードでtargetがない､またはnone以外の値が指定された場合
      $option_target = $options['target'];
      $option_target = $option_target ? ' target="' . esc_attr($option_target) . '"' : "";
      //ショートコードからtargetの引数が渡されたら優先する｡なければ設定ページで指定された値を使う｡
      $target = !is_null($target) ? ' target="_' . esc_attr($target) . '"' : $option_target;
    }

    //値がない場合はデフォルト値を設定する
    if(!$width) $width = 72;
    if(!$ratio) $ratio = 1;

    $title = esc_attr($title);

    $param = array();

    $param['w'] = $width;

    if ( isset( $param['w'] ) && $ratio != 0 ) {
      $height = round( $width / $ratio );
      $param['h'] = $height;
    }

    $query = $param ? '?' . http_build_query( $param ) : null;

    $parse = parse_url( $href );
    $domain = $parse['host'];

    if ( $this->is_url($href) ) {

      $post_id = $this->is_eyecatch($href);

      $html  = '<div class="' . $name . '">';
      $html .= '<a class="' . $name . '__item" href="' . $href . '"' . $target . '>';
      $html .= '<figure class="' . $name . '__img">';

      if($post_id) {
        $html .= get_the_post_thumbnail( $post_id, $this->options['thumbnail-size']);

      } else {
        $html .= '<img src="https://s.wordpress.com/mshots/v1/' . rawurlencode($href) . $query . '" alt="' . $title . '">';
      }

      $html .= '</figure>';
      $html .= '<div class="' . $name . '__content">';
      $html .= '<div class="' . $name . '__title">' . $title . '</div>';
      if($show_domain){
        $html .= '<div class="' . $name . '__domain">';
        $html .= '<img class="' . $name . '__favicon" src="https://www.google.com/s2/favicons?domain=' . $domain . '">';
        $html .= '<span class="' . $name . '__domain-name">' . $domain . '</span>';
        $html .= '</div>';
      }
      $html .= '</div>';
      $html .= '</a>';
      $html .= '</div>';
      $html .= "\n";

      return $html;

    } else {
      return __('URL format is invalid.(WP Thumbnail Linkbox Shortcode)', 'wp-thumbnail-linkbox-shortcode');
    }
  }
}

$wptls = new Wp_thumbnail_linkbox_shortcode();
