<?php
use ToolPackage\Rurl;
use ToolPackage\Matcher;
// use \PageLink;
class Gather
{
    //采集配置
    public $config;

    public $rurl;

    public $matcher;

    public $pageLink;

    //最大采集数量
    public $maxNum = 20;

    //当前采集数量
    public $num = 0;

    //当前的列表页数
    public $page = 0;

    //采集结果保存目录
    public $saveDir;

    //结果处理回调
    public $onResult;

    //内容页连接
    public $contentLinks = [];

    public function __construct($config)
    {
        $this->setRurl();
        empty($config['maxNum']) || ($this->maxNum = $config['maxNum']);
        $this->setConfig($config);
        $this->pageLink = new PageLink();
    }

    public function setOnResult($callback)
    {
        $this->onResult = $callback;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setRurl()
    {
        $this->rurl = new Rurl();
    }

    public function setSaveDir($path)
    {
        $this->saveDir = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), '\/');
    }

    /**
     * 访问列表url
     */
    public function requestListUrl($urls)
    {
        $this->rurl->setOnFinished([$this, 'matchContentLink']);
        foreach($urls as $url)
        {
            // echo '访问列表URL: ' . $url . PHP_EOL;
            $this->rurl->get($url);
        }
    }

    /**
     * 匹配列表页中文章链接
     */
    public function matchContentLink($rurl)
    {
        if($this->config['listSectionPatterns'])
        {
            $matcher = new Matcher($rurl->contents);
            $matcher->setFields(['listSection']);
            $matcher->setPatterns($this->config['listSectionPatterns']);
            $matcher->exec();
            $list_section = ($matcher->getResultsOneValue())['listSection'];
        }
        else
        {
            $list_section = $rurl->contents;
        }

        $matcher = new Matcher($list_section);
        $matcher->setFields(['contentLink']);
        $matcher->setPatterns($this->config['contentLinkPatterns']);
        $matcher->exec();
        $links = ($matcher->getResults())['contentLink'];
        array_walk($links, function(&$val, $key){
            $val = $this->pageLink->toAbsolute($val, $this->rurl->currentUri);
        });
        $this->contentLinks = array_merge($this->contentLinks, $links);

        if($this->config['nextPagePatterns'])
        {
            $matcher = new Matcher($rurl->contents);
            $matcher->setFields(['nextPage']);
            $matcher->setPatterns($this->config['nextPagePatterns']);
            $matcher->exec();
            $this->nextPage = ($matcher->getResults())['nextPage'];
            array_walk($this->nextPage, function(&$val){
                $val = $this->pageLink->toAbsolute($val, $this->rurl->currentUri);
            });
        }
    }

    /**
     * 请求内容页面
     */
    public function requestContentUrl()
    {
        $this->rurl->setOnfinished([$this, 'matchContent']);
        while($url = array_shift($this->contentLinks))
        {
            if($this->num >= $this->maxNum)
            {
                break;
            }
            // echo '访问内容URI: ' . $url . PHP_EOL;
            $this->rurl->get($url);
        }
    }

    /**
     * 匹配内容
     */
    public function matchContent($rurl)
    {
        $matcher = new Matcher($rurl->contents);
        $matcher->setFields($this->config['contentFields']);
        $matcher->setPatterns($this->config['contentPatterns']);
        $matcher->exec();
        $this->num++;
        $result = $matcher->getResultsOneValue();
        if($this->onResult)
        {
            ($this->onResult)($result, $this->num, $this->page, $this->rurl->currentUri);
        }
        else
        {
            $this->save($result);
        }
    }

    public function catFile($name)
    {
        if($this->saveDir)
        {
            return $this->saveDir . DIRECTORY_SEPARATOR . $name;
        }
        else
        {
            return getcwd() . DIRECTORY_SEPARATOR . $name;
        }
    }

    /**
     * 保存结果
     */
    public function save($result)
    {
        $pre_save_data = json_encode($result);
        $contentName = $this->catFile('contents/' . md5($pre_save_data));
        $indexFile = $this->catFile('index/index.json');
        $this->rurl->createFile($contentName);
        file_put_contents($contentName, $pre_save_data);
        if(file_exists($indexFile))
        {
            $indexDB = json_decode(file_get_contents($indexFile), true);
        }
        else
        {
            $indexDB = [];
        }
        array_push($indexDB, $contentName);
        $this->rurl->createFile($indexFile);
        file_put_contents($indexFile, json_encode($indexDB));
    }

    /**
     * 运行
     */
    public function exec()
    {
        $this->nextPage = $this->config['urls'];
        while($this->nextPage && $this->num < $this->maxNum)
        {
            $this->requestListUrl($this->nextPage);
            $this->requestContentUrl();
        }
    }
}
