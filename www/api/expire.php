<?php include_once $_SERVER['DOCUMENT_ROOT']."/mida/db/config.php"; ?>
<?php

    if($_SERVER['REMOTE_ADDR']!='117.52.20.22') {
        die('허용되지 않은 IP입니다.');
    }

    $query = "UPDATE mt_site_info SET e_date = :e_date WHERE idx = :idx";
    $value = array(':e_date'=>$_POST['date'], ':idx'=>1);
    $result = execute_pdo($query, $value);

    if(!$result['errorCode']) {
        echo 'success';
    } else {
        echo 'fail';
    }

?>