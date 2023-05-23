<?php

// use function PHPSTORM_META\type;

    function get_context()
    {
        $options  = array(
            "http" => array(
                'user_agent'    => 'Mozilla/5.0 (X11; Linux x86_64) 
                                    AppleWebKit/537.36 (KHTML, like Gecko) 
                                    Chrome/107.0.0.0 
                                    Safari/537.36',
                'method'        => 'GET',
            ), 
            "ssl"=>array(
                "verify_peer"           =>  false,
                "verify_peer_name"      =>  false,
                'allow_self_signed'     =>  true
            ),
        );
        $context  = stream_context_create($options);
        return $context;
    }
    
    function crawl_page($fp,$count,$url)
    {
        $i=0;
        $link=$url;
        while(1)
        {
            $i++;
            $html = file_get_html($link,false,get_context());
            echo "Page= ".$i."\n";
            // $items = $html->find('h2.overlay-title  heading-font-4  overlay-title--');
            $allCollection = $html->find('a.standard-link');

            foreach($allCollection  as $collection)
            {
                $collection_page_link = $collection->href;
                
                while(1)
                {

                $page                 = file_get_html($collection_page_link,false,get_context());
                $category_name        = $page->find('h2.large-title',0);
                $products             = $page->find('a.product-block__title-link');

                foreach($products as $product)
                {
                    $array = get_product(++$count,$product->href,$category_name);
                    echo $count.">>>".$array['price']."\n";
                    
                    if($array!==null)
                    {
                        fputcsv($fp,$array);
                    }
                }

                if($page->find('span.next a',0)!=NULL)
                {
                    $link                 =$page->find('span.next a',0);
                    $new_link             = $link->href;
                    $collection_page_link = $new_link;
                    $page                 = "";
                }

                else break;
                }

            }
                
            if($html->find('span.next a',0)!=NULL)
            {
                $new_page=$html->find('span.next a',0);
                $new_page_link = $new_page->href;
                $link = $new_page_link;
                echo $link;
                $html = "";
            }
            else break;
        }
    }
    function get_product($count,$url,$category_name)
        {
                $product_array = array();
                $title         = "";
                $description   = "";
                $category      = $category_name;
                $Price         = "";
                $img           = "";
                $url           = $url;
                $page          = file_get_html($url,false,get_context());
                if(!empty($page))
                {
                    if($page->find('meta[property=og:title]',0)!=NULL)
                {
                    $t = $page->find('meta[property=og:title]',0);
                    $title = $t->content;
                }

                if($page->find('meta[property=og:description]',0)!=NULL)
                {
                    $p = $page->find('meta[property=og:description]',0);
                    $description = trim($p->content);
                }

                if($page->find('span.theme-money',0)!=NULL)
                {
                    $p = $page->find('span.theme-money',0);
                    $price = $p->plaintext;;
                }
                if($page->find('meta[property=og:image:secure_url]',0) != NULL)
                {
                    $m= $page->find('meta[property=og:image:secure_url]',0);
                    $img = $m->content;
                }

                    $product_array = array('ID'=>$count,'title'=>$title,'description'=>$description,'category'=>$category,'Price'=>$price,'URL'=>$url,'iamgeURL'=>$img);

                    return $product_array;
                }       
        }