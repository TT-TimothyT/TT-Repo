<?php
/**
 * Levels Section Template Part
 */

// Check for the existence of ACF fields to avoid errors when ACF is not enabled
$levels_header = get_field('levels_header');
$averages = get_field('averages');

$levels_button = get_field('levels_button');
$btn_text = $levels_button['text'];
$btn_post = $levels_button['link'];
$btn_link = get_permalink($btn_post);

$lvls_subtext = get_field('levels_subtext');

$l = 1;

$avg_array = array();
foreach ($average['average_levels'] as $lvl) {

}

if ($levels_header || $averages): ?>
    <section class="levels-sect">
        <?php if ($levels_header): ?>
            <div class="container lvl-header">
                <div class="row">
                    <div class="col-12">
                        <h3 class="h2 fw-semibold text-center"><?php echo esc_html($levels_header); ?></h3>
                    </div>
                </div>
                
            </div>
        <?php endif; ?>

        <?php if ($averages): ?>
            <div class="container lvl-container">
                <?php foreach ($averages as $average){
                    $avg_array[] = $average;
                    }
                ?>
                    
                    <?php if ($avg_array): ?>
                        <div class="row lvl-numbers">
                        <div class="col text-end me-2 invisible"><?php echo esc_html($avg_array[1]['average_title']); ?></div>
                        <?php foreach ($avg_array[1]['average_levels'] as $lvl_array){ ?>
                            <div class="col avg-lvls">
                            <span class="h5 text-uppercase">Level <?php echo $l; ?></span>
                            </div>
                        
                        <?php 
                            $l++;
                        } ?>
                        </div>
                        <?php foreach ($averages as $avgs){ ?>
                        <div class="row">
                            <div class="col text-end me-2 avg-title"><span class="h6 text-capitalize"><?php echo esc_html($avgs['average_title']); ?></span></div>
                            
                            <?php if (!empty($avgs['average_levels'])): ?>
                                
                                    <?php 
                                    
                                    foreach ($avgs['average_levels'] as $level): 
                                     
                                        ?>
                                        <div class="avg-lvls col">
                                        <span><?php echo esc_html($level['level']); ?></span>
                                        </div>
                                    <?php 
                                
                                endforeach; ?>
                                
                            <?php endif; ?>
                        </div>
                        <?php } ?>
                        </div>
                    <?php endif; ?>
   
        <?php endif; ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="lvls-find">
                    <?php if (!empty($btn_text) && !empty($btn_link)) { ?>
                        <a class="btn btn-primary" href="<?php echo esc_url($btn_link); ?>"><?php echo $btn_text; ?></a>
                    <?php } ?>

                    <?php if (!empty($lvls_subtext)) { ?>
                        <span class="fst-italic text-center"><?php echo $lvls_subtext; ?></span>
                    <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>
