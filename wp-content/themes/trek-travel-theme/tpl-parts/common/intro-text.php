<?php
/**
 * Template part for displaying the intro section
 * 
 * @package Theme_Name
 */

$header = get_field('intro_header');
$content = get_field('intro_content');

if (!$header && !$content) return;
?>

<section class="intro-text-section">
    <div class="container">
        <div class="content-wrapper">
            <?php if ($header): ?>
                <div class="section-header-b h2"><?php echo esc_html($header); ?></div>
            <?php endif; ?>
            
            <?php if ($content): ?>
                <div class="intro-content">
                    <?php echo wp_kses_post($content); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>