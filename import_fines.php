<?php
/**
 * Copyright (C) 2018 Drajat Hasan (drajathasan20@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Fines Import section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

// max chars in line for file operations
$max_chars = 1024*100; // SLiMS Biblio max_char standard

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// Empty checking
function emptyChecking($value) {
	global $dbs;
	 if ($value == '') {
		$value = 'NULL';
		return $value;
	 } else {
	 	$value = str_replace('"', '', $value);
	 	$value = $dbs->escape_string($value);
	 	return "'".$value."'";
	 }
}

//Export
if (isset($_POST['doImport'])) {
	$file = $_FILES['importFile']['tmp_name'];
	$maxLine = (integer)$_POST['maxLine'];
	$separator = trim(htmlentities($_POST['separator']));
	$enclosure = trim(htmlentities($_POST['enclosure']));
	$handle = fopen($file,"r");
	$inserted = 0;
	$num = 0;

	// Check extention
	$ext_check = pathinfo($_FILES["importFile"]['name'], PATHINFO_EXTENSION);

	if ($ext_check != 'csv') {
		utility::jsAlert('Ekstensi file yang diijinkan yaitu .csv!');
		echo '<script type="text/javascript">parent.$(\'#importFile\').val(\'\');</script>';
		exit();
	}

	// Start Time to count query speed
	$starttime = microtime(true);
	//loop through the csv file and insert into database
	while ($data = fgetcsv($handle, $maxLine, $separator, $enclosure)) {
		// Check
		if ($data[0]) {
			// Insert
	        $insert = $dbs->query("INSERT INTO fines VALUES
	            (
	                ".emptyChecking($data[0]).",
	                ".emptyChecking($data[1]).",
	                ".emptyChecking($data[2]).",
	                ".emptyChecking($data[3]).",
	                ".emptyChecking($data[4]).",
	                ".emptyChecking($data[5])."
	            )
	        ");
	        // Check insert
	        if ($insert) {
	        	$inserted++;
	        }
	        // Calc total of row
	        $num++;
	    }
	}
	// Stop count time
	$endtime = microtime(true);
	// Calculates total time taken
	$duration = $endtime - $starttime;
	$duration = substr($duration, 0,5);
	utility::jsAlert('Berhasil memasukan : '.$inserted.' data dari '.$num.' antrian data dalam '.$duration.' detik');
	echo '<script type="text/javascript">parent.$(\'#importFile\').val(\'\');</script>';
	exit();
}
?>
<fieldset class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    <h2><?php echo __('Import Tool'); ?></h2>
    </div>
    <div class="infoBox">
       Impor data denda.
	</div>
</div>
</fieldset>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Import Now').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input = simbio_form_element::textField('file', 'importFile');
$str_input .= ' Maximum '.$sysconf['max_upload'].' KB';
$form->addAnything(__('File To Import'), $str_input);
// records offset
$form->addTextField('text', 'separator', 'Pemisah', ',', 'style="width: 10%;"');
$form->addTextField('text', 'enclosure', 'Pagar', '\'', 'style="width: 10%;"');
$form->addTextField('text', 'maxLine', 'Maksimal panjang baris', $max_chars, 'style="width: 10%;"');
// output the form
echo $form->printOut();