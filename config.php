<?php
return [
    // 最大采集数量
    'maxNum' => 50,
    //定义采集标签
    'contentFields' => ['title','contents'],
    //采集规则
    'contentPatterns' => [
        '<h1 {*?}>{ }<a {*?}>{title?}</a>{ }</h1>',
        '<article {*?}>{ }{contents?}<p><strong>原创不易，觉得不错点个赞。</strong>{*?}</article>',
        '<script type=\'text/javascript\'>{*?}</script>{ }<article {*?}>{ }{contents?}</article>'
    ],
    //列表页所在区域范围规则
    'listSectionPatterns' => ['<div class="stream-list blog-stream">{listSection?}</div>{ }<div class="text-center">{*?}<li class="page-item"'],
    //内容连接规则
    'contentLinkPatterns' => ['<h2{*?}>{ }<a href="{contentLink?}">'],
    //下一页规则
    'nextPagePatterns' => ['<a  class="page-link" rel="next" href="{nextPage?}">'],
    //匹配的页面地址
    'urls' => ['https://segmentfault.com/blogs?page=1']
];
