<?php 
global $product;

$weatherData = trek_weather_data();
$tripCity = strtolower($product->get_attribute('city'));
$tripCountry = strtolower($product->get_attribute('country'));
$tripWeather = $weatherData[$tripCountry][$tripCity];
if (!empty($tripWeather)):
?>

<div class="container pdp-section pdp-weather" id="weather">
    <div class="row">
        <div class="col-12">
            <h5 class="fw-semibold">Weather</h5>
            <p class="fw-normal fs-md lh-md">Average monthly temperature and precipitation</p>
            <div class="container pdp-weather__weather-table">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Jan</th>
                            <th scope="col">Feb</th>
                            <th scope="col">Mar</th>
                            <th scope="col">Apr</th>
                            <th scope="col">May</th>
                            <th scope="col">Jun</th>
                            <th scope="col">Jul</th>
                            <th scope="col">Aug</th>
                            <th scope="col">Sep</th>
                            <th scope="col">Oct</th>
                            <th scope="col">Nov</th>
                            <th scope="col">Dec</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">High</th>
                            <?php 
                            foreach ($tripWeather["f"] as $t => $tValue):
                                if (str_contains($t, 'max')):
                            ?>
                            <td>
                                <span class="unit-group">
                                    <?php echo $tValue; ?>°F<span class="separator">·</span>
                                    <span class="small-text"><?php echo $tripWeather["c"][$t]; ?>°C</span>
                                </span>
                            </td>
                            <?php 
                                endif;
                            endforeach;
                            ?>
                        </tr>
                        <tr>
                            <th scope="row">Low</th>
                            <?php 
                            foreach ($tripWeather["f"] as $t => $tValue):
                                if (str_contains($t, 'min')):
                            ?>
                            <td>
                                <span class="unit-group">
                                    <?php echo $tValue; ?>°F<span class="separator">·</span>
                                    <span class="small-text"><?php echo $tripWeather["c"][$t]; ?>°C</span>
                                </span>
                            </td>
                            <?php 
                                endif;
                            endforeach;
                            ?>
                        </tr>
                        <tr>
                            <th scope="row">Rain</th>
                            <?php 
                            foreach ($tripWeather["pre"] as $p => $pValue):
                                $cmValue = $pValue * 2.54; // Convert inches to centimeters
                            ?>
                            <td>
                                <span class="unit-group">
                                    <?php echo $pValue; ?>"<span class="separator">·</span>
                                    <span class="small-text"><?php echo number_format($cmValue, 2); ?> cm</span>
                                </span>
                            </td>
                            <?php 
                            endforeach;
                            ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif;?>