<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
check_status(ACCESS_ADMINISTRATOR);

# ---------- AJAX: return last 5 kB of log ----------------------------------
if (isset($_GET['ajaxlog'])) {
    $log = CXO_ATM_LOG;
    if (is_readable($log)) {
        $size = filesize($log);
        echo file_get_contents($log, false, null, max(0, $size-5120), 5120);
    }
    exit;                                      // stop rendering
}

/* Clear-log button pressed? */
if (isset($_POST['clear_log'])) {
    if (is_writable(CXO_ATM_LOG)) {
        file_put_contents(CXO_ATM_LOG, '');
        $page['infos'][] = 'Log cleared';
    } else {
        $page['errors'][] = 'Log file not writable';
    }
}

/* bulk-button pressed? */
if (isset($_POST['bulk'])) {
    $movs = query2array(
        "SELECT id, path FROM ".IMAGES_TABLE." WHERE path LIKE '%.mov'",
        'id', 'path'
    );
    foreach ($movs as $id => $row) {
        _cxo_transcode_and_update($id, $row);
    }
    $page['infos'][] = count($movs).' MOV file(s) converted';
}

/* View */
global $template;
$template->set_filename('cxo_bulk_convert', __DIR__.'/admin.tpl');
$template->assign('MOV_LEFT', pwg_db_fetch_row(
    pwg_query("SELECT COUNT(*) FROM ".IMAGES_TABLE." WHERE path LIKE '%.mov'")
)[0]);
$template->assign_var_from_handle('ADMIN_CONTENT', 'cxo_bulk_convert');