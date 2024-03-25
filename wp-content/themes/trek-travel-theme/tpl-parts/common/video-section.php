<?php
// Video Section Template Part

$video = get_field('intro_video');

?>

<?php if (!empty($video)): ?>
    <section class="video-sect">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <?php if (!empty($video)): 
                        // Extract the YouTube video ID from the URL
                        preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video, $matches);
                        $video_id = $matches[1];
                        // Create the embed URL with parameters to hide overlay visuals
                        $embed_url = "https://www.youtube.com/embed/$video_id?rel=0&showinfo=0&modestbranding=1&autoplay=0&mute=1";
                        ?>
                        <div class="video-box">
                            <iframe src="<?php echo esc_url($embed_url); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
