<?php

define('SVN_RES_URL', "http://192.168.0.134:81/svn/longames/starAct/trunk/client/sanguo/staract_2/res/");
define('SVN_SRC_URL', "http://192.168.0.134:81/svn/longames/starAct/trunk/client/sanguo/staract_2/src/app/");

//版本列表，第一个是apk的整包，后面是svn上的版本号，只会对最后面的apk的组做增量打包
$version_list = array(
	array("1.4", 19778, 19992, 20038),
	
); 

?>