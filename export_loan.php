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

/* Loan Export section */

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

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// Export
if (isset($_POST['doExport'])) {
	$fine_q = $dbs->query('SELECT * FROM loan');
	// Check filename
	if (empty($_POST['fileName'])) {
		utility::jsAlert('Nama file tidak boleh kosong!');
		exit();
	}

	// Query check
	if (!$fine_q) {
		utility::jsAlert('Gagal mengambil data : '.$dbs->error);
	} else {
		$opfile = fopen('php://output', 'w');
		$filename = (string)$_POST['fileName'].'.csv';
		if ($opfile AND $fine_q) {
		    header('Content-Type: text/csv');
		    header('Content-Disposition: attachment; filename="'.$filename.'"');
		    header('Pragma: no-cache');
		    header('Expires: 0');
		    while ($row = $fine_q->fetch_array(MYSQLI_NUM)) {
		        fputcsv($opfile, array_values($row));
		    }
		}
	}
	exit();
}
?>
<fieldset class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    <h2><?php echo __('Export Tool'); ?></h2>
    </div>
    <div class="infoBox">
       Expor data peminjaman.
	</div>
</div>
</fieldset>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doExport" value="'.__('Export Now').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// records offset
$form->addTextField('text', 'fileName', 'Nama File', 'export_loan', 'style="width: 20%;"');
// output the form
echo $form->printOut();