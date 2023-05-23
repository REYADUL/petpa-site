<?php 
    ini_set("memory_limit","-1");
    set_time_limit(0);
    ini_set('default_charset','utf-8');
    require_once('simple_html_dom.php');
    require_once('function.php');

    $domain =  "yourpetpa.com.au";

    $url    =  "https://yourpetpa.com.au/collections";

    $fp =fopen($domain.'.csv','w');
    $array = array('ID','Title','Description','Category','Price','URL','ImageURL');

    fputcsv($fp,$array);

    $count = 0;

    crawl_page($fp,$count,$url);

    fclose($fp);
    echo "<!DONE>\n";

?>