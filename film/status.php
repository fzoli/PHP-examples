<?php
include "class.php";
header ('Content-Type: text/html; charset=utf-8');
$page = new Page($userDbName);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $page->printPageHead(""); ?>
<title>Státusz</title>
</head>
<body>
<?php $page->printPageOpen(); ?>
            <div>
              <table class="statInfo">
                <tr>
                  <td class="toCenter">Online: <?php print $page::$user->getUserList(1); ?></td>
                  <td class="toCenter">Látogató: <?php print $page::$user->getUserList(0); ?></td>
                </tr>
              </table>
              <?php if ((bool)$_SESSION['azon']) { ?>
              <table class="lista">
                <tr>
                  <td>Felhasználó (<?php print count(page::$user->getUserList('felhasználó')); ?>)</td>
                </tr>
                <tr>
                  <td><?php $page::printFelhasznalo('felhasználó'); ?></td>
                </tr>
              </table>
              <table class="lista">
                <tr>
                  <td>Moderátor (<?php print count(page::$user->getUserList('moderátor')); ?>)</td>
                </tr>
                <tr>
                  <td><?php $page::printFelhasznalo('moderátor'); ?></td>
                </tr>
              </table>
              <table class="lista">
                <tr>
                  <td>Admin (<?php print count(page::$user->getUserList('admin')); ?>)</td>
                </tr>
                <tr>
                  <td><?php $page::printFelhasznalo('admin'); ?></td>
                </tr>
              </table>
              <table class="lista">
                <tr>
                  <td>Root (<?php print count(page::$user->getUserList('root')); ?>)</td>
                </tr>
                <tr>
                  <td><?php $page::printFelhasznalo('root'); ?></td>
                </tr>
              </table>
              <?php } ?>
              <table class="lista"><tr><td></td></tr></table>
            </div>
<?php $page->printPageClose(); ?>
</body>
</html>