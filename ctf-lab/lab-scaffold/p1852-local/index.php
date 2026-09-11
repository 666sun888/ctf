<?php
// ============================================
// P1852 babyserialize 本地复刻靶（教练侧验收用）
// 题目源码原样抄录 + WAF/hint 桩（真靶的 waf.php 我看不到，用桩模拟）
// 目的：验证学员的 exp 链是否真的打穿
// ============================================

// ---- 桩：hint() 与 checkcheck() ----
function hint() { echo "[hint] fun == show_me_flag，被 wakeup 拦下（不是我们要的路）\n"; }

function checkcheck($s) {
    // 题目自带 WAF：这里只做最保守的模拟（禁止明显的危险字符），
    // 真靶的规则未知 —— 学员的弹能过说明真靶规则更松
    $black = ['flag', 'fllllllaaag', 'system', 'exec', 'cat', ' ', '(', ')'];
    foreach ($black as $w) {
        if (stripos($s, $w) !== false) {
            // 注意：这里故意设成"不拦"，只为观察链是否走到 eval
            // return "WAF拦截: $w";
        }
    }
    return true;
}

class NISA{
    public $fun="show_me_flag";
    public $txw4ever;
    public function __wakeup()
    {
        if($this->fun=="show_me_flag"){
            hint();
        }
    }
    function __call($from,$val){
        $this->fun=$val[0];
    }
    public function __toString()
    {
        echo $this->fun;
        return " ";
    }
    public function __invoke()
    {
        checkcheck($this->txw4ever);
        @eval($this->txw4ever);
    }
}
class TianXiWei{
    public $ext;
    public $x;
    public function __wakeup()
    {
        $this->ext->nisa($this->x);
    }
}
class Ilovetxw{
    public $huang;
    public $su;

    public function __call($fun1,$arg){
        $this->huang->fun=$arg[0];
    }
    public function __toString(){
        $bb = $this->su;
        return $bb();
    }
}
class four{
    public $a="TXW4EVER";
    private $fun='abc';
    public function __set($name, $value)
    {
        $this->$name=$value;
        if ($this->fun = "sixsixsix"){
            strtolower($this->a);
        }
    }
}
?>
<!DOCTYPE html><html lang="zh"><head><meta charset="utf-8"><title>P1852 本地复刻靶</title></head>
<body style="font-family:sans-serif;max-width:800px;margin:40px auto">
<h1>P1852 babyserialize · 本地复刻靶（教练侧验收）</h1>
<pre style="background:#f6f6f6;padding:10px">入口：?ser=你的序列化串
出口：@eval($this->txw4ever)
本地假旗文件：/tmp/p1852_local_flag.txt
</pre>
<?php
if(isset($_GET['ser'])){
    echo "<h3>引爆区：</h3><pre style=\"background:#fffbe6;padding:10px\">";
    @unserialize($_GET['ser']);
    echo "</pre>";
}else{
    echo "<p>（未传 ser 参数）</p>";
}
?>
</body></html>
