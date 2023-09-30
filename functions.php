<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form) {
    $sponsorChoose = new Typecho\Widget\Helper\Form\Element\Radio(
        'sponsorChoose',
        array(
            "sponsorclose" => "关闭赞赏功能",
            "sponsorFooter" => '显示在页脚',
            "sponsorPage" => '显示在页面'
        ),
        "sponsorclose",
        _t('是否开启赞赏功能')
    );
    $form->addInput($sponsorChoose);
    $sponsorPageURL=new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorPageURL',
        NULL,
        '',
        _t('赞赏页面路径'),
        _t('仅当开启赞赏显示在页面时有效')
    );
    $form->addInput($sponsorPageURL);
    $wechatCode=new \Typecho\Widget\Helper\Form\Element\Text(
        'wechatCode',
        NULL,
        '',
        _t('微信打赏二维码'),
        _t('填入微信打赏二维码路径')
    );
    $form->addInput($wechatCode);
    $alipayCode=new \Typecho\Widget\Helper\Form\Element\Text(
        'alipayCode',
        NULL,
        '',
        _t('支付宝打赏二维码'),
        _t('填入支付宝打赏二维码路径')
    );
    $form->addInput($alipayCode);
}

function parse_Sponsor_Content($content, $BlogsName, $uid) {
    // 解析赞赏界面
    $v1 = 'count(1)';
    $v2 = 'sum(CHAR_LENGTH(`text`))';
    $v3 = 'FROM_UNIXTIME(`created`, "%Y-%m-%d")';
    $db = Typecho_Db::get();
    $query1 = $db->select($v1, $v2)->from('table.contents')
    ->where('type = ?', 'post');
    $query2 = $db->select($v3)->from('table.users')
    ->where('uid = ?', $uid);
    // $result = $db->fetchAll($query);
    // echo $result[0][$v1];
    $result = $db->fetchRow($query1);
    $PostNum = $result[$v1];
    $CharNum = $result[$v2];
    $PostTime = $db->fetchRow($query2)[$v3];
    $content = preg_replace("/\\\$BlogsName/sm",$BlogsName,$content);
    $content = preg_replace("/\\\$PostNum/sm",$PostNum,$content);
    $content = preg_replace("/\\\$CharNum/sm",$CharNum,$content);
    $content = preg_replace("/\\\$PostTime/sm",$PostTime,$content);
    return $content;
}
function printSponsor($sponsorChoose, $themeUrl, $sponsorPageURL, $wechatCode, $alipayCode, $isSponsorPage = FALSE) {
    // 输出HTML
    if ($isSponsorPage) { // 赞赏页面
        print <<<EOT
        <div class="post-content post-sponsor-tag">
            <a href="javascript:void(0)" onclick="reward()" title="打赏，支持一下">支持作者 ⚡️</a>
        </div>
        EOT;
        // 输出QR
        printSponsorQR($themeUrl, $wechatCode, $alipayCode);
    } else {
        if ($sponsorChoose == 'sponsorclose') return; // 未开启赞赏功能
        if ($sponsorChoose == 'sponsorFooter') { // 显示在页脚
            print <<<EOT
                <div class="post-content post-sponsor-tag">
                <p>如果你认为这篇文章还不错，可以考虑
                    <a href="javascript:void(0)" onclick="reward()" title="打赏，支持一下">为作者充电 ⚡️</a>
                </p>
                </div>
            EOT;
            // 输出QR
            printSponsorQR($themeUrl, $wechatCode, $alipayCode);
        } else { // 显示在独立页面
            print <<<EOT
                <div class="post-content post-sponsor-tag">
                <p>如果你认为这篇文章还不错，可以考虑
                    <a href={$sponsorPageURL} title="打赏，支持一下">为作者充电 ⚡️</a>
                </p>
                </div>
            EOT;
        }
        printSponsorCSSShadow();
    }
    printSponsorCSS($themeUrl);
}
function printSponsorQR($themeUrl, $wechatCode, $alipayCode) {
    print <<<EOT
        <div class="hide_qr" style="display: none;"></div>
        <div class="show_qr" style="display: none;">
            <a class="qr_close" href="javascript:void(0)" onclick="reward()" title="关闭">
                <img src="{$themeUrl}/img/close.png" alt="取消">
            </a>
            <div class="reward_img">
                <img src={$wechatCode} alt="收款二维码">
            </div>
            <div class="reward_bg">
                <div class="pay_box choice" qr_code={$wechatCode}>
                    <span class="pay_box_span"></span>
                    <span class="qr_code">
                        <img src="{$themeUrl}/img/wechat.svg" alt="微信二维码">
                    </span>
                </div>
                <div class="pay_box" qr_code={$alipayCode}>
                    <span class="pay_box_span"></span>
                    <span class="qr_code">
                        <img src="{$themeUrl}/img/alipay.svg" alt="支付宝二维码">
                    </span>
                </div>
            </div>
        </div>
    EOT;
    // 输出JS
    print <<<EOT
        <script src="{$themeUrl}/js/jquery.min.js"></script>
        <script>
            $(function() {
                $(".pay_box").click(function() {
                    $(this).addClass('choice').siblings('.pay_box').removeClass('choice');
                    var qr_code = $(this).attr('qr_code');
                    $(".reward_img img").attr("src", qr_code);
                });
                $(".hide_qr").click(function() {
                    reward();
                });
            });
            function reward() {
                $(".hide_qr").fadeToggle();
                $(".show_qr").fadeToggle();
            }
        </script>
    EOT;
}
function printSponsorCSSShadow() {
    print <<<EOT
    <style>
        .post-sponsor-tag {
            margin-bottom: 60px;
        }

        .post-sponsor-tag a {
            display: inline-block;
            position: relative;
            text-decoration: none;
            line-height: 1.4em;
            z-index: 0;
            transition: all .25s ease;
            padding: 0 3px
        }

        .post-sponsor-tag a::after {
            content: "";
            position: absolute;
            display: block;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 40%;
            z-index: -1;
            transition: all .25s ease;
            background-color: rgba(132, 231, 25, 0.3);
        }

        .post-sponsor-tag a:hover::after {
            height: 100%;
            background-color: rgba(132, 231, 25, 0.3);
        }
    </style>
    EOT;
}
function printSponsorCSS($themeUrl) {
    print <<<EOT
        <style>
            .hide_qr {
                z-index: 999;
                background: #000;
                opacity: .5;
                -moz-opacity: .5;
                left: 0;
                top: 0;
                height: 100%;
                width: 100%;
                position: fixed;
                display: none;
            }

            .show_qr {
                width: 23vw;
                background-color: #fff;
                border-radius: 6px;
                position: fixed;
                z-index: 1000;
                left: 50%;
                top: 50%;
                margin-left: -11.5vw;
                margin-top: -15%;
                display: none;
            }

            .show_qr a.qr_close {
                display: inline-block;
                top: 10px;
                position: absolute;
                right: 10px;
            }

            .show_qr img {
                border: none;
                border-width: 0;
                border-radius: 6px 6px 0 0;
                width: 100%;
                height: auto;
                margin: 0;
                box-shadow: none;
            }

            .show_qr a.qr_close img {
                border-radius: 0;
            }

            .reward_bg {
                text-align: center;
                margin: 0 auto;
                cursor: pointer;
                width: 100%;
                height: 100%;
                overflow: hidden;
            }

            .pay_box {
                display: inline-block;
                margin-right: 10px;
                padding: 15px 0;
            }

            .pay_box img {
                width: auto;
            }

            span.pay_box_span {
                width: 16px;
                height: 16px;
                background: url({$themeUrl}/img/noselect.svg);
                display: block;
                float: left;
                margin-top: 6px;
                margin-right: 5px;
            }

            .pay_box.choice span.pay_box_span {
                background: url({$themeUrl}/img/select.svg);
            }

            .reward_bg img {
                display: inline !important;
            }
        </style>
    EOT;
}
