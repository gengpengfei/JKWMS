<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:71:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/search.html";i:1545875901;s:69:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/base.html";i:1545875901;s:69:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/head.html";i:1545875901;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link href='<?php echo $static; ?>/css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='<?php echo $static; ?>/css/style.css' rel='stylesheet' type='text/css'>
    <script src="<?php echo $static; ?>/js/jquery-1.9.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $static; ?>/js/bootstrap.min.js" type="text/javascript"></script>
</head>

<style type="text/css">
    .title{text-align: center;margin: 100px auto;}
    .module{text-align: center;margin: 20px auto;}
    .search {position: relative;}
    .search .typeahead{width: 80%;font-size: 18px;line-height: 1.3333333;}
    .search input{width: 80%;display: inline-block;}
    .search button{height: 48px;width: 18%; margin-top: -5px; text-transform: uppercase;font-weight: bold;font-size: 14px; }
</style>
<script src="<?php echo $static; ?>/js/bootstrap-typeahead.js" type="text/javascript"></script>

<body>

<div class="container">
    <div class="title">
        <h1><?php echo $title; ?></h1>
    </div>

    <div class="module">
        <ul class="nav nav-pills">
            <?php if(is_array($module) || $module instanceof \think\Collection || $module instanceof \think\Paginator): $i = 0; $__LIST__ = $module;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$group): $mod = ($i % 2 );++$i;if(isset($group['children'])): ?>
            <li role="presentation" class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    <?php echo (isset($group['title']) && ($group['title'] !== '')?$group['title']:''); ?> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <?php if(is_array($group['children']) || $group['children'] instanceof \think\Collection || $group['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $group['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
                    <li role="presentation"><a href="#" module><?php echo (isset($val['title']) && ($val['title'] !== '')?$val['title']:''); ?></a></li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </li>
            <?php else: ?>
            <li role="presentation"><a href="#" module><?php echo (isset($group['title']) && ($group['title'] !== '')?$group['title']:''); ?></a></li>
            <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>

    <div class="form-group search">
        <input  id="search_input" class="form-control input-lg  ng-pristine ng-empty ng-invalid ng-invalid-required" type="text" placeholder="接口名称/接口信息/作者/接口地址" data-provide="typeahead" autocomplete="off">
        <button class="btn btn-lg btn-success" id="search" data-loading-text="Loading..." autocomplete="off"><i class="glyphicon glyphicon-search"></i> 搜 素</button>
    </div>

    <div class="result">
        <div class="list-group"></div>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $('#search_input').typeahead({
            source: function (query, process) {
                $.getJSON("<?php echo $root; ?>/doc/search", { "query": query }, function(data){
                    var items = [];
                    $.each(data, function(index, doc){
                        items.push(doc.title);
                    });
                    process(items);
                });
            }
        });
        $('#search').click(function(){
            var query = $('#search_input').val();
            var $btn = $(this).button('loading');
            $.ajax({
                type: "GET",
                url: "<?php echo $root; ?>/doc/search?query="+query,
                dataType:'json',
                success: function (data) {
                    $(".result .list-group").html('');
                    $.each(data, function(index, doc){
                        var item = '<a href="javascript:void(0)" class="list-group-item" name="'+ doc.name +'" title="'+ doc.title +'" doc>' +
                            '<span class="badge">'+ doc.author +'</span>' +
                            ''+ doc.title + '<span class="text-primary">('+ doc.url +')</span>'+'</a>';
                        $(".result .list-group").append(item);
                    });
                    $btn.button('reset');
                },
                complete : function(XMLHttpRequest,status){
                    if(status == 'timeout'){
                        alert("网络超时");
                        $btn.button('reset');
                    }
                }
            });
        });

        $('a[module]').click(function(){
            if(window.parent)
            {
                var zTree = window.parent.zTree;
                var node = zTree.getNodeByParam("title", $(this).text());
                zTree.selectNode(node);
            }
        });

        $(".result .list-group").on('click', 'a[doc]', function(){
            if(window.parent)
            {
                var zTree = window.parent.zTree;
                var node = zTree.getNodeByParam("name", $(this).attr('name'));
                window.parent.loadText(node.tId, $(this).attr('title'), $(this).attr('name'));
                zTree.selectNode(node);
            }
        });
    });
</script>


</body>
</html>