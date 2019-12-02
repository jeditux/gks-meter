<?php
/**
 * @package Gks_Meters
 * @version 1.0
 */
/*
Plugin Name: Gks Meters
Plugin URI: http://www.jeditux.ru/
Description: Plugin for meters transferring.
Author: Anatoliy Gostev
Version: 1.0
Author URI: http://www.jeditux.ru/
*/

define('gks_meters_path', str_replace('\\', '/', DIRNAME(__FILE__)) . '/');
define('gks_meters_pageID', 3);

add_filter('the_content', 'gks_meters_content');

function gks_meters_content($content){
	// ID страницы для перехвата
	$pageID = get_option('gks_meters_pageID', gks_meters_pageID);
	// Вернем контент
	return ( is_page($pageID) ) ? gks_meters_page($content) : $content;
}

function gks_check_account($account){
	global $wpdb;
	$table_readings = $wpdb->prefix.gks_readings;
	$readings = intval($wpdb->get_var("SELECT COUNT(*) FROM $table_readings WHERE account=$account"));
	if($readings>0) return true;
	else return false;
	//if ($account == '01022014') return true;
	//else return false;
}

function gks_meters_page($content){
	global $wpdb;
	
	$table_readings = $wpdb->prefix.gks_readings;
	echo "<h2>Ввод показаний</h2>";
	if (isset($_POST['gks_check_btn'])){
		if (function_exists ('check_admin_referer') )
		{
			check_admin_referer('gks_base_setup_form');
		}
		$account = $_POST['gks_account'];
		if (gks_check_account($account)){
			$readings = $wpdb->get_results("SELECT * FROM $table_readings WHERE account=$account");
			$electricity = intval($readings[0]->electricity);
			$electricity2 = intval($readings[0]->electricity2);
			$cold_water = intval($readings[0]->cold_water);
			$cold_water2 = intval($readings[0]->cold_water2);
			$hot_water = intval($readings[0]->hot_water);
			$hot_water2 = intval($readings[0]->hot_water2);
			$input_date = $readings[0]->input_date;
			echo "<font color=\"#00CC00\">Лицевой счет корректный. Последние показания переданы ".$input_date."</font>";
		}
		else{
			echo "<font color=\"#CC0000\">Ваши показания не могут быть переданы, проверьте правильность занесения данных или Ваш дом отсутствует в перечне домов ОАО \"Сети\"</font>";
		}
	}
	
	if (isset($_POST['gks_input_btn'])){
		if (function_exists ('check_admin_referer') )
		{
			check_admin_referer('gks_base_setup_form');
		}
		$account = $_POST['gks_account'];
		if (gks_check_account($account)){
			//echo "<h3>Лицевой счет корректный</h3>";
			$account = $_POST['gks_account'];
			//$electricity = intval($_POST['gks_electricity']);
			//$electricity2 = intval($_POST['gks_electricity2']);
			$cold_water = intval($_POST['gks_cold_water']);
			$cold_water2 = intval($_POST['gks_cold_water2']);
			$hot_water = intval($_POST['gks_hot_water']);
			$hot_water2 = intval($_POST['gks_hot_water2']);
			$wpdb->update(
				$table_readings,
				array('input_date' => current_time('mysql'), 'cold_water' => $cold_water, 'cold_water2' => $cold_water2, 'hot_water' => $hot_water, 'hot_water2' => $hot_water2),
				array('account' => $account),
				array('%s', '%d', '%d', '%d', '%d', '%d', '%d'),
				array('%s')
			);
			$readings = $wpdb->get_results("SELECT * FROM $table_readings WHERE account=$account");
			$input_date = $readings[0]->input_date;
			echo "<font color=\"#00CC00\">Показания приняты. Последние показания переданы ".$input_date."</font>";
		}
		else{
			echo "<font color=\"#CC0000\">Лицевой счет указан неверно</font>";
		}
	}
	
	echo "
		<form name='gks_base_setup' method='post' action='".$_SERVER['PHP_SELF']."?page_id=".get_option('gks_meters_pageID', gks_meters_pageID)."&amp;updated=true'>
	";
	
	if(function_exists('wp_nonce_field')){
		wp_nonce_field('gks_base_setup_form');
	}	
	echo
	"
		<table>
			<tr>
				<td style='text-align:right;'>Код лицевого счета для оплаты ЖКУ через ИПТС</td>
				<td><input type='text' name='gks_account' value='".$account."'/></td>
				<td style='text-align:center;'>
					<input type='submit' name='gks_check_btn' value='Проверить' style='width:140px; height:40px'/>
				</td>
				<td></td>
			</tr>
			<!--<tr>
				<td style='text-align:right;'>Электроэнергия</td>
				<td><input type='text' name='gks_electricity' value='".$electricity."'/></td>
				<td></td><td></td>
			</tr>
			<tr>
				<td style='text-align:right;'>Электроэнергия (ночь)</td>
				<td><input type='text' name='gks_electricity2' value='".$electricity2."'/></td>
				<td></td><td></td>
			</tr>-->
			<tr>
				<td style='text-align:right;'>Холодная вода (счетчик в ванной)</td>
				<td><input type='text' name='gks_cold_water' value='".$cold_water."'/></td>
				<td style='text-align:right;'>Холодная вода (счетчик на кухне)</td>
				<td><input type='text' name='gks_cold_water2' value='".$cold_water2."'/></td>
			</tr>
			<tr>
				<td style='text-align:right;'>Горячая вода (счетчик в ванной)</td>
				<td><input type='text' name='gks_hot_water' value='".$hot_water."'/></td>
				<td style='text-align:right;'>Горячая вода (счетчик на кухне)</td>
				<td><input type='text' name='gks_hot_water2' value='".$hot_water2."'/></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td style='text-align:center;'>
					<input type='submit' name='gks_input_btn' value='Ввод' style='width:140px; height:40px'/>
				</td>
			</tr>
			<tr>
				<td colspan=4 style='text-align:center;'>
					<p>Показания индивидуальных приборов учета на данной странице передаются в том случае, если:</p>
					<p>1. Ваш дом находится на управлении Управляющей компании ООО \"Зелёный город\",</p>
					<p>2. Ваш дом не выбрал способ управления и расчет коммунальных услуг осуществляет ресурсоснабжающая организация ОАО \"Сети\",</p>
					<p>3. Ваш дом в индивидуальной частной собственности (передаются показания холодной воды).</p>
					<p>4. В случае если Ваш дом находится на управлении ООО УК «Мэйзон» показания индивидуальных приборов учета  передаются непосредственно в Управляющую компанию.</p>
					<p></p>
					<p>Кроме того, показания приборов учета передаются по телефонам 8(818-52)5-83-18, 8-900-911-06-38, 8-900-911-06-41, 8-900-911-06-42.</p>
				</td>
			</tr>
		</table>
	</form>
	";
}

function gks_install(){
	global $wpdb;
	
	$table_readings = $wpdb->prefix.gks_readings;
	
    $sql1 = 
	"
		CREATE TABLE IF NOT EXISTS `".$table_readings."` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `account` varchar(64) NOT NULL,
		  `input_date` datetime NOT NULL,
		  `electricity` int(10) NOT NULL,
		  `electricity2` int(10) NOT NULL,
		  `cold_water` int(10) NOT NULL,
		  `cold_water2` int(10) NOT NULL,
		  `hot_water` int(10) NOT NULL,
		  `hot_water2` int(10) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";
	$wpdb->query($sql1);
	$page = array (
		'post_title' => 'Передача показаний приборов учета',
		'post_content' => 'Если Вы видите этот текст - что-то пошло не так',
		'post_status' => 'publish',
		'post_author' => 1,
		'post_type' => 'page'
	);
	$pageID = wp_insert_post($page);
	
	//add_option('gks_meters_pageID', is_numeric($pageID) ? $pageID : gks_meters_pageID);
	update_option('gks_meters_pageID', is_numeric($pageID) ? $pageID : gks_meters_pageID);
}

function gks_uninstall(){
	global $wpdb;
	$table_readings = $wpdb->prefix.gks_readings;
	
	$sql1 = "DROP TABLE `".$table_readings."`;";
	$wpdb->query($sql1);
	//delete_option('gks_status_url');
	wp_delete_post(get_option('gks_meters_pageID', gks_meters_pageID));
	delete_option('gks_meters_pageID');
}

register_activation_hook(__FILE__, 'gks_install');
//register_deactivation_hook(__FILE__, 'gks_uninstall');
add_action('admin_menu', 'gks_add_admin_page');
add_action('init', 'gks_run');
?>