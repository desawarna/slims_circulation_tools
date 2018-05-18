# slims_circulation_tools

Pindahkan semua file dalam folder admin/modules/circulation
lalu edit file submenu.php pada folder admin/modules/circulation, setelah itu tambahkan skrip dibawah berikut.

$menu[] = array('Header', __('Tools'));
$menu[] = array('Impor data denda', MWB.'circulation/import_fines.php', 'Impor data denda');
$menu[] = array('Expor data denda', MWB.'circulation/export_fines.php', 'Expor data denda');
$menu[] = array('Impor data peminjaman buku', MWB.'circulation/import_loan.php', 'Impor data peminjaman');
$menu[] = array('Expor data peminjaman buku', MWB.'circulation/export_loan.php', 'Expor data peminjaman');
