<?php
class PageLink
{
    public function toAbsolute($url, $main)
    {
        $url_info = $this->parse($url);

        if(!empty($url_info['scheme']))
        {
            return $url;
        }

        $main_url_info = $this->parse($main);

        $new_url = '';
        if(mb_substr($url,0,2) === '//')
        {
            $new_url .= $main_url_info['scheme'] . ':' . $url;
        }
        else if(mb_substr($url,0,1) === '/')
        {
            $new_url .= $main_url_info['scheme'] . '://' . $main_url_info['host'];
            if(!empty($main_url_info['port']))
            {
                $new_url .= ':' . $main_url_info['port'] . $url;
            }
            $new_url .= $url;
        }
        else
        {
            $path = !empty($main_url_info['path'])?$main_url_info['path']:'';
            if(empty($main_url_info['path']))
            {
                $path = '/';
            }
            else if(substr($main_url_info['path'], -1) == '/')
            {
                $path = $main_url_info['path'];
            }
            else
            {
                $path = rtrim(dirname($main_url_info['path']), '\/') . '/';
            }

            $pre = $main_url_info['scheme'] . '://' . $main_url_info['host'] . $path;

                    // if(substr($pre, -1) == '/')
                    // {
                        // $pre = rtrim($pre, '/');
                    // }
                    // else
                    // {
                        // $pre = dirname($pre);
                    // }

            $l = explode('/', $url);
            $ua = array();
            foreach($l as $ll)
            {
                if($ll == '..')
                {
                    $pre = dirname($pre) . '/';
                }
                else if($ll == '' || $ll == '.')
                {

                }
                else
                {
                    $ua[] = $ll;
                }
            }
            $new_url = $pre . implode('/',$ua);
        }
        return $new_url;
    }

    /**
     * 解析url
     */
    public function parse($url)
    {
        return parse_url($url);
    }
}
