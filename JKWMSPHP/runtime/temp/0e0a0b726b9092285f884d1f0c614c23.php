<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:69:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/pass.html";i:1545875901;s:69:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/base.html";i:1545875901;s:69:"/Users/jk/Desktop/obj/newWMS/vendor/weiwei/api-doc/src/view/head.html";i:1545875901;}*/ ?>
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

<style>
    html{height:100%;}
    body {
        background-image: linear-gradient(to bottom,#71BA51 0,#00B16A 100%);
        background-repeat: repeat-x;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FF71BA51', endColorstr='#FF00B16A', GradientType=0);
        color: #fff;
        box-sizing: border-box;
        min-height: 100%;
        height: 100%;
    }
</style>

<body>

<div class="container" style="">
    <div class="container">
        <div class="page-header text-center" style="border-bottom: none;margin-top: 200px;"	>
            <h2><?php echo $title; ?> / <?php echo $version; ?></h2>
        </div>
        <div class="row" style="margin-top: 100px;">
            <div class="col-xs-12 col-sm-3"></div>
            <div class="col-xs-12 col-sm-4">
                <input class="form-control input-lg" type="password" id="pass" placeholder="请输入访问密码...">
            </div>
            <div class="col-xs-12 col-sm-2">
                <button type="button" class="btn btn-info btn-lg btn-block">进入</button>
            </div>
            <div class="col-xs-12 col-sm-3"></div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function(){
        $("button").click(function(){
            if($("#pass").val() == ""){
                alert("请输入密码");$("#pass").focus();
            }else{
                $.ajax({
                    url: "<?php echo $root; ?>/doc/login",
                    data: {pass: $("#pass").val() },
                    dataType: "json",
                    success: function(data){
                        if(data.status == 200){
                            location.href = "/doc";
                        }else{
                            alert(data.message);
                        }
                    }
                });
            }
        })
    });
</script>

</body>
</html>