<?php
//svn diff --summarize -r 8560:8612 http://192.168.0.134:81/svn/longames/starAct/trunk/client/sanguo/staract_2/res
//svn export -q --force "http://192.168.0.134:81/svn/longames/starAct/trunk/client/sanguo/staract_2/res/effect/effects_role_xuanfeng.ExportJson"@8612 "D:\a\res\effects_role_xuanfeng.ExportJson"

	$dir = getcwd();
	$quick_root = $_ENV["QUICK_V3_ROOT"];
	$php = $quick_root."quick\bin\win32\php.exe ";
	$script = $php.$quick_root."quick/bin/lib/compile_scripts.php";

    function exec_sys_cmd($cmd_str)
    {
        echo "exec: $cmd_str\n";
        //system($cmd_str, $retval);
        exec($cmd_str, $out, $retval);
        // echo $retval."*******************\n";
        return $out;
    }

    function dealOne($file, $key)
    {
		global $ver_name, $last_code, $sub_folder, $code;

		$s1 = strstr($file, $key);
		$pos = strrpos($s1, "/");
		$folder = substr($s1, 0, $pos);
		$name = substr($s1, $pos+1);
		// echo $s1."\n";
		// echo $folder."|".$name."\n";

		$target = "pkg/".$ver_name."/".$sub_folder.$folder;
		mkdir($target, 0777, true);
		$export = "svn export -q --force ".$file."@".$last_code." ".$target;
		// echo $export."\n";
		$out = exec_sys_cmd($export);
    }


	include_once("config.php");
	$len = count($version_list);
	$big = $version_list[$len-1];
	$len = count($big);
	$ver_name = $big[0];
	$last_code = $big[$len-1];
	$folder = "pkg/".$ver_name; 

	if (file_exists($folder))
	{
		date_default_timezone_set("Asia/Shanghai");
		rename($folder, $folder."_".date("Ymd_his"));
	}


	$zip_size = array();
	for ($i=1; $i<$len-1; $i++)
	{
		$code = $big[$i];
		$sub_folder = $last_code."_".$code;

		//****************res*************************
		$summarize = "svn diff --summarize -r ".$code.":".$last_code." ".SVN_RES_URL;
		$out = exec_sys_cmd($summarize);
		// echo $summarize;
		// var_dump($out);

		foreach($out as $one)
		{
			$flag = $one[0];
			if($flag!="A" && $flag!="M") 
			{
				echo "flag:".$flag."\n";
				continue;
			}
			$file  = strstr($one, "http");
			dealOne($file, "/res/");
		}

		//****************src*************************
		$summarize = "svn diff --summarize -r ".$code.":".$last_code." ".SVN_SRC_URL;
		$out = exec_sys_cmd($summarize);
		// echo $summarize;
		// var_dump($out);

		foreach($out as $one)
		{
			$flag = $one[0];
			if($flag!="A" && $flag!="M") 
			{
				echo "flag:".$flag."\n";
				continue;
			}
			$file  = strstr($one, "http");
			dealOne($file, "/app/");
		}


		//write ver.txt
		$ver_file = "pkg/".$ver_name."/".$sub_folder."/ver.txt";
		$fp=fopen($ver_file,'w');
		fwrite($fp, $last_code);
		fclose($fp);

		//compile lua files
		$path = $dir."/pkg/".$ver_name."/".$sub_folder."/app";
		$compile = $script." -i ".$path." -o ".$path." -m files -ek xbl271724 -es XJ";
		// echo $compile;
		exec_sys_cmd($compile);

		$ui = $dir."\\pkg\\".$ver_name."\\".$sub_folder."\\res\\ui";
		echo $ui;
		if(file_exists($ui))
		{
			$rm_ui = "rmdir /s/q ".$ui;
			echo "aaaaaaaaaaaaaaaaaaaaaaaaaaa111111111111111111111111111111111".$rm_ui;			
			exec_sys_cmd($rm_ui);
		}

		// //zip file
		$path = $dir."/pkg/".$ver_name."/".$sub_folder."/";
		$zip_file = $path."../staract_".$sub_folder.".zip";
		chdir($path);
		$zip = "haozipC a -tzip ".$zip_file." *";
		exec_sys_cmd($zip);
		chdir($dir);
		$zip_size[$i] = filesize($zip_file);
	}

	//Version Data, for request
	var_dump($zip_size);
	$ver_file = $dir."/pkg/".$ver_name."/version.php";
	$fp = fopen($ver_file, 'w');
	fwrite($fp, "<?php\n");
	fwrite($fp, "\$version_list = array(\n");
	for($i=1; $i<$len-1; $i++)
	{
		fwrite($fp, "\"".$big[$i]."\"=>array(".$last_code.",".$zip_size[$i]."),\n");
	}
	fwrite($fp, ");\n");
	fwrite($fp, "?>\n");
	fclose($fp);
?>
