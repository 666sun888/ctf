<?php
$DB = 'ctf7b';
require_once __DIR__ . '/../config.php';
// ============================================
// 考点：堆叠注入变种——UPDATE 改数据 + 业务联动
// 速查：course/SQL注入速查.md 第 5 节（堆叠注入）
// 提示：flag 只有管理员能看到……你现在是 guest。堆叠注入只能查吗？
// ============================================
$flag = "flag{si7b_stacked_update_wins}";
// 模拟已登录用户 guest
$role = 'guest';
$u = mysqli_query($conn, "SELECT role FROM users WHERE username='guest'");
if ($u) { $r = mysqli_fetch_assoc($u); if ($r) $role = $r['role']; }

$rows = [];
$error = '';
$blocked = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    if (preg_match('/union/i', $id)) {
        $blocked = "❌ 拦截：不允许 union";
    } else {
        $sql = "SELECT id, name FROM products WHERE id=" . $id;
        if (mysqli_multi_query($conn, $sql)) {
            do {
                if ($result = mysqli_store_result($conn)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $rows[] = $row;
                    }
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($conn));
        } else {
            $error = mysqli_error($conn);
        }
    }
    // 重新读取角色（如果堆叠 UPDATE 生效，这里会变成 admin）
    $u2 = mysqli_query($conn, "SELECT role FROM users WHERE username='guest'");
    if ($u2) { $r2 = mysqli_fetch_assoc($u2); if ($r2) $role = $r2['role']; }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head><meta charset="utf-8"><title>si7b 用户中心</title></head>
<body style="font-family: sans-serif; max-width: 640px; margin: 40px auto;">
<h1>👤 用户中心（si7b 堆叠变种）</h1>
<p>当前用户：guest（角色：<b><?= htmlspecialchars($role) ?></b>）</p>
<?php if ($role === 'admin'): ?>
  <p style="color:#0a0">🎉 管理员权限已解锁！flag: <code><?= $flag ?></code></p>
<?php endif; ?>
<form method="get">
  商品 ID：<input type="text" name="id" value="1">
  <button type="submit">查询</button>
</form>
<?php if ($blocked): ?><p style="color:#c00"><?= htmlspecialchars($blocked) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:#c00">❌ 出错：<?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($rows): ?>
<table border="1" cellpadding="6">
<?php foreach ($rows as $row): ?>
<tr><?php foreach ($row as $cell): ?><td><?= htmlspecialchars($cell ?? '') ?></td><?php endforeach; ?></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
<p>提示：flag 只有管理员能看到。你现在是 guest——堆叠注入只能查数据吗？想想 UPDATE。</p>
</body>
</html>
