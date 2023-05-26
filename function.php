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
    
    function crawl_page($fp,$count,$url,$domain)
    {
        $i=0;
        
        $link=$url;
        $domain = "https://yourpetpa.com.au";
        $all_uniqe_title = array();
        $length = count($all_uniqe_title);
        $length++;
        while(1)
        {
            $i++;
            $html = new simple_html_dom();
            $html->load_file($link, false, get_context());
            echo "Page= ".$i."\n";
            $allCollection = $html->find('a.standard-link');
            
            foreach($allCollection  as $collection)
            {
                $link                 = $collection->href;
                $collection_page_link = $domain.$link;

                echo $collection_page_link."\n";
                $p=0;
                while(1)
                {
                $p++;
                $page                 = new simple_html_dom();
                $page                 ->load_file($collection_page_link,false,get_context());
                $category_name        = $page->find('meta[property=og:title]',0)->content;

                echo "Product_list_page= ".$p."\n";

                $products             = $page->find('a.product-block__title-link');

                foreach($products as $product)
                {   
                    $product_details_link = $domain.$product->href;
                    $array = get_product($length,$product_details_link,$category_name);
                    echo ++$count.">>>".$array['Price']."\n";
                    echo 'title'.">>>".$array['title']."\n";

                    $data_insert=0;
                    if (!in_array($array['title'], $all_uniqe_title) && $array!==null) 
                    {
                        $all_uniqe_title[] = $array['title'];
                        $data_insert=1;
                        $length++;
                        
                    } 
                    // var_dump($all_uniqe_title);                
                    if($data_insert)
                    {
                        fputcsv($fp,$array);
                    }
                }

                if($page->find('span.next a',0)!=NULL)
                {
                    $link                 =$page->find('span.next a',0);
                    $new_link             = $link->href;
                    $collection_page_link = $domain.$new_link;
                    echo $collection_page_link."\n";
                    $page                 = "";
                }

                else break;
                }

            }
                
            if($html->find('span.next a',0)!=NULL)
            {
                $new_page=$html->find('span.next a',0);
                $new_page_link = $new_page->href;
                $link = $domain.$new_page_link;
                echo $link."\n";
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
                $price         = "";
                $img           = "";
                $url           = $url;
                $page          = file_get_html($url,false,get_context());
                
                if(!empty($page))
                {
                if($page->find('h3.product-detail__title',0)!=NULL)
                {
                    $t = $page->find('h3.product-detail__title',0);
                    $title = $t->plaintext;
                }
                elseif($page->find('meta[property=og:title]',0)!=NULL)
                {
                    $t = $page->find('meta[property=og:title]',0);
                    $title = $t->content;
                }

                if($page->find('meta[property=og:description]',0)!=NULL)
                {
                    $p = $page->find('meta[property=og:description]',0);
                    $description = trim($p->content);
                }

                if($page->find('span.product-price__compare',0)!=NULL)
                {
                    $p = $page->find('span.product-price__compare ',0);
                    $save =  $page->find('span.theme-money span.percent__discount ',0)->plaintext;
                    $text = trim($p->plaintext);
                    $remove   = array("Don't pay this","save",$save);
                    $price = str_replace($remove,' ', $text);
                }
                
                // elseif($page->find('span.theme-money',0)!=NULL)
                // {
                //     $p = $page->find('span.theme-money',0);
                //     $price = $p->plaintext;
                // }
                if($page->find('meta[property=og:image:secure_url]',0) != NULL)
                {
                    $m= $page->find('meta[property=og:image:secure_url]',0);
                    $img = $m->content;
                }

                    $product_array = array('ID'=>$count,'title'=>$title,'description'=>$description,'category'=>$category,'Price'=>$price,'URL'=>$url,'iamgeURL'=>$img);

                    return $product_array;
                }       
        }