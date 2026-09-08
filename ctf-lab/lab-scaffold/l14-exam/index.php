<?php
// ============================================
// L14 毕业考 · PicVault 照片站 · 会话恢复
// 旗在本目录：flag_l14_3b7e.txt
// ============================================
error_reporting(0);   // 本站风格：静默运行，不吐任何警告

class AvatarKeeper {
    public $notifier;
    public function __wakeup() {
        // 防篡改：对象恢复后立即把外发通道重置为安全默认值
        $safe = new MailNotifier();
        $safe->box = __DIR__ . '/uploads/index.html';
        $this->notifier = $safe;
    }
    public function __destruct() {
        // 收尾：通知一声
        $this->notifier->notify("keeper closed");
    }
}
class MailNotifier {
    public $box;
    public function notify($msg) {
        echo file_get_contents($this->box);
    }
}

$act = isset($_REQUEST['a']) ? $_REQUEST['a'] : '';
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }

if ($act === 'restore') {
    $d = isset($_REQUEST['d']) ? $_REQUEST['d'] : '';
    if ($d === '') die('没收到数据');
    $obj = unserialize(base64_decode($d));
    if ($obj === false) {
        echo "会话恢复失败（串不合法）。";
    } else {
        echo "会话恢复成功：" . h(get_class($obj));
    }
} else {
    $src = h(file_get_contents(__FILE__));
    echo "<!DOCTYPE html><html lang=\"zh\"><head><meta charset=\"utf-8\"><title>PicVault 照片站</title></head>";
    echo "<body style='font-family:sans-serif;max-width:820px;margin:40px auto'>";
    echo "<h1>🖼️ PicVault 照片站（L14 毕业考）</h1>";
    echo "<p>访客的相册会话会被序列化保存，回来时从这恢复：</p>";
    echo "<form method='post' action='?a=restore'>";
    echo "<textarea name='d' rows='4' cols='80' placeholder='base64 的序列化会话'></textarea><br>";
    echo "<button type='submit'>恢复会话</button></form>";
    echo "<h3>全站源码（就这一个文件，随便审）</h3>";
    echo "<pre style='background:#f6f6f6;padding:10px;'>" . $src . "</pre>";
    echo "<p style='color:#888'>情报：旗在本目录的 flag_l14_3b7e.txt。</p>";
    echo "</body></html>";
}
