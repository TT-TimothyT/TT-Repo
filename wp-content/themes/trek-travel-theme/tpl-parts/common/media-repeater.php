<?php
/**
 * Template Part: ACF Media Gallery (images + videos)
 * Usage: get_template_part('tpl-parts/common/media-repeater');
 */
do_action('enqueue_swiper_js');

$cache_key = 'acf_media_gallery_' . get_the_ID();
$cached_html = get_transient($cache_key);

// if ($cached_html === false) {
    ob_start();
    $m_header = get_field('media_header');
    ?>
    <section class="media-repeater">
        <div class="container my-4">
            <?php if ($m_header): ?>
                <h3 class="m-header"><?php echo esc_html($m_header); ?></h3>
            <?php endif; ?>

            <div class="swiper media-swiper">
                <div class="swiper-wrapper">
                    <?php while (have_rows('media_content')) : the_row();
                        $file = get_sub_field('media_file');
                        $preview = get_sub_field('video_thumbnail');
                        $video_text = get_sub_field('video_text');
                        // print_r($preview);

                        if (!$file) continue;

                        $url  = esc_url($file['url']);
                        $mime = $file['mime_type'] ?? '';
                        $alt  = esc_attr($file['title'] ?? '');
                        $is_video = strpos($mime, 'video') !== false;

                        // print_r($mime);
                        ?>
                        <div class="swiper-slide">
                            <div class="media-item p-2 h-100">
                                <?php if ($is_video && $preview): ?>
                                    <div class="video-wrapper">
                                        <video class="video-element" controls playsinline preload="metadata" poster="<?php echo esc_url($preview['url']); ?>">
                                            <source src="<?php echo esc_url($file['url']); ?>" type="<?php echo esc_attr($file['mime_type']); ?>">
                                            Your browser does not support the video tag.
                                        </video>

                                        <div class="video-overlay" data-video-play>
                                            <div class="v-grad"></div>
                                            <img class="video-preview-img" src="<?php echo esc_url($preview['url']); ?>" alt="">
                                            
                                            <?php if ($text = get_sub_field('video_text')): ?>
                                            <div class="video-overlay-text"><?php echo esc_html($text); ?></div>
                                            <?php endif; ?>
                                            
                                            <div class="play-icon-overlay"></div>
                                        </div>
                                    </div>


                                <?php elseif ($is_video): ?>
                                    <div class="video-wrapper">
                                        <video class="video-element" playsinline preload="metadata" poster="<?php echo esc_url($preview['url']); ?>">
                                            <source src="<?php echo esc_url($file['url']); ?>" type="<?php echo esc_attr($file['mime_type']); ?>">
                                            Your browser does not support the video tag.
                                        </video>

                                        <div class="video-overlay" data-video-play>
                                            <div class="v-grad"></div>
                                            
                                            
                                            <?php if ($text = get_sub_field('video_text')): ?>
                                            <div class="video-overlay-text"><?php echo esc_html($text); ?></div>
                                            <?php endif; ?>
                                            
                                            <div class="play-icon-overlay"></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo $url; ?>" alt="<?php echo $alt; ?>" class="img-fluid w-100 rounded" loading="lazy" />
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="swiper-nav">
                                    
                    <div class="s-btn-prev"></div>
                    <div class="swiper-pagination"></div>
                    <div class="s-btn-next"></div>
                </div>
            </div>
        </div>
    </section>
    <?php
    // $cached_html = ob_get_clean();
    // set_transient($cache_key, $cached_html, HOUR_IN_SECONDS);
// }

// echo $cached_html;
?>

<script>
    jQuery(document).ready(function ($) {
    // Swiper init with responsive enable/disable
    const swiper = new Swiper('.media-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 60,
        loop: true,
        // cssMode: true,
        breakpoints: {
            1200: {
            slidesPerView: '3',
            spaceBetween: 30,
            // enabled: false
            },
            768: {
            slidesPerView: 2,
            spaceBetween: 30,
            },
            0: {
            slidesPerView: 1,
            spaceBetween: 20,
            },
        },
        navigation: {
            nextEl: '.s-btn-next',
            prevEl: '.s-btn-prev'
        },
        pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
        });


    // Play video on click of preview image
    $(document).on('click', '.video-preview', function () {
        const videoUrl = $(this).data('video');
        const video = `
            <video controls autoplay class="w-100 rounded">
                <source src="${videoUrl}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        `;
        $(this).replaceWith(video);
    });
});

jQuery(document).ready(function($) {
  $('.video-wrapper').each(function() {
    const $wrapper = $(this);
    const $video = $wrapper.find('video').get(0);
    const $overlay = $wrapper.find('.video-overlay');

    // When overlay clicked: play video & hide overlay
    $overlay.on('click', function() {
      $overlay.addClass('faded'); // fade out overlay
      $video.controls = true;     // show controls
      $video.play();
    });

    // When video ends: show overlay again
    $video.addEventListener('ended', function() {
      $video.controls = false;    // hide controls
      $overlay.removeClass('faded'); // fade in overlay
      $video.currentTime = 0;     // reset video position if desired
    });

    // Optional: pause on click video to toggle play/pause
    // $($video).on('click', function() {
    //   if ($video.paused) {
    //     $video.play();
    //   } else {
    //     $video.pause();
    //   }
    // });
  });
});


</script>
