<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 赞助页面
 *
 * @package custom
 */
$this->need('header.php'); ?>
<div id="main" class="container">
    <div id="main-page" role="main" style="display: none;">
        <article>
            <blockquote class="pull-right">
                <h6><?php $this->title() ?></h6>
            </blockquote>
            <div class="clearfix"></div>
            <div class="post-content" itemprop="articleBody">
                <?php $this->content=parse_Sponsor_Content($this->content, $this->options->title, $this->author->uid); ?>
                <?php $this->content(); ?>
            </div>
            <?php
                printSponsor(
                    $this->options->sponsorChoose,
                    $this->options->themeUrl,
                    $this->options->sponsorPageURL,
                    $this->options->wechatCode,
                    $this->options->alipayCode,
                    TRUE
                );
            ?>
        </article>
    </div>
</div>
<?php $this->need('footer.php'); ?>