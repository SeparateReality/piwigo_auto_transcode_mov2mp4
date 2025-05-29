<?php
/*
Plugin Name: CXO Auto Transcode MOV
Version: 15.e
Description: Auto-transcodes .mov → .mp4 on upload + bulk converter via admin page
Author: cxo
Has Settings: true
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

const CXO_ATM_LOG = __DIR__ . '/cxo_transcode.log';

/* -------------------------------------------------------------------------- */
/* Helper: tiny logger                                                        */
function cxo_log($msg): void
{
    file_put_contents(
        CXO_ATM_LOG,
        '[' . date('Y-m-d H:i:s') . "] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}

/* -------------------------------------------------------------------------- */
/* Upload hooks (API + HTML form)                                             */
add_event_handler('ws_images_add', 'cxo_after_mov_upload_api', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);
add_event_handler('loc_end_add_uploaded_file', 'cxo_after_mov_upload_html', EVENT_HANDLER_PRIORITY_NEUTRAL, 1);
function cxo_after_mov_upload_api($image_id, $fileInfo): void
{   // handle API hook
    _cxo_transcode_and_update($image_id, $fileInfo['path']);
}

function cxo_after_mov_upload_html($fileInfo): void
{   // handle HTML-form hook
    _cxo_transcode_and_update($fileInfo['id'], $fileInfo['path']);
}

/* -------------------------------------------------------------------------- */
/* Shared worker: transcode + DB switch + delete source                       */
function _cxo_transcode_and_update($image_id, $rel_mov_path): bool
{
    // only used for .mov files
    if (!preg_match('#\.mov$#i', $rel_mov_path)) {
        return true;
    }

    $rel_mp4 = preg_replace('#\.mov$#i', '.mp4', $rel_mov_path);
    $abs_mov = realpath(PHPWG_ROOT_PATH . ltrim($rel_mov_path, './'));
    if ($abs_mov === false) {
        cxo_log("ID $image_id – realpath failed");
        return false;
    }
    $abs_mp4 = dirname($abs_mov) . '/' . basename($rel_mp4);

    cxo_log("ID $image_id – start on $abs_mov");

    $codec = trim(shell_exec(
        "/usr/bin/ffprobe -v error -select_streams v:0 -show_entries stream=codec_name -of default=nw=1:nk=1 '$abs_mov'"
    ));

    $ffmpeg = in_array($codec, ['h264', 'hevc'])
        ? "/usr/bin/ffmpeg -i '$abs_mov' -c copy -movflags +faststart '$abs_mp4'"
        : "/usr/bin/ffmpeg -i '$abs_mov' -c:v libx264 -preset medium -crf 22 -c:a aac -b:a 128k -movflags +faststart '$abs_mp4'";

    $exit = 0;
    exec("$ffmpeg 2>/dev/null", $_, $exit);
    if ($exit !== 0) {
        cxo_log("ID $image_id – ffmpeg failed (exit $exit)");
        return false;
    }

    unlink($abs_mov);
    cxo_log("ID $image_id – MOV deleted, switching DB");

    pwg_query("
    UPDATE " . IMAGES_TABLE . "
       SET file = REPLACE(file , '.mov', '.mp4'),
           path = REPLACE(path , '.mov', '.mp4')
     WHERE id  = " . intval($image_id)
    );

    cxo_log("ID $image_id – DB updated to .mp4");
    return true;
}
